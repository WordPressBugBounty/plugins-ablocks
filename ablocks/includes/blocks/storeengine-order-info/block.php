<?php

namespace ABlocks\Blocks\StoreengineOrderInfo;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ABlocks\Classes\BlockBaseAbstract;
use ABlocks\Classes\CssGenerator;
use ABlocks\Helper;
use ABlocks\Controls\Typography;
use ABlocks\Controls\Border;
use ABlocks\Controls\Dimensions;
use ABlocks\Controls\Range;
use ABlocks\Classes\CssGeneratorV2;

class Block extends BlockBaseAbstract {
	protected $block_name = 'storeengine-order-info';

	public function build_css_v1( $attributes ) {
		$css_generator = new CssGenerator( $attributes );
		return $css_generator->generate_css();
	}

	public function build_css_v2( $attributes ) {
		$css_generator = new CssGenerator( $attributes );

		$css_generator->add_class_styles(
			'{{WRAPPER}} .storeengine-thankyou-order-info-shortcode .storeengine-thankyou-order-info-success__content h4',
			$this->get_status_title_css( $attributes ),
			$this->get_status_title_css( $attributes, 'Tablet' ),
			$this->get_status_title_css( $attributes, 'Mobile' )
		);

		$css_generator->add_class_styles(
			'{{WRAPPER}} .storeengine-thankyou-order-info-shortcode .storeengine-thankyou-order-info-success__content h4:hover',
			$this->get_status_title_hover_css( $attributes ),
			$this->get_status_title_hover_css( $attributes, 'Tablet' ),
			$this->get_status_title_hover_css( $attributes, 'Mobile' )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .storeengine-thankyou-order-info-shortcode .storeengine-thankyou-order-info-success__content p span,
			 {{WRAPPER}} .storeengine-thankyou-order-info-shortcode .storeengine-thankyou-order-info-success__content p time',
			$this->get_deatils_title_css( $attributes ),
			$this->get_deatils_title_css( $attributes, 'Tablet' ),
			$this->get_deatils_title_css( $attributes, 'Mobile' )
		);

		$css_generator->add_class_styles(
			'{{WRAPPER}} .storeengine-thankyou-order-info-shortcode .storeengine-thankyou-order-info-success__content p span:hover,
			 {{WRAPPER}} .storeengine-thankyou-order-info-shortcode .storeengine-thankyou-order-info-success__content p time:hover',
			$this->get_deatils_title_hover_css( $attributes ),
			$this->get_deatils_title_hover_css( $attributes, 'Tablet' ),
			$this->get_deatils_title_hover_css( $attributes, 'Mobile' )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .storeengine-thankyou-order-info-shortcode .storeengine-thankyou-order-info-success .storeengine-thankyou-order-info-success__email p span,
			{{WRAPPER}} .storeengine-thankyou-order-info-shortcode .storeengine-thankyou-order-info-success .storeengine-thankyou-order-info-success__email p a',
			$this->get_email_css( $attributes ),
			$this->get_email_css( $attributes, 'Tablet' ),
			$this->get_email_css( $attributes, 'Mobile' )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .storeengine-thankyou-order-info-shortcode .storeengine-thankyou-order-info-success .storeengine-thankyou-order-info-success__email p span:hover,
			{{WRAPPER}} .storeengine-thankyou-order-info-shortcode .storeengine-thankyou-order-info-success .storeengine-thankyou-order-info-success__email p a:hover',
			$this->get_email_hover_css( $attributes ),
			$this->get_email_hover_css( $attributes, 'Tablet' ),
			$this->get_email_hover_css( $attributes, 'Mobile' )
		);

		return $css_generator->generate_css();
	}

	public function build_css( $attributes ) {
		if ( isset( $attributes['blockVersion'] ) && (int) $attributes['blockVersion'] === 2 ) {
			return $this->build_css_v2( $attributes );
		}
		return $this->build_css_v1( $attributes );
	}

	public function get_status_title_css( $attributes, $device = '' ) {
		$css = [];

		$css['color'] = $attributes['titleContentColor'] ?? '';
		$typography_value = ! empty( $attributes['titleContentTypography'] ) ? Typography::get_css( $attributes['titleContentTypography'], '', $device ) : array();

		return array_merge(
			$css,
			$typography_value
		);
	}

	public function get_status_title_hover_css( $attributes, $device = '' ) {
		$css = [];

		$css['color'] = $attributes['titleContentColorH'] ?? '';

		return $css;
	}
	public function get_deatils_title_css( $attributes, $device = '' ) {
		$css = [];

		$css['color'] = $attributes['detailsColor'] ?? '';
		$typography_value = ! empty( $attributes['detailsTypography'] ) ? Typography::get_css( $attributes['titleContentTypography'], '', $device ) : array();

		return array_merge(
			$css,
			$typography_value
		);
	}

	public function get_deatils_title_hover_css( $attributes, $device = '' ) {
		$css = [];

		$css['color'] = $attributes['detailsColorH'] ?? '';

		return $css;
	}

	public function get_email_css( $attributes, $device = '' ) {
		$css = [];

		$css['color'] = $attributes['emailColor'] ?? '';
		$typography_value = ! empty( $attributes['emailTypography'] ) ? Typography::get_css( $attributes['emailTypography'], '', $device ) : array();

		return array_merge(
			$css,
			$typography_value
		);
	}
	public function get_email_hover_css( $attributes, $device = '' ) {
		$css = [];

		$css['color'] = $attributes['emailColorH'] ?? '';

		return $css;
	}

	public function render_block_content( $attributes, $content, $block_instance ) {
		$attr_array = [
			'ids'         => Helper::get_attribute_value( $attributes, 'products_ids' ),
			'dummy' => false,
		];
		if ( isset( $_GET['context'] ) && 'edit' === sanitize_text_field( $_GET['context'] ) ) {
			$attr_array['dummy'] = true;
		}

		$shortcode = '[storeengine_thankyou_order_info ' . Helper::attr_shortcode( $attr_array ) . ']';
		echo do_shortcode( $shortcode );
	}

}
