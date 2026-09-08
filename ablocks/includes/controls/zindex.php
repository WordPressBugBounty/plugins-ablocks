<?php
namespace ABlocks\Controls;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ABlocks\Classes\ControlBaseAbstract;

class Zindex extends ControlBaseAbstract {
	public static function get_attribute_default_value( $is_responsive = false ) {
		if ( $is_responsive ) {
			return [
				'zIndex' => '',
				'zIndexTablet' => '',
				'zIndexMobile' => '',
			];
		}
		return [
			'zIndex' => '',
		];
	}

	public static function get_attribute( $attributeName, $isResponsive = false ) {
		return [
			$attributeName => [
				'type' => 'object',
				'default' => self::get_attribute_default_value( $isResponsive ),
			]
		];
	}

	public static function get_css( $attribute_value, $property = '', $device = '' ) {
		$default_attar_value = $device ? self::responsive_defaults() : self::get_attribute_default_value( false );
		$value = wp_parse_args( $attribute_value, $default_attar_value );
		$css = [];
		if ( $value[ 'zIndex' . $device ] ) {
			$css[ $property ] = $value[ 'zIndex' . $device ];
		}
		return $css;
	}


}
