<?php
namespace ABlocks\Blocks\AcademyCourses;

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

class Block extends BlockBaseAbstract {
	protected $block_name = 'academy-courses';

	public function build_css( $attributes ) {
		$css_generator = new CssGenerator( $attributes, $this->block_name );

		$course_category_desktop_css = $this->get_course_card_category_css( $attributes );
		if ( ! empty( $attributes['category_color'] ) ) {
			$course_category_desktop_css['color'] = $attributes['category_color'];
		}
		$css_generator->add_class_styles(
			'{{WRAPPER}} .academy-courses--grid .academy-course .academy-course__meta--categroy a',
			$course_category_desktop_css,
			$this->get_course_card_category_css( $attributes, 'Tablet' ),
			$this->get_course_card_category_css( $attributes, 'Mobile' )
		);

		$course_category_desktop_hover_css = [];
		if ( ! empty( $attributes['category_hover_color'] ) ) {
			$course_category_desktop_hover_css['color'] = $attributes['category_hover_color'];
		}
		$css_generator->add_class_styles(
			'{{WRAPPER}} .academy-courses--grid .academy-course .academy-course__meta--categroy:hover a',
			$course_category_desktop_hover_css,
		);

		$course_title_desktop_css = $this->get_course_card_title_css( $attributes );
		if ( ! empty( $attributes['title_color'] ) ) {
			$course_title_desktop_css['color'] = $attributes['title_color'];
		}
		$css_generator->add_class_styles(
			'{{WRAPPER}} .academy-courses--grid .academy-course .academy-course__title, 
            {{WRAPPER}} .academy-courses--grid .academy-course .academy-course__title a',
			$course_title_desktop_css,
			$this->get_course_card_title_css( $attributes, 'Tablet' ),
			$this->get_course_card_title_css( $attributes, 'Mobile' )
		);

		$course_title_desktop_hover_css = [];
		if ( ! empty( $attributes['title_hover_color'] ) ) {
			$course_title_desktop_hover_css['color'] = $attributes['title_hover_color'];
		}
		$css_generator->add_class_styles(
			'{{WRAPPER}} .academy-courses--grid .academy-course .academy-course__title:hover, 
            {{WRAPPER}} .academy-courses--grid .academy-course .academy-course__title:hover a',
			$course_title_desktop_hover_css,
		);

		$course_author_desktop_css = $this->get_course_card_title_css( $attributes );
		if ( ! empty( $attributes['author_color'] ) ) {
			$course_author_desktop_css['color'] = $attributes['author_color'];
		}
		$css_generator->add_class_styles(
			'{{WRAPPER}} .academy-courses--grid .academy-course .academy-course__author,
            {{WRAPPER}} .academy-courses--grid .academy-course .academy-course__author .author,
            {{WRAPPER}} .academy-courses--grid .academy-course .academy-course__author .author a',
			$course_author_desktop_css,
			$this->get_course_card_author_css( $attributes, 'Tablet' ),
			$this->get_course_card_author_css( $attributes, 'Mobile' )
		);

		$course_author_desktop_hover_css = [];
		if ( ! empty( $attributes['author_hover_color'] ) ) {
			$course_author_desktop_hover_css['color'] = $attributes['author_hover_color'];
		}
		$css_generator->add_class_styles(
			'{{WRAPPER}} .academy-courses--grid .academy-course .academy-course__author:hover,
            {{WRAPPER}} .academy-courses--grid .academy-course .academy-course__author:hover .author,
            {{WRAPPER}} .academy-courses--grid .academy-course .academy-course__author:hover .author a',
			$course_author_desktop_hover_css,
		);

		$course_rating_desktop_css = $this->get_course_card_rating_css( $attributes );
		if ( ! empty( $attributes['rating_color'] ) ) {
			$course_rating_desktop_css['color'] = $attributes['rating_color'];
		}
		$css_generator->add_class_styles(
			'{{WRAPPER}} .academy-courses--grid .academy-course .academy-course__rating,
            {{WRAPPER}} .academy-courses--grid .academy-course .academy-course__rating .academy-group-star, 
            {{WRAPPER}} .academy-courses--grid .academy-course .academy-course__rating .academy-group-star .academy-icon::before, 
            {{WRAPPER}} .academy-courses--grid .academy-course .academy-course__rating .academy-course__rating-count',
			$course_rating_desktop_css,
			$this->get_course_card_rating_css( $attributes, 'Tablet' ),
			$this->get_course_card_rating_css( $attributes, 'Mobile' )
		);

		$course_rating_desktop_hover_css = [];
		if ( ! empty( $attributes['rating_hover_color'] ) ) {
			$course_rating_desktop_hover_css['color'] = $attributes['rating_hover_color'];
		}
		$css_generator->add_class_styles(
			'{{WRAPPER}} .academy-courses--grid .academy-course .academy-course__rating:hover,
            {{WRAPPER}} .academy-courses--grid .academy-course .academy-course__rating:hover .academy-group-star, 
            {{WRAPPER}} .academy-courses--grid .academy-course .academy-course__rating:hover .academy-group-star .academy-icon::before, 
            {{WRAPPER}} .academy-courses--grid .academy-course .academy-course__rating:hover .academy-course__rating-count',
			$course_rating_desktop_hover_css,
		);

		$course_price_css = $this->get_course_card_price_css( $attributes );
		if ( ! empty( $attributes['price_color'] ) ) {
			$course_price_css['color'] = $attributes['price_color'];
		}
		$css_generator->add_class_styles(
			'{{WRAPPER}} .academy-courses--grid .academy-course .academy-course__price',
			$course_price_css,
			$this->get_course_card_price_css( $attributes, 'Tablet' ),
			$this->get_course_card_price_css( $attributes, 'Mobile' )
		);

		$course_price_hover_css = [];
		if ( ! empty( $attributes['price_hover_color'] ) ) {
			$course_price_hover_css['color'] = $attributes['price_hover_color'];
		}
		$css_generator->add_class_styles(
			'{{WRAPPER}} .academy-courses--grid .academy-course .academy-course__price:hover',
			$course_price_hover_css,
		);

		$css_generator->add_class_styles(
			'{{WRAPPER}} .academy-courses--grid .academy-row .academy-course,
            {{WRAPPER}} .academy-courses--grid .academy-row .academy-course, 
            {{WRAPPER}} .academy-courses--grid .academy-row .academy-course, 
            {{WRAPPER}} .academy-courses--grid .academy-row .academy-course',
			$this->get_course_card_css( $attributes ),
			$this->get_course_card_css( $attributes, 'Tablet' ),
			$this->get_course_card_css( $attributes, 'Mobile' )
		);

		$css_generator->add_class_styles(
			'{{WRAPPER}} .academy-courses--grid .academy-row .academy-course:hover,
            {{WRAPPER}} .academy-courses--grid .academy-row .academy-course:hover, 
            {{WRAPPER}} .academy-courses--grid .academy-row .academy-course:hover, 
            {{WRAPPER}} .academy-courses--grid .academy-row .academy-course:hover',
			$this->get_course_card_hover_css( $attributes ),
			$this->get_course_card_hover_css( $attributes, 'Tablet' ),
			$this->get_course_card_hover_css( $attributes, 'Mobile' )
		);

		$css_generator->add_class_styles(
			'{{WRAPPER}} .academy-courses--grid .academy-row .academy-course .academy-add-to-wishlist-btn',
			$this->get_wish_icon_css( $attributes ),
			$this->get_wish_icon_css( $attributes, 'Tablet' ),
			$this->get_wish_icon_css( $attributes, 'Mobile' )
		);

		$css_generator->add_class_styles(
			'{{WRAPPER}} .academy-courses--grid .academy-row .academy-course .academy-add-to-wishlist-btn:hover',
			$this->get_wish_icon_hover_css( $attributes ),
			$this->get_wish_icon_hover_css( $attributes, 'Tablet' ),
			$this->get_wish_icon_hover_css( $attributes, 'Mobile' )
		);

		return $css_generator->generate_css();
	}

	public function get_course_card_category_css( $attributes, $device = '' ) {
		$course_category_typography_css = ! empty( $attributes['cat_typography'] ) ? Typography::get_css( $attributes['cat_typography'], '', $device ) : array();
		return $course_category_typography_css;
	}

	public function get_course_card_title_css( $attributes, $device = '' ) {
		$course_title_typography_css = ! empty( $attributes['title_typography'] ) ? Typography::get_css( $attributes['title_typography'], '', $device ) : array();
		return $course_title_typography_css;
	}

	public function get_course_card_author_css( $attributes, $device = '' ) {
		$course_author_typography_css = ! empty( $attributes['author_typography'] ) ? Typography::get_css( $attributes['author_typography'], '', $device ) : array();
		return $course_author_typography_css;
	}

	public function get_course_card_rating_css( $attributes, $device = '' ) {
		$course_rating_typography_css = ! empty( $attributes['rating_typography'] ) ? Typography::get_css( $attributes['rating_typography'], '', $device ) : array();
		return $course_rating_typography_css;
	}

	public function get_course_card_price_css( $attributes, $device = '' ) {
		$course_price_typography_css = ! empty( $attributes['price_typography'] ) ? Typography::get_css( $attributes['price_typography'], '', $device ) : array();
		return $course_price_typography_css;
	}

	public function get_wish_icon_css( $attributes, $device = '' ) {
		$wish_icon_background_css = ! empty( $attributes['wish_icon_background'] ) ? Background::get_css( $attributes['wish_icon_background'], 'background', $device ) : array();
		if ( ! empty( $attributes['wish_icon_color'] ) ) {
			$wish_icon_background_css['color'] = $attributes['wish_icon_color'];
		}
		return $wish_icon_background_css;
	}

	public function get_wish_icon_hover_css( $attributes, $device = '' ) {
		$wish_icon_background_css = ! empty( $attributes['wish_icon_background'] ) ? Background::get_hover_css( $attributes['wish_icon_background'], 'background', $device ) : array();
		if ( ! empty( $attributes['wish_icon_hover_color'] ) ) {
			$wish_icon_background_css['color'] = $attributes['wish_icon_hover_color'];
		}
		return $wish_icon_background_css;
	}

	public function get_course_card_css( $attributes, $device = '' ) {
		$course_background_css = ! empty( $attributes['card_background'] ) ? Background::get_css( $attributes['card_background'], 'background', $device ) : array();
		$course_border_css = ! empty( $attributes['card_border'] ) ? Border::get_css( $attributes['card_border'], '', $device ) : array();
		$course_margin_css = ! empty( $attributes['card_margin'] ) ? Dimensions::get_css( $attributes['card_margin'], 'margin', $device ) : array();
		$course_padding_css = ! empty( $attributes['card_padding'] ) ? Dimensions::get_css( $attributes['card_padding'], 'padding', $device ) : array();

		return array_merge(
			$course_background_css,
			$course_border_css,
			$course_margin_css,
			$course_padding_css
		);
	}

	public function get_course_card_hover_css( $attributes, $device = '' ) {
		$course_background_hover_css = ! empty( $attributes['card_background'] ) ? Background::get_hover_css( $attributes['card_background'], 'background', $device ) : array();
		$course_border_hover_css = ! empty( $attributes['card_border'] ) ? Border::get_hover_css( $attributes['card_border'], '', $device ) : array();
		$course_margin_hover_css = ! empty( $attributes['card_hover_margin'] ) ? Dimensions::get_css( $attributes['card_hover_margin'], 'margin', $device ) : array();
		$course_padding_hover_css = ! empty( $attributes['card_hover_padding'] ) ? Dimensions::get_css( $attributes['card_hover_padding'], 'padding', $device ) : array();

		return array_merge(
			$course_background_hover_css,
			$course_border_hover_css,
			$course_margin_hover_css,
			$course_padding_hover_css
		);
	}





	public function render_block_content( $attributes, $content, $block_instance ) {
		$attr_array = [
			'count'                 => Helper::get_attribute_value( $attributes, 'course_count' ),
			'column_per_row'        => Helper::get_attribute_value( $attributes, 'course_columns' ),
			'has_pagination'        => Helper::get_attribute_value( $attributes, 'show_pagination' ),
			'course_level'          => Helper::get_attribute_value( $attributes, 'difficulty_levels' ),
			'price_type'            => Helper::get_attribute_value( $attributes, 'price_types' ),
			'orderby'               => Helper::get_attribute_value( $attributes, 'order_by' ),
			'order'                 => Helper::get_attribute_value( $attributes, 'course_order' ),
			'ids'                   => Helper::get_attribute_value( $attributes, 'course_ids' ),
			'exclude_ids'           => Helper::get_attribute_value( $attributes, 'course_exclude_ids' ),
			'category'              => Helper::get_attribute_value( $attributes, 'course_categories' ),
			'cat_not_in'            => Helper::get_attribute_value( $attributes, 'course_exclude_categories' ),
			'tag'                   => Helper::get_attribute_value( $attributes, 'course_tags' ),
			'tag_not_in'            => Helper::get_attribute_value( $attributes, 'course_exclude_tags' ),
		];
		$shortcode = '[academy_courses ' . Helper::attr_shortcode( $attr_array ) . ']';
		echo do_shortcode( $shortcode );
	}

}
