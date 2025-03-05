<?php
namespace ABlocks\Blocks\Container;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ABlocks\Classes\BlockBaseAbstract;
use ABlocks\Classes\CssGenerator;
use ABlocks\Controls\Dimensions;
use ABlocks\Controls\BackgroundOverlay;
use ABlocks\Controls\Range;
use ABlocks\Helper;

class Block extends BlockBaseAbstract {
	protected $block_name = 'container';

	public function build_css( $attributes ) {
		$css_generator = new CssGenerator( $attributes, $this->block_name );

		$css_generator->add_class_styles(
			'.ablocks-is-fse-theme {{WRAPPER}}:not(.block-editor-block-list__block).ablocks-block--container--is-root, body:not(.ablocks-is-fse-theme) {{WRAPPER}}:not(.block-editor-block-list__block).ablocks-block--container--is-root',
			$this->reset_static_css( $attributes ),
			$this->reset_static_css( $attributes, 'Tablet' ),
			$this->reset_static_css( $attributes, 'Mobile' ),
		);

		$css_generator->add_class_styles(
			'{{WRAPPER}}.ablocks-block--container',
			$this->get_main_wrapper_css( $attributes ),
			$this->get_main_wrapper_css( $attributes, 'Tablet' ),
			$this->get_main_wrapper_css( $attributes, 'Mobile' )
		);

		$css_generator->add_class_styles(
			'{{WRAPPER}} > .ablocks-block-container',
			$this->get_block_container_css( $attributes ),
			$this->get_block_container_css( $attributes, 'Tablet' ),
			$this->get_block_container_css( $attributes, 'Mobile' )
		);

		$css_generator->add_class_styles(
			'{{WRAPPER}} > .ablocks-block-container',
			$this->get_inner_blocks_closest_parent_css( $attributes ),
			$this->get_inner_blocks_closest_parent_css( $attributes, 'Tablet' ),
			$this->get_inner_blocks_closest_parent_css( $attributes, 'Mobile' )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} > .ablocks-block-container > *:not(style,.ablocks-block--container)',
			$this->get_container_inner_blocks_row_column_display_css( $attributes ),
			$this->get_container_inner_blocks_row_column_display_css( $attributes, 'Tablet' ),
			$this->get_container_inner_blocks_row_column_display_css( $attributes, 'Mobile' )
		);

		$css_generator->add_class_styles(
			'{{WRAPPER}}::before',
			$this->get_container_before_css( $attributes ),
			$this->get_container_before_css( $attributes, 'Tablet' ),
			$this->get_container_before_css( $attributes, 'Mobile' ),
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}}:hover::before',
			$this->get_container_before_hover_css( $attributes ),
			$this->get_container_before_hover_css( $attributes, 'Tablet' ),
			$this->get_container_before_hover_css( $attributes, 'Mobile' ),
		);

		return $css_generator->generate_css();
	}

	public function get_inner_blocks_closest_parent_css( $attributes, $device = '' ) {
		$css = [];

		if ( ! empty( $attributes[ 'direction' . $device ] ) ) {
			$css['flex-direction'] = $attributes[ 'direction' . $device ];
		}
		if ( ! empty( $attributes[ 'justify' . $device ] ) ) {
			$css['justify-content'] = $attributes[ 'justify' . $device ];
		}
		if ( ! empty( $attributes[ 'align' . $device ] ) ) {
			$css['align-items'] = $attributes[ 'align' . $device ];
		}
		if ( ! empty( $attributes[ 'wrap' . $device ] ) ) {
			$css['flex-wrap'] = $attributes[ 'wrap' . $device ];
		}
		return array_merge(
			$css,
			Range::get_css([
				'attributeValue' => $attributes['minimumHeight'],
				'attributeObjectKey' => 'value',
				'isResponsive' => true,
				'hasUnit' => true,
				'defaultValue' => 0,
				'property' => 'min-height',
				'unitDefaultValue' => 'px',
				'device' => $device,
			]),
			Range::get_css([
				'attributeValue' => $attributes['gap'],
				'attributeObjectKey' => 'rowGap',
				'isResponsive' => true,
				'hasUnit' => true,
				'defaultValue' => 0,
				'property' => 'row-gap',
				'unitDefaultValue' => 'px',
				'device' => $device,
			]),
			Range::get_css([
				'attributeValue' => $attributes['gap'],
				'attributeObjectKey' => 'columnGap',
				'isResponsive' => true,
				'hasUnit' => true,
				'defaultValue' => 0,
				'property' => 'column-gap',
				'unitDefaultValue' => 'px',
				'device' => $device,
			]),
		);

	}
	public function get_container_inner_blocks_row_column_display_css( $attributes, $device = '' ) {
		$css = [];
		if ( 'row' === $attributes[ 'direction' . $device ] || 'row-reverse' === $attributes[ 'direction' . $device ] ) {
			$css['display'] = 'inline-block';
			$css['width'] = 'auto';
		}
		return $css;
	}

	public function get_block_container_css( $attributes, $device = '' ) {
		$css = [];
		$preparedContentWidth = Range::get_css([
			'attributeValue' => $attributes['containerContentWidth'],
			'attributeObjectKey' => 'value',
			'isResponsive' => true,
			'hasUnit' => true,
			'defaultValue' => 1140,
			'property' => 'value',
			'unitDefaultValue' => 'px',
			'device' => $device,
		]);
		$content_box_width_value = $preparedContentWidth['value'] ?? '';
		if ( empty( $content_box_width_value ) && $device === '' ) {
			$content_box_width_value = Helper::get_settings( 'default_container_width' ) ?? 1140;
		}
		$content_box_width_unit = $preparedContentWidth['valueUnit'] ?? 'px';
		$is_root_container = isset( $attributes['isRootContainer'] ) ? $attributes['isRootContainer'] : false;

		if ( $is_root_container && $attributes['containerWidthType'] === 'boxed' && ! empty( $content_box_width_value ) ) {
			$css['max-width'] = "min(100%, {$content_box_width_value}{$content_box_width_unit})";
			$css['margin-right'] = 'auto !important';
			$css['margin-left'] = 'auto !important';
		}

		return $css;
	}

	public function reset_static_css( $attributes, $device = '' ) {
		$preparedContainer = Range::get_css([
			'attributeValue' => $attributes['containerWidth'],
			'attributeObjectKey' => 'value',
			'isResponsive' => true,
			'hasUnit' => true,
			'defaultValue' => 100,
			'property' => 'value',
			'unitDefaultValue' => '%',
			'device' => '',
		]);
		$css = [];
		if ( 'custom' === $attributes['containerWidthType'] && ! empty( $preparedContainer['value'] ) ) {
			$css['margin-left'] = 'auto !important';
			$css['margin-right'] = 'auto !important';
		}
		return $css;
	}
	public function get_main_wrapper_css( $attributes, $device = '' ) {
		$css = [];
		$preparedContainer = Range::get_css([
			'attributeValue' => $attributes['containerWidth'],
			'attributeObjectKey' => 'value',
			'isResponsive' => true,
			'hasUnit' => true,
			'defaultValue' => 100,
			'property' => 'value',
			'unitDefaultValue' => '%',
			'device' => $device,
		]);

		$is_root_container = isset( $attributes['isRootContainer'] ) ? $attributes['isRootContainer'] : false;
		if ( ( ! $is_root_container || 'custom' === $attributes['containerWidthType'] ) && ! empty( $preparedContainer['value'] ) ) {
			$css['max-width'] = "min(100%, {$preparedContainer['value']}{$preparedContainer['valueUnit']}) !important";
		}

		if ( 'custom' === $attributes['containerWidthType'] && ! empty( $preparedContainer['value'] ) && $is_root_container ) {
			$css['margin-left'] = 'auto !important';
			$css['margin-right'] = 'auto !important';
		}

		if ( $is_root_container && 'custom' !== $attributes['containerWidthType'] ) {
			unset( $css['max-width'] );
			unset( $css['margin-left'] );
			unset( $css['margin-right'] );
		}

		$css['overflow'] = ! empty( $attributes['overflow'] ) ? $attributes['overflow'] : 'visible';

		return array_merge(
			Dimensions::get_css( $attributes['_padding'], 'padding', $device ),
			$css
		);
	}
	public static function get_container_before_css( $attributes, $device = '' ) {
		return array_merge(
			BackgroundOverlay::get_before_css( $attributes['_backgroundOverlay'], $attributes['_border'], 'background', $device ),
		);
	}
	public static function get_container_before_hover_css( $attributes, $device = '' ) {
		return array_merge(
			BackgroundOverlay::get_before_hover_css( $attributes['_backgroundOverlay'], $attributes['_border'], 'background', $device ),
		);
	}
}
