<?php
namespace ABlocks\Controls;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ABlocks\Classes\ControlBaseAbstract;

class GroupButton extends ControlBaseAbstract {

	public static function get_attribute_default_value( $is_responsive = false, $default = [] ) {
		if ( ! is_array( $default ) ) {
			$default = [];
		}
		if ( $is_responsive ) {
			return array_merge( [
				'value' => 'default',
				'valueTablet' => '',
				'valueMobile' => '',
			], $default );
		}
		return $default['value'] ?? 'default';
	}

	public static function get_attribute( $attributeName, $isResponsive = false, $default = [] ) {
		if ( $isResponsive ) {
			return [
				$attributeName => [
					'type' => 'object',
					'default' => self::get_attribute_default_value( $isResponsive, $default ),
				],
			];
		}
		return [
			$attributeName => [
				'type' => 'string',
				'default' => self::get_attribute_default_value( $isResponsive, $default ),
			]
		];
	}

	public static function get_css( $attribute_value, $property = '', $device = '' ) {
		if ( ! is_array( $attribute_value ) ) {
			return [];
		}
		$value = array_merge(
			self::get_attribute_default_value( $device ? true : false ),
			$attribute_value
		);

		$css = [];
		if ( '' !== $value[ 'value' . $device ] ) {
			$css[ $property ] = $value[ 'value' . $device ];
		}
		return $css;
	}
}
