<?php
namespace ABlocks\Blocks\StoreengineCartList;

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

class Block extends BlockBaseAbstract {
	protected $block_name = 'storeengine-cart-list';

	public function build_css( $attributes ) {
		$css_generator = new CssGenerator( $attributes, $this->block_name );

		$css_generator->add_class_styles(
			'{{WRAPPER}} .storeengine-cart-table',
			$this->get_cart_list_table_css( $attributes ),
			$this->get_cart_list_table_css( $attributes, 'Tablet' ),
			$this->get_cart_list_table_css( $attributes, 'Mobile' )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .storeengine-cart-table:hover',
			$this->get_cart_list_table_hover_css( $attributes ),
			$this->get_cart_list_table_hover_css( $attributes, 'Tablet' ),
			$this->get_cart_list_table_hover_css( $attributes, 'Mobile' )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .storeengine-cart-table .storeengine-cart-table__head tr',
			$this->get_cart_list_table_header_css( $attributes ),
			$this->get_cart_list_table_header_css( $attributes, 'Tablet' ),
			$this->get_cart_list_table_header_css( $attributes, 'Mobile' )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .storeengine-cart-table .storeengine-cart-table__head tr:hover',
			$this->get_cart_list_table_header_hover_css( $attributes ),
			$this->get_cart_list_table_header_hover_css( $attributes, 'Tablet' ),
			$this->get_cart_list_table_header_hover_css( $attributes, 'Mobile' )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .storeengine-cart-list-table-shortcode .storeengine-cart-table__head tr th',
			$this->get_cart_list_table_header_text_css( $attributes ),
			$this->get_cart_list_table_header_text_css( $attributes, 'Tablet' ),
			$this->get_cart_list_table_header_text_css( $attributes, 'Mobile' )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .storeengine-cart-product .storeengine-cart-product__content .storeengine-cart-product-title a',
			$this->get_cart_list_product_title( $attributes ),
			$this->get_cart_list_product_title( $attributes, 'Tablet' ),
			$this->get_cart_list_product_title( $attributes, 'Mobile' )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .storeengine-cart-product .storeengine-cart-product__content .storeengine-cart-product-title a:hover',
			$this->get_cart_list_product_title_hover_css( $attributes ),
			$this->get_cart_list_product_title_hover_css( $attributes, 'Tablet' ),
			$this->get_cart_list_product_title_hover_css( $attributes, 'Mobile' )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .storeengine-cart-product__content .storeengine-cart-product-price',
			$this->get_cart_list_subtitle_css( $attributes ),
			$this->get_cart_list_subtitle_css( $attributes, 'Tablet' ),
			$this->get_cart_list_subtitle_css( $attributes, 'Mobile' )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .storeengine-cart-product__content .storeengine-cart-product-price:hover',
			$this->get_cart_list_subtitle_hover_css( $attributes ),
			$this->get_cart_list_subtitle_hover_css( $attributes, 'Tablet' ),
			$this->get_cart_list_subtitle_hover_css( $attributes, 'Mobile' )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .storeengine-cart-list-table-shortcode .storeengine-cart-table__body-td .storeengine-price bdi',
			$this->get_cart_list_product_price_css( $attributes ),
			$this->get_cart_list_product_price_css( $attributes, 'Tablet' ),
			$this->get_cart_list_product_price_css( $attributes, 'Mobile' )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .storeengine-cart-list-table-shortcode .storeengine-cart-table__body-td .storeengine-price bdi:hover',
			$this->get_cart_list_product_price_hover_css( $attributes ),
			$this->get_cart_list_product_price_hover_css( $attributes, 'Tablet' ),
			$this->get_cart_list_product_price_hover_css( $attributes, 'Mobile' )
		);

		return $css_generator->generate_css();
	}

	public function get_cart_list_table_css( $attributes, $device = '' ) {
		$css = [];
		if ( ! empty( $attributes['tableBackground'] ) ) {
			$css['background'] = $attributes['tableBackground'];
		}
		return $css;
	}
	public function get_cart_list_table_hover_css( $attributes, $device = '' ) {
		$css = [];
		if ( ! empty( $attributes['tableBackgroundH'] ) ) {
			$css['background'] = $attributes['tableBackgroundH'];
		}
		return array_merge(
			Range::get_css([
				'attributeValue' => isset( $attributes['tableTransition'] ) ? $attributes['tableTransition'] : '',
				'attribute_object_key' => 'value',
				'defaultValue' => 0,
				'unitDefaultValue' => 's',
				'property' => 'transition-duration',
				'device' => $device,
			]),
			$css,
		);
	}

	public function get_cart_list_table_header_css( $attributes, $device = '' ) {
		$css = [];
		if ( ! empty( $attributes['tableHeaderBackground'] ) ) {
			$css['background'] = $attributes['tableHeaderBackground'];
		}
		return $css;
	}

	public function get_cart_list_table_header_hover_css( $attributes, $device = '' ) {
		$css = [];
		if ( ! empty( $attributes['tableHeaderBackgroundH'] ) ) {
			$css['background'] = $attributes['tableHeaderBackgroundH'];
		}
		return array_merge(
			Range::get_css([
				'attributeValue' => isset( $attributes['tableTransition'] ) ? $attributes['tableTransition'] : '',
				'attribute_object_key' => 'value',
				'defaultValue' => 0,
				'unitDefaultValue' => 's',
				'property' => 'transition-duration',
				'device' => $device,
			]),
			$css,
		);
	}

	public function get_cart_list_table_header_text_css( $attributes, $device = '' ) {
		$typography = isset( $attributes['tableHeaderTypography'] ) && is_array( $attributes['tableHeaderTypography'] )
			? $attributes['tableHeaderTypography']
			: [];

		$typography_value = array_merge( $typography, [ 'font-weight' => '400' ] );

			return array_merge(
				Typography::get_css( $typography_value, $device ),
			);
	}

	public function get_cart_list_product_title( $attributes, $device = '' ) {
		$css['color'] = $attributes['productTitleColor'] ?? '';
		$typography = isset( $attributes['productTilteTypography'] ) && is_array( $attributes['productTilteTypography'] )
			? $attributes['productTilteTypography']
			: array();

		$typography_value = array_merge( $typography, [ 'font-weight' => '400' ] );

			return array_merge(
				Typography::get_css( $typography_value, $device ),
				$css,
			);

	}
	public function get_cart_list_product_title_hover_css( $attributes, $device = '' ) {
		$css = [];
		if ( ! empty( $attributes['productTitleColorH'] ) ) {
			$css['color'] = $attributes['productTitleColorH'];
		}
		return $css;
	}

	public function get_cart_list_subtitle_css( $attributes, $device = '' ) {
		$css['color'] = $attributes['productSubTiteColor'] ?? '';
		$typography = isset( $attributes['productsubTitleTypography'] ) && is_array( $attributes['productsubTitleTypography'] )
			? $attributes['productsubTitleTypography']
			: [];

		$typography_value = array_merge( $typography, [ 'font-weight' => '400' ] );

			return array_merge(
				Typography::get_css( $typography_value, $device ),
				$css,
			);

	}

	public function get_cart_list_subtitle_hover_css( $attributes, $device = '' ) {
		$css = [];
		$css['color'] = $attributes['productSubTiteColorH'] ?? '';

		return $css;
	}
	public function get_cart_list_product_price_css( $attributes, $device = '' ) {
		// $css = [];
		$css['color'] = $attributes['productPriceColor'] ?? '';

		$typography = isset( $attributes['productPriceTypography'] ) && is_array( $attributes['productPriceTypography'] )
			? $attributes['productPriceTypography']
			: [];

		$typography_value = array_merge( $typography, [ 'font-weight' => '400' ] );

		return array_merge(
			Typography::get_css( $typography_value, $device ),
			$css,
		);

	}
	public function get_cart_list_product_price_hover_css( $attributes, $device = '' ) {
		$css['color'] = $attributes['productPriceColorH'] ?? '';
		return $css;
	}

	public function render_block_content( $attributes, $content, $block_instance ) {
		$attr_array = [];
		$shortcode = '[storeengine_cart_list_table ' . Helper::attr_shortcode( $attr_array ) . ']';
		echo do_shortcode( $shortcode );
	}

}
