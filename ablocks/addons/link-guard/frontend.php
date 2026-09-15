<?php

namespace ABlocksLinkGuard;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use ABlocks\Classes\CacheBackend;

/**
 * Takes links to unpublished posts out of rendered content.
 *
 * ## What it costs
 *
 * Resolving a URL to a post is a rewrite-rule walk plus a query, so the answer
 * is stored on the post that contains the link (`META`), keyed by URL. Post
 * meta arrives with the post in the main query, so a warm render costs one
 * query at most — priming the linked posts it does not already have.
 *
 * Only the URL → ID mapping is stored, never "hidden or not". Whether a link
 * shows is decided on every render from the target's live status, so a stored
 * map can never keep a published post's link hidden.
 *
 * ## "Not a post" goes stale; "post #42" does not
 *
 * A positive answer is stable: post #42 stays post #42 whatever happens to its
 * status. A 0 is not — a writer may link to `/next-weeks-post/` before that
 * post exists. Each map records the generation it was built in, and a 0 from an
 * older generation is resolved again. `Invalidation` advances the generation
 * whenever a post appears or changes slug.
 */
class Frontend {

	/** URL → post ID map for the links inside a post. */
	const META = '_ablocks_link_guard';

	/** One row per post this post links to; the reverse index Invalidation reads. */
	const TARGET_META = '_ablocks_link_guard_target';

	/** Maps larger than this stop growing; everything still works, uncached. */
	const MAX_ENTRIES = 500;

	const MARKER = 'data-ablocks-link-guard';

	/**
	 * Resolutions made during this request, shared across content filters.
	 *
	 * @var array<string, int>
	 */
	private $memo = [];

	public static function init() {
		$self = new self();
		// Late: after blocks, shortcodes and other content filters have added
		// whatever links they add.
		add_filter( 'the_content', [ $self, 'filter_content' ], 99 );
	}

	public function filter_content( $content ) {
		if ( ! is_string( $content ) || false === stripos( $content, '<a' ) ) {
			return $content;
		}

		return $this->filter_html( $content, (int) get_the_ID() );
	}

	/**
	 * @param string $html    Rendered HTML.
	 * @param int    $post_id Post the HTML belongs to, where the lookup map is
	 *                        cached. 0 resolves without caching.
	 * @return string
	 */
	public function filter_html( $html, $post_id = 0 ) {
		$urls      = [];
		$processor = new \WP_HTML_Tag_Processor( $html );

		while ( $processor->next_tag( 'a' ) ) {
			$url = Resolver::normalize( $processor->get_attribute( 'href' ) );
			if ( '' !== $url ) {
				$urls[ $url ] = true;
			}
		}

		if ( ! $urls ) {
			return $html;
		}

		$ids = $this->lookup( array_keys( $urls ), $post_id );
		_prime_post_caches( array_values( array_unique( array_filter( $ids ) ) ), false, false );

		$hidden = [];
		foreach ( $ids as $url => $id ) {
			if ( $id && $id !== $post_id && ! self::is_linkable( $id ) ) {
				$hidden[ $url ] = $id;
			}
		}

		if ( ! $hidden ) {
			return $html;
		}

		$marked    = false;
		$processor = new \WP_HTML_Tag_Processor( $html );

		while ( $processor->next_tag( 'a' ) ) {
			$href = $processor->get_attribute( 'href' );
			$url  = Resolver::normalize( $href );

			if ( ! isset( $hidden[ $url ] ) ) {
				continue;
			}

			/**
			 * What to do with a link to a post visitors cannot see.
			 *
			 * - `unwrap` (default): drop the `<a>`, keep its contents, so the
			 *   text still shows. Button-styled links included: their styling
			 *   lives on the `<a>`, so the label shows as plain text.
			 * - `remove`: drop the link and its contents.
			 * - `keep`: leave the link alone.
			 *
			 * @param string $action    unwrap|remove|keep.
			 * @param int    $target_id The unpublished post.
			 * @param string $href      The link as written.
			 * @param int    $post_id   The post being rendered.
			 */
			$action = apply_filters( 'ablocks/link_guard/action', 'unwrap', $hidden[ $url ], $href, $post_id );

			if ( in_array( $action, [ 'unwrap', 'remove' ], true ) ) {
				$processor->set_attribute( self::MARKER, $action );
				$marked = true;
			}
		}//end while

		if ( ! $marked ) {
			return $html;
		}

		// The tag processor cannot delete a tag and keep its children, so it
		// marks the anchors and this removes them. Attribute values are matched
		// as whole quoted strings, so a `>` inside a title cannot end the tag
		// early. Anchors cannot nest, so the first `</a>` closes the one opened.
		$attr    = '(?:[^>"\']|"[^"]*"|\'[^\']*\')';
		$pattern = '#<a\b' . $attr . '*?\s' . self::MARKER . '="(unwrap|remove)"' . $attr . '*>(.*?)</a\s*>#is';

		$result = preg_replace_callback(
			$pattern,
			static function ( $match ) {
				return 'remove' === $match[1] ? '' : $match[2];
			},
			$processor->get_updated_html()
		);

		return null === $result ? $html : $result;
	}

	/**
	 * Whether visitors can open a post.
	 *
	 * @param int $post_id Target post.
	 * @return bool
	 */
	public static function is_linkable( $post_id ) {
		$post = get_post( $post_id );

		if ( ! $post ) {
			$linkable = false;
		} elseif ( 'attachment' === $post->post_type ) {
			// Attachments inherit their status, and an attachment page is not
			// what a writer is waiting to publish.
			$linkable = true;
		} else {
			$linkable = is_post_publicly_viewable( $post );
		}

		/**
		 * @param bool         $linkable Whether the link should be shown.
		 * @param int          $post_id  Target post ID.
		 * @param \WP_Post|null $post    Target post, null when it no longer exists.
		 */
		return (bool) apply_filters( 'ablocks/link_guard/is_linkable', $linkable, (int) $post_id, $post );
	}

	/**
	 * Post ID for each URL, from the request memo, the post's stored map, or a
	 * fresh resolve — in that order. Fresh answers are written back.
	 *
	 * @param string[] $urls    Normalized URLs.
	 * @param int      $post_id Owner of the stored map, or 0.
	 * @return array<string, int>
	 */
	private function lookup( array $urls, $post_id ) {
		$generation = CacheBackend::generation( ABLOCKS_LINK_GUARD_GENERATION_OPTION );
		$stored     = $post_id ? get_post_meta( $post_id, self::META, true ) : [];
		$map        = ( is_array( $stored ) && isset( $stored['map'] ) && is_array( $stored['map'] ) ) ? $stored['map'] : [];
		$fresh      = is_array( $stored ) && isset( $stored['generation'] ) && (int) $stored['generation'] === $generation;

		$result = [];
		$dirty  = false;

		foreach ( $urls as $url ) {
			if ( isset( $this->memo[ $url ] ) ) {
				$id = $this->memo[ $url ];
				$dirty = $dirty || ! isset( $map[ $url ] );
			} elseif ( isset( $map[ $url ] ) && ( (int) $map[ $url ] > 0 || $fresh ) ) {
				$id = (int) $map[ $url ];
			} else {
				$id    = Resolver::resolve( $url );
				$dirty = true;
			}

			$this->memo[ $url ] = $id;
			$result[ $url ]     = $id;
		}

		if ( $dirty && $post_id && ! wp_is_post_revision( $post_id ) ) {
			$this->store( $post_id, $map, $fresh, $result, $generation );
		}

		return $result;
	}

	private function store( $post_id, array $map, $fresh, array $result, $generation ) {
		if ( ! $fresh ) {
			// Old zeros that were not re-checked in this render would otherwise
			// be stamped with the current generation and trusted again.
			$map = array_filter( $map );
		}

		$map = array_merge( $map, $result );
		if ( count( $map ) > self::MAX_ENTRIES ) {
			return;
		}

		update_post_meta(
			$post_id,
			self::META,
			[
				'generation' => $generation,
				'map'        => $map,
			]
		);

		$indexed = array_map( 'intval', get_post_meta( $post_id, self::TARGET_META ) );
		foreach ( array_unique( array_filter( $result ) ) as $target_id ) {
			if ( $target_id !== $post_id && ! in_array( $target_id, $indexed, true ) ) {
				add_post_meta( $post_id, self::TARGET_META, $target_id );
			}
		}
	}
}
