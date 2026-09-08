<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$attributes = include __DIR__ . '/../atomic-shared/attributes.php';
$attributes['svgCode'] = [ 'type' => 'string', 'default' => '' ];
// The media library file the markup came from, so the picker can show what is
// selected and offer Replace. The markup itself still lives in svgCode — that
// is what makes fill / stroke / currentColor styleable.
$attributes['svgUrl']  = [ 'type' => 'string', 'default' => '' ];
$attributes['svgId']   = [ 'type' => 'number', 'default' => 0 ];

return $attributes;
