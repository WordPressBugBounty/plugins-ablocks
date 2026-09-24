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

		// ✅ Resolve value by device and inheritance: the device's own value, then
		// its declared default (defaultValue / defaultValueTablet / …Mobile), then
		// the same for each containing wider device — custom breakpoints included.
		// Mirrors getCSS() in src/controls/range/helper.js.
		$device_defaults = [
			''       => $args['defaultValue'],
			'Tablet' => $args['defaultValueTablet'],
			'Mobile' => $args['defaultValueMobile'],
		];
		$device_value = self::resolve( $args['device'], function ( $suffix ) use ( $value, $args, $device_defaults ) {
			$stored = self::get_filled_value( $value, $args['attributeObjectKey'] . $suffix );
			return Helper::has_responsive_value( $stored ) ? $stored : ( $device_defaults[ $suffix ] ?? '' );
		} );

		// ✅ If value exists, apply unit and return
		if ( Helper::has_responsive_value( $device_value ) ) {
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
		$key_prefix = $args['attributeObjectKey'] . 'Unit';
		$value      = $args['attributeValue'];
		$unit       = self::resolve( $args['device'], function ( $suffix ) use ( $value, $key_prefix ) {
			return self::get_filled_value( $value, $key_prefix . $suffix );
		} );
		return Helper::has_responsive_value( $unit ) ? $unit : $args['unitDefaultValue'];
	}

	/**
	 * The device's own value, else the nearest containing wider device's (see
	 * Helper::get_responsive_ancestors()), as read by $read_at( $suffix ).
	 * Returns null when nothing is set.
	 */
	private static function resolve( $device, $read_at ) {
		$target = 'Desktop' === $device ? '' : (string) $device;
		foreach ( array_merge( [ $target ], Helper::get_responsive_ancestors( $target ) ) as $suffix ) {
			$v = $read_at( $suffix );
			if ( Helper::has_responsive_value( $v ) ) {
				return $v;
			}
		}
		return null;
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
