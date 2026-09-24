<?php
namespace ABlocks\Blocks\Menu;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ABlocks\Controls\Typography;
use ABlocks\Controls\Dimensions;
use ABlocks\Controls\Border;
use ABlocks\Classes\BlockBaseAbstract;
use ABlocks\Classes\CssGenerator;
use ABlocks\Controls\Range;
use ABlocks\Controls\BoxShadow;
use ABlocks\Classes\CssGeneratorV2;
use ABlocks\Controls\Color;
use ABlocks\Controls\Alignment;

class Block extends BlockBaseAbstract {
	protected $block_name = 'menu';

	public function build_css_v1( $attributes ) {
		$css_generator = new CssGenerator( $attributes, $this->block_name );
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-menu',
			$this->get_menu_css( $attributes, '' ),
			$this->get_menu_css( $attributes, 'Tablet' ),
			$this->get_menu_css( $attributes, 'Mobile' ),
			$css_generator->custom_device_map( function ( $device ) use ( $attributes ) { return $this->get_menu_css( $attributes, $device ); } )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}}.ablocks-menu',
			$this->get_menu_css( $attributes, '' ),
			$this->get_menu_css( $attributes, 'Tablet' ),
			$this->get_menu_css( $attributes, 'Mobile' ),
			$css_generator->custom_device_map( function ( $device ) use ( $attributes ) { return $this->get_menu_css( $attributes, $device ); } )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-main-menu > .ablocks-menu-item',
			$this->get_menu_item_css( $attributes ),
			$this->get_menu_item_css( $attributes, 'Tablet' ),
			$this->get_menu_item_css( $attributes, 'Mobile' ),
			$css_generator->custom_device_map( function ( $device ) use ( $attributes ) { return $this->get_menu_item_css( $attributes, $device ); } )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-main-menu > .ablocks-menu-item .ablocks-menu-item__link',
			$this->get_menu_item_css( $attributes ),
			$this->get_menu_item_css( $attributes, 'Tablet' ),
			$this->get_menu_item_css( $attributes, 'Mobile' ),
			$css_generator->custom_device_map( function ( $device ) use ( $attributes ) { return $this->get_menu_item_css( $attributes, $device ); } )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-main-menu > .ablocks-menu-item:hover',
			$this->get_menu_item_hover_css( $attributes, '' ),
			$this->get_menu_item_hover_css( $attributes, 'Tablet' ),
			$this->get_menu_item_hover_css( $attributes, 'Mobile' ),
			$css_generator->custom_device_map( function ( $device ) use ( $attributes ) { return $this->get_menu_item_hover_css( $attributes, $device ); } )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-main-menu > .ablocks-menu-item > .ablocks-menu-item__link:hover',
			$this->get_menu_item_hover_css( $attributes, '' ),
			$this->get_menu_item_hover_css( $attributes, 'Tablet' ),
			$this->get_menu_item_hover_css( $attributes, 'Mobile' ),
			$css_generator->custom_device_map( function ( $device ) use ( $attributes ) { return $this->get_menu_item_hover_css( $attributes, $device ); } )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-block--menu-child-sub',
			$this->get_sub_menu_css( $attributes, '' ),
			$this->get_sub_menu_css( $attributes, 'Tablet' ),
			$this->get_sub_menu_css( $attributes, 'Mobile' ),
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-block--menu-child-sub:hover',
			$this->get_sub_menu_hover_css( $attributes, '' ),
			$this->get_sub_menu_hover_css( $attributes, 'Tablet' ),
			$this->get_sub_menu_hover_css( $attributes, 'Mobile' ),
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-block--menu-child-sub .ablocks-block--menu-item',
			$this->get_subMenu_responsive_css( $attributes, '' ),
			$this->get_subMenu_responsive_css( $attributes, 'Tablet' ),
			$this->get_subMenu_responsive_css( $attributes, 'Mobile' ),
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-block--menu-child-sub .ablocks-block--menu-item:hover',
			$this->get_subMenu_responsive_hover_css( $attributes, '' ),
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-block--menu-child-sub .ablocks-block--menu-item .ablocks-menu-item__link',
			$this->get_subMenu_responsive_text_css( $attributes, '' ),
			$this->get_subMenu_responsive_text_css( $attributes, 'Tablet' ),
			$this->get_subMenu_responsive_text_css( $attributes, 'Mobile' ),
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-menu-child-mega',
			$this->getMegaMenuCSS( $attributes ),
			$this->getMegaMenuCSS( $attributes, 'Tablet' ),
			$this->getMegaMenuCSS( $attributes, 'Mobile' ),
			$css_generator->custom_device_map( function ( $device ) use ( $attributes ) { return $this->getMegaMenuCSS( $attributes, $device ); } )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-main-menu > .ablocks-menu-item > .ablocks-menu-item__link',
			$this->get_menu_item_link_css( $attributes, '' ),
			$this->get_menu_item_link_css( $attributes, 'Tablet' ),
			$this->get_menu_item_link_css( $attributes, 'Mobile' ),
			$css_generator->custom_device_map( function ( $device ) use ( $attributes ) { return $this->get_menu_item_link_css( $attributes, $device ); } )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-main-menu > .ablocks-menu-item:hover > .ablocks-menu-item__link ',
			$this->get_menu_item_link_hover_css( $attributes, '' ),
		);
		$css_generator->add_class_styles( '{{WRAPPER}} .ablocks-main-menu  > .ablocks-menu-item > .ablocks-menu-item__dropdown-icon svg',
			$this->get_menu_item_dropdown_icon_css( $attributes ),
			$this->get_menu_item_dropdown_icon_css( $attributes, 'Tablet' ),
			$this->get_menu_item_dropdown_icon_css( $attributes, 'Mobile' ),
			$css_generator->custom_device_map( function ( $device ) use ( $attributes ) { return $this->get_menu_item_dropdown_icon_css( $attributes, $device ); } )
		);
		$css_generator->add_class_styles( '{{WRAPPER}} .ablocks-main-menu  > .ablocks-menu-item:hover > .ablocks-menu-item__dropdown-icon svg', $this->get_menu_item_dropdown_icon_hover_css( $attributes ) );
		$css_generator->add_class_styles( '{{WRAPPER}} .ablocks-menu__trigger-wrapper', $this->get_hamburger_menu_wrapper_css( $attributes ) );
		$css_generator->add_class_styles( '{{WRAPPER}} .ablocks-menu__trigger-wrapper .ablocks-menu__trigger ',
			$this->get_hamburger_menu_css( $attributes ),
			$this->get_hamburger_menu_css( $attributes, 'Tablet' ),
			$this->get_hamburger_menu_css( $attributes, 'Mobile' ),
		);
		$css_generator->add_class_styles( '{{WRAPPER}} .ablocks-menu__trigger-wrapper .ablocks-menu__trigger:hover ',
			$this->get_hamburgerHoverCSS( $attributes ),
			$this->get_hamburgerHoverCSS( $attributes, 'Tablet' ),
			$this->get_hamburgerHoverCSS( $attributes, 'Mobile' ),
		);
		$css_generator->add_class_styles( '{{WRAPPER}} .ablocks-menu__trigger-wrapper .ablocks-menu__trigger .ablocks-menu__trigger-item ', $this->get_hamburger_menu_item_css( $attributes ) );

		return $css_generator->generate_css();
	}
	public function build_css_v2( $attributes ) {
		$css_generator = new CssGeneratorV2( $attributes, $this->block_name );
		// Wrapper CSS
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-menu',
			$this->get_menu_css( $attributes, '' ),
			$this->get_menu_css( $attributes, 'Tablet' ),
			$this->get_menu_css( $attributes, 'Mobile' ),
			$css_generator->custom_device_map( function ( $device ) use ( $attributes ) { return $this->get_menu_css( $attributes, $device ); } )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}}.ablocks-menu',
			$this->get_menu_css( $attributes, '' ),
			$this->get_menu_css( $attributes, 'Tablet' ),
			$this->get_menu_css( $attributes, 'Mobile' ),
			$css_generator->custom_device_map( function ( $device ) use ( $attributes ) { return $this->get_menu_css( $attributes, $device ); } )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-main-menu > .ablocks-menu-item',
			$this->get_menu_item_css( $attributes ),
			$this->get_menu_item_css( $attributes, 'Tablet' ),
			$this->get_menu_item_css( $attributes, 'Mobile' ),
			$this->get_menu_tablet_hamburger_css( $attributes, $this->get_menu_responsive_background_only_css( $attributes ) ),
			$css_generator->custom_device_map( function ( $device ) use ( $attributes ) { return $this->get_menu_item_css( $attributes, $device ); } )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-main-menu > .ablocks-menu-item .ablocks-menu-item__link',
			$this->get_menu_item_css( $attributes ),
			$this->get_menu_item_css( $attributes, 'Tablet' ),
			$this->get_menu_item_css( $attributes, 'Mobile' ),
			$this->get_menu_tablet_hamburger_css( $attributes, $this->get_menu_responsive_background_only_css( $attributes ) ),
			$css_generator->custom_device_map( function ( $device ) use ( $attributes ) { return $this->get_menu_item_css( $attributes, $device ); } )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-main-menu > .ablocks-menu-item:hover',
			$this->get_menu_item_hover_css( $attributes, '' ),
			$this->get_menu_item_hover_css( $attributes, 'Tablet' ),
			$this->get_menu_item_hover_css( $attributes, 'Mobile' ),
			$css_generator->custom_device_map( function ( $device ) use ( $attributes ) { return $this->get_menu_item_hover_css( $attributes, $device ); } )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-main-menu > .ablocks-menu-item .ablocks-menu-item__link:hover',
			$this->get_menu_item_hover_css( $attributes, '' ),
			$this->get_menu_item_hover_css( $attributes, 'Tablet' ),
			$this->get_menu_item_hover_css( $attributes, 'Mobile' ),
			$css_generator->custom_device_map( function ( $device ) use ( $attributes ) { return $this->get_menu_item_hover_css( $attributes, $device ); } )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-block--menu-child-sub',
			$this->get_sub_menu_css( $attributes, '' ),
			$this->get_sub_menu_css( $attributes, 'Tablet' ),
			$this->get_sub_menu_css( $attributes, 'Mobile' ),
			$css_generator->custom_device_map( function ( $device ) use ( $attributes ) { return $this->get_sub_menu_css( $attributes, $device ); } )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-block--menu-child-sub:hover',
			$this->get_sub_menu_hover_css( $attributes, '' ),
			$this->get_sub_menu_hover_css( $attributes, 'Tablet' ),
			$this->get_sub_menu_hover_css( $attributes, 'Mobile' ),
			$css_generator->custom_device_map( function ( $device ) use ( $attributes ) { return $this->get_sub_menu_hover_css( $attributes, $device ); } )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-block--menu-child-sub .ablocks-block--menu-item',
			$this->get_subMenu_responsive_css( $attributes, '' ),
			$this->get_subMenu_responsive_css( $attributes, 'Tablet' ),
			$this->get_subMenu_responsive_css( $attributes, 'Mobile' ),
			$css_generator->custom_device_map( function ( $device ) use ( $attributes ) { return $this->get_subMenu_responsive_css( $attributes, $device ); } )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-block--menu-child-sub .ablocks-block--menu-item:hover',
			$this->get_subMenu_responsive_hover_css( $attributes, '' ),
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-block--menu-child-sub .ablocks-block--menu-item .ablocks-menu-item__link',
			$this->get_subMenu_responsive_text_css( $attributes, '' ),
			$this->get_subMenu_responsive_text_css( $attributes, 'Tablet' ),
			$this->get_subMenu_responsive_text_css( $attributes, 'Mobile' ),
			$css_generator->custom_device_map( function ( $device ) use ( $attributes ) { return $this->get_subMenu_responsive_text_css( $attributes, $device ); } )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-block--menu-child-sub .ablocks-menu-item:hover .ablocks-menu-item__link',
			$this->get_subMenu__text_hover_css( $attributes )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-menu-child-mega',
			$this->getMegaMenuCSS( $attributes ),
			$this->getMegaMenuCSS( $attributes, 'Tablet' ),
			$this->getMegaMenuCSS( $attributes, 'Mobile' ),
			$css_generator->custom_device_map( function ( $device ) use ( $attributes ) { return $this->getMegaMenuCSS( $attributes, $device ); } )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-main-menu > .ablocks-menu-item > .ablocks-menu-item__link',
			$this->get_menu_item_link_css( $attributes, '' ),
			$this->get_menu_item_link_css( $attributes, 'Tablet' ),
			$this->get_menu_item_link_css( $attributes, 'Mobile' ),
			$this->get_menu_tablet_hamburger_css( $attributes, $this->get_menu_item_link_css( $attributes, 'Mobile' ) ),
			$css_generator->custom_device_map( function ( $device ) use ( $attributes ) { return $this->get_menu_item_link_css( $attributes, $device ); } )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-main-menu > .ablocks-menu-item:hover > .ablocks-menu-item__link ',
			$this->get_menu_item_link_hover_css( $attributes, '' ),
		);
		$css_generator->add_class_styles( '{{WRAPPER}} .ablocks-main-menu  > .ablocks-menu-item > .ablocks-menu-item__dropdown-icon svg',
			$this->get_menu_item_dropdown_icon_css( $attributes ),
			$this->get_menu_item_dropdown_icon_css( $attributes, 'Tablet' ),
			$this->get_menu_item_dropdown_icon_css( $attributes, 'Mobile' ),
			$this->get_menu_tablet_hamburger_css( $attributes, $this->get_menu_item_dropdown_icon_css( $attributes, 'Mobile' ) ),
			$css_generator->custom_device_map( function ( $device ) use ( $attributes ) { return $this->get_menu_item_dropdown_icon_css( $attributes, $device ); } )
		);
		$css_generator->add_class_styles( '{{WRAPPER}} .ablocks-main-menu  > .ablocks-menu-item:hover > .ablocks-menu-item__dropdown-icon svg', $this->get_menu_item_dropdown_icon_hover_css( $attributes ) );
		$css_generator->add_class_styles( '{{WRAPPER}} .ablocks-menu__trigger-wrapper', $this->get_hamburger_menu_wrapper_css( $attributes ) );
		$css_generator->add_class_styles( '{{WRAPPER}} .ablocks-menu__trigger-wrapper .ablocks-menu__trigger ',
			$this->get_hamburger_menu_css( $attributes ),
			$this->get_hamburger_menu_css( $attributes, 'Tablet' ),
			$this->get_hamburger_menu_css( $attributes, 'Mobile' ),
			$css_generator->custom_device_map( function ( $device ) use ( $attributes ) { return $this->get_hamburger_menu_css( $attributes, $device ); } )
		);
		$css_generator->add_class_styles( '{{WRAPPER}} .ablocks-menu__trigger-wrapper .ablocks-menu__trigger:hover ',
			$this->get_hamburgerHoverCSS( $attributes ),
			$this->get_hamburgerHoverCSS( $attributes, 'Tablet' ),
			$this->get_hamburgerHoverCSS( $attributes, 'Mobile' ),
			$css_generator->custom_device_map( function ( $device ) use ( $attributes ) { return $this->get_hamburgerHoverCSS( $attributes, $device ); } )
		);
		$css_generator->add_class_styles( '{{WRAPPER}} .ablocks-menu__trigger-wrapper .ablocks-menu__trigger .ablocks-menu__trigger-item ',
			$this->get_hamburger_menu_item_css( $attributes )
		);
		// menu active syle
		$css_generator->add_class_styles( '{{WRAPPER}} .ablocks-main-menu .ablocks-menu-item.ablocks-block--menu-item-active',
			$this->get_menu_active_styles_css( $attributes ),
			$this->get_menu_active_styles_css( $attributes, 'Tablet' ),
			$this->get_menu_active_styles_css( $attributes, 'Mobile' ),
			$css_generator->custom_device_map( function ( $device ) use ( $attributes ) { return $this->get_menu_active_styles_css( $attributes, $device ); } )
		);
		$css_generator->add_class_styles( '{{WRAPPER}} .ablocks-main-menu .ablocks-menu-item.ablocks-block--menu-item-active > .ablocks-menu-item__link',
			[ 'color' => $this->get_menu_active_color_value( $attributes, '' ) ],
			[ 'color' => $this->get_menu_active_color_value( $attributes, 'Tablet' ) ],
			[ 'color' => $this->get_menu_active_color_value( $attributes, 'Mobile' ) ],
			$css_generator->custom_device_map( function ( $device ) use ( $attributes ) { return [ 'color' => $this->get_menu_active_color_value( $attributes, $device ) ]; } )
		);
		$css_generator->add_class_styles( '{{WRAPPER}} .ablocks-main-menu .ablocks-menu-item.ablocks-block--menu-item-active > .ablocks-menu-item__dropdown-icon svg',
			[ 'fill' => $this->get_menu_active_color_value( $attributes, '' ) ],
			[ 'fill' => $this->get_menu_active_color_value( $attributes, 'Tablet' ) ],
			[ 'fill' => $this->get_menu_active_color_value( $attributes, 'Mobile' ) ],
			$css_generator->custom_device_map( function ( $device ) use ( $attributes ) { return [ 'fill' => $this->get_menu_active_color_value( $attributes, $device ) ]; } )
		);
		return $css_generator->generate_css();
	}

	private function get_menu_css( $attributes, $device = '' ) {
		$css = [];
		$padding = isset( $attributes['padding'] ) ? $attributes['padding'] : '';

		$alignment = isset( $attributes['alignment'] ) ? $attributes['alignment'] : '';
		if ( is_string( $alignment ) ) {
			// `alignment` became a responsive object; a menu saved before that
			// still has the old plain string, which only ever applied to desktop.
			if ( '' === $device && '' !== $alignment ) {
				$css['justify-content'] = $alignment;
			}
		} else {
			$css = array_merge( $css, Alignment::get_css( $alignment, 'justify-content', $device ) );
		}

		return array_merge(
			Dimensions::get_css( $padding, 'padding', $device ),
			$css
		);
	}
	public function build_css( $attributes ) {
		if ( isset( $attributes['blockVersion'] ) && (int) $attributes['blockVersion'] === 2 ) {
			return $this->build_css_v2( $attributes );
		}
		return $this->build_css_v1( $attributes );
	}



	private function get_menu_item_css( $attributes, $device = '' ) {
		$menu_border_css = ! empty( $attributes['menuItemBorder'] ) ? Border::get_css( $attributes['menuItemBorder'], '', $device ) : array();
		$typographyValueGlobal = ! empty( $attributes['menuItemTypographyGlobal'] ) ? $attributes['menuItemTypographyGlobal'] : array();
		$css = array_merge(
			$menu_border_css,
			isset( $attributes['menuItemTypography'] ) ? Typography::get_css( $attributes['menuItemTypography'], '', $device, $typographyValueGlobal ) : [],
			Dimensions::get_css( isset( $attributes['menuItemPadding'] ) ? $attributes['menuItemPadding'] : [], 'padding', $device ),
			Dimensions::get_css( isset( $attributes['menuItemMargin'] ) ? $attributes['menuItemMargin'] : [], 'margin', $device )
		);

		if ( isset( $attributes[ 'menuItemDirection' . $device ] ) && ! empty( $attributes[ 'menuItemDirection' . $device ] ) ) {
			$css['flex-direction'] = $attributes[ 'menuItemDirection' . $device ];
		}

		// Justify content
		if ( isset( $attributes[ 'menuItemJustify' . $device ] ) && ! empty( $attributes[ 'menuItemJustify' . $device ] ) ) {
			$css['justify-content'] = $attributes[ 'menuItemJustify' . $device ];
		}

		// Align items
		if ( isset( $attributes[ 'menuItemAlign' . $device ] ) && ! empty( $attributes[ 'menuItemAlign' . $device ] ) ) {
			$css['align-items'] = $attributes[ 'menuItemAlign' . $device ];
		}
		if ( ! empty( $attributes['menuItemTransition'] ) ) {
			$css['transition'] = $attributes['menuItemTransition'] . 's';
		}
		if (
			( $attributes['sideBarMenuDevice'] ?? null ) === 'tablet' && in_array( $device, [ 'Tablet', 'Mobile' ], true ) ||
			( ( $attributes['sideBarMenuDevice'] ?? null ) !== 'tablet' && $device === 'Mobile' )
		) {
				$css['background'] = Color::get_css( isset( $attributes['menuResponsiveBackground'] ) ? $attributes['menuResponsiveBackground'] : '' );
		}
		return array_merge(
			[ 'color' => Color::get_css( isset( $attributes['menuItemTextColor'] ) ? $attributes['menuItemTextColor'] : '' ) ],
			[ 'background' => Color::get_css( isset( $attributes['menuItemBackground'] ) ? $attributes['menuItemBackground'] : '' ) ],
			$css
		);
	}
	private function get_menu_item_link_css( $attributes, $device = '' ) {
		$css = [];
		if (
			( $attributes['sideBarMenuDevice'] ?? null ) === 'tablet' && in_array( $device, [ 'Tablet', 'Mobile' ], true ) ||
			( ( $attributes['sideBarMenuDevice'] ?? null ) !== 'tablet' && $device === 'Mobile' )
		) {
				$css['color'] = Color::get_css( isset( $attributes['menuResponsiveTextColor'] ) ? $attributes['menuResponsiveTextColor'] : '' ) . ' !important';

		}
		return array_merge(
			[ 'color' => Color::get_css( isset( $attributes['menuItemTextColor'] ) ? $attributes['menuItemTextColor'] : '' ) ],
			$css
		);
	}

	private function get_menu_item_hover_css( $attributes, $device = '' ) {
		return array_merge(
			[ 'color' => Color::get_css( isset( $attributes['menuItemTextColorH'] ) ? $attributes['menuItemTextColorH'] : '' ) ],
			[ 'background' => Color::get_css( isset( $attributes['menuItemBackgroundH'] ) ? $attributes['menuItemBackgroundH'] : '' ) ],
			Border::get_hover_css( isset( $attributes['menuItemBorder'] ) ? $attributes['menuItemBorder'] : [], $device )
		);
	}
	private function get_menu_item_link_hover_css( $attributes ) {
		return [ 'color' => Color::get_css( isset( $attributes['menuItemTextColorH'] ) ? $attributes['menuItemTextColorH'] : '' ) ];
	}
	private function get_sub_menu_css( $attributes, $device = '' ) {
		$css = [];
		if ( isset( $attributes['menuItemBorder']['commonWidth'] ) ) {
			$css['margin-top'] = $attributes['menuItemBorder']['commonWidth'] . 'px';
		}
		if ( isset( $attributes['menuItemBorder']['bottomWidth'] ) ) {
			$css['margin-top'] = $attributes['menuItemBorder']['bottomWidth'] . 'px';
		}
		return array_merge(
			$css,
			Range::get_css([
				'attributeValue' => $attributes['subMenuWidth'],
				'attribute_object_key' => 'value',
				'isResponsive' => true,
				'defaultValue' => 250,
				'hasUnit' => true,
				'unitDefaultValue' => 'px',
				'property' => 'width',
				'device' => $device,
			]),
			BoxShadow::get_css( ! empty( $attributes['subMenuBoxShadow'] ) ? $attributes['subMenuBoxShadow'] : '', $device ),
			Dimensions::get_css( $attributes['subMenuPadding'], 'padding', $device ),
			Border::get_css( $attributes['subMenuBorder'], '', $device ),
		);
	}
	private function get_sub_menu_hover_css( $attributes, $device = '' ) {
		return array_merge(
			Border::get_hover_css( $attributes['subMenuBorder'], '', $device ),
			BoxShadow::get_hover_css( $attributes['subMenuBoxShadow'], '', $device )
		);
	}
	private function get_subMenu_responsive_css( $attributes, $device = '' ) {
		$css = [];
		if ( ! empty( $attributes['subMenuItemTransition'] ) ) {
			$css['transition-duration'] = $attributes['subMenuItemTransition'] . 's';
		}
		if (
			( isset( $attributes['sideBarMenuDevice'] ) && $attributes['sideBarMenuDevice'] === 'tablet' && in_array( $device, [ 'Tablet', 'Mobile' ], true ) ) ||
			( isset( $attributes['sideBarMenuDevice'] ) && $attributes['sideBarMenuDevice'] !== 'tablet' && $device === 'Mobile' )
		) {
				$css['background'] = Color::get_css( isset( $attributes['subMenuResponsiveBg'] ) ? $attributes['subMenuResponsiveBg'] : '' ) . '!important';
		}
		return array_merge(
			[ 'background' => Color::get_css( isset( $attributes['subMenuItemBackground'] ) ? $attributes['subMenuItemBackground'] : '' ) ],
			$css
		);
	}

	private function get_subMenu_responsive_hover_css( $attributes ) {
		return array_merge(
			[ 'color' => Color::get_css( isset( $attributes['subMenuItemTextColorH'] ) ? $attributes['subMenuItemTextColorH'] : '' ) ],
			[ 'background' => Color::get_css( isset( $attributes['subMenuItemBackgroundH'] ) ? $attributes['subMenuItemBackgroundH'] : '' ) ]
		);
	}
	private function get_subMenu_responsive_text_css( $attributes, $device = '' ) {
		$css = [];
		if (
			( $attributes['sideBarMenuDevice'] ?? null ) === 'tablet' && in_array( $device, [ 'Tablet', 'Mobile' ], true ) ||
			( ( $attributes['sideBarMenuDevice'] ?? null ) !== 'tablet' && $device === 'Mobile' )
		) {
				$css['color'] = Color::get_css( isset( $attributes['subMenuResponsiveColor'] ) ? $attributes['subMenuResponsiveColor'] : '' ) . ' !important';
		}

		return array_merge(
			[ 'color' => Color::get_css( isset( $attributes['subMenuItemTextColor'] ) ? $attributes['subMenuItemTextColor'] : '' ) ],
			$css
		);
	}
	private function get_subMenu__text_hover_css( $attributes, $device = '' ) {
		return [ 'color' => Color::get_css( isset( $attributes['subMenuItemTextColorH'] ) ? $attributes['subMenuItemTextColorH'] : '' ) ];
	}
	private function getMegaMenuCSS( $attributes, $device = '' ) {
		$css = [];
		if ( isset( $attributes['sideBarMenuDevice'] ) && $attributes['sideBarMenuDevice'] === 'tablet' && $device === 'Tablet' ) {
			$css['position'] = 'static !important';
		}
		return $css;
	}


	private function get_menu_item_dropdown_icon_css( $attributes, $device = '' ) {
		$css = [];
		if (
			( $attributes['sideBarMenuDevice'] ?? null ) === 'tablet' && in_array( $device, [ 'Tablet', 'Mobile' ], true ) ||
			( ( $attributes['sideBarMenuDevice'] ?? null ) !== 'tablet' && $device === 'Mobile' )
		) {
				$css['fill'] = Color::get_css( isset( $attributes['menuResponsiveTextColor'] ) ? $attributes['menuResponsiveTextColor'] : '' ) . ' !important';
		}
		return array_merge(
			[ 'fill' => Color::get_css( isset( $attributes['menuItemTextColor'] ) ? $attributes['menuItemTextColor'] : '' ) ],
			$css
		);
	}
	private function get_menu_item_dropdown_icon_hover_css( $attributes ) {
		return [ 'fill' => Color::get_css( isset( $attributes['menuItemTextColorH'] ) ? $attributes['menuItemTextColorH'] : '' ) ];
	}
	private function get_hamburger_menu_wrapper_css( $attributes ) {
			$css = [];
		if ( isset( $attributes['hamburgerAlignment'] ) ) {
			$css['justify-content'] = $attributes['hamburgerAlignment'];
		}
		return $css;
	}
	public function get_hamburger_menu_css( $attributes, $device = '' ) {
		$hamburger_border_css = ! empty( $attributes['hamburgerBorder'] ) ? Border::get_css( $attributes['hamburgerBorder'], '', $device ) : array();
		$css = array_merge(
			$hamburger_border_css,
			Dimensions::get_css( isset( $attributes['hamburgerPadding'] ) ? $attributes['hamburgerPadding'] : [], 'padding', $device ),
			[ 'background' => Color::get_css( isset( $attributes['hamburgerBackground'] ) ? $attributes['hamburgerBackground'] : '' ) ],
		);
		$height_humber = 30 + ( isset( $attributes['hamburgerHeight.value'] ) ? $attributes['hamburgerHeight.value'] : 0 );
		if ( $height_humber ) {
			$css['height'] = "{$height_humber}px";
		}

		return $css;
	}
	public function get_hamburgerHoverCSS( $attributes, $device = '' ) {
		$css = Border::get_hover_css( isset( $attributes['hamburgerBorder'] ) ? $attributes['hamburgerBorder'] : [], $device );
		return $css;
	}


	public function get_hamburger_menu_item_css( $attributes, $device = '' ) {
		return array_merge(
			[ 'background' => Color::get_css( isset( $attributes['hamburgerColor'] ) ? $attributes['hamburgerColor'] : '' ) ],
			Range::get_css([
				'attributeValue' => $attributes['hamburgerHeight'],
				'attribute_object_key' => 'value',
				'isResponsive' => false,
				'defaultValue' => 3,
				'hasUnit' => true,
				'unitDefaultValue' => 'px',
				'property' => 'height',
				'device' => $device,
			]),
			Range::get_css([
				'attributeValue' => $attributes['hamburgerWidth'],
				'attribute_object_key' => 'value',
				'isResponsive' => false,
				'defaultValue' => 30,
				'hasUnit' => true,
				'unitDefaultValue' => 'px',
				'property' => 'width',
				'device' => $device,
			])
		);
	}
	// menu active color syle
	public function get_menu_active_styles_css( $attributes, $device = '' ) {
		return array_merge(
			[ 'color' => Color::get_css( isset( $attributes['menuActveColor'] ) ? $attributes['menuActveColor'] : '' ) ],
			[ 'background' => Color::get_css( isset( $attributes['menuActveColorBg'] ) ? $attributes['menuActveColorBg'] : '' ) ],
		);
	}
	// The responsive text color is emitted with !important on these devices, so the active color needs it too to win over it.
	private function get_menu_active_color_value( $attributes, $device = '' ) {
		$color = Color::get_css( isset( $attributes['menuActveColor'] ) ? $attributes['menuActveColor'] : '' );
		if (
			'' !== $color &&
			( ( $attributes['sideBarMenuDevice'] ?? null ) === 'tablet' && in_array( $device, [ 'Tablet', 'Mobile' ], true ) ||
			( ( $attributes['sideBarMenuDevice'] ?? null ) !== 'tablet' && $device === 'Mobile' ) )
		) {
			return $color . ' !important';
		}
		return $color;
	}

	/**
	 * Just the "Menu Responsive" background, with none of get_menu_item_css()'s
	 * other declarations (border, padding, direction, ...) that don't belong in
	 * the tablet-hamburger custom breakpoint below.
	 */
	private function get_menu_responsive_background_only_css( $attributes ) {
		if ( empty( $attributes['menuResponsiveBackground'] ) ) {
			return [];
		}
		return [ 'background' => Color::get_css( $attributes['menuResponsiveBackground'] ) ];
	}

	/**
	 * "Enable Tablet Hamburger Menu" shows a hamburger in the 768px-1024px range
	 * (see style.css) independently of the Tablet(800)/Mobile(480) breakpoints the
	 * responsive controls use, and independently of "Hamburger Menu Device". The
	 * "Menu Responsive" colors were only ever emitted for those two breakpoints,
	 * so they never reached this range. This adds them there too, as a custom
	 * breakpoint on the same selector, without touching the standard Tablet/Mobile
	 * output.
	 */
	private function get_menu_tablet_hamburger_css( $attributes, $styles ) {
		if ( empty( $attributes['enableTabletHamburger'] ) || empty( $styles ) ) {
			return [];
		}
		return [
			'tabletHamburger' => [
				'width' => 1024,
				'min' => 768,
				'max' => 1024,
				'styles' => $styles,
			],
		];
	}
}
