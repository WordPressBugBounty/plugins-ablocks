<?php
namespace ABlocks\Blocks\Menu;

use ABlocks\Controls\Typography;
use ABlocks\Controls\Dimensions;
use ABlocks\Controls\Border;
use ABlocks\Classes\BlockBaseAbstract;
use ABlocks\Classes\CssGenerator;
use ABlocks\Controls\Range;

class Block extends BlockBaseAbstract {
	protected $block_name = 'menu';

	public function build_css( $attributes ) {
		// Generate CSS
		$css_generator = new CssGenerator( $attributes );
		// Wrapper CSS
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-menu',
			$this->get_menu_css( $attributes, '' ),
			$this->get_menu_css( $attributes, 'Tablet' ),
			$this->get_menu_css( $attributes, 'Mobile' )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}}.ablocks-menu',
			$this->get_menu_css( $attributes, '' ),
			$this->get_menu_css( $attributes, 'Tablet' ),
			$this->get_menu_css( $attributes, 'Mobile' )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-main-menu > .ablocks-menu-item',
			$this->get_menu_item_css( $attributes ),
			$this->get_menu_item_css( $attributes, 'Tablet' ),
			$this->get_menu_item_css( $attributes, 'Mobile' )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-main-menu > .ablocks-menu-item:hover',
			$this->get_menu_item_hover_css( $attributes, '' ),
			$this->get_menu_item_hover_css( $attributes, 'Tablet' ),
			$this->get_menu_item_hover_css( $attributes, 'Mobile' )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-menu .ablocks-block--menu-child-sub',
			$this->getSubMenuPositionCSS( $attributes, '' ),
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-menu-child-mega',
			$this->getMegaMenuCSS( $attributes ),
			$this->getMegaMenuCSS( $attributes, 'Tablet' ),
			$this->getMegaMenuCSS( $attributes, 'Mobile' )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-main-menu > .ablocks-menu-item > .ablocks-menu-item__link',
			$this->get_menu_item_link_css( $attributes, '' ),
			$this->get_menu_item_link_css( $attributes, 'Tablet' ),
			$this->get_menu_item_link_css( $attributes, 'Mobile' )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-main-menu > .ablocks-menu-item:hover > .ablocks-menu-item__link ',
			$this->get_menu_item_link_hover_css( $attributes, '' ),
		);
		$css_generator->add_class_styles( '{{WRAPPER}} .ablocks-main-menu  > .ablocks-menu-item > .ablocks-menu-item__dropdown-icon svg',
			$this->get_menu_item_dropdown_icon_css( $attributes ),
			$this->get_menu_item_dropdown_icon_css( $attributes, 'Tablet' ),
			$this->get_menu_item_dropdown_icon_css( $attributes, 'Mobile' )
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

	private function get_menu_css( $attributes, $device = '' ) {
		$css = [];
		$padding = isset( $attributes['padding'] ) ? $attributes['padding'] : '';

		if ( ! empty( $attributes['alignment'] ) ) {
			$css['justify-content'] = $attributes['alignment'];
		}
		return array_merge(
			Dimensions::get_css( $padding, 'padding', $device ),
			$css
		);
	}




	private function get_menu_item_css( $attributes, $device = '' ) {
		$menu_border_css = ! empty( $attributes['menuItemBorder'] ) ? Border::get_css( $attributes['menuItemBorder'], '', $device ) : array();
		$css = array_merge(
			$menu_border_css,
			Typography::get_css( isset( $attributes['menuItemTypography'] ) ? $attributes['menuItemTypography'] : [], '', $device ),
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
		// Text color
		if ( ! empty( $attributes['menuItemTextColor'] ) ) {
			$css['color'] = $attributes['menuItemTextColor'];
		}
		if ( ! empty( $attributes['menuItemBackground'] ) ) {
			$css['background'] = $attributes['menuItemBackground'];
		}
		if ( ! empty( $attributes['menuItemTransition'] ) ) {
			$css['transition'] = $attributes['menuItemTransition'] . 's';
		}
		if (
			( $attributes['sideBarMenuDevice'] ?? null ) === 'tablet' && in_array( $device, [ 'Tablet', 'Mobile' ], true ) ||
			( ( $attributes['sideBarMenuDevice'] ?? null ) !== 'tablet' && $device === 'Mobile' )
		) {
			$background_color = isset( $attributes['menuResponsiveBackground'] ) ? $attributes['menuResponsiveBackground'] : '';
			if ( ! empty( $background_color ) ) {
				$css['background-color'] = $background_color;
			}
		}
		return $css;
	}
	private function get_menu_item_link_css( $attributes, $device = '' ) {
		$css = [];
		if ( ! empty( $attributes['menuItemTextColor'] ) ) {
			$css['color'] = $attributes['menuItemTextColor'];
		}
		if (
			( $attributes['sideBarMenuDevice'] ?? null ) === 'tablet' && in_array( $device, [ 'Tablet', 'Mobile' ], true ) ||
			( ( $attributes['sideBarMenuDevice'] ?? null ) !== 'tablet' && $device === 'Mobile' )
		) {
			$text_color = isset( $attributes['menuResponsiveTextColor'] ) ? $attributes['menuResponsiveTextColor'] : '';
			if ( ! empty( $text_color ) ) {
				$css['color'] = $text_color . ' !important';
			}
		}
		return $css;
	}

	private function get_menu_item_hover_css( $attributes, $device = '' ) {
		$css = array_merge(
			Border::get_hover_css( isset( $attributes['menuItemBorder'] ) ? $attributes['menuItemBorder'] : [], $device )
		);

		// Text color on hover
		if ( ! empty( $attributes['menuItemTextColorH'] ) ) {
			$css['color'] = $attributes['menuItemTextColorH'];
		}
		if ( ! empty( $attributes['menuItemBackgroundH'] ) ) {
			$css['background'] = $attributes['menuItemBackgroundH'];
		}
		return $css;
	}
	private function get_menu_item_link_hover_css( $attributes ) {
		$css = [];
		if ( ! empty( $attributes['menuItemTextColorH'] ) ) {
			$css['color'] = $attributes['menuItemTextColorH'];
		}

		return $css;
	}
	private function getSubMenuPositionCSS( $attributes ) {
		$css = [];
		if ( isset( $attributes['menuItemBorder']['commonWidth'] ) ) {
			$css['margin-top'] = $attributes['menuItemBorder']['commonWidth'] . 'px';
		}
		if ( isset( $attributes['menuItemBorder']['bottomWidth'] ) ) {
			$css['margin-top'] = $attributes['menuItemBorder']['bottomWidth'] . 'px';
		}
		return $css;
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
		if ( ! empty( $attributes['menuItemTextColor'] ) ) {
			$css['fill'] = $attributes['menuItemTextColor'];
		}
		if (
			( $attributes['sideBarMenuDevice'] ?? null ) === 'tablet' && in_array( $device, [ 'Tablet', 'Mobile' ], true ) ||
			( ( $attributes['sideBarMenuDevice'] ?? null ) !== 'tablet' && $device === 'Mobile' )
		) {
			$text_color = isset( $attributes['menuResponsiveTextColor'] ) ? $attributes['menuResponsiveTextColor'] : '';
			if ( ! empty( $text_color ) ) {
				$css['fill'] = $text_color . ' !important';
			}
		}
		return $css;
	}
	private function get_menu_item_dropdown_icon_hover_css( $attributes ) {
		$css = [];
		if ( ! empty( $attributes['menuItemTextColorH'] ) ) {
			$css['fill'] = $attributes['menuItemTextColorH'];
		}
		return $css;
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
			Dimensions::get_css( isset( $attributes['hamburgerPadding'] ) ? $attributes['hamburgerPadding'] : [], 'padding', $device )
		);

		if ( isset( $attributes['hamburgerBackground'] ) ) {
			$css['background'] = $attributes['hamburgerBackground'];
		}
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
		$css = [];
		if ( isset( $attributes['hamburgerColor'] ) ) {
			$css['background'] = $attributes['hamburgerColor'];
		}

		return array_merge(
			$css,
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
}
