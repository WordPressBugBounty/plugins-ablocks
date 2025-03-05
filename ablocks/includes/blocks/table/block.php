<?php
namespace ABlocks\Blocks\Table;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ABlocks\Classes\BlockBaseAbstract;
use ABlocks\Classes\CssGenerator;
use ABlocks\Controls\Border;

class Block extends BlockBaseAbstract {
	protected $block_name = 'table';

	public function build_css( $attributes ) {

		// Generate CSS start
		$css_generator = new CssGenerator( $attributes, $this->block_name );
		$css_generator->add_class_styles(
			'{{WRAPPER}}.ablocks-block--table table',
			$this->get_table_css( $attributes )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}}.ablocks-block--table table, {{WRAPPER}}.ablocks-block--table-header .ablocks-block--table-cell , {{WRAPPER}}.ablocks-block--table .ablocks-block--table-cell',
			$this->get_table_border_css( $attributes ),
			$this->get_table_border_css( $attributes, 'Tablet' ),
			$this->get_table_border_css( $attributes, 'Mobile' )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}}.ablocks-block--table table:hover, {{WRAPPER}}.ablocks-block--table-header .ablocks-block--table-cell:hover, {{WRAPPER}}.ablocks-block--table .ablocks-block--table-cell:hover',
			$this->get_table_border_hover_css( $attributes ),
			$this->get_table_border_hover_css( $attributes, 'Tablet' ),
			$this->get_table_border_hover_css( $attributes, 'Mobile' )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-block--table-body .ablocks-table-row--odd',
			$this->get_row_odd_css( $attributes )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-block--table-body .ablocks-table-row--odd:hover',
			$this->get_row_odd_hover_css( $attributes )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-block--table-body .ablocks-table-row--even',
			$this->get_row_even_css( $attributes )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-block--table-body .ablocks-table-row--even:hover',
			$this->get_row_even_hover_css( $attributes )
		);
		// ---header----
		$css_generator->add_class_styles(
			'{{WRAPPER}}.ablocks-block--table .ablocks-block--table-header',
			$this->get_header_css( $attributes )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}}.ablocks-block--table .ablocks-block--table-header:hover',
			$this->get_header_hover_css( $attributes )
		);
		// --table body----
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-block--table-body',
			$this->get_body_css( $attributes )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-block--table-body:hover',
			$this->get_body_hover_css( $attributes )
		);

		// --table footer--
		$css_generator->add_class_styles(
			'{{WRAPPER}}.ablocks-block--table .ablocks-block--table-footer',
			$this->get_footer_css( $attributes )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}}.ablocks-block--table .ablocks-block--table-footer:hover',
			$this->get_footer_hover_css( $attributes )
		);
		return $css_generator->generate_css();
	}
	public function get_table_css( $attributes, $device = '' ) {
		$css = [];

		if ( ! empty( $attributes['borderCollapse'] ) && $attributes['borderCollapse'] === 'collapse' ) {
			$css['border-collapse'] = 'collapse';
		} elseif ( ! empty( $attributes['borderCollapse'] ) && $attributes['borderCollapse'] === 'separate' ) {
			$css['border-collapse'] = 'separate';
		}

		return $css;
	}

	public function get_table_border_css( $attributes, $device = '' ) {

		return Border::get_css( $attributes['border'], '', $device );

	}
	public function get_table_border_hover_css( $attributes, $device = '' ) {

		return Border::get_hover_css( $attributes['border'], '', $device );
	}

	public function get_row_odd_css( $attributes, $device = '' ) {
		$css = [];
		if ( ! empty( $attributes['rowOddColor'] ) ) {
			$css['background'] = $attributes['rowOddColor'];
		}
		return $css;
	}

	public function get_row_odd_hover_css( $attributes, $device = '' ) {
		$css = [];
		if ( ! empty( $attributes['rowOddColorH'] ) ) {
			$css['background'] = $attributes['rowOddColorH'];
		}
		return $css;
	}

	public function get_row_even_css( $attributes, $device = '' ) {
		$css = [];
		if ( ! empty( $attributes['rowEvenColor'] ) ) {
			$css['background'] = $attributes['rowEvenColor'];
		}
		return $css;
	}

	public function get_row_even_hover_css( $attributes, $device = '' ) {
		$css = [];
		if ( ! empty( $attributes['rowEvenColorH'] ) ) {
			$css['background'] = $attributes['rowEvenColorH'];
		}
		return $css;
	}

	// --header ----
	public function get_header_css( $attributes, $device = '' ) {
		$css = [];
		if ( ! empty( $attributes['headerColor'] ) ) {
			$css['background'] = $attributes['headerColor'] . ' !important';
		}

		return $css;
	}

	public function get_header_hover_css( $attributes, $device = '' ) {
		$css = [];
		if ( ! empty( $attributes['headerColorH'] ) ) {
			$css['background'] = $attributes['headerColorH'] . ' !important';
		}

		return $css;
	}
	// ---table body----
	public function get_body_css( $attributes, $device = '' ) {
		$css = [];
		if ( ! empty( $attributes['bodyBg'] ) ) {
			$css['background'] = $attributes['bodyBg'] . ' !important';
		}

		return $css;
	}

	public function get_body_hover_css( $attributes, $device = '' ) {
		$css = [];
		if ( ! empty( $attributes['bodyBgH'] ) ) {
			$css['background'] = $attributes['bodyBgH'] . ' !important';
		}

		return $css;
	}
	// -table footer
	public function get_footer_css( $attributes, $device = '' ) {
		$css = [];
		if ( ! empty( $attributes['footerColor'] ) ) {
			$css['background'] = $attributes['footerColor'] . ' !important';
		}

		return $css;
	}

	public function get_footer_hover_css( $attributes, $device = '' ) {
		$css = [];
		if ( ! empty( $attributes['footerColorH'] ) ) {
			$css['background'] = $attributes['footerColorH'] . ' !important';
		}

		return $css;
	}
}
