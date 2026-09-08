<?php

namespace ABlocks\API;

use ABlocks\Helper;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Media intake for editor pastes.
 *
 * Pasting a document into the block editor drops every embedded image into the
 * clipboard as a `data:` URI. The editor turns each one into a File whose name
 * comes from the MIME type alone — `image/png` becomes `image.png` — so a
 * document with three pictures produces three uploads that all ask for the same
 * filename. `wp_unique_filename()` cannot separate them because the uploads run
 * concurrently and none has hit the disk when the others pick their name, so the
 * last write wins and the earlier images are silently destroyed: three
 * attachment rows, one file, the same picture repeated down the page.
 *
 * This endpoint takes the image before the editor can, and names it after its
 * own bytes (`<slug>-<hash>.webp`). Two different images can never collide, and
 * the same image pasted twice resolves to the attachment already in the library
 * instead of a duplicate. Images are converted to WebP on the way in, which is
 * the point of routing them through here rather than `wp/v2/media`.
 */
class PasteController {

	/**
	 * Attachment meta holding the sha1 of the bytes we ingested, used to
	 * recognise an image that is already in the library.
	 */
	const HASH_META = '_ablocks_paste_hash';

	/**
	 * Formats left untouched: WebP is already the target, GIF would lose its
	 * animation, and SVG is not a raster image at all.
	 */
	const NO_CONVERT = [ 'image/webp', 'image/gif', 'image/svg+xml' ];

	/**
	 * Lossy WebP quality. Only photographs ever reach it — anything flat enough
	 * for lossless to win is stored losslessly — so this leans towards detail
	 * rather than towards the smallest possible file.
	 */
	const DEFAULT_QUALITY = 92;

	public function register_routes() {
		register_rest_route(
			ABLOCKS_REST_NAMESPACE,
			'/paste/image',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'ingest_image' ),
				'permission_callback' => array( $this, 'can_upload' ),
				'args'                => array(
					'url'     => array(
						'required'          => false,
						'type'              => 'string',
						'description'       => 'Remote image URL to fetch. Ignored when a file is uploaded.',
						'sanitize_callback' => 'esc_url_raw',
					),
					'name'    => array(
						'required'          => false,
						'type'              => 'string',
						'description'       => 'Filename hint, usually a slug of the alt text or nearest heading.',
						'sanitize_callback' => 'sanitize_text_field',
					),
					'alt'     => array(
						'required'          => false,
						'type'              => 'string',
						'description'       => 'Alt text to store on the attachment.',
						'sanitize_callback' => 'sanitize_text_field',
					),
					'post_id' => array(
						'required'          => false,
						'type'              => 'integer',
						'description'       => 'Post the attachment should belong to.',
						'sanitize_callback' => 'absint',
					),
				),
			)
		);
	}

	public function can_upload() {
		return current_user_can( 'upload_files' );
	}

	/**
	 * Take one pasted image into the media library, as WebP where that helps.
	 *
	 * @param WP_REST_Request $request Multipart request carrying `file`, or a
	 *                                 body carrying `url`.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function ingest_image( WP_REST_Request $request ) {
		$this->load_media_api();

		$files   = $request->get_file_params();
		$post_id = (int) $request->get_param( 'post_id' );

		if ( ! empty( $files['file'] ) ) {
			$source = $this->stage_uploaded_file( $files['file'] );
		} else {
			$source = $this->stage_remote_file( (string) $request->get_param( 'url' ) );
		}

		if ( is_wp_error( $source ) ) {
			return $source;
		}

		$hash     = sha1_file( $source['path'] );
		$existing = $this->find_by_hash( $hash );

		if ( $existing ) {
			$this->discard( $source['path'] );
			return rest_ensure_response( $this->describe( $existing, true ) );
		}

		$prepared = $this->to_webp( $source['path'], $source['mime'] );
		if ( is_wp_error( $prepared ) ) {
			$this->discard( $source['path'] );
			return $prepared;
		}

		$attachment_id = $this->store(
			$prepared,
			$this->build_filename( (string) $request->get_param( 'name' ), $hash, $prepared['mime'] ),
			$post_id
		);

		if ( is_wp_error( $attachment_id ) ) {
			$this->discard( $prepared['path'] );
			return $attachment_id;
		}

		update_post_meta( $attachment_id, self::HASH_META, $hash );

		$alt = (string) $request->get_param( 'alt' );
		if ( '' !== $alt ) {
			update_post_meta( $attachment_id, '_wp_attachment_image_alt', $alt );
		}

		return rest_ensure_response( $this->describe( $attachment_id, false ) );
	}

	/**
	 * Pull in the upload and image helpers.
	 *
	 * `wp_tempnam()`, `download_url()`, `wp_handle_sideload()` and
	 * `wp_generate_attachment_metadata()` all live under `wp-admin/includes`,
	 * which a REST request never loads on its own.
	 */
	private function load_media_api() {
		if ( ! function_exists( 'wp_tempnam' ) || ! function_exists( 'download_url' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}
		if ( ! function_exists( 'wp_generate_attachment_metadata' ) ) {
			require_once ABSPATH . 'wp-admin/includes/image.php';
		}
		if ( ! function_exists( 'media_handle_sideload' ) ) {
			require_once ABSPATH . 'wp-admin/includes/media.php';
		}
	}

	/**
	 * Move a multipart upload somewhere we can work on it, having checked that
	 * the bytes really are an image the site accepts.
	 *
	 * @param array $file One entry from `$_FILES`.
	 *
	 * @return array|WP_Error `[ path, mime ]`.
	 */
	private function stage_uploaded_file( array $file ) {
		if ( ! empty( $file['error'] ) || empty( $file['tmp_name'] ) ) {
			return new WP_Error( 'ablocks_paste_upload_failed', __( 'The pasted image could not be read.', 'ablocks' ), array( 'status' => 400 ) );
		}

		$max = (int) apply_filters( 'ablocks/paste/max_image_bytes', 20 * MB_IN_BYTES );
		if ( $max > 0 && filesize( $file['tmp_name'] ) > $max ) {
			return new WP_Error( 'ablocks_paste_too_large', __( 'The pasted image is too large to import.', 'ablocks' ), array( 'status' => 413 ) );
		}

		$mime = $this->sniff_image_mime( $file['tmp_name'] );
		if ( is_wp_error( $mime ) ) {
			return $mime;
		}

		// The multipart temp file is cleaned up by PHP at request end, which is
		// too early: WebP conversion and the sideload both still need it. Take
		// our own copy so the lifetime is ours to manage.
		$copy = wp_tempnam( 'ablocks-paste' );
		if ( ! $copy || ! copy( $file['tmp_name'], $copy ) ) {
			return new WP_Error( 'ablocks_paste_stage_failed', __( 'The pasted image could not be staged for import.', 'ablocks' ), array( 'status' => 500 ) );
		}

		return array(
			'path' => $copy,
			'mime' => $mime,
		);
	}

	/**
	 * Download a remote image referenced by the pasted markup.
	 *
	 * @param string $url Absolute http(s) URL.
	 *
	 * @return array|WP_Error `[ path, mime ]`.
	 */
	private function stage_remote_file( string $url ) {
		if ( '' === $url || ! wp_http_validate_url( $url ) ) {
			return new WP_Error( 'ablocks_paste_bad_url', __( 'No importable image was supplied.', 'ablocks' ), array( 'status' => 400 ) );
		}

		if ( ! $this->host_is_allowed( $url ) ) {
			return new WP_Error( 'ablocks_paste_host_blocked', __( 'That image host is not allowed for pasted content.', 'ablocks' ), array( 'status' => 403 ) );
		}

		// download_url() goes through wp_safe_remote_get(), so redirects into
		// the local network are rejected for us.
		$temp = download_url( $url );
		if ( is_wp_error( $temp ) ) {
			return $temp;
		}

		$mime = $this->sniff_image_mime( $temp );
		if ( is_wp_error( $mime ) ) {
			$this->discard( $temp );
			return $mime;
		}

		return array(
			'path' => $temp,
			'mime' => $mime,
		);
	}

	/**
	 * Only fetch from the hosts a document paste legitimately points at. Pastes
	 * carry markup we did not author, so an arbitrary URL in an `<img>` is an
	 * instruction from an untrusted source, not from the user.
	 *
	 * @param string $url Absolute URL.
	 */
	private function host_is_allowed( string $url ): bool {
		$host = wp_parse_url( $url, PHP_URL_HOST );
		if ( ! $host ) {
			return false;
		}
		$host = strtolower( $host );

		$allowed = apply_filters(
			'ablocks/paste/allowed_image_hosts',
			array( 'googleusercontent.com', 'google.com', 'gstatic.com' )
		);

		$site_host = strtolower( (string) wp_parse_url( home_url(), PHP_URL_HOST ) );
		if ( $site_host && $host === $site_host ) {
			return true;
		}

		foreach ( (array) $allowed as $domain ) {
			$domain = strtolower( ltrim( (string) $domain, '.' ) );
			if ( '' === $domain ) {
				continue;
			}
			if ( $host === $domain || substr( $host, - ( strlen( $domain ) + 1 ) ) === '.' . $domain ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Decide the MIME from the bytes rather than from anything the client said,
	 * and refuse anything that is not an image the site would accept on upload.
	 *
	 * @param string $path Local file.
	 *
	 * @return string|WP_Error
	 */
	private function sniff_image_mime( string $path ) {
		$size = @getimagesize( $path ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- A non-image is an expected input here, not an error.
		$mime = ! empty( $size['mime'] ) ? $size['mime'] : '';

		if ( ! $mime ) {
			$checked = wp_check_filetype_and_ext( $path, basename( $path ) );
			$mime    = ! empty( $checked['type'] ) ? $checked['type'] : '';
		}

		if ( ! $mime || 0 !== strpos( $mime, 'image/' ) ) {
			return new WP_Error( 'ablocks_paste_not_an_image', __( 'The pasted file is not an image.', 'ablocks' ), array( 'status' => 415 ) );
		}

		if ( ! in_array( $mime, (array) get_allowed_mime_types(), true ) ) {
			return new WP_Error( 'ablocks_paste_mime_blocked', __( 'That image type is not allowed on this site.', 'ablocks' ), array( 'status' => 415 ) );
		}

		return $mime;
	}

	/**
	 * Re-encode to WebP when that is both possible and worth it.
	 *
	 * A WebP that comes out heavier than its source — common for small flat-
	 * colour PNGs — is thrown away and the original kept, so the conversion can
	 * only ever shrink a page.
	 *
	 * @param string $path Staged file, replaced in place on success.
	 * @param string $mime MIME of the staged file.
	 *
	 * @return array|WP_Error `[ path, mime ]`.
	 */
	private function to_webp( string $path, string $mime ) {
		$unchanged = array(
			'path' => $path,
			'mime' => $mime,
		);

		$enabled = (bool) apply_filters(
			'ablocks/paste/convert_webp',
			(bool) Helper::get_settings( 'paste_convert_webp', true )
		);

		if ( ! $enabled || in_array( $mime, self::NO_CONVERT, true ) ) {
			return $unchanged;
		}

		if ( ! wp_image_editor_supports( array( 'mime_type' => 'image/webp' ) ) ) {
			return $unchanged;
		}

		$quality = (int) apply_filters(
			'ablocks/paste/webp_quality',
			(int) Helper::get_settings( 'paste_webp_quality', self::DEFAULT_QUALITY )
		);
		$quality = max( 1, min( 100, $quality ) );

		// Encode both ways and keep whichever is smaller. Which one wins is
		// decided entirely by the picture: a chart, diagram or screenshot is a
		// few flat colours, so lossless comes out a fraction of the lossy file
		// AND pixel-identical, while a photograph is the other way round by a
		// wide margin. Guessing from a colour count would need a threshold to
		// tune and would still be wrong sometimes; encoding twice costs about a
		// second on the largest images and is right every time.
		$candidates = array_filter( array(
			$this->encode_webp( $path, $mime, true, $quality ),
			$this->encode_webp( $path, $mime, false, $quality ),
		) );

		if ( ! $candidates ) {
			return $unchanged;
		}

		usort( $candidates, static function ( $a, $b ) {
			return filesize( $a ) <=> filesize( $b );
		} );

		$converted = array_shift( $candidates );
		foreach ( $candidates as $discard ) {
			$this->discard( $discard );
		}

		// A tiny PNG of two flat colours can still beat anything WebP does with
		// it. Keeping whichever file is smaller means the conversion can only
		// ever shrink a page; set this filter false to have WebP unconditionally.
		$prefer_smaller = (bool) apply_filters( 'ablocks/paste/keep_smaller_original', true );

		if ( $prefer_smaller && filesize( $converted ) >= filesize( $path ) ) {
			$this->discard( $converted );
			return $unchanged;
		}

		$this->discard( $path );

		return array(
			'path' => $converted,
			'mime' => 'image/webp',
		);
	}

	/**
	 * Write one WebP, lossless or lossy.
	 *
	 * `WP_Image_Editor` cannot do this. It has no lossless mode at all, and its
	 * quality is not usable for a format conversion either: `get_output_format()`
	 * calls `set_quality()` with no argument whenever the output mime differs
	 * from the input, which throws away whatever was set and substitutes core's
	 * default of 86 — so `set_quality( 92 )` followed by `save( $file,
	 * 'image/webp' )` silently encodes at 86. Hence the direct encoders.
	 *
	 * @param string $path     Source image.
	 * @param string $mime     Source MIME, for picking the GD reader.
	 * @param bool   $lossless Encode losslessly.
	 * @param int    $quality  Quality 1-100, lossy only.
	 *
	 * @return string|false Path to the encoded file, or false.
	 */
	private function encode_webp( string $path, string $mime, bool $lossless, int $quality ) {
		$target = wp_tempnam( 'ablocks-paste-webp' );
		if ( ! $target ) {
			return false;
		}

		$written = class_exists( 'Imagick' )
			? $this->encode_webp_imagick( $path, $target, $lossless, $quality )
			: $this->encode_webp_gd( $path, $mime, $target, $lossless, $quality );

		if ( ! $written || ! file_exists( $target ) || ! filesize( $target ) ) {
			$this->discard( $target );
			return false;
		}

		return $target;
	}

	/**
	 * @param string $path     Source image.
	 * @param string $target   File to write.
	 * @param bool   $lossless Encode losslessly.
	 * @param int    $quality  Quality 1-100, lossy only.
	 */
	private function encode_webp_imagick( string $path, string $target, bool $lossless, int $quality ): bool {
		try {
			$image = new \Imagick( $path );

			// An animation would be flattened to its first frame; those are
			// excluded upstream, but a multi-frame source could still arrive.
			if ( $image->getNumberImages() > 1 ) {
				$image->clear();
				return false;
			}

			$image->setImageFormat( 'webp' );

			if ( $lossless ) {
				$image->setOption( 'webp:lossless', 'true' );
			} else {
				$image->setImageCompressionQuality( $quality );
			}

			$written = $image->writeImage( $target );
			$image->clear();

			return (bool) $written;
		} catch ( \Exception $e ) {
			return false;
		}//end try
	}

	/**
	 * @param string $path     Source image.
	 * @param string $mime     Source MIME.
	 * @param string $target   File to write.
	 * @param bool   $lossless Encode losslessly.
	 * @param int    $quality  Quality 1-100, lossy only.
	 */
	private function encode_webp_gd( string $path, string $mime, string $target, bool $lossless, int $quality ): bool {
		if ( ! function_exists( 'imagewebp' ) ) {
			return false;
		}

		// GD only learned the lossless flag in PHP 8.1.
		if ( $lossless && ! defined( 'IMG_WEBP_LOSSLESS' ) ) {
			return false;
		}

		$readers = array(
			'image/png'  => 'imagecreatefrompng',
			'image/jpeg' => 'imagecreatefromjpeg',
			'image/gif'  => 'imagecreatefromgif',
			'image/webp' => 'imagecreatefromwebp',
			'image/bmp'  => 'imagecreatefrombmp',
		);

		if ( empty( $readers[ $mime ] ) || ! function_exists( $readers[ $mime ] ) ) {
			return false;
		}

		$image = @call_user_func( $readers[ $mime ], $path ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- A malformed image is an expected input, not an error.
		if ( ! $image ) {
			return false;
		}

		// Transparency survives WebP, but only if GD is told to keep it.
		imagepalettetotruecolor( $image );
		imagealphablending( $image, false );
		imagesavealpha( $image, true );

		$written = imagewebp( $image, $target, $lossless ? IMG_WEBP_LOSSLESS : $quality );
		imagedestroy( $image );

		return (bool) $written;
	}

	/**
	 * Name the file after its own content.
	 *
	 * The slug keeps it findable in the media library; the hash fragment is what
	 * makes two different pasted images impossible to confuse, which is the
	 * whole reason this endpoint exists.
	 *
	 * @param string $hint Caller's suggestion, typically from alt text.
	 * @param string $hash sha1 of the source bytes.
	 * @param string $mime MIME of the file being stored.
	 */
	private function build_filename( string $hint, string $hash, string $mime ): string {
		$slug = sanitize_title( $hint );

		// `image` is what the editor calls every clipboard image; it says nothing
		// about the picture, so fall back to something that at least says where
		// it came from.
		if ( '' === $slug || 'image' === $slug ) {
			$slug = 'pasted-image';
		}

		// Alt text is a sentence; cut it back to whole words so the filename
		// still reads as something rather than ending mid-syllable.
		if ( strlen( $slug ) > 60 ) {
			$slug = substr( $slug, 0, 60 );
			$break = strrpos( $slug, '-' );
			if ( false !== $break && $break > 20 ) {
				$slug = substr( $slug, 0, $break );
			}
		}

		$ext = 'jpg';

		$known = wp_get_mime_types();
		foreach ( $known as $extensions => $type ) {
			if ( $type === $mime ) {
				$ext = strtok( $extensions, '|' );
				break;
			}
		}

		return sanitize_file_name( $slug . '-' . substr( $hash, 0, 8 ) . '.' . $ext );
	}

	/**
	 * Move a staged file into the uploads directory and register the attachment.
	 *
	 * @param array  $prepared `[ path, mime ]`.
	 * @param string $filename Name to store it under.
	 * @param int    $post_id  Post to attach it to, 0 for none.
	 *
	 * @return int|WP_Error Attachment ID.
	 */
	private function store( array $prepared, string $filename, int $post_id ) {
		$file = array(
			'name'     => $filename,
			'type'     => $prepared['mime'],
			'tmp_name' => $prepared['path'],
			'error'    => 0,
			'size'     => filesize( $prepared['path'] ),
		);

		$sideloaded = wp_handle_sideload(
			$file,
			array(
				'test_form' => false,
				'mimes'     => array( pathinfo( $filename, PATHINFO_EXTENSION ) => $prepared['mime'] ),
			)
		);

		if ( ! empty( $sideloaded['error'] ) ) {
			return new WP_Error( 'ablocks_paste_sideload_failed', $sideloaded['error'], array( 'status' => 500 ) );
		}

		$attachment_id = wp_insert_attachment(
			array(
				'post_mime_type' => $sideloaded['type'],
				'post_title'     => preg_replace( '/\.[^.]+$/', '', wp_basename( $sideloaded['file'] ) ),
				'post_content'   => '',
				'post_status'    => 'inherit',
			),
			$sideloaded['file'],
			$post_id,
			true
		);

		if ( is_wp_error( $attachment_id ) ) {
			return $attachment_id;
		}

		wp_update_attachment_metadata(
			$attachment_id,
			wp_generate_attachment_metadata( $attachment_id, $sideloaded['file'] )
		);

		return (int) $attachment_id;
	}

	/**
	 * Find an attachment already holding these exact bytes.
	 *
	 * @param string $hash sha1 of the source bytes.
	 *
	 * @return int Attachment ID, or 0.
	 */
	private function find_by_hash( string $hash ): int {
		$found = get_posts(
			array(
				'post_type'              => 'attachment',
				'post_status'            => 'inherit',
				'posts_per_page'         => 1,
				'fields'                 => 'ids',
				'no_found_rows'          => true,
				'update_post_term_cache' => false,
				'meta_key'               => self::HASH_META, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- Indexed lookup on a single row; the alternative is a duplicate upload.
				'meta_value'             => $hash, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			)
		);

		if ( empty( $found ) ) {
			return 0;
		}

		// A row can outlive its file if the upload was removed from disk by hand.
		$id = (int) $found[0];
		if ( ! get_attached_file( $id ) || ! file_exists( get_attached_file( $id ) ) ) {
			return 0;
		}

		return $id;
	}

	/**
	 * Shape one attachment for the editor.
	 *
	 * @param int  $attachment_id Attachment.
	 * @param bool $reused        Whether it was already in the library.
	 */
	private function describe( int $attachment_id, bool $reused ): array {
		$meta = wp_get_attachment_metadata( $attachment_id );

		return array(
			'id'     => $attachment_id,
			'url'    => wp_get_attachment_url( $attachment_id ),
			'width'  => isset( $meta['width'] ) ? (int) $meta['width'] : 0,
			'height' => isset( $meta['height'] ) ? (int) $meta['height'] : 0,
			'mime'   => get_post_mime_type( $attachment_id ),
			'alt'    => (string) get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ),
			'reused' => $reused,
		);
	}

	/**
	 * @param string $path Temp file to remove, if it is still there.
	 */
	private function discard( string $path ) {
		if ( $path && file_exists( $path ) ) {
			// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged, WordPress.WP.AlternativeFunctions.unlink_unlink
			@unlink( $path );
		}
	}
}
