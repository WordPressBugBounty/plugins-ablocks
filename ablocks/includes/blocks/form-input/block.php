<?php
namespace ABlocks\Blocks\FormInput;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ABlocks\Classes\BlockBaseAbstract;
use ABlocks\Classes\CssGenerator;

class Block extends BlockBaseAbstract {
	protected $parent_block_name = 'form-builder';
	protected $block_name = 'form-input';

	public function build_css( $attributes ) {
		$css_generator = new CssGenerator( $attributes );
		$css_generator->add_class_styles(
			'{{WRAPPER}}',
			$this->get_wrapper_css( $attributes ),
			$this->get_wrapper_css( $attributes, 'Tablet' ),
			$this->get_wrapper_css( $attributes, 'Mobile' )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}}.ablocks-block--form-input',
			$this->get_input_block_main_wrapper( $attributes ),
			$this->get_input_block_main_wrapper( $attributes, 'Tablet' ),
			$this->get_input_block_main_wrapper( $attributes, 'Mobile' )
		);

		// Generate button icon CSS start
		$css_generator->add_class_styles(
			'{{WRAPPER}}  .ablocks-icon-wrap',
			$this->get_icon_wrapper_css( $attributes ),
			$this->get_icon_wrapper_css( $attributes, 'Tablet' ),
			$this->get_icon_wrapper_css( $attributes, 'Mobile' )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-form-builder__input-show-icon',
			$this->get_icon_space_css( $attributes ),
			$this->get_icon_space_css( $attributes, 'Tablet' ),
			$this->get_icon_space_css( $attributes, 'Mobile' )
		);

		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-form-builder__input-toggle-password .ablocks-icon-wrap ',
			$this->get_password_show_hide_icon_wrapper_css( $attributes )
		);
		return $css_generator->generate_css();
	}

	public function get_wrapper_css() {

		return [];

	}
	public function get_icon_wrapper_css( $attributes, $device = '' ) {
		$css = [];

		// Check for 'inputIconSize' in the attributes and set font-size
		if ( isset( $attributes['inputIconSize'] ) ) {
			$css['font-size'] = $attributes['inputIconSize'] . 'px';
		}

		// Check for 'iconColor' in the attributes and set fill color
		if ( isset( $attributes['iconColor'] ) ) {
			$css['fill'] = $attributes['iconColor'];
		}

		return $css;
	}
	public function get_icon_space_css( $attributes, $device = '' ) {
		$css = [];

		// Check for 'inputIconSpace' in the attributes and set padding-left
		if ( isset( $attributes['inputIconSpace'] ) ) {
			$css['padding-left'] = $attributes['inputIconSpace'] . 'px';
		}

		return $css;
	}

	public function get_input_block_main_wrapper( $attributes, $device = '' ) {
		$css = [];
		$css['display'] = 'inline-block';
		$css['box-sizing'] = 'border-box';
		$css['padding'] = '0px 2px';

		if ( isset( $attributes['inputWidth'] ) && is_numeric( $attributes['inputWidth'] ) ) {
			$css['width'] = ( $attributes['inputWidth'] - 1 ) . '%';
		} else {
			// Handle case where inputWidth is not set or not a number
			$css['width'] = '100%'; // or some default value
		}

		return $css;
	}

	public function get_password_show_hide_icon_wrapper_css( $attributes ) {
		$password_show_hide_icon_wrapper_css = [];

		if ( isset( $attributes['passwordShowHideIconSize'] ) ) {
			$password_show_hide_icon_wrapper_css['font-size'] = $attributes['passwordShowHideIconSize'] . 'px';
		}

		return $password_show_hide_icon_wrapper_css;
	}
}
