<?php
namespace ABlocks\Blocks\AtomicImage;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ABlocks\Classes\AtomicBlockBase;

/**
 * atomic-image — a single <img> with the atomic style system (object-fit,
 * sizing, border, spacing). Static markup; only the scoped CSS is rendered here.
 */
class Block extends AtomicBlockBase {
	protected $block_name = 'atomic-image';
}
