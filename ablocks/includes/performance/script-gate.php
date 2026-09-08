<?php
namespace ABlocks\Performance;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The shared mechanism for stopping a <script> tag from executing, and letting
 * something on the client release it later.
 *
 * Two features need exactly this. `DelayJs` holds aBlocks' own scripts back
 * until the first user interaction; the cookie-consent addon holds third-party
 * tags back until consent for their category is granted. Both do the same three
 * things — decide whether a tag is in scope, rewrite its type so the browser
 * parses but does not run it, and release it later — so the rewriting lives
 * here once rather than as two independent regexes over every script on the
 * page.
 *
 * The rewrite is deliberately textual. `script_loader_tag` hands over a string,
 * and the consent addon's second layer works on already-rendered markup, so a
 * DOM-based rewrite is not available in either caller.
 */
class ScriptGate {

	/**
	 * Marker type written into the tag, e.g. `ablocks/delayed` or `text/plain`.
	 *
	 * @var string
	 */
	private $type;

	/**
	 * `( $handle, $src, $tag ) => bool` — whether this tag is in scope.
	 *
	 * @var callable
	 */
	private $in_scope;

	/**
	 * `( $handle, $src ) => array` — extra attributes to write onto the tag.
	 *
	 * @var callable|null
	 */
	private $attributes;

	/**
	 * Whether to move `src` out of the way as well as changing the type.
	 *
	 * @var bool
	 */
	private $move_src;

	public function __construct( $type, callable $in_scope, $attributes = null, $move_src = false ) {
		$this->type       = $type;
		$this->in_scope   = $in_scope;
		$this->attributes = $attributes;
		$this->move_src   = (bool) $move_src;
	}

	/**
	 * Start filtering enqueued script tags.
	 *
	 * @param int $priority Filter priority.
	 */
	public function hook( $priority = 20 ) {
		add_filter( 'script_loader_tag', [ $this, 'filter_tag' ], $priority, 3 );
	}

	/**
	 * @param string $tag    Rendered script tag.
	 * @param string $handle Script handle.
	 * @param string $src    Script source.
	 * @return string
	 */
	public function filter_tag( $tag, $handle, $src ) {
		if ( ! call_user_func( $this->in_scope, $handle, $src, $tag ) ) {
			return $tag;
		}
		$attrs = $this->attributes ? (array) call_user_func( $this->attributes, $handle, $src ) : [];
		return self::rewrite_tag( $tag, $this->type, $attrs, $this->move_src );
	}

	/**
	 * Rewrite one script tag so the browser will not execute it.
	 *
	 * A `<script>` whose type is not a JavaScript MIME type is parsed as a data
	 * block: it does not run, and — this is the part that matters for consent —
	 * a `src` on it is never fetched either, so no request reaches the third
	 * party before the visitor has decided.
	 *
	 * @param string $tag      The full `<script …>` opening tag, or a whole tag pair.
	 * @param string $type     Marker type to write.
	 * @param array  $attrs    Extra attributes, name => value.
	 * @param bool   $move_src Whether to rename `src` to `data-ablocks-src`.
	 * @return string
	 */
	public static function rewrite_tag( $tag, $type, array $attrs = [], $move_src = false ) {
		if ( $move_src ) {
			$tag = preg_replace( '/\ssrc=/', ' data-ablocks-src=', $tag, 1 );
		}

		$extra = '';
		foreach ( $attrs as $name => $value ) {
			$extra .= sprintf( '%s="%s" ', esc_attr( $name ), esc_attr( $value ) );
		}

		// An existing type is preserved so the release step can restore it —
		// `type="module"` is not interchangeable with a classic script.
		if ( preg_match( '/\stype=["\']([^"\']*)["\']/i', $tag, $found ) ) {
			$extra .= sprintf( 'data-ablocks-type="%s" ', esc_attr( $found[1] ) );
			$tag    = preg_replace( '/\stype=["\'][^"\']*["\']/i', '', $tag, 1 );
		}

		return preg_replace(
			'/^<script\s/',
			sprintf( '<script type="%s" %s', esc_attr( $type ), $extra ),
			$tag,
			1
		);
	}

	/**
	 * Whether a tag has already been gated by either feature.
	 *
	 * @param string $tag Script tag.
	 * @return bool
	 */
	public static function is_gated( $tag ) {
		return (bool) preg_match( '/\stype=["\'](?:text\/plain|ablocks\/[a-z-]+)["\']/i', $tag );
	}
}
