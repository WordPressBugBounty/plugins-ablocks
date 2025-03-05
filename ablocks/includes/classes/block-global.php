<?php
namespace ABlocks\Classes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ABlocks\Controls\Dimensions;
use ABlocks\Controls\Width;
use ABlocks\Controls\Border;
use ABlocks\Controls\Position;
use ABlocks\Controls\Background;
use ABlocks\Controls\Zindex;
use ABlocks\Controls\Transform;
use ABlocks\Controls\Mask;
use ABlocks\Controls\Animation;
use ABlocks\Controls\BoxShadow;
use ABlocks\Helper;

class BlockGlobal {
	public static function get_attributes() {
		$attributes = [
			'_hide_on_desktop' => [
				'type' => 'boolean',
				'default' => false,
			],
			'_hide_on_tablet' => [
				'type' => 'boolean',
				'default' => false,
			],
			'_hide_on_mobile' => [
				'type' => 'boolean',
				'default' => false,
			]
		];
		$attributes = array_merge(
			Dimensions::get_attribute( '_margin', true ),
			Dimensions::get_attribute( '_padding', true ),
			Width::get_attribute( '_width', true ),
			Border::get_attribute( '_border', true ),
			Position::get_attribute( '_position', true ),
			Background::get_attribute( '_background', true ),
			Zindex::get_attribute( '_zIndex', true ),
			Transform::get_attribute( '_transform', true ),
			Mask::get_attribute( '_mask', true ),
			Animation::get_attribute( '_animation', true ),
			BoxShadow::get_attribute( '_boxShadow', true ),
			Transform::get_attribute( '_transform', true ),
			$attributes,
		);
		return apply_filters( 'ablocks/get_block_common_attributes', $attributes );
	}
	public static function get_wrapper_css( $attribute, $device = '' ) {
		$transitions = [];
		$transition_types = [
			'_border' => 'border',
			'_background' => 'background',
			'_boxShadow' => 'box-shadow',
		];
		foreach ( $transition_types as $key => $property ) {
			if ( isset( $attribute[ $key ] ) ) {
				$duration = ! empty( $attribute[ $key ]['transitionDuration'] ) ? $attribute[ $key ]['transitionDuration'] : '0';
				$transitions[] = "{$property} {$duration}s ease";
			}
		}
		$transition_string = implode( ',', $transitions );

		$css = array_merge(
			Dimensions::get_css( $attribute['_margin'], 'margin', $device ),
			Dimensions::get_css( $attribute['_padding'], 'padding', $device ),
			Mask::get_css( $attribute['_mask'], $device ),
			Background::get_css( $attribute['_background'], 'background', $device ),
			Border::get_css( $attribute['_border'], '', $device ),
			Width::get_css( $attribute['_width'], 'width', $device ),
			BoxShadow::get_css( $attribute['_boxShadow'], $device ),
			Position::get_css( $attribute['_position'], 'position', $device ),
			Zindex::get_css( $attribute['_zIndex'], 'z-index', $device ),
			[ 'transition' => $transition_string ]
		);
		return apply_filters( 'ablocks/get_block_common_wrapper_css', $css, $attribute, $device );
	}
	public static function get_wrapper_hover_css( $attribute, $device = '' ) {
		$css = array_merge(
			Background::get_hover_css( $attribute['_background'], 'background', $device ),
			Border::get_hover_css( $attribute['_border'], '', $device ),
			BoxShadow::get_hover_css( $attribute['_boxShadow'], '', $device ),
		);
		return apply_filters( 'ablocks/get_block_common_wrapper_hover_css', $css, $attribute, $device );
	}
	public static function get_container_css( $attribute, $device = '' ) {
		$css = array_merge(
			Transform::get_css( $attribute['_transform'], 'transform', $device ),
		);
		return apply_filters( 'ablocks/get_block_common_container_css', $css, $attribute, $device );
	}
	public static function get_container_hover_css( $attribute, $device = '' ) {
		$css = array_merge(
			Transform::get_hover_css( $attribute['_transform'], 'transform', $device ),
		);
		return apply_filters( 'ablocks/get_block_common_container_hover_css', $css, $attribute, $device );
	}
	public static function get_wrapper_device_responsive_css( $attribute, $device = '' ) {
		if ( ! isset( $attribute['_hide_on_desktop'] ) && ! isset( $attribute['_hide_on_tablet'] ) && ! isset( $attribute['_hide_on_mobile'] ) ) {
			return [];
		}

		if ( ! $device && Helper::has_value( $attribute['_hide_on_desktop'] ) ) {
			return [
				'display' => 'none'
			];
		}

		if ( 'Tablet' === $device && Helper::has_value( $attribute['_hide_on_tablet'] ) ) {
			return [
				'display' => 'none'
			];
		}
		if ( 'Mobile' === $device && Helper::has_value( $attribute['_hide_on_mobile'] ) ) {
			return [
				'display' => 'none'
			];
		}

		return [
			'display' => 'inherit'
		];
	}
}
