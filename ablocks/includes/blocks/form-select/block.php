<?php
namespace ABlocks\Blocks\FormSelect;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ABlocks\Classes\BlockBaseAbstract;
use ABlocks\Classes\CssGenerator;

class Block extends BlockBaseAbstract {
	protected $parent_block_name = 'form-builder';
	protected $block_name = 'form-select';

	public function build_css( $attributes ) {
		$css_generator = new CssGenerator( $attributes );
		$css_generator->add_class_styles(
			'{{WRAPPER}}',
			$this->get_wrapper_css( $attributes ),
			$this->get_wrapper_css( $attributes, 'Tablet' ),
			$this->get_wrapper_css( $attributes, 'Mobile' )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}}.ablocks-block--form-select',
			$this->get_input_block_main_wrapper( $attributes ),
			$this->get_input_block_main_wrapper( $attributes, 'Tablet' ),
			$this->get_input_block_main_wrapper( $attributes, 'Mobile' ),
		);
		return $css_generator->generate_css();
	}
	public function get_wrapper_css() {
		return [];
	}
	public function get_input_block_main_wrapper( $attributes, $device = '' ) {
		$css = [];
		$css['display'] = 'inline-block';
		$css['box-sizing'] = 'border-box';
		$css['padding'] = '0px 2px';
		if ( isset( $attributes['inputWidth'] ) ) {
			$css['width'] = ( $attributes['inputWidth'] - 1 ) . '%';
		}

		return $css;
	}
}
