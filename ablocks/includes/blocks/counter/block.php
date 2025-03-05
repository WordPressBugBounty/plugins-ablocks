<?php
namespace ABlocks\Blocks\Counter;

use ABlocks\Classes\BlockBaseAbstract;
use ABlocks\Classes\CssGenerator;
use ABlocks\Controls\Alignment;
use ABlocks\Controls\Typography;
use ABlocks\Controls\Dimensions;
use ABlocks\Controls\Icon;
use ABlocks\Controls\Range;

class Block extends BlockBaseAbstract {
	protected $block_name = 'counter';

	public function build_css( $attributes ) {
		$css_generator = new CssGenerator( $attributes, $this->block_name );
		// wrapper css
		$css_generator->add_class_styles(
			'{{WRAPPER}}',
			$this->get_wrapper_css( $attributes ),
			$this->get_wrapper_css( $attributes, 'Tablet' ),
			$this->get_wrapper_css( $attributes, 'Mobile' ),
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}}.ablocks-block--counter--number .ablocks-block-container',
			$this->get_number_wrapper_css( $attributes ),
			$this->get_number_wrapper_css( $attributes, 'Tablet' ),
			$this->get_number_wrapper_css( $attributes, 'Mobile' ),
		);
		// number text css
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-counter__content',
			$this->get_number_text_css( $attributes ),
			$this->get_number_text_css( $attributes, 'Tablet' ),
			$this->get_number_text_css( $attributes, 'Mobile' ),
		);
		// heading text css
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-counter__text',
			$this->get_heading_text_css( $attributes ),
			$this->get_heading_text_css( $attributes, 'Tablet' ),
			$this->get_heading_text_css( $attributes, 'Mobile' ),
		);
		// counter bar css

		// counter circle css
		$css_generator->add_class_styles(
			'{{WRAPPER}}.ablocks-block--counter--bar .ablocks-bar-counter__background',
			$this->get_counter_bar_bg_css( $attributes )
		);

		$css_generator->add_class_styles(
			'{{WRAPPER}}.ablocks-block--counter--bar .ablocks-bar-counter__progress',
			$this->get_counter_bar_progress_css( $attributes ),
			$this->get_counter_bar_progress_css( $attributes, 'Tablet' ),
			$this->get_counter_bar_progress_css( $attributes, 'Mobile' )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}}.ablocks-block--counter--circle',
			$this->get_counter_circle_css( $attributes ),
			$this->get_counter_circle_css( $attributes, 'Tablet' ),
			$this->get_counter_circle_css( $attributes, 'Mobile' )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}}.ablocks-block--counter--circle .ablocks-circle-counter__background',
			$this->get_counter_circle_bg_css( $attributes )
		);

		$css_generator->add_class_styles(
			'{{WRAPPER}}.ablocks-block--counter--circle .ablocks-circle-counter__progress',
			$this->get_counter_circle_progress_css( $attributes )
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

		return $css_generator->generate_css();
	}

	public function get_wrapper_css( $attributes, $device = '' ) {
		$alignment = ! empty( $attributes['alignment'] ) ? $attributes['alignment'] : '';
		return Alignment::get_css( $alignment, 'text-align', $device );
	}
	public function get_number_wrapper_css( $attributes, $device = '' ) {
		$counterNumberWrapperCSS = array(
			'display' => 'flex',
			'align-items' => 'center',
		);
		if ( isset( $attributes['alignment'][ 'value' . $device ] ) ) {
			$counterNumberWrapperCSS['justify-content'] = $attributes['alignment'][ 'value' . $device ];
		}
		return $counterNumberWrapperCSS;
	}
	public function get_number_text_css( $attributes, $device = '' ) {
		$css = [];
		$number_color = ! empty( $attributes['numberColor'] ) ? $attributes['numberColor'] : '';
		if ( $number_color ) {
			$css['color'] = $attributes['numberColor'];
		}
		if ( isset( $attributes['mediaPosition'] ) && $attributes['mediaPosition'] === 'leftOfNumber' || isset( $attributes['mediaPosition'] ) && $attributes['mediaPosition'] === 'rightOfNumber' || isset( $attributes['layout'] ) && $attributes['layout'] === 'bar' ) {
			$css['display'] = 'inline-flex';
			$css['align-items'] = 'center';
			$css['justify-content'] = 'center';
		} else {
			$css['display'] = 'block';
		}
		return array_merge(
			isset( $attributes['numberTypography'] ) ? Typography::get_css( $attributes['numberTypography'], '', $device ) : [],
			isset( $attributes['numberMargin'] ) ? Dimensions::get_css( $attributes['numberMargin'], 'margin', '', $device ) : [],
			$css
		);
	}

	public function get_heading_text_css( $attributes, $device = '' ) {
		$css = [];
		$heading_color = ! empty( $attributes['headingColor'] ) ? $attributes['headingColor'] : '';
		if ( $heading_color ) {
			$css['color'] = $attributes['headingColor'];
		}
		return array_merge(
			isset( $attributes['headingTypography'] ) ? Typography::get_css( $attributes['headingTypography'], '', $device ) : [],
			isset( $attributes['headingMargin'] ) ? Dimensions::get_css( $attributes['headingMargin'], 'margin', $device ) : [],
			$css
		);
	}


	public function get_counter_bar_bg_css( $attributes ) {
		$counter_bar_bg_css = [];

		if ( ! empty( $attributes['barBackgroundColor'] ) ) {
			$counter_bar_bg_css['background'] = $attributes['barBackgroundColor'];
		}

		return $counter_bar_bg_css;
	}

	public function get_counter_bar_progress_css( $attributes, $device = '' ) {
		$counter_bar_progress_css = [];
		if ( ! empty( $attributes['barProgressColor'] ) ) {
			$counter_bar_progress_css['background'] = $attributes['barProgressColor'];
		}
		if ( ! empty( $attributes['barHeadingPosition'] ) ) {
			if ( $attributes['barHeadingPosition'] === 'inner' ) {
				$counter_bar_progress_css['justify-content'] = 'space-between';
			} elseif (
				$attributes['barHeadingPosition'] === 'top' ||
				$attributes['barHeadingPosition'] === 'bottom'
			) {
				$counter_bar_progress_css['justify-content'] = 'right';
			}
		}

		return array_merge(
			Range::get_css([
				'attributeValue' => $attributes['barSize'],
				'attribute_object_key' => 'value',
				'isResponsive' => true,
				'defaultValue' => 40,
				'hasUnit' => true,
				'unitDefaultValue' => 'px',
				'property' => 'height',
				'device' => $device,
			]),
			$counter_bar_progress_css,
		);
	}

	public function get_counter_circle_css( $attributes, $device = '' ) {
		$counterCircleCSS = array(
			'align-items' => 'center',
		);
		if ( isset( $attributes['alignment'][ 'value' . $device ] ) ) {
			$alignmentValue = $attributes['alignment'][ 'value' . $device ];

			if ( $alignmentValue === 'center' ) {
				$counterCircleCSS['align-items'] = 'center';
			} elseif ( $alignmentValue === 'left' ) {
				$counterCircleCSS['align-items'] = 'flex-start';
			} elseif ( $alignmentValue === 'right' ) {
				$counterCircleCSS['align-items'] = 'flex-end';
			}
		}
		return $counterCircleCSS;
	}
	public function get_counter_circle_bg_css( $attributes ) {
		$counter_circle_bg_css = [];

		if ( ! empty( $attributes['circleBackgroundColor'] ) ) {
			$counter_circle_bg_css['stroke'] = $attributes['circleBackgroundColor'];
		}

		if ( ! empty( $attributes['circleStrokeSize'] ) ) {
			$counter_circle_bg_css['stroke-width'] = $attributes['circleStrokeSize'];
		}

		return array_merge(
			Range::get_css([
				'attributeValue' => $attributes['circleStrokeSize'],
				'attribute_object_key' => 'value',
				'defaultValue' => 20,
				'property' => 'stroke-width',
			]),
			$counter_circle_bg_css,
		);
	}

	public function get_counter_circle_progress_css( $attributes ) {
		$counter_circle_progress_css = [];

		if ( ! empty( $attributes['circleProgressColor'] ) ) {
			$counter_circle_progress_css['stroke'] = $attributes['circleProgressColor'];
		}

		return array_merge(
			Range::get_css([
				'attributeValue' => $attributes['circleStrokeSize'],
				'attribute_object_key' => 'value',
				'defaultValue' => 20,
				'property' => 'stroke-width',
			]),
			$counter_circle_progress_css,
		);
	}

}
