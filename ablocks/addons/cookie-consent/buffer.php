<?php
namespace ABlocksCookieConsent;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ABlocks\Performance\ScriptGate;

/**
 * Layer two: everything that never went through `wp_enqueue_script`.
 *
 * This is the layer that decides whether the feature works. GTM, GA4 and the
 * Meta Pixel are, on the overwhelming majority of sites, pasted into a header
 * hook or injected by another plugin — none of that passes through
 * `script_loader_tag`, so layer one never sees it.
 *
 * Buffering the whole page is the highest-risk thing this addon does, and the
 * mitigations are deliberate rather than incidental:
 *
 * - it only runs when the addon is on and the mode is opt-in;
 * - it only rewrites a script a rule matched with confidence, and leaves
 *   everything else exactly as it found it;
 * - anything third-party it could not classify is reported rather than guessed
 *   at, so an incomplete configuration is visible instead of silent;
 * - `dry_run` runs the whole pass and rewrites nothing, which is how a site
 *   owner finds out what would break before it breaks.
 */
class Buffer {

	/**
	 * Script types that actually execute. A `application/ld+json` block or an
	 * `text/html` template is not JavaScript and must be left alone — gating a
	 * template would silently change the page.
	 *
	 * @var array
	 */
	private static $executable_types = [
		'',
		'text/javascript',
		'application/javascript',
		'text/ecmascript',
		'application/ecmascript',
		'module',
	];

	public static function init() {
		if ( ! Helper::is_gating_active() || ! Helper::get( 'buffer_gating', true ) ) {
			return;
		}

		$self = new self();
		// Started as early as a theme's output can be, so this buffer is the
		// outermost one and its callback therefore runs on the finished
		// document — after every inner buffer has flushed into it.
		add_action( 'template_redirect', [ $self, 'start' ], -9999 );
	}

	public function start() {
		if ( is_feed() || is_embed() || is_robots() ) {
			return;
		}
		ob_start( [ $this, 'filter' ] );
	}

	/**
	 * @param string $html The rendered document.
	 * @return string
	 */
	public function filter( $html ) {
		if ( ! is_string( $html ) || '' === $html ) {
			return $html;
		}
		// Cheap bail-outs first: this callback runs on every page.
		if ( false === stripos( $html, '<script' ) ) {
			return $html;
		}
		if ( ! $this->is_html_response() ) {
			return $html;
		}

		$dry_run = (bool) Helper::get( 'dry_run', false );

		$filtered = preg_replace_callback(
			'#<script\b([^>]*)>(.*?)</script\s*>#is',
			function ( $match ) use ( $dry_run ) {
				return $this->process( $match, $dry_run );
			},
			$html
		);

		// A backtrack limit or a catastrophic pattern returns null. Serving the
		// original page ungated is bad; serving an empty page is worse.
		return null === $filtered ? $html : $filtered;
	}

	/**
	 * Decide what to do with one script element.
	 *
	 * @param array $match   Regex match: 0 whole, 1 attributes, 2 contents.
	 * @param bool  $dry_run Report instead of rewrite.
	 * @return string
	 */
	private function process( $match, $dry_run ) {
		$whole = $match[0];
		$attrs = $match[1];
		$code  = $match[2];
		$open  = '<script ' . ltrim( $attrs ) . '>';

		if ( ScriptGate::is_gated( $open ) ) {
			return $whole;
		}
		// The consent scripts must never gate themselves.
		if ( false !== stripos( $attrs, 'data-ablocks-consent' ) ) {
			return $whole;
		}
		if ( ! $this->is_executable( $attrs ) ) {
			return $whole;
		}

		$src = $this->attribute( $attrs, 'src' );

		if ( $src ) {
			$category = Gating::match_src( $src );
			$subject  = $src;
			$source   = 'external';
		} else {
			$category = Gating::match_inline( $code );
			$subject  = $code;
			$source   = 'inline';
		}

		if ( '' === $category ) {
			// Not classified. Report a third-party script so the gap is
			// visible; leave a first-party or unrecognised inline script alone
			// and say nothing, because reporting every one is noise.
			if ( $src && Gating::is_third_party( $src ) ) {
				Report::add( $src, '', $source );
			}
			return $whole;
		}

		if ( $dry_run ) {
			Report::add( $subject, $category, $source );
			return $whole;
		}

		return ScriptGate::rewrite_tag( $open, 'text/plain', [ 'data-ablocks-consent' => $category ] )
			. $code
			. '</script>';
	}

	/**
	 * Whether this script would run if left alone.
	 *
	 * @param string $attrs Raw attribute string.
	 * @return bool
	 */
	private function is_executable( $attrs ) {
		$type = strtolower( trim( (string) $this->attribute( $attrs, 'type' ) ) );
		return in_array( $type, self::$executable_types, true );
	}

	/**
	 * Pull one attribute's value out of a raw attribute string.
	 *
	 * @param string $attrs Raw attribute string.
	 * @param string $name  Attribute name.
	 * @return string
	 */
	private function attribute( $attrs, $name ) {
		if ( preg_match( '/\s' . preg_quote( $name, '/' ) . '\s*=\s*("([^"]*)"|\'([^\']*)\'|([^\s>]+))/i', ' ' . $attrs, $found ) ) {
			foreach ( [ 2, 3, 4 ] as $group ) {
				if ( isset( $found[ $group ] ) && '' !== $found[ $group ] ) {
					return $found[ $group ];
				}
			}
		}
		return '';
	}

	/**
	 * Only rewrite documents that are actually HTML. A plugin that hijacks the
	 * request to return XML or a file download still passes through this
	 * buffer, and must come out the other side untouched.
	 *
	 * @return bool
	 */
	private function is_html_response() {
		foreach ( headers_list() as $header ) {
			if ( 0 === stripos( $header, 'content-type:' ) ) {
				return (bool) stripos( $header, 'text/html' );
			}
		}
		// No explicit header means PHP's default, which is text/html.
		return true;
	}
}
