<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$attributes = [
	'block_id' => [
		'type' => 'string',
		'default' => ''
	],
	'inputType' => [
		'type' => 'string',
		'default' => ''
	],
	'optionWidth' => [
		'type' => 'number',
		'default' => 50
	],
	'label' => [
		'type' => 'string',
		'default' => 'What\'s your favorite programming lang?'
	],

	'helperText' => [
		'type' => 'string',
		'default' => '',
	],
	'inputWidth' => [
		'type' => 'number',
		'default' => 100
	],
	'name' => [
		'type' => 'string',
		'default' => '',
	],
	'isRequired' => [
		'type' => 'boolean',
		'default' => true,
	],
	'errorMsg' => [
		'type' => 'string',
		'default' => 'This field is required'
	],
	'radioArr' => [
		'type' => 'array',
		'default' => [
			[
				'id' => 1,
				'value' => 'Javascript',
			],
		]
	]
];

$attributes = array_merge(
	$attributes,
);

return array_merge( $attributes, \ABlocks\Classes\BlockGlobal::get_attributes() );

