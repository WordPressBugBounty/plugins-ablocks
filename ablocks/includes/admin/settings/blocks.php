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
			'atomic-text' => true,
			'atomic-image' => true,
			'atomic-svg' => true,
			'atomic-div' => true,
			'atomic-flex' => true,
			'atomic-grid' => true,
			'icon' => true,
			'image' => true,
			'image-scroll' => true,
			'lists' => true,
			'paragraph' => true,
			'player' => true,
			'spacer' => true,
			'star-ratings' => true,
			'video' => true,
			'tabs' => true,
			'toggle' => true,
			'loop-builder' => true,
			'loop-template' => true,
			'loop-load-more' => true,
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
			'savings-calculator' => true,
			'frontend-dashboard' => true,
			'lottie-animation' => true,
			'marquee' => true,
			'marquee-child' => true,
			'logout' => true,
			'qr-code' => true,
			'chart' => true,
			'text-path' => true,
			'advance-lists' => true,
			'stacked-cards' => true,
			'group-image-effect' => true,
			'taxonomy-listing' => true,
			'dynamic-text' => true,
			'featured-image' => true,
			'scroll-to-top' => true,
			'breadcrumb' => true,
			'interactive-circle-infographic' => true,
			'code-highlighter' => true,
			// Academy LMS Blocks
			'academy-courses' => false,
			'academy-container' => false,
			'academy-course-search' => false,
			'academy-enroll-form' => false,
			'academy-instructor-registration-form' => false,
			'academy-course-media' => false,
			'academy-login-form' => false,
			'academy-password-reset-form' => false,
			'academy-pdf' => false,
			'academy-student-registration-form' => false,
			'academy-certificate' => false,
			'academy-course-curriculums' => false,
			'academy-course-reviews' => false,
			'academy-review-form' => false,
			'academy-review-list' => false,
			'academy-addition-info' => false,
			'academy-course-instructor' => false,
			'academy-course-description' => false,
			'academy-enroll-content' => false,

			// StoreEngine Blocks
			'storeengine-products' => false,
			'storeengine-cart-list' => false,
			'storeengine-login-form' => false,
			'storeengine-coupon-form' => false,
			'storeengine-product-filter' => false,
			'storeengine-checkout-form' => false,
			'storeengine-continue-button' => false,
			'storeengine-checkout-button' => false,
			'storeengine-cart-sub-table' => false,
			'storeengine-cart-button' => false,
			'storeengine-order-info' => false,
			'storeengine-billing-info' => false,
			'storeengine-shipping-info' => false,
			'storeengine-order-details' => false,
			'storeengine-funnel-checkout' => false,
			'storeengine-funnel-offer' => false,
			'storeengine-funnel-accept' => false,
			'storeengine-funnel-decline' => false,
			'storeengine-funnel-continue' => false,
			'storeengine-mini-cart' => false,
			'storeengine-cart-notice' => false,
			'storeengine-product-gallery' => false,
			'storeengine-product-summary' => false,
			'storeengine-product-description' => false,
			'storeengine-product-review' => false,

			// Easy Content Manager Blocks
			'ecm-bookmark-button' => false,
			'ecm-bookmark-badge' => false,
			'ecm-bookmark-list' => false,
			'ecm-bookmark-count' => false,
			'ecm-reaction-list' => false,
			'ecm-reaction' => false,
			'ecm-upvote' => false,
			'ecm-upvote-count' => false,
			'ecm-upvote-list' => false,
			'ecm-claim-button' => false,
			'ecm-claim-badge' => false,
			'ecm-claim-list' => false,
			'ecm-claim-count' => false,
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
