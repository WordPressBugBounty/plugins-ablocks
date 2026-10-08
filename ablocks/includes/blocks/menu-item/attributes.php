<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ABlocks\Controls\Link;
use ABlocks\Controls\Icon;
use ABlocks\Controls\Range;
$attributes = [
	'block_id' => [
		'type' => 'string',
		'default' => '',
	],
	'label' => [
		'type' => 'string',
		'default' => 'Menu',
	],
	'isSubMenu' => [
		'type' => 'boolean',
		'default' => false,
	],
	'hasMegaMenu' => [
		'type' => 'boolean',
		'default' => false
	],
	'hasLink' => [
		'type' => 'boolean',
		'default' => false,
	],
	'link' => [
		'type' => 'string',
		'default' => '#',
	],
	'showIcon' => [
		'type' => 'boolean',
		'default' => false,
	],
	'iconPosition' => [
		'type' => 'string',
		'default' => 'left',
	],
];

$attributes = array_merge(
	$attributes,
	// Adding attributes from various controls
	Link::get_attribute( 'link' ),
	// The icon is stored in the block comment: the item's dropdown arrow also
	// uses `svg.ablocks-svg-icon`, so an HTML-sourced icon would read it back.
	Icon::get_attribute( 'icon', [
		'size' => 16,
		'color' => '',
		'hasNoSelectorOrSource' => true,
	] ),
	Range::get_attribute([
		'attributeName' => 'iconSpace',
		'attributeObjectKey' => 'value',
		'isResponsive' => true,
		'defaultValue' => 8,
		'hasUnit' => true,
		'unitDefaultValue' => 'px',
	]),
);

return $attributes;
