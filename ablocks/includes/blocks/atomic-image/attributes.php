<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$attributes = include __DIR__ . '/../atomic-shared/attributes.php';
$attributes['src']      = [ 'type' => 'string', 'default' => '' ];
$attributes['alt']      = [ 'type' => 'string', 'default' => '' ];
$attributes['imageId']  = [ 'type' => 'number', 'default' => 0 ];
$attributes['sizeSlug'] = [ 'type' => 'string', 'default' => '' ];
$attributes['link']     = [ 'type' => 'object', 'default' => [ 'url' => '', 'newTab' => false, 'noFollow' => false ] ];

return $attributes;
