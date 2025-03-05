<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ABlocks\Controls\Range;
use ABlocks\Controls\Dimensions;
use ABlocks\Controls\Background;
use ABlocks\Controls\BoxShadow;
use ABlocks\Controls\Border;


$attributes = [
	'block_id' => array(
		'type' => 'string',
		'default' => '',
	),
	'popupPosition' => array(
		'type' => 'string',
		'default' => 'popup',
	),
	'panelBlockPosition' => array(
		'type' => 'string',
		'default' => 'bottom',
	),
	'popupOnTop' => array(
		'type' => 'boolean',
		'default' => false,
	),
	'panelContentPosition' => array(
		'type' => 'string',
		'default' => 'auto',
	),
	'disableCloseButton' => array(
		'type' => 'boolean',
		'default' => false,
	),
	'useHoverTrigger' => array(
		'type' => 'boolean',
		'default' => false,
	),
	'enableAutoTriggerTimer' => array(
		'type' => 'boolean',
		'default' => false,
	),
	'backdropColor' => array(
		'type' => 'string',
		'default' => '#000000b3',
	),
	'closeBtnBackgroundColor' => array(
		'type' => 'string',
		'default' => '',
	),
	'closeBtnColor' => array(
		'type' => 'string',
		'default' => '',
	),
	'openPanel' => array(
		'type' => 'string',
		'default' => 'close',
	),
	'closePosition' => array(
		'type' => 'string',
		'default' => 'right',
	),
	'noTrigger' => array(
		'type' => 'boolean',
		'default' => false,
	),
];

$attributes = array_merge(
	$attributes,
	Range::get_attribute([
		'attributeName' => 'popupTopOffset',
		'isResponsive' => false,
		'defaultValue' => 0,
	]),
	Range::get_attribute([
		'attributeName' => 'panelWidth',
		'attributeObjectKey' => 'value',
		'isResponsive' => true,
		'defaultValue' => 0,
		'hasUnit' => true,
		'unitDefaultValue' => 'px',
	]),
	Range::get_attribute([
		'attributeName' => 'panelHeight',
		'attributeObjectKey' => 'value',
		'isResponsive' => true,
		'defaultValue' => 0,
		'hasUnit' => true,
		'unitDefaultValue' => 'px',
	]),
	Range::get_attribute([
		'attributeName' => 'autoTriggerTime',
		'isResponsive' => false,
		'defaultValue' => 0,
	]),
	Range::get_attribute([
		'attributeName' => 'closeBtnTop',
		'isResponsive' => false,
		'defaultValue' => 5,
		'hasUnit' => false,
	]),
	Range::get_attribute([
		'attributeName' => 'closeBtnSide',
		'isResponsive' => false,
		'defaultValue' => 5,
		'hasUnit' => false,
	]),
	Dimensions::get_attribute( 'panelPadding', true ),
	Background::get_attribute( 'panelBackground' ),
	Border::get_attribute( 'panelBorder', true ),
	BoxShadow::get_attribute( 'panelShadow', true ),
);

return array_merge( $attributes, \ABlocks\Classes\BlockGlobal::get_attributes() );
