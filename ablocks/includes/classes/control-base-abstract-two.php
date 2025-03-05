<?php
namespace ABlocks\Classes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class ControlBaseAbstractTwo {
	abstract public static function get_attribute_default_value( $args );
	abstract public static function get_attribute( $args );
	abstract public static function get_css( $args );

	public static function get_unit( $args ) {
		$defaultUnit = $args['unitDefaultValue'];
		$value = $args['attributeValue'];
		$device = $args['device'];
		$keyPrefix = $args['attributeObjectKey'] . 'Unit';

		// Retrieve units with fallback to default
		$unitDesktop = $value[ $keyPrefix ] ?? $defaultUnit; // Desktop
		$unitTablet = $value[ $keyPrefix . 'Tablet' ] ?? $unitDesktop; // Tablet inherits from Desktop
		$unitMobile = $value[ $keyPrefix . 'Mobile' ] ?? $unitTablet; // Mobile inherits from Tablet

		// Return the appropriate unit based on the device
		if ( '' === $device ) {
			return $unitDesktop; // Desktop
		}

		if ( 'Tablet' === $device ) {
			return $unitTablet; // Tablet
		}

		if ( 'Mobile' === $device ) {
			return $unitMobile; // Mobile
		}

		return $defaultUnit; // Default fallback
	}
}
