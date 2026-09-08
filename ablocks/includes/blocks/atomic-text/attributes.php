<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$attributes = [
	'block_id'      => [ 'type' => 'string', 'default' => '' ],
	'blockVersion'  => [ 'type' => 'number', 'default' => 3 ],
	'tag'           => [ 'type' => 'string', 'default' => 'p' ],
	'content'       => [ 'type' => 'string', 'default' => '' ],
	'globalClasses' => [ 'type' => 'array', 'default' => [] ],
	'htmlId'        => [ 'type' => 'string', 'default' => '' ],
	'link'          => [ 'type' => 'object', 'default' => [ 'url' => '', 'newTab' => false, 'noFollow' => false ] ],
	'animation'     => [ 'type' => 'object', 'default' => [ 'type' => '', 'duration' => '0.6', 'delay' => '0' ] ],
	// Multiple interactions/animations. Each: {id, trigger, effect, type,
	// direction, duration(ms), delay(ms), easing, off:{deviceId:true}}.
	'animations'    => [ 'type' => 'array', 'default' => [] ],
	'transitionDuration' => [ 'type' => 'string', 'default' => '' ],
	'alignment'     => [
		'type'    => 'object',
		'default' => [ 'value' => '', 'valueTablet' => '', 'valueMobile' => '' ],
	],
	// Style buckets (breakpoint × state) are created on demand as controls are
	// touched — see StyleBuckets for the shape — so the default is just the
	// schema marker. These defaults back-fill attributes a saved block omitted
	// (AssetsGenerator), so the marker has to be here or the styles would read
	// as an unreadable schema.
	'styles'        => [ 'type' => 'object', 'default' => \ABlocks\Classes\StyleBuckets::empty_styles() ],
	// Shared "Advanced" tab (see AtomicBlockBase::build_css).
	'customCSS'     => [ 'type' => 'string', 'default' => '' ],
	'hideOn'        => [ 'type' => 'object', 'default' => [ 'desktop' => false, 'tablet' => false, 'mobile' => false ] ],
];

return $attributes;
