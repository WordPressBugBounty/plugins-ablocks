<?php
namespace ABlocks\Blocks\StoreengineProducts;

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
use ABlocks\Controls\Alignment;

class Block extends BlockBaseAbstract {
	protected $block_name = 'storeengine-products';

	public function build_css( $attributes ) {
		$css_generator = new CssGenerator( $attributes, $this->block_name );

		$css_generator->add_class_styles(
			'{{WRAPPER}} .storeengine-products--grid .storeengine-products__body .storeengine-row .storeengine-product',
			$this->get_products_card_css( $attributes ),
			$this->get_products_card_css( $attributes, 'Tablet' ),
			$this->get_products_card_css( $attributes, 'Mobile' )
		);

		$css_generator->add_class_styles(
			'{{WRAPPER}} .storeengine-products--grid .storeengine-products__body .storeengine-row .storeengine-product:hover',
			$this->get_products_card_hover_css( $attributes ),
			$this->get_products_card_hover_css( $attributes, 'Tablet' ),
			$this->get_products_card_hover_css( $attributes, 'Mobile' )
		);

		$products_title_desktop_css = $this->get_products_card_title_css( $attributes );
		if ( ! empty( $attributes['title_color'] ) ) {
			$products_title_desktop_css['color'] = $attributes['title_color'];
		}
		$css_generator->add_class_styles(
			'{{WRAPPER}} .storeengine-products--grid .storeengine-row .storeengine-product .storeengine-product__body .storeengine-product__title, 
            {{WRAPPER}} .storeengine-products--grid .storeengine-row .storeengine-product .storeengine-product__body .storeengine-product__title a',
			$products_title_desktop_css,
			$this->get_products_card_title_css( $attributes, 'Tablet' ),
			$this->get_products_card_title_css( $attributes, 'Mobile' )
		);

		$get_products_card_title_hover_css = [];
		if ( ! empty( $attributes['title_hover_color'] ) ) {
			$get_products_card_title_hover_css['color'] = $attributes['title_hover_color'];
		}
		$css_generator->add_class_styles(
			'{{WRAPPER}} .storeengine-products--grid .storeengine-row .storeengine-product .storeengine-product__body .storeengine-product__title:hover, 
            {{WRAPPER}} .storeengine-products--grid .storeengine-row .storeengine-product .storeengine-product__body .storeengine-product__title:hover a',
			$get_products_card_title_hover_css,
		);
		$products_price_css = $this->get_products_price_css( $attributes );
		if ( ! empty( $attributes['price_color'] ) ) {
			$products_price_css['color'] = $attributes['price_color'];
		}
		$css_generator->add_class_styles(
			'{{WRAPPER}} .storeengine-product ins .storeengine-price',
			$products_price_css,
			$this->get_products_price_css( $attributes, 'Tablet' ),
			$this->get_products_price_css( $attributes, 'Mobile' )
		);

		// $products_price_hover_css = [];
		// if ( ! empty( $attributes['price_hover_color'] ) ) {
		// $products_price_hover_css['color'] = $attributes['price_hover_color'];
		// }
		// $css_generator->add_class_styles(
		// '{{WRAPPER}} .storeengine-product span.price:hover',
		// $products_price_hover_css,
		// );
		$cart_button_desktop_css = [];
		$cart_button_desktop_css = $this->get_products_cart_button_css( $attributes );
		if ( ! empty( $attributes['cart_button_text_color'] ) ) {
			$cart_button_desktop_css['color'] = $attributes['cart_button_text_color'];
		}

		$css_generator->add_class_styles(
			'{{WRAPPER}} .storeengine-add-to-cart-buttons .storeengine-btn',
			$this->get_products_cart_button_css( $attributes ),
			$this->get_products_cart_button_css( $attributes, 'Tablet' ),
			$this->get_products_cart_button_css( $attributes, 'Mobile' )
		);

		$css_generator->add_class_styles(
			'{{WRAPPER}} .storeengine-add-to-cart-buttons .storeengine-btn:hover',
			$this->get_products_cart_button_hover_css( $attributes ),
			$this->get_products_cart_button_hover_css( $attributes, 'Tablet' ),
			$this->get_products_cart_button_hover_css( $attributes, 'Mobile' )
		);
		// $css_generator->add_class_styles(
		// '{{WRAPPER}} .storeengine-product .storeengine-ajax-add-to-cart-form .storeengine-add-to-cart-buttons',
		// $this->get_button_leyout_css( $attributes ),
		// $this->get_button_leyout_css( $attributes, 'Tablet' ),
		// $this->get_button_leyout_css( $attributes, 'Mobile' )
		// );

		return $css_generator->generate_css();

	}

	public function get_products_card_css( $attributes, $device = '' ) {
		$products_background_css = ! empty( $attributes['card_background'] ) ? Background::get_css( $attributes['card_background'], 'background', $device ) : array();
		$products_border_css = ! empty( $attributes['card_border'] ) ? Border::get_css( $attributes['card_border'], '', $device ) : array();
		$products_margin_css = ! empty( $attributes['card_margin'] ) ? Dimensions::get_css( $attributes['card_margin'], 'margin', $device ) : array();
		$products_padding_css = ! empty( $attributes['card_padding'] ) ? Dimensions::get_css( $attributes['card_padding'], 'padding', $device ) : array();

		return array_merge(
			$products_background_css,
			$products_border_css,
			$products_margin_css,
			$products_padding_css
		);
	}

	public function get_products_card_hover_css( $attributes, $device = '' ) {
		$products_background_hover_css = ! empty( $attributes['card_background'] ) ? Background::get_hover_css( $attributes['card_background'], 'background', $device ) : array();
		$products_border_hover_css = ! empty( $attributes['card_border'] ) ? Border::get_hover_css( $attributes['card_border'], '', $device ) : array();
		$products_margin_hover_css = ! empty( $attributes['card_hover_margin'] ) ? Dimensions::get_css( $attributes['card_hover_margin'], 'margin', $device ) : array();
		$products_padding_hover_css = ! empty( $attributes['card_hover_padding'] ) ? Dimensions::get_css( $attributes['card_hover_padding'], 'padding', $device ) : array();

		return array_merge(
			$products_background_hover_css,
			$products_border_hover_css,
			$products_margin_hover_css,
			$products_padding_hover_css
		);
	}

	public function get_products_price_css( $attributes, $device = '' ) {
		$products_price_typography_css = ! empty( $attributes['price_typography'] ) ? Typography::get_css( $attributes['price_typography'], '', $device ) : array();
		return $products_price_typography_css;
	}
	public function get_products_card_title_css( $attributes, $device = '' ) {
		$course_title_typography_css = ! empty( $attributes['title_typography'] ) ? Typography::get_css( $attributes['title_typography'], '', $device ) : array();
		return $course_title_typography_css;
	}

	public function get_products_cart_button_css( $attributes, $device = '' ) {
		$css = [];
		$typography_value = ! empty( $attributes['cart_button_typography'] ) ? Typography::get_css( $attributes['cart_button_typography'], '', $device ) : array();

		$css['color'] = ! empty( $attributes['cart_button_text_color'] ) ? $attributes['cart_button_text_color'] : '';
		$css['background'] = ! empty( $attributes['cart_button_color'] ) ? $attributes['cart_button_color'] : '';

		return array_merge(
			$css,
			$typography_value,
			Range::get_css([
				'attributeValue' => $attributes['buttonWidth'],
				'attribute_object_key' => 'value',
				'isResponsive' => true,
				'hasUnit' => false,
				'defaultValue' => 0,
				'unitDefaultValue' => 'px',
				'property' => 'width',
				'device' => $device,
			])
		);
	}

	public function get_products_cart_button_hover_css( $attributes, $device = '' ) {
		$css = [];

		$css['color'] = ! empty( $attributes['cart_button_text_hover_color'] ) ? $attributes['cart_button_text_hover_color'] : '';
		$css['background'] = ! empty( $attributes['cart_button_hover_color'] ) ? $attributes['cart_button_hover_color'] : '';

		return $css;
	}
	// public function get_button_leyout_css( $attributes, $device = '' ) {
	// $css = [];
	// $button_alignment = [];

	// $css['display'] = 'flex';

	// $buttons_direction = Range::get_css([
	// 'attributeValue' => $attributes['buttonDriection'],
	// 'attribute_object_key' => 'value',
	// 'isResponsive' => true,
	// 'hasUnit' => false,
	// 'defaultValue' => 'column',
	// 'unitDefaultValue' => '',
	// 'property' => 'flex-direction',
	// 'device' => $device,
	// ]);
	// $buttons_gap = Range::get_css([
	// 'attributeValue' => $attributes['buttonGap'],
	// 'attribute_object_key' => 'value',
	// 'isResponsive' => true,
	// 'hasUnit' => false,
	// 'defaultValue' => 8,
	// 'unitDefaultValue' => 'px',
	// 'property' => 'gap',
	// 'device' => $device,
	// ]);
	// if ( ! empty( $attributes['buttonAlignment'][ 'value' . $device ] ) ) {
	// $button_alignment['align-items'] = $attributes['buttonAlignment'][ 'value' . $device ];
	// }
	// return array_merge(
	// $css,
	// $buttons_direction,
	// $buttons_gap,
	// $button_alignment,
	// );
	// }

	public function render_block_content( $attributes, $content, $block_instance ) {
		$attr_array = [
			'count'                 => Helper::get_attribute_value( $attributes, 'products_count' ),
			'column_per_row'        => Helper::get_attribute_value( $attributes, 'products_columns' ),
			'has_pagination'        => Helper::get_attribute_value( $attributes, 'show_pagination' ),
			'products_level'          => Helper::get_attribute_value( $attributes, 'difficulty_levels' ),
			'price_type'            => Helper::get_attribute_value( $attributes, 'price_types' ),
			'orderby'               => Helper::get_attribute_value( $attributes, 'order_by' ),
			'order'                 => Helper::get_attribute_value( $attributes, 'products_order' ),
			'ids'                   => Helper::get_attribute_value( $attributes, 'products_ids' ),
			'exclude_ids'           => Helper::get_attribute_value( $attributes, 'products_exclude_ids' ),
			'category'              => Helper::get_attribute_value( $attributes, 'products_categories' ),
			'cat_not_in'            => Helper::get_attribute_value( $attributes, 'products_exclude_categories' ),
			'tag'                   => Helper::get_attribute_value( $attributes, 'products_tags' ),
			'tag_not_in'            => Helper::get_attribute_value( $attributes, 'products_exclude_tags' ),
		];
		$shortcode = '[storeengine_products ' . Helper::attr_shortcode( $attr_array ) . ']';
		echo do_shortcode( $shortcode );
	}

}
