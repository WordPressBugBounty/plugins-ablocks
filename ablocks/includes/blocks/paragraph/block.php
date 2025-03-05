<?php
namespace ABlocks\Blocks\Paragraph;

use ABlocks\Classes\BlockBaseAbstract;
use ABlocks\Classes\CssGenerator;
use ABlocks\Controls\Alignment;
use ABlocks\Controls\TextShadow;
use ABlocks\Controls\TextStroke;
use ABlocks\Controls\Typography;

class Block extends BlockBaseAbstract {
	protected $block_name = 'paragraph';

	public function build_css( $attributes ) {

		$css_generator = new CssGenerator( $attributes, $this->block_name );

		$css_generator->add_class_styles(
			'{{WRAPPER}}',
			$this->get_wrapper_css( $attributes ),
			$this->get_wrapper_css( $attributes, 'Tablet' ),
			$this->get_wrapper_css( $attributes, 'Mobile' ),
		);

		$desktop_paragraph_text_style = $this->get_paragraph_text_css( $attributes );

		if ( ! empty( $attributes['textColor'] ) ) {
			$desktop_paragraph_text_style['color'] = $attributes['textColor'];
		}
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-paragraph-text',
			$desktop_paragraph_text_style,
			$this->get_paragraph_text_css( $attributes, 'Tablet' ),
			$this->get_paragraph_text_css( $attributes, 'Mobile' ),
		);

		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-paragraph-text-drop-caps::first-letter',
			$this->get_paragraph_drop_text_css( $attributes ),
			$this->get_paragraph_drop_text_css( $attributes, 'Tablet' ),
			$this->get_paragraph_drop_text_css( $attributes, 'Mobile' ),
		);
		return $css_generator->generate_css();
	}

	public function get_wrapper_css( $attributes, $device = '' ) {
		return isset( $attributes['alignment'] ) ? Alignment::get_css( $attributes['alignment'], 'text-align', $device ) : [];
	}

	public function get_paragraph_text_css( $attributes, $device = '' ) {
		return array_merge(
			isset( $attributes['typography'] ) ? Typography::get_css( $attributes['typography'], '', $device ) : [],
			isset( $attributes['textStroke'] ) ? TextStroke::get_css( $attributes['textStroke'], '', $device ) : [],
			isset( $attributes['textShadow'] ) ? TextShadow::get_css( $attributes['textShadow'], '', $device ) : [],
		);
	}

	public function get_paragraph_drop_text_css( $attributes, $device = '' ) {
		$css = [];
		if ( ! empty( $attributes['dropCapsTextColor'] ) ) {
			$css['color'] = $attributes['dropCapsTextColor'];
		}
		return $css;
	}
}
