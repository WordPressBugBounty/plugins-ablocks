<?php
namespace ABlocks\Blocks\Carousel;

use ABlocks\Classes\BlockBaseAbstract;
use ABlocks\Classes\CssGenerator;
use ABlocks\Classes\CssGeneratorV2;
use ABlocks\Controls\Alignment;
use ABlocks\Controls\Range;
use ABlocks\Controls\Dimensions;
use ABlocks\Controls\Border;


class Block extends BlockBaseAbstract {
	protected $block_name = 'carousel';
	protected $style_depends = [ 'ablocks-swiper-style' ];
	protected $script_depends = [ 'ablocks-swiper-script' ];

	public function build_css_v1( $attributes ) {

		$css_generator = new CssGenerator( $attributes );

		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-carousel-swiper .swiper-wrapper',
			$this->get_carousel_css( $attributes ),
			$this->get_carousel_css( $attributes, 'Tablet' ),
			$this->get_carousel_css( $attributes, 'Mobile' ),
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-carousel-navigation__button',
			$this->get_navigation_button_css( $attributes ),
			$this->get_navigation_button_css( $attributes, 'Tablet' ),
			$this->get_navigation_button_css( $attributes, 'Mobile' ),
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-carousel-navigation__button--next',
			$this->get_navigation_next_button_css( $attributes ),
			$this->get_navigation_next_button_css( $attributes, 'Tablet' ),
			$this->get_navigation_next_button_css( $attributes, 'Mobile' ),
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-carousel-navigation__button--prev',
			$this->get_navigation_prev_button_css( $attributes ),
			$this->get_navigation_prev_button_css( $attributes, 'Tablet' ),
			$this->get_navigation_prev_button_css( $attributes, 'Mobile' ),
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-carousel-navigation__button .ablocks-icon-wrap',
			$this->get_navigation_icon_css( $attributes ),
			$this->get_navigation_icon_css( $attributes, 'Tablet' ),
			$this->get_navigation_icon_css( $attributes, 'Mobile' ),
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .swiper-pagination-bullet',
			$this->get_pagination_color_css( $attributes ),
			$this->get_pagination_color_css( $attributes, 'Tablet' ),
			$this->get_pagination_color_css( $attributes, 'Mobile' )
		);

		$css_generator->add_class_styles(
			'{{WRAPPER}} .swiper-pagination-bullet.swiper-pagination-bullet-active',
			$this->get_pagination_active_color_css( $attributes ),
			$this->get_pagination_active_color_css( $attributes, 'Tablet' ),
			$this->get_pagination_active_color_css( $attributes, 'Mobile' )
		);

		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-carousel-navigation__button .ablocks-svg-icon',
			$this->get_navigation_icon_svg_css( $attributes ),
			$this->get_navigation_icon_svg_css( $attributes, 'Tablet' ),
			$this->get_navigation_icon_svg_css( $attributes, 'Mobile' )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-carousel-navigation__button .ablocks-svg-icon:hover',
			$this->get_navigation_icon_svg_hover_css( $attributes ),
			$this->get_navigation_icon_svg_hover_css( $attributes, 'Tablet' ),
			$this->get_navigation_icon_svg_hover_css( $attributes, 'Mobile' )
		);
		return $css_generator->generate_css();
	}

	public function build_css_v2( $attributes ) {

		$css_generator = new CssGeneratorV2( $attributes, $this->block_name );

		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-carousel-swiper .swiper-wrapper',
			$this->get_carousel_css( $attributes ),
			$this->get_carousel_css( $attributes, 'Tablet' ),
			$this->get_carousel_css( $attributes, 'Mobile' ),
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-carousel-navigation__button',
			$this->get_navigation_button_css( $attributes ),
			$this->get_navigation_button_css( $attributes, 'Tablet' ),
			$this->get_navigation_button_css( $attributes, 'Mobile' ),
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-carousel-navigation__button--next',
			$this->get_navigation_next_button_css( $attributes ),
			$this->get_navigation_next_button_css( $attributes, 'Tablet' ),
			$this->get_navigation_next_button_css( $attributes, 'Mobile' ),
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-carousel-navigation__button--prev',
			$this->get_navigation_prev_button_css( $attributes ),
			$this->get_navigation_prev_button_css( $attributes, 'Tablet' ),
			$this->get_navigation_prev_button_css( $attributes, 'Mobile' ),
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-carousel-navigation__button .ablocks-icon-wrap',
			$this->get_navigation_icon_css( $attributes ),
			$this->get_navigation_icon_css( $attributes, 'Tablet' ),
			$this->get_navigation_icon_css( $attributes, 'Mobile' ),
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .swiper-pagination-bullet',
			$this->get_pagination_color_css( $attributes ),
			$this->get_pagination_color_css( $attributes, 'Tablet' ),
			$this->get_pagination_color_css( $attributes, 'Mobile' )
		);

		$css_generator->add_class_styles(
			'{{WRAPPER}} .swiper-pagination-bullet.swiper-pagination-bullet-active',
			$this->get_pagination_active_color_css( $attributes ),
			$this->get_pagination_active_color_css( $attributes, 'Tablet' ),
			$this->get_pagination_active_color_css( $attributes, 'Mobile' )
		);

		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-carousel-navigation__button .ablocks-svg-icon',
			$this->get_navigation_icon_svg_css( $attributes ),
			$this->get_navigation_icon_svg_css( $attributes, 'Tablet' ),
			$this->get_navigation_icon_svg_css( $attributes, 'Mobile' )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-carousel-navigation__button .ablocks-svg-icon:hover',
			$this->get_navigation_icon_svg_hover_css( $attributes ),
			$this->get_navigation_icon_svg_hover_css( $attributes, 'Tablet' ),
			$this->get_navigation_icon_svg_hover_css( $attributes, 'Mobile' )
		);
		return $css_generator->generate_css();
	}
	public function build_css( $attributes ) {
		if ( isset( $attributes['blockVersion'] ) && (int) $attributes['blockVersion'] === 2 ) {
			return $this->build_css_v2( $attributes );
		}
		return $this->build_css_v1( $attributes );
	}

	public function get_carousel_css( $attributes, $device = '' ) {
		$carousel_css = [];
		if ( ! empty( $attributes['verticalAlignment'] ) ) {
			$carousel_css['align-items'] = $attributes['verticalAlignment'];
		}

		return array_merge(
			Range::get_css([
				'attributeValue' => $attributes['carouselHeight'],
				'attribute_object_key' => 'value',
				'isResponsive' => true,
				'defaultValue' => 300,
				'hasUnit' => true,
				'unitDefaultValue' => 'px',
				'property' => 'min-height',
				'device' => $device,
			]),
			$carousel_css,
		);
	}


	public function get_navigation_button_css( $attributes, $device = '' ) {
		$navigation_button_css = [];

		return array_merge(
			Range::get_css([
				'attributeValue' => $attributes['navigationIconPositionY'],
				'attribute_object_key' => 'value',
				'defaultValue' => 50,
				'isResponsive' => false,
				'hasUnit' => true,
				'unitDefaultValue' => '%',
				'property' => 'top',
				'device' => $device,
			]),
			$navigation_button_css,
		);
	}


	public function get_navigation_prev_button_css( $attributes, $device = '' ) {
		$navigation_prev_button_css = [];

		return array_merge(
			Range::get_css([
				'attributeValue' => $attributes['navigationIconPositionPrevX'],
				'attribute_object_key' => 'value',
				'defaultValue' => -3,
				'hasUnit' => true,
				'isResponsive' => false,
				'unitDefaultValue' => '%',
				'property' => 'left',
				'device' => $device,
			]),
			$navigation_prev_button_css,
		);
	}

	public function get_navigation_next_button_css( $attributes, $device = '' ) {
		$navigation_next_button_css = [];

		return array_merge(
			Range::get_css([
				'attributeValue' => $attributes['navigationIconPositionNextX'],
				'attribute_object_key' => 'value',
				'defaultValue' => -3,
				'isResponsive' => false,
				'hasUnit' => true,
				'unitDefaultValue' => '%',
				'property' => 'right',
				'device' => $device,
			]),
			$navigation_next_button_css,
		);
	}


	public function get_navigation_icon_css( $attributes, $device = '' ) {
		$navigation_icon_css = [];

		return array_merge(
			Range::get_css([
				'attributeValue' => $attributes['navigationIconSize'],
				'attribute_object_key' => 'value',
				'defaultValue' => 35,
				'isResponsive' => true,
				'hasUnit' => true,
				'unitDefaultValue' => 'px',
				'property' => 'font-size',
				'device' => $device,
			]),
			$navigation_icon_css,
		);
	}


	public function get_navigation_icon_svg_css( $attributes, $device = '' ) {
		$navigation_icon_svg_css = [];
		if ( isset( $attributes['navigationIconColor'] ) ) {
			$navigation_icon_svg_css['fill'] = $attributes['navigationIconColor'];
		};
		if ( isset( $attributes['navigationIconBgColor'] ) ) {
			$navigation_icon_svg_css['background-color'] = $attributes['navigationIconBgColor'];
		};
		return array_merge(
			$navigation_icon_svg_css,
			Dimensions::get_css( $attributes['navigationIconPadding'], 'padding', $device ),
			Border::get_css( $attributes['navigationIconBorder'], '', $device ),
		);
	}
	public function get_navigation_icon_svg_hover_css( $attributes, $device = '' ) {
		$navigation_icon_svg_hover_css = [];
		if ( isset( $attributes['navigationIconColorH'] ) ) {
			$navigation_icon_svg_hover_css['fill'] = $attributes['navigationIconColorH'];
		};
		if ( isset( $attributes['navigationIconBgColorH'] ) ) {
			$navigation_icon_svg_hover_css['background-color'] = $attributes['navigationIconBgColorH'];
		};
		return array_merge(
			Range::get_css([
				'attributeValue' => $attributes['navigationIconTransition'],
				'attribute_object_key' => 'value',
				'defaultValue' => 10,
				'unitDefaultValue' => 's',
				'property' => 'transition-duration',
				'device' => $device,
			]),
			$navigation_icon_svg_hover_css,
			Border::get_hover_css( $attributes['navigationIconBorder'], '', $device ),
		);
	}

	public function get_pagination_color_css( $attributes ) {
		$pagination_color_css = [];
		if ( isset( $attributes['paginationColor'] ) ) {
			$pagination_color_css['background-color'] = $attributes['paginationColor'];
		};
		return $pagination_color_css;
	}

	public function get_pagination_active_color_css( $attributes ) {
		$pagination_active_color_css = [];
		if ( isset( $attributes['paginationActiveColor'] ) ) {
			$pagination_active_color_css['background-color'] = $attributes['paginationActiveColor'];
		};
		return $pagination_active_color_css;
	}



}
