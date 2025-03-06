<?php
namespace ABlocks\Blocks\StripeButton;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ABlocks\Classes\BlockBaseAbstract;
use ABlocks\Classes\CssGenerator;
use ABlocks\Controls\Alignment;
use ABlocks\Controls\Border;
use ABlocks\Controls\BoxShadow;
use ABlocks\Controls\Typography;
use ABlocks\Controls\Dimensions;
use ABlocks\Controls\TextShadow;
use ABlocks\Controls\Icon;
class Block extends BlockBaseAbstract {
	protected $block_name = 'stripe-button';

	public function build_css( $attributes ) {

		// Generate CSS start
		$css_generator = new CssGenerator( $attributes, $this->block_name );

		// Generate wrapper CSS start
		$css_generator->add_class_styles(
			'{{WRAPPER}}',
			$this->get_wrapper_css( $attributes ),
			$this->get_wrapper_css( $attributes, 'Tablet' ),
			$this->get_wrapper_css( $attributes, 'Mobile' )
		);
		// Generate wrapper CSS end

		// Generate button CSS start
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-block-container .ablocks-stripe-button',
			$this->get_button_css( $attributes ),
			$this->get_button_css( $attributes, 'Tablet' ),
			$this->get_button_css( $attributes, 'Mobile' )
		);
		// Generate button CSS end

		// Generate button hover CSS start
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-block-container .ablocks-stripe-button:hover',
			$this->get_button_hover_css( $attributes ),
			$this->get_button_hover_css( $attributes, 'Tablet' ),
			$this->get_button_hover_css( $attributes, 'Mobile' )
		);

		// Generate button icon hover CSS
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-block-container .ablocks-stripe-button:hover .ablocks-icon-wrap svg.ablocks-svg-icon',
			$this->get_icon_hover_css( $attributes ),
			$this->get_icon_hover_css( $attributes, 'Tablet' ),
			$this->get_icon_hover_css( $attributes, 'Mobile' )
		);

		// Generate button text CSS start
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-block-container .ablocks-stripe-button .ablocks-stripe-button__text',
			$this->get_button_text_css( $attributes )
		);

		// Generate button icon CSS start
		$css_generator->add_class_styles(
			'{{WRAPPER}}  .ablocks-icon-wrap',
			Icon::get_wrapper_css( $attributes ),
			Icon::get_wrapper_css( $attributes, 'Tablet' ),
			Icon::get_wrapper_css( $attributes, 'Mobile' )
		);

		$css_generator->add_class_styles(
			'{{WRAPPER}}  .ablocks-icon-wrap img.ablocks-image-icon',
			Icon::get_element_image_css( $attributes ),
			Icon::get_element_image_css( $attributes, 'Tablet' ),
			Icon::get_element_image_css( $attributes, 'Mobile' ),
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}}  .ablocks-icon-wrap img.ablocks-image-icon:hover',
			Icon::get_element_image_hover_css( $attributes ),
			Icon::get_element_image_hover_css( $attributes, 'Tablet' ),
			Icon::get_element_image_hover_css( $attributes, 'Mobile' ),
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}}  .ablocks-icon-wrap svg.ablocks-svg-icon',
			Icon::get_element_css( $attributes ),
			Icon::get_element_css( $attributes, 'Tablet' ),
			Icon::get_element_css( $attributes, 'Mobile' ),
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}}  .ablocks-icon-wrap svg.ablocks-svg-icon:hover',
			Icon::get_element_image_hover_css( $attributes ),
			Icon::get_element_image_hover_css( $attributes, 'Tablet' ),
			Icon::get_element_image_hover_css( $attributes, 'Mobile' ),
		);

		return $css_generator->generate_css();
	}

	public function get_wrapper_css( $attributes, $device = '' ) {
		$css = [];
		$position = isset( $attributes['position'][ 'value' . $device ] ) && $attributes['position'][ 'value' . $device ] !== 'stretch';

		if ( $position ) {
				$css['text-align'] = $attributes['position'][ 'value' . $device ];
		}

		return $css;
	}

	public function get_button_css( $attributes, $device = '' ) {
		$css = [];

		if ( ! empty( $attributes['background'] ) ) {
			$css['background'] = $attributes['background'];
		} else {
			$css['background'] = $attributes['buttonType'];
		}

		if ( isset( $attributes['alignment'][ 'value' . $device ] ) ) {
			$css['justify-content'] = $attributes['alignment'][ 'value' . $device ];
		}

		if ( isset( $attributes['position'][ 'value' . $device ] ) && $attributes['position'][ 'value' . $device ] === 'stretch' ) {
			$css['width'] = '100%';
		}

		$defaultUnit = 'px';

			$unit = ! empty( $attributes['iconSpace'][ 'valueUnit' . $device ] ) ? $attributes['iconSpace'][ 'valueUnit' . $device ] : $defaultUnit;

		if ( ! empty( $attributes['iconSpace'][ 'value' . $device ] ) ) {
			$css['column-gap'] = $attributes['iconSpace'][ 'value' . $device ] . $unit;
		}

		return array_merge(
			$css,
			[ 'color' => $attributes['textColor'] ?? '#000000' ],
			Border::get_css( $attributes['border'], '', $device ),
			BoxShadow::get_css( $attributes['boxShadow'], '', $device ),
			Typography::get_css( $attributes['typography'], '', $device ),
			Dimensions::get_css( $attributes['padding'], 'padding', $device ),
		);
	}

	public function get_button_hover_css( $attributes, $device = '' ) {
		$css = [];
		if ( ! empty( $attributes['textColorH'] ) ) {
			$css['color'] = $attributes['textColorH'];
		}

		if ( ! empty( $attributes['backgroundH'] ) ) {
			$css['background'] = $attributes['backgroundH'];
		}

		if ( ! empty( $attributes['transition'] ) ) {
			$css['transition-duration'] = $attributes['transition'] . 's';
		}
		return array_merge(
			$css,
			Border::get_hover_css( $attributes['border'], '', $device ),
			BoxShadow::get_hover_css( $attributes['boxShadow'], '', $device )
		);
	}

	public function get_button_text_css( $attributes, $device = '' ) {
		return TextShadow::get_css( $attributes['textShadow'] );
	}

	public function get_icon_hover_css( $attributes, $device = '' ) {
		$css = [];

		if ( ! empty( $attributes['textColorH'] ) ) {
			$css['fill'] = $attributes['textColorH'];
		}

		return $css;
	}


}
