<?php
namespace ABlocks\Blocks\AtomicFlex;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ABlocks\Classes\AtomicContainerBase;

/**
 * atomic-flex — an atomic container block. All style compilation, per-state/device
 * variants and interaction animations come from AtomicBlockBase; the front-end
 * rewrite of a linked container comes from AtomicContainerBase.
 */
class Block extends AtomicContainerBase {
	protected $block_name = 'atomic-flex';
}
