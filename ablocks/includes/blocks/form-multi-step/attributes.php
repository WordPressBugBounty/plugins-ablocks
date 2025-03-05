<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$attributes = [
	'block_id' => array(
		'type' => 'string',
		'default' => ''
	),
	'steps' => [
		'default' => [
			[
				'id' => 1,
				'value' => 'Step One'
			]
		]
	]
];

$attributes = array_merge(
	$attributes,
);

return array_merge( $attributes, \ABlocks\Classes\BlockGlobal::get_attributes() );

