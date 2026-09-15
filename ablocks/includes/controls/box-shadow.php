<?php
namespace ABlocks\Controls;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ABlocks\Classes\ControlBaseAbstract;
use ABlocks\Controls\Color;

class BoxShadow extends ControlBaseAbstract {
	public static function get_attribute_default_value( $is_responsive = false ) {
		return [
			'preset'   => '',
			'shadowType' => 'default',
			'shadow'     => '',
			'horizontal' => '',
			'vertical'   => '',
			'blur'       => '',
			'spread'     => '',
			'color'      => '',
			'presetH' => '',
			'shadowTypeH' => 'default',
			'shadowH' => '',
			'horizontalH' => '',
			'verticalH' => '',
			'blurH' => '',
			'spreadH' => '',
			'colorH' => '',
			'transitionDuration' => '0',
		];
	}

	public static function get_attribute( $attributeName, $is_responsive = false ) {
		return [
			$attributeName => [
				'type' => 'object',
				'default' => self::get_attribute_default_value( true ),
			],
		];
	}
	public static function get_css( $attribute_value, $property = '', $device = '' ) {
		$value = wp_parse_args( $attribute_value,
			self::get_attribute_default_value( true )
		);
		$css = [];
		$color = Color::get_css( $value['color'] ? $value['color'] : '#000000' );
		if ( $value['shadowType'] !== 'default' && $value['shadowType'] === 'inner_shadow' ) {
			if ( ! empty( $value['preset'] ) ) {
				$css['box-shadow'] = 'inset ' . $value['horizontal'] . 'px ' . $value['vertical'] . 'px ' . $value['blur'] . 'px ' . $value['spread'] . 'px ' . $color;

			}
		} elseif ( $value['shadowType'] !== 'default' && $value['shadowType'] === 'outer_shadow' ) {
			if ( ! empty( $value['preset'] ) ) {
				$css['box-shadow'] = $value['horizontal'] . 'px ' . $value['vertical'] . 'px ' . $value['blur'] . 'px ' . $value['spread'] . 'px ' . $color;
			}
		} else {
			// A 0 offset is a valid shadow (e.g. a centered glow); only an unset
			// value skips it, matching the editor's '' / undefined check.
			if ( self::is_offset_set( $value['horizontal'] ) && self::is_offset_set( $value['vertical'] ) ) {
				$css['box-shadow'] = $value['horizontal'] . 'px ' . $value['vertical'] . 'px ' . $value['blur'] . 'px ' . $value['spread'] . 'px ' . $color;
			}
		}
		if ( $value['transitionDuration'] ) {
			// This control animates the box-shadow, so the transition property is
			// always "box-shadow". Previously it used $property, which callers
			// pass inconsistently (often the device string), producing invalid
			// CSS like "transition:Tablet 0.3s".
			$css['transition'] = 'box-shadow ' . $value['transitionDuration'] . 's';
		}
		return $css;
	}

	public static function get_hover_css( $attribute, $device = '' ) {
		$value = wp_parse_args(
			$attribute,
			self::get_attribute_default_value( true ),
		);

		$css = [];
		$colorH = Color::get_css( $value['colorH'] ? $value['colorH'] : '#000000' );
		if ( $value['shadowTypeH'] !== 'default' && $value['shadowTypeH'] === 'inner_shadow' ) {
			if ( ! empty( $value['presetH'] ) ) {
				$css['box-shadow'] = 'inset ' . $value['horizontalH'] . 'px ' . $value['verticalH'] . 'px ' . $value['blurH'] . 'px ' . $value['spreadH'] . 'px ' . $colorH;
			}
		} elseif ( $value['shadowTypeH'] !== 'default' && $value['shadowTypeH'] === 'outer_shadow' ) {
			if ( ! empty( $value['presetH'] ) ) {
				$css['box-shadow'] = $value['horizontalH'] . 'px ' . $value['verticalH'] . 'px ' . $value['blurH'] . 'px ' . $value['spreadH'] . 'px ' . $colorH;
			}
		} else {
			if ( self::is_offset_set( $value['horizontalH'] ) && self::is_offset_set( $value['verticalH'] ) ) {
				$css['box-shadow'] = $value['horizontalH'] . 'px ' . $value['verticalH'] . 'px ' . $value['blurH'] . 'px ' . $value['spreadH'] . 'px ' . $colorH;
			}
		}
		return $css;
	}

	/**
	 * Whether a shadow offset is set. 0 / "0" count as set; only null and ''
	 * mean "not configured".
	 */
	private static function is_offset_set( $value ) {
		return null !== $value && '' !== $value;
	}

}
