<?php
namespace ABlocks\Blocks\FormBuilder;

use ABlocks\Controls\Alignment;
use ABlocks\Controls\Typography;
use ABlocks\Controls\Range;
use ABlocks\Controls\Border;
use ABlocks\Controls\Dimensions;
use ABlocks\Controls\Link;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$attributes = [
	'block_id' => array(
		'type' => 'string',
		'default' => ''
	),
	'variationSelected' => [
		'type' => 'boolean',
		'default' => true,
	],
	'postId' => [
		'type' => 'number',
		'default' => '',
	],
	'showLabels' => [
		'type' => 'boolean',
		'default' => true
	],
	'loginRedirect' => [
		'type' => 'boolean',
		'default' => false
	],
	'registerRedirect' => [
		'type' => 'boolean',
		'default' => false
	],
	'formName' => array(
		'type' => 'string',
		'default' => ''
	),
	'formType' => array(
		'type' => 'string',
		'default' => ''
	),
	'formActions' => array(
		'type' => 'array',
		'default' => []
	),
	'successColor' => [
		'type' => 'string',
		'default' => '#0EFF0E'
	],
	'labelColor' => [
		'type' => 'string',
		'default' => '#000000'
	],
	'inputColor' => [
		'type' => 'string',
		'default' => ''
	],
	'inputBgColor' => [
		'type' => 'string',
		'default' => 'white'
	],
	'inputPlaceholderColor' => [
		'type' => 'string',
		'default' => ''
	],
	'buttonSize' => [
		'type' => 'string',
		'default' => 'full-width'
	],
	'buttonColor' => [
		'type' => 'string',
		'default' => ''
	],
	'buttonBgColor' => [
		'type' => 'string',
		'default' => ''
	],
	'buttonHColor' => [
		'type' => 'string',
		'default' => ''
	],
	'buttonBgHColor' => [
		'type' => 'string',
		'default' => ''
	],
	'buttonText' => [
		'type' => 'string',
		'default' => 'Submit Here',
	],
];

$attributes = array_merge(
	$attributes,
	Alignment::get_attribute( 'labelAlignment', true, [ 'value' => 'left' ] ),
	Alignment::get_attribute( 'inputAlignment', true, [ 'value' => 'left' ] ),
	Alignment::get_attribute( 'buttonAlignment', true, [ 'value' => 'center' ] ),
	Range::get_attribute( [
		'attributeName' => 'rowsSpacing',
		'attributeObjectKey' => 'value',
		'isResponsive' => true,
		'defaultValue' => 0,
	] ),
	Range::get_attribute( [
		'attributeName' => 'labelSpacing',
		'attributeObjectKey' => 'value',
		'isResponsive' => true,
		'defaultValue' => 10,
	] ),
	Range::get_attribute( [
		'attributeName' => 'inputIconPosition',
		'attributeObjectKey' => 'value',
		'isResponsive' => false,
		'defaultValue' => 60,
	] ),
	Link::get_attribute( 'link' ),
	Typography::get_attribute( 'labelTypography', true ),
	Typography::get_attribute( 'inputTypography', true ),
	Typography::get_attribute( 'buttonTypography', true ),
	Border::get_attribute( 'inputBorder', true ),
	Border::get_attribute( 'buttonBorder', true ),
	Dimensions::get_attribute( 'inputPadding', true ),
	Dimensions::get_attribute( 'buttonPadding', true ),
);

return array_merge( $attributes, \ABlocks\Classes\BlockGlobal::get_attributes() );

