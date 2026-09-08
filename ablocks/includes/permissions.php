<?php
/**
 * The aBlocks capability layer.
 *
 * Permission slugs ARE WordPress capability strings, so every call site in the
 * plugin is a plain current_user_can( 'ablocks_…' ). Nothing is ever written to
 * the database with add_cap(); Permissions\Caps grants them for the duration of
 * a request through the `user_has_cap` filter, so revoking is immediate and
 * deactivating aBlocks leaves a user with exactly the caps they started with.
 *
 * This half — the vocabulary and the bridge — is all aBlocks itself needs. On
 * its own it hands out one fixed arrangement: an administrator holds
 * everything, a role that can edit posts gets the editing capabilities, and
 * nobody else reaches an aBlocks screen. That is exactly what the plugin did
 * before capabilities had names, so installing or updating changes nothing.
 *
 * Configuring it — per role, per user, with presets and a screen to do it on —
 * is aBlocks Pro. Pro supplies the stored map through
 * `ablocks/permissions/role_grants` and `ablocks/permissions/user_grants`; see
 * docs/ROLE-PERMISSIONS.md.
 *
 * @package ABlocks
 */

namespace ABlocks;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WP_User;

class Permissions {

	/**
	 * Reaching any aBlocks admin screen. Derived — anyone holding at least one
	 * other capability holds this one too, so the top-level menu can be gated
	 * without enumerating every permission at the call site.
	 */
	const ACCESS = 'ablocks_access';

	/**
	 * Saving anything on the Settings screen. Derived from the three groups the
	 * settings blob is partitioned into — the endpoint accepts the request and
	 * SettingsGuard decides, key by key, which groups the user may actually
	 * change.
	 */
	const SAVE_SETTINGS = 'ablocks_save_settings';

	public static function init() {
		Permissions\Caps::init();
	}

	/**
	 * Capabilities that unlock an aBlocks admin screen.
	 *
	 * Editing capabilities are absent on purpose: someone who may style blocks
	 * has no reason to see the aBlocks menu, and showing them a top-level menu
	 * whose every child is hidden is worse than showing nothing.
	 *
	 * @return string[]
	 */
	public static function screen_capabilities() {
		return [
			'ablocks_manage_settings',
			'ablocks_manage_global_styles',
			'ablocks_manage_performance',
			'ablocks_manage_forms',
			'ablocks_view_submissions',
			'ablocks_run_scanner',
			'ablocks_manage_addons',
			'ablocks_manage_theme_builder',
			'ablocks_import_templates',
		];
	}

	/**
	 * Capabilities computed from other capabilities.
	 *
	 * Not in the catalogue and not configurable — they exist so a call site can
	 * ask one question instead of three.
	 *
	 * @return array Derived capability => the capabilities that imply it.
	 */
	public static function derived_capabilities() {
		return [
			self::ACCESS        => self::screen_capabilities(),
			self::SAVE_SETTINGS => [
				'ablocks_manage_settings',
				'ablocks_manage_global_styles',
				'ablocks_manage_performance',
			],
		];
	}

	/**
	 * Every capability this module hands out, grouped for the admin screen.
	 *
	 * @return array
	 */
	public static function catalogue() {
		return apply_filters('ablocks/permissions/catalogue', [
			'editing'  => [
				'label'       => __( 'Editing', 'ablocks' ),
				'permissions' => [
					'ablocks_use_editor'       => [
						'label'       => __( 'Use aBlocks blocks', 'ablocks' ),
						'description' => __( 'Insert and edit aBlocks blocks in the post editor.', 'ablocks' ),
					],
					'ablocks_edit_style'       => [
						'label'       => __( 'Change styling', 'ablocks' ),
						'description' => __( 'Colors, typography, backgrounds and borders. Without this a user gets content controls only.', 'ablocks' ),
					],
					'ablocks_edit_advanced'    => [
						'label'       => __( 'Advanced settings', 'ablocks' ),
						'description' => __( 'Spacing, position, responsive visibility and animation.', 'ablocks' ),
					],
					'ablocks_edit_custom_css'  => [
						'label'       => __( 'Custom CSS & attributes', 'ablocks' ),
						'description' => __( 'Separate from advanced settings on purpose — this one injects code into the page.', 'ablocks' ),
					],
					'ablocks_copy_paste_style' => [
						'label'       => __( 'Copy & paste styles', 'ablocks' ),
						'description' => __( 'Copy styles between blocks, and paste styles from Figma.', 'ablocks' ),
					],
				],
			],
			'design'   => [
				'label'       => __( 'Site design', 'ablocks' ),
				'permissions' => [
					'ablocks_manage_global_styles' => [
						'label'       => __( 'Manage the design system', 'ablocks' ),
						'description' => __( 'Global colors, typography presets and container defaults — these affect every page.', 'ablocks' ),
					],
					'ablocks_manage_theme_builder' => [
						'label'       => __( 'Theme Builder', 'ablocks' ),
						'description' => __( 'Build and assign headers, footers and other site-wide layouts.', 'ablocks' ),
					],
					'ablocks_access_site_editor'   => [
						'label'       => __( 'WordPress Site Editor', 'ablocks' ),
						'description' => __( 'Templates, template parts, menus and widgets. Grants the WordPress edit_theme_options capability.', 'ablocks' ),
					],
					'ablocks_import_templates'     => [
						'label'       => __( 'Import templates & demos', 'ablocks' ),
						'description' => __( 'Template kits and demo import create posts and media in bulk.', 'ablocks' ),
					],
				],
			],
			'admin'    => [
				'label'       => __( 'aBlocks screens', 'ablocks' ),
				'permissions' => [
					'ablocks_manage_settings'    => [
						'label'       => __( 'Settings', 'ablocks' ),
						'description' => __( 'Editor options, page setup, visibility and integrations.', 'ablocks' ),
					],
					'ablocks_manage_performance' => [
						'label'       => __( 'Performance suite', 'ablocks' ),
						'description' => __( 'Caching, asset generation and image tools. A wrong toggle here affects every visitor.', 'ablocks' ),
					],
					'ablocks_manage_forms'       => [
						'label'       => __( 'Form Builder', 'ablocks' ),
						'description' => __( 'Build forms and edit their settings.', 'ablocks' ),
					],
					'ablocks_view_submissions'   => [
						'label'       => __( 'Form submissions', 'ablocks' ),
						'description' => __( 'Read and export what visitors submitted. This is personal data — grant it deliberately.', 'ablocks' ),
					],
					'ablocks_run_scanner'        => [
						'label'       => __( 'Site Scanner', 'ablocks' ),
						'description' => __( 'Run the site scan and read its report.', 'ablocks' ),
					],
					'ablocks_manage_addons'      => [
						'label'       => __( 'Add-ons', 'ablocks' ),
						'description' => __( 'Turn add-ons on and off. Only ever restricts — this screen installs plugins, so it always requires the WordPress plugin-installation capability as well.', 'ablocks' ),
					],
				],
			],
		]);
	}

	/**
	 * Flat list of every capability slug.
	 *
	 * @return string[]
	 */
	public static function all_slugs() {
		static $slugs = null;
		if ( null === $slugs ) {
			$slugs = [];
			foreach ( self::catalogue() as $group ) {
				$slugs = array_merge( $slugs, array_keys( $group['permissions'] ) );
			}
		}
		return $slugs;
	}

	public static function is_valid_slug( $slug ) {
		return in_array( $slug, self::all_slugs(), true );
	}

	/**
	 * Capabilities that can never be granted by this module, only taken away.
	 *
	 * The add-ons screen installs plugins (Ajax\Dashboard::install_plugin), and a
	 * permission system that can hand out plugin installation can hand out
	 * everything. So this permission gates the screen for people who already
	 * hold the WordPress capability, and does nothing for anyone else.
	 *
	 * @return array Slug => the WordPress capability the user must already hold.
	 */
	public static function requires_native_cap() {
		return [
			'ablocks_manage_addons' => 'install_plugins',
		];
	}

	/**
	 * Everything the block editor itself is gated on.
	 *
	 * The set a role that can edit posts holds by default, and the building
	 * block Pro's presets start from.
	 *
	 * @return string[]
	 */
	public static function editing_capabilities() {
		return [
			'ablocks_use_editor',
			'ablocks_edit_style',
			'ablocks_edit_advanced',
			'ablocks_edit_custom_css',
			'ablocks_copy_paste_style',
		];
	}

	/**
	 * Sort + filter a grants list so two equivalent lists compare equal.
	 *
	 * @param array $grants
	 *
	 * @return string[]
	 */
	public static function normalize( array $grants ) {
		$grants = array_values( array_unique( array_filter( $grants, [ __CLASS__, 'is_valid_slug' ] ) ) );
		sort( $grants );
		return $grants;
	}

	/**
	 * What a role gets when nothing has configured it.
	 *
	 * Installing or updating aBlocks must not change what anybody can do. Any
	 * role that can edit posts could already use every aBlocks block and
	 * control, and no role but administrator could reach an aBlocks screen. That
	 * is exactly what this returns.
	 *
	 * @param string $role_slug
	 *
	 * @return string[]
	 */
	public static function default_grants_for_role( $role_slug ) {
		$role = get_role( $role_slug );

		$grants = ( $role && ! empty( $role->capabilities['edit_posts'] ) )
			? self::editing_capabilities()
			: [];

		return apply_filters( 'ablocks/permissions/default_role_grants', $grants, $role_slug );
	}

	/**
	 * The capabilities a role holds.
	 *
	 * The filter is where aBlocks Pro returns the map a site configured on the
	 * Roles & Permissions screen. It may return an empty array — "this role gets
	 * nothing" has to be expressible — so a filtered value replaces the default
	 * rather than adding to it.
	 *
	 * @param string $role_slug
	 *
	 * @return string[]
	 */
	public static function get_role_grants( $role_slug ) {
		$grants = apply_filters(
			'ablocks/permissions/role_grants',
			self::default_grants_for_role( $role_slug ),
			$role_slug
		);

		return self::normalize( (array) $grants );
	}

	/**
	 * Every capability a user effectively holds.
	 *
	 * Deliberately reads roles and meta directly and never calls user_can().
	 * Permissions\Caps calls this from inside the `user_has_cap` filter, so a
	 * capability check in here would recurse.
	 *
	 * @param WP_User|int|null $user
	 *
	 * @return string[]
	 */
	public static function for_user( $user = null ) {
		$user = self::resolve_user( $user );

		if ( ! $user || ! $user->exists() ) {
			return [];
		}

		if ( self::is_real_admin( $user ) ) {
			return self::all_slugs();
		}

		$grants = [];
		foreach ( (array) $user->roles as $role_slug ) {
			$grants = array_merge( $grants, self::get_role_grants( $role_slug ) );
		}

		// Where aBlocks Pro applies a per-user override. It replaces the union of
		// the user's roles rather than adding to it, because an override has to be
		// able to take something away as well as give it.
		$grants = apply_filters( 'ablocks/permissions/user_grants', self::normalize( $grants ), $user );
		$grants = self::normalize( (array) $grants );

		// Permissions that can only ever restrict. Holding one without the
		// underlying WordPress capability means nothing. Applied after the filter
		// so an override cannot route around it either.
		foreach ( self::requires_native_cap() as $slug => $native ) {
			if ( in_array( $slug, $grants, true ) && empty( $user->allcaps[ $native ] ) ) {
				$grants = array_values( array_diff( $grants, [ $slug ] ) );
			}
		}

		return $grants;
	}

	/**
	 * Whether a user holds a capability.
	 *
	 * Everywhere except inside the `user_has_cap` filter itself, prefer plain
	 * current_user_can( 'ablocks_…' ) — Caps makes that work.
	 *
	 * @param string           $slug
	 * @param WP_User|int|null $user
	 *
	 * @return bool
	 */
	public static function user_can( $slug, $user = null ) {
		$grants  = self::for_user( $user );
		$derived = self::derived_capabilities();

		if ( isset( $derived[ $slug ] ) ) {
			return ! empty( array_intersect( $derived[ $slug ], $grants ) );
		}

		return in_array( $slug, $grants, true );
	}

	/**
	 * A real administrator, as opposed to somebody this module elevated.
	 *
	 * Reads the raw capability array rather than user_can(), because the
	 * `user_has_cap` filter can change what user_can() returns and anything that
	 * decides who may edit the permission map has to be immune to the thing it
	 * configures. Every permission-management endpoint gates on this.
	 *
	 * @param WP_User|int|null $user
	 *
	 * @return bool
	 */
	public static function is_real_admin( $user = null ) {
		$user = self::resolve_user( $user );

		if ( ! $user || ! $user->exists() ) {
			return false;
		}

		if ( is_multisite() && is_super_admin( $user->ID ) ) {
			return true;
		}

		return ! empty( $user->allcaps['manage_options'] );
	}

	/**
	 * @param WP_User|int|null $user
	 *
	 * @return WP_User|null
	 */
	private static function resolve_user( $user = null ) {
		if ( $user instanceof WP_User ) {
			return $user;
		}

		if ( is_numeric( $user ) && $user > 0 ) {
			$resolved = get_user_by( 'id', (int) $user );
			return $resolved ?: null;
		}

		$current = wp_get_current_user();

		return $current instanceof WP_User ? $current : null;
	}
}
