<?php
namespace ABlocks\Blocks\DualButton;

use ABlocks\Classes\BlockBaseAbstract;
use ABlocks\Controls\Alignment;
use ABlocks\Classes\CssGenerator;
use ABlocks\Controls\Range;


class Block extends BlockBaseAbstract {
	protected $block_name = 'dual-button';

	public function build_css( $attributes ) {

		$css_generator = new CssGenerator( $attributes, $this->block_name );

		$css_generator->add_class_styles(
			'{{WRAPPER}}.ablocks-block--dual-button > .ablocks-block-container',
			$this->getDualButtonCss( $attributes ),
			$this->getDualButtonCss( $attributes, 'Tablet' ),
			$this->getDualButtonCss( $attributes, 'Mobile' )
		);

		return $css_generator->generate_css();
	}

	public function getDualButtonCss( $attributes, $device = '' ) {
		$css = [];
		$stack = isset( $attributes['stack'] ) ? $attributes['stack'] : '';

		$css['flex-wrap'] = 'wrap';
		if ( $stack === 'vertical' ) {
			$css['flex-direction'] = 'column';
			$css['display'] = 'flex';
		}

		if ( $stack === 'horizontal' ) {
			$css['flex-direction'] = 'row';
			$css['display'] = 'flex';
		}
		return array_merge(
			Range::get_css([
				'attributeValue' => $attributes['gap'],
				'attribute_object_key' => 'value',
				'isResponsive' => true,
				'defaultValue' => 20,
				'hasUnit' => true,
				'unitDefaultValue' => 'px',
				'property' => 'gap',
				'device' => $device,
			]),
			$css,
			isset( $attributes['alignment'] ) ? Alignment::get_css( $attributes['alignment'], $attributes['stack'] === 'horizontal' ? 'justify-content' : 'align-items', $device ) : [],
		);
	}
}
