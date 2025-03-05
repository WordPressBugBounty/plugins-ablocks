<?php
namespace ABlocks\Controls;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ABlocks\Classes\ControlBaseAbstract;
use ABlocks\Controls\Dimensions;
use ABlocks\Controls\Range;

class Icon {
	public static function get_attribute_default_value( $is_responsive = false ) {
		return [];
	}

	public static function get_attribute( $attribute_prefix = 'icon', $default = [] ) {
		$default_args = wp_parse_args($default, [
			'path' => 'M248 8C111 8 0 119 0 256s111 248 248 248 248-111 248-248S385 8 248 8zm0 448c-110.3 0-200-89.7-200-200S137.7 56 248 56s200 89.7 200 200-89.7 200-200 200zm84-143.4c-20.8 25-51.5 39.4-84 39.4s-63.2-14.3-84-39.4c-8.5-10.2-23.6-11.5-33.8-3.1-10.2 8.5-11.5 23.6-3.1 33.8 30 36 74.1 56.6 120.9 56.6s90.9-20.6 120.9-56.6c8.5-10.2 7.1-25.3-3.1-33.8-10.2-8.4-25.3-7.1-33.8 3.1zM136.5 211c7.7-13.7 19.2-21.6 31.5-21.6s23.8 7.9 31.5 21.6l9.5 17c2.1 3.7 6.2 4.7 9.3 3.7 3.6-1.1 6-4.5 5.7-8.3-3.3-42.1-32.2-71.4-56-71.4s-52.7 29.3-56 71.4c-.3 3.7 2.1 7.2 5.7 8.3 3.4 1.1 7.4-.5 9.3-3.7l9.5-17zM328 152c-23.8 0-52.7 29.3-56 71.4-.3 3.7 2.1 7.2 5.7 8.3 3.5 1.1 7.4-.5 9.3-3.7l9.5-17c7.7-13.7 19.2-21.6 31.5-21.6s23.8 7.9 31.5 21.6l9.5 17c2.1 3.7 6.2 4.7 9.3 3.7 3.6-1.1 6-4.5 5.7-8.3-3.3-42.1-32.2-71.4-56-71.4z',
			'viewBox' => '0 0 496 512',
			'className' => 'far fa-smile-beam',
			'hasNoSelectorOrSource' => false,
			'size' => 55,
			'color' => '#69727d',
			'BgColor' => '',
			'iconType' => 'default',
			'iconShape' => 'circle',
		]);

		$svgPathKey = $attribute_prefix . 'SvgPath';
		$svgViewBoxKey = $attribute_prefix . 'SvgViewBox';
		$svgClassKey = $attribute_prefix . 'Class';
		$customImageUrl = $attribute_prefix . 'ImageUrl';
		$customImageID = $attribute_prefix . 'ImageID';
		$customImageSize = $attribute_prefix . 'ImageSize';

		$attribute = [
			$svgPathKey => [
				'type' => 'string',
				'default' => $default_args['path'],
			],
			$svgViewBoxKey => [
				'type' => 'string',
				'default' => $default_args['viewBox'],
			],
			$svgClassKey => [
				'type' => 'string',
				'default' => $default_args['className'],
			],
			$customImageUrl => [
				'type' => 'string',
				'default' => '',
			],
			$customImageID => [
				'type' => 'number',
				'default' => 0,
			],
			$customImageSize => [
				'type' => 'string',
				'default' => '',
			],
			$attribute_prefix . 'Color' => [
				'type' => 'string',
				'default' => $default_args['color'],
			],
			$attribute_prefix . 'BgColor' => [
				'type' => 'string',
				'default' => $default_args['BgColor'],
			],
			$attribute_prefix . 'Type' => [
				'type' => 'string',
				'default' => $default_args['iconType'],
			],
			$attribute_prefix . 'Shape' => [
				'type' => 'string',
				'default' => $default_args['iconShape'],
			],
		];

		// Add additional attributes if 'hasNoSelectorOrSource' is false
		if ( ! $default_args['hasNoSelectorOrSource'] ) {
			$attribute[ $svgPathKey ]['source'] = 'attribute';
			$attribute[ $svgPathKey ]['selector'] = 'svg.ablocks-svg-icon path';
			$attribute[ $svgPathKey ]['attribute'] = 'd';

			$attribute[ $svgViewBoxKey ]['source'] = 'attribute';
			$attribute[ $svgViewBoxKey ]['selector'] = 'svg.ablocks-svg-icon';
			$attribute[ $svgViewBoxKey ]['attribute'] = 'viewBox';
		}

		$attribute = array_merge(
			$attribute,
			Dimensions::get_attribute( $attribute_prefix . 'Padding', false ),
			Dimensions::get_attribute( $attribute_prefix . 'BorderRadius', false ),
			Dimensions::get_attribute( $attribute_prefix . 'BorderWidth', false ),
			Range::get_attribute([
				'attributeName' => $attribute_prefix . 'Size',
				'isResponsive' => false,
				'defaultValue' => $default_args['size'],
			]),
			Range::get_attribute([
				'attributeName' => $attribute_prefix . 'Rotate',
				'isResponsive' => false,
				'defaultValue' => 0,
			]),
			Range::get_attribute([
				'attributeName' => $attribute_prefix . 'ImageWidth',
				'isResponsive' => false,
				'defaultValue' => 0,
			])
		);

		return $attribute;
	}

	public static function get_css( $attribute_value, $property = '', $device = '' ) {
		return [];
	}



	public static function get_wrapper_css( $attributes, $device = '', $attributePrefix = 'icon' ) {
		$iconType = $attributes[ $attributePrefix . 'Type' ];
		$iconShape = $attributes[ $attributePrefix . 'Shape' ];
		$backgroundColor = $attributes[ $attributePrefix . 'BgColor' ];
		$primaryColor = $attributes[ $attributePrefix . 'Color' ];
		$iconViewCSS = [];

		if ( $iconType !== 'default' ) {
			if ( $iconType === 'stacked' ) {
				if ( $iconShape === 'circle' ) {
					$iconViewCSS = [
						'background' => $backgroundColor ? $backgroundColor : '#ddd',
						'border-radius' => '50%',
						'padding' => '.5em',
					];
				} elseif ( $iconShape === 'square' ) {
					$iconViewCSS = [
						'background' => $backgroundColor ? $backgroundColor : '#ddd',
						'padding' => '.5em',
					];
				}
			} elseif ( $iconType === 'framed' ) {
				if ( $iconShape === 'circle' ) {
					$iconViewCSS = [
						'background' => $backgroundColor ? $backgroundColor : 'transparent',
						'padding' => '.5em',
						'border-radius' => '50px',
						'border' => '2px solid ' . ( $primaryColor ? $primaryColor : '#69727d' ),
					];
				} elseif ( $iconShape === 'square' ) {
					$iconViewCSS = [
						'background' => $backgroundColor ? $backgroundColor : 'transparent',
						'padding' => '.5em',
						'border' => '2px solid ' . ( $primaryColor ? $primaryColor : '#69727d' ),
					];
				}
			}//end if
		}//end if

		if ( ! empty( $attributes[ $attributePrefix . 'Size' ] ) && empty( $attributes[ $attributePrefix . 'ImageUrl' ] ) ) {
			$iconViewCSS['font-size'] = $attributes[ $attributePrefix . 'Size' ] . 'px';
		}
		// Remove font size for fixing
		if ( isset( $attributes[ $attributePrefix . 'ImageUrl' ] ) && ! empty( $attributes[ $attributePrefix . 'ImageUrl' ] ) ) {
			$iconViewCSS['font-size'] = 'unset';
		}

		return array_merge(
			[ 'background' => $backgroundColor ],
			$iconViewCSS,
			'default' !== $iconType ? array_merge(
				Dimensions::get_css( $attributes[ $attributePrefix . 'Padding' ], 'padding', $device ),
				Dimensions::get_css( $attributes[ $attributePrefix . 'BorderRadius' ], 'border-radius', $device ),
				Dimensions::get_css( $attributes[ $attributePrefix . 'BorderWidth' ], 'border-width', $device )
			) : []
		);
	}

	public static function get_element_css( $attributes, $device = '', $attributePrefix = 'icon' ) {
		$iconType = $attributes[ $attributePrefix . 'Type' ] ?? 'default';
		$iconShape = $attributes[ $attributePrefix . 'Shape' ] ?? '';
		$primaryColor = $attributes[ $attributePrefix . 'Color' ] ?? '#69727d';
		$rotate = $attributes[ $attributePrefix . 'Rotate' ] ?? 0;
		$iconViewCSS = [];

		if ( $iconType !== 'default' ) {
			$iconViewCSS['fill'] = $primaryColor;
		}

		if ( $rotate ) {
			$iconViewCSS['transform'] = 'rotate(' . $rotate . 'deg)';
		}

		return array_merge(
			[ 'fill' => $primaryColor ],
			$iconViewCSS
		);
	}

	public static function get_element_image_css( $attributes, $device = '', $attributePrefix = 'icon' ) {
		$rotate = $attributes[ $attributePrefix . 'Rotate' ] ?? 0;
		$iconViewCSS = [];

		if ( $rotate ) {
			$iconViewCSS['transform'] = 'rotate(' . $rotate . 'deg)';
		}

		return array_merge(
			[
				'width' => $attributes[ $attributePrefix . 'Size' ] . 'px',
				'height' => 'auto'
			],
			$iconViewCSS
		);
	}
}
