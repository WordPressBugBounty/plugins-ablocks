<?php
namespace ABlocks\Blocks\PriceMenu;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ABlocks\Classes\BlockBaseAbstract;
use ABlocks\Classes\CssGenerator;
use ABlocks\Classes\CssGeneratorV2;
use ABlocks\Controls\Alignment;
use ABlocks\Controls\Border;
use ABlocks\Controls\Dimensions;
use ABlocks\Controls\Typography;
use ABlocks\Controls\TextShadow;
use ABlocks\Controls\TextStroke;
use ABlocks\Controls\Range;
use ABlocks\Controls\Color;
use ABlocks\Blocks\PriceMenuItem\Block as PriceMenuItemBlock;
class Block extends BlockBaseAbstract {
	protected $block_name = 'price-menu';

	public function build_css_v1( $attributes ) {

		$css_generator = new CssGenerator( $attributes, $this->block_name );

		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-block-container',
			$this->get_all_menu_css( $attributes, '' ),
			$this->get_all_menu_css( $attributes, 'Tablet' ),
			$this->get_all_menu_css( $attributes, 'Mobile' ),
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-price-menu-item, {{WRAPPER}} .ablocks-price-menu-item-details',
			$this->get_gap_around_css( $attributes, '' ),
			$this->get_gap_around_css( $attributes, 'Tablet' ),
			$this->get_gap_around_css( $attributes, 'Mobile' ),
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-price-menu-item-details-brief',
			$this->get_details_brief_css( $attributes, '' ),
			$this->get_details_brief_css( $attributes, 'Tablet' ),
			$this->get_details_brief_css( $attributes, 'Mobile' ),
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-block--price-menu-item',
			$this->get_item_css( $attributes, '' ),
			$this->get_item_css( $attributes, 'Tablet' ),
			$this->get_item_css( $attributes, 'Mobile' ),
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-block--price-menu-item:hover',
			$this->get_item_hover_css( $attributes, '' ),
			$this->get_item_hover_css( $attributes, 'Tablet' ),
			$this->get_item_hover_css( $attributes, 'Mobile' ),
		);

		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-price-menu-item',
			$this->get_inner_item_css( $attributes, '' ),
			$this->get_inner_item_css( $attributes, 'Tablet' ),
			$this->get_inner_item_css( $attributes, 'Mobile' ),
		);

		// TitleText CSS
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-price-menu-item-details-title',
			$this->get_title_text_css( $attributes, '' ),
			$this->get_title_text_css( $attributes, 'Tablet' ),
			$this->get_title_text_css( $attributes, 'Mobile' ),
		);
		// DescriptionText CSS
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-price-menu-item-details-des',
			$this->get_description_text_css( $attributes, '' ),
			$this->get_description_text_css( $attributes, 'Tablet' ),
			$this->get_description_text_css( $attributes, 'Mobile' ),
		);
		// SeparatorText CSS
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-price-menu-divider__pattern-' . ( $attributes['dividerType'] === 'mask-style' ? 'mask' : 'css' ),
			$this->get_divider_css( $attributes, '' ),
			$this->get_divider_css( $attributes, 'Tablet' ),
			$this->get_divider_css( $attributes, 'Mobile' ),
		);
		// PriceText CSS
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-price-menu-item-price',
			$this->get_price_text_css( $attributes, '' ),
			$this->get_price_text_css( $attributes, 'Tablet' ),
			$this->get_price_text_css( $attributes, 'Mobile' ),
		);
		$this->add_layout_styles( $css_generator, $attributes );

		return $css_generator->generate_css();
	}
	public function build_css_v2( $attributes ) {

		$css_generator = new CssGeneratorV2( $attributes, $this->block_name );

		$css_generator->add_class_styles(
			'{{WRAPPER}}:not(.ablocks-has-block-container), {{WRAPPER}}.ablocks-block--price-menu > .ablocks-block-container',
			$this->get_all_menu_css( $attributes, '' ),
			$this->get_all_menu_css( $attributes, 'Tablet' ),
			$this->get_all_menu_css( $attributes, 'Mobile' ),
			$css_generator->custom_device_map( function ( $device ) use ( $attributes ) { return $this->get_all_menu_css( $attributes, $device ); } )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-price-menu-item, {{WRAPPER}} .ablocks-price-menu-item-details',
			$this->get_gap_around_css( $attributes, '' ),
			$this->get_gap_around_css( $attributes, 'Tablet' ),
			$this->get_gap_around_css( $attributes, 'Mobile' ),
			$css_generator->custom_device_map( function ( $device ) use ( $attributes ) { return $this->get_gap_around_css( $attributes, $device ); } )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-price-menu-item-details-brief',
			$this->get_details_brief_css( $attributes, '' ),
			$this->get_details_brief_css( $attributes, 'Tablet' ),
			$this->get_details_brief_css( $attributes, 'Mobile' ),
			$css_generator->custom_device_map( function ( $device ) use ( $attributes ) { return $this->get_details_brief_css( $attributes, $device ); } )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-block--price-menu-item',
			$this->get_item_css( $attributes, '' ),
			$this->get_item_css( $attributes, 'Tablet' ),
			$this->get_item_css( $attributes, 'Mobile' ),
			$css_generator->custom_device_map( function ( $device ) use ( $attributes ) { return $this->get_item_css( $attributes, $device ); } )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-block--price-menu-item:hover',
			$this->get_item_hover_css( $attributes, '' ),
			$this->get_item_hover_css( $attributes, 'Tablet' ),
			$this->get_item_hover_css( $attributes, 'Mobile' ),
			$css_generator->custom_device_map( function ( $device ) use ( $attributes ) { return $this->get_item_hover_css( $attributes, $device ); } )
		);

		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-price-menu-item',
			$this->get_inner_item_css( $attributes, '' ),
			$this->get_inner_item_css( $attributes, 'Tablet' ),
			$this->get_inner_item_css( $attributes, 'Mobile' ),
			$css_generator->custom_device_map( function ( $device ) use ( $attributes ) { return $this->get_inner_item_css( $attributes, $device ); } )
		);

		// TitleText CSS
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-price-menu-item-details-title',
			$this->get_title_text_css( $attributes, '' ),
			$this->get_title_text_css( $attributes, 'Tablet' ),
			$this->get_title_text_css( $attributes, 'Mobile' ),
			$css_generator->custom_device_map( function ( $device ) use ( $attributes ) { return $this->get_title_text_css( $attributes, $device ); } )
		);
		// DescriptionText CSS
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-price-menu-item-details-des',
			$this->get_description_text_css( $attributes, '' ),
			$this->get_description_text_css( $attributes, 'Tablet' ),
			$this->get_description_text_css( $attributes, 'Mobile' ),
			$css_generator->custom_device_map( function ( $device ) use ( $attributes ) { return $this->get_description_text_css( $attributes, $device ); } )
		);
		// SeparatorText CSS
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-price-menu-divider__pattern-' . ( $attributes['dividerType'] === 'mask-style' ? 'mask' : 'css' ),
			$this->get_divider_css( $attributes, '' ),
			$this->get_divider_css( $attributes, 'Tablet' ),
			$this->get_divider_css( $attributes, 'Mobile' ),
			$css_generator->custom_device_map( function ( $device ) use ( $attributes ) { return $this->get_divider_css( $attributes, $device ); } )
		);
		// PriceText CSS
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-price-menu-item-price',
			$this->get_price_text_css( $attributes, '' ),
			$this->get_price_text_css( $attributes, 'Tablet' ),
			$this->get_price_text_css( $attributes, 'Mobile' ),
			$css_generator->custom_device_map( function ( $device ) use ( $attributes ) { return $this->get_price_text_css( $attributes, $device ); } )
		);
		$this->add_layout_styles( $css_generator, $attributes );

		return $css_generator->generate_css();
	}

	// Divider width and Price alignment layout; shared with the items (see
	// PriceMenuItem\Block and src/blocks/price-menu/edit.js).
	private function add_layout_styles( $css_generator, $attributes ) {
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-divider',
			$this->get_divider_width_css( $attributes, '' ),
			$this->get_divider_width_css( $attributes, 'Tablet' ),
			$this->get_divider_width_css( $attributes, 'Mobile' ),
			$css_generator->custom_device_map( function ( $device ) use ( $attributes ) { return $this->get_divider_width_css( $attributes, $device ); } )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-price-menu-item',
			PriceMenuItemBlock::get_item_width_css( $attributes, '' ),
			PriceMenuItemBlock::get_item_width_css( $attributes, 'Tablet' ),
			PriceMenuItemBlock::get_item_width_css( $attributes, 'Mobile' ),
			$css_generator->custom_device_map( function ( $device ) use ( $attributes ) { return PriceMenuItemBlock::get_item_width_css( $attributes, $device ); } )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-price-menu-item-details-brief > .ablocks-price-menu-item-price',
			PriceMenuItemBlock::get_price_row_css( $attributes )
		);
	}

	public function get_divider_width_css( $attributes, $device = '' ) {
		return PriceMenuItemBlock::get_divider_width_css( $attributes, $device, '--ablocks-pm-divider-width', 100 );
	}
	public function build_css( $attributes ) {
		if ( isset( $attributes['blockVersion'] ) && (int) $attributes['blockVersion'] === 2 ) {
			return $this->build_css_v2( $attributes );
		}
		return $this->build_css_v1( $attributes );
	}

	public function get_item_css( $attributes, $device = '' ) {
		$css = [];

		if ( ! empty( $attributes['transition'] ) ) {
			$css['transition-duration'] = $attributes['transition'] . 's';
		}
		return array_merge(
			[ 'background' => Color::get_css( isset( $attributes['itemBackground'] ) ? $attributes['itemBackground'] : '' ) ],
			$css,
			isset( $attributes['alignment'] ) ? Alignment::get_css( $attributes['alignment'], 'justify-content', $device ) : [],
			isset( $attributes['itemBorder'] ) ? Border::get_css( $attributes['itemBorder'], '', $device ) : [],
			isset( $attributes['itemPadding'] ) ? Dimensions::get_css( $attributes['itemPadding'], 'padding', $device ) : [],
		);
	}

	public function get_item_hover_css( $attributes, $device = '' ) {
		return array_merge(
			[ 'background' => Color::get_css( isset( $attributes['itemBackgroundH'] ) ? $attributes['itemBackgroundH'] : '' ) ],
			isset( $attributes['itemBorder'] ) ? Border::get_hover_css( $attributes['itemBorder'], '', $device ) : [],
		);
	}

	public function get_inner_item_css( $attributes, $device = '' ) {
		$css = [];

		if ( isset( $attributes['itemsDirection'][ 'value' . $device ] ) ) {
			$css['flex-direction'] = $attributes['itemsDirection'][ 'value' . $device ];
		}

		if ( isset( $attributes['alignment'][ 'value' . $device ] ) ) {
			$css['align-items'] = $attributes['alignment'][ 'value' . $device ];
		}

		return $css;
	}

	public function get_gap_around_css( $attributes, $device = '' ) {
		$css = [];
		if ( isset( $attributes['alignment'][ 'value' . $device ] ) ) {
			$css['align-items'] = $attributes['alignment'][ 'value' . $device ];
		}

		return array_merge(
			$css,
			Range::get_css([
				'attributeValue' => $attributes['gap'],
				'attribute_object_key' => 'value',
				'isResponsive' => true,
				'hasUnit' => true,
				'defaultValue' => 10,
				'unitDefaultValue' => 'px',
				'property' => 'gap',
				'device' => $device,
			]),
		);
	}

	public function get_details_brief_css( $attributes, $device = '' ) {
		$css = [];
		if ( isset( $attributes['alignment'][ 'value' . $device ] ) ) {
			if ( $attributes['itemsDirection'][ 'value' . $device ] === 'row' ) {
				$css['justify-content'] = 'space-between';
			} else {
				$css['justify-content'] = $attributes['alignment'][ 'value' . $device ];
			}
		}

		$gap = Range::get_css([
			'attributeValue' => $attributes['gap'],
			'attribute_object_key' => 'value',
			'isResponsive' => true,
			'hasUnit' => true,
			'defaultValue' => 10,
			'unitDefaultValue' => 'px',
			'property' => 'gap',
			'device' => $device,
		]);
		// Title & price gap; unset falls back to the gap above. Longhands only: a
		// `gap` shorthand at a narrower breakpoint would reset an inherited
		// column-gap that the generator dedupes away.
		$title_price_gap = Range::get_css([
			'attributeValue' => isset( $attributes['titlePriceGap'] ) ? $attributes['titlePriceGap'] : [],
			'attributeObjectKey' => 'value',
			'isResponsive' => true,
			'hasUnit' => true,
			'unitDefaultValue' => 'px',
			'property' => 'column-gap',
			'device' => $device,
		]);
		$gap_css = [];
		if ( isset( $gap['gap'] ) ) {
			$gap_css['row-gap'] = $gap['gap'];
		}
		// A % gap is rigid and would crush the title once it outgrows the free
		// space, so it becomes shrinkable spacers (style.css) that yield first.
		if ( PriceMenuItemBlock::is_percent_title_price_gap( $attributes, $device ) ) {
			$gap_css['column-gap'] = '0px';
			$gap_css['--ablocks-pm-tp-gap'] = $title_price_gap['column-gap'];
			$gap_css['--ablocks-pm-tp-spacer'] = 'block';
			return array_merge( $gap_css, $css );
		}
		if ( isset( $title_price_gap['column-gap'] ) || isset( $gap['gap'] ) ) {
			$gap_css['column-gap'] = isset( $title_price_gap['column-gap'] ) ? $title_price_gap['column-gap'] : $gap['gap'];
		}
		if ( $device && PriceMenuItemBlock::has_percent_title_price_gap( $attributes ) ) {
			$gap_css['--ablocks-pm-tp-spacer'] = 'none';
		}

		return array_merge( $gap_css, $css );
	}

	public function get_all_menu_css( $attributes, $device = '' ) {
		$css = [];
		$css['display'] = 'flex';
		$css['flex-wrap'] = 'wrap';
		$css['flex-direction'] = 'column';

		return array_merge(
			$css,
			Range::get_css([
				'attributeValue' => $attributes['columnGap'],
				'attribute_object_key' => 'value',
				'isResponsive' => true,
				'hasUnit' => true,
				'defaultValue' => 20,
				'unitDefaultValue' => 'px',
				'property' => 'gap',
				'device' => $device,
			]),
		);
	}

	public function get_title_text_css( $attributes, $device = '' ) {
		$typographyValueGlobal = ! empty( $attributes['titleTypographyGlobal'] ) ? $attributes['titleTypographyGlobal'] : array();
		return array_merge(
			[ 'color' => Color::get_css( isset( $attributes['titleColor'] ) ? $attributes['titleColor'] : '' ) ],
			isset( $attributes['titleTypography'] ) ? Typography::get_css( $attributes['titleTypography'], '', $device, $typographyValueGlobal ) : [],
			isset( $attributes['titleTextStroke'] ) ? TextStroke::get_css( $attributes['titleTextStroke'], '', $device ) : [],
			isset( $attributes['titleTextShadow'] ) ? TextShadow::get_css( $attributes['titleTextShadow'], '', $device ) : [],
			isset( $attributes['titleAlignment'] ) ? Alignment::get_css( $attributes['titleAlignment'], 'text-align', $device ) : [],
		);
	}
	public function get_description_text_css( $attributes, $device = '' ) {
		$typographyValueGlobal = ! empty( $attributes['descriptionTypographyGlobal'] ) ? $attributes['descriptionTypographyGlobal'] : array();
		return array_merge(
			[ 'color' => Color::get_css( isset( $attributes['descriptionColor'] ) ? $attributes['descriptionColor'] : '' ) ],
			isset( $attributes['descriptionTypography'] ) ? Typography::get_css( $attributes['descriptionTypography'], '', $device, $typographyValueGlobal ) : [],
			isset( $attributes['descriptionTextStroke'] ) ? TextStroke::get_css( $attributes['descriptionTextStroke'], '', $device ) : [],
			isset( $attributes['descriptionTextShadow'] ) ? TextShadow::get_css( $attributes['descriptionTextShadow'], '', $device ) : [],
			isset( $attributes['descriptionAlignment'] ) ? Alignment::get_css( $attributes['descriptionAlignment'], 'text-align', $device ) : [],
		);
	}
	public function get_divider_css( $attributes, $device = '' ) {
		$css = [];
		$css['--ablocks-divider-pattern-color'] = Color::get_css(
		isset( $attributes['color'] ) ? $attributes['color'] : '#000000');

		$moreRangeCSS = [];
		if ( isset( $attributes['dividerType'] ) && $attributes['dividerType'] === 'mask-style' && isset( $attributes['size'] ) && ! empty( $attributes['size'] ) ) {
			$moreRangeCSS = Range::get_css([
				'attributeValue' => $attributes['size'],
				'attribute_object_key' => 'value',
				'isResponsive' => false,
				'defaultValue' => 20,
				'hasUnit' => false,
				'unitDefaultValue' => 'px',
				'property' => '--ablocks-divider-pattern-height',
			]);
		} elseif ( isset( $attributes['weight'] ) && ! empty( $attributes['weight'] ) ) {
			$moreRangeCSS = Range::get_css([
				'attributeValue' => $attributes['weight'],
				'attribute_object_key' => 'value',
				'isResponsive' => false,
				'defaultValue' => 2,
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
			$moreRangeCSS,
		);
	}
	public function get_price_text_css( $attributes, $device = '' ) {
		$typographyValueGlobal = ! empty( $attributes['priceTypographyGlobal'] ) ? $attributes['priceTypographyGlobal'] : array();
		return array_merge(
			[ 'color' => Color::get_css( isset( $attributes['priceColor'] ) ? $attributes['priceColor'] : '' ) ],
			isset( $attributes['priceTypography'] ) ? Typography::get_css( $attributes['priceTypography'], '', $device, $typographyValueGlobal ) : [],
			isset( $attributes['priceTextStroke'] ) ? TextStroke::get_css( $attributes['priceTextStroke'], '', $device ) : [],
			isset( $attributes['priceTextShadow'] ) ? TextShadow::get_css( $attributes['priceTextShadow'], '', $device ) : [],
			isset( $attributes['priceAlignment'] ) ? Alignment::get_css( $attributes['priceAlignment'], 'text-align', $device ) : [],
		);
	}

}
