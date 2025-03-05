<?php
namespace ABlocks\Controls;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ABlocks\Classes\ControlBaseAbstractTwo;

class Range extends ControlBaseAbstractTwo {

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
		if ( ! isset( $args['property'] ) ||
			empty( $args['property'] )
		) {
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
		$css = [];

		if ( ! $args['isResponsive'] && ! $args['hasUnit'] ) {
			if ( $args['property'] === 'value' ) {
				$css['value'] = $value;
				$css['valueUnit'] = $args['unitDefaultValue'];
			} else {
				$css[ $args['property'] ] = $value . $args['unitDefaultValue'];
			}
			return $css;
		}

		// Check and generate CSS for the specified device
		$deviceValueKey = $args['attributeObjectKey'] . $args['device'];
		if ( ! empty( $value[ $deviceValueKey ] ) ) {
			$unit = self::get_unit( [
				'attributeValue'      => $value,
				'attributeObjectKey'  => $args['attributeObjectKey'],
				'unitDefaultValue'    => $args['unitDefaultValue'],
				'device'              => $args['device'],
			] );

			if ( $args['property'] === 'value' ) {
				$css['value'] = $value[ $deviceValueKey ];
				$css['valueUnit'] = $unit;
			} else {
				$css[ $args['property'] ] = $value[ $deviceValueKey ] . $unit;
			}
		}

		return $css;
	}

}
