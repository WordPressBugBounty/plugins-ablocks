<?php
namespace ABlocks\Admin\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Base {
	public static function get_saved_data() {
		$settings = get_option( ABLOCKS_SETTINGS_NAME );
		if ( $settings ) {
			return json_decode( $settings, true );
		}
		return [];
	}
	public static function get_default_data() {
		return apply_filters('ablocks/admin/settings/base_default_data', [
			// global style
			'default_container_width' => 1140,
			'container_padding' => 10,
			'container_element_gap' => 20,
			'enabled_assets_file_generation' => true,
			'enabled_block_copy_paste_style' => true,
			'enabled_only_selected_fonts' => false,
			'selected_fonts' => [],
			'enabled_coming_soon_page' => false,
			'coming_soon_page' => '',
			'enabled_maintenance_page' => false,
			'maintenance_page' => '',
		]);
	}

	public static function save_settings( $form_data = false ) {
		$default_data = self::get_default_data();
		$saved_data = self::get_saved_data();
		$settings_data = wp_parse_args( $saved_data, $default_data );
		if ( $form_data ) {
			$settings_data = wp_parse_args( $form_data, $settings_data );
		}
		// if settings already saved, then update it
		if ( count( $saved_data ) ) {
			return update_option( ABLOCKS_SETTINGS_NAME, wp_json_encode( $settings_data ) );
		}
		return add_option( ABLOCKS_SETTINGS_NAME, wp_json_encode( $settings_data ) );
	}
}
