<?php

use ABlocks\Controls\Range;
use ABlocks\Controls\Icon;
$attributes = [
	'block_id'          => [
		'type'          => 'string',
		'default'       => ''
	],
	'verticalAlignment' => [
		'type' => 'string',
		'default' => 'center'
	],
	'effect' => [
		'type' => 'string',
		'default' => 'slide'
	],
	'reverseDirection' => [
		'type' => 'boolean',
		'default' => true
	],
	'carouselSlideLength' => [
		'type' => 'number',
		'default' => 1
	],
	'isLoop' => [
		'type' => 'boolean',
		'default' => false
	],
	'autoplay' => [
		'type' => 'boolean',
		'default' => true
	],
	'autoPlayReverse' => [
		'type' => 'boolean',
		'default' => false
	],
	'autoplayDelay' => [
		'type' => 'number',
		'default' => 3000
	],
	'autoplayProgress' => [
		'type' => 'boolean',
		'default' => true
	],
	'autoplayPauseOnHover' => [
		'type' => 'boolean',
		'default' => true
	],
	'speed' => [
		'type' => 'number',
		'default' => 800
	],
	'pagination' => [
		'type' => 'boolean',
		'default' => true
	],
	'paginationClickable' => [
		'type' => 'boolean',
		'default' => true
	],
	'navigation' => [
		'type' => 'boolean',
		'default' => true
	],
	'navigationIconColor' => [
		'type' => 'string',
		'default' => '#686868'
	],
	'navigationIconBgColor' => [
		'type' => 'string',
		'default' => '#e4e4e4'
	],
	'paginationColor' => [
		'type' => 'string',
		'default' => 'black',
	],
	'paginationActiveColor' => [
		'type' => 'string',
		'default' => 'black',
	],
	'grabCursor' => [
		'type' => 'boolean',
		'default' => true
	],
	'mousewheel' => [
		'type' => 'boolean',
		'default' => true
	],
];

$attributes = array_merge(
	$attributes,
	Range::get_attribute([
		'attributeName' => 'carouselHeight',
		'attributeObjectKey' => 'value',
		'isResponsive' => true,
		'hasUnit' => true,
		'unitDefaultValue' => 'px',
		'defaultValue' => 300,
		'defaultValueMobile' => 300,
		'defaultValueTablet' => 300,
	]),
	Range::get_attribute([
		'attributeName' => 'navigationIconSize',
		'attributeObjectKey' => 'value',
		'isResponsive' => true,
		'hasUnit' => true,
		'unitDefaultValue' => 'px',
		'defaultValue' => 35,
		'defaultValueMobile' => 35,
		'defaultValueTablet' => 35,
	]),
	Range::get_attribute([
		'attributeName' => 'slidesPerView',
		'isResponsive' => true,
		'defaultValue' => 1,
		'defaultValueMobile' => 1,
		'defaultValueTablet' => 1,
		'hasUnit' => false,
	]),
	Range::get_attribute([
		'attributeName' => 'gap',
		'isResponsive' => true,
		'defaultValue' => 0,
		'defaultValueMobile' => 0,
		'defaultValueTablet' => 0,
		'hasUnit' => false,
	]),
	Range::get_attribute([
		'attributeName' => 'navigationIconPositionY',
		'isResponsive' => false,
		'defaultValue' => 50,
		'attributeObjectKey' => 'value',
		'hasUnit' => true,
		'unitDefaultValue' => '%',
		'copyStyle' => true
	]),
	Range::get_attribute([
		'attributeName' => 'navigationIconPositionNextX',
		'isResponsive' => false,
		'defaultValue' => -3,
		'hasUnit' => true,
		'isResponsive' => true,
		'unitDefaultValue' => '%',
	]),
	Range::get_attribute([
		'attributeName' => 'navigationIconPositionPrevX',
		'isResponsive' => false,
		'defaultValue' => -3,
		'hasUnit' => true,
		'unitDefaultValue' => '%',
	]),
	Icon::get_attribute( 'leftIcon', [
		'path' => 'M8 256c0 137 111 248 248 248s248-111 248-248S393 8 256 8 8 119 8 256zm448 0c0 110.5-89.5 200-200 200S56 366.5 56 256 145.5 56 256 56s200 89.5 200 200zm-72-20v40c0 6.6-5.4 12-12 12H256v67c0 10.7-12.9 16-20.5 8.5l-99-99c-4.7-4.7-4.7-12.3 0-17l99-99c7.6-7.6 20.5-2.2 20.5 8.5v67h116c6.6 0 12 5.4 12 12z',
		'viewBox' => '0 0 512 512',
		'hasNoSelectorOrSource' => true,
		'className' => 'far fa-arrow-alt-circle-left'
	] ),
	Icon::get_attribute( 'rightIcon', [
		'path' => 'M504 256C504 119 393 8 256 8S8 119 8 256s111 248 248 248 248-111 248-248zm-448 0c0-110.5 89.5-200 200-200s200 89.5 200 200-89.5 200-200 200S56 366.5 56 256zm72 20v-40c0-6.6 5.4-12 12-12h116v-67c0-10.7 12.9-16 20.5-8.5l99 99c4.7 4.7 4.7 12.3 0 17l-99 99c-7.6 7.6-20.5 2.2-20.5-8.5v-67H140c-6.6 0-12-5.4-12-12z',
		'viewBox' => '0 0 512 512',
		'hasNoSelectorOrSource' => true,
		'className' => 'far fa-arrow-alt-circle-right'
	] ),
);

return array_merge( $attributes, \ABlocks\Classes\BlockGlobal::get_attributes() );

