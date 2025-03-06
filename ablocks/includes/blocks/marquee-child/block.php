<?php
namespace ABlocks\Blocks\MarqueeChild;

use ABlocks\Classes\BlockBaseAbstract;
use ABlocks\Classes\CssGenerator;
use ABlocks\Controls\Alignment;


class Block extends BlockBaseAbstract {
	protected $parent_block_name = 'marquee';
	protected $block_name = 'marquee-child';
	public function build_css( $attributes ) {

		$css_generator = new CssGenerator( $attributes );

		return $css_generator->generate_css();
	}

}
