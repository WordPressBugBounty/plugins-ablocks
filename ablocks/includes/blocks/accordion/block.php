<?php
namespace ABlocks\Blocks\Accordion;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ABlocks\Classes\BlockBaseAbstract;
use ABlocks\Classes\CssGenerator;
use ABlocks\Helper;
use ABlocks\Controls\Typography;
use ABlocks\Controls\TextShadow;
use ABlocks\Controls\TextStroke;
use ABlocks\Controls\Background;
use ABlocks\Controls\Border;
use ABlocks\Controls\Dimensions;
use ABlocks\Controls\Alignment;
use ABlocks\Controls\Range;

class Block extends BlockBaseAbstract {
	protected $block_name = 'accordion';

	public function build_css( $attributes ) {
		$css_generator = new CssGenerator( $attributes );

		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-block--single-accordion',
			$this->get_item_css( $attributes ),
			$this->get_item_css( $attributes, 'Tablet' ),
			$this->get_item_css( $attributes, 'Mobile' )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-block--single-accordion:hover',
			$this->get_item_hover_css( $attributes ),
			$this->get_item_hover_css( $attributes, 'Tablet' ),
			$this->get_item_hover_css( $attributes, 'Mobile' )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-block--single-accordion__heading .ablocks-block-accordion-title',
			$this->get_title_css( $attributes ),
			$this->get_title_css( $attributes, 'Tablet' ),
			$this->get_title_css( $attributes, 'Mobile' )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-block--single-accordion__heading:hover .ablocks-block-accordion-title',
			$this->get_title_hover_css( $attributes ),
			$this->get_title_hover_css( $attributes, 'Tablet' ),
			$this->get_title_hover_css( $attributes, 'Mobile' )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-block--single-accordion-is-selected .ablocks-block-accordion-title',
			$this->get_title_active_css( $attributes ),
			$this->get_title_active_css( $attributes, 'Tablet' ),
			$this->get_title_active_css( $attributes, 'Mobile' )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-block--single-accordion__heading',
			$this->get_panel_css( $attributes ),
			$this->get_panel_css( $attributes, 'Tablet' ),
			$this->get_panel_css( $attributes, 'Mobile' )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-block--single-accordion__heading:hover',
			$this->get_panel_hover_css( $attributes ),
			$this->get_panel_hover_css( $attributes, 'Tablet' ),
			$this->get_panel_hover_css( $attributes, 'Mobile' ),
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-block--single-accordion-is-selected .ablocks-block--single-accordion__heading',
			$this->get_panel_active_css( $attributes ),
			$this->get_panel_active_css( $attributes, 'Tablet' ),
			$this->get_panel_active_css( $attributes, 'Mobile' )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-block--single-accordion__heading svg.ablocks-svg-icon',
			$this->get_icon_css( $attributes )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}}  .ablocks-block--single-accordion__heading:hover svg.ablocks-svg-icon',
			$this->get_icon_hover_css( $attributes )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-block--single-accordion-is-selected svg.ablocks-svg-icon',
			$this->get_icon_active_css( $attributes )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-block--single-accordion__body-content',
			$this->get_content_css( $attributes ),
			$this->get_content_css( $attributes, 'Tablet' ),
			$this->get_content_css( $attributes, 'Mobile' )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-block--single-accordion__body-content:hover',
			$this->get_content_hover_css( $attributes ),
			$this->get_content_hover_css( $attributes, 'Tablet' ),
			$this->get_content_hover_css( $attributes, 'Mobile' )
		);
		return $css_generator->generate_css();
	}
	public function get_item_css( $attributes, $device = '' ) {
		$css = [];

		return array_merge(
			Range::get_css([
				'attributeValue' => $attributes['itemSpace'],
				'attribute_object_key' => 'value',
				'isResponsive' => false,
				'defaultValue' => 10,
				'property' => 'margin-bottom',
				'hasUnit' => false,
				'unitDefaultValue' => 'px',
				'device' => $device,

			]),
			$css,
			isset( $attributes['itemBorder'] ) ? Border::get_css( $attributes['itemBorder'], '', $device ) : []
		);
	}
	public function get_item_hover_css( $attributes, $device = '' ) {
		return array_merge(
			isset( $attributes['itemBorder'] ) ? Border::get_hover_css( $attributes['itemBorder'], '', $device ) : []
		);
	}
	public function get_title_css( $attributes, $device = '' ) {
		$css = [];
		if ( ! empty( $attributes['headerTextColor'] ) ) {
			$css['color'] = $attributes['headerTextColor'];
		}

		return array_merge(
			Range::get_css([
				'attributeValue' => $attributes['iconSpace'],
				'attribute_object_key' => 'value',
				'isResponsive' => false,
				'defaultValue' => 10,
				'property' => 'margin-left',
				'device' => $device,
			]),
			$css,
			Typography::get_css( $attributes['headerTypography'], '', $device ),
			TextShadow::get_css( $attributes['headerTextShadow'], '', $device ),
			TextStroke::get_css( $attributes['headerTextStroke'], '', $device )
		);
	}
	public function get_title_hover_css( $attributes, $device = '' ) {
		$css = [];
		if ( ! empty( $attributes['headerTextColorH'] ) ) {
			$css['color'] = $attributes['headerTextColorH'];
		}
		return array_merge(
			$css,
		);
	}
	public function get_title_active_css( $attributes, $device = '' ) {
		$css = [];
		if ( ! empty( $attributes['headerTextActiveColor'] ) ) {
			$css['color'] = $attributes['headerTextActiveColor'];
		}
		return array_merge(
			$css,
		);
	}
	public function get_panel_css( $attributes, $device = '' ) {
		$css = [];
		if ( ! empty( $attributes['headerBackgroundColor'] ) ) {
			$css['background'] = $attributes['headerBackgroundColor'];
		}
		return array_merge(
			$css,
			isset( $attributes['headerBorder'] ) ? Border::get_css( $attributes['headerBorder'], '', $device ) : [],
			isset( $attributes['headerPadding'] ) ? Dimensions::get_css( $attributes['headerPadding'], 'padding', $device ) : []
		);
	}
	public function get_panel_hover_css( $attributes, $device = '' ) {
		$css = [];
		if ( ! empty( $attributes['headerBackgroundColorH'] ) ) {
			$css['background'] = $attributes['headerBackgroundColorH'];
		}
		return array_merge(
			$css,
			isset( $attributes['headerBorder'] ) ? Border::get_hover_css( $attributes['headerBorder'], '', $device ) : []
		);
	}
	public function get_panel_active_css( $attributes, $device = '' ) {
		$css = [];
		if ( ! empty( $attributes['headerBackgroundActiveColor'] ) ) {
			$css['background'] = $attributes['headerBackgroundActiveColor'];
		}
		return array_merge(
			$css,
		);
	}
	public function get_icon_css( $attributes, $device = '' ) {
		$css = [];

		if ( ! empty( $attributes['iconColor'] ) ) {
			$css['fill'] = $attributes['iconColor'];
		}
		return array_merge(
			$css,
			Range::get_css([
				'attributeValue' => $attributes['iconSize'],
				'attribute_object_key' => 'value',
				'isResponsive' => false,
				'defaultValue' => 30,
				'property' => 'font-size',
				'device' => $device,
			]),
		);
	}
	public function get_icon_hover_css( $attributes ) {
		$css = [];
		if ( ! empty( $attributes['iconColorH'] ) ) {
			$css['fill'] = $attributes['iconColorH'];
		}
		return array_merge(
			$css,
		);
	}
	public function get_icon_active_css( $attributes ) {
		$css = [];
		if ( ! empty( $attributes['iconActiveColor'] ) ) {
			$css['fill'] = $attributes['iconActiveColor'];
		}
		return array_merge(
			$css,
		);
	}
	public function get_content_css( $attributes, $device = '' ) {
		$css = [];
		if ( ! empty( $attributes['bodyBackground'] ) ) {
			$css['background'] = $attributes['bodyBackground'];
		}
		return array_merge(
			$css,
			isset( $attributes['bodyPadding'] ) ? Dimensions::get_css( $attributes['bodyPadding'], 'padding', $device ) : []
		);
	}
	public function get_content_hover_css( $attributes, $device = '' ) {
		$css = [];
		if ( ! empty( $attributes['bodyBackgroundH'] ) ) {
			$css['background'] = $attributes['bodyBackgroundH'];
		}
		return array_merge(
			$css,
		);

	}
}
