<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ABlocks\Controls\Alignment;
use ABlocks\Controls\TextShadow;
use ABlocks\Controls\TextStroke;
use ABlocks\Controls\Typography;
use ABlocks\Controls\Border;
use ABlocks\Controls\Range;
$attributes = [
	'block_id' => [
		'type' => 'string',
	],
	'progressValue' => [
		'type' => 'number',
		'default' => 100,
	],
	'maxValue' => [
		'type' => 'number',
		'default' => 100,
	],
	'barColor' => [
		'type' => 'string',
		'default' => 'black',
	],
	'trackColor' => [
		'type' => 'string',
		'default' => '#e0e0e0',
	],
	'layout' => [
		'type' => 'string',
		'default' => 'bar',
	],
	'mediaPosition' => [
		'type' => 'string',
		'default' => 'top',
	],
	'isShowPercentage' => [
		'type' => 'boolean',
		'default' => true,
	],
	'direction' => [
		'type' => 'string',
		'default' => 'left',
	],
	'progressRelative' => [
		'type' => 'string',
		'default' => 'entire_page',
	],
	'progressRelativeSelector' => [
		'type' => 'string',
		'default' => '',
	],

	// progress bar attributes
	'barProgressColor' => [
		'type' => 'string',
		'default' => '#000000',
	],
	'barBackgroundColor' => [
		'type' => 'string',
		'default' => '#DDDDDF',
	],
	// circle
	'circleProgressColor' => [
		'type' => 'string',
		'default' => '#000000',
	],
	'circleBackgroundColor' => [
		'type' => 'string',
		'default' => '#dadada',
	],
	'circleSize' => [
		'type' => 'number',
		'default' => 110,
	],
	'circleStrokeSize' => [
		'type' => 'number',
		'default' => 10,
	],

	'contentColor' => [
		'type' => 'string',
		'default' => 'white'
	]
];

$attributes = array_merge(
	$attributes,
	Alignment::get_attribute( 'alignment', true, [ 'value' => 'center' ] ),
	Typography::get_attribute( 'contentTypography', true ),
	TextShadow::get_attribute( 'contentTextShadow' ),
	TextStroke::get_attribute( 'contentTextStroke', true ),
	Border::get_attribute( 'barBorder', true ),
	Range::get_attribute( [
		'attributeName' => 'barHeightSize',
		'attributeObjectKey' => 'value',
		'isResponsive' => true,
		'defaultValue' => 40,
		'defaultValueMobile' => 30,
		'defaultValueTablet' => 30,
		'hasUnit' => true,
		'unitDefaultValue' => 'px',
	] ),
);

return array_merge( $attributes, \ABlocks\Classes\BlockGlobal::get_attributes() );


