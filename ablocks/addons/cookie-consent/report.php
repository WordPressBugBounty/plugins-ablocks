<?php
namespace ABlocksCookieConsent;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * What the gating layers saw on the last pages that were rendered.
 *
 * Two audiences. In dry run it answers "what would you have blocked?" before
 * anything is blocked. With gating live it answers the harder question — "what
 * third-party script is running that you have no rule for?" — which is the one
 * that decides whether the configuration is actually complete.
 *
 * Kept in a transient rather than a table: it is diagnostic, it is
 * regenerable by loading a page, and it should expire on its own.
 */
class Report {

	const KEY   = 'ablocks_cc_scan';
	const LIMIT = 120;

	/**
	 * Entries collected during this request, flushed once on shutdown so a
	 * page with forty scripts writes the option once and not forty times.
	 *
	 * @var array
	 */
	private static $pending = [];

	/**
	 * @param string $subject  Script URL or handle.
	 * @param string $category Matched category, '' when unclassified.
	 * @param string $source   'enqueued' or 'inline'.
	 */
	public static function add( $subject, $category, $source ) {
		$subject = (string) $subject;
		if ( '' === $subject ) {
			return;
		}
		// Inline snippets can be kilobytes; the report only needs a fingerprint.
		if ( 'inline' === $source && strlen( $subject ) > 120 ) {
			$subject = trim( preg_replace( '/\s+/', ' ', substr( $subject, 0, 120 ) ) ) . '…';
		}

		$key = md5( $source . '|' . $subject );
		if ( isset( self::$pending[ $key ] ) ) {
			return;
		}

		self::$pending[ $key ] = [
			'subject'  => $subject,
			'category' => (string) $category,
			'source'   => $source,
			'url'      => self::current_url(),
			'seen'     => time(),
		];

		if ( 1 === count( self::$pending ) ) {
			add_action( 'shutdown', [ __CLASS__, 'flush' ], 99 );
		}
	}

	public static function flush() {
		if ( empty( self::$pending ) ) {
			return;
		}
		$stored = get_transient( self::KEY );
		$stored = is_array( $stored ) ? $stored : [];
		$stored = array_merge( $stored, self::$pending );

		// Newest wins when the report is full — an old entry has already been
		// read, and the entries that matter are from the page just loaded.
		if ( count( $stored ) > self::LIMIT ) {
			uasort(
				$stored,
				function ( $a, $b ) {
					return $b['seen'] <=> $a['seen'];
				}
			);
			$stored = array_slice( $stored, 0, self::LIMIT, true );
		}

		set_transient( self::KEY, $stored, DAY_IN_SECONDS );
		self::$pending = [];
	}

	/**
	 * @return array Entries, newest first.
	 */
	public static function get() {
		$stored = get_transient( self::KEY );
		$stored = is_array( $stored ) ? $stored : [];
		uasort(
			$stored,
			function ( $a, $b ) {
				return $b['seen'] <=> $a['seen'];
			}
		);
		return array_values( $stored );
	}

	public static function clear() {
		delete_transient( self::KEY );
	}

	private static function current_url() {
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		$path = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
		return $path ? substr( $path, 0, 190 ) : '';
	}
}
