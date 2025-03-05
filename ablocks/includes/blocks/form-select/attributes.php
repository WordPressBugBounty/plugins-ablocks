<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$attributes = [
	'block_id' => array(
		'type' => 'string',
		'default' => ''
	),
	'inputWidth' => [
		'type' => 'number',
		'default' => 100
	],
	'labelName' => [
		'type' => 'string',
		'default' => 'What\'s your favorite programming lang?'
	],
	'name' => [
		'type' => 'string',
		'default' => 'Message',
	],
	'helperText' => [
		'type' => 'string',
		'default' => ''
	],
	'isRequired' => [
		'type' => 'boolean',
		'default' => true,
	],
	'errorMsg' => [
		'type' => 'string',
		'default' => 'This field is required',
	],
	'options' => [
		'type' => 'array',
		'default' => [
			[
				'id' => 1,
				'value' => 'javascript'
			]
		]
	]
];

$attributes = array_merge(
	$attributes,
);

return array_merge( $attributes, \ABlocks\Classes\BlockGlobal::get_attributes() );

