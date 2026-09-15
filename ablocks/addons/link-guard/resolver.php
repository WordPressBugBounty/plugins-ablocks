<?php

namespace ABlocksLinkGuard;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Maps an `href` to the post it points at.
 *
 * `url_to_postid()` understands every permalink structure, custom post type
 * and page hierarchy, but it answers the question "can the *current visitor*
 * see this?" rather than "which post is this?". A scheduled post resolves for
 * an editor and resolves to 0 for everyone else, because `WP_Query` drops
 * unpublished singular results after fetching them. A cache built on that
 * answer would depend on who happened to load the page first.
 *
 * The fetch happens before the drop, and `posts_results` fires in between. So
 * the resolver listens there for the duration of one `url_to_postid()` call and
 * keeps the post it saw, which makes the answer the same for every viewer.
 */
class Resolver {

	/**
	 * Post seen by the singular query during the current resolve() call.
	 *
	 * @var int
	 */
	private static $captured = 0;

	/**
	 * Slug the singular query asked for during the current resolve() call.
	 *
	 * @var string
	 */
	private static $slug = '';

	/**
	 * Reduce an href to a canonical same-site URL, or '' when it cannot be a
	 * link to a post (another site, an anchor, mailto:, an upload, wp-admin...).
	 *
	 * The result is also the cache key, so two spellings of the same link —
	 * relative and absolute, with or without `www.`, a `#section` or a
	 * tracking query string — collapse to one entry.
	 *
	 * @param string $href Raw attribute value.
	 * @return string
	 */
	public static function normalize( $href ) {
		$href = trim( (string) $href );

		if ( '' === $href || '#' === $href[0] ) {
			return '';
		}

		if ( 0 === strpos( $href, '//' ) ) {
			$href = 'https:' . $href;
		}

		$parts = wp_parse_url( $href );
		if ( false === $parts ) {
			return '';
		}

		if ( isset( $parts['scheme'] ) && ! in_array( strtolower( $parts['scheme'] ), [ 'http', 'https' ], true ) ) {
			return '';
		}

		$home = wp_parse_url( home_url() );

		if ( isset( $parts['host'] ) ) {
			if ( self::bare_host( $parts['host'] ) !== self::bare_host( isset( $home['host'] ) ? $home['host'] : '' ) ) {
				return '';
			}
		} elseif ( empty( $parts['path'] ) || '/' !== $parts['path'][0] ) {
			// Document-relative links ("next-post/", "?p=3") depend on the page
			// they sit on; guessing their target could hide a working link.
			return '';
		}

		$path      = isset( $parts['path'] ) ? $parts['path'] : '/';
		$home_path = isset( $home['path'] ) ? rtrim( $home['path'], '/' ) : '';

		if ( '' !== $home_path && 0 !== strpos( $path, $home_path ) ) {
			return '';
		}

		$relative = '/' . ltrim( substr( $path, strlen( $home_path ) ), '/' );

		if ( preg_match( '#^/(?:wp-admin|wp-content|wp-includes|wp-json)(?:/|$)|^/wp-[a-z-]+\.php#i', $relative ) ) {
			return '';
		}

		// A file: an upload, a sitemap, a download. Never a post.
		if ( preg_match( '#\.(?!html?$|php$)[a-z0-9]{2,5}$#i', $relative ) ) {
			return '';
		}

		// Keep the query string only when it is what identifies the post.
		$query = '';
		if ( ! empty( $parts['query'] ) && preg_match( '#(?:^|&)(p|page_id|preview_id)=(\d+)#', $parts['query'], $match ) ) {
			$query = '?' . ( 'page_id' === $match[1] ? 'page_id' : 'p' ) . '=' . $match[2];
		}

		return untrailingslashit( home_url( $relative ) ) . $query;
	}

	/**
	 * Post ID a normalized URL points at, whatever that post's status. 0 when
	 * the URL is not a single post (an archive, a custom route, nothing).
	 *
	 * @param string $url Output of normalize().
	 * @return int
	 */
	public static function resolve( $url ) {
		if ( preg_match( '#\?(?:p|page_id)=(\d+)$#', $url, $match ) ) {
			return (int) $match[1];
		}

		self::$captured = 0;
		self::$slug     = '';
		add_action( 'parse_query', [ __CLASS__, 'capture_slug' ], PHP_INT_MAX );
		add_filter( 'posts_results', [ __CLASS__, 'capture' ], PHP_INT_MAX, 2 );

		try {
			$id = (int) url_to_postid( trailingslashit( $url ) );
		} finally {
			remove_action( 'parse_query', [ __CLASS__, 'capture_slug' ], PHP_INT_MAX );
			remove_filter( 'posts_results', [ __CLASS__, 'capture' ], PHP_INT_MAX );
		}

		if ( $id || self::$captured ) {
			return $id ? $id : self::$captured;
		}

		return self::trashed( self::$slug );
	}

	/**
	 * Trashing a post renames its slug to `{slug}__trashed`, so its old URL
	 * stops matching anything and would otherwise look like a link to nowhere.
	 *
	 * @param string $slug Slug the URL asked for.
	 * @return int
	 */
	private static function trashed( $slug ) {
		if ( '' === $slug ) {
			return 0;
		}

		$ids = get_posts(
			[
				'name'             => $slug . '__trashed',
				'post_type'        => 'any',
				'post_status'      => 'trash',
				'fields'           => 'ids',
				'posts_per_page'   => 1,
				'suppress_filters' => true,
				'no_found_rows'    => true,
			]
		);

		return $ids ? (int) $ids[0] : 0;
	}

	/**
	 * @internal Hooked only for the duration of resolve().
	 *
	 * @param \WP_Query $query The query url_to_postid() is about to run.
	 */
	public static function capture_slug( $query ) {
		if ( '' !== self::$slug || ! $query->is_singular() ) {
			return;
		}
		$slug       = $query->get( 'name' ) ? $query->get( 'name' ) : $query->get( 'pagename' );
		self::$slug = $slug ? sanitize_title( basename( $slug ) ) : '';
	}

	/**
	 * @internal Hooked only for the duration of resolve().
	 *
	 * @param \WP_Post[] $posts Raw query results.
	 * @param \WP_Query  $query The query.
	 * @return \WP_Post[]
	 */
	public static function capture( $posts, $query ) {
		if ( ! self::$captured && ! empty( $posts ) && $query->is_singular() ) {
			self::$captured = (int) ( is_object( $posts[0] ) ? $posts[0]->ID : $posts[0] );
		}
		return $posts;
	}

	private static function bare_host( $host ) {
		return preg_replace( '#^www\.#', '', strtolower( $host ) );
	}
}
