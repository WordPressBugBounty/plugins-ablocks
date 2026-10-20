<?php
namespace ABlocks\Frontend\DynamicContent;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class Interpreter {

	protected array $interpreters = [
		'post-type' => Interpreters\PostType::class,
		'current'   => Interpreters\CurrentPost::class,
		'current-date-time' => Interpreters\CurrentDateTime::class,
		'site-title'   => Interpreters\SiteTitle::class,
		'site-tagline' => Interpreters\SiteTagline::class,
		'user-info'    => Interpreters\UserInfo::class,
		'request-parameter' => Interpreters\RequestParams::class,
		'shortcode' => Interpreters\Shortcode::class,
		'link'  => Interpreters\Link::class,
		'image' => Interpreters\Image::class,
	];
	protected string $content;
	protected array $context;
	public function __construct( string $content, $context = [] ) {
		$this->content = $content;
		$this->context = $context;
		$this->parse();// exit;
	}
	public function parse() : void {
		$source        = $this->content;
		// Matches arrive left to right, so the HTML context is tracked once
		// across the block rather than rescanned from the start for each tag.
		$scan          = [
			'pos'   => 0,
			'mode'  => 'text',
			'quote' => '',
		];
		$this->content = preg_replace_callback(
			'~ablocks_dc:(.+?):ablocks_dc|<span\s+(?=[^>]*\bclass=["\']ablocks-richtext-dynamic-content["\'])(?=[^>]*\bdata-source=["\']([^"\']+)["\'])(?=[^>]*\bdata-field=["\']([^"\']+)["\'])[^>]*>(.*?)<\/span>~ims',
			function ( $matches ) use ( $source, &$scan ) {
				// Offsets are captured only to answer one question — is this
				// tag inside a tag's attributes or in text — and are then
				// dropped, so apply_changes() sees the shape it always has.
				$offset = $matches[0][1];
				$plain  = array_map(
					function ( $match ) {
						return is_array( $match ) ? $match[0] : $match;
					},
					$matches
				);
				// Trailing groups that did not participate come back as
				// [ '', -1 ]; the non-offset call omitted them entirely.
				while ( count( $plain ) > 1 && '' === end( $plain ) && -1 === $matches[ count( $plain ) - 1 ][1] ) {
					array_pop( $plain );
				}
				$in_attribute = self::is_inside_tag( $source, $offset, $scan );
				$url_start    = $in_attribute && self::starts_url_attribute( $source, $offset );
				// A tag that is also in this request came from the visitor —
				// a search box, a page echoing a query arg — not the author.
				if ( self::is_reflected( $plain[0] ) ) {
					return self::neutralize( $plain[0] );
				}
				$output = $this->apply_changes( $plain, $in_attribute );
				// esc_attr() keeps `javascript:`; a tag that opens a URL
				// attribute decides its scheme, so drop unsafe ones.
				return $url_start ? wp_kses_bad_protocol( $output, wp_allowed_protocols() ) : $output;
			},
			$this->content,
			-1,
			$count,
			PREG_OFFSET_CAPTURE
		);
	}
	public function apply_changes( array $matches, bool $in_attribute = false ): string {
		$filtered = $this->filter( $matches ); // returns an array
		$args = array_merge( $filtered, [ $this->interpreters, $this->context ] );
		$parser = new ArgumentParser( ...$args );
		$ins = $parser->interpret();
		if ( is_null( $ins ) ) {
			return '';
		}
		$ins->set_in_attribute( $in_attribute );
		return self::neutralize( $ins->content() );
	}

	/**
	 * Whether a position in an HTML string falls inside a tag's attributes.
	 *
	 * Walks the HTML from where the previous call stopped, tracking quoted
	 * attribute values, so a `>` or `<` inside `title="a>b"` does not end or
	 * start a tag. Anything unclear counts as an attribute, the stricter of
	 * the two escapes.
	 *
	 * @param string $html   The block's HTML.
	 * @param int    $offset Byte offset of the tag; never less than the last call's.
	 * @param array  $state  Scanner state carried between calls.
	 * @return bool
	 */
	private static function is_inside_tag( string $html, int $offset, array &$state ) : bool {
		$i = $state['pos'];
		while ( $i < $offset ) {
			if ( 'comment' === $state['mode'] ) {
				$end = strpos( $html, '-->', $i );
				if ( false === $end || $end + 3 > $offset ) {
					$i = $offset;
					break;
				}
				$i             = $end + 3;
				$state['mode'] = 'text';
			} elseif ( 'tag' === $state['mode'] ) {
				$i += strcspn( $html, '' === $state['quote'] ? '"\'>' : $state['quote'], $i, $offset - $i );
				if ( $i >= $offset ) {
					break;
				}
				$char = $html[ $i ];
				if ( '' !== $state['quote'] ) {
					$state['quote'] = '';
				} elseif ( '>' === $char ) {
					$state['mode'] = 'text';
				} else {
					$state['quote'] = $char;
				}
				++$i;
			} else {
				$i += strcspn( $html, '<', $i, $offset - $i );
				if ( $i >= $offset ) {
					break;
				}
				if ( 0 === substr_compare( $html, '<!--', $i, 4 ) ) {
					$state['mode'] = 'comment';
					$i            += 4;
				} elseif ( isset( $html[ $i + 1 ] ) && preg_match( '~[a-z/!?]~i', $html[ $i + 1 ] ) ) {
					$state['mode'] = 'tag';
					++$i;
				} else {
					++$i;
				}
			}
		}//end while
		$state['pos'] = max( $state['pos'], $offset );
		return 'tag' === $state['mode'];
	}

	/**
	 * Whether the tag at $offset is the first thing in a URL attribute's
	 * value (href, src, …), where it would choose the URL's scheme.
	 *
	 * @param string $html   The block's HTML.
	 * @param int    $offset Byte offset of the tag.
	 * @return bool
	 */
	private static function starts_url_attribute( string $html, int $offset ) : bool {
		$before = substr( $html, max( 0, $offset - 64 ), min( $offset, 64 ) );
		return 1 === preg_match( '~(?:^|[\s"\'/])(?:href|src|action|formaction|poster|cite|background|data|xlink:href|srcset)\s*=\s*["\']?\s*$~i', $before );
	}

	/**
	 * Whether a matched tag also appears in the current request's input.
	 *
	 * A tag the author saved is never in the URL, form data or cookies of the
	 * page view that renders it; one that is was echoed back from the visitor.
	 * Both sides are reduced to lowercase letters and digits after undoing
	 * the usual encodings, so escaping, tag stripping or URL encoding on the
	 * way back to the page cannot hide the echo.
	 *
	 * @param string $match The full matched tag.
	 * @return bool
	 */
	private static function is_reflected( string $match ) : bool {
		$fingerprint = self::request_fingerprint();
		if ( '' === $fingerprint ) {
			return false;
		}
		$skeleton = self::skeleton( $match );
		return '' !== $skeleton && false !== strpos( $fingerprint, $skeleton );
	}

	/**
	 * The current request's input, reduced to skeletons, keeping only values
	 * that could carry a tag. Built once per request.
	 *
	 * @return string
	 */
	private static function request_fingerprint() : string {
		static $fingerprint = null;
		if ( null !== $fingerprint ) {
			return $fingerprint;
		}
		$fingerprint = '';
		// The editor's server-side preview sends the block's own attributes,
		// tags included. The route needs an authenticated, nonce-checked
		// author, so it cannot be used to reflect a visitor's text.
		if ( self::is_block_renderer_request() ) {
			return $fingerprint;
		}

		$values  = [];
		$collect = function ( $data ) use ( &$collect, &$values ) {
			foreach ( (array) $data as $key => $value ) {
				$values[] = (string) $key;
				if ( is_array( $value ) ) {
					$collect( $value );
				} elseif ( is_scalar( $value ) ) {
					$values[] = (string) $value;
				}
			}
		};
		// phpcs:disable WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput
		$collect( wp_unslash( $_GET ) );
		$collect( wp_unslash( $_POST ) );
		$collect( wp_unslash( $_COOKIE ) );
		$values[] = wp_unslash( $_SERVER['REQUEST_URI'] ?? '' );
		// phpcs:enable

		$skeletons = [];
		foreach ( $values as $value ) {
			if ( '' === $value ) {
				continue;
			}
			$decoded  = rawurldecode( $value );
			$variants = [ $value, $decoded, rawurldecode( $decoded ), urldecode( $value ) ];
			foreach ( $variants as $variant ) {
				// sanitize_text_field() and friends drop tags and %XX octets.
				foreach ( [ $variant, wp_strip_all_tags( $variant ), preg_replace( '/%[a-f0-9]{2}/i', '', $variant ) ] as $form ) {
					$skeleton = self::skeleton( (string) $form );
					if ( false !== strpos( $skeleton, 'ablocksdc' ) || false !== strpos( $skeleton, 'ablocksrichtextdynamiccontent' ) ) {
						$skeletons[ $skeleton ] = true;
					}
				}
			}
		}
		$fingerprint = implode( '|', array_keys( $skeletons ) );
		return $fingerprint;
	}

	/**
	 * Lowercase letters and digits only, after decoding HTML entities.
	 *
	 * @param string $text Text.
	 * @return string
	 */
	private static function skeleton( string $text ) : string {
		$text = html_entity_decode( $text, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
		return (string) preg_replace( '/[^a-z0-9]/', '', strtolower( $text ) );
	}

	/**
	 * Whether this is the editor's block-renderer REST request.
	 *
	 * @return bool
	 */
	private static function is_block_renderer_request() : bool {
		if ( ! defined( 'REST_REQUEST' ) || ! REST_REQUEST || ! current_user_can( 'edit_posts' ) ) {
			return false;
		}
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$uri = rawurldecode( $_SERVER['REQUEST_URI'] ?? '' );
		return false !== strpos( $uri, '/wp/v2/block-renderer/' ) || false !== strpos( $uri, '/wp-block-editor/v1/' );
	}

	/**
	 * Make a string unable to carry a dynamic-content tag.
	 *
	 * The filter below runs on every block, wrappers included, so whatever one
	 * tag outputs is scanned again by the block around it. Text that is not the
	 * site author's — a URL parameter, a comment, a user's display name — must
	 * never reach that scan as a tag, or a visitor could make the page run a
	 * shortcode or print something of their choosing. Renders identically;
	 * only the parser stops recognising it.
	 *
	 * @param string $text Untrusted text.
	 * @return string
	 */
	public static function neutralize( $text ) {
		if ( ! is_string( $text ) || ( false === stripos( $text, 'ablocks_dc' ) && false === stripos( $text, 'ablocks-richtext-dynamic-content' ) ) ) {
			return $text;
		}
		return str_ireplace(
			[ 'ablocks_dc', 'ablocks-richtext-dynamic-content' ],
			[ 'ablocks&#95;dc', 'ablocks-richtext-dynamic&#45;content' ],
			$text
		);
	}

	/**
	 * The same for a URL: the underscore is percent-encoded, which names the
	 * same address, because an HTML entity would be mangled by esc_url().
	 *
	 * @param string $url Untrusted URL.
	 * @return string
	 */
	public static function neutralize_url( $url ) {
		if ( ! is_string( $url ) || false === stripos( $url, 'ablocks_dc' ) ) {
			return $url;
		}
		return str_ireplace( 'ablocks_dc', 'ablocks%5Fdc', $url );
	}

	/**
	 * Visitor- and user-written text that core blocks render. Text echoed
	 * from the request itself is caught by is_reflected().
	 */
	public static function protect_untrusted_text() : void {
		$text = [
			'comment_text',
			'comment_excerpt',
			'get_comment_author',
			'the_author',
			// get_the_author_meta(), which the core post-author blocks use.
			'get_the_author_display_name',
			'get_the_author_description',
			'get_the_author_user_description',
			'get_the_author_first_name',
			'get_the_author_last_name',
			'get_the_author_nickname',
		];
		foreach ( $text as $hook ) {
			add_filter( $hook, [ self::class, 'neutralize' ], 999 );
		}
		foreach ( [ 'get_comment_author_url', 'get_the_author_user_url' ] as $hook ) {
			add_filter( $hook, [ self::class, 'neutralize_url' ], 999 );
		}
		// Some core blocks (Avatar) read the comment's properties directly,
		// past every filter above.
		add_filter( 'get_comment', [ self::class, 'neutralize_comment' ], 999 );
	}

	/**
	 * Neutralise a comment's visitor-written fields where it is loaded.
	 *
	 * @param mixed $comment Comment object.
	 * @return mixed
	 */
	public static function neutralize_comment( $comment ) {
		if ( $comment instanceof \WP_Comment ) {
			$comment->comment_author     = self::neutralize( $comment->comment_author );
			$comment->comment_author_url = self::neutralize_url( $comment->comment_author_url );
			$comment->comment_content    = self::neutralize( $comment->comment_content );
		}
		return $comment;
	}

	public function filter( array $matches ) : array {
		$is_richtext = false;
		array_shift( $matches );
		if ( count( $matches ) > 2 ) {
			array_shift( $matches );
			$is_richtext = true;
		}
		return [
			implode( '|', $matches ),
			$is_richtext
		];
	}

	public static function init( string $content, $block, $instance ) : string {
		// This filter runs for EVERY block on the page (including core and
		// third-party blocks). The regex in parse() is expensive, so skip it
		// unless the block actually contains a dynamic-content marker.
		if ( false === strpos( $content, 'ablocks_dc' )
			&& false === strpos( $content, 'ablocks-richtext-dynamic-content' ) ) {
			return $content;
		}
		$ins = new self( $content, $instance->context );
		return $ins->content;
	}
}
