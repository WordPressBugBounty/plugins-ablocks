<?php
namespace ABlocks\Blocks\TableCell;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ABlocks\Classes\BlockBaseAbstract;
use ABlocks\Classes\CssGenerator;
use ABlocks\Controls\Alignment;


class Block extends BlockBaseAbstract {
	protected $parent_block_name = 'table';
	protected $block_name = 'table-cell';

	public function build_css( $attributes ) {

		// Generate CSS start
		$css_generator = new CssGenerator( $attributes, $this->block_name );

		// Generate CSS using the row styles
		$css_generator->add_class_styles(
			'{{WRAPPER}}',
			$this->get_cell_css( $attributes )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}}:hover',
			$this->get_cell_hover_css( $attributes )
		);
		return $css_generator->generate_css();
	}
	public function get_cell_css( $attributes, $device = '' ) {
		$css = [];
		if ( ! empty( $attributes['cellColor'] ) ) {
			$css['background'] = $attributes['cellColor'];
		}
		return array_merge(
			Alignment::get_css( $attributes['textAlignment'], 'justify-content', $device ),
			$css
		);
	}

	public function get_cell_hover_css( $attributes, $device = '' ) {
		$css = [];
		if ( ! empty( $attributes['cellColorH'] ) ) {
			$css['background'] = $attributes['cellColorH'];
		}

		return $css;
	}
}
