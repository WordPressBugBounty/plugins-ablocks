<?php

use ABlocks\Controls\Border;
use ABlocks\Controls\Background;
use ABlocks\Controls\Dimensions;
use ABlocks\Controls\Range;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$attributes = [
	'block_id' => [
		'type' => 'string',
		'default' => '',
	],
	'flipDirection' => [
		'type' => 'string',
		'default' => 'left',
	],
	'showSide' => [
		'type' => 'string',
		'default' => 'front',
	],
];

$attributes = array_merge(
	$attributes,
	Range::get_attribute([
		'attributeName' => 'transitionSpeed',
		'attributeObjectKey' => 'value',
		'defaultValue' => 0.6,
		'unitDefaultValue' => 's',
	]),
	Border::get_attribute( 'cardBorder', true ),
	Background::get_attribute( 'frontCardBackground', true ),
	Background::get_attribute( 'backCardBackground', true ),
	Dimensions::get_attribute( 'frontPadding', true ),
	Dimensions::get_attribute( 'backPadding', true ),
);

return array_merge( $attributes, \ABlocks\Classes\BlockGlobal::get_attributes() );

