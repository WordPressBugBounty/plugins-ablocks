<?php
namespace ABlocks\Controls;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ABlocks\Classes\ControlBaseAbstract;

class Dimensions extends ControlBaseAbstract {
	public static function get_attribute_default_value( $is_responsive = false ) {
		if ( $is_responsive ) {
			return [
				'isLinked' => true,
				'isLinkedTablet' => true,
				'isLinkedMobile' => true,
				'common' => '',
				'top' => '',
				'right' => '',
				'bottom' => '',
				'left' => '',
				'unit' => 'px',
				'commonTablet' => '',
				'topTablet' => '',
				'rightTablet' => '',
				'bottomTablet' => '',
				'leftTablet' => '',
				'unitTablet' => '',
				'commonMobile' => '',
				'topMobile' => '',
				'rightMobile' => '',
				'bottomMobile' => '',
				'leftMobile' => '',
				'unitMobile' => '',
			];
		}//end if
		return [
			'isLinked' => true,
			'common' => '',
			'top' => '',
			'right' => '',
			'bottom' => '',
			'left' => '',
			'unit' => 'px',
		];
	}

	public static function get_attribute( $attributeName, $isResponsive = false ) {
		$attribute_value = self::get_attribute_default_value( $isResponsive );
		if ( $isResponsive ) {
			return [
				$attributeName => [
					'type' => 'object',
					'default' => $attribute_value
				]
			];
		}
		return [
			$attributeName => [
				'type' => 'object',
				'default' => $attribute_value
			]
		];
	}
	public static function get_css( $attribute_value, $property = '', $device = '' ) {
		$attribute_value = wp_parse_args( $attribute_value, $device ? self::responsive_defaults() : self::get_attribute_default_value( false ) );
		$css = [];
		$unit = self::get_unit( $attribute_value, $device );

		if ( (bool) $attribute_value[ 'isLinked' . $device ] ) {
			if ( '' !== $attribute_value[ 'common' . $device ] ) {
				$css[ $property ] = $attribute_value[ 'common' . $device ] . $unit;
			}
		} else {
			/*
			 * A side may carry its own unit (`topUnit`, `rightUnit`, …), so an
			 * author can mix e.g. `padding-top: 2rem` with `padding-left: 10px`.
			 * Purely additive: a side that has never been given one falls back to
			 * the group unit, so anything saved before this compiles
			 * byte-for-byte as it did.
			 */
			foreach ( [ 'top', 'right', 'bottom', 'left' ] as $side ) {
				if ( '' === $attribute_value[ $side . $device ] ) {
					continue;
				}
				$side_unit = self::device_member( $attribute_value, $side . 'Unit', $device );
				if ( '' === $side_unit ) {
					$side_unit = self::device_member( $attribute_value, $side . 'Unit' );
				}
				if ( '' === $side_unit ) {
					$side_unit = $unit;
				}
				$css[ $property . '-' . $side ] = $attribute_value[ $side . $device ] . $side_unit;
			}
		}
		return $css;
	}

}
