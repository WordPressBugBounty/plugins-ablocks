<?php
namespace ABlocks\Blocks\Chart;

use ABlocks\Controls\Range;


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ABlocks\Classes\BlockBaseAbstract;
use ABlocks\Classes\CssGenerator;
use ABlocks\Controls\Alignment;

class Block extends BlockBaseAbstract {
	protected $block_name = 'chart';
	protected $script_depends = [ 'ablocks-chart.js-script' ];
	public function build_css( $attributes ) {
		$css_generator = new CssGenerator( $attributes, $this->block_name );
		$css_generator->add_class_styles(
			'{{WRAPPER}}',
			$this->get_wrapper_css( $attributes ),
			$this->get_wrapper_css( $attributes, 'Tablet' ),
			$this->get_wrapper_css( $attributes, 'Mobile' )
		);

		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-chart-canvas',
			$this->get_chart_css( $attributes ),
			$this->get_chart_css( $attributes, 'Tablet' ),
			$this->get_chart_css( $attributes, 'Mobile' )
		);

		return $css_generator->generate_css();
	}

	public function get_wrapper_css( $attributes, $device = '' ) {
		return isset( $attributes['alignment'] ) ? Alignment::get_css( $attributes['alignment'], 'text-align', $device ) : [];
	}

	public function get_chart_css( $attributes, $device = '' ) {
		$css = [];
		$widthCSS = Range::get_css([
			'attributeValue' => $attributes['chartWidth'],
			'attribute_object_key' => 'value',
			'isResponsive' => true,
			'defaultValue' => 95,
			'hasUnit' => true,
			'unitDefaultValue' => '%',
			'property' => 'width',
			'device' => $device,
		]);
		if ( ! empty( $widthCSS['width'] ) ) {
			$widthCSS['width'] = $widthCSS['width'] . ' !important';
		}
		$heightCSS = [];
		$heightCSS['height'] = 'auto' . ' !important';
		return array_merge(
			$widthCSS,
			$heightCSS
		);
	}

}
