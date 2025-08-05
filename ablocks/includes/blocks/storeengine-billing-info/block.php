<?php

namespace ABlocks\Blocks\StoreengineBillingInfo;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ABlocks\Classes\BlockBaseAbstract;
use ABlocks\Classes\CssGenerator;
use ABlocks\Classes\CssGeneratorV2;
use ABlocks\Helper;
use ABlocks\Controls\Typography;
use ABlocks\Controls\Background;
use ABlocks\Controls\Border;
use ABlocks\Controls\Dimensions;
use ABlocks\Controls\Range;

class Block extends BlockBaseAbstract {
	protected $block_name = 'storeengine-billing-info';

	public function build_css_v1( $attributes ) {
		$css_generator = new CssGenerator( $attributes );
		return $css_generator->generate_css();
	}

	public function build_css_v2( $attributes ) {
		$css_generator = new CssGenerator( $attributes, $this->block_name );

		$css_generator->add_class_styles(
			'{{WRAPPER}} .storeengine-order-billing-shortcode .storeengine-order-billing-heading',
			$this->get_billing_heading_css( $attributes, '' ),
			$this->get_billing_heading_css( $attributes, 'Tablet' ),
			$this->get_billing_heading_css( $attributes, 'Mobile' )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .storeengine-order-billing-shortcode .storeengine-order-billing-heading:hover',
			$this->get_billing_heading_hover_css( $attributes, '' ),
			$this->get_billing_heading_hover_css( $attributes, 'Tablet' ),
			$this->get_billing_heading_hover_css( $attributes, 'Mobile' )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .storeengine-order-billing-shortcode .storeengine-order-billing-address p',
			$this->get_billing_address_css( $attributes, '' ),
			$this->get_billing_address_css( $attributes, 'Tablet' ),
			$this->get_billing_address_css( $attributes, 'Mobile' )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .storeengine-order-billing-shortcode .storeengine-order-billing-address p:hover',
			$this->get_billing_address_hover_css( $attributes, '' ),
			$this->get_billing_address_hover_css( $attributes, 'Tablet' ),
			$this->get_billing_address_hover_css( $attributes, 'Mobile' )
		);

		return $css_generator->generate_css();
	}

	public function build_css( $attributes ) {
		if ( isset( $attributes['blockVersion'] ) && (int) $attributes['blockVersion'] === 2 ) {
			return $this->build_css_v2( $attributes );
		}
		return $this->build_css_v1( $attributes );
	}

	public function get_billing_heading_css( $attributes, $device = '' ) {
		$css = [];
		$css['color'] = $attributes['heading_color'] ?? '';
		$typography_css = isset( $attributes['heading_typograhy'] )
		? Typography::get_css( $attributes['heading_typograhy'], '', $device )
		: [];

		return array_merge(
			$css,
			$typography_css
		);
	}

	public function get_billing_heading_hover_css( $attributes, $device = '' ) {
		$css = [];
		$css['color'] = $attributes['heading_hover_color'] ?? '';
		return $css;
	}


	public function get_billing_address_css( $attributes, $device = '' ) {
		$css = [];
		$css['color'] = $attributes['address_color'] ?? '';
		$typography_css = isset( $attributes['address_typograhy'] )
		? Typography::get_css( $attributes['address_typograhy'], '', $device )
		: [];

		return array_merge(
			$css,
			$typography_css
		);
	}

	public function get_billing_address_hover_css( $attributes, $device = '' ) {
		$css = [];
		$css['color'] = $attributes['address_hover_color'] ?? '';
		return $css;
	}

	public function render_block_content( $attributes, $content, $block_instance ) {
		$attr_array = [];

		if ( isset( $_GET['context'] ) && 'edit' === sanitize_text_field( $_GET['context'] ) ) {
			$attr_array['dummy'] = true;
		}

		$shortcode = '[storeengine_order_billing_address ' . Helper::attr_shortcode( $attr_array ) . ']';
		echo do_shortcode( $shortcode );
	}

}
