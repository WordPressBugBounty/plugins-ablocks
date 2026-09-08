<?php
namespace ABlocks\Blocks\AtomicGrid;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ABlocks\Classes\AtomicContainerBase;

/**
 * atomic-grid — an atomic container block. All style compilation, per-state/device
 * variants and interaction animations come from AtomicBlockBase; the front-end
 * rewrite of a linked container comes from AtomicContainerBase.
 */
class Block extends AtomicContainerBase {
	protected $block_name = 'atomic-grid';
}
