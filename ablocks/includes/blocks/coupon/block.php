<?php
namespace ABlocks\Blocks\Coupon;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ABlocks\Classes\BlockBaseAbstract;
use ABlocks\Classes\CssGenerator;
use ABlocks\Controls\Range;
use ABlocks\Controls\Dimensions;
use ABlocks\Controls\Icon;
use ABlocks\Controls\Alignment;
use ABlocks\Controls\Typography;
use ABlocks\Controls\Border;
use ABlocks\Controls\TextShadow;



class Block extends BlockBaseAbstract {
	protected $block_name = 'coupon';

	public function build_css( $attributes ) {
		$css_generator = new CssGenerator( $attributes );

		$css_generator->add_class_styles(
			'{{WRAPPER}}',
			$this->get_wrapper_css( $attributes ),
			$this->get_wrapper_css( $attributes, 'Tablet' ),
			$this->get_wrapper_css( $attributes, 'Mobile' )
		);

		$css_generator->add_class_styles(
			'{{WRAPPER}}  .ablocks-icon-wrap',
			Icon::get_wrapper_css( $attributes ),
			Icon::get_wrapper_css( $attributes, 'Tablet' ),
			Icon::get_wrapper_css( $attributes, 'Mobile' )
		);

		$css_generator->add_class_styles(
			'{{WRAPPER}}  .ablocks-icon-wrap img.ablocks-image-icon',
			Icon::get_element_image_css( $attributes )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}}  .ablocks-icon-wrap svg.ablocks-svg-icon',
			Icon::get_element_css( $attributes ),
		);

		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-coupon-code',
			$this->get_coupon_text_css( $attributes ),
			$this->get_coupon_text_css( $attributes, 'Tablet' ),
			$this->get_coupon_text_css( $attributes, 'Mobile' )
		);

		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-coupon-clipboard',
			$this->get_btn_text_css( $attributes ),
			$this->get_btn_text_css( $attributes, 'Tablet' ),
			$this->get_btn_text_css( $attributes, 'Mobile' )
		);

		return $css_generator->generate_css();
	}


	public function get_wrapper_css( $attributes, $device = '' ) {
		$css = [];

		if ( isset( $attributes['position'][ 'value' . $device ] ) ) {
			$css['justify-content'] = $attributes['position'][ 'value' . $device ];
		}

		return $css;
	}

	public function get_button_hover_css( $attributes, $device = '' ) {
		$css = [];

		if ( isset( $attributes['textColorH'] ) ) {
			$css['color'] = $attributes['textColorH'];
		}
		if ( isset( $attributes['backgroundH'] ) ) {
			$css['background'] = $attributes['backgroundH'];
		}

		return array_merge(
			isset( $attributes['padding'] ) ? Dimensions::get_css( $attributes['padding'], 'padding', $device ) : [],
			$css
		);
	}


	public function get_coupon_text_css( $attributes, $device = '' ) {
		$css = [
			'color' => $attributes['couponCodeColor'],
			'background' => $attributes['couponCodeBgColor'],
		];

		return array_merge(
			$css,
			isset( $attributes['couponBorder'] ) ? Border::get_css( $attributes['couponBorder'], '', $device ) : [],
			isset( $attributes['couponPadding'] ) ? Dimensions::get_css( $attributes['couponPadding'], 'padding', $device ) : [],
			Typography::get_css( $attributes['couponTypography'], '', $device ),
			TextShadow::get_css( $attributes['couponTextShadow'], '', $device )
		);
	}

	public function get_btn_text_css( $attributes, $device = '' ) {
		$css = [
			'color' => $attributes['couponBtnTextColor'],
			'background' => $attributes['couponBtnBgColor'],
		];

		return array_merge(
			$css,
			Border::get_css( $attributes['buttonBorder'], '', $device ),
			Dimensions::get_css( $attributes['buttonPadding'], 'padding', $device ),
			Typography::get_css( $attributes['buttonTypography'], '', $device ),
			TextShadow::get_css( $attributes['buttonTextShadow'], '', $device )
		);
	}

	public function get_icon_css( $attributes, $device = '' ) {

		return ! empty( $attributes['iconRotate'] ) ? [ 'transform' => 'rotate(' . $attributes['iconRotate'] . 'deg)' ] : [];

	}

}
