<?php
namespace ABlocks\Controls;

use ABlocks\Classes\ControlBaseAbstract;

class Mask extends ControlBaseAbstract {
	public static function get_attribute_default_value( $is_responsive = false ) {
		if ( $is_responsive ) {
			return [
				'mask' => false,
				'maskTablet' => false,
				'maskMobile' => false,
				'maskShape' => 'circle',
				'customMaskShape' => '',
				'maskSize' => '',
				'maskSizeTablet' => '',
				'maskSizeMobile' => '',
				'maskPosition' => '',
				'maskPositionTablet' => '',
				'maskPositionMobile' => '',
				'maskRepeat' => '',
				'maskRepeatTablet' => '',
				'maskRepeatMobile' => '',
				'scaleUnit' => 'px',
				'scaleUnitTablet' => 'px',
				'scaleUnitMobile' => 'px',
				'scale' => '0',
				'scaleTablet' => '0',
				'scaleMobile' => '0',
				'xPositionUnit' => 'px',
				'xPositionUnitTablet' => 'px',
				'xPositionUnitMobile' => 'px',
				'xPosition' => '',
				'xPositionTablet' => '0',
				'xPositionMobile' => '0',
				'yPositionUnit' => 'px',
				'yPositionUnitTablet' => 'px',
				'yPositionUnitMobile' => 'px',
				'yPosition' => '0',
				'yPositionTablet' => '0',
				'yPositionMobile' => '0',
			];
		}//end if
		return [
			'mask' => false,
			'maskShape' => 'circle',
			'maskSize' => '',
			'maskPosition' => '',
			'maskRepeat' => '',
			'scaleUnit' => 'px',
			'scale' => '0',
			'xPosition' => '',
			'xPositionUnit' => 'px',
			'yPosition' => '',
			'yPositionUnit' => 'px',
		];
	}

	public static function get_attribute( $attributeName, $isResponsive = false ) {
		return [
			$attributeName => [
				'type' => 'object',
				'default' => self::get_attribute_default_value( $isResponsive ),
			]
		];
	}

	public static function get_css( $attribute_value, $property = '', $device = '' ) {
		$default_attar_value = self::get_attribute_default_value( (bool) $device );
		$value = wp_parse_args( $attribute_value, $default_attar_value );

		$css = [];

		// Base mask defaults — desktop only. Tablet/mobile calls must NOT re-state these
		// because filter_responsive_styles would incorrectly emit them in media queries when
		// the user's desktop setting differs (e.g. desktop position: top left → tablet would
		// wrongly reset to center center).
		if ( $value['mask'] && '' === $device ) {
			if ( 'custom' === $value['maskShape'] ) {
				$css['mask-image'] = 'url(' . $value['customMaskShape'] . ')';
			} else {
				$css['mask-image'] = 'url(' . ABLOCKS_ROOT_URL . '/assets/images/mask-shapes/' . $value['maskShape'] . '.svg)';
			}
			$css['-webkit-mask-image'] = $css['mask-image'];
			$css['mask-size'] = 'contain';
			$css['-webkit-mask-size'] = 'contain';
			$css['mask-position'] = 'center center';
			$css['-webkit-mask-position'] = 'center center';
			$css['mask-repeat'] = 'no-repeat';
			$css['-webkit-mask-repeat'] = 'no-repeat';
		}

		// Device-specific overrides. Read through device_member(): a custom
		// breakpoint (or the generator's value-less probe suffix) has no key of
		// its own in the control's defaults, and indexing it directly is what
		// produced "Undefined array key …" notices on every page.
		$mask_size     = self::device_member( $value, 'maskSize', $device );
		$mask_position = self::device_member( $value, 'maskPosition', $device );
		$mask_repeat   = self::device_member( $value, 'maskRepeat', $device );

		// Device-specific size: named value (contain / cover)
		if ( $mask_size && 'custom' !== $mask_size ) {
			$css['mask-size'] = $mask_size;
			$css['-webkit-mask-size'] = $css['mask-size'];
		}

		// Device-specific size: custom px/em/% value
		$scale_unit = self::device_member( $value, 'scaleUnit', $device );
		if ( $mask_size && 'custom' === $mask_size && $scale_unit ) {
			$css['mask-size'] = self::device_member( $value, 'scale', $device ) . $scale_unit;
			$css['-webkit-mask-size'] = $css['mask-size'];
		}

		// Device-specific position: named value (e.g. top left, center center)
		if ( $mask_position && 'custom' !== $mask_position ) {
			$css['mask-position'] = $mask_position;
			$css['-webkit-mask-position'] = $css['mask-position'];
		}

		// Device-specific position: custom X Y values
		// Use !== '' rather than truthy so a value of '0' is not skipped.
		if ( 'custom' === $mask_position ) {
			$x_pos  = self::device_member( $value, 'xPosition', $device );
			$y_pos  = self::device_member( $value, 'yPosition', $device );
			$x_val  = '' !== $x_pos ? $x_pos : '0';
			$y_val  = '' !== $y_pos ? $y_pos : '0';
			$x_unit = self::device_member( $value, 'xPositionUnit', $device );
			$y_unit = self::device_member( $value, 'yPositionUnit', $device );
			$css['mask-position'] = $x_val . $x_unit . ' ' . $y_val . $y_unit;
			$css['-webkit-mask-position'] = $css['mask-position'];
		}

		// Device-specific repeat override
		if ( $mask_repeat ) {
			$css['mask-repeat'] = $mask_repeat;
			$css['-webkit-mask-repeat'] = $css['mask-repeat'];
		}

		return $css;
	}


}
