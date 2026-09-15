<?php
namespace ABlocks\Classes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ABlocks\Controls\Typography;
use ABlocks\Controls\Color;
use ABlocks\Controls\Dimensions;
use ABlocks\Controls\Alignment;
use ABlocks\Helper;

/**
 * Shared "bucket -> CSS" transformer for the atomic style system, so a block's
 * local class and a reusable global class compile identically.
 *
 * A bucket is one set of style props for a given (breakpoint x interaction
 * state). Where the bucket lives is StyleBuckets' concern; which props exist
 * and what CSS they become is StylesSchema's. This class only turns one
 * bucket's props into declarations, and walks the tree to build rules.
 */
class AtomicStyles {

	/**
	 * Compile a full bucket tree for one selector base into CSS.
	 *
	 * The stored state key IS the pseudo-selector, so it is appended directly;
	 * the breakpoint comes from the bucket's device via the one media-query
	 * builder. Buckets emit widest-first, which is what makes a narrower
	 * breakpoint win in cascade mode.
	 */
	public static function compile_variants( $selector_base, $styles ) {
		return self::rules_to_css( self::compile_rules( $styles ), $selector_base );
	}

	/**
	 * Compile a bucket tree into a normalised rule list:
	 *
	 *   [ [ media-query, state-selector, [ [ prop, value ], … ] ], … ]
	 *
	 * Values are cast to strings and the structure is a plain list, so the JSON
	 * encoding is byte-identical to the JS mirror's `JSON.stringify` — that is
	 * what makes the style hash reproducible across the editor and the front end.
	 *
	 * `$alignment` (the block's own alignment attribute, which lives outside the
	 * bucket tree and is still device-suffixed) folds into each device's normal
	 * state. It has to participate in the hash: two blocks with identical styles
	 * but different alignment are not interchangeable.
	 */
	public static function compile_rules( $styles, $alignment = [] ) {
		$rules      = [];
		$devices    = Helper::get_responsive_devices();
		$has_styles = StyleBuckets::has_schema_version( $styles );

		foreach ( $devices as $device ) {
			$media      = Helper::breakpoint_media_query( $device );
			$bucket_key = StyleBuckets::device_bucket_key( $device );

			foreach ( StyleBuckets::state_keys() as $state ) {
				$declarations = [];

				if ( $has_styles ) {
					$props = StyleBuckets::read_bucket( $styles, $bucket_key, $state );
					if ( ! empty( $props ) ) {
						$declarations = self::apply_background_reset(
							self::state_declarations( $props ),
							'' === $state && '' === $bucket_key
						);
					}
				}

				// Alignment applies to the normal state, last so it beats a
				// `textAlign` style prop set at the same level.
				if ( '' === $state && ! empty( $alignment ) ) {
					$declarations = array_merge(
						$declarations,
						Alignment::get_css( $alignment, 'text-align', $device['suffix'] )
					);
				}

				if ( empty( $declarations ) ) {
					continue;
				}

				$pairs = [];
				foreach ( $declarations as $property => $value ) {
					if ( '' === $value || null === $value ) {
						continue;
					}
					$pairs[] = [ (string) $property, (string) $value ];
				}

				if ( ! empty( $pairs ) ) {
					$rules[] = [ $media, $state, $pairs ];
				}
			}
		}

		return $rules;
	}

	/**
	 * FNV-1a 32-bit over the normalised rule list.
	 *
	 * Deliberately not md5: the editor has to compute the identical hash at save
	 * time, and FNV-1a is a handful of lines in both languages rather than a
	 * crypto dependency in the editor bundle.
	 */
	public static function style_hash( $rules ) {
		$json = wp_json_encode( $rules, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
		$hash = 2166136261;
		$len  = strlen( $json );

		for ( $i = 0; $i < $len; $i++ ) {
			$hash ^= ord( $json[ $i ] );
			$hash  = ( $hash * 16777619 ) & 0xFFFFFFFF;
		}

		return str_pad( dechex( $hash ), 8, '0', STR_PAD_LEFT );
	}

	/** The shared style class for a bucket tree, or '' when it compiles to nothing. */
	public static function style_class( $styles, $alignment = [] ) {
		$rules = self::compile_rules( $styles, $alignment );
		return empty( $rules ) ? '' : 'ablocks-s-' . self::style_hash( $rules );
	}

	/** Hashes already emitted this request, so each rule set is written once. */
	private static $emitted = [];

	/** Forget what has been emitted (test/CLI helper). */
	public static function reset_emitted() {
		self::$emitted = [];
	}

	/**
	 * Register a block's compiled styles and return its shared class plus the
	 * CSS that still needs emitting — empty on every block after the first with
	 * the same rule set, which is where the duplicate-CSS reduction comes from.
	 *
	 * The class is repeated in the selector (0-2-0) so a block's own styles beat
	 * an applied global class (0-1-0) without resorting to `!important`, which
	 * would make global classes unable to override anything.
	 */
	public static function register_styles( $styles, $alignment = [] ) {
		$rules = self::compile_rules( $styles, $alignment );
		if ( empty( $rules ) ) {
			return [ 'class' => '', 'css' => '' ];
		}

		$hash  = self::style_hash( $rules );
		$class = 'ablocks-s-' . $hash;

		if ( isset( self::$emitted[ $hash ] ) ) {
			return [ 'class' => $class, 'css' => '' ];
		}
		self::$emitted[ $hash ] = true;

		return [ 'class' => $class, 'css' => self::rules_to_css( $rules, '.' . $class . '.' . $class ) ];
	}

	/** Render a normalised rule list against a selector base. */
	public static function rules_to_css( $rules, $selector_base ) {
		$css = '';
		foreach ( $rules as $rule ) {
			list( $media, $state, $pairs ) = $rule;
			$declarations = '';
			foreach ( $pairs as $pair ) {
				// Escaped here rather than at the schema, so every kind of prop
				// — scalar, range, colour, typography, effect, overlay — passes
				// through one guard on its way out. This runs after style_hash()
				// has already read $rules, so the hash the editor writes into
				// the markup is unaffected.
				$declarations .= Helper::esc_css_value( $pair[0] ) . ':' . Helper::esc_css_value( $pair[1] ) . ';';
			}
			$body = $selector_base . $state . '{' . $declarations . '}';

			// A wrapping container's own children must size from their content,
			// or the line can never be over-subscribed and `flex-wrap` never
			// breaks one. That is a statement about THIS container's children,
			// so it is emitted as a child rule here rather than as an inherited
			// custom property: a custom property inherits down the whole tree,
			// so a wrapping container silently re-sized the children of every
			// non-wrapping container nested inside it — measured, a
			// non-wrapping inner container's children came out `flex-basis:
			// auto` (content-sized) instead of `0%` (equal share).
			//
			// Derived from the pairs rather than stored, so it costs nothing in
			// the bucket tree, and — because this runs after style_hash() has
			// read $rules — the hash in already-saved markup is unaffected.
			$body .= self::wrap_child_css( $pairs, $selector_base . $state );

			$css .= ( '' !== $media ) ? $media . '{' . $body . '}' : $body;
		}
		return $css;
	}

	/**
	 * The child rule a wrapping container needs, or ''.
	 *
	 * Scoped to container children only, matching the base stylesheets — a leaf
	 * block is sized by its own block, not by the row it sits in. `:where()`
	 * keeps the selector at the same specificity as those base rules, and this
	 * <style> is injected after them, so it wins on order alone.
	 *
	 * Mirrors wrapChildCss() in atomic-shared/styles.js.
	 *
	 * @param array  $pairs    The bucket's declaration pairs.
	 * @param string $selector The already-composed selector for this bucket.
	 * @return string A CSS rule, or ''.
	 */
	public static function wrap_child_css( $pairs, $selector ) {
		$wraps = false;
		foreach ( $pairs as $pair ) {
			if ( 'flex-wrap' === $pair[0]
				&& ( 'wrap' === $pair[1] || 'wrap-reverse' === $pair[1] ) ) {
				$wraps = true;
			}
		}
		if ( ! $wraps ) {
			return '';
		}
		return $selector . self::WRAP_CHILD_SELECTOR . '{flex-basis:auto;}';
	}

	/** The child combinator both compilers append for a wrapping container. */
	const WRAP_CHILD_SELECTOR = '>:where(.ablocks-atomic-div,.ablocks-atomic-flex,.ablocks-atomic-grid)';

	/**
	 * A block-specific rule emitted only for the buckets that compile a given
	 * CSS property — media query and state preserved.
	 *
	 * Lets one block react to a declaration the shared compiler produced
	 * without that reaction leaking to every other atomic block, and without a
	 * second copy of the bucket/breakpoint walk. Used by the SVG block, whose
	 * graphic must stop filling its wrapper once the author has asked for the
	 * wrapper to position it.
	 *
	 * @param array       $styles          The block's styles object.
	 * @param string      $selector_base   The block's own selector.
	 * @param string      $property        The compiled CSS property to look for.
	 * @param string      $suffix          Appended to the selector (e.g. ' svg').
	 * @param string      $declarations    The declarations to emit.
	 * @param string|null $unless_property Skip a bucket that ALSO compiles this
	 *                                     property — an explicit value there is
	 *                                     more specific than the reaction being
	 *                                     conditioned on, and must win outright
	 *                                     rather than being overridden by it.
	 * @return string CSS, or ''.
	 */
	public static function conditional_rules( $styles, $selector_base, $property, $suffix, $declarations, $unless_property = null ) {
		$css = '';
		foreach ( self::compile_rules( $styles ) as $rule ) {
			list( $media, $state, $pairs ) = $rule;
			$found  = false;
			$skip   = false;
			foreach ( $pairs as $pair ) {
				if ( $pair[0] === $property ) {
					$found = true;
				}
				if ( null !== $unless_property && $pair[0] === $unless_property ) {
					$skip = true;
				}
			}
			if ( ! $found || $skip ) {
				continue;
			}
			$body = $selector_base . $state . $suffix . '{' . $declarations . '}';
			$css .= ( '' !== $media ) ? $media . '{' . $body . '}' : $body;
		}
		return $css;
	}

	/** Whether a bucket is the base one (base device, normal state). */
	public static function is_base_bucket( $bucket ) {
		return '' === $bucket['state']
			&& '' === StyleBuckets::device_bucket_key( $bucket['device'] );
	}

	/**
	 * A bucket that sets a solid background colour and no gradient of its own
	 * must clear any gradient inherited from a lower-precedence bucket:
	 * `background-color` and `background-image` are separate properties, so the
	 * gradient would otherwise stay painted on top of the solid colour.
	 *
	 * Skipped for the base bucket on purpose — resetting there would also wipe a
	 * gradient supplied by an applied global class, which the block never asked
	 * to override.
	 */
	public static function apply_background_reset( $declarations, $is_base_bucket ) {
		if ( $is_base_bucket || ! is_array( $declarations ) ) {
			return $declarations;
		}

		$has_color = isset( $declarations['background-color'] ) && '' !== $declarations['background-color'];
		$has_image = isset( $declarations['background-image'] ) && '' !== $declarations['background-image'];

		if ( $has_color && ! $has_image ) {
			$declarations['background-image'] = 'unset';
		}

		return $declarations;
	}

	/**
	 * Compile one bucket of style props into a CSS declarations map.
	 *
	 * Props inside a bucket carry no device suffix — the bucket key is the
	 * device — so this reads them directly. Every prop the atomic system
	 * understands is declared once in StylesSchema; this walks that descriptor
	 * rather than enumerating props itself, so the JS editor compiler and this
	 * one cannot drift on which props exist, what CSS property each maps to, or
	 * what order they emit in.
	 */
	public static function state_declarations( $props ) {
		$css = [];
		if ( ! is_array( $props ) ) {
			return $css;
		}

		foreach ( StylesSchema::props() as $entry ) {
			$prop = $entry['prop'];

			switch ( $entry['kind'] ) {

				case 'typography':
					if ( ! empty( $props['typography'] ) ) {
						$global = ! empty( $props['typographyGlobal'] ) ? $props['typographyGlobal'] : '';
						// false: no font-stack expansion — the JS mirror cannot
						// reproduce it, and these declarations are hashed.
						$css    = array_merge( $css, Typography::get_css( $props['typography'], '', '', $global, false ) );
					}
					break;

				case 'color':
					$value = self::read_scalar( $props, $prop );
					if ( '' !== $value ) {
						$css[ $entry['css'] ] = Color::get_css( $value );
					}
					break;

				case 'scalar':
					$value = self::read_scalar( $props, $prop );
					if ( '' !== $value ) {
						$css[ $entry['css'] ] = $value;
					}
					break;

				case 'range':
					$value = self::read_range( $props, $prop );
					if ( '' !== $value ) {
						$css[ $entry['css'] ] = $value;
					}
					break;

				case 'effect':
					// Repeatable lists (shadow / transform / transition / filters).
					$value = StyleEffects::to_css( $prop, isset( $props[ $prop ] ) ? $props[ $prop ] : null );
					if ( '' !== $value ) {
						$css[ $entry['css'] ] = $value;
					}
					break;

				case 'overlay':
					// Background overlay layers -> background-image + the four
					// positional properties, emitted together.
					$css = array_merge( $css, StyleBackground::to_declarations( isset( $props[ $prop ] ) ? $props[ $prop ] : null ) );
					break;

				case 'clip':
					// `background-clip: text` still needs the -webkit- longhand.
					$value = self::read_scalar( $props, $prop );
					if ( '' !== $value ) {
						$css[ '-webkit-' . $entry['css'] ] = $value;
						$css[ $entry['css'] ]              = $value;
					}
					break;

				case 'border':
					$css = array_merge( $css, self::border_css( $props ) );
					break;

				case 'dimensions':
					$css = array_merge( $css, self::spacing_css( $props, $prop ) );
					break;
			}
		}

		// An explicit width, held against a flex row too narrow for it, held
		// against a row with space to spare, held against the base stylesheets'
		// `flex-basis: 0%` on every container child, kept from overflowing a
		// parent narrower than it, and centred in whatever is left — all five
		// mirror the JS compiler's stateToPairs(), which carries the full
		// reasoning. Appended after the schema loop in both, so the declaration
		// order the style hash is taken over stays identical.
		$has_width     = '' !== self::read_range( $props, 'width' );
		$has_max_width = '' !== self::read_range( $props, 'maxWidth' );

		if ( $has_width ) {
			// No `flex-shrink: 0` — see the JS note. Pinning shrink to 0 is
			// what let a child escape its parent, and `flex-basis: auto` below
			// already holds the width whenever the row has room for it.
			if ( '' === self::read_scalar( $props, 'flexGrow' ) ) {
				$css['flex-grow'] = '0';
			}
			if ( '' === self::read_scalar( $props, 'flexBasis' ) ) {
				$css['flex-basis'] = 'auto';
			}
			if ( ! $has_max_width ) {
				$css['max-width'] = '100%';
			}
		}

		// Centring answers to EITHER cap — see the JS note. Kept as its own
		// condition rather than folded into the block above so the declaration
		// order both compilers hash over stays identical.
		if ( ( $has_width || $has_max_width ) && ! self::has_horizontal_margin( $props ) ) {
			$css['margin-left']  = 'auto';
			$css['margin-right'] = 'auto';
		}

		return $css;
	}

	/**
	 * Whether the author set a left/right margin of their own — `common` covers
	 * the linked case, where one value drives all four sides.
	 *
	 * @param array $props The bucket's props.
	 * @return bool Whether a horizontal margin is set.
	 */
	private static function has_horizontal_margin( $props ) {
		$margin = isset( $props['margin'] ) ? $props['margin'] : null;
		if ( ! is_array( $margin ) ) {
			return false;
		}
		foreach ( [ 'common', 'left', 'right' ] as $side ) {
			if ( isset( $margin[ $side ] ) && '' !== $margin[ $side ] ) {
				return true;
			}
		}
		return false;
	}

	/** A scalar prop from this bucket. Absent means "inherit", not "empty". */
	private static function read_scalar( $props, $base ) {
		return ( isset( $props[ $base ] ) && '' !== $props[ $base ] ) ? $props[ $base ] : '';
	}

	/** An aBlocks Range object ({ value, valueUnit }) -> "<value><unit>". */
	private static function read_range( $props, $base ) {
		$obj = isset( $props[ $base ] ) ? $props[ $base ] : '';

		if ( is_array( $obj ) ) {
			if ( ! isset( $obj['value'] ) || '' === $obj['value'] ) {
				return '';
			}
			$unit = ( isset( $obj['valueUnit'] ) && '' !== $obj['valueUnit'] ) ? $obj['valueUnit'] : 'px';
			return $obj['value'] . $unit;
		}

		return ( is_string( $obj ) && '' !== $obj ) ? $obj : '';
	}

	/**
	 * The border group: Range width/radius plus scalar style/colour.
	 *
	 * CSS paints no border without a style, so a bucket that sets only a width
	 * OR only a colour still gets one. Colour-only is the common case — a hover
	 * bucket that recolours an existing border — and it rendered nothing before
	 * this fallback covered it.
	 */
	private static function border_css( $props ) {
		$css = [];

		$width  = self::read_range( $props, 'borderWidth' );
		$style  = self::read_scalar( $props, 'borderStyle' );
		$color  = self::read_scalar( $props, 'borderColor' );
		$radius = self::read_range( $props, 'borderRadius' );

		// Per-side widths are overrides layered on the uniform one, so they are
		// emitted after it and win by cascade order.
		$side_widths    = [];
		$has_side_width = false;
		foreach ( StylesSchema::BORDER_SIDES as $side ) {
			$value = self::read_range( $props, 'borderWidth' . $side );
			$side_widths[ strtolower( $side ) ] = $value;
			if ( '' !== $value ) {
				$has_side_width = true;
			}
		}

		if ( '' !== $width ) {
			$css['border-width'] = $width;
		}
		foreach ( $side_widths as $side => $value ) {
			if ( '' !== $value ) {
				$css[ 'border-' . $side . '-width' ] = $value;
			}
		}

		// A width on any single side needs a style too, or it paints nothing.
		if ( '' !== $width || $has_side_width || '' !== $color ) {
			$css['border-style'] = '' !== $style ? $style : 'solid';
		} elseif ( '' !== $style ) {
			// A style on its own is meaningful (e.g. `none` to remove a border).
			$css['border-style'] = $style;
		}

		if ( '' !== $color ) {
			$css['border-color'] = Color::get_css( $color );
		}

		if ( '' !== $radius ) {
			$css['border-radius'] = $radius;
		}
		foreach ( StylesSchema::BORDER_CORNERS as $corner => $property ) {
			$value = self::read_range( $props, 'borderRadius' . $corner );
			if ( '' !== $value ) {
				$css[ $property ] = $value;
			}
		}

		return $css;
	}

	/**
	 * Compile padding/margin for one bucket via the aBlocks Dimensions control's
	 * own get_css, so the output is identical to every other block's spacing.
	 * The device argument is always '' — the bucket already is the device.
	 */
	private static function spacing_css( $props, $prop ) {
		$obj = isset( $props[ $prop ] ) && is_array( $props[ $prop ] ) ? $props[ $prop ] : [];
		if ( empty( $obj ) ) {
			return [];
		}
		return Dimensions::get_css( $obj, $prop, '' );
	}

	/**
	 * Shared "Advanced" tab output: free-form Custom CSS (with a `selector`
	 * placeholder for this block) + per-device visibility. $base is the block's
	 * own selector. Mirrors the JS editor preview.
	 */
	public static function advanced_css( $base, $attributes ) {
		$css = '';

		// Custom CSS — `selector` resolves to this block; strip any </style> so
		// authored CSS can't break out of the inline <style> tag.
		$custom = isset( $attributes['customCSS'] ) ? (string) $attributes['customCSS'] : '';
		if ( '' !== trim( $custom ) ) {
			$custom = str_replace( 'selector', $base, $custom );
			$custom = preg_replace( '#</\s*style#i', '', $custom );
			$css   .= $custom;
		}

		/*
		 * Responsive visibility — hide on desktop / tablet / mobile ranges.
		 *
		 * These deliberately stay EXCLUSIVE bands and do not follow the site's
		 * `breakpoint_mode`. Visibility is not a cascading value: "hide on
		 * tablet" must not also hide the block on mobile, so a max-width
		 * envelope would be wrong here even when style rules cascade.
		 */
		$hide = isset( $attributes['hideOn'] ) && is_array( $attributes['hideOn'] ) ? $attributes['hideOn'] : [];
		if ( ! empty( $hide['desktop'] ) || ! empty( $hide['tablet'] ) || ! empty( $hide['mobile'] ) ) {
			$bp     = Helper::get_breakpoints();
			$tablet = isset( $bp['tablet'] ) ? (int) $bp['tablet'] : 1024;
			$mobile = isset( $bp['mobile'] ) ? (int) $bp['mobile'] : 767;
			$none   = $base . '{display:none !important;}';
			if ( ! empty( $hide['desktop'] ) ) {
				$css .= '@media screen and (min-width:' . ( $tablet + 1 ) . 'px){' . $none . '}';
			}
			if ( ! empty( $hide['tablet'] ) ) {
				$css .= '@media screen and (min-width:' . ( $mobile + 1 ) . 'px) and (max-width:' . $tablet . 'px){' . $none . '}';
			}
			if ( ! empty( $hide['mobile'] ) ) {
				$css .= '@media screen and (max-width:' . $mobile . 'px){' . $none . '}';
			}
		}

		return $css;
	}

	/**
	 * Turn a declarations map into a minified declaration string.
	 */
	public static function to_string( $declarations ) {
		$out = '';
		foreach ( $declarations as $prop => $value ) {
			if ( '' !== $value && null !== $value ) {
				$out .= Helper::esc_css_value( $prop ) . ':' . Helper::esc_css_value( $value ) . ';';
			}
		}
		return $out;
	}
}
