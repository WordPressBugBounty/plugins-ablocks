<?php
namespace ABlocks\Blocks\MenuChildSub;

use ABlocks\Controls\Typography;
use ABlocks\Controls\Dimensions;
use ABlocks\Controls\Border;
use ABlocks\Controls\BoxShadow;
use ABlocks\Controls\Range;
use ABlocks\Classes\BlockBaseAbstract;
use ABlocks\Classes\CssGenerator;

class Block extends BlockBaseAbstract {
	protected $parent_block_name = 'menu';
	protected $block_name = 'menu-child-sub';

	public function build_css( $attributes ) {
		// Generate CSS
		$css_generator = new CssGenerator( $attributes );
		// Wrapper CSS
		$css_generator->add_class_styles(
			'{{WRAPPER}}',
			$this->get_wrapper_css( $attributes, '' ),
			$this->get_wrapper_css( $attributes, 'Tablet' ),
			$this->get_wrapper_css( $attributes, 'Mobile' )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} > .ablocks-menu-item',
			$this->get_menu_item_css( $attributes, '' ),
			$this->get_menu_item_css( $attributes, 'Tablet' ),
			$this->get_menu_item_css( $attributes, 'Mobile' )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} > .ablocks-menu-item > .ablocks-menu-item__link',
			$this->get_menu_item_link_css( $attributes, '' ),
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} > .ablocks-menu-item:hover > .ablocks-menu-item__link',
			$this->get_menu_item_link_hover_css( $attributes, '' ),
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} > .ablocks-menu-item:hover',
			$this->get_menu_item_hover_css( $attributes, '' ),
			$this->get_menu_item_hover_css( $attributes, 'Tablet' ),
			$this->get_menu_item_hover_css( $attributes, 'Mobile' )
		);
		$css_generator->add_class_styles( '{{WRAPPER}} > .ablocks-menu-item .ablocks-menu-item__dropdown-icon svg', $this->get_menu_item_dropdown_icon_css( $attributes ) );
		$css_generator->add_class_styles( '{{WRAPPER}} > .ablocks-menu-item:hover .ablocks-menu-item__dropdown-icon svg', $this->get_menu_item_dropdown_icon_hover_css( $attributes ) );
		return $css_generator->generate_css();
	}

	private function get_wrapper_css( $attributes, $device = '' ) {
		$css = [];

		if ( ! empty( $attributes['background'] ) ) {
			$css['background'] = $attributes['background'];
		}

		return array_merge(
			Range::get_css([
				'attributeValue' => $attributes['width'],
				'attribute_object_key' => 'value',
				'isResponsive' => true,
				'defaultValue' => 250,
				'hasUnit' => true,
				'unitDefaultValue' => 'px',
				'property' => 'width',
				'device' => $device,
			]),
			BoxShadow::get_css( ! empty( $attributes['boxShadow'] ) ? $attributes['boxShadow'] : '', $device ),
			Dimensions::get_css( $attributes['padding'], 'padding', $device ),
			Border::get_css( $attributes['border'], '', $device ),
			$css
		);
	}

	private function get_menu_item_css( $attributes, $device = '' ) {
		$css = array_merge(
			Border::get_css( isset( $attributes['menuItemBorder'] ) ? $attributes['menuItemBorder'] : [], '', $device ),
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
			$css['transition-duration'] = $attributes['menuItemTransition'] . 's';
		}
		return $css;
	}
	private function get_menu_item_link_css( $attributes ) {
		$css = [];
		if ( ! empty( $attributes['menuItemTextColor'] ) ) {
			$css['color'] = $attributes['menuItemTextColor'];
		}
		return $css;
	}
	private function get_menu_item_hover_css( $attributes, $device = '' ) {
		$css = array_merge(
			Border::get_hover_css( isset( $attributes['menuItemBorder'] ) ? $attributes['menuItemBorder'] : [], '', $device )
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
	private function get_menu_item_dropdown_icon_css( $attributes ) {
		$css = [];
		if ( ! empty( $attributes['menuItemTextColor'] ) ) {
			$css['fill'] = $attributes['menuItemTextColor'];
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
	private function get_hamburger_menu_css( $attributes ) {
			$css = [];
		if ( isset( $attributes['hamburgerAlignment'] ) ) {
			$css['justify-content'] = $attributes['hamburgerAlignment'];
		}
		return $css;
	}
}
