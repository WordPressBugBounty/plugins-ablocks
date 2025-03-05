<?php

namespace ABlocks\Ajax;

use ABlocks\Helper;
use ABlocks\Classes\Sanitizer;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ABlocks\Classes\AbstractAjaxHandler;
use WP_Query;

class SearchBlock extends AbstractAjaxHandler {
	public function __construct() {
		$this->actions = array(
			'search_block_ajax_action'      => array(
				'callback' => array( $this, 'ablocks_blocks_search_blocks_ajax_handler' ),
				'allow_visitor_action' => true
			),
		);
	}

	public function ablocks_blocks_search_blocks_ajax_handler( $form_data ) {

		$payload = Sanitizer::sanitize_payload([
			'current_page_id'        => 'integer',
			'searchQuery'        => 'string',
			'source'            => 'string',
		], $form_data); // phpcs:ignore WordPress.Security.NonceVerification.Missing

		$searchQuery = $payload['searchQuery'];
		$source = $payload['source'];
		$current_page_id = $payload['current_page_id'];

		$args = array(
			's' => $searchQuery,
			'posts_per_page' => -1,
			'post_type'      => $source,
			'post__not_in'   => array( $current_page_id )
		);

		$query = new WP_Query( $args );

		if ( $query->have_posts() ) {
			$results = array();
			$results_html = '';
			while ( $query->have_posts() ) {

				$query->the_post();
				$title = get_the_title();
				$link = get_permalink();
				$thumbnail = get_the_post_thumbnail_url( get_the_ID(), 'full' );

				ob_start();
				Helper::get_template('search-block/search-result-template.php', array(
					'title' => $title,
					'link' => $link,
					'thumbnail' => $thumbnail,
				));
				$results_html .= ob_get_clean();

				$results[] = array(
					'title' => get_the_title(),
					'link' => get_permalink(),
					'thumbnail' => get_the_post_thumbnail_url( get_the_ID(), 'full' ),
				);
			}//end while

			wp_send_json_success(array(
				'html' => $results_html,
				'data' => $results
			));
		}//end if

	}
}
