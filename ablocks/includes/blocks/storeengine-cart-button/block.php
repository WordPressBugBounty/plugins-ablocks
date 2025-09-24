<?php

namespace ABlocks\Blocks\StoreengineCartButton;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ABlocks\Classes\BlockBaseAbstract;
use ABlocks\Classes\CssGenerator;
use ABlocks\Helper;
use ABlocks\Controls\Typography;
use ABlocks\Controls\Background;
use ABlocks\Controls\Border;
use ABlocks\Controls\Dimensions;
use ABlocks\Controls\Range;
use ABlocks\Controls\BoxShadow;

class Block extends BlockBaseAbstract {
	protected $block_name = 'storeengine-cart-button';

	public function build_css( $attributes ) {
		$css_generator = new CssGenerator( $attributes, $this->block_name );

		$css_generator->add_class_styles(
			'{{WRAPPER}} .storeengine-btn--add-to-cart, 
			{{WRAPPER}} .storeengine-btn--add-to-cart-replacement, 
			{{WRAPPER}} .storeengine-btn--direct-checkout, 
			{{WRAPPER}} .storeengine-btn--view-options',
			$this->get_button_css( $attributes ),
			$this->get_button_css( $attributes, 'Tablet' ),
			$this->get_button_css( $attributes, 'Mobile' )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .storeengine-btn--add-to-cart:hover, 
			{{WRAPPER}} .storeengine-btn--add-to-cart-replacement:hover, 
			{{WRAPPER}} .storeengine-btn--direct-checkout:hover, 
			{{WRAPPER}} .storeengine-btn--view-options:hover',
			$this->get_button_hover_css( $attributes, '' ),
			$this->get_button_hover_css( $attributes, 'Tablet' ),
			$this->get_button_hover_css( $attributes, 'Mobile' )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .storeengine-single-product-quantity-wrap',
			$this->get_button_alignment_css( $attributes, '' ),
			$this->get_button_alignment_css( $attributes, 'Tablet' ),
			$this->get_button_alignment_css( $attributes, 'Mobile' )
		);

		return $css_generator->generate_css();
	}

	public function get_button_css( $attributes, $device = '' ) {
		$css               = [];
		$css['color']      = $attributes['button_color'] ?? '';
		$css['background'] = $attributes['button_bg'] ?? '';
		$typography_value  = isset( $attributes['btn_typography'] ) ? Typography::get_css( $attributes['btn_typography'], '', $device ) : array();

		if ( ! empty( $attributes['padding'] ) ) {
			$cssPadding = Dimensions::get_css( $attributes['padding'], 'padding', $device );
		}

		return array_merge(
			$css,
			$typography_value,
			$cssPadding,
			Border::get_css( $attributes['buttonBorder'], '', $device ),
			BoxShadow::get_css( $attributes['boxShadow'], '', $device ),
			Range::get_css( [
				'attributeValue'       => $attributes['button_width'],
				'attribute_object_key' => 'value',
				'isResponsive'         => true,
				'defaultValue'         => 100,
				'hasUnit'              => true,
				'unitDefaultValue'     => '%',
				'property'             => 'width',
				'device'               => $device,
			] )
		);
	}

	public function get_button_hover_css( $attributes, $device = '' ) {
		$css               = [];
		$css['color']      = $attributes['button_color_hover'] ?? '';
		$css['background'] = $attributes['button_bg_hover'] ?? '';

		return array_merge(
			$css,
			Border::get_hover_css( $attributes['buttonBorder'] ?? [], '', $device ),
			BoxShadow::get_hover_css( $attributes['boxShadow'], '', $device ),
		);
	}

	public function get_button_alignment_css( $attributes, $device = '' ) {
		$alignment_css = [];

		if ( ! empty( $attributes['buttonAlign'][ 'value' . $device ] ) ) {
			$alignment_css['justify-content'] = $attributes['buttonAlign'][ 'value' . $device ];
		}

		return $alignment_css;
	}

	public function render_block_content( $attributes, $content, $block_instance ) {
		$attr_array = [
			'label'           => Helper::get_attribute_value( $attributes, 'label' ),
			'product_id'      => Helper::get_attribute_value( $attributes, 'product_id' ),
			'price_id'        => Helper::get_attribute_value( $attributes, 'price_id' ),
			'variation_id'    => Helper::get_attribute_value( $attributes, 'variation_id' ),
			'direct_checkout' => Helper::get_attribute_value( $attributes, 'direct_checkout' ) ? 'true' : 'false',
			'quantity'        => Helper::get_attribute_value( $attributes, 'quantity' ),
			'show_quantity'   => Helper::get_attribute_value( $attributes, 'show_quantity' ),
			'disabled'        => Helper::get_attribute_value( $attributes, 'disabled' ),
			'price_display'   => Helper::get_attribute_value( $attributes, 'price_display' ),
		];

		if ( isset( $block_instance->context['postId'] ) && ! empty( $block_instance->context['postId'] ) && empty( $attr_array['product_id'] ) ) {
			$attr_array['product_id'] = $block_instance->context['postId'];
		}

		if ( isset( $_GET['context'] ) && 'edit' === sanitize_text_field( $_GET['context'] ) ) {
			$attr_array['dummy'] = true;
		}

		$shortcode = '[storeengine_add_to_cart ' . Helper::attr_shortcode( $attr_array ) . ']';
		echo do_shortcode( $shortcode );
	}

}
