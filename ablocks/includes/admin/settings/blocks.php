<?php
namespace ABlocks\Admin\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Blocks {
	public static function get_saved_data() {
		$settings = get_option( ABLOCKS_BLOCKS_VISIBILITY_SETTINGS_NAME );
		if ( $settings ) {
			return json_decode( $settings, true );
		}
		return [];
	}
	public static function get_default_data() {
		return apply_filters('ablocks/admin/settings/blocks_default_data', [
			// Core Blocks
			'progress-tracker' => true,
			'accordion' => true,
			'button' => true,
			'dual-button' => true,
			'notice' => true,
			'container' => true,
			'countdown' => true,
			'counter' => true,
			'divider' => true,
			'heading' => true,
			'icon' => true,
			'image' => true,
			'image-scroll' => true,
			'lists' => true,
			'paragraph' => true,
			'spacer' => true,
			'star-ratings' => true,
			'video' => true,
			'tabs' => true,
			'toggle' => true,
			'news-ticker' => true,
			'image-hotspot' => true,
			'form-builder' => true,
			'form-multi-step' => true,
			'toggle-child' => true,
			'menu' => true,
			'coupon' => true,
			'modal' => true,
			'content-timeline' => true,
			'map' => true,
			'table-of-content' => true,
			'table' => true,
			'carousel' => true,
			'image-comparison' => true,
			'flip-box' => true,
			'filterable-cards' => true,
			'filterable-cards-item' => true,
			'stripe-button' => true,
			'paypal-button' => true,
			'social-shares' => true,
			'search' => true,
			'svg-draw' => true,
			'info-box' => true,
			'price-menu' => true,
			'marquee' => true,
			'marquee-child' => true,
			'logout' => true,
			// Academy LMS Blocks
			'academy-courses' => true,
			'academy-course-search' => true,
			'academy-enroll-form' => true,
			'academy-instructor-registration-form' => true,
			'academy-login-form' => true,
			'academy-password-reset-form' => true,
			'academy-pdf' => true,
			'academy-student-registration-form' => true,
			'academy-certificate' => true,
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
			return update_option( ABLOCKS_BLOCKS_VISIBILITY_SETTINGS_NAME, wp_json_encode( $settings_data ) );
		}
		return add_option( ABLOCKS_BLOCKS_VISIBILITY_SETTINGS_NAME, wp_json_encode( $settings_data ) );
	}
}
