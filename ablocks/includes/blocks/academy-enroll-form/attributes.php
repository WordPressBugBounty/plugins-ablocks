<?php

use ABlocks\Controls\Typography;
use ABlocks\Controls\Background;
use ABlocks\Controls\Border;
use ABlocks\Controls\Dimensions;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$attributes = [
	'block_id' => array(
		'type' => 'string',
		'default' => '',
	),
	'course_id' => array(
		'type' => 'number',
		'default' => 0,
	),
	'start_btn_color' => array(
		'type' => 'string',
		'default' => '#fff',
	),
	'enroll_btn_color' => array(
		'type' => 'string',
		'default' => '#fff',
	),
	'start_btn_color_hover' => array(
		'type' => 'string',
		'default' => '#fff',
	),
	'enroll_btn_color_hover' => array(
		'type' => 'string',
		'default' => '#fff',
	),
	'start_btn_bg_color' => array(
		'type' => 'string',
		'default' => '#7B68EE',
	),
	'enroll_btn_bg_color' => array(
		'type' => 'string',
		'default' => '#7B68EE',
	),
	'start_btn_bg_hover_color' => array(
		'type' => 'string',
		'default' => '#6F5DD6',
	),
	'enroll_btn_bg_hover_color' => array(
		'type' => 'string',
		'default' => '#6F5DD6',
	),
];

$attributes = array_merge(
	$attributes,
	Typography::get_attribute( 'start_btn_typography', true ),
	Dimensions::get_attribute( 'start_btn_padding', true ),
	Dimensions::get_attribute( 'enroll_btn_padding', true ),
	Typography::get_attribute( 'enroll_btn_typography', true ),
	Border::get_attribute( 'start_btn_border', true ),
	Border::get_attribute( 'enroll_btn_border', true ),
);

return array_merge( $attributes, \ABlocks\Classes\BlockGlobal::get_attributes() );

