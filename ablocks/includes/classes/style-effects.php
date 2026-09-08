<?php
namespace ABlocks\Classes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Repeatable effects -> CSS.
 *
 * Mirror of the compile half of `src/blocks/atomic-shared/effects.js`. Box
 * shadow, transform, transition and the two filter props are stored as arrays
 * of structured entries (CSS accepts several of each), so they compile by
 * formatting every entry and joining with that effect's separator.
 *
 * The editor computes a style hash over compiled output, so these formatters
 * must match the JS ones character for character. The parity harness asserts
 * exactly that.
 */
class StyleEffects {

	/** How each effect's entries are joined into one CSS value. */
	const JOIN = [
		'boxShadow'      => ', ',
		'textShadow'     => ', ',
		'transform'      => ' ',
		'transition'     => ', ',
		'filter'         => ' ',
		'backdropFilter' => ' ',
	];

	/** Filter type -> unit and default, mirroring FILTER_TYPES in the JS. */
	const FILTERS = [
		'blur'       => [
			'unit' => 'px',
			'def' => 0
		],
		'brightness' => [
			'unit' => '%',
			'def' => 100
		],
		'contrast'   => [
			'unit' => '%',
			'def' => 100
		],
		'saturate'   => [
			'unit' => '%',
			'def' => 100
		],
		'grayscale'  => [
			'unit' => '%',
			'def' => 0
		],
		'sepia'      => [
			'unit' => '%',
			'def' => 0
		],
		'invert'     => [
			'unit' => '%',
			'def' => 0
		],
		'hue-rotate' => [
			'unit' => 'deg',
			'def' => 0
		],
		'opacity'    => [
			'unit' => '%',
			'def' => 100
		],
	];

	/**
	 * Whether a prop is a repeatable effect list.
	 *
	 * @param string $prop Prop name.
	 * @return bool Whether it is an effect list.
	 */
	public static function is_effect( $prop ) {
		return isset( self::JOIN[ $prop ] );
	}

	/**
	 * A numeric entry field, falling back when absent or unparseable.
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
	 * One shadow entry -> its CSS fragment.
	 *
	 * @param array $e Shadow entry.
	 * @return string CSS.
	 */
	private static function shadow_css( $e ) {
		$parts = [
			self::n( self::num( $e['x'] ?? 0 ) ) . 'px',
			self::n( self::num( $e['y'] ?? 0 ) ) . 'px',
			self::n( self::num( $e['blur'] ?? 10, 10 ) ) . 'px',
			self::n( self::num( $e['spread'] ?? 0 ) ) . 'px',
			! empty( $e['color'] ) ? $e['color'] : 'rgba(0, 0, 0, 0.5)',
		];
		$prefix = ( isset( $e['position'] ) && 'inset' === $e['position'] ) ? 'inset ' : '';
		return $prefix . implode( ' ', $parts );
	}

	/**
	 * One text-shadow entry -> its CSS fragment.
	 *
	 * `text-shadow` takes no spread and no inset, so it is its own entry shape
	 * rather than a reuse of the box-shadow one.
	 *
	 * @param array $e Text shadow entry.
	 * @return string CSS.
	 */
	private static function text_shadow_css( $e ) {
		return implode(
			' ',
			[
				self::n( self::num( $e['x'] ?? 0 ) ) . 'px',
				self::n( self::num( $e['y'] ?? 0 ) ) . 'px',
				self::n( self::num( $e['blur'] ?? 4, 4 ) ) . 'px',
				! empty( $e['color'] ) ? $e['color'] : 'rgba(0, 0, 0, 0.5)',
			]
		);
	}

	/**
	 * One transform entry -> its CSS function(s).
	 *
	 * @param array $e Transform entry.
	 * @return string CSS.
	 */
	private static function transform_css( $e ) {
		$type = isset( $e['type'] ) ? $e['type'] : 'move';
		$x    = self::num( $e['x'] ?? 0 );
		$y    = self::num( $e['y'] ?? 0 );
		$z    = self::num( $e['z'] ?? 0 );

		switch ( $type ) {
			case 'scale':
				// Scale defaults to 1, not 0 — a stored 0 would collapse the element.
				$sx = self::n( self::num( $e['x'] ?? 1, 1 ) );
				// One value driving every axis, which is how Elementor's scale
				// behaves until you unlink it.
				if ( ! isset( $e['proportional'] ) || false !== $e['proportional'] ) {
					return sprintf( 'scale3d(%s, %s, 1)', $sx, $sx );
				}
				return sprintf(
					'scale3d(%s, %s, %s)',
					$sx,
					self::n( self::num( $e['y'] ?? 1, 1 ) ),
					self::n( self::num( $e['z'] ?? 1, 1 ) )
				);

			case 'rotate':
				// One rotation PER AXIS. The previous single `rotate3d(x, y, z, a)`
				// collapsed three independent angles into one rotation about a
				// diagonal axis, so entering X=45 and Y=30 rotated about neither.
				$parts = [];
				if ( $x ) {
					$parts[] = sprintf( 'rotateX(%sdeg)', self::n( $x ) );
				}
				if ( $y ) {
					$parts[] = sprintf( 'rotateY(%sdeg)', self::n( $y ) );
				}
				if ( $z ) {
					$parts[] = sprintf( 'rotateZ(%sdeg)', self::n( $z ) );
				}
				return implode( ' ', $parts );

			case 'skew':
				return sprintf( 'skew(%sdeg, %sdeg)', self::n( $x ), self::n( $y ) );

			case 'flip':
				$axis = ! empty( $e['axis'] ) ? $e['axis'] : 'x';
				if ( 'xy' === $axis ) {
					return 'scaleX(-1) scaleY(-1)';
				}
				return 'y' === $axis ? 'scaleY(-1)' : 'scaleX(-1)';

			default:
				// Z stays a px length — percentages are invalid on translateZ.
				$unit = ! empty( $e['unit'] ) ? $e['unit'] : 'px';
				return sprintf(
					'translate3d(%s%s, %s%s, %spx)',
					self::n( $x ),
					$unit,
					self::n( $y ),
					$unit,
					self::n( $z )
				);
		}//end switch
	}

	/**
	 * One transition entry -> its CSS fragment.
	 *
	 * @param array $e Transition entry.
	 * @return string CSS.
	 */
	private static function transition_css( $e ) {
		$parts = [
			! empty( $e['property'] ) ? $e['property'] : 'all',
			self::n( self::num( $e['duration'] ?? 200, 200 ) ) . 'ms',
			! empty( $e['easing'] ) ? $e['easing'] : 'ease',
		];
		$delay = self::num( $e['delay'] ?? 0 );
		if ( $delay ) {
			$parts[] = self::n( $delay ) . 'ms';
		}
		return implode( ' ', $parts );
	}

	/**
	 * One filter entry -> its CSS function.
	 *
	 * @param array $e Filter entry.
	 * @return string CSS.
	 */
	private static function filter_css( $e ) {
		$type = isset( $e['type'], self::FILTERS[ $e['type'] ] ) ? $e['type'] : 'blur';
		$meta = self::FILTERS[ $type ];
		return sprintf(
			'%s(%s%s)',
			$type,
			self::n( self::num( $e['value'] ?? $meta['def'], $meta['def'] ) ),
			$meta['unit']
		);
	}

	/**
	 * One entry -> its CSS fragment.
	 *
	 * @param string $prop  Effect prop name.
	 * @param mixed  $entry Stored entry.
	 * @return string CSS.
	 */
	private static function entry_css( $prop, $entry ) {
		if ( ! is_array( $entry ) ) {
			return '';
		}
		switch ( $prop ) {
			case 'boxShadow':
				return self::shadow_css( $entry );
			case 'textShadow':
				return self::text_shadow_css( $entry );
			case 'transform':
				return self::transform_css( $entry );
			case 'transition':
				return self::transition_css( $entry );
			case 'filter':
			case 'backdropFilter':
				return self::filter_css( $entry );
		}
		return '';
	}

	/**
	 * A list of entries -> one CSS value, or '' when there is nothing to emit.
	 *
	 * @param string $prop    Effect prop name.
	 * @param mixed  $entries Stored entries.
	 * @return string The CSS value.
	 */
	public static function to_css( $prop, $entries ) {
		if ( ! self::is_effect( $prop ) ) {
			return '';
		}

		// These props used to hold a raw CSS string. Pass one through rather
		// than dropping it silently, so anything authored before the repeater
		// still renders.
		if ( is_string( $entries ) ) {
			return trim( $entries );
		}

		if ( ! is_array( $entries ) || empty( $entries ) ) {
			return '';
		}

		$out = [];
		foreach ( $entries as $entry ) {
			if ( is_array( $entry ) && ! empty( $entry['disabled'] ) ) {
				continue;
			}
			$css = self::entry_css( $prop, $entry );
			if ( '' !== $css ) {
				$out[] = $css;
			}
		}

		return empty( $out ) ? '' : implode( self::JOIN[ $prop ], $out );
	}
}
