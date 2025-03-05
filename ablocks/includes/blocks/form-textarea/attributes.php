<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
use ABlocks\Controls\Range;

$attributes = [
	'block_id' => array(
		'type' => 'string',
		'default' => ''
	),
	'inputWidth' => [
		'type' => 'number',
		'default' => 100
	],
	'customName' => [
		'type' => 'string',
		'default' => '',
	],
	'name' => [
		'type' => 'string',
		'default' => 'message'
	],
	'helperText' => [
		'type' => 'string',
		'default' => '',
	],
	'inputType' => [
		'type' => 'string',
		'default' => '',
	],
	'errorMsg' => [
		'type' => 'string',
		'default' => 'This field is required',
	],

	'labelName' => [
		'type' => 'string',
		'default' => 'Message'
	],
	'isRequired' => [
		'type' => 'boolean',
		'default' => true
	],
	'placeholder' => [
		'type' => 'string',
		'default' => 'Enter your message'
	]
];

$attributes = array_merge(
	$attributes,
	Range::get_attribute( [
		'attributeName' => 'textAreaRow',
		'isResponsive' => false,
		'defaultValue' => 5,
	] ),
);

return array_merge( $attributes, \ABlocks\Classes\BlockGlobal::get_attributes() );

