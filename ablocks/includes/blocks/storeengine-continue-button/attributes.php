<?php

use ABlocks\Controls\Typography;
use ABlocks\Controls\Background;
use ABlocks\Controls\Border;
use ABlocks\Controls\Dimensions;
use ABlocks\Controls\BoxShadow;
use ABlocks\Controls\Alignment;


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$attributes = [
	'block_id' => [
		'type' => 'string',
		'default' => '',
	],
	'blockVersion' => [
		'type' => 'number',
		'default' => 2,
	],
	'buttonColor' => [
		'type'    => 'string',
		'default' => '',
	],
	'buttonColorH' => [
		'type'    => 'string',
		'default' => '',
	],
	'buttonBackground' => [
		'type'    => 'string',
		'default' => '',
	],
	'buttonBackgroundH' => [
		'type'    => 'string',
		'default' => '',
	],
	'buttonWidth' => [
		'type'    => 'number',
		'default' => 100,
	],
];

$attributes = array_merge(
	$attributes,
	Dimensions::get_attribute( 'padding', true ),
	Typography::get_attribute( 'buttonTypography', true ),
	Border::get_attribute( 'buttonBorder', true ),
	Alignment::get_attribute( 'buttonAlignment', true, [ 'value' => 'left' ] ),
	BoxShadow::get_attribute( 'boxShadow', true ),
);

return array_merge( $attributes, \ABlocks\Classes\BlockGlobal::get_attributes() );

