<?php
namespace ABlocks\Blocks\lists;

use ABlocks\Classes\BlockBaseAbstract;
use ABlocks\Classes\CssGenerator;
use ABlocks\Controls\Alignment;
use ABlocks\Controls\Dimensions;
use ABlocks\Controls\Typography;
use ABlocks\Controls\Width;
use ABlocks\Controls\Border;

class Block extends BlockBaseAbstract {

	protected $block_name = 'lists';

	public function build_css( $attributes ) {
		// Generate CSS
		$css_generator = new CssGenerator( $attributes );
		// Wrapper CSS
		$css_generator->add_class_styles(
			'{{WRAPPER}}',
			$this->get_wrapper_css( $attributes, '' ),
			$this->get_wrapper_css( $attributes, 'Tablet' ),
			$this->get_wrapper_css( $attributes, 'Mobile' ),
		);
		// List CSS
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-list',
			$this->get_list_css( $attributes, '' ),
			$this->get_list_css( $attributes, 'Tablet' ),
			$this->get_list_css( $attributes, 'Mobile' ),
		);
		// List Wrapper CSS
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-list__item-content',
			$this->get_list_wrapper_css( $attributes, '' ),
			$this->get_list_wrapper_css( $attributes, 'Tablet' ),
			$this->get_list_wrapper_css( $attributes, 'Mobile' ),
		);

		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-list_item-content-divider',
			$this->get_Divider_Wrapper_css( $attributes, '' ),
			$this->get_Divider_Wrapper_css( $attributes, 'Tablet' ),
			$this->get_Divider_Wrapper_css( $attributes, 'Mobile' ),
		);
		// Marker CSS
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-list__item-content .ablocks-list__item-marker',
			$this->get_marker_css( $attributes, '' ),
			$this->get_marker_css( $attributes, 'Tablet' ),
			$this->get_marker_css( $attributes, 'Mobile' ),
		);
		// Icon CSS
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-list__item-content .ablocks-svg-icon',
			$this->get_icon_css( $attributes, '' ),
			$this->get_icon_css( $attributes, 'Tablet' ),
			$this->get_icon_css( $attributes, 'Mobile' ),
		);
		// Icon hover css
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-list__item-content .ablocks-svg-icon:hover',
			$this->get_icon_hover_css( $attributes, '' ),
			$this->get_icon_hover_css( $attributes, 'Tablet' ),
			$this->get_icon_hover_css( $attributes, 'Mobile' ),
		);

		// List text CSS
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-list__item-content .ablocks-list__item-text',
			$this->get_list_text_css( $attributes ),
			$this->get_list_text_css( $attributes, 'Tablet' ),
			$this->get_list_text_css( $attributes, 'Mobile' ),
		);

		return $css_generator->generate_css();
	}

	public function get_wrapper_css( $attributes, $device = '' ) {
		$typography = isset( $attributes['typography'] ) ? $attributes['typography'] : '';
		$alignment = isset( $attributes['alignment'] ) ? $attributes['alignment'] : '';
		return array_merge(
			Alignment::get_css( $alignment, 'text-align', $device ),
			Typography::get_css( $typography, '', $device ),
		);
	}

	public function get_list_css( $attributes, $device = '' ) {
		$css = [];

		if ( isset( $attributes['spaceBetween'][ 'value' . $device ] ) && ! empty( $attributes['spaceBetween'][ 'value' . $device ] ) ) {
			$css['gap'] = $attributes['spaceBetween'][ 'value' . $device ] . 'px';
		}

		$stack = $attributes[ 'stack' . $device ] ?? $attributes['stack'] ?? '';
		if ( $stack === 'vertical' ) {
			$css['flex-direction'] = 'column';
			$vertical_alignment = $attributes[ 'verticalAlignment' . $device ] ?? $attributes['verticalAlignment'] ?? '';
			if ( ! empty( $vertical_alignment ) ) {
				$css['align-items'] = $vertical_alignment;
			}
		} elseif ( $stack === 'horizontal' ) {
			$css['flex-direction'] = 'row';
			$horizontal_alignment = $attributes[ 'horizontalAlignment' . $device ] ?? $attributes['horizontalAlignment'] ?? '';
			if ( ! empty( $horizontal_alignment ) ) {
				$css['justify-content'] = $horizontal_alignment;
			}
		}

		$width_css = isset( $attributes[ 'width' . $device ] )
			? Width::get_css( $attributes[ 'width' . $device ], 'width', $device )
			: [];

		$css = array_merge(
			$css,
			$width_css,
			isset( $attributes['horizontalAlignment'] )
				? Alignment::get_css( $attributes['horizontalAlignment'], 'justify-content', $device )
				: []
		);
		foreach ( $css as $property => $value ) {
			if ( is_array( $value ) ) {
				unset( $css[ $property ] );
			}
		}
		return $css;
	}

	public function get_list_wrapper_css( $attributes, $device = '' ) {
		$css = [];
		$marker_type = $attributes['markerType'] ?? '';
		$text_indent = $attributes['textIndent'][ 'value' . $device ] ?? '';
		if ( $marker_type && 'icon' === $marker_type ) {
			if ( ! empty( $attributes['position'][ 'value' . $device ] ) ) {
				$css['align-items'] = $attributes['position'][ 'value' . $device ];
			}
		}
		if ( $text_indent && ! empty( $text_indent ) ) {
			$css['gap'] = $text_indent . 'px';
		}

		return $css;
	}

	public function get_divider_wrapper_css( $attributes, $device = '' ) {
		$css = [];

		if ( isset( $attributes['divider'] ) && ! empty( $attributes['divider'] ) ) {
			$weight_value = isset( $attributes['weight'][ 'value' . $device ] ) ? $attributes['weight'][ 'value' . $device ] : 1;
			$weight_unit = isset( $attributes['weight'][ 'valueUnit' . $device ] ) ? $attributes['weight'][ 'valueUnit' . $device ] : 'px';

			$border_color = isset( $attributes['borderColor'] ) ? $attributes['borderColor'] : 'black';
			$divider_pattern_url = isset( $attributes['dividerPatternUrl'] ) ? $attributes['dividerPatternUrl'] : '';
			$stack = isset( $attributes['stack'] ) ? $attributes['stack'] : '';

			if ( ! empty( $divider_pattern_url ) ) {
				if ( $stack === 'vertical' ) {
					$css['border-bottom'] = "{$weight_value}{$weight_unit} {$divider_pattern_url} {$border_color}";
				} elseif ( $stack === 'horizontal' ) {
					$css['border-right'] = "{$weight_value}{$weight_unit} {$divider_pattern_url} {$border_color}";
				}
			}

			if ( $stack === 'horizontal' ) {
				$width_value = isset( $attributes['width'][ 'value' . $device ] ) ? $attributes['width'][ 'value' . $device ] : '0';
			} else {
				$width_value = isset( $attributes['width'][ 'value' . $device ] ) ? $attributes['width'][ 'value' . $device ] : '%';
				$width_unit = isset( $attributes['width'][ 'valueUnit' . $device ] ) ? $attributes['width'][ 'valueUnit' . $device ] : '100%';
				$css['width'] = "{$width_value}{$width_unit}";
			}
		}//end if

		return $css;
	}



	public function get_marker_css( $attributes, $device = '' ) {
		$css = [];
		$marker_size = isset( $attributes['markerSize'][ 'value' . $device ] ) ? $attributes['markerSize'][ 'value' . $device ] : '';
		if ( isset( $attributes['markerColor'] ) && ! empty( $attributes['markerColor'] ) ) {
			$css['background'] = $attributes['markerColor'];
		}

		if ( $marker_size && ! empty( $marker_size ) ) {
			$css['max-width'] = $marker_size . 'px';
			$css['max-height'] = $marker_size . 'px';
			$css['min-width'] = $marker_size . 'px';
			$css['min-height'] = $marker_size . 'px';
		}

		return $css;
	}

	public function get_icon_css( $attributes, $device = '' ) {
		$css = [];
		$border = isset( $attributes['border'] ) ? $attributes['border'] : '';
		$padding = isset( $attributes['padding'] ) ? $attributes['padding'] : '';
		$css['box-sizing'] = 'content-box';

		if ( isset( $attributes['markerType'] ) && $attributes['markerType'] === 'icon' ) {
			if ( $attributes['iconType'] === 'stacked' ) {
				$css['background'] = ! empty( $attributes['iconBackgroundColor'] ) ? $attributes['iconBackgroundColor'] : '#ddd';
				$css['padding'] = '.2em';
				$css['color'] = ! empty( $attributes['iconColor'] ) ? $attributes['iconColor'] : '#000000';

				if ( $attributes['iconShape'] === 'circle' ) {
					$css['border-radius'] = '50px';
				}
			} elseif ( $attributes['iconType'] === 'framed' ) {
				$css['background'] = ! empty( $attributes['iconBackgroundColor'] ) ? $attributes['iconBackgroundColor'] : 'transparent';
				$css['padding'] = '.2em';
				$css['color'] = ! empty( $attributes['iconColor'] ) ? $attributes['iconColor'] : '#69727d';
				$css['border'] = '2px solid ' . ( ! empty( $attributes['iconColor'] ) ? $attributes['iconColor'] : '#69727d' );

				if ( $attributes['iconShape'] === 'circle' ) {
					$css['border-radius'] = '50px';
				}
			}

			$defaultUnit = 'px';

			$unit = ! empty( $attributes['iconSize'][ 'valueUnit' . $device ] ) ? $attributes['iconSize'][ 'valueUnit' . $device ] : $defaultUnit;

			if ( ! empty( $attributes['iconSize'][ 'value' . $device ] ) ) {
				$css['font-size'] = $attributes['iconSize'][ 'value' . $device ] . $unit;
			}

			if ( ! empty( $attributes[ 'iconColor' . $device ] ) && isset( $attributes[ 'iconColor' . $device ] ) ) {
				$css['color'] = $attributes['iconColor'];
				$css['fill'] = $attributes['iconColor'];
			}

			if ( isset( $attributes['iconBackground'] ) && $attributes['iconBackground'] ) {
				if ( ! empty( $attributes['iconBackgroundColor'] ) ) {
					$css['background'] = $attributes['iconBackgroundColor'];
				}
			}
		}//end if

		return array_merge(
			$css,
			isset( $attributes['border'] ) ? Border::get_css( $attributes['border'], '', $device ) : [],
			Dimensions::get_css( $padding, 'padding', $device )
		);
	}


	public function get_icon_hover_css( $attributes, $device = '' ) {
		$border = isset( $attributes['border'] ) ? $attributes['border'] : '';
		return array_merge(
			isset( $attributes['border'] ) ? Border::get_hover_css( $attributes['border'], '', $device ) : []
		);
	}

	public function get_list_text_css( $attributes, $device = '' ) {
		$css = [];
		if ( isset( $attributes['textColor'] ) ) {
			$css['color'] = $attributes['textColor'];
		}

		return $css;
	}
}
