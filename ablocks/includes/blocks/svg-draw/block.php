<?php
namespace ABlocks\Blocks\SvgDraw;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ABlocks\Classes\BlockBaseAbstract;
use ABlocks\Classes\CssGenerator;
use ABlocks\Controls\Dimensions;
use ABlocks\Controls\Icon;

class Block extends BlockBaseAbstract {
	protected $block_name = 'svg-draw';

	public function build_css( $attributes ) {
		$css_generator = new CssGenerator( $attributes, $this->block_name );

		// Generate wrapper CSS start
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-block-container',
			$this->get_wrapper_css( $attributes ),
			$this->get_wrapper_css( $attributes, 'Tablet' ),
			$this->get_wrapper_css( $attributes, 'Mobile' )
		);

		// Icon Style
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-icon-wrap',
			Icon::get_wrapper_css( $attributes ),
			Icon::get_wrapper_css( $attributes, 'Tablet' ),
			Icon::get_wrapper_css( $attributes, 'Mobile' )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} svg > path',
			$this->get_svg_css( $attributes ),
			$this->get_svg_css( $attributes, 'Tablet' ),
			$this->get_svg_css( $attributes, 'Mobile' )
		);

		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-icon-wrap img.ablocks-image-icon',
			Icon::get_element_image_css( $attributes )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-icon-wrap svg.ablocks-svg-icon',
			Icon::get_element_css( $attributes ),
		);

		return $css_generator->generate_css();
	}

	public function get_wrapper_css( $attributes, $device = '' ) {
		$css = [];
		if ( ! empty( $attributes['alignment'][ 'value' . $device ] ) ) {
			$css['justify-content'] = $attributes['alignment'][ 'value' . $device ];
		}
		return $css;
	}

	public function get_svg_css( $attributes = [], $device = '' ) {
		$css = [];
		if ( ! empty( $attributes['svgDrawColor'] ) ) {
			$css['stroke'] = $attributes['svgDrawColor'];
		}
		if ( ! empty( $attributes['duration'] ) ) {
			$css['animation-duration'] = $attributes['duration'] . 's !important';
		}
		return $css;
	}
}
