<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$attributes = include __DIR__ . '/../atomic-shared/attributes.php';
$attributes['htmlTag'] = [ 'type' => 'string', 'default' => 'div' ];

return $attributes;
