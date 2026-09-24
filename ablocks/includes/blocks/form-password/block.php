<?php
namespace ABlocks\Blocks\FormPassword;

use ABlocks\Controls\Dimensions;
use ABlocks\Controls\Border;
use ABlocks\Controls\Range;
use ABlocks\Controls\Color;


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ABlocks\Classes\BlockBaseAbstract;
use ABlocks\Classes\CssGenerator;

class Block extends BlockBaseAbstract {
	protected $parent_block_name = 'form-builder';
	protected $block_name = 'form-password';

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
			'{{WRAPPER}}.ablocks-block--form-password',
			$this->get_input_block_main_wrapper( $attributes ),
			$this->get_input_block_main_wrapper( $attributes, 'Tablet' ),
			$this->get_input_block_main_wrapper( $attributes, 'Mobile' ),
		);

		// Generate button icon CSS start
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-form-builder__input-icon .ablocks-icon-wrap',
			$this->get_icon_wrapper_css( $attributes ),
			$this->get_icon_wrapper_css( $attributes, 'Tablet' ),
			$this->get_icon_wrapper_css( $attributes, 'Mobile' ),
			$css_generator->custom_device_map( function ( $device ) use ( $attributes ) { return $this->get_icon_wrapper_css( $attributes, $device ); } )
		);

		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-form-builder__input-show-icon',
			$this->get_icon_space_css( $attributes ),
			$this->get_icon_space_css( $attributes, 'Tablet' ),
			$this->get_icon_space_css( $attributes, 'Mobile' ),
			$css_generator->custom_device_map( function ( $device ) use ( $attributes ) { return $this->get_icon_space_css( $attributes, $device ); } )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-form-builder__input-toggle-password .ablocks-icon-wrap ',
			$this->get_password_show_hide_icon_wrapper_css( $attributes )
		);
		return $css_generator->generate_css();
	}

	public function get_wrapper_css() {
		// phpcs:ignore Squiz.PHP.NonExecutableCode.ReturnNotRequired
		return;
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

	public function get_icon_wrapper_css( $attributes, $device = '' ) {
		$css = [];
		if ( isset( $attributes['inputIconSize'] ) ) {
			$css['font-size'] = $attributes['inputIconSize'] . 'px';
		}
		return array_merge(
			$css,
			[ 'fill' => Color::get_css( isset( $attributes['iconColor'] ) ? $attributes['iconColor'] : '' ) ]
		);
	}
	public function get_icon_space_css( $attributes, $device = '' ) {
		$css = [];
		if ( isset( $attributes['inputIconSpace'] ) ) {
			$css['padding-left'] = $attributes['inputIconSpace'] . 'px';
		}

		return $css;
	}
	public function get_password_show_hide_icon_wrapper_css( $attributes ) {
		$password_show_hide_icon_wrapper_css = [];

		if ( isset( $attributes['passwordShowHideIconSize'] ) ) {
			$password_show_hide_icon_wrapper_css['font-size'] = $attributes['passwordShowHideIconSize'] . 'px';
		}
		return array_merge(
			[ 'fill' => Color::get_css( isset( $attributes['passwordIconColor'] ) ? $attributes['passwordIconColor'] : '' ) ],
			$password_show_hide_icon_wrapper_css
		);
	}
}
