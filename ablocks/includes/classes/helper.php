<?php
namespace ABlocks\Classes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Helper {
	public static function attr_shortcode( $attr_array ) {
		$html_attr = '';
		foreach ( $attr_array as $attr_name => $attr_val ) {
			if ( empty( $attr_val ) ) {
				continue;
			}
			if ( is_array( $attr_val ) ) {
				$html_attr .= $attr_name . '="' . implode( ',', $attr_val ) . '" ';
			} else {
				$html_attr .= $attr_name . '="' . $attr_val . '" ';
			}
		}
		return $html_attr;
	}

	public static function get_attribute_value( $attributes, $attribute_name ) {
		return isset( $attributes[ $attribute_name ] ) ? $attributes[ $attribute_name ] : '';
	}

	public static function get_terms_list( $taxonomy = 'category' ) {
		$options = [];
		$terms   = get_terms( [
			'taxonomy'   => $taxonomy,
			'hide_empty' => true,
		] );

		if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				$options[] = [
					'label' => $term->name,
					'value' => $term->term_id,
				];
			}
		}

		return $options;
	}

	public static function get_icon_picker_attribute( $attributePrefix = 'icon', $defaultValue = [] ) {
		$svgPathKey = $attributePrefix . 'SvgPath';
		$svgViewBoxKey = $attributePrefix . 'SvgViewBox';
		$svgClassKey = $attributePrefix . 'Class';

		$attribute = [
			$svgPathKey => [
				'type' => 'string',
				'source' => 'attribute',
				'selector' => 'svg.ablocks-svg-icon path',
				'attribute' => 'd',
			],
			$svgViewBoxKey => [
				'type' => 'string',
				'source' => 'attribute',
				'selector' => 'svg.ablocks-svg-icon',
				'attribute' => 'viewBox',
			],
			$svgClassKey => [
				'type' => 'string',
			],
		];

		if ( isset( $defaultValue['path'] ) && isset( $defaultValue['viewBox'] ) ) {
			$attribute[ $svgPathKey ]['default'] = $defaultValue['path'];
			$attribute[ $svgViewBoxKey ]['default'] = $defaultValue['viewBox'];
		}
		if ( isset( $defaultValue['className'] ) ) {
			$attribute[ $svgClassKey ]['default'] = $defaultValue['className'];
		}
		return $attribute;
	}

}

