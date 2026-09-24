<?php
namespace ABlocks\Classes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The bucket namespace for the atomic `styles` attribute.
 *
 * Mirror of `src/blocks/atomic-shared/buckets.js`. Where StylesSchema answers
 * "which props exist and what CSS do they become", this answers "where is a
 * value stored and in what order does it emit".
 *
 * Shape (schema v3):
 *
 *   [
 *     'v'        => 3,
 *     ''         => [ …props ],                              // desktop, normal
 *     ':hover'   => [ …props ],                              // desktop, hover
 *     '@tablet'  => [ '' => [ …props ], ':hover' => [ … ] ], // tablet, per state
 *   ]
 *
 * Two levels, never mixed: the top level is keyed by state OR breakpoint, and a
 * breakpoint's value is keyed by state only. Props never carry a device suffix —
 * the bucket key is the device — so registering a new breakpoint adds exactly
 * one key rather than one key per prop.
 */
class StyleBuckets {

	/** Bumped only when the stored shape changes incompatibly. */
	const SCHEMA_VERSION = 3;

	/** The key holding the schema version (not a bucket). */
	const VERSION_KEY = 'v';

	/**
	 * Interaction states, in emission order. The stored key IS the CSS
	 * pseudo-selector, so no translation table is needed at compile time.
	 * `:focus-visible` follows `:focus` so it wins where both are set.
	 */
	public static function states() {
		return [
			[ 'key' => '', 'label' => 'Normal' ],
			[ 'key' => ':hover', 'label' => 'Hover' ],
			[ 'key' => ':focus', 'label' => 'Focus' ],
			[ 'key' => ':focus-visible', 'label' => 'Focus (keyboard)' ],
			[ 'key' => ':active', 'label' => 'Active' ],
		];
	}

	/** Just the state keys, in emission order. */
	public static function state_keys() {
		return array_column( self::states(), 'key' );
	}

	public static function is_state_key( $key ) {
		return '' === $key || 0 === strpos( (string) $key, ':' );
	}

	public static function is_breakpoint_key( $key ) {
		return 0 === strpos( (string) $key, '@' );
	}

	/**
	 * Reserved for class-driven states (`-current`, `-open`). Parsed and
	 * preserved so they can be added later without a storage change; the
	 * compilers skip them.
	 *
	 * @param string $key Bucket key.
	 * @return bool Whether the key is a reserved class-state bucket.
	 */
	public static function is_reserved_key( $key ) {
		return 0 === strpos( (string) $key, '-' );
	}

	/**
	 * The bucket key for a responsive device entry. The base device has no key
	 * of its own — its states live at the top level.
	 *
	 * @param array $device A device entry from the responsive device list.
	 * @return string '' for the base device, else '@<lcfirst id>'.
	 */
	public static function device_bucket_key( $device ) {
		$device = (array) $device;
		if ( empty( $device['min'] ) && empty( $device['max'] ) ) {
			return '';
		}
		return '@' . lcfirst( (string) ( isset( $device['id'] ) ? $device['id'] : '' ) );
	}

	/** A styles array at the current schema version. */
	public static function empty_styles() {
		return [ self::VERSION_KEY => self::SCHEMA_VERSION ];
	}

	/**
	 * Whether a stored styles array is readable by this compiler. An array
	 * without the marker is treated as unreadable rather than interpreted, so a
	 * pre-v3 shape can never be half-compiled into wrong CSS.
	 *
	 * @param mixed $styles Stored styles value.
	 * @return bool Whether it carries the current schema version.
	 */
	public static function has_schema_version( $styles ) {
		return is_array( $styles )
			&& isset( $styles[ self::VERSION_KEY ] )
			&& self::SCHEMA_VERSION === (int) $styles[ self::VERSION_KEY ];
	}

	/**
	 * Every non-empty (device, state, props) triple in canonical emission
	 * order: devices widest-first (the order Helper::get_responsive_devices()
	 * already returns), and within a device, states in states() order.
	 *
	 * Reserved `-` buckets are skipped — they are storage-only this release.
	 *
	 * @param array $styles  Stored styles array.
	 * @param array $devices Ordered responsive device list.
	 * @return array[] Entries of [ 'device' => …, 'state' => …, 'props' => … ].
	 */
	public static function ordered_buckets( $styles, $devices ) {
		$out = [];
		if ( ! self::has_schema_version( $styles ) ) {
			return $out;
		}

		foreach ( (array) $devices as $device ) {
			$key   = self::device_bucket_key( $device );
			$level = ( '' === $key )
				? $styles
				: ( isset( $styles[ $key ] ) ? $styles[ $key ] : null );

			if ( ! is_array( $level ) ) {
				continue;
			}

			foreach ( self::state_keys() as $state ) {
				$props = isset( $level[ $state ] ) ? $level[ $state ] : null;
				if ( is_array( $props ) && ! empty( $props ) ) {
					$out[] = [
						'device' => $device,
						'state'  => $state,
						'props'  => $props,
					];
				}
			}
		}//end foreach

		return $out;
	}

	/**
	 * Read one bucket's own props (no inheritance) — what this device/state
	 * actually overrides, as opposed to what it renders as.
	 *
	 * @param array  $styles Stored styles array.
	 * @param string $bucket Breakpoint bucket key ('' for base).
	 * @param string $state  State key.
	 * @return array The bucket's props, or an empty array.
	 */
	public static function read_bucket( $styles, $bucket, $state ) {
		if ( ! is_array( $styles ) ) {
			return [];
		}
		$level = ( '' === $bucket )
			? $styles
			: ( isset( $styles[ $bucket ] ) ? $styles[ $bucket ] : null );

		if ( ! is_array( $level ) || ! isset( $level[ $state ] ) || ! is_array( $level[ $state ] ) ) {
			return [];
		}
		return $level[ $state ];
	}

	/**
	 * What a device/state inherits for one prop: the nearest value up the
	 * cascade, not counting the bucket itself, stepping over value-less
	 * remnants (a Range cleared down to its unit). Mirror of the JS
	 * `inheritedProp()`.
	 *
	 * @param array  $styles  Stored styles array.
	 * @param string $bucket  Breakpoint bucket key ('' for base).
	 * @param string $state   State key.
	 * @param string $prop    Prop name.
	 * @param array  $devices Devices applying here, widest-first.
	 * @return mixed The inherited value, or null.
	 */
	public static function inherited_prop( $styles, $bucket, $state, $prop, $devices ) {
		foreach ( self::resolve_buckets( $bucket, $devices ) as $b ) {
			foreach ( '' === $state ? [ '' ] : [ $state, '' ] as $s ) {
				if ( $b === $bucket && $s === $state ) {
					continue;
				}
				$props = self::read_bucket( $styles, $b, $s );
				$value = isset( $props[ $prop ] ) ? $props[ $prop ] : null;
				if ( self::has_value( $value ) ) {
					return $value;
				}
			}
		}
		return null;
	}

	/**
	 * The buckets to consult for one device, nearest first. Mirror of the JS
	 * `resolveBuckets()`.
	 *
	 * @param string $bucket  The bucket being resolved for.
	 * @param array  $devices Devices applying at that one, widest-first.
	 * @return string[] Bucket keys, nearest first.
	 */
	private static function resolve_buckets( $bucket, $devices ) {
		if ( empty( $devices ) ) {
			return '' === $bucket ? [ '' ] : [ $bucket, '' ];
		}
		$out = [ $bucket ];
		foreach ( array_reverse( $devices ) as $device ) {
			$key = self::device_bucket_key( $device );
			if ( ! in_array( $key, $out, true ) ) {
				$out[] = $key;
			}
		}
		if ( ! in_array( '', $out, true ) ) {
			$out[] = '';
		}
		return $out;
	}

	/**
	 * Whether a stored value counts as set. Mirror of the JS `hasValue()`:
	 * unit / isLinked bookkeeping members are not values.
	 *
	 * @param mixed $value Stored value.
	 * @return bool Whether anything was actually entered.
	 */
	private static function has_value( $value ) {
		if ( null === $value || '' === $value || false === $value ) {
			return false;
		}
		if ( is_array( $value ) ) {
			if ( array_keys( $value ) === range( 0, count( $value ) - 1 ) ) {
				return count( $value ) > 0;
			}
			foreach ( $value as $key => $member ) {
				$key = (string) $key;
				if ( 'isLinked' === $key || 'unit' === $key || 'Unit' === substr( $key, -4 ) ) {
					continue;
				}
				if ( self::has_value( $member ) ) {
					return true;
				}
			}
			return false;
		}
		return true;
	}
}
