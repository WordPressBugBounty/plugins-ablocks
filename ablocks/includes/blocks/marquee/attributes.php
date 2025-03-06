<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
use ABlocks\Controls\Range;


$attributes = [
	'block_id' => [
		'type' => 'string',
		'default' => '',
	],
	'marqueeSlideLength' => [
		'type' => 'number',
		'default' => 0,
	],
	'pauseOnHover' => [
		'type' => 'boolean',
		'default' => true,
	],
	'marqueeDirection' => [
		'type' => 'string',
		'default' => 'left',
	],
	'marqueeSpeed' => [
		'type' => 'number',
		'default' => 10,
	],
	'loop' => [
		'type' => 'boolean',
		'default' => false,
	],
	'loopCount' => [
		'type' => 'number',
		'default' => 2,
	],
];

$attributes = array_merge(
	$attributes,
	Range::get_attribute([
		'attributeName' => 'gap',
		'attributeObjectKey' => 'value',
		'isResponsive' => false,
		'hasUnit' => false,
		'unitDefaultValue' => 'px',
		'defaultValue' => 12,
	])
);

return array_merge( $attributes, \ABlocks\Classes\BlockGlobal::get_attributes() );
