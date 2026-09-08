<?php
namespace ABlocks\Controls;

use ABlocks\Classes\ControlBaseAbstract;

class GsapAnimation extends ControlBaseAbstract {
	public static function get_attribute_default_value( $is_responsive = false ) {
		if ( $is_responsive ) {
			return [
				'animationType' => 'none',
				'duration' => 1,
				'delay' => 0,
				'triggerType' => 'scroll',
				// from method values
				'ease' => 'none',
				'x' => 0,
				'y' => 0,
				'rotation' => 0,
				'rotationY' => 0,
				'opacity' => 1,
				// to method values
				'xTo' => 0,
				'yTo' => 0,
				'rotationTo' => 0,
				'rotationYTo' => 0,
				'opacityTo' => 1,
				'easeTo' => 'none',
				// repeat/loops
				'loops' => false,
				'numberOfRepeat' => 0, // if -1 then infinite loops
				'isSmooth' => true, // yoyo
			// if animation type 'scroll' then these will work
				'startPoint' => 80, // top ${this.attribute?.startPoint ?? 80}%
				'endPoint' => 10
			];

		}//end if
		return [
			'animationType' => 'none',
			'duration' => 1,
			'delay' => 0,
			'triggerType' => 'scroll',
			// from method values
			'ease' => 'none',
			'x' => 0,
			'y' => 0,
			'rotation' => 0,
			'rotationY' => 0,
			'opacity' => 1,
			// to method values
			'xTo' => 0,
			'yTo' => 0,
			'rotationTo' => 0,
			'rotationYTo' => 0,
			'opacityTo' => 1,
			'easeTo' => 'none',
			// repeat/loops
			'loops' => false,
			'numberOfRepeat' => 0, // if -1 then infinite loops
			'isSmooth' => true, // yoyo
		// if animation type 'scroll' then these will work
			'startPoint' => 80, // top ${this.attribute?.startPoint ?? 80}%
			'endPoint' => 10
		];
	}

	public static function get_attribute( $attributeName, $isResponsive = false ) {
		return [
			$attributeName => [
				'type' => 'object',
				'default' => self::get_attribute_default_value( $isResponsive ),
			]
		];
	}

	/**
	 * GSAP animations are driven entirely by the frontend script (see the
	 * gsap view scripts) — this control contributes no CSS.
	 *
	 * The body used to compile a mask-image rule from `mask*` members and then
	 * throw the result away with an unconditional `return []`. Since the control
	 * declares no mask members at all, every one of those reads was an
	 * "Undefined array key" notice on any page carrying a GSAP animation. Dead
	 * code that could only emit warnings, so it is gone; masking lives in the
	 * Mask control.
	 *
	 * @param mixed  $attribute_value Stored control value.
	 * @param string $property        Unused.
	 * @param string $device          Unused.
	 * @return array Always empty.
	 */
	public static function get_css( $attribute_value, $property = '', $device = '' ) {
		return [];
	}


}
