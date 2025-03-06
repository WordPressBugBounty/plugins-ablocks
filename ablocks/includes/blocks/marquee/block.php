<?php
namespace ABlocks\Blocks\Marquee;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ABlocks\Classes\BlockBaseAbstract;
use ABlocks\Classes\CssGenerator;
use ABlocks\Controls\Range;


class Block extends BlockBaseAbstract {
	protected $block_name = 'marquee';

	public function build_css( $attributes ) {
		// Generate CSS start
		$css_generator = new CssGenerator( $attributes, $this->block_name );

		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-block-marquee__children-child',
			$this->get_inner_block_css( $attributes ),
			$this->get_inner_block_css( $attributes, 'Tablet' ),
			$this->get_inner_block_css( $attributes, 'Mobile' )
		);

		return $css_generator->generate_css();
	}

	public function get_inner_block_css( $attributes, $device = '' ) {
		$css = [];
		if ( ! empty( $attributes['gap'][ 'value' . $device ] ) ) {
			$gap_value = $attributes['gap'][ 'value' . $device ];
			$gap_unit = ! empty( $attributes['gap'][ 'valueUnit' . $device ] )
				? $attributes['gap'][ 'valueUnit' . $device ]
				: 'px';

			$css['gap'] = $gap_value . $gap_unit;
		}
		return array_merge(
			Range::get_css([
				'attributeValue'       => $attributes['gap'],
				'attribute_object_key' => 'value',
				'isResponsive'         => false,
				'defaultValue'         => 12,
				'hasUnit'              => false,
				'unitDefaultValue'     => 'px',
				'property'             => 'gap',
				'device'               => $device,
			]),
			$css
		);
	}

}

