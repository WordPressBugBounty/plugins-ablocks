<?php
namespace ABlocks\Blocks\AtomicText;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ABlocks\Classes\AtomicBlockBase;

/**
 * Atomic Text block.
 *
 * Renders a single semantic tag (no wrapper). All style compilation, per-state
 * and per-breakpoint variants, and interaction animations come from
 * AtomicBlockBase — this block adds nothing of its own.
 */
class Block extends AtomicBlockBase {
	protected $block_name = 'atomic-text';
}
