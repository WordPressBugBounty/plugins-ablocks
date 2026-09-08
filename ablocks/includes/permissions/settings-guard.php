<?php
/**
 * Field-level enforcement for the Settings screen.
 *
 * Ajax\Settings::save_settings takes the whole settings blob in one request, so
 * a single capability on the endpoint would mean "design system" and "site
 * configuration" can never be separate permissions. This partitions the payload
 * instead: keys the user may not change are replaced with what is already
 * saved, so the save succeeds and simply does not move them.
 *
 * Replacing with the *saved* value matters. save_settings falls back to
 * defaults for any missing key, so dropping a key would silently reset it.
 *
 * @package ABlocks
 */

namespace ABlocks\Permissions;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ABlocks\Admin\Settings\Base as BaseSettings;
use ABlocks\Permissions;

class SettingsGuard {

	/**
	 * Which capability owns which settings keys.
	 *
	 * Anything not matched here belongs to ablocks_manage_settings — the safe
	 * default, since that is the most restrictive of the three and new keys
	 * should not become editable by a designer without somebody deciding so.
	 *
	 * @return array
	 */
	public static function key_map() {
		return apply_filters('ablocks/permissions/settings_key_map', [
			'ablocks_manage_global_styles' => [
				'prefixes' => [ 'global_', 'lock_global_' ],
				'keys'     => [
					'default_container_width',
					'container_padding',
					'container_element_gap',
					'enabled_block_copy_paste_style',
					'enabled_load_google_font_locally',
					'enabled_only_selected_fonts',
					'selected_fonts',
					'font_metric_fallback',
				],
			],
			'ablocks_manage_performance'   => [
				'prefixes' => [ 'perf_' ],
				'keys'     => [
					'enabled_assets_file_generation',
				],
			],
		]);
	}

	/**
	 * The capability required to change a given settings key.
	 *
	 * @param string $key
	 *
	 * @return string
	 */
	public static function capability_for_key( $key ) {
		foreach ( self::key_map() as $capability => $match ) {
			if ( in_array( $key, $match['keys'], true ) ) {
				return $capability;
			}
			foreach ( $match['prefixes'] as $prefix ) {
				if ( 0 === strpos( $key, $prefix ) ) {
					return $capability;
				}
			}
		}

		return 'ablocks_manage_settings';
	}

	/**
	 * Replace values the current user may not change with the saved ones.
	 *
	 * @param array $payload
	 *
	 * @return array
	 */
	public static function filter_payload( array $payload ) {
		// Real administrators, and anyone holding all three, change everything.
		if ( Permissions::is_real_admin() ) {
			return $payload;
		}

		$saved   = BaseSettings::get_saved_data();
		$default = BaseSettings::get_default_data();
		$allowed = [];

		foreach ( $payload as $key => $value ) {
			$capability = self::capability_for_key( $key );

			if ( ! isset( $allowed[ $capability ] ) ) {
				$allowed[ $capability ] = current_user_can( $capability );
			}

			if ( $allowed[ $capability ] ) {
				continue;
			}

			if ( array_key_exists( $key, $saved ) ) {
				$payload[ $key ] = $saved[ $key ];
			} elseif ( array_key_exists( $key, $default ) ) {
				$payload[ $key ] = $default[ $key ];
			} else {
				unset( $payload[ $key ] );
			}
		}

		return $payload;
	}
}
