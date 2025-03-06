<?php
namespace ABlocks\Blocks\AdvanceListItem;

use ABlocks\Classes\BlockBaseAbstract;
use ABlocks\Classes\CssGenerator;
use ABlocks\Controls\Alignment;
use ABlocks\Controls\Dimensions;
use ABlocks\Controls\Typography;
use ABlocks\Controls\TextShadow;
use ABlocks\Controls\TextStroke;
use ABlocks\Controls\Width;
use ABlocks\Controls\Border;
use ABlocks\Controls\Icon;
use ABlocks\Controls\Range;

class Block extends BlockBaseAbstract {

	protected $parent_block_name = 'advance-lists';
	protected $block_name = 'advance-list-item';

	public function build_css( $attributes ) {
		// Generate CSS
		$css_generator = new CssGenerator( $attributes );

		$css_generator->add_class_styles(
			'{{WRAPPER}}.ablocks-block--advance-list-item .ablocks-block-container',
			$this->get_container_css( $attributes, '' ),
			$this->get_container_css( $attributes, 'Tablet' ),
			$this->get_container_css( $attributes, 'Mobile' ),
		);
		// Marker
		$css_generator->add_class_styles(
			'{{WRAPPER}}.ablocks-block--advance-list-item .advance-list-item-marker',
			$this->get_marker_css( $attributes, '' ),
			$this->get_marker_css( $attributes, 'Tablet' ),
			$this->get_marker_css( $attributes, 'Mobile' ),
		);
		// Icon Style
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-icon-wrap',
			Icon::get_wrapper_css( $attributes ),
			Icon::get_wrapper_css( $attributes, 'Tablet' ),
			Icon::get_wrapper_css( $attributes, 'Mobile' )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-icon-wrap img.ablocks-image-icon',
			Icon::get_element_image_css( $attributes ),
			Icon::get_element_image_css( $attributes, 'Tablet' ),
			Icon::get_element_image_css( $attributes, 'Mobile' ),
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-icon-wrap svg.ablocks-svg-icon',
			Icon::get_element_css( $attributes ),
			Icon::get_element_css( $attributes, 'Tablet' ),
			Icon::get_element_css( $attributes, 'Mobile' ),
		);

		// text
		$desktop_paragraph_text_style = $this->get_paragraph_text_css( $attributes );
		if ( ! empty( $attributes['textColor'] ) ) {
			$desktop_paragraph_text_style['color'] = $attributes['textColor'];
		}
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-block-container .ablocks-advance-list-item-text',
			$desktop_paragraph_text_style,
			$this->get_paragraph_text_css( $attributes, 'Tablet' ),
			$this->get_paragraph_text_css( $attributes, 'Mobile' ),
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-block-container .ablocks-advance-list-item-text-drop-caps::first-letter',
			$this->get_paragraph_drop_text_css( $attributes ),
			$this->get_paragraph_drop_text_css( $attributes, 'Tablet' ),
			$this->get_paragraph_drop_text_css( $attributes, 'Mobile' ),
		);

		// Divider CSS
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-advance-list-item-divider__pattern-' . ( $attributes['dividerType'] === 'mask-style' ? 'mask' : 'css' ),
			$this->get_divider_css( $attributes, '' ),
			$this->get_divider_css( $attributes, 'Tablet' ),
			$this->get_divider_css( $attributes, 'Mobile' ),
		);

		return $css_generator->generate_css();
	}


	public function get_marker_css( $attributes, $device = '' ) {
		$css = [];

		if ( ! empty( $attributes['shapeColor'] ) && $attributes['markerType'] === 'Shapes' ) {
			$css['border-color'] = $attributes['shapeColor'] . ' !important';
		}
		if ( ! empty( $attributes['shapeSize'][ 'value' . $device ] ) ) {
			$shapeSize = $attributes['shapeSize'][ 'value' . $device ] . 'px';

			if ( $attributes['markerType'] === 'Emoji' ) {
				$css['font-size'] = $shapeSize;
			} else {
				if ( in_array( $attributes['shapeType'], [ 'inset', 'outset', 'ridge' ], true ) ) {
					$css['border'] = $shapeSize . ' ' . $attributes['shapeType'];
				} else {
					$css['border-bottom'] = $shapeSize . ' ' . $attributes['shapeType'];
					$css['width'] = $shapeSize;
				}
			}
		}
		return $css;
	}

	public function get_container_css( $attributes, $device = '' ) {
		$css = [];
		if ( ! empty( $attributes['alignment'][ 'value' . $device ] ) && $attributes['alignment'][ 'value' . $device ] === 'flex-start' ) {
			if ( ! empty( $attributes['iconAlignment'][ 'value' . $device ] ) && $attributes['iconAlignment'][ 'value' . $device ] ) {
				$css['justify-content'] = 'flex-start';
			} else {
				$css['justify-content'] = 'flex-end';
			};
		} elseif ( ! empty( $attributes['alignment'][ 'value' . $device ] ) && $attributes['alignment'][ 'value' . $device ] === 'flex-end' ) {
			if ( ! empty( $attributes['iconAlignment'][ 'value' . $device ] ) && $attributes['iconAlignment'][ 'value' . $device ] === 'row' ) {
				$css['justify-content'] = 'flex-end';
			} else {
				$css['justify-content'] = 'flex-start';
			};
		} else {
			$css['justify-content'] = 'center';
		}
		return array_merge(
			$css,
			isset( $attributes['iconAlignment'] ) ? Alignment::get_css( $attributes['iconAlignment'], 'flex-direction', $device ) : [],
			Range::get_css([
				'attributeValue' => $attributes['innerGap'],
				'attribute_object_key' => 'value',
				'isResponsive' => true,
				'defaultValue' => null,
				'hasUnit' => true,
				'unitDefaultValue' => 'px',
				'property' => 'gap',
				'device' => $device,
			]),
		);
	}

	public function get_paragraph_text_css( $attributes, $device = '' ) {
		return array_merge(
			isset( $attributes['listTypography'] ) ? Typography::get_css( $attributes['listTypography'], '', $device ) : [],
			isset( $attributes['listTextStroke'] ) ? TextStroke::get_css( $attributes['listTextStroke'], '', $device ) : [],
			isset( $attributes['listTextShadow'] ) ? TextShadow::get_css( $attributes['listTextShadow'] ) : [],
		);
	}
	public function get_paragraph_drop_text_css( $attributes, $device = '' ) {
		$css = [];
		if ( ! empty( $attributes['dropCapsTextColor'] ) ) {
			$css['color'] = $attributes['dropCapsTextColor'];
		}
		return $css;
	}

	public function get_divider_css( $attributes, $device = '' ) {
		$css = [];
		$divider_width = isset( $attributes['width'][ 'value' . $device ] ) ? $attributes['width'][ 'value' . $device ] : 60;
		$divider_color = isset( $attributes['color'] ) ? $attributes['color'] : '#000000';
		$default_Unit = '%';

		if ( ! empty( $attributes['listsDirection'][ 'value' . $device ] ) && $attributes['listsDirection'][ 'value' . $device ] === 'row' ) {
			$css['display'] = 'none';
		} else {
			$css['display'] = 'block';
		}
		if ( ! empty( $attributes['color'] ) ) {
			$css['--ablocks-divider-pattern-color'] = $divider_color;
		}

		$moreRangeCSS = [];
		if ( isset( $attributes['dividerType'] ) && $attributes['dividerType'] === 'mask-style' && isset( $attributes['size'] ) && ! empty( $attributes['size'] ) ) {
			$moreRangeCSS = Range::get_css([
				'attributeValue' => $attributes['size'],
				'attribute_object_key' => 'value',
				'isResponsive' => false,
				'defaultValue' => null,
				'hasUnit' => false,
				'unitDefaultValue' => 'px',
				'property' => '--ablocks-divider-pattern-height',
			]);
		} elseif ( isset( $attributes['weight'] ) && ! empty( $attributes['weight'] ) ) {
			$moreRangeCSS = Range::get_css([
				'attributeValue' => $attributes['weight'],
				'attribute_object_key' => 'value',
				'isResponsive' => false,
				'defaultValue' => null,
				'hasUnit' => false,
				'unitDefaultValue' => 'px',
				'property' => '--ablocks-divider-pattern-weight',
			]);
		}//end if

		if ( ! empty( $attributes['dividerPatternUrl'] ) ) {
			if ( $attributes['dividerType'] === 'mask-style' ) {
				$css['--ablocks-divider-pattern-url'] = 'url(' . $attributes['dividerPatternUrl'] . ')';
			} else {
				$css['--ablocks-divider-pattern-style'] = $attributes['dividerPatternUrl'];
			}
		}

		return array_merge(
			$css,
			Range::get_css([
				'attributeValue' => $attributes['width'],
				'attribute_object_key' => 'value',
				'isResponsive' => true,
				'defaultValue' => null,
				'hasUnit' => false,
				'unitDefaultValue' => '%',
				'property' => 'width',
				'device' => $device,
			]),
			$moreRangeCSS,
		);
	}

}
