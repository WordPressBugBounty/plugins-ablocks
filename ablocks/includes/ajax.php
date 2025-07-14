<?php

namespace ABlocks;

use WP_Query;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use ABlocks\Ajax\Settings;
use ABlocks\Ajax\Dashboard;
use ABlocks\Ajax\DemoImport;
use ABlocks\Ajax\DynamicContent;
use ABlocks\Ajax\SearchBlock;
use ABlocks\Ajax\FormBuilder;
use ABlocks\Ajax\Entry;
use ABlocks\Ajax\StripePaymentAjax;
use ABlocks\Helper;

class Ajax {

	public static function init() {
		$self = new self();
		$self->dispatch_hooks();
		add_action( 'wp_ajax_get_post_by_post_type', [ $self, 'get_post_by_post_type_callback' ] );
		add_action( 'wp_ajax_get_academy_terms', array( $self, 'get_academy_terms' ) );
		add_action( 'wp_ajax_get_storeengine_terms', array( $self, 'get_storeengine_terms' ) );
	}
	public function dispatch_hooks() {
		( new Dashboard() )->dispatch_actions();
		( new Settings() )->dispatch_actions();
		( new DynamicContent() )->dispatch_actions();
		( new SearchBlock() )->dispatch_actions();
		( new FormBuilder() )->dispatch_actions();
		( new Entry() )->dispatch_actions();
		( new StripePaymentAjax() )->dispatch_actions();
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

	public function get_storeengine_terms() {
		check_ajax_referer( 'ablocks_nonce', 'security' );
				$cats = Helper::get_terms_list( 'storeengine_product_category' );
		$tags = Helper::get_terms_list( 'storeengine_product_tag' );

		wp_send_json_success(array(
			'categories' => $cats,
			'tags'       => $tags,
		), 200);
	}


	public function get_post_by_post_type_callback() {
		check_ajax_referer( 'ablocks-editor-nonce', 'security' );

		$post_per_pages = isset( $_POST['post_per_pages'] ) ? intval( $_POST['post_per_pages'] ) : -1;
		if ( ! current_user_can( 'edit_posts' ) ) {
			die();
		}

		$args = array(
			'post_type' => 'post',
			'posts_per_page' => $post_per_pages
		);

		$query = new WP_Query( $args );
		if ( $query->have_posts() ) {
			$posts = array();

			while ( $query->have_posts() ) {
				$query->the_post();

				$thumbnail_url = has_post_thumbnail() ? get_the_post_thumbnail_url( get_the_ID(), 'thumbnail' ) : '';

				$posts[] = array(
					'id'        => get_the_ID(),
					'title'     => get_the_title(),
					'excerpt'   => get_the_excerpt(),
					'thumbnail' => $thumbnail_url
				);
			}

			wp_send_json_success( $posts );
		}
		wp_send_json_error( 'No posts found' );
	}
}
