<?php

namespace ABlocks\Blocks\Divider;

use ABlocks\Classes\BlockBaseAbstract;
use ABlocks\Classes\CssGenerator;
use ABlocks\Controls\Alignment;
use ABlocks\Controls\TextStroke;
use ABlocks\Controls\Typography;
use ABlocks\Controls\Icon;
use ABlocks\Controls\Range;

class Block extends BlockBaseAbstract {

	protected $block_name = 'divider';

	public function build_css( $attributes ) {
		$css_generator = new CssGenerator( $attributes, $this->block_name );
		$css_generator->add_class_styles(
			'{{WRAPPER}}',
			$this->get_wrapper_css( $attributes ),
			$this->get_wrapper_css( $attributes, 'Tablet' ),
			$this->get_wrapper_css( $attributes, 'Mobile' ),
		);

		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-block-container',
			$this->get_divider_container_css( $attributes ),
			$this->get_divider_container_css( $attributes, 'Tablet' ),
			$this->get_divider_container_css( $attributes, 'Mobile' ),
		);

		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-divider',
			$this->get_divider_css( $attributes ),
			$this->get_divider_css( $attributes, 'Tablet' ),
			$this->get_divider_css( $attributes, 'Mobile' ),
		);
		// element text

		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-divider__element-text',
			$this->get_divider_element_text_css( $attributes ),
			$this->get_divider_element_text_css( $attributes, 'Tablet' ),
			$this->get_divider_element_text_css( $attributes, 'Mobile' ),
		);
		// Icon Style
		$css_generator->add_class_styles(
			'{{WRAPPER}}  .ablocks-divider__element-icon .ablocks-icon-wrap',
			Icon::get_wrapper_css( $attributes ),
			Icon::get_wrapper_css( $attributes, 'Tablet' ),
			Icon::get_wrapper_css( $attributes, 'Mobile' )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}}  .ablocks-divider__element-icon .ablocks-icon-wrap',
			array_merge( Icon::get_wrapper_css( $attributes ), $this->get_icon_spacing_margins( $attributes, '' ) ),
			array_merge( Icon::get_wrapper_css( $attributes, 'Tablet' ), $this->get_icon_spacing_margins( $attributes, 'Tablet' ) ),
			array_merge( Icon::get_wrapper_css( $attributes, 'Mobile' ), $this->get_icon_spacing_margins( $attributes, 'Mobile' ) )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-divider__element-icon .ablocks-icon-wrap img.ablocks-image-icon',
			Icon::get_element_image_css( $attributes ),
			Icon::get_element_image_css( $attributes, 'Tablet' ),
			Icon::get_element_image_css( $attributes, 'Mobile' ),
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-divider__element-icon .ablocks-icon-wrap img.ablocks-image-icon:hover',
			Icon::get_element_image_hover_css( $attributes ),
			Icon::get_element_image_hover_css( $attributes, 'Tablet' ),
			Icon::get_element_image_hover_css( $attributes, 'Mobile' ),
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-divider__element-icon .ablocks-icon-wrap svg.ablocks-svg-icon',
			Icon::get_element_css( $attributes ),
			Icon::get_element_css( $attributes, 'Tablet' ),
			Icon::get_element_css( $attributes, 'Mobile' ),
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-divider__element-icon .ablocks-icon-wrap svg.ablocks-svg-icon:hover',
			Icon::get_element_image_hover_css( $attributes ),
			Icon::get_element_image_hover_css( $attributes, 'Tablet' ),
			Icon::get_element_image_hover_css( $attributes, 'Mobile' ),
		);
		return $css_generator->generate_css();
	}

	public function get_wrapper_css( $attributes, $device = '' ) {
		if ( isset( $attributes['alignment'] ) ) {
			return Alignment::get_css( $attributes['alignment'], 'text-align', $device );
		}
		return [];
	}

	public function get_divider_element_text_css( $attributes, $device = '' ) {
		$divider_text_styles = [];
		if ( isset( $attributes['elementTextColor'] ) ) {
			$divider_text_styles['color'] = $attributes['elementTextColor'];
		}
		return array_merge(
			$divider_text_styles,
			Range::get_css([
				'attributeValue' => $attributes['elementTextSpacing'],
				'attributeObjectKey' => 'value',
				'isResponsive' => true,
				'defaultValue' => 0,
				'hasUnit' => true,
				'unitDefaultValue' => 'px',
				'property' => 'padding-right',
				'device' => $device,
			]),
			Range::get_css([
				'attributeValue' => $attributes['elementTextSpacing'],
				'attributeObjectKey' => 'value',
				'isResponsive' => true,
				'defaultValue' => 0,
				'hasUnit' => true,
				'unitDefaultValue' => 'px',
				'property' => 'padding-left',
				'device' => $device,
			]),
			Typography::get_css( $attributes['elementTextTypography'], '', $device ),
			TextStroke::get_css( $attributes['elementTextStroke'], '', $device ),
		);
	}


	public function get_divider_container_css( $attributes, $device = '' ) {
		$divider_container_css = [];
		if ( ! empty( $attributes['alignment'][ 'value' . $device ] ) ) {
			$divider_container_css['justify-content'] = $attributes['alignment'][ 'value' . $device ];
		}
		return array_merge(
			Range::get_css([
				'attributeValue' => $attributes['gap'],
				'attributeObjectKey' => 'value',
				'isResponsive' => true,
				'defaultValue' => 10,
				'unitDefaultValue' => 'px',
				'property' => 'padding-block-start',
				'device' => $device,
			]),
			Range::get_css([
				'attributeValue' => $attributes['gap'],
				'attributeObjectKey' => 'value',
				'isResponsive' => true,
				'defaultValue' => 10,
				'unitDefaultValue' => 'px',
				'property' => 'padding-block-end',
				'device' => $device,
			]),
			$divider_container_css
		);
	}

	public function get_divider_css( $attributes, $device = '' ) {
		$css = [];

		$prepared_container = Range::get_css([
			'attributeValue' => $attributes['width'],
			'attributeObjectKey' => 'value',
			'isResponsive' => true,
			'defaultValue' => 100,
			'hasUnit' => true,
			'unitDefaultValue' => '%',
			'property' => 'value',
			'device' => $device,
		]);
		if ( ! empty( $prepared_container['value'] ) && ( '100%' !== $prepared_container['value'] . $prepared_container['valueUnit'] || $device !== '' ) ) {
			$css['max-width'] = "min(100%, {$prepared_container['value']}{$prepared_container['valueUnit']})";
		}
		return array_merge(
			$css
		);
	}

	public function get_icon_spacing_margins( $attributes, $device = '' ) {
		$marginLeft = Range::get_css([
			'attributeValue' => $attributes['elementIconSpacing'],
			'attributeObjectKey' => 'value',
			'isResponsive' => true,
			'defaultValue' => 0,
			'hasUnit' => true,
			'unitDefaultValue' => 'px',
			'property' => 'margin-left',
			'device' => $device,
		]);
		$marginRight = Range::get_css([
			'attributeValue' => $attributes['elementIconSpacing'],
			'attributeObjectKey' => 'value',
			'isResponsive' => true,
			'defaultValue' => 0,
			'hasUnit' => true,
			'unitDefaultValue' => 'px',
			'property' => 'margin-right',
			'device' => $device,
		]);
		return [
			...$marginLeft,
			...$marginRight
		];
	}
}
