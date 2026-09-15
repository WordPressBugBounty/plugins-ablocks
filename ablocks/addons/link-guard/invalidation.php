<?php

namespace ABlocksLinkGuard;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use ABlocks\Classes\CacheBackend;

/**
 * Keeps the stored link maps honest and tells caches when a page changed
 * because of *another* post.
 *
 * When a scheduled post publishes, a page cache purges that post, the home
 * page and its archives. It has no idea that last week's article linked to it
 * and is still being served with the link taken out. The reverse index written
 * by `Frontend` answers "who links here?", and this class cleans those posts'
 * object cache and fires `ablocks/link_guard/linking_posts_changed` so a cache
 * integration can purge exactly those URLs.
 */
class Invalidation {

	public static function init() {
		$self = new self();
		add_action( 'save_post', [ $self, 'forget_links' ] );
		add_action( 'transition_post_status', [ $self, 'status_changed' ], 10, 3 );
		add_action( 'post_updated', [ $self, 'slug_changed' ], 10, 3 );
		add_action( 'deleted_post', [ $self, 'deleted' ] );
	}

	/**
	 * The post's content may have changed, so its links are scanned afresh on
	 * the next render.
	 *
	 * @param int $post_id Saved post.
	 */
	public function forget_links( $post_id ) {
		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}
		delete_post_meta( $post_id, Frontend::META );
		delete_post_meta( $post_id, Frontend::TARGET_META );
	}

	/**
	 * @param string   $new_status New status.
	 * @param string   $old_status Previous status.
	 * @param \WP_Post $post       The post.
	 */
	public function status_changed( $new_status, $old_status, $post ) {
		if ( $new_status === $old_status || in_array( $new_status, [ 'auto-draft', 'inherit' ], true ) ) {
			return;
		}

		// A post that was not there before may be what a stored "not a post"
		// was waiting for.
		CacheBackend::bump_generation( ABLOCKS_LINK_GUARD_GENERATION_OPTION );

		if ( is_post_status_viewable( $new_status ) !== is_post_status_viewable( $old_status ) ) {
			self::announce( $post->ID );
		}
	}

	/**
	 * @param int      $post_id Post ID.
	 * @param \WP_Post $after   Post after the update.
	 * @param \WP_Post $before  Post before the update.
	 */
	public function slug_changed( $post_id, $after, $before ) {
		if ( $after->post_name !== $before->post_name ) {
			CacheBackend::bump_generation( ABLOCKS_LINK_GUARD_GENERATION_OPTION );
		}
	}

	/**
	 * A deleted target makes its links dead, the same as unpublishing it.
	 *
	 * @param int $post_id Deleted post.
	 */
	public function deleted( $post_id ) {
		self::announce( $post_id );
	}

	/**
	 * Posts whose content links to a post.
	 *
	 * @param int $target_id Linked post.
	 * @return int[]
	 */
	public static function linking_posts( $target_id ) {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$ids = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT DISTINCT post_id FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value = %s",
				Frontend::TARGET_META,
				(string) (int) $target_id
			)
		);

		return array_map( 'intval', $ids );
	}

	private static function announce( $target_id ) {
		$ids = self::linking_posts( $target_id );
		if ( ! $ids ) {
			return;
		}

		foreach ( $ids as $id ) {
			clean_post_cache( $id );
		}

		/**
		 * Posts whose rendered content changed because a post they link to was
		 * published, unpublished or deleted. Purge these from any page cache.
		 *
		 * @param int[] $post_ids  Linking posts.
		 * @param int   $target_id The post whose status changed.
		 */
		do_action( 'ablocks/link_guard/linking_posts_changed', $ids, (int) $target_id );
	}
}
