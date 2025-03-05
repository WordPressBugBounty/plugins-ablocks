<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ABlocks\Controls\Alignment;
use ABlocks\Controls\Typography;
use ABlocks\Controls\Range;
use ABlocks\Controls\Dimensions;
use ABlocks\Controls\Border;

$attributes = [
	'block_id' => [
		'type' => 'string',
		'default' => ''
	],
	'isSwitch' => [
		'type' => 'boolean',
		'default' => false,
	],
	'leftLabel' => [
		'type' => 'string',
		'default' => 'Monthly',
	],
	'rightLabel' => [
		'type' => 'string',
		'default' => 'Yearly',
	],
	'toggleBarBgColor' => [
		'type' => 'string',
		'default' => ''
	],
	'toggleDirection' => [
		'type' => 'string',
		'default' => 'row'
	],

	'labelColorState' => [
		'type' => 'string',
		'default' => 'normal',
	],
	'labelNormalColor' => [
		'type' => 'string',
		'default' => 'black',
	],
	'labelActiveColor' => [
		'type' => 'string',
		'default' => '#562DD4',
	],
	'toggleColorState' => [
		'type' => 'string',
		'default' => 'normal',
	],
	'toggleNormalColor' => [
		'type' => 'string',
		'default' => 'white',
	],
	'toggleNormalBgColor' => [
		'type' => 'string',
		'default' => '#562DD4',
	],
	'toggleActiveColor' => [
		'type' => 'string',
		'default' => 'white',
	],
	'toggleActiveBgColor' => [
		'type' => 'string',
		'default' => '#d4552d',
	],
];

$attributes = array_merge(
	$attributes,
	Alignment::get_attribute( 'alignment', true, [ 'value' => 'center' ] ),
	Typography::get_attribute( 'labelTypography', true ),
	Dimensions::get_attribute( 'toggleBarPadding', true ),
	Border::get_attribute( 'toggleBarBorder', true ),
	Range::get_attribute([
		'attributeName' => 'space',
		'isResponsive' => true,
		'attributeObjectKey' => 'value',
		'defaultValue' => 0,
		'hasUnit' => false,
	]),
	Range::get_attribute([
		'attributeName' => 'gap',
		'isResponsive' => true,
		'attributeObjectKey' => 'value',
		'defaultValue' => 0,
		'hasUnit' => false,
	]),
	Range::get_attribute([
		'attributeName' => 'toggleWidth',
		'isResponsive' => false,
		'defaultValue' => 60,
	]),
	Range::get_attribute([
		'attributeName' => 'toggleHeight',
		'isResponsive' => false,
		'defaultValue' => 28,
	]),
);

return array_merge( $attributes, \ABlocks\Classes\BlockGlobal::get_attributes() );

