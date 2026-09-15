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
		if ( ! Helper::is_gating_active() ) {
			return;
		}
		// Two independent reasons to buffer. A site can hold scripts back and
		// leave its videos alone, or the reverse, so neither switch may speak
		// for the other.
		if ( ! Helper::get( 'buffer_gating', true ) && ! Helper::get( 'embed_gating', true ) ) {
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
		if ( false === stripos( $html, '<script' )
			&& false === stripos( $html, '<iframe' )
			&& false === stripos( $html, '<img' ) ) {
			return $html;
		}
		if ( ! $this->is_html_response() ) {
			return $html;
		}

		$dry_run = (bool) Helper::get( 'dry_run', false );

		if ( Helper::get( 'buffer_gating', true ) ) {
			$filtered = preg_replace_callback(
				'#<script\b([^>]*)>(.*?)</script\s*>#is',
				function ( $match ) use ( $dry_run ) {
					return $this->process( $match, $dry_run );
				},
				$html
			);

			// A backtrack limit or a catastrophic pattern returns null.
			// Serving the original page ungated is bad; serving an empty page
			// is worse.
			$html = null === $filtered ? $html : $filtered;
		}

		if ( Helper::get( 'embed_gating', true ) ) {
			$html = $this->gate_elements( $html, $dry_run );
		}

		return $html;
	}

	/**
	 * The iframe and pixel pass.
	 *
	 * Runs on everything *between* the script elements rather than on the whole
	 * document. An inline script is perfectly entitled to contain the text
	 * `<img src="…facebook.com/tr…">` — in a template string, in a JSON blob,
	 * in an example — and rewriting it there would not gate a request, it would
	 * corrupt the script. Splitting on script blocks and skipping the captured
	 * halves costs one more pass and removes the whole class of problem.
	 *
	 * @param string $html    The document.
	 * @param bool   $dry_run Report instead of rewrite.
	 * @return string
	 */
	private function gate_elements( $html, $dry_run ) {
		$has_iframe = false !== stripos( $html, '<iframe' );
		$has_img    = false !== stripos( $html, '<img' );

		if ( ! $has_iframe && ! $has_img ) {
			return $html;
		}

		$parts = preg_split(
			'#(<script\b[^>]*>.*?</script\s*>)#is',
			$html,
			-1,
			PREG_SPLIT_DELIM_CAPTURE
		);

		if ( ! is_array( $parts ) ) {
			return $html;
		}

		foreach ( $parts as $index => $part ) {
			// Odd indices are the captured script blocks themselves.
			if ( 1 === $index % 2 || '' === $part ) {
				continue;
			}

			if ( $has_iframe ) {
				$done = preg_replace_callback(
					'#<iframe\b([^>]*)>(.*?)</iframe\s*>#is',
					function ( $match ) use ( $dry_run ) {
						return $this->process_embed( $match, $dry_run );
					},
					$part
				);
				$part = null === $done ? $part : $done;
			}

			if ( $has_img ) {
				$done = preg_replace_callback(
					'#<img\b([^>]*?)/?>#is',
					function ( $match ) use ( $dry_run ) {
						return $this->process_pixel( $match, $dry_run );
					},
					$part
				);
				$part = null === $done ? $part : $done;
			}

			$parts[ $index ] = $part;
		}

		return implode( '', $parts );
	}

	/**
	 * One iframe: leave it, or stand a consent card where it was.
	 *
	 * @param array $match   Regex match: 0 whole, 1 attributes, 2 contents.
	 * @param bool  $dry_run Report instead of rewrite.
	 * @return string
	 */
	private function process_embed( $match, $dry_run ) {
		$whole = $match[0];
		$attrs = $match[1];

		if ( false !== stripos( $attrs, 'data-ablocks-consent' ) ) {
			return $whole;
		}

		$src = $this->attribute( $attrs, 'src' );
		if ( '' === (string) $src ) {
			return $whole;
		}

		$rule = Embeds::match( $src, 'embed' );

		if ( ! $rule ) {
			// An unrecognised third-party iframe is the same kind of gap as an
			// unrecognised third-party script, and worth the same report.
			if ( Gating::is_third_party( $src ) ) {
				Report::add( $src, '', 'iframe' );
			}
			return $whole;
		}

		if ( $dry_run ) {
			Report::add( $src, $rule['category'], 'iframe' );
			return $whole;
		}

		// An invisible beacon is stripped the way a pixel is. It occupies no
		// space, so there is no gap to explain and nothing to offer to load.
		if ( ! empty( $rule['beacon'] ) ) {
			return $this->strip_source( $whole, 'iframe', $rule['category'] );
		}

		return Embeds::placeholder( $whole, $rule );
	}

	/**
	 * One image: leave it, or take its source away.
	 *
	 * No placeholder and no announcement. A tracking pixel is not content the
	 * visitor is missing, and drawing a consent card where a 1×1 beacon used to
	 * be would invent a loss to apologise for.
	 *
	 * @param array $match   Regex match: 0 whole, 1 attributes.
	 * @param bool  $dry_run Report instead of rewrite.
	 * @return string
	 */
	private function process_pixel( $match, $dry_run ) {
		$whole = $match[0];
		$attrs = $match[1];

		if ( false !== stripos( $attrs, 'data-ablocks-consent' ) ) {
			return $whole;
		}

		$src = $this->attribute( $attrs, 'src' );
		if ( '' === (string) $src ) {
			return $whole;
		}

		$rule = Embeds::match( $src, 'pixel' );
		if ( ! $rule ) {
			return $whole;
		}

		if ( $dry_run ) {
			Report::add( $src, $rule['category'], 'pixel' );
			return $whole;
		}

		return $this->strip_source( $whole, 'img', $rule['category'] );
	}

	/**
	 * Move an element's `src` aside and label it with its category.
	 *
	 * Renaming the attribute is the whole mechanism: a browser does not fetch
	 * `data-ablocks-src`, and the element stays exactly where the author put
	 * it, keeping whatever size and styling it had.
	 *
	 * @param string $tag      The whole element.
	 * @param string $name     Tag name, `img` or `iframe`.
	 * @param string $category Category slug.
	 * @return string
	 */
	private function strip_source( $tag, $name, $category ) {
		$rewritten = preg_replace( '/\ssrc=/i', ' data-ablocks-src=', $tag, 1 );

		// `\b`, not `\s`: the same trap that made `ScriptGate` silently skip a
		// `<script>` whose only attribute was its type.
		return preg_replace(
			'/^<' . $name . '\b/i',
			sprintf( '<%s data-ablocks-consent="%s" ', $name, esc_attr( $category ) ),
			$rewritten,
			1
		);
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
