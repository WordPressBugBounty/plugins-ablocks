<?php
namespace ABlocks\Blocks\MenuItem;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ABlocks\Classes\BlockBaseAbstract;
use ABlocks\Classes\CssGenerator;
use ABlocks\Controls\Color;
use ABlocks\Controls\Icon;
use ABlocks\Controls\Range;

class Block extends BlockBaseAbstract {
	protected $parent_block_name = 'menu';
	protected $block_name = 'menu-item';

	public function build_css( $attributes ) {
		// Generate CSS
		$css_generator = new CssGenerator( $attributes );

		if ( ! empty( $attributes['showIcon'] ) ) {
			// Child combinators keep this item's icon styles out of the menu
			// items nested in its submenu.
			$icon_wrap = '{{WRAPPER}} > .ablocks-menu-item__link > .ablocks-icon-wrap';
			$icon_wrap_hover = '{{WRAPPER}}:hover > .ablocks-menu-item__link > .ablocks-icon-wrap';
			$styles = [
				'{{WRAPPER}} > .ablocks-menu-item__link' => [ $this, 'get_link_css' ],
				$icon_wrap => [ Icon::class, 'get_wrapper_css' ],
				$icon_wrap . ' svg.ablocks-svg-icon' => [ $this, 'get_icon_css' ],
				$icon_wrap . ' img.ablocks-image-icon' => [ Icon::class, 'get_element_image_css' ],
				$icon_wrap_hover . ' svg.ablocks-svg-icon, ' . $icon_wrap_hover . ' img.ablocks-image-icon' => [ Icon::class, 'get_element_image_hover_css' ],
			];
			foreach ( $styles as $selector => $get_css ) {
				$css_generator->add_class_styles(
					$selector,
					call_user_func( $get_css, $attributes ),
					call_user_func( $get_css, $attributes, 'Tablet' ),
					call_user_func( $get_css, $attributes, 'Mobile' ),
					$css_generator->custom_device_map( function ( $device ) use ( $attributes, $get_css ) { return call_user_func( $get_css, $attributes, $device ); } )
				);
			}
		}

		return $css_generator->generate_css();
	}

	public function get_link_css( $attributes, $device = '' ) {
		return Range::get_css([
			'attributeValue' => $attributes['iconSpace'],
			'attribute_object_key' => 'value',
			'isResponsive' => true,
			'defaultValue' => 8,
			'hasUnit' => true,
			'unitDefaultValue' => 'px',
			'property' => 'column-gap',
			'device' => $device,
		]);
	}

	// Without an icon color the icon follows the link text color, so the Menu
	// block's normal/hover/active link colors apply to it too.
	public function get_icon_css( $attributes, $device = '' ) {
		$color = Color::get_css( isset( $attributes['iconColor'] ) ? $attributes['iconColor'] : '' );
		return array_merge(
			Icon::get_element_css( $attributes, $device ),
			[ 'fill' => $color ? $color : 'currentColor' ]
		);
	}
}
