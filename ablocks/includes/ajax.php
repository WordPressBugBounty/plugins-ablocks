<?php

namespace ABlocks;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use ABlocks\Ajax\Settings;
use ABlocks\Ajax\Dashboard;
use ABlocks\Ajax\DemoImport;
use ABlocks\Ajax\SearchBlock;
use ABlocks\Ajax\FormBuilder;
use ABlocks\Helper;

class Ajax {

	public static function init() {
		$self = new self();
		$self->dispatch_hooks();
		add_action( 'wp_ajax_get_academy_terms', array( $self, 'get_academy_terms' ) );
	}
	public function dispatch_hooks() {
		( new Dashboard() )->dispatch_actions();
		( new Settings() )->dispatch_actions();
		( new SearchBlock() )->dispatch_actions();
		( new FormBuilder() )->dispatch_actions();
		( new DemoImport() )->dispatch_actions();
	}
	public function get_academy_terms() {
		check_ajax_referer( 'ablocks-editor-nonce', 'security' );

		if ( ! current_user_can( 'edit_posts' ) ) {
			die();
		}

		$cats = Helper::get_terms_list( 'academy_courses_category' );
		$tags = Helper::get_terms_list( 'academy_courses_tag' );

		wp_send_json_success(array(
			'categories' => $cats,
			'tags'       => $tags,
		), 200);
	}
}
