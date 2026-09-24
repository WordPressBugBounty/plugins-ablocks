<?php
namespace ABlocks\Classes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ABlocks\Classes\BlockBaseAbstract;
use ABlocks\Classes\AtomicStyles;
use ABlocks\Controls\Alignment;
use ABlocks\Helper;

/**
 * Shared base for every atomic block (text, image, svg, div, flex, grid).
 *
 * Renders the block's per-state (Normal / Hover / Focus / Active) × per-device
 * styles onto a single higher-specificity selector — `.ablocks-block-{id}
 * .ablocks-{block_name}` (0-2-0) so the block's own styles beat any applied
 * global class (0-1-0) — plus interaction animations and an optional
 * `extra_css()` hook for block-specific declarations. Concrete blocks only set
 * `$block_name` and (optionally) override `extra_css()` / `block_css_class()`.
 */
abstract class AtomicBlockBase extends BlockBaseAbstract {

	// Elementor easing names -> CSS timing functions.
	protected static $easing_css = [
		'easeIn'    => 'cubic-bezier(0.42, 0, 1, 1)',
		'easeOut'   => 'cubic-bezier(0, 0, 0.58, 1)',
		'easeInOut' => 'cubic-bezier(0.42, 0, 0.58, 1)',
		'backIn'    => 'cubic-bezier(0.36, 0, 0.66, -0.56)',
		'backOut'   => 'cubic-bezier(0.34, 1.56, 0.64, 1)',
		'backInOut' => 'cubic-bezier(0.68, -0.6, 0.32, 1.6)',
		'linear'    => 'linear',
	];

	/** The block's own class, e.g. `ablocks-atomic-div`. */
	protected function block_css_class() {
		return 'ablocks-' . $this->block_name;
	}

	/** Whether to emit a per-device text-align from the `alignment` attribute. */
	protected function supports_alignment() {
		return true;
	}

	/**
	 * Keep saved markup pointing at the style class build_css() emits — see
	 * AtomicStyles::refresh_style_class().
	 */
	public function render_callback( $attributes, $content, $block_instance ) {
		$content = AtomicStyles::refresh_style_class(
			$content,
			isset( $attributes['styles'] ) && is_array( $attributes['styles'] ) ? $attributes['styles'] : [],
			$this->supports_alignment() && isset( $attributes['alignment'] ) && is_array( $attributes['alignment'] ) ? $attributes['alignment'] : []
		);
		return parent::render_callback( $attributes, $content, $block_instance );
	}

	/** Block-specific declarations, appended after the shared style rules. */
	protected function extra_css( $base, $attributes ) {
		return '';
	}

	public function build_css( $attributes ) {
		$block_id = isset( $attributes['block_id'] ) ? $attributes['block_id'] : '';
		if ( '' === $block_id ) {
			return '';
		}

		$base      = '.ablocks-block-' . $block_id . '.' . $this->block_css_class();
		$styles    = isset( $attributes['styles'] ) && is_array( $attributes['styles'] ) ? $attributes['styles'] : [];
		$alignment = isset( $attributes['alignment'] ) && is_array( $attributes['alignment'] ) ? $attributes['alignment'] : [];
		$css       = '';

		/*
		 * The block's state/breakpoint styles are emitted as a CONTENT-HASHED
		 * SHARED class, not per instance: every block whose styles compile to
		 * the same rule set gets the same `ablocks-s-{hash}` class, and the rule
		 * is written once per request no matter how many blocks carry it. The
		 * editor stamps the identical class into the markup at save time.
		 *
		 * Alignment folds into the same rule set (see compile_rules) rather than
		 * being emitted separately — it is device-suffixed and belongs to the
		 * normal state, and keeping it in the hash is what stops two blocks with
		 * identical styles but different alignment from sharing a class.
		 */
		$registered = AtomicStyles::register_styles(
			$styles,
			$this->supports_alignment() ? $alignment : []
		);
		$css .= $registered['css'];

		// Transition (smooths hover / focus / active state changes).
		if ( ! empty( $attributes['transitionDuration'] ) ) {
			$css .= $base . '{transition:all ' . $attributes['transitionDuration'] . 's ease;}';
		}

		// Block-specific extra CSS.
		$css .= $this->extra_css( $base, $attributes );

		// Interaction animations (multiple, per trigger + per breakpoint).
		$animations = isset( $attributes['animations'] ) && is_array( $attributes['animations'] ) ? $attributes['animations'] : [];
		if ( empty( $animations ) ) {
			$animations = $this->migrate_legacy_animation( $attributes );
		}
		$css .= $this->build_animation_css( $base, $animations, Helper::get_responsive_devices() );

		// Shared Advanced tab: Custom CSS + responsive visibility.
		$css .= AtomicStyles::advanced_css( $base, $attributes );

		return $css;
	}

	/** Back-compat: convert the old single `animation` object to the new list. */
	protected function migrate_legacy_animation( $attributes ) {
		$anim = isset( $attributes['animation'] ) && is_array( $attributes['animation'] ) ? $attributes['animation'] : [];
		if ( empty( $anim['type'] ) ) {
			return [];
		}
		$map = [
			'fadeIn'     => [ 'fade', '' ],
			'slideUp'    => [ 'slide', 'bottom' ],
			'slideDown'  => [ 'slide', 'top' ],
			'slideLeft'  => [ 'slide', 'right' ],
			'slideRight' => [ 'slide', 'left' ],
			'zoomIn'     => [ 'scale', '' ],
		];
		$m = isset( $map[ $anim['type'] ] ) ? $map[ $anim['type'] ] : [ 'fade', '' ];
		return [
			[
				'id'        => 'legacy',
				'trigger'   => 'load',
				'effect'    => $m[0],
				'type'      => 'in',
				'direction' => $m[1],
				'duration'  => (float) ( ! empty( $anim['duration'] ) ? $anim['duration'] : 0.6 ) * 1000,
				'delay'     => (float) ( ! empty( $anim['delay'] ) ? $anim['delay'] : 0 ) * 1000,
				'easing'    => 'easeIn',
				'repeat'    => '',
				'times'     => 1,
				'off'       => [],
			],
		];
	}

	protected function keyframe_name( $a ) {
		$effect = ! empty( $a['effect'] ) ? $a['effect'] : 'fade';
		$type   = ( isset( $a['type'] ) && 'out' === $a['type'] ) ? 'out' : 'in';
		$dir    = ! empty( $a['direction'] ) ? $a['direction'] : 'none';
		if ( 'slide' === $effect && empty( $a['direction'] ) ) {
			$dir = 'top';
		}
		return 'ablocks-a-' . $effect . '-' . $type . '-' . $dir;
	}

	protected function offset_transform( $direction, $dist ) {
		$n = -$dist;
		$p = $dist;
		switch ( $direction ) {
			case 'top': return 'translateY(' . $n . 'px)';
			case 'bottom': return 'translateY(' . $p . 'px)';
			case 'left': return 'translateX(' . $n . 'px)';
			case 'right': return 'translateX(' . $p . 'px)';
			case 'top-left': return 'translate(' . $n . 'px, ' . $n . 'px)';
			case 'top-right': return 'translate(' . $p . 'px, ' . $n . 'px)';
			case 'bottom-left': return 'translate(' . $n . 'px, ' . $p . 'px)';
			case 'bottom-right': return 'translate(' . $p . 'px, ' . $p . 'px)';
			default: return '';
		}
	}

	protected function keyframe_css( $a ) {
		$effect    = ! empty( $a['effect'] ) ? $a['effect'] : 'fade';
		$type      = ( isset( $a['type'] ) && 'out' === $a['type'] ) ? 'out' : 'in';
		$direction = isset( $a['direction'] ) ? $a['direction'] : '';
		if ( 'slide' === $effect && '' === $direction ) {
			$direction = 'top';
		}
		$dist      = ( 'slide' === $effect ) ? 60 : 24;
		$translate = $this->offset_transform( $direction, $dist );
		$scale     = ( 'scale' === $effect ) ? 'scale(0.85)' : '';
		$off       = trim( $translate . ' ' . $scale );
		if ( '' === $off ) {
			$off = 'none';
		}
		$name = $this->keyframe_name( $a );
		if ( 'out' === $type ) {
			return '@keyframes ' . $name . '{from{opacity:1;transform:none;}to{opacity:0;transform:' . $off . ';}}';
		}
		return '@keyframes ' . $name . '{from{opacity:0;transform:' . $off . ';}to{opacity:1;transform:none;}}';
	}

	protected function iteration_count( $a ) {
		if ( isset( $a['repeat'] ) && 'loop' === $a['repeat'] ) {
			return 'infinite';
		}
		if ( isset( $a['repeat'] ) && 'times' === $a['repeat'] ) {
			$n = isset( $a['times'] ) ? (int) $a['times'] : 1;
			return $n > 0 ? (string) $n : '1';
		}
		return '1';
	}

	protected function anim_shorthand( $a ) {
		$dur    = ( isset( $a['duration'] ) && '' !== $a['duration'] ) ? $a['duration'] : 600;
		$del    = ( isset( $a['delay'] ) && '' !== $a['delay'] ) ? $a['delay'] : 0;
		$easing = ( isset( $a['easing'] ) && isset( self::$easing_css[ $a['easing'] ] ) ) ? self::$easing_css[ $a['easing'] ] : self::$easing_css['easeIn'];
		return $this->keyframe_name( $a ) . ' ' . $dur . 'ms ' . $easing . ' ' . $del . 'ms ' . $this->iteration_count( $a ) . ' both';
	}

	protected function trigger_suffix( $trigger ) {
		switch ( $trigger ) {
			case 'hover': return ':hover';
			case 'scrollIn': return '.ablocks-in-view';
			case 'click': return '.ablocks-anim-active';
			default: return '';
		}
	}

	protected function build_animation_css( $base, $animations, $devices ) {
		if ( empty( $animations ) || ! is_array( $animations ) ) {
			return '';
		}
		$css = '';

		$seen = [];
		foreach ( $animations as $a ) {
			$name = $this->keyframe_name( $a );
			if ( ! isset( $seen[ $name ] ) ) {
				$seen[ $name ] = true;
				$css          .= $this->keyframe_css( $a );
			}
		}

		$has_scroll_in = false;
		foreach ( $animations as $a ) {
			if ( ( isset( $a['trigger'] ) ? $a['trigger'] : '' ) === 'scrollIn' && ( ! isset( $a['type'] ) || 'out' !== $a['type'] ) ) {
				$has_scroll_in = true;
				break;
			}
		}
		if ( $has_scroll_in ) {
			$css .= $base . '.ablocks-anim-scroll.ablocks-anim-ready:not(.ablocks-in-view){opacity:0;}';
		}

		foreach ( [ 'load', 'scrollIn', 'hover', 'click' ] as $trigger ) {
			$group = array_filter( $animations, function ( $a ) use ( $trigger ) {
				return ( isset( $a['trigger'] ) ? $a['trigger'] : 'load' ) === $trigger;
			} );
			if ( empty( $group ) ) {
				continue;
			}
			$sel           = $base . $this->trigger_suffix( $trigger );
			$shorthand_for = function ( $device_id ) use ( $group ) {
				$parts = [];
				foreach ( $group as $a ) {
					if ( empty( $a['off'][ $device_id ] ) ) {
						$parts[] = $this->anim_shorthand( $a );
					}
				}
				return implode( ', ', $parts );
			};
			$desktop = $shorthand_for( 'Desktop' );
			if ( '' !== $desktop ) {
				$css .= $sel . '{animation:' . $desktop . ';}';
			}
			foreach ( $devices as $d ) {
				if ( 'Desktop' === $d['id'] || ( empty( $d['max'] ) && empty( $d['min'] ) ) ) {
					continue;
				}
				$sh = $shorthand_for( $d['id'] );
				if ( $sh !== $desktop ) {
					$media = Helper::breakpoint_media_condition( isset( $d['min'] ) ? $d['min'] : 0, isset( $d['max'] ) ? $d['max'] : 0 );
					if ( '' !== $media ) {
						$css .= '@media screen and ' . $media . '{' . $sel . '{animation:' . ( '' !== $sh ? $sh : 'none' ) . ';}}';
					}
				}
			}
		}

		$scroll_on = array_filter( $animations, function ( $a ) {
			return ( isset( $a['trigger'] ) ? $a['trigger'] : '' ) === 'scrollOn';
		} );
		if ( ! empty( $scroll_on ) ) {
			$scrub = function ( $device_id ) use ( $scroll_on ) {
				$parts = [];
				foreach ( $scroll_on as $a ) {
					if ( empty( $a['off'][ $device_id ] ) ) {
						$parts[] = $this->keyframe_name( $a ) . ' 1000ms linear 0ms 1 both';
					}
				}
				return implode( ', ', $parts );
			};
			$desk = $scrub( 'Desktop' );
			if ( '' !== $desk ) {
				$css .= $base . '{animation:' . $desk . ';animation-play-state:paused;}';
			}
			foreach ( $devices as $d ) {
				if ( 'Desktop' === $d['id'] || ( empty( $d['max'] ) && empty( $d['min'] ) ) ) {
					continue;
				}
				$sh = $scrub( $d['id'] );
				if ( $sh !== $desk ) {
					$media = Helper::breakpoint_media_condition( isset( $d['min'] ) ? $d['min'] : 0, isset( $d['max'] ) ? $d['max'] : 0 );
					if ( '' !== $media ) {
						$css .= '@media screen and ' . $media . '{' . $base . '{animation:' . ( '' !== $sh ? $sh : 'none' ) . ';}}';
					}
				}
			}
		}

		return $css;
	}
}
