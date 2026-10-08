<?php
namespace ABlocks\Blocks\PriceMenuItem;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ABlocks\Classes\BlockBaseAbstract;
use ABlocks\Classes\CssGenerator;
use ABlocks\Controls\Icon;
use ABlocks\Controls\Alignment;
use ABlocks\Controls\Typography;
use ABlocks\Controls\TextShadow;
use ABlocks\Controls\TextStroke;
use ABlocks\Controls\Range;
use ABlocks\Controls\Color;
use ABlocks\Helper;

class Block extends BlockBaseAbstract {
	protected $parent_block_name = 'price-menu';
	protected $block_name = 'price-menu-item';

	public function build_css( $attributes ) {

		$css_generator = new CssGenerator( $attributes, $this->block_name );
		// Icon Style
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-icon-wrap',
			Icon::get_wrapper_css( $attributes ),
			Icon::get_wrapper_css( $attributes, 'Tablet' ),
			Icon::get_wrapper_css( $attributes, 'Mobile' ),
			$css_generator->custom_device_map( function ( $device ) use ( $attributes ) { return Icon::get_wrapper_css( $attributes, $device ); } )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-icon-wrap:hover',
			Icon::get_wrapper_hover_css( $attributes ),
			Icon::get_wrapper_hover_css( $attributes, 'Tablet' ),
			Icon::get_wrapper_hover_css( $attributes, 'Mobile' ),
			$css_generator->custom_device_map( function ( $device ) use ( $attributes ) { return Icon::get_wrapper_hover_css( $attributes, $device ); } )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-icon-wrap img.ablocks-image-icon',
			Icon::get_element_image_css( $attributes ),
			Icon::get_element_image_css( $attributes, 'Tablet' ),
			Icon::get_element_image_css( $attributes, 'Mobile' ),
			$css_generator->custom_device_map( function ( $device ) use ( $attributes ) { return Icon::get_element_image_css( $attributes, $device ); } )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-icon-wrap img.ablocks-image-icon:hover',
			Icon::get_element_image_hover_css( $attributes ),
			Icon::get_element_image_hover_css( $attributes, 'Tablet' ),
			Icon::get_element_image_hover_css( $attributes, 'Mobile' ),
			$css_generator->custom_device_map( function ( $device ) use ( $attributes ) { return Icon::get_element_image_hover_css( $attributes, $device ); } )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-icon-wrap svg.ablocks-svg-icon',
			Icon::get_element_css( $attributes ),
			Icon::get_element_css( $attributes, 'Tablet' ),
			Icon::get_element_css( $attributes, 'Mobile' ),
			$css_generator->custom_device_map( function ( $device ) use ( $attributes ) { return Icon::get_element_css( $attributes, $device ); } )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-icon-wrap svg.ablocks-svg-icon:hover',
			Icon::get_element_image_hover_css( $attributes ),
			Icon::get_element_image_hover_css( $attributes, 'Tablet' ),
			Icon::get_element_image_hover_css( $attributes, 'Mobile' ),
			$css_generator->custom_device_map( function ( $device ) use ( $attributes ) { return Icon::get_element_image_hover_css( $attributes, $device ); } )
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
		// Divider width (item override of the parent's width)
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-divider',
			self::get_divider_width_css( $attributes, '' ),
			self::get_divider_width_css( $attributes, 'Tablet' ),
			self::get_divider_width_css( $attributes, 'Mobile' ),
			$css_generator->custom_device_map( function ( $device ) use ( $attributes ) { return self::get_divider_width_css( $attributes, $device ); } )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-price-menu-item',
			self::get_item_width_css( $attributes, '' ),
			self::get_item_width_css( $attributes, 'Tablet' ),
			self::get_item_width_css( $attributes, 'Mobile' ),
			$css_generator->custom_device_map( function ( $device ) use ( $attributes ) { return self::get_item_width_css( $attributes, $device ); } )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-price-menu-item-details-brief > .ablocks-price-menu-item-price',
			self::get_price_row_css( $attributes )
		);
		// Responsive placements: hide the slots not used on each device.
		foreach ( [
			'divider' => isset( $attributes['placeDivider'] ) ? $attributes['placeDivider'] : '',
			'price' => isset( $attributes['placePrice'] ) ? $attributes['placePrice'] : '',
		] as $kind => $placement ) {
			foreach ( self::get_used_placements( $placement ) as $slot ) {
				$css_generator->add_class_styles(
					'{{WRAPPER}} .' . self::get_slot_class_name( $kind, $slot ),
					self::get_slot_css( $placement, $slot, '' ),
					self::get_slot_css( $placement, $slot, 'Tablet' ),
					self::get_slot_css( $placement, $slot, 'Mobile' ),
					$css_generator->custom_device_map( function ( $device ) use ( $placement, $slot ) { return self::get_slot_css( $placement, $slot, $device ); } )
				);
			}
		}

		return $css_generator->generate_css();
	}

	/**
	 * Divider width as a custom property; mirrors get_divider_width_css() in
	 * src/blocks/price-menu-item/styling.js. The parent passes its own property
	 * and default. A width without a stored unit keeps the legacy unit: % with a
	 * description, px without.
	 */
	public static function get_divider_width_css( $attributes, $device = '', $property = '--ablocks-pm-item-divider-width', $default_value = null ) {
		return Range::get_css([
			'attributeValue' => isset( $attributes['width'] ) ? $attributes['width'] : [],
			'attributeObjectKey' => 'value',
			'isResponsive' => true,
			'defaultValue' => $default_value,
			'hasUnit' => true,
			'unitDefaultValue' => ! empty( $attributes['allowDescription'] ) ? '%' : 'px',
			'property' => $property,
			'device' => $device,
		]);
	}

	// A responsive member for the device, inherited from wider devices; '' when unset.
	private static function resolve_member( $value, $key, $device ) {
		$target = 'Desktop' === $device ? '' : (string) $device;
		foreach ( array_merge( [ $target ], Helper::get_responsive_ancestors( $target ) ) as $suffix ) {
			$member = is_array( $value ) && isset( $value[ $key . $suffix ] ) && ! is_array( $value[ $key . $suffix ] ) ? (string) $value[ $key . $suffix ] : '';
			if ( Helper::has_responsive_value( $member ) ) {
				return $member;
			}
		}
		return '';
	}

	// A % Title & price gap in effect on this device (unit default px).
	public static function is_percent_title_price_gap( $attributes, $device = '' ) {
		$gap = isset( $attributes['titlePriceGap'] ) ? $attributes['titlePriceGap'] : [];
		return '' !== self::resolve_member( $gap, 'value', $device ) && '%' === Range::get_unit( [
			'attributeValue' => $gap,
			'attributeObjectKey' => 'value',
			'unitDefaultValue' => 'px',
			'device' => $device,
		] );
	}

	// A % on the title row; mirrors isPercentTitleRow() in styling.js. The legacy
	// implicit % (Allow description, no stored unit) is left alone.
	public static function is_percent_title_row( $attributes, $device = '' ) {
		return self::is_percent_title_price_gap( $attributes, $device ) || (
			! empty( $attributes['allowDivider'] ) &&
			'near title' === self::get_placement( isset( $attributes['placeDivider'] ) ? $attributes['placeDivider'] : '', $device ) &&
			'%' === self::resolve_member( isset( $attributes['width'] ) ? $attributes['width'] : [], 'valueUnit', $device )
		);
	}

	private static function has_percent_unit( $value ) {
		if ( ! is_array( $value ) ) {
			return false;
		}
		foreach ( $value as $key => $member ) {
			if ( 0 === strpos( $key, 'valueUnit' ) && '%' === $member ) {
				return true;
			}
		}
		return false;
	}

	public static function has_percent_title_price_gap( $attributes ) {
		return self::has_percent_unit( isset( $attributes['titlePriceGap'] ) ? $attributes['titlePriceGap'] : [] );
	}

	// Whether any device can use the % title row, so narrower devices that do
	// not must reset the item width they would otherwise inherit.
	public static function has_percent_title_row( $attributes ) {
		return self::has_percent_title_price_gap( $attributes ) || (
			! empty( $attributes['allowDivider'] ) &&
			in_array( 'near title', self::get_used_placements( isset( $attributes['placeDivider'] ) ? $attributes['placeDivider'] : '' ), true ) &&
			self::has_percent_unit( isset( $attributes['width'] ) ? $attributes['width'] : [] )
		);
	}

	// Once Price alignment is chosen the item spans its row and the price fills
	// the space after the title/divider so text-align can position it. A % on
	// the title row needs the same; mirrors get_item_width_css() in styling.js.
	public static function get_item_width_css( $attributes, $device = '' ) {
		if ( ! empty( $attributes['priceAlignmentCustom'] ) ) {
			return $device ? [] : [ 'width' => '100%' ];
		}
		if ( self::is_percent_title_row( $attributes, $device ) ) {
			return [ 'width' => '100%' ];
		}
		return $device && self::has_percent_title_row( $attributes ) ? [ 'width' => 'fit-content' ] : [];
	}

	public static function get_price_row_css( $attributes ) {
		return ! empty( $attributes['priceAlignmentCustom'] ) ? [ 'flex-grow' => '1' ] : [];
	}

	/**
	 * placeDivider / placePrice: a legacy string or a responsive array
	 * ( value, valueTablet, valueMobile, … ). Mirrors placement.js.
	 */
	public static function get_placement( $placement, $device = '' ) {
		$placement = is_array( $placement ) ? $placement : [ 'value' => (string) $placement ];
		$target    = 'Desktop' === $device ? '' : (string) $device;
		foreach ( array_merge( [ $target ], Helper::get_responsive_ancestors( $target ) ) as $suffix ) {
			$value = isset( $placement[ 'value' . $suffix ] ) ? $placement[ 'value' . $suffix ] : '';
			if ( Helper::has_responsive_value( $value ) ) {
				return $value;
			}
		}
		return '';
	}

	public static function get_used_placements( $placement ) {
		if ( ! is_array( $placement ) ) {
			return Helper::has_responsive_value( $placement ) ? [ $placement ] : [];
		}
		$used = [];
		foreach ( $placement as $key => $value ) {
			if ( 0 === strpos( $key, 'value' ) && is_string( $value ) && Helper::has_responsive_value( $value ) ) {
				$used[] = $value;
			}
		}
		return array_values( array_unique( $used ) );
	}

	// The placement is a block attribute and this name lands in a selector of
	// an inline <style>, which esc_css_value() does not cover; keep it a class.
	public static function get_slot_class_name( $kind, $placement ) {
		return 'ablocks-price-menu-' . $kind . '-slot--' . sanitize_html_class( preg_replace( '/\s+/', '-', $placement ) );
	}

	public static function get_slot_css( $placement, $slot, $device = '' ) {
		if ( count( self::get_used_placements( $placement ) ) < 2 ) {
			return [];
		}
		return [ 'display' => self::get_placement( $placement, $device ) === $slot ? 'block' : 'none' ];
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

		if ( ! empty( $attributes['color'] ) ) {
			$css['--ablocks-divider-pattern-color'] = Color::get_css(
			isset( $attributes['color'] ) ? $attributes['color'] : '#000000');

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
