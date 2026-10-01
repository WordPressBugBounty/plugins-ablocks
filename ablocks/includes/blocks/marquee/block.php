<?php
namespace ABlocks\Blocks\Marquee;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ABlocks\Classes\BlockBaseAbstract;
use ABlocks\Classes\CssGenerator;
use ABlocks\Classes\CssGeneratorV2;
use ABlocks\Controls\Range;


class Block extends BlockBaseAbstract {
	protected $block_name = 'marquee';

	public function build_css_v1( $attributes ) {
		// Generate CSS start
		$css_generator = new CssGenerator( $attributes, $this->block_name );

		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-block-marquee .ablocks-block-marquee__children',
			$this->get_inner_block_css( $attributes ),
			$this->get_inner_block_css( $attributes, 'Tablet' ),
			$this->get_inner_block_css( $attributes, 'Mobile' ),
			$css_generator->custom_device_map( function ( $device ) use ( $attributes ) { return $this->get_inner_block_css( $attributes, $device ); } )
		);

		$css_generator->add_class_styles(
			'{{WRAPPER}}',
			$this->get_marquee_height_css( $attributes ),
			$this->get_marquee_height_css( $attributes, 'Tablet' ),
			$this->get_marquee_height_css( $attributes, 'Mobile' ),
			$css_generator->custom_device_map( function ( $device ) use ( $attributes ) { return $this->get_marquee_height_css( $attributes, $device ); } )
		);

		return $css_generator->generate_css();
	}
	public function build_css_v2( $attributes ) {
		// Generate CSS start
		$css_generator = new CssGeneratorV2( $attributes, $this->block_name );

		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-block-marquee .ablocks-block-marquee__children',
			$this->get_inner_block_css( $attributes ),
			$this->get_inner_block_css( $attributes, 'Tablet' ),
			$this->get_inner_block_css( $attributes, 'Mobile' ),
			$css_generator->custom_device_map( function ( $device ) use ( $attributes ) { return $this->get_inner_block_css( $attributes, $device ); } )
		);

		$css_generator->add_class_styles(
			'{{WRAPPER}}',
			$this->get_marquee_height_css( $attributes ),
			$this->get_marquee_height_css( $attributes, 'Tablet' ),
			$this->get_marquee_height_css( $attributes, 'Mobile' ),
			$css_generator->custom_device_map( function ( $device ) use ( $attributes ) { return $this->get_marquee_height_css( $attributes, $device ); } )
		);

		return $css_generator->generate_css();
	}

	public function build_css( $attributes ) {
		if ( isset( $attributes['blockVersion'] ) && (int) $attributes['blockVersion'] === 2 ) {
			return $this->build_css_v2( $attributes );
		}
		return $this->build_css_v1( $attributes );
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
				'isResponsive'         => true,
				'defaultValue'         => 12,
				'hasUnit'              => false,
				'unitDefaultValue'     => 'px',
				'property'             => 'gap',
				'device'               => $device,
			]),
			$css
		);
	}

	/**
	 * Viewport height of an Up/Down marquee, read by style.css through
	 * --ablocks-marquee-height (falls back to 200px there). Mirrors
	 * getMarqueeHeightCSS() in src/blocks/marquee/styling.js.
	 */
	public function get_marquee_height_css( $attributes, $device = '' ) {
		$direction = isset( $attributes['marqueeDirection'] ) ? $attributes['marqueeDirection'] : '';
		if ( 'up' !== $direction && 'down' !== $direction ) {
			return [];
		}
		return Range::get_css([
			'attributeValue'     => isset( $attributes['marqueeHeight'] ) ? $attributes['marqueeHeight'] : [],
			'attributeObjectKey' => 'value',
			'isResponsive'       => true,
			'defaultValue'       => 200,
			'hasUnit'            => true,
			'unitDefaultValue'   => 'px',
			'property'           => '--ablocks-marquee-height',
			'device'             => $device,
		]);
	}

}
