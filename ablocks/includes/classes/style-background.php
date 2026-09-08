<?php
namespace ABlocks\Classes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Background overlay layers -> CSS.
 *
 * Mirror of the compile half of `src/blocks/atomic-shared/background.js`.
 * `background-image` is a list — several images and gradients stacked, first one
 * on top — with `background-position` / `-size` / `-repeat` / `-attachment`
 * carrying one entry per layer, so the whole group compiles together here.
 *
 * The editor computes a style hash over compiled output, so this formatter must
 * match the JS one character for character. The parity harness asserts exactly
 * that.
 */
class StyleBackground {

	/** Per-layer fallbacks, used when ANY layer sets that property. */
	const LAYER_FALLBACK = [
		'position'   => 'center center',
		'size'       => 'auto',
		'repeat'     => 'repeat',
		'attachment' => 'scroll',
	];

	/** Member -> CSS property, in emission order. */
	const LAYER_PROPERTIES = [
		'position'   => 'background-position',
		'size'       => 'background-size',
		'repeat'     => 'background-repeat',
		'attachment' => 'background-attachment',
	];

	/**
	 * A numeric member, falling back when absent or unparseable.
	 *
	 * @param mixed $value    Raw stored value.
	 * @param float $fallback Fallback.
	 * @return float|int The number.
	 */
	private static function num( $value, $fallback = 0 ) {
		if ( is_numeric( $value ) ) {
			return $value + 0;
		}
		return $fallback;
	}

	/**
	 * Trim a float the way JS number-to-string does (1.0 -> "1").
	 *
	 * @param mixed $value Number to format.
	 * @return string Formatted number.
	 */
	private static function n( $value ) {
		$value = (float) $value;
		return ( floor( $value ) === $value ) ? (string) (int) $value : (string) $value;
	}

	/**
	 * One layer -> its `background-image` fragment, or '' when it has nothing to
	 * paint (an image layer with no file yet).
	 *
	 * @param array $e Layer entry.
	 * @return string CSS fragment.
	 */
	public static function layer_image( $e ) {
		if ( ! is_array( $e ) ) {
			return '';
		}

		if ( isset( $e['type'] ) && 'image' === $e['type'] ) {
			return ! empty( $e['url'] ) ? 'url("' . $e['url'] . '")' : '';
		}

		$from = ( ! empty( $e['from'] ) ? $e['from'] : '#000000' ) . ' ' . self::n( self::num( $e['fromStop'] ?? 0 ) ) . '%';
		$to   = ( ! empty( $e['to'] ) ? $e['to'] : 'rgba(0, 0, 0, 0)' ) . ' ' . self::n( self::num( $e['toStop'] ?? 100, 100 ) ) . '%';

		if ( isset( $e['gradientType'] ) && 'radial' === $e['gradientType'] ) {
			// Radial centre. Deliberately NOT `position`: that member is the
			// layer's background-position, and sharing one key made picking a
			// radial centre also pin background-position on the whole stack.
			$at = ! empty( $e['radialPosition'] ) ? $e['radialPosition'] : 'center center';
			return 'radial-gradient(circle at ' . $at . ', ' . $from . ', ' . $to . ')';
		}

		return 'linear-gradient(' . self::n( self::num( $e['angle'] ?? 180, 180 ) ) . 'deg, ' . $from . ', ' . $to . ')';
	}

	/**
	 * A list of layers -> the background declarations they compile to.
	 *
	 * The four positional properties are emitted only when at least one layer
	 * sets them, and when they are, every layer supplies its own entry so the
	 * comma lists stay index-aligned with the image list.
	 *
	 * @param mixed $entries Stored layers.
	 * @return array CSS declarations keyed by property, in emission order.
	 */
	public static function to_declarations( $entries ) {
		if ( ! is_array( $entries ) || empty( $entries ) ) {
			return [];
		}

		$layers = [];
		foreach ( $entries as $entry ) {
			if ( ! is_array( $entry ) ) {
				continue;
			}
			// Mirrors the `! e.disabled` filter in background.js, and the same
			// check StyleEffects::to_css() already made for the other
			// repeatable lists — the inspector's per-entry eye toggle has to
			// mean something here too.
			if ( ! empty( $entry['disabled'] ) ) {
				continue;
			}
			$image = self::layer_image( $entry );
			if ( '' === $image ) {
				continue;
			}
			$layers[] = [
				'entry' => $entry,
				'image' => $image
			];
		}

		if ( empty( $layers ) ) {
			return [];
		}

		$css = [ 'background-image' => implode( ', ', wp_list_pluck( $layers, 'image' ) ) ];

		foreach ( self::LAYER_PROPERTIES as $member => $property ) {
			$any_set = false;
			foreach ( $layers as $layer ) {
				if ( ! empty( $layer['entry'][ $member ] ) ) {
					$any_set = true;
					break;
				}
			}
			if ( ! $any_set ) {
				continue;
			}

			$values = [];
			foreach ( $layers as $layer ) {
				$values[] = ! empty( $layer['entry'][ $member ] )
					? $layer['entry'][ $member ]
					: self::LAYER_FALLBACK[ $member ];
			}
			$css[ $property ] = implode( ', ', $values );
		}

		return $css;
	}
}
