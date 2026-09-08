<?php
namespace ABlocksCookieConsent;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ABlocks\Performance\ScriptGate;

/**
 * Layer one: enqueued scripts.
 *
 * Anything that went through `wp_enqueue_script` passes through
 * `script_loader_tag`, which is the cheapest and safest place to gate — the
 * handle and the source are both known, and nothing has been rendered yet.
 *
 * It is also, on a typical site, the minority of what needs gating. GA4, GTM
 * and the Meta Pixel are usually pasted into a header hook rather than
 * enqueued; those are `Buffer`'s problem.
 *
 * This class also owns the rule matching, because both layers ask the same
 * question of different inputs.
 */
class Gating {

	/**
	 * Category matched by the most recent `in_scope` call, so the attribute
	 * callback does not have to run the rules a second time.
	 *
	 * @var string
	 */
	private $matched = '';

	public static function init() {
		if ( ! Helper::is_gating_active() ) {
			return;
		}

		$self = new self();

		// Dry run walks the same rules and records what it would have done,
		// without touching a single tag.
		if ( Helper::get( 'dry_run', false ) ) {
			add_filter( 'script_loader_tag', [ $self, 'observe_tag' ], 20, 3 );
			return;
		}

		( new ScriptGate(
			'text/plain',
			[ $self, 'in_scope' ],
			[ $self, 'gate_attributes' ]
		) )->hook( 20 );
	}

	/**
	 * @param string $handle Script handle.
	 * @param string $src    Script source.
	 * @param string $tag    Rendered tag.
	 * @return bool
	 */
	public function in_scope( $handle, $src, $tag = '' ) {
		if ( $tag && ScriptGate::is_gated( $tag ) ) {
			return false;
		}
		$this->matched = self::match_src( $src, $handle );
		return '' !== $this->matched;
	}

	/**
	 * @return array
	 */
	public function gate_attributes() {
		return [ 'data-ablocks-consent' => $this->matched ];
	}

	/**
	 * Dry-run counterpart: record the verdict, return the tag untouched.
	 *
	 * @param string $tag    Rendered tag.
	 * @param string $handle Script handle.
	 * @param string $src    Script source.
	 * @return string
	 */
	public function observe_tag( $tag, $handle, $src ) {
		$category = self::match_src( $src, $handle );
		if ( $category ) {
			Report::add( $src ? $src : $handle, $category, 'enqueued' );
		} elseif ( self::is_third_party( $src ) ) {
			Report::add( $src, '', 'enqueued' );
		}
		return $tag;
	}

	/**
	 * Which category, if any, a script source or handle belongs to.
	 *
	 * @param string $src    Script URL.
	 * @param string $handle Script handle.
	 * @return string Category slug, or '' when nothing matched.
	 */
	public static function match_src( $src, $handle = '' ) {
		$src = (string) $src;
		if ( '' === $src && '' === (string) $handle ) {
			return '';
		}

		foreach ( Helper::active_rules() as $rule ) {
			if ( ! empty( $rule['handles'] ) ) {
				$handles = array_map( 'trim', explode( ',', (string) $rule['handles'] ) );
				if ( in_array( (string) $handle, $handles, true ) ) {
					return $rule['category'];
				}
			}
			if ( ! empty( $rule['src'] ) && '' !== $src && self::matches( $rule['src'], $src ) ) {
				return $rule['category'];
			}
		}

		return '';
	}

	/**
	 * Which category, if any, an inline script's contents belong to.
	 *
	 * @param string $code Inline script contents.
	 * @return string Category slug, or ''.
	 */
	public static function match_inline( $code ) {
		$code = (string) $code;
		if ( '' === trim( $code ) ) {
			return '';
		}

		foreach ( Helper::active_rules() as $rule ) {
			if ( ! empty( $rule['inline'] ) && self::matches( $rule['inline'], $code ) ) {
				return $rule['category'];
			}
		}

		return '';
	}

	/**
	 * Run one rule pattern against a subject.
	 *
	 * Patterns come from the settings screen, so a malformed one is a user
	 * typo rather than a bug — it must not raise a warning on every page view,
	 * and it must never match by accident.
	 *
	 * @param string $pattern Regular-expression body.
	 * @param string $subject Text to test.
	 * @return bool
	 */
	private static function matches( $pattern, $subject ) {
		$result = @preg_match( '#' . str_replace( '#', '\#', $pattern ) . '#i', $subject ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- a bad pattern is user input, not an error worth surfacing on every request.
		return 1 === $result;
	}

	/**
	 * Whether a URL points somewhere other than this site.
	 *
	 * Used only for the "seen but not classified" report — a third-party script
	 * nobody wrote a rule for is exactly what a site owner needs told about.
	 *
	 * @param string $src Script URL.
	 * @return bool
	 */
	public static function is_third_party( $src ) {
		$src = (string) $src;
		if ( '' === $src || 0 === strpos( $src, '/' ) || 0 === strpos( $src, 'data:' ) ) {
			return false;
		}
		$host = wp_parse_url( $src, PHP_URL_HOST );
		if ( ! $host ) {
			return false;
		}
		$home = wp_parse_url( home_url(), PHP_URL_HOST );
		return strtolower( $host ) !== strtolower( (string) $home );
	}
}
