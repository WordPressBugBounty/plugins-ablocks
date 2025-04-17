<?php

namespace ABlocks\Blocks\TextPath;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ABlocks\Classes\BlockBaseAbstract;
use ABlocks\Classes\CssGenerator;
use ABlocks\Controls\Typography;
use ABlocks\Controls\Dimensions;
use ABlocks\Controls\Border;
use ABlocks\Controls\Icon;
use ABlocks\Controls\BoxShadow;
use ABlocks\Controls\Range;
use ABlocks\Controls\Alignment;
use ABlocks\Controls\TextStroke;
use ABlocks\Controls\Responsive;
use ABlocks\Controls\Color;
use ABlocks\Controls\TextShadow;


class Block extends BlockBaseAbstract {

	protected $block_name = 'text-path';

	public function build_css( $attributes ) {
		$css_generator = new CssGenerator( $attributes );
		$css_generator->add_class_styles(
			'{{WRAPPER}}',
			$this->get_wrapper_css( $attributes ),
			$this->get_wrapper_css( $attributes, 'Tablet' ),
			$this->get_wrapper_css( $attributes, 'Mobile' )
		);

		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-text-path',
			$this->get_text_path_container_css( $attributes ),
			$this->get_text_path_container_css( $attributes, 'Tablet' ),
			$this->get_text_path_container_css( $attributes, 'Mobile' )
		);

		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-text-path-text',
			$this->get_text_css( $attributes ),
			$this->get_text_css( $attributes, 'Tablet' ),
			$this->get_text_css( $attributes, 'Mobile' )
		);

		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-text-path-text:hover',
			$this->get_text_hover_css( $attributes ),
			$this->get_text_hover_css( $attributes, 'Tablet' ),
			$this->get_text_hover_css( $attributes, 'Mobile' )
		);

		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-path-text-path',
			$this->get_text_path_css( $attributes ),
			$this->get_text_path_css( $attributes, 'Tablet' ),
			$this->get_text_path_css( $attributes, 'Mobile' )
		);

		return $css_generator->generate_css();
	}
	// wrapper css grnerate
	public function get_wrapper_css( $attributes, $device = '' ) {
		return [ 'width' => '100%' ];
	}
	// text path css generate
	public function get_text_css( $attributes, $device = '' ) {
		$typography = isset( $attributes['typography'] ) && is_array( $attributes['typography'] )
			? $attributes['typography']
			: [];

		$text_shadow = ! empty( $attributes['textShadow'] )
			? TextShadow::get_css( $attributes['textShadow'], '', $device ) : [];

		$typography_value = array_merge( $typography, [ 'font-weight' => '400' ] );

		return array_merge(
			Typography::get_css( $typography_value, $device ),
			$text_shadow
		);
	}

	// container css genarate
	public function get_text_path_container_css( $attributes, $device = '' ) {
		$text_path_container_css = Range::get_css( [] );

		$text_path_css = Range::get_css( [] );

		if ( ! empty( $attributes['alignment'][ 'value' . $device ] ) ) {
			$text_path_container_css['justify-content'] = $attributes['alignment'][ 'value' . $device ];
		}
		return array_merge(
			$text_path_container_css,
			$text_path_css,
		);

	}

	// text hover css generate
	public function get_text_hover_css( $attributes, $device = '' ) {
		$css = [];
		if ( ! empty( $attributes['textColorH'] ) ) {
			$css['fill'] = $attributes['textColorH'];
		}
		return array_merge(
			Range::get_css([
				'attributeValue' => isset( $attributes['transition'] ) ? $attributes['transition'] : '',
				'attribute_object_key' => 'value',
				'defaultValue' => 0,
				'unitDefaultValue' => 's',
				'property' => 'transition-duration',
				'device' => $device,
			]),
			$css,
		);
	}
	// path css genarate
	public function get_text_path_css( $attributes, $device = '' ) {
		$css = [];
		if ( ! empty( $attributes['rotate'] ) ) {
			$css['transform'] = "rotate({$attributes['rotate']}deg)";
		}
		$css['--width'] = isset( $attributes['iconSize'] ) ? $attributes['iconSize'] . 'px' : '0px';
		return $css;
	}
}

