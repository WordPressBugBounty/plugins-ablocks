<?php

use ABlocks\Controls\Typography;
use ABlocks\Controls\Range;
use ABlocks\Controls\Dimensions;
use ABlocks\Controls\Border;
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
	'quantity' => [
		'type'    => 'number',
	],
	'product_id' => [
		'type'    => 'number',
	],
	'price_display' => [
		'type'    => 'string',
		'default' => 'radio',
	],
	'label' => [
		'type' => 'string',
		'default' => 'Buy Now',
	],
	'direct_checkout' => [
		'type' => 'boolean',
		'default' => true,
	],
	'button_color' => [
		'type'    => 'string',
		'default' => '#fff',
	],
	'button_color_hover' => [
		'type'    => 'string',
		'default' => '#fff',
	],
	'button_bg_hover' => [
		'type'    => 'string',
		'default' => '#008DFF',
	],
	'button_bg' => [
		'type'    => 'string',
		'default' => '#008DFF',
	],

];

$attributes = array_merge(
	$attributes,
	Dimensions::get_attribute( 'padding', true ),
	Typography::get_attribute( 'btn_typography', true ),
	Border::get_attribute( 'buttonBorder', true ),
	BoxShadow::get_attribute( 'boxShadow', true ),
	Alignment::get_attribute( 'buttonAlign', true, [ 'value' => 'left' ] ),
	Range::get_attribute( [
		'attributeName' => 'button_width',
		'attributeObjectKey' => 'value',
		'isResponsive' => true,
		'defaultValue' => 100,
		'hasUnit' => true,
		'unitDefaultValue' => '%',
	]),
);

return array_merge( $attributes, \ABlocks\Classes\BlockGlobal::get_attributes() );

