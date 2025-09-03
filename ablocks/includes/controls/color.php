<?php
namespace ABlocks\Controls;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ABlocks\Classes\ControlBaseAbstract;

class Color {

	public static function parse_preset_color_to_object( $value ) {
		if ( is_string( $value ) && strpos( $value, 'var:preset' ) === 0 ) {
			$parts = explode( '|', $value );
			if ( count( $parts ) === 3 ) {
				return [
					'source' => $parts[0],
					'color'  => $parts[1],
					'slug'   => $parts[2],
				];
			}
		}
		return false;
	}

	public static function get_css( $attribute_value ) {
		if ( ! empty( $attribute_value ) ) {
			$preset_color = self::parse_preset_color_to_object( $attribute_value );
			return $preset_color
				? sprintf( 'var(--wp--preset--color--%s)', esc_attr( $preset_color['slug'] ) )
				: $attribute_value;
		}
		return '';
	}
}
