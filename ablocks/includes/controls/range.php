<?php
namespace ABlocks\Controls;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ABlocks\Helper;

class Range {

	public static function get_attribute_default_value( $args ) {

		$unitObject = $args['hasUnit'] ? [
			$args['attributeObjectKey'] . 'Unit' => $args['unitDefaultValue'],
			$args['attributeObjectKey'] . 'UnitTablet' => '',
			$args['attributeObjectKey'] . 'UnitMobile' => '',
		] : [];

		if ( $args['isResponsive'] ) {
			return array_merge([
				$args['attributeObjectKey'] => $args['defaultValue'],
				$args['attributeObjectKey'] . 'Tablet' => $args['defaultValueTablet'],
				$args['attributeObjectKey'] . 'Mobile' => $args['defaultValueMobile']
			], $unitObject);
		} elseif ( ! $args['isResponsive'] && $args['hasUnit'] ) {
			return array_merge([
				$args['attributeObjectKey'] => $args['defaultValue'],
				$args['attributeObjectKey'] . 'Unit' => $args['unitDefaultValue']
			], $unitObject);
		}

		return $args['defaultValue'];
	}

	public static function get_attribute( $args ) {
		$defaults = [
			'attributeName' => '',
			'isResponsive' => false,
			'defaultValue' => '',
			'defaultValueTablet' => '',
			'defaultValueMobile' => '',
			'hasUnit' => false,
			'unitDefaultValue' => 'px',
			'attributeObjectKey' => 'value',
		];

		$args = wp_parse_args( $args, $defaults );

		if ( $args['isResponsive'] ) {
			return [
				$args['attributeName'] => [
					'type' => 'object',
					'default' => self::get_attribute_default_value( $args )
				]
			];
		} elseif ( ! $args['isResponsive'] && $args['hasUnit'] ) {
			return [
				$args['attributeName'] => [
					'type' => 'object',
					'default' => self::get_attribute_default_value( $args )
				]
			];
		}

		return [
			$args['attributeName'] => [
				'type' => 'number',
				'default' => $args['defaultValue']
			]
		];
	}

	public static function get_css( $args ) {
		if ( ! isset( $args['property'] ) || empty( $args['property'] ) ) {
			return [];
		}

		// Set default values for missing arguments
		$args = wp_parse_args( $args, [
			'attributeValue'      => '',
			'isResponsive'        => false,
			'defaultValue'        => '',
			'defaultValueTablet'  => '',
			'defaultValueMobile'  => '',
			'hasUnit'             => false,
			'unitDefaultValue'    => 'px',
			'attributeObjectKey'  => 'value',
			'device'              => '',
		]);

		$value = $args['attributeValue'];
		$css   = [];

		// ✅ Basic non-responsive + non-unit handling
		if ( ! $args['isResponsive'] && ! $args['hasUnit'] ) {
			if ( $args['property'] === 'value' ) {
				$css['value']     = $value;
				$css['valueUnit'] = $args['unitDefaultValue'];
			} elseif ( '' !== (string) $value ) {
				// Skip when there's no value — otherwise we emit the unit alone
				// (e.g. "transition-duration:s"), invalid CSS that bloats every
				// block's generated stylesheet.
				$css[ $args['property'] ] = $value . $args['unitDefaultValue'];
			}
			return $css;
		}

		// ✅ Get values per device
		$desktop_key = $args['attributeObjectKey'];
		$tablet_key  = $args['attributeObjectKey'] . 'Tablet';
		$mobile_key  = $args['attributeObjectKey'] . 'Mobile';

		// A stored 0 is a real value (e.g. "top: 0" at Tablet), so only a missing
		// or empty-string key counts as unset — matching the editor helper.
		$desktop_val = self::get_filled_value( $value, $desktop_key );
		$tablet_val  = self::get_filled_value( $value, $tablet_key );
		$mobile_val  = self::get_filled_value( $value, $mobile_key );

		$device_value = '';

		switch ( $args['device'] ) {
			case '':
				if ( $desktop_val !== '' ) {
					$device_value = $desktop_val;
				} else {
					$device_value = $args['defaultValue'];
				}
				break;

			case 'Mobile':
				if ( $mobile_val !== '' ) {
					$device_value = $mobile_val;
				} elseif ( $args['defaultValueMobile'] !== '' ) {
					$device_value = $args['defaultValueMobile'];
				} elseif ( $tablet_val !== '' ) {
					$device_value = $tablet_val;
				} elseif ( $args['defaultValueTablet'] !== '' ) {
					$device_value = $args['defaultValueTablet'];
				} elseif ( $desktop_val !== '' ) {
					$device_value = $desktop_val;
				} else {
					$device_value = $args['defaultValue'];
				}
				break;

			case 'Tablet':
				if ( $tablet_val !== '' ) {
					$device_value = $tablet_val;
				} elseif ( $args['defaultValueTablet'] !== '' ) {
					$device_value = $args['defaultValueTablet'];
				} elseif ( $desktop_val !== '' ) {
					$device_value = $desktop_val;
				} else {
					$device_value = $args['defaultValue'];
				}
				break;

			default: // Custom breakpoint suffix (e.g. "Bp1024")
				$custom_val = self::get_filled_value( $value, $desktop_key . $args['device'] );
				if ( $custom_val !== '' ) {
					$device_value = $custom_val;
				} elseif ( $desktop_val !== '' ) {
					$device_value = $desktop_val;
				} else {
					$device_value = $args['defaultValue'];
				}
				break;
		}//end switch

		// ✅ If value exists, apply unit and return
		if ( $device_value !== '' ) {
			$unit = self::get_unit( [
				'attributeValue'      => $value,
				'attributeObjectKey'  => $args['attributeObjectKey'],
				'unitDefaultValue'    => $args['unitDefaultValue'],
				'device'              => $args['device'],
			] );

			if ( $args['property'] === 'value' ) {
				$css['value']     = $device_value;
				$css['valueUnit'] = $unit;
			} else {
				$css[ $args['property'] ] = $device_value . $unit;
			}
		}

		return $css;
	}


	public static function get_unit( $args ) {
		$defaultUnit = $args['unitDefaultValue'];
		$value = $args['attributeValue'];
		$device = $args['device'];
		$keyPrefix = $args['attributeObjectKey'] . 'Unit';

		// Retrieve units with fallback to default
		$unitDesktop = Helper::get_array_value( $value, $keyPrefix, $defaultUnit ); // Desktop
		$unitTablet = Helper::get_array_value( $value, $keyPrefix . 'Tablet', $unitDesktop ); // Tablet inherits from Desktop
		$unitMobile = Helper::get_array_value( $value, $keyPrefix . 'Mobile', $unitTablet ); // Mobile inherits from Tablet

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

		if ( is_string( $device ) && '' !== $device ) {
			// Custom breakpoint inherits the desktop unit when it has none of its own.
			$unitCustom = self::get_filled_value( $value, $keyPrefix . $device );
			return '' !== $unitCustom ? $unitCustom : $unitDesktop;
		}

		return $defaultUnit; // Default fallback
	}

	/**
	 * A stored responsive member, or '' when it is missing, null or an empty
	 * string. Unlike empty(), a 0 value is kept.
	 */
	private static function get_filled_value( $value, $key ) {
		if ( ! is_array( $value ) || ! isset( $value[ $key ] ) || is_array( $value[ $key ] ) || '' === (string) $value[ $key ] ) {
			return '';
		}
		return $value[ $key ];
	}
}
