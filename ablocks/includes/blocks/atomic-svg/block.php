<?php
namespace ABlocks\Blocks\AtomicSvg;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ABlocks\Classes\AtomicBlockBase;
use ABlocks\Classes\AtomicStyles;

/**
 * atomic-svg — inline SVG with the atomic style system (fill / stroke / size).
 * Static markup; only the scoped CSS is rendered here.
 */
class Block extends AtomicBlockBase {
	protected $block_name = 'atomic-svg';

	/**
	 * The graphic fills its wrapper by default — that is what makes the Size
	 * panel's width resize the icon, since the wrapper shrink-wraps it.
	 *
	 * `justify-content` asks for the opposite: it positions content inside the
	 * box, which needs the graphic to be SMALLER than the box. While the
	 * graphic was filling, there was never any free space to distribute, so
	 * the control silently did nothing — measured, a 300px-wide block with
	 * `justify-content: center` painted a 300px graphic flush to the left edge.
	 *
	 * So when the author has set it, the graphic takes its own intrinsic size
	 * and the wrapper positions it. Emitted per bucket, so it follows the
	 * breakpoint and state the author set it in.
	 *
	 * Skipped for a bucket that ALSO sets Width there: Custom Width is the more
	 * specific, more deliberate choice — it is what makes the Size panel resize
	 * the icon at all — and must keep winning even once justify-content is set,
	 * or reducing it silently stopped doing anything the moment the author also
	 * centred the icon: the graphic reverted to its intrinsic size instead of
	 * shrinking, and with it the space it occupies inside the wrapper (what
	 * Align items / Justify content actually position), so both kept reading
	 * the icon's PREVIOUS, unshrunk size.
	 */
	protected function extra_css( $base, $attributes ) {
		$styles = isset( $attributes['styles'] ) && is_array( $attributes['styles'] )
			? $attributes['styles']
			: [];

		return AtomicStyles::conditional_rules(
				$styles,
				$base,
				'justify-content',
				' svg',
				'width:auto;max-width:100%;',
				'width'
			)
			. $this->cancel_flex_item_centering( $styles, $base );
	}

	/**
	 * Cancel the shared width guard's `margin-left:auto;margin-right:auto`
	 * (see StylesSchema::state_declarations()) wherever it actually fired.
	 *
	 * That guard exists to centre a narrower TOP-LEVEL block — a plain block
	 * box, the only shape it was written for. This wrapper is `display:
	 * inline-flex`, so in that normal-flow case the auto margins do nothing at
	 * all: inline-level boxes never centre on their own horizontal margins.
	 * They start doing something only once the wrapper becomes a Flex/Grid
	 * ITEM of an Atomic Div/Flex/Grid parent — every value for a flex item's
	 * own `display` computes to a block-level "outer display" regardless of
	 * what was authored, which is exactly the size at which auto margins
	 * start responding. And what they do there is take over the item's
	 * position on the main axis outright — a flex item's own auto margins are
	 * consulted before `justify-content` ever runs, so the parent's
	 * Left/Center/Right stopped choosing anything: measured, `justify-content:
	 * flex-start` and `flex-end` on the SAME 50px-wide icon with a Custom
	 * Width rendered at the IDENTICAL centred position (277px from the left in
	 * a 604px row either way) the moment the icon carried a width of its own.
	 *
	 * So the guard is worth exactly nothing for this block in the case it was
	 * built for, and actively wrong in the one case it can ever reach. Rather
	 * than touch the shared compiler both other guards on this block already
	 * come from — and risk every OTHER atomic block that relies on it staying
	 * centred inside a normal, non-flex parent — this walks the SAME compiled
	 * buckets and, only where the guard actually wrote `auto` (never where an
	 * author's OWN margin is what is there instead — `has_horizontal_margin()`
	 * is what stopped the guard writing anything in that case, and this must
	 * agree with it or an author's margin would be overwritten right back to
	 * zero), resets it to `0` on the very same selector. Same specificity,
	 * later in this block's own <style> tag, so it is this declaration that
	 * wins — the shared rule is untouched, and the outcome is identical to it
	 * never having fired here at all.
	 *
	 * @param array  $styles The block's styles object.
	 * @param string $base   The block's own selector.
	 * @return string CSS, or ''.
	 */
	private function cancel_flex_item_centering( $styles, $base ) {
		$css = '';
		foreach ( AtomicStyles::compile_rules( $styles ) as $rule ) {
			list( $media, $state, $pairs ) = $rule;
			$centred = false;
			foreach ( $pairs as $pair ) {
				if ( 'margin-left' === $pair[0] && 'auto' === $pair[1] ) {
					$centred = true;
					break;
				}
			}
			if ( ! $centred ) {
				continue;
			}
			$body = $base . $state . '{margin-left:0;margin-right:0;}';
			$css .= ( '' !== $media ) ? $media . '{' . $body . '}' : $body;
		}
		return $css;
	}
}
