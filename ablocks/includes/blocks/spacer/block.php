<?php

namespace ABlocks\Blocks\Spacer;

use ABlocks\Classes\BlockBaseAbstract;
use ABlocks\Classes\CssGenerator;

class Block extends BlockBaseAbstract {
	protected $block_name = 'spacer';

	public function build_css( $attributes ) {
		$css_generator = new CssGenerator( $attributes, $this->block_name );

		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-spacer__box',
			$this->get_spacer_css( $attributes ),
			$this->get_spacer_css( $attributes, 'Tablet' ),
			$this->get_spacer_css( $attributes, 'Mobile' ),
		);

		return $css_generator->generate_css();
	}

	public function get_spacer_css( $attributes, $device = '' ) {
		$spacer_css = [];
		$height = isset( $attributes['spacerHeight'] ) ? $attributes['spacerHeight'] : '';
		$height_key = 'value' . $device;

		if ( ! empty( $height[ $height_key ] ) ) {
			$value_unit = isset( $height['valueUnit'] ) ? $height['valueUnit'] : 'px';
			$spacer_css['height'] = $height[ $height_key ] . $value_unit . '!important';
		}

		return $spacer_css;
	}
}
