<?php
namespace ABlocks\Blocks\AtomicSvg;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ABlocks\Classes\AtomicBlockBase;

/**
 * atomic-svg — inline SVG with the atomic style system (fill / stroke / size).
 * Static markup; only the scoped CSS is rendered here.
 */
class Block extends AtomicBlockBase {
	protected $block_name = 'atomic-svg';
}
