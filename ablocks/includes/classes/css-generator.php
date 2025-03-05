<?php
namespace ABlocks\Classes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ABlocks\Classes\AssetsGenerator;

class CssGenerator {
	private $parent_class;
	private $class_styles = [];

	public function __construct( $attributes = [] ) {
		$this->parent_class = '.ablocks-block-' . $attributes['block_id'];
		// Alert - don't touch here
		if ( isset( $attributes['_margin'] ) ) { // check has advanced tab or not
			$this->add_class_styles(
				'{{WRAPPER}}',
				BlockGlobal::get_wrapper_css( $attributes ),
				BlockGlobal::get_wrapper_css( $attributes, 'Tablet' ),
				BlockGlobal::get_wrapper_css( $attributes, 'Mobile' )
			);
			$this->add_class_styles(
				'{{WRAPPER}}:hover',
				BlockGlobal::get_wrapper_hover_css( $attributes ),
				BlockGlobal::get_wrapper_hover_css( $attributes, 'Tablet' ),
				BlockGlobal::get_wrapper_hover_css( $attributes, 'Mobile' )
			);
			$this->add_class_styles(
				'{{WRAPPER}}.ablocks-hide-on-desktop,{{WRAPPER}}.ablocks-hide-on-tablet,{{WRAPPER}}.ablocks-hide-on-mobile',
				BlockGlobal::get_wrapper_device_responsive_css( $attributes ),
				BlockGlobal::get_wrapper_device_responsive_css( $attributes, 'Tablet' ),
				BlockGlobal::get_wrapper_device_responsive_css( $attributes, 'Mobile' )
			);
			$this->add_class_styles(
				'{{WRAPPER}}:hover > .ablocks-block-container',
				BlockGlobal::get_container_hover_css( $attributes ),
				BlockGlobal::get_container_hover_css( $attributes, 'Tablet' ),
				BlockGlobal::get_container_hover_css( $attributes, 'Mobile' )
			);
			$this->add_class_styles(
				'{{WRAPPER}} > .ablocks-block-container',
				BlockGlobal::get_container_css( $attributes ),
				BlockGlobal::get_container_css( $attributes, 'Tablet' ),
				BlockGlobal::get_container_css( $attributes, 'Mobile' )
			);
		}//end if
	}

	public function add_class_styles( $class_name, $desktop_styles, $tablet_styles = [], $mobile_styles = [] ) {
		$this->class_styles[] = [
			'class_name' => $class_name,
			'desktop_styles' => $desktop_styles,
			'tablet_styles' => $tablet_styles,
			'mobile_styles' => $mobile_styles
		];
	}

	public function generate_css() {
		$css_output = '';

		foreach ( $this->class_styles as $class_style ) {
			$desktop_css = AssetsGenerator::minify_css( $this->generate_css_for_media_query( 'desktop', $class_style['desktop_styles'] ) );
			$tablet_css = AssetsGenerator::minify_css( $this->generate_css_for_media_query( 'tablet', $class_style['tablet_styles'] ) );
			$mobile_css = AssetsGenerator::minify_css( $this->generate_css_for_media_query( 'mobile', $class_style['mobile_styles'] ) );

			$parent_selector = $this->get_parent_selector( $class_style['class_name'] );
			$css_blocks = [];

			$addToCssBlocks = function ( $mediaQuery, $max_width, $css ) use ( &$css_blocks, $parent_selector ) {
				if ( ! empty( $css ) ) {
					$css_blocks[] = ( '' !== $mediaQuery ) ? "@media screen and (max-width: $max_width) {\n$parent_selector {\n$css\n}\n}" : "$parent_selector {\n$css\n}";
				}
			};

			$addToCssBlocks( '', '', $desktop_css );
			$addToCssBlocks( 'tablet', $this->get_breakpoint( 'tablet' ), $tablet_css );
			$addToCssBlocks( 'mobile', $this->get_breakpoint( 'mobile' ), $mobile_css );

			$css_output .= implode( "\n\n", $css_blocks ) . "\n\n";
		}

		return preg_replace( '/\s+/', ' ', $css_output );
	}

	public function generate_css_for_media_query( $media_query, $styles ) {
		if ( empty( $styles ) ) {
			return '';
		}
		$css_string = implode("\n", array_map(
			function ( $property, $value ) {
				return "$property: $value;";
			},
			array_keys( $styles ),
			$styles
		));

		return $css_string . "\n";
	}

	public function get_breakpoint( $media_query ) {
		switch ( $media_query ) {
			case 'tablet':
				return '800px';
			case 'mobile':
				return '480px';
			default:
				return '1200px';
		}
	}

	public function get_parent_selector( $class_name ) {
		return $this->parent_class ? str_replace( '{{WRAPPER}}', $this->parent_class, $class_name ) : $class_name;
	}
}

