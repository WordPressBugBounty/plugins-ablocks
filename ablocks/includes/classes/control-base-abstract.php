<?php
namespace ABlocks\Classes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ABlocks\Helper;

abstract class ControlBaseAbstract {
	abstract public static function get_attribute_default_value( $is_responsive = false);
	abstract public static function get_attribute( $attributeName, $is_responsive = false);
	abstract public static function get_css( $attribute_value, $property = '', $device = '');
	public static function has_value( $value ) {
		return isset( $value ) && ! empty( $value );
	}

	/**
	 * Read one device-suffixed member of a control's value.
	 *
	 * A control's defaults only declare the built-in suffixes ('', 'Tablet',
	 * 'Mobile'), so any other device — a user-registered custom breakpoint, or
	 * the value-less probe suffix CssGenerator uses to subtract a baseline —
	 * has no key at all. Reading it directly emits "Undefined array key" under
	 * WP_DEBUG on every page. Missing reads as unset, which is what the callers
	 * already treated the resulting null as.
	 *
	 * @param array  $value   The control's parsed value.
	 * @param string $base    Member name without the device suffix.
	 * @param string $device  Device suffix ('' | 'Tablet' | 'Mobile' | 'Bp…').
	 * @param mixed  $default Returned when the key is absent.
	 * @return mixed
	 */
	public static function device_member( $value, $base, $device = '', $default = '' ) {
		$key = $base . $device;
		return array_key_exists( $key, (array) $value ) ? $value[ $key ] : $default;
	}

	/**
	 * Cached responsive_defaults() results, keyed by control class.
	 *
	 * @var array
	 */
	private static $responsive_defaults_cache = [];

	/**
	 * The control's responsive defaults, extended to cover every device suffix a
	 * compiler can ask for.
	 *
	 * Controls declare defaults for the built-in suffixes only ('', 'Tablet',
	 * 'Mobile'), but CssGenerator also calls get_css() with each user-registered
	 * custom-breakpoint suffix and with a value-less probe suffix. Those keys
	 * exist nowhere, so every `$value[ 'top' . $device ]` in a control emitted an
	 * "Undefined array key" notice. Mirroring each responsive member onto the
	 * extra suffixes — defaulted to the desktop value, i.e. "nothing overridden
	 * here" — makes those reads defined without changing a single output: a
	 * device with no stored value already compiled as unset.
	 *
	 * Use in place of `get_attribute_default_value( true )` when parsing a value
	 * for get_css().
	 *
	 * @return array Defaults covering '', 'Tablet', 'Mobile' and every extra suffix.
	 */
	public static function responsive_defaults() {
		$cache_key = static::class;
		if ( isset( self::$responsive_defaults_cache[ $cache_key ] ) ) {
			return self::$responsive_defaults_cache[ $cache_key ];
		}

		$defaults = (array) static::get_attribute_default_value( true );

		$suffixes = [ '__ablocksphantom__' ];
		foreach ( Helper::get_responsive_devices() as $device ) {
			$suffix = isset( $device['suffix'] ) ? $device['suffix'] : '';
			if ( '' === $suffix || 'Tablet' === $suffix || 'Mobile' === $suffix ) {
				continue;
			}
			$suffixes[] = $suffix;
		}

		foreach ( array_keys( $defaults ) as $key ) {
			if ( 'Tablet' !== substr( $key, -6 ) ) {
				continue;
			}
			$base = substr( $key, 0, -6 );
			foreach ( $suffixes as $suffix ) {
				if ( array_key_exists( $base . $suffix, $defaults ) ) {
					continue;
				}
				// Copy the TABLET default, not the desktop one. A responsive
				// suffix's default means "nothing overridden at this device",
				// which is what Tablet/Mobile already encode; the desktop entry
				// is the base value and can be a real setting (Alignment's is
				// the literal 'default'), which would then be emitted as a CSS
				// declaration for every custom breakpoint.
				$defaults[ $base . $suffix ] = $defaults[ $key ];
			}
		}

		self::$responsive_defaults_cache[ $cache_key ] = $defaults;
		return $defaults;
	}
	public static function get_unit( $attribute_value, $device = '' ) {
		return Helper::get_responsive_value(
			$attribute_value,
			'unit',
			$device,
			[]
		);
	}
}
