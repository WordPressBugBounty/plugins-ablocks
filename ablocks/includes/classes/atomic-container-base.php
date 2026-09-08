<?php
namespace ABlocks\Classes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Shared base for the atomic CONTAINER blocks — div, flex and grid.
 *
 * Everything about style compilation still comes from AtomicBlockBase. What
 * this layer adds is one front-end-only rewrite: a container carrying a Link
 * URL is saved as an `<a>` element (see makeContainerSave in
 * atomic-shared/container.js), and an `<a>` may not contain another `<a>`.
 *
 * That is not a validation nicety, it is a parser rule with teeth. Feed a
 * browser `<a class="…flex container"><div>…</div><div><a href>…</a></div>…</a>`
 * — a linked container holding a child that is itself linked, an image with a
 * link being the everyday way to get one — and the HTML5 adoption agency
 * algorithm rebuilds the tree: the outer anchor is closed at the nested one, an
 * EMPTY clone of it is left behind inside the child, and every sibling after
 * that point is lifted out of the container altogether. Parsed with a real
 * HTML5 parser, four children of a flex row came back as
 *
 *     <a class="…"><div>1</div><div>2</div></a>
 *     <div><a class="…"></a><a href>3</a></div>
 *     <div>4</div>
 *
 * which is exactly the reported break: the first children still in their row
 * inside the bordered box, an empty bordered box after it (that clone), and the
 * remaining children spilled down the page at their natural size because they
 * are no longer in the flex container at all. The editor never showed it — it
 * renders the chosen htmlTag and no anchor whatsoever — so the two disagreed
 * only once published.
 *
 * No stylesheet can reach this: by the time CSS runs, the DOM is already the
 * wrong shape. So the anchor stops being the box. The container is rendered
 * back as its own tag, carrying every class, id, data-attribute and therefore
 * every compiled style and breakpoint it had, and the link becomes a
 * transparent overlay child stretched across it (`.ablocks-atomic-link`, sized
 * by the rules in each container's style.css). The whole box stays clickable,
 * links inside it stay clickable and are no longer torn out of the layout, and
 * the markup the browser is handed is valid, so the tree it builds is the tree
 * the editor drew.
 *
 * Done here at render rather than in save() on purpose: the saved markup of
 * every page already published carries the nested anchors, and a save-side fix
 * — even with a deprecation to keep those posts valid in the editor — would
 * still serve that stored markup on the front end until someone opened and
 * re-saved each one. Rewriting on output fixes the pages that are already
 * broken, today, and leaves the editor and the stored content untouched.
 */
abstract class AtomicContainerBase extends AtomicBlockBase {

	/**
	 * The tags the HTML-tag control offers — see TAGS in
	 * atomic-shared/container.js. Anything else falls back to a div rather
	 * than being echoed into the page as a tag name.
	 */
	protected static $allowed_tags = [ 'div', 'section', 'article', 'aside', 'header', 'footer', 'main', 'nav' ];

	public function render_callback( $attributes, $content, $block_instance ) {
		// Before the base class wraps or filters anything, so it only ever sees
		// the shape it would have seen for an unlinked container.
		$content = self::unnest_container_link( $content, $attributes );
		return parent::render_callback( $attributes, $content, $block_instance );
	}

	/**
	 * Turn `<a class="container" href>…</a>` back into `<tag class="container">
	 * <a class="ablocks-atomic-link" href></a>…</tag>`.
	 *
	 * Deliberately conservative: it only acts on markup shaped the way
	 * makeContainerSave writes it — a linked container whose first tag is the
	 * anchor and whose last tag closes it — and returns the content untouched
	 * on anything else, so a container that has already been rewritten, or one
	 * whose markup came from somewhere unexpected, is left exactly as it is.
	 *
	 * @param string $content    The block's saved markup, inner blocks rendered.
	 * @param array  $attributes The block's attributes.
	 * @return string The rewritten markup, or $content unchanged.
	 */
	public static function unnest_container_link( $content, $attributes ) {
		if ( ! is_string( $content ) || '' === $content ) {
			return $content;
		}
		if ( empty( $attributes['link']['url'] ) ) {
			return $content;
		}
		// The container's own anchor opens the markup and closes it; anything
		// else is not the shape this rewrite understands.
		if ( ! preg_match( '#^(\s*)<a\s([^>]*)>#i', $content, $open ) ) {
			return $content;
		}
		if ( ! preg_match( '#</a>(\s*)$#i', $content, $close ) ) {
			return $content;
		}

		$element_attrs = $open[2];

		// The link attributes move to the overlay verbatim — they were escaped
		// when the block was saved, and re-encoding them here would be one more
		// place for that to go wrong.
		$link_attrs = '';
		foreach ( [ 'href', 'target', 'rel' ] as $name ) {
			if ( preg_match( '#(^|\s)' . $name . '="([^"]*)"#i', $element_attrs, $found ) ) {
				$link_attrs   .= ' ' . $name . '="' . $found[2] . '"';
				$element_attrs = str_replace( $found[0], $found[1], $element_attrs );
			}
		}
		if ( '' === $link_attrs ) {
			return $content;
		}

		$tag = isset( $attributes['htmlTag'] ) ? strtolower( (string) $attributes['htmlTag'] ) : 'div';
		if ( ! in_array( $tag, self::$allowed_tags, true ) ) {
			$tag = 'div';
		}

		// The hook the overlay is positioned against.
		if ( preg_match( '#(^|\s)class="([^"]*)"#i', $element_attrs, $found ) ) {
			$element_attrs = str_replace(
				$found[0],
				$found[1] . 'class="' . $found[2] . ' ablocks-has-link"',
				$element_attrs
			);
		} else {
			$element_attrs = 'class="ablocks-has-link" ' . $element_attrs;
		}
		// One space where the link attributes were lifted out.
		$element_attrs = trim( preg_replace( '/\s{2,}/', ' ', $element_attrs ) );

		$inner = substr(
			$content,
			strlen( $open[0] ),
			strlen( $content ) - strlen( $open[0] ) - strlen( $close[0] )
		);

		return $open[1]
			. '<' . $tag . ( '' !== $element_attrs ? ' ' . $element_attrs : '' ) . '>'
			. '<a class="ablocks-atomic-link"' . $link_attrs . self::overlay_label( $inner ) . '></a>'
			. $inner
			. '</' . $tag . '>'
			. $close[1];
	}

	/**
	 * An accessible name for the overlay.
	 *
	 * The anchor used to BE the container, so it was named by everything inside
	 * it; emptied out, it would announce as a bare URL and read as an unlabelled
	 * link. This gives back roughly what a screen reader heard before — the
	 * container's own text, or failing that the first alt text in it, which for
	 * the common linked-image card is the only text there is. Capped, because
	 * the name is a label and not the content.
	 *
	 * @param string $inner The container's inner markup.
	 * @return string An ` aria-label="…"` attribute, or an empty string.
	 */
	private static function overlay_label( $inner ) {
		$label = trim( preg_replace( '/\s+/', ' ', wp_strip_all_tags( $inner ) ) );
		if ( '' === $label && preg_match( '#\salt="([^"]+)"#i', $inner, $found ) ) {
			$label = trim( $found[1] );
		}
		if ( '' === $label ) {
			return '';
		}
		return ' aria-label="' . esc_attr( mb_substr( $label, 0, 100 ) ) . '"';
	}
}
