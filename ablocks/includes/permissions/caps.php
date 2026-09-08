<?php
/**
 * The capability bridge.
 *
 * Turns the stored permission map into capabilities WordPress can actually
 * check, for the length of one request. Nothing here calls add_cap(): revoking
 * a permission takes effect on the next page load, and deactivating aBlocks
 * leaves every user with exactly the capabilities they had before.
 *
 * @package ABlocks
 */

namespace ABlocks\Permissions;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ABlocks\Permissions;
use WP_User;

class Caps {

	/**
	 * Capabilities WordPress itself gates on, granted alongside one of ours.
	 *
	 * Only edit_theme_options, and only for the Site Editor permission. This is
	 * the one place the module reaches outside its own namespace, so the list
	 * stays short and obvious: a broad grant here is how a permission system
	 * turns into a privilege-escalation bug.
	 *
	 * @return array
	 */
	public static function native_bridge() {
		return apply_filters('ablocks/permissions/native_bridge', [
			'ablocks_access_site_editor' => [ 'edit_theme_options' ],
		]);
	}

	/**
	 * Per-user cache of the capabilities we add. Cleared when the map changes.
	 *
	 * @var array
	 */
	private static $cache = [];

	/**
	 * Reentrancy guard. Everything Permissions::for_user() touches reads roles
	 * and meta directly, but a filter added by another plugin might not, and a
	 * capability check inside a capability filter would recurse forever.
	 *
	 * @var bool
	 */
	private static $resolving = false;

	public static function init() {
		$self = new self();

		add_filter( 'user_has_cap', [ $self, 'grant_capabilities' ], 10, 4 );
		add_filter( 'map_meta_cap', [ $self, 'guard_theme_builder_templates' ], 10, 4 );

		// The map changing has to invalidate the request-local cache, or a save
		// followed by a check in the same request answers with the old map.
		add_action( 'ablocks/permissions/changed', [ __CLASS__, 'flush_cache' ] );
	}

	public static function flush_cache() {
		self::$cache = [];
	}

	/**
	 * @param array   $allcaps
	 * @param array   $caps
	 * @param array   $args
	 * @param WP_User $user
	 *
	 * @return array
	 */
	public function grant_capabilities( $allcaps, $caps, $args, $user ) {
		if ( self::$resolving || ! $user instanceof WP_User || ! $user->ID ) {
			return $allcaps;
		}

		// Administrators are NOT short-circuited here. They hold every aBlocks
		// capability, but holding manage_options does not by itself answer
		// current_user_can( 'ablocks_access' ) — these are our own capability
		// strings, and nothing in WordPress knows an administrator should pass
		// them. Skipping the merge for administrators hid the entire aBlocks
		// menu from the very people who own the site. The per-user cache below
		// keeps the repeated cost down instead.
		$granted = self::granted_for( $user );

		if ( empty( $granted ) ) {
			return $allcaps;
		}

		// Union, never overwrite: a capability WordPress already answered false
		// for stays false unless this module explicitly grants it.
		return $allcaps + $granted;
	}

	/**
	 * The capability array to merge in for a user.
	 *
	 * @param WP_User $user
	 *
	 * @return array
	 */
	private static function granted_for( WP_User $user ) {
		if ( isset( self::$cache[ $user->ID ] ) ) {
			return self::$cache[ $user->ID ];
		}

		self::$resolving = true;
		$grants          = Permissions::for_user( $user );
		self::$resolving = false;

		$granted = [];

		foreach ( $grants as $slug ) {
			$granted[ $slug ] = true;
		}

		foreach ( Permissions::derived_capabilities() as $derived => $implied_by ) {
			if ( array_intersect( $implied_by, $grants ) ) {
				$granted[ $derived ] = true;
			}
		}

		$bridge = self::native_bridge();
		foreach ( $bridge as $slug => $native_caps ) {
			if ( empty( $granted[ $slug ] ) ) {
				continue;
			}
			foreach ( (array) $native_caps as $native_cap ) {
				$granted[ $native_cap ] = true;
			}
		}

		self::$cache[ $user->ID ] = $granted;

		return $granted;
	}

	/**
	 * Keep Theme Builder templates behind the Theme Builder permission.
	 *
	 * The ablocks_tb post type is registered with capability_type => post, so
	 * anybody who can edit posts can edit a site's header and footer. That was
	 * true before this module existed; now that Theme Builder is a permission,
	 * it should mean something. Denying here rather than changing the post
	 * type's capability_type keeps existing sites working with no migration and
	 * no capabilities written to the database.
	 *
	 * @param array  $caps
	 * @param string $cap
	 * @param int    $user_id
	 * @param array  $args
	 *
	 * @return array
	 */
	public function guard_theme_builder_templates( $caps, $cap, $user_id, $args ) {
		static $guarded = [ 'edit_post', 'delete_post', 'publish_post' ];

		if ( ! in_array( $cap, $guarded, true ) || empty( $args[0] ) ) {
			return $caps;
		}

		if ( 'ablocks_tb' !== get_post_type( $args[0] ) ) {
			return $caps;
		}

		if ( Permissions::is_real_admin( $user_id ) || Permissions::user_can( 'ablocks_manage_theme_builder', $user_id ) ) {
			return $caps;
		}

		return [ 'do_not_allow' ];
	}
}
