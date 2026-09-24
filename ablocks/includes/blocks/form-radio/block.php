<?php
namespace ABlocks\Blocks\FormRadio;

use ABlocks\Controls\Range;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ABlocks\Classes\BlockBaseAbstract;
use ABlocks\Classes\CssGenerator;

class Block extends BlockBaseAbstract {
	protected $parent_block_name = 'form-builder';
	protected $block_name = 'form-radio';

	public function build_css( $attributes ) {
		$css_generator = new CssGenerator( $attributes );
		$css_generator->add_class_styles(
			'{{WRAPPER}}',
			$this->get_wrapper_css( $attributes ),
			$this->get_wrapper_css( $attributes, 'Tablet' ),
			$this->get_wrapper_css( $attributes, 'Mobile' ),
			$css_generator->custom_device_map( function ( $device ) use ( $attributes ) { return $this->get_wrapper_css( $attributes, $device ); } )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}}.ablocks-block--form-radio',
			$this->get_input_block_main_wrapper( $attributes ),
			$this->get_input_block_main_wrapper( $attributes, 'Tablet' ),
			$this->get_input_block_main_wrapper( $attributes, 'Mobile' ),
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-form-builder__radio-option',
			$this->get_radio_dynamic_width_css( $attributes ),
			$this->get_radio_dynamic_width_css( $attributes, 'Tablet' ),
			$this->get_radio_dynamic_width_css( $attributes, 'Mobile' ),
			$css_generator->custom_device_map( function ( $device ) use ( $attributes ) { return $this->get_radio_dynamic_width_css( $attributes, $device ); } )
		);
		return $css_generator->generate_css();
	}
	public function get_radio_dynamic_width_css( $attributes, $device = '' ) {
		$css = [];
		if ( isset( $attributes['optionWidth'] ) ) {
			$css['width'] = ( $attributes['optionWidth'] - 6 ) . '%';
		}
		return $css;
	}
	public function get_wrapper_css() {
		return [];
	}
	public function get_input_block_main_wrapper( $attributes, $device = '' ) {
		$css = [];
		$css['box-sizing'] = 'border-box';

		// The device's own width, else the nearest containing wider device's
		// (custom breakpoints included), else Desktop's.
		$input_width = isset( $attributes['inputWidth'] ) ? (array) $attributes['inputWidth'] : [];
		$widthValue  = \ABlocks\Helper::get_responsive_value( $input_width, 'value', $device );
		$widthValue  = false === $widthValue ? 100 : $widthValue;
		$widthUnit   = \ABlocks\Helper::get_responsive_value( $input_width, 'valueUnit', $device );
		$widthUnit   = false === $widthUnit ? '%' : $widthUnit;

		if ( is_numeric( $widthValue ) ) {
			$widthValue = max( 0, floatval( $widthValue ) - 1 );
		}

		$css['width'] = $widthValue . $widthUnit;

		return $css;
	}
}
