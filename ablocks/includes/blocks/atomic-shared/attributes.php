<?php
/**
 * Common attributes shared by every atomic block. Each block's attributes.php
 * includes this and adds its own extras (tag, src, svgCode …).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return [
	'block_id'           => [ 'type' => 'string', 'default' => '' ],
	'blockVersion'       => [ 'type' => 'number', 'default' => 3 ],
	'globalClasses'      => [ 'type' => 'array', 'default' => [] ],
	'htmlId'             => [ 'type' => 'string', 'default' => '' ],
	// Any atomic element can be a link — that is what makes a whole card
	// clickable — so this is shared rather than per-block.
	'link'               => [ 'type' => 'object', 'default' => [ 'url' => '', 'newTab' => false, 'noFollow' => false ] ],
	'animation'          => [ 'type' => 'object', 'default' => [ 'type' => '', 'duration' => '0.6', 'delay' => '0' ] ],
	'animations'         => [ 'type' => 'array', 'default' => [] ],
	'transitionDuration' => [ 'type' => 'string', 'default' => '' ],
	'alignment'          => [ 'type' => 'object', 'default' => [ 'value' => '', 'valueTablet' => '', 'valueMobile' => '' ] ],
	// Buckets are created on demand as controls are touched, so the default is
	// just the schema marker — see StyleBuckets for the shape. These defaults
	// back-fill attributes a saved block omitted (AssetsGenerator), so the
	// marker has to be here or the styles would read as an unreadable schema.
	'styles'             => [ 'type' => 'object', 'default' => \ABlocks\Classes\StyleBuckets::empty_styles() ],
	// Shared "Advanced" tab: free-form CSS (with a `selector` placeholder) and
	// per-device visibility. Consumed by AtomicBlockBase::build_css().
	'customCSS'          => [ 'type' => 'string', 'default' => '' ],
	'hideOn'             => [ 'type' => 'object', 'default' => [ 'desktop' => false, 'tablet' => false, 'mobile' => false ] ],
];
