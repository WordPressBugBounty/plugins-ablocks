<?php
namespace ABlocks\Blocks\LoopLoadMore;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ABlocks\Classes\BlockBaseAbstract;
use ABlocks\Classes\CssGeneratorV2;
use ABlocks\Controls\Typography;
use ABlocks\Controls\Border;
use ABlocks\Controls\Dimensions;
use ABlocks\Controls\Range;
use ABlocks\Controls\Alignment;
use ABlocks\Controls\BoxShadow;

class Block extends BlockBaseAbstract {
	protected $parent_block_name = 'loop-builder';
	protected $block_name = 'loop-load-more';

	public function build_css( $attributes ) {

		// Generate CSS start
		$css_generator = new CssGeneratorV2( $attributes, $this->block_name );
			// button style
		$css_generator->add_class_styles(
			'{{WRAPPER}}',
			$this->loop_load_More_Wrapper( $attributes ),
			$this->loop_load_More_Wrapper( $attributes, 'Tablet' ),
			$this->loop_load_More_Wrapper( $attributes, 'Mobile' ),
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-loop-load-more__text',
			$this->loop_load_More_button( $attributes ),
			$this->loop_load_More_button( $attributes, 'Tablet' ),
			$this->loop_load_More_button( $attributes, 'Mobile' )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-loop-load-more__text:hover',
			$this->loop_load_More_button_hover( $attributes ),
			$this->loop_load_More_button_hover( $attributes, 'Tablet' ),
			$this->loop_load_More_button_hover( $attributes, 'Mobile' )
		);
		return $css_generator->generate_css();
	}
	public function render_block_content( $attributes, $content, $block_instance ) {
		global $post;
		$current_post_id    = isset( $post->ID ) ? intval( $post->ID ) : 0;
		$load_more_text     = isset( $attributes['loadMoreButtonText'] ) ? esc_html( $attributes['loadMoreButtonText'] ) : '';
		$no_more_items_text = isset( $attributes['noMoreItemsText'] ) ? esc_attr( $attributes['noMoreItemsText'] ) : '';
		$data_per_page      = isset( $attributes['dataPerPageShow'] ) ? esc_attr( $attributes['dataPerPageShow'] ) : '';
		$card_numbers       = isset( $attributes['cardNumbers'] ) ? esc_attr( $attributes['cardNumbers'] ) : '';

		echo '<span 
            class="ablocks-loop-load-more__text" 
            data-post-id="' . $current_post_id . '" 
            data-no-item-text="' . $no_more_items_text . '" 
            data-per-page="' . $data_per_page . '" 
            data-cards-numbers="' . $card_numbers . '" 
            data-more-button-text="' . $load_more_text . '"
          >' . $load_more_text . '</span>';
	}

	private function loop_load_More_Wrapper( $attributes, $device = '' ) {
		$css = [];
		if ( isset( $attributes['moreButtonAlignment'] ) ) {
			$css['justify-content'] = $attributes['moreButtonAlignment'];
		}
		$css = array_merge($css, Range::get_css([
			'attributeValue' => $attributes['loadMoreButtonGap'] ?? null,
			'attribute_object_key' => 'value',
			'isResponsive' => true,
			'hasUnit' => false,
			'unitDefaultValue' => 'px',
			'defaultValue' => '',
			'property' => 'margin-top',
			'device' => $device,
		]));
		return $css;
	}
	private function loop_load_More_button( $attributes, $device = '' ) {
		$css = [];

		if ( ! empty( $attributes['loadMoreButtonBackground'] ) ) {
			$css['background'] = $attributes['loadMoreButtonBackground'];
		}
		if ( ! empty( $attributes['loadMoreButtonTextColor'] ) ) {
			$css['color'] = $attributes['loadMoreButtonTextColor'];
		}

		return array_merge(
			$css,
			Border::get_css( $attributes['moreButtonBorder'], '', $device ),
			BoxShadow::get_css( $attributes['moreButtonboxShadow'], '', $device ),
			Typography::get_css( $attributes['moreButtonTypography'], $device ),
			Dimensions::get_css( $attributes['moreButtonPadding'], 'padding', $device ),
		);
	}
	private function loop_load_More_button_hover( $attributes, $device = '' ) {
		$css = [];
		if ( ! empty( $attributes['loadMoreButtonTextColorH'] ) ) {
			$css['color'] = $attributes['loadMoreButtonTextColorH'];
		}

		if ( ! empty( $attributes['loadMoreButtonBackgroundH'] ) ) {
			$css['background'] = $attributes['loadMoreButtonBackgroundH'];
		}

		if ( ! empty( $attributes['loadMoreButtonTransition'] ) ) {
			$css['transition-duration'] = $attributes['loadMoreButtonTransition'] . 's';
		}
		return array_merge(
			$css,
			Border::get_hover_css( $attributes['moreButtonBorder'], '', $device ),
			BoxShadow::get_hover_css( $attributes['moreButtonboxShadow'], '', $device )
		);
	}
}
