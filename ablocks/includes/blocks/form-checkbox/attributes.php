<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$attributes = [
	'block_id' => array(
		'type' => 'string',
		'default' => ''
	),
	'label' => [
		'type' => 'string',
		'default' => 'Message',
	],
	'inputWidth' => [
		'type' => 'number',
		'default' => 100
	],
	'errorMsg' => [
		'type' => 'string',
		'default' => 'This field is required',
	],
	'helperText' => [
		'type' => 'string',
		'default' => '',
	],
	'name' => [
		'type' => 'string',
		'default' => ''
	],
	'isRequired' => [
		'type' => 'boolean',
		'default' => true,
	],
	'isChecked' => [
		'type' => 'boolean',
		'default' => false,
	],
	'placeholder' => [
		'type' => 'string',
		'default' => 'Enter your message'
	],
];

$attributes = array_merge(
	$attributes,
);

return array_merge( $attributes, \ABlocks\Classes\BlockGlobal::get_attributes() );

