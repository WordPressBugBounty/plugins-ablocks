<?php

use ABlocks\Controls\Border;
use ABlocks\Controls\Dimensions;
use ABlocks\Controls\Alignment;
use ABlocks\Controls\Range;
use ABlocks\Controls\Typography;
use ABlocks\Controls\TextShadow;
use ABlocks\Controls\TextStroke;
use ABlocks\Controls\Icon;


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$attributes = [
	'block_id' => [
		'type' => 'string',
		'default' => ''
	],
	'currentPostID' => [
		'type' => 'integer',
		'default' => '',
	],
	'searchTerm' => [
		'type' => 'string',
		'default' => '',
	],
	'source' => [
		'type' => 'string',
		'default' => 'any',
	],
	'isIcon' => [
		'type' => 'string',
		'default' => 'icon',
	],
	'variant' => [
		'type' => 'string',
		'default' => 'classic',
	],
	'position' => [
		'type' => 'string',
		'default' => 'default'
	],
	'buttonText' => [
		'type' => 'string',
		'default' => 'Search',
	],
	'buttonTextColor' => [
		'type' => 'string',
		'default' => '',
	],
	'placeholder' => [
		'type' => 'string',
		'default' => 'Write anything.......',
	],
	'placeholderColor' => [
		'type' => 'string',
		'default' => 'gray',
	],
	'inputTextColor' => [
		'type' => 'string',
		'default' => '#949494',
	],
	'searchResTColor' => [
		'type' => 'string',
		'default' => '#000000',
	],
	'loadingSpinnerColor' => [
		'type' => 'string',
		'default' => '#fff',
	],
];

$attributes = array_merge(
	$attributes,
	Alignment::get_attribute( 'buttonAlignment', false, [ 'value' => 'left' ] ),
	Typography::get_attribute( 'buttonTypography', true ),
	Typography::get_attribute( 'searchResTypography', true ),
	TextShadow::get_attribute( 'buttonTextShadow' ),
	TextStroke::get_attribute( 'buttonTextStroke', true ),
	Alignment::get_attribute( 'alignment', true, [ 'value' => 'left' ] ),
	Typography::get_attribute( 'inputTypography', true ),
	TextShadow::get_attribute( 'inputTextShadow' ),
	TextStroke::get_attribute( 'inputTextStroke', true ),
	Alignment::get_attribute( 'fullscreenButtonAlignment', true, [ 'value' => 'center' ] ),
	Alignment::get_attribute( 'horizontalAlignment', true, [ 'value' => 'left' ] ),
	Alignment::get_attribute( 'verticalAlignment', true, [ 'value' => 'top' ] ),
	Border::get_attribute( 'listBorder', true ),
	Border::get_attribute( 'itemBorder', true ),
	Dimensions::get_attribute( 'listPadding', true ),
	Dimensions::get_attribute( 'itemPadding', true ),
	Range::get_attribute( [
		'attributeName' => 'gap',
		'attributeObjectKey' => 'value',
		'isResponsive' => true,
		'defaultValue' => 0,
		'defaultValueMobile' => 0,
		'defaultValueTablet' => 0,
		'hasUnit' => true,
		'unitDefaultValue' => 'px',
	] ),
	Range::get_attribute( [
		'attributeName' => 'listGap',
		'attributeObjectKey' => 'value',
		'isResponsive' => true,
		'defaultValue' => 10,
		'defaultValueMobile' => 10,
		'defaultValueTablet' => 10,
		'hasUnit' => true,
		'unitDefaultValue' => 'px',
	] ),
	Range::get_attribute( [
		'attributeName' => 'iconWidth',
		'attributeObjectKey' => 'value',
		'isResponsive' => true,
		'defaultValue' => 18,
		'defaultValueMobile' => 18,
		'defaultValueTablet' => 12,
		'hasUnit' => true,
		'unitDefaultValue' => 'px',
	] ),
	Range::get_attribute( [
		'attributeName' => 'listWidth',
		'attributeObjectKey' => 'value',
		'isResponsive' => true,
		'defaultValue' => 230,
		'defaultValueMobile' => 200,
		'defaultValueTablet' => 230,
		'hasUnit' => true,
		'unitDefaultValue' => 'px',
	] ),
	Range::get_attribute( [
		'attributeName' => 'thumbnailWidth',
		'attributeObjectKey' => 'value',
		'isResponsive' => true,
		'defaultValue' => 100,
		'hasUnit' => true,
		'unitDefaultValue' => '%',
	] ),
	Range::get_attribute( [
		'attributeName' => 'thumbnailHeight',
		'attributeObjectKey' => 'value',
		'isResponsive' => true,
		'defaultValue' => 70,
		'hasUnit' => true,
		'unitDefaultValue' => 'px',
	] ),
	Range::get_attribute( [
		'attributeName' => 'itemWidth',
		'attributeObjectKey' => 'value',
		'isResponsive' => true,
		'defaultValue' => 230,
		'defaultValueMobile' => 200,
		'defaultValueTablet' => 230,
		'hasUnit' => true,
		'unitDefaultValue' => 'px',
	] ),
	Range::get_attribute( [
		'attributeName' => 'itemGap',
		'attributeObjectKey' => 'value',
		'isResponsive' => true,
		'defaultValue' => 0,
		'defaultValueMobile' => 0,
		'defaultValueTablet' => 0,
		'hasUnit' => true,
		'unitDefaultValue' => 'px',
	] ),
	Range::get_attribute( [
		'attributeName' => 'horizontalOffset',
		'attributeObjectKey' => 'value',
		'isResponsive' => true,
		'defaultValue' => 230,
		'defaultValueMobile' => 230,
		'defaultValueTablet' => 230,
		'hasUnit' => true,
		'unitDefaultValue' => 'px',
	] ),
	Range::get_attribute( [
		'attributeName' => 'verticalOffset',
		'attributeObjectKey' => 'value',
		'isResponsive' => true,
		'defaultValue' => 230,
		'defaultValueMobile' => 230,
		'defaultValueTablet' => 230,
		'hasUnit' => true,
		'unitDefaultValue' => 'px',
	] ),
);

return array_merge( $attributes, \ABlocks\Classes\BlockGlobal::get_attributes() );

