<?php
namespace ABlocks\Classes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The single prop -> CSS descriptor for the atomic style system.
 *
 * Both compilers read this instead of keeping their own prop lists: the PHP
 * frontend compiler (AtomicStyles) and the JS editor compiler (the mirror in
 * src/blocks/atomic-shared/styles-schema.js). Adding a style prop means one
 * entry here and one in the mirror — nothing else enumerates props.
 *
 * The array order IS the canonical emission order. Declarations are emitted in
 * this order on both sides so the two compilers serialise identically, which is
 * what lets a block's style hash be computed in the editor and matched on the
 * front end.
 *
 * Each entry is [ prop, css, kind ] where kind is one of:
 *   typography - the `typography` object, compiled by Controls\Typography
 *   color      - a scalar resolved through Controls\Color (supports globals)
 *   scalar     - a scalar emitted as-is
 *   range      - an aBlocks Range object ({ value, valueUnit }) -> "<value><unit>"
 *   border     - the border group (width/style/colour/radius + its fallbacks)
 *   dimensions - padding / margin, compiled by Controls\Dimensions
 *   effect     - a repeatable list, compiled by StyleEffects
 *   overlay    - background overlay layers, compiled by StyleBackground
 *   clip       - background-clip plus its -webkit- companion
 */
class StylesSchema {

	/**
	 * The border group's members, in the order they are emitted. Declared here
	 * rather than inline in the compiler so the JS mirror can assert the same
	 * set.
	 *
	 * The per-side widths and per-corner radii are optional overrides on top of
	 * the uniform value — a block that sets none of them compiles exactly as it
	 * did before they existed, which matters because the style class is a hash
	 * of compiled output.
	 */
	const BORDER_SIDES = [ 'Top', 'Right', 'Bottom', 'Left' ];

	/** Corner prop suffix -> CSS property, in CSS shorthand order. */
	const BORDER_CORNERS = [
		'TopLeft'     => 'border-top-left-radius',
		'TopRight'    => 'border-top-right-radius',
		'BottomRight' => 'border-bottom-right-radius',
		'BottomLeft'  => 'border-bottom-left-radius',
	];

	const BORDER_MEMBERS = [
		'borderWidth',
		'borderWidthTop',
		'borderWidthRight',
		'borderWidthBottom',
		'borderWidthLeft',
		'borderStyle',
		'borderColor',
		'borderRadius',
		'borderRadiusTopLeft',
		'borderRadiusTopRight',
		'borderRadiusBottomRight',
		'borderRadiusBottomLeft',
	];

	/** Ordered descriptor. */
	public static function props() {
		return [
			[ 'prop' => 'typography', 'css' => '', 'kind' => 'typography' ],

			[ 'prop' => 'textColor', 'css' => 'color', 'kind' => 'color' ],
			[ 'prop' => 'backgroundColor', 'css' => 'background-color', 'kind' => 'color' ],
			[ 'prop' => 'backgroundGradient', 'css' => 'background-image', 'kind' => 'scalar' ],
			// Overlay layers expand to background-image + the four positional
			// props at once, so they emit as a group (like border / dimensions).
			[ 'prop' => 'backgroundOverlay', 'css' => '', 'kind' => 'overlay' ],

			[ 'prop' => 'border', 'css' => '', 'kind' => 'border' ],

			// Size.
			[ 'prop' => 'width', 'css' => 'width', 'kind' => 'range' ],
			[ 'prop' => 'maxWidth', 'css' => 'max-width', 'kind' => 'range' ],
			[ 'prop' => 'minWidth', 'css' => 'min-width', 'kind' => 'range' ],
			[ 'prop' => 'height', 'css' => 'height', 'kind' => 'range' ],
			[ 'prop' => 'minHeight', 'css' => 'min-height', 'kind' => 'range' ],
			[ 'prop' => 'maxHeight', 'css' => 'max-height', 'kind' => 'range' ],
			[ 'prop' => 'aspectRatio', 'css' => 'aspect-ratio', 'kind' => 'scalar' ],

			// Gaps.
			[ 'prop' => 'gap', 'css' => 'gap', 'kind' => 'range' ],
			[ 'prop' => 'rowGap', 'css' => 'row-gap', 'kind' => 'range' ],
			[ 'prop' => 'columnGap', 'css' => 'column-gap', 'kind' => 'range' ],
			[ 'prop' => 'strokeWidth', 'css' => 'stroke-width', 'kind' => 'range' ],

			// Position offsets.
			[ 'prop' => 'top', 'css' => 'top', 'kind' => 'range' ],
			[ 'prop' => 'right', 'css' => 'right', 'kind' => 'range' ],
			[ 'prop' => 'bottom', 'css' => 'bottom', 'kind' => 'range' ],
			[ 'prop' => 'left', 'css' => 'left', 'kind' => 'range' ],
			// Elementor's "Anchor offset": how far above the element an in-page
			// anchor link should stop, so a fixed header does not cover it.
			[ 'prop' => 'anchorOffset', 'css' => 'scroll-margin-top', 'kind' => 'range' ],

			// Layout.
			[ 'prop' => 'display', 'css' => 'display', 'kind' => 'scalar' ],
			[ 'prop' => 'flexDirection', 'css' => 'flex-direction', 'kind' => 'scalar' ],
			[ 'prop' => 'flexWrap', 'css' => 'flex-wrap', 'kind' => 'scalar' ],
			[ 'prop' => 'justifyContent', 'css' => 'justify-content', 'kind' => 'scalar' ],
			[ 'prop' => 'alignItems', 'css' => 'align-items', 'kind' => 'scalar' ],
			[ 'prop' => 'alignContent', 'css' => 'align-content', 'kind' => 'scalar' ],
			[ 'prop' => 'gridTemplateColumns', 'css' => 'grid-template-columns', 'kind' => 'scalar' ],
			[ 'prop' => 'gridTemplateRows', 'css' => 'grid-template-rows', 'kind' => 'scalar' ],
			[ 'prop' => 'gridAutoFlow', 'css' => 'grid-auto-flow', 'kind' => 'scalar' ],
			[ 'prop' => 'gridAutoColumns', 'css' => 'grid-auto-columns', 'kind' => 'scalar' ],
			[ 'prop' => 'gridAutoRows', 'css' => 'grid-auto-rows', 'kind' => 'scalar' ],

			// Flex-item behaviour (when placed inside an atomic flex/grid parent).
			[ 'prop' => 'flexBasis', 'css' => 'flex-basis', 'kind' => 'scalar' ],
			[ 'prop' => 'flexGrow', 'css' => 'flex-grow', 'kind' => 'scalar' ],
			[ 'prop' => 'flexShrink', 'css' => 'flex-shrink', 'kind' => 'scalar' ],
			[ 'prop' => 'order', 'css' => 'order', 'kind' => 'scalar' ],
			[ 'prop' => 'alignSelf', 'css' => 'align-self', 'kind' => 'scalar' ],

			// Media (image / svg).
			[ 'prop' => 'objectFit', 'css' => 'object-fit', 'kind' => 'scalar' ],
			[ 'prop' => 'objectPosition', 'css' => 'object-position', 'kind' => 'scalar' ],
			[ 'prop' => 'overflow', 'css' => 'overflow', 'kind' => 'scalar' ],
			[ 'prop' => 'fill', 'css' => 'fill', 'kind' => 'scalar' ],
			[ 'prop' => 'stroke', 'css' => 'stroke', 'kind' => 'scalar' ],

			[ 'prop' => 'textAlign', 'css' => 'text-align', 'kind' => 'scalar' ],

			// Position.
			[ 'prop' => 'position', 'css' => 'position', 'kind' => 'scalar' ],
			[ 'prop' => 'zIndex', 'css' => 'z-index', 'kind' => 'scalar' ],

			// Effects.
			[ 'prop' => 'transform', 'css' => 'transform', 'kind' => 'effect' ],
			[ 'prop' => 'transformOrigin', 'css' => 'transform-origin', 'kind' => 'scalar' ],
			[ 'prop' => 'filter', 'css' => 'filter', 'kind' => 'effect' ],
			[ 'prop' => 'backdropFilter', 'css' => 'backdrop-filter', 'kind' => 'effect' ],
			[ 'prop' => 'mixBlendMode', 'css' => 'mix-blend-mode', 'kind' => 'scalar' ],
			// `isolation: isolate` starts a stacking context, which is what
			// confines a CHILD's blend mode to this element instead of letting it
			// blend with whatever the page happens to paint behind it.
			[ 'prop' => 'isolation', 'css' => 'isolation', 'kind' => 'scalar' ],
			[ 'prop' => 'opacity', 'css' => 'opacity', 'kind' => 'scalar' ],
			[ 'prop' => 'boxShadow', 'css' => 'box-shadow', 'kind' => 'effect' ],
			[ 'prop' => 'textShadow', 'css' => 'text-shadow', 'kind' => 'effect' ],
			[ 'prop' => 'cursor', 'css' => 'cursor', 'kind' => 'scalar' ],
			[ 'prop' => 'transition', 'css' => 'transition', 'kind' => 'effect' ],

			// Background (image layer settings; the colour/gradient are above).
			[ 'prop' => 'backgroundRepeat', 'css' => 'background-repeat', 'kind' => 'scalar' ],
			[ 'prop' => 'backgroundSize', 'css' => 'background-size', 'kind' => 'scalar' ],
			[ 'prop' => 'backgroundPosition', 'css' => 'background-position', 'kind' => 'scalar' ],
			[ 'prop' => 'backgroundAttachment', 'css' => 'background-attachment', 'kind' => 'scalar' ],
			// `background-clip: text` needs the -webkit- prefix to paint anywhere
			// but the newest engines, so this kind emits the pair.
			[ 'prop' => 'backgroundClip', 'css' => 'background-clip', 'kind' => 'clip' ],

			// Spacing.
			[ 'prop' => 'padding', 'css' => 'padding', 'kind' => 'dimensions' ],
			[ 'prop' => 'margin', 'css' => 'margin', 'kind' => 'dimensions' ],
		];
	}

	/**
	 * Reorder a declarations map into canonical order, so both compilers
	 * serialise the same declaration set identically — which is what makes the
	 * style hash reproducible across the editor and the front end.
	 *
	 * Some descriptor entries emit CSS properties the descriptor does not name
	 * one-by-one: `typography` expands to font-family/font-size/…, `dimensions`
	 * to padding-top/padding-right/…, and the border group to its longhands. An
	 * unnamed property therefore inherits the rank of the last named property
	 * before it, keeping it adjacent to the group that produced it. Compiler
	 * output is already in descriptor order, so this is a stable no-op there and
	 * a normaliser for maps assembled anywhere else.
	 */
	/**
	 * @param array $declarations CSS declarations keyed by property.
	 * @return array The same declarations in canonical order.
	 */
	public static function canonical_order( $declarations ) {
		if ( ! is_array( $declarations ) || empty( $declarations ) ) {
			return $declarations;
		}

		$rank = [];
		foreach ( self::props() as $i => $entry ) {
			if ( '' !== $entry['css'] ) {
				$rank[ $entry['css'] ] = $i;
			}
		}

		$indexed   = [];
		$n         = 0;
		$last_rank = 0;
		foreach ( $declarations as $property => $value ) {
			if ( isset( $rank[ $property ] ) ) {
				$last_rank = $rank[ $property ];
			}
			$indexed[] = [ $last_rank, $n++, $property, $value ];
		}

		usort(
			$indexed,
			function ( $a, $b ) {
				return $a[0] === $b[0] ? $a[1] - $b[1] : $a[0] - $b[0];
			}
		);

		$out = [];
		foreach ( $indexed as $row ) {
			$out[ $row[2] ] = $row[3];
		}
		return $out;
	}
}
