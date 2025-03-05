<?php
namespace ABlocks\Classes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class ControlBaseAbstract {
	abstract public static function get_attribute_default_value( $is_responsive = false);
	abstract public static function get_attribute( $attributeName, $is_responsive = false);
	abstract public static function get_css( $attribute_value, $property = '', $device = '');
	public static function has_value( $value ) {
		return isset( $value ) && ! empty( $value );
	}
	public static function get_unit( $attributeValue, $device = '' ) {
		$unit = $attributeValue['unit'];

		if ( '' === $device ) {
			return $unit;
		}

		$unitTablet = $attributeValue['unitTablet'];
		$unitMobile = $attributeValue['unitMobile'];

		if ( 'Tablet' === $device ) {
			return '' !== $unitTablet ? $unitTablet : $unit;
		} elseif ( 'Mobile' === $device ) {
			return '' !== $unitMobile ? $unitMobile : ( '' !== $unitTablet ? $unitTablet : $unit );
		}

		return $unit;
	}
}
