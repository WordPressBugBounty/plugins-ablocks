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
}
