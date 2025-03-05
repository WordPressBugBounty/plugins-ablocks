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
				'yPositionUnit' => '0',
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

		/**
		 * Generated CSS
		 * css property
		 * mask-image
		 * mask-size
		 * mask-position
		 * mask-repeat
		 */

		if ( $value['mask'] ) {
			if ( 'custom' === $value['maskShape'] ) {
				$css['mask-image'] = 'url(' . $value['customMaskShape'] . ')';
			} else {
				$css['mask-image'] = 'url(' . ABLOCKS_ROOT_URL . '/assets/images/mask-shapes/' . $value['maskShape'] . '.svg)';
			}
			$css['mask-size'] = 'contain';
			$css['mask-position'] = 'center center';
			$css['mask-repeat'] = 'no-repeat';
		}

		if ( $value[ 'maskSize' . $device ] && 'custom' !== $value[ 'maskSize' . $device ] ) {
			$css['mask-size'] = $value[ 'maskSize' . $device ];
		}

		if (
			$value[ 'maskSize' . $device ] &&
			'custom' === $value[ 'maskSize' . $device ] &&
			$value[ 'scaleUnit' . $device ]
		) {
			$css['mask-size'] = $value[ 'scale' . $device ] . $value[ 'scaleUnit' . $device ];
		}

		if (
			'custom' === $value[ 'maskPosition' . $device ] &&
			$value[ 'xPosition' . $device ] &&
			$value[ 'xPositionUnit' . $device ]
		) {
			$css['-webkit-mask-position-x'] = $value[ 'xPosition' . $device ] . $value[ 'xPositionUnit' . $device ];
			$css['-webkit-mask-position-y'] = $value[ 'yPosition' . $device ] . $value[ 'yPositionUnit' . $device ];
		}

		if (
			'custom' === $value[ 'maskPosition' . $device ] &&
			$value[ 'yPosition' . $device ] &&
			$value[ 'yPositionUnit' . $device ]
		) {
			$css['-webkit-mask-position-y'] = $value[ 'yPosition' . $device ] . $value[ 'yPositionUnit' . $device ];
		}

		if ( $value[ 'maskRepeat' . $device ] ) {
			$css['mask-repeat'] = $value[ 'maskRepeat' . $device ];
		}
		return $css;
	}


}
