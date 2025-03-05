<?php
namespace ABlocks\Blocks\AcademyEnrollForm;

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
	protected $block_name = 'academy-enroll-form';

	public function build_css( $attributes ) {
		$css_generator = new CssGenerator( $attributes );

		$css_generator->add_class_styles(
			'{{WRAPPER}} .academy-enroll-form .academy-widget-enroll__continue a',
			$this->getStartButtonCss( $attributes ),
			$this->getStartButtonCss( $attributes, 'Tablet' ),
			$this->getStartButtonCss( $attributes, 'Mobile' )
		);

		$css_generator->add_class_styles(
			'{{WRAPPER}} .academy-enroll-form .academy-widget-enroll__continue a:hover',
			$this->getStartButtonHoverCss( $attributes ),
			$this->getStartButtonHoverCss( $attributes, 'Tablet' ),
			$this->getStartButtonHoverCss( $attributes, 'Mobile' )
		);

		$css_generator->add_class_styles(
			'{{WRAPPER}} .academy-enroll-form .academy-widget-enroll__enroll-form button',
			$this->getEnrollButtonCss( $attributes ),
			$this->getEnrollButtonCss( $attributes, 'Tablet' ),
			$this->getEnrollButtonCss( $attributes, 'Mobile' )
		);

		$css_generator->add_class_styles(
			'{{WRAPPER}} .academy-enroll-form .academy-widget-enroll__enroll-form button:hover',
			$this->getEnrollButtonHoverCss( $attributes ),
			$this->getEnrollButtonHoverCss( $attributes, 'Tablet' ),
			$this->getEnrollButtonHoverCss( $attributes, 'Mobile' )
		);

		return $css_generator->generate_css();
	}



	public function getStartButtonCss( $attributes, $device = '' ) {
		$css = array();
		$start_btn_typography = ! empty( $attributes['start_btn_typography'] ) ? Typography::get_css( $attributes['start_btn_typography'], '', $device ) : array();
		$start_btn_padding = ! empty( $attributes['start_btn_padding'] ) ? Dimensions::get_css( $attributes['start_btn_padding'], 'padding', $device ) : array();
		$start_btn_border = ! empty( $attributes['start_btn_border'] ) ? Border::get_css( $attributes['start_btn_border'], '', $device ) : array();
		if ( ! empty( $attributes['start_btn_color'] ) ) {
			$css['color'] = $attributes['start_btn_color'];
		}
		if ( ! empty( $attributes['start_btn_bg_color'] ) ) {
			$css['background'] = $attributes['start_btn_bg_color'];
		}
		return array_merge(
			$start_btn_typography,
			$start_btn_padding,
			$start_btn_border,
			$css,
		);
	}
	public function getStartButtonHoverCss( $attributes, $device = '' ) {
		$css = array();
		$start_btn_border = ! empty( $attributes['start_btn_border'] ) ? Border::get_hover_css( $attributes['start_btn_border'], '', $device ) : array();
		if ( ! empty( $attributes['start_btn_color_hover'] ) ) {
			$css['color'] = $attributes['start_btn_color_hover'];
		}
		if ( ! empty( $attributes['start_btn_bg_hover_color'] ) ) {
			$css['background'] = $attributes['start_btn_bg_hover_color'];
		}
		return array_merge(
			$css,
			$start_btn_border
		);
	}

	public function getEnrollButtonCss( $attributes, $device = '' ) {
		$css = array();
		$enroll_btn_typography = ! empty( $attributes['enroll_btn_typography'] ) ? Typography::get_css( $attributes['enroll_btn_typography'], '', $device ) : array();
		$enroll_btn_padding = ! empty( $attributes['enroll_btn_padding'] ) ? Dimensions::get_css( $attributes['enroll_btn_padding'], 'padding', $device ) : array();
		$enroll_btn_border = ! empty( $attributes['enroll_btn_border'] ) ? Border::get_css( $attributes['enroll_btn_border'], '', $device ) : array();
		if ( ! empty( $attributes['enroll_btn_color'] ) ) {
			$css['color'] = $attributes['enroll_btn_color'];
		}
		if ( ! empty( $attributes['enroll_btn_bg_color'] ) ) {
			$css['background'] = $attributes['enroll_btn_bg_color'];
		}
		return array_merge(
			$enroll_btn_typography,
			$enroll_btn_padding,
			$enroll_btn_border,
			$css,
		);
	}
	public function getEnrollButtonHoverCss( $attributes, $device = '' ) {
		$css = array();
		$enroll_btn_border = ! empty( $attributes['enroll_btn_border'] ) ? Border::get_hover_css( $attributes['enroll_btn_border'], '', $device ) : array();
		if ( ! empty( $attributes['enroll_btn_color_hover'] ) ) {
			$css['color'] = $attributes['enroll_btn_color_hover'];
		}
		if ( ! empty( $attributes['enroll_btn_bg_hover_color'] ) ) {
			$css['background'] = $attributes['enroll_btn_bg_hover_color'];
		}
		return array_merge(
			$css,
			$enroll_btn_border
		);
	}




	public function render_block_content( $attributes, $content, $block_instance ) {
		$attr_array = [
			'course_id'                 => Helper::get_attribute_value( $attributes, 'course_id' ),
		];
		$shortcode = '[academy_enroll_form  ' . Helper::attr_shortcode( $attr_array ) . ']';
		echo do_shortcode( $shortcode );
	}

}
