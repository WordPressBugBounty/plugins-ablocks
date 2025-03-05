<?php
namespace ABlocks\Controls;

if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

use ABlocks\Classes\ControlBaseAbstract;

class Width extends ControlBaseAbstract {

	public static function get_attribute_default_value( $is_responsive = false ) {
		if ( $is_responsive ) {
			return [
				'widthType' => 'default',
				'widthTypeTablet' => '',
				'widthTypeMobile' => '',
				'customWidth' => '',
				'customWidthTablet' => '',
				'customWidthMobile' => '',
				'customWidthUnit' => '%',
				'customWidthUnitTablet' => '',
				'customWidthUnitMobile' => '',
			];
		}
		return [
			'widthType' => 'default',
			'customWidth' => '',
			'customWidthUnit' => '%',
		];
	}

	public static function get_attribute( $attributeName, $isResponsive = false ) {
		if ( $isResponsive ) {
			return [
				$attributeName => [
					'type' => 'object',
					'default' => self::get_attribute_default_value(
						$isResponsive
					),
				],
			];
		}
		return [
			$attributeName => [
				'type' => 'object',
				'default' => self::get_attribute_default_value( $isResponsive ),
			],
		];
	}
	public static function get_css(
		$attribute_value,
		$property = '',
		$device = ''
	) {
		$attribute_value = wp_parse_args(
			$attribute_value,
			self::get_attribute_default_value( (bool) $device )
		);
		$css = [];
		if ( ! empty( $attribute_value[ 'widthType' . $device ] ) ) {
			if ( $attribute_value[ 'widthType' . $device ] === 'full' ) {
				$css[ $property ] = '100%';
			} elseif ( $attribute_value[ 'widthType' . $device ] === 'auto' ) {
				$css[ $property ] = 'auto';
				$css['display'] = 'inline-block';
			} elseif ( $attribute_value[ 'widthType' . $device ] === 'custom' ) {
				$css[ $property ] =
					$attribute_value[ 'customWidth' . $device ] .
					self::get_unit(
						[
							'unit' => ! empty(
								$attribute_value['customWidthUnit']
							)
								? $attribute_value['customWidthUnit']
								: '',
							'unitTablet' => ! empty(
								$attribute_value['customWidthUnitTablet']
							)
								? $attribute_value['customWidthUnitTablet']
								: '',
							'unitMobile' => ! empty(
								$attribute_value['customWidthUnitTablet']
							)
								? $attribute_value['customWidthUnitTablet']
								: '',
						],
						$device
					) . '!important';
			}//end if
		}//end if
		return $css;
	}
}
