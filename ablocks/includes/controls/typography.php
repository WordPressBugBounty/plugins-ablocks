<?php
namespace ABlocks\Controls;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ABlocks\Classes\ControlBaseAbstract;

class Typography extends ControlBaseAbstract {
	public static function get_attribute_default_value( $is_responsive = false, $default = [] ) {
		$base_values = [
			'fontFamily' => '',
			'weight' => '400',
			'transform' => '',
			'style' => '',
			'decoration' => '',
			'fontSize' => '',
			'fontSizeUnit' => 'px',
			'lineHeight' => '',
			'lineHeightUnit' => 'px',
			'letterSpacing' => '',
			'letterSpacingUnit' => 'px',
			'wordSpacing' => '',
			'wordSpacingUnit' => 'px',
		];

		if ( $is_responsive ) {
			$responsive_values = [
				'fontSizeTablet' => '',
				'fontSizeMobile' => '',
				'fontSizeUnitTablet' => 'px',
				'fontSizeUnitMobile' => 'px',
				'lineHeightTablet' => '',
				'lineHeightMobile' => '',
				'lineHeightUnitTablet' => 'px',
				'lineHeightUnitMobile' => 'px',
				'letterSpacingTablet' => '',
				'letterSpacingMobile' => '',
				'letterSpacingUnitTablet' => 'px',
				'letterSpacingUnitMobile' => 'px',
				'wordSpacingTablet' => '',
				'wordSpacingMobile' => '',
				'wordSpacingUnitTablet' => 'px',
				'wordSpacingUnitMobile' => 'px',
			];
			return array_merge( $base_values, $responsive_values, $default );
		}//end if

		return array_merge( $base_values, $default );
	}


	public static function get_attribute( $attributeName, $isResponsive = false, $default = [] ) {
		return [
			$attributeName => [
				'type' => 'object',
				'default' => self::get_attribute_default_value( $isResponsive, $default ),
			]
		];
	}

	public static function get_css( $attribute_value, $property = '', $device = '' ) {
		global $ablocks_fonts;
		if ( empty( $attribute_value ) ) {
			return [];
		}
		$default_attr_value = self::get_attribute_default_value( (bool) $device );
		$value = wp_parse_args( $attribute_value, $default_attr_value );
		$css = [];

		// Parse font family
		if ( ! empty( $value['fontFamily'] ) ) {
			$font_family = $value['fontFamily'];
			$font_weight = ! empty( $value['weight'] ) ? $value['weight'] : '400';

			$css['font-family'] = $font_family;

			if ( isset( $ablocks_fonts[ $font_family ] ) ) {
				if ( ! in_array( $font_weight, $ablocks_fonts[ $font_family ], true ) ) {
					$ablocks_fonts[ $font_family ][] = $font_weight;
				}
			} else {
				$ablocks_fonts[ $font_family ] = [ $font_weight ];
			}
			update_option( ABLOCKS_FONTS_SETTINGS_NAME, wp_json_encode( $ablocks_fonts ) );
		}

		// For desktop-specific styles
		if ( empty( $device ) ) {
			if ( ! empty( $value['weight'] ) && $value['weight'] !== '400' ) {
				$css['font-weight'] = $value['weight'];
			}
			if ( ! empty( $value['transform'] ) ) {
				$css['text-transform'] = $value['transform'];
			}
			if ( ! empty( $value['style'] ) ) {
				$css['font-style'] = $value['style'];
			}
			if ( ! empty( $value['decoration'] ) ) {
				$css['text-decoration'] = $value['decoration'];
			}
		}

		// Device-specific font properties
		$properties = [ 'fontSize', 'lineHeight', 'letterSpacing', 'wordSpacing' ];
		foreach ( $properties as $prop ) {
			if ( ! empty( $value[ $prop . $device ] ) && ! empty( $value[ $prop . 'Unit' . $device ] ) ) {
				$css_prop = strtolower( preg_replace( '/([a-z])([A-Z])/', '$1-$2', $prop ) );
				$css[ $css_prop ] = $value[ $prop . $device ] . $value[ $prop . 'Unit' . $device ];
			}
		}

		return $css;
	}
}
