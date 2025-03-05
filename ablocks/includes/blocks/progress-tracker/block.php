<?php
namespace ABlocks\Blocks\ProgressTracker;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ABlocks\Classes\BlockBaseAbstract;
use ABlocks\Classes\CssGenerator;
use ABlocks\Controls\Alignment;
use ABlocks\Controls\TextShadow;
use ABlocks\Controls\TextStroke;
use ABlocks\Controls\Typography;
use Google\Service\Slides\Shadow;
use ABlocks\Controls\Border;
use ABlocks\Controls\Range;

class Block extends BlockBaseAbstract {
	protected $block_name = 'progress-tracker';

	public function build_css( $attributes ) {
			// Generate CSS start
			$css_generator = new CssGenerator( $attributes );

			// Generate wrapper CSS start
			$css_generator->add_class_styles(
				'{{WRAPPER}} .ablocks-block-progress-circle',
				$this->get_wrapper_css( $attributes ),
				$this->get_wrapper_css( $attributes, 'Tablet' ),
				$this->get_wrapper_css( $attributes, 'Mobile' )
			);

		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-block-progress__text',
			$this->get_content_css( $attributes ),
			$this->get_content_css( $attributes, 'Tablet' ),
			$this->get_content_css( $attributes, 'Mobile' )
		);
		// Progress Bar Background CSS
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-block-progress-bar-track',
			$this->get_progress_bar_track_css( $attributes ),
			$this->get_progress_bar_track_css( $attributes, 'Tablet' ),
			$this->get_progress_bar_track_css( $attributes, 'Mobile' )
		);

		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-block-progress-bar-track:hover',
			$this->get_progress_bar_hover_css( $attributes ),
			$this->get_progress_bar_hover_css( $attributes, 'Tablet' ),
			$this->get_progress_bar_hover_css( $attributes, 'Mobile' )
		);

		// Progress Bar Progress CSS
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-block-progress-bar',
			$this->get_progress_bar_css( $attributes )
		);

		// circle css
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-block-progress-circle__svg-bar',
			$this->get_progress_circle_css( $attributes ),
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-block-progress-circle__svg-track',
			$this->get_progress_circle_bg_css( $attributes ),
		);

		return $css_generator->generate_css();
	}

	public function get_wrapper_css( $attributes, $device = '' ) {
		$alignment_key = 'value' . $device;
		$alignment = isset( $attributes['alignment'][ $alignment_key ] )
					 ? $attributes['alignment'][ $alignment_key ]
					 : 'center';
		switch ( $alignment ) {
			case 'center':
				$justify_content_value = 'center';
				break;
			case 'right':
				$justify_content_value = 'flex-end';
				break;
			default:
				$justify_content_value = 'flex-start';
				break;
		}
		return array(
			'display' => 'flex',
			'justify-content' => $justify_content_value,
		);
	}


	public function get_content_css( $attributes, $device = '' ) {
		$content_css = [];
		if ( ! empty( $attributes['contentColor'] ) ) {
			$content_css['color'] = $attributes['contentColor'];
		}
		$typography = isset( $attributes['contentTypography'] ) ? $attributes['contentTypography'] : '';
		$text_stroke = isset( $attributes['contentTextStroke'] ) ? $attributes['contentTextStroke'] : '';
		$text_shadow = isset( $attributes['contentTextShadow'] ) ? $attributes['contentTextShadow'] : '';
		return array_merge(
			$content_css,
			Typography::get_css( $typography, '', $device ),
			TextStroke::get_css( $text_stroke, '', $device ),
			TextShadow::get_css( $text_shadow, '', $device )
		);
	}
	public function get_progress_bar_track_css( $attributes, $device = '' ) {
		$css = [];
		if ( ! empty( $attributes['barBackgroundColor'] ) ) {
			$css['background'] = $attributes['barBackgroundColor'];
		}
		return array_merge(
			Range::get_css([
				'attributeValue' => $attributes['barHeightSize'],
				'attribute_object_key' => 'value',
				'isResponsive' => true,
				'defaultValue' => 40,
				'hasUnit' => true,
				'unitDefaultValue' => 'px',
				'property' => 'height',
				'device' => $device,
			]),
			isset( $attributes['barBorder'] ) ? Border::get_css( $attributes['barBorder'], '', $device ) : [],
			$css,
		);
	}
	public function get_progress_bar_hover_css( $attributes, $device = '' ) {
		return array_merge(
			isset( $attributes['barBorder'] ) ? Border::get_hover_css( $attributes['barBorder'], '', $device ) : []
		);
	}

	public function get_progress_bar_css( $attributes ) {
		$css = [];
		if ( ! empty( $attributes['barProgressColor'] ) ) {
			$css['background'] = $attributes['barProgressColor'];
		}
		if ( ! empty( $attributes['direction'] ) && $attributes['direction'] === 'left' ) {
			$css['justify-content'] = 'right';
		} elseif ( ! empty( $attributes['direction'] ) && $attributes['direction'] === 'right' ) {
			$css['justify-content'] = 'left';
		}
		return $css;
	}

	public function get_progress_circle_css( $attributes ) {
		$css = [];
		if ( $attributes['circleProgressColor'] ) {
			$css['stroke'] = $attributes['circleProgressColor'];
		}
		return $css;
	}

	public function get_progress_circle_bg_css( $attributes ) {
		$css = [];
		if ( $attributes['circleBackgroundColor'] ) {
			$css['stroke'] = $attributes['circleBackgroundColor'];
		}
		return array_merge(
			Range::get_css([
				'attributeValue' => $attributes['circleStrokeSize'],
				'attribute_object_key' => 'value',
				'defaultValue' => 10,
				'unitDefaultValue' => 'px',
				'property' => 'stroke-width'
			]),
			$css,
		);
	}
}
