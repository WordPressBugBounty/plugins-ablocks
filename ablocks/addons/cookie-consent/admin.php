<?php
namespace ABlocksCookieConsent;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The addon's place in the aBlocks admin.
 *
 * One submenu entry, appearing only while the addon is on, plus the data the
 * React screen needs at boot so it does not have to make a request before it
 * can render anything.
 */
class Admin {

	public static function init() {
		$self = new self();
		add_filter( 'ablocks/admin_menu_list', [ $self, 'admin_menu' ] );
		add_filter( 'ablocks/assets/dashboard_scripts_data', [ $self, 'dashboard_data' ] );
	}

	/**
	 * Insert the screen directly after Theme Builder, so the two addon screens
	 * sit together rather than one of them landing under Settings.
	 *
	 * @param array $menu Menu registry.
	 * @return array
	 */
	public function admin_menu( $menu ) {
		$entry = [
			ABLOCKS_PLUGIN_SLUG . '-cookie-consent' => [
				'parent_slug' => ABLOCKS_PLUGIN_SLUG,
				'title'       => __( 'Cookie Consent', 'ablocks' ),
				'capability'  => 'manage_options',
			],
		];

		$anchor = ABLOCKS_PLUGIN_SLUG . '-theme-builder';
		if ( ! isset( $menu[ $anchor ] ) ) {
			$anchor = ABLOCKS_PLUGIN_SLUG . '-addons';
		}
		if ( ! isset( $menu[ $anchor ] ) ) {
			return array_merge( $menu, $entry );
		}

		$position = array_search( $anchor, array_keys( $menu ), true ) + 1;

		return array_merge(
			array_slice( $menu, 0, $position, true ),
			$entry,
			array_slice( $menu, $position, null, true )
		);
	}

	/**
	 * @param array $data Localised dashboard data.
	 * @return array
	 */
	public function dashboard_data( $data ) {
		$data['cookie_consent'] = [
			'defaults'        => Helper::defaults(),
			'settings'        => Helper::get_settings(),
			'consent_signals' => ConsentMode::signal_map(),
			'pro'             => Helper::pro_features(),
			'record_count'    => Record::count(),
			'table_ready'     => Database::table_exists(),
			// Still needed by the notice that asks a site to move a tag it
			// entered before aBlocks stopped placing them.
			'tag_providers'   => Tags::detected_providers(),
			'embed_defaults'  => [
				'embed' => Embeds::default_rules(),
				'pixel' => Embeds::default_pixel_rules(),
			],
		];
		return $data;
	}
}
