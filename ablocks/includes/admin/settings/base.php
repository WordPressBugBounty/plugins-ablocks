<?php
namespace ABlocks\Admin\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ABlocks\Controls\Typography;
use ABlocks\Controls\Range;

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
			'enabled_assets_file_generation' => false,
			'enabled_block_copy_paste_style' => true,
			'enabled_load_google_font_locally' => false,
			'enabled_only_selected_fonts' => false,
			'selected_fonts' => [],
			'enabled_coming_soon_page' => false,
			'coming_soon_page' => '',
			'enabled_maintenance_page' => false,
			'maintenance_page' => '',
			'frontend_dashboard_page' => '',
			'login_page' => '',
			'registration_page' => '',
			'forget_password_page' => '',
			'global_color' => [
				[
					'id' => 'primary',
					'label' => 'Primary Color',
					'value' => '#6EC1E4',
					'is_system' => true,
				],
				[
					'id' => 'secondary',
					'label' => 'Secondary',
					'value' => '#54595F',
					'is_system' => true,
				],
				[
					'id' => 'text',
					'label' => 'Text',
					'value' => '#7A7A7A',
					'is_system' => true,
				],
				[
					'id' => 'accent',
					'label' => 'Accent',
					'value' => '#61CE70',
					'is_system' => true,
				],
			],
			'global_typography' => [
				[
					'id' => 'primary',
					'label' => 'Primary',
					'value' => Typography::get_attribute_default_value( true, [] ),
					'is_system' => true,
				],
				[
					'id' => 'secondary',
					'label' => 'Secondary',
					'value' => Typography::get_attribute_default_value( true, [] ),
					'is_system' => true,
				],
				[
					'id' => 'text',
					'label' => 'Text',
					'value' => Typography::get_attribute_default_value( true, [] ),
					'is_system' => true,
				],
				[
					'id' => 'accent',
					'label' => 'Accent',
					'value' => Typography::get_attribute_default_value( true, [] ),
					'is_system' => true,
				],
			],
			'global_font_family_fallback' => 'Sans-serif',
			// Global Typography and color
			'global_body_text_color' => '',
			'global_body_typography' => Typography::get_attribute_default_value( true, [] ),
			'global_body_paragraph_space' => Range::get_attribute_default_value( [
				'isResponsive' => true,
				'defaultValue' => '',
				'defaultValueTablet' => '',
				'defaultValueMobile' => '',
				'hasUnit' => true,
				'unitDefaultValue' => 'px',
				'attributeObjectKey' => 'value',
			] ),
			'global_link_color' => '',
			'global_link_hover_color' => '',
			'global_link_typography' => Typography::get_attribute_default_value( true, [] ),
			'global_link_hover_typography' => Typography::get_attribute_default_value( true, [] ),
			'global_h1_color' => '',
			'global_h1_typography' => Typography::get_attribute_default_value( true, [] ),
			'global_h2_color' => '',
			'global_h2_typography' => Typography::get_attribute_default_value( true, [] ),
			'global_h3_color' => '',
			'global_h3_typography' => Typography::get_attribute_default_value( true, [] ),
			'global_h4_color' => '',
			'global_h4_typography' => Typography::get_attribute_default_value( true, [] ),
			'global_h5_color' => '',
			'global_h5_typography' => Typography::get_attribute_default_value( true, [] ),
			'global_h6_color' => '',
			'global_h6_typography' => Typography::get_attribute_default_value( true, [] ),
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
