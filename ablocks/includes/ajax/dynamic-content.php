<?php

namespace ABlocks\Ajax;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ABlocks\Classes\AbstractAjaxHandler;
use ABlocks\Helper;

class DynamicContent extends AbstractAjaxHandler {

	public function __construct() {
		$this->actions = array(
			'get_taxonomies_data'      => array(
				'callback' => array( $this, 'get_taxonomies_data' ),
				'capability' => 'edit_posts',
				'fields'     => [
					'post_type'      => 'string',
				],
			),
			'get_taxonomy_term_data'      => array(
				'callback' => array( $this, 'get_taxonomy_term_data' ),
				'capability' => 'edit_posts',
				'fields'     => [
					'taxonomy'      => 'string',
				],
			),
			'get_terms_for_post'      => array(
				'callback' => array( $this, 'get_terms_for_post' ),
				'capability' => 'edit_posts',
				'fields'     => [
					'post_id'      => 'integer',
					'taxonomy'      => 'string',
				],
			),
			'get_all_meta_keys_for_post_type'      => array(
				'callback' => array( $this, 'get_all_meta_keys_for_post_type' ),
				'capability' => 'edit_posts',
				'fields'     => [
					'post_type'      => 'string',
				],
			),
			'get_post_meta'      => array(
				'callback' => array( $this, 'get_post_meta' ),
				'capability' => 'edit_posts',
				'fields'     => [
					'post_id'      => 'integer',
					'meta_key'      => 'string',
				],
			),
			'search_posts'      => array(
				'callback' => array( $this, 'search_posts' ),
				'capability' => 'edit_posts',
				'fields'     => [
					'query'      => 'string',
				],
			),
			'get_author_data'      => array(
				'callback' => array( $this, 'get_author_data' ),
				'capability' => 'edit_posts',
				'fields'     => [
					'post_id'           => 'integer',
					'author_id'         => 'integer',
				],
			),
			'get_author_meta'      => array(
				'callback' => array( $this, 'get_author_meta' ),
				'capability' => 'edit_posts',
				'fields'     => [
					'author_id'      => 'integer',
					'meta_key'      => 'string',
				],
			),
			'get_post_comments_count'      => array(
				'callback' => array( $this, 'get_post_comments_count' ),
				'capability' => 'edit_posts',
				'fields'     => [
					'postID'      => 'integer',
				],
			),
			'get_featured_media_data'      => array(
				'callback' => array( $this, 'get_featured_media_data' ),
				'capability' => 'edit_posts',
				'fields'     => [
					'postID'      => 'integer',
				],
			),
			'get_all_taxonomies_terms_data'      => array(
				'callback' => array( $this, 'get_all_taxonomies_terms_data' ),
				'capability' => 'edit_posts'
			),
			'search_medias'      => array(
				'callback' => array( $this, 'search_medias' ),
				'capability' => 'edit_posts',
				'fields'     => [
					'query'      => 'string',
					'mediaID'      => 'integer',
				],
			),
			'get_all_authors_data'      => array(
				'callback' => array( $this, 'get_all_authors_data' ),
				'capability' => 'edit_posts'
			),
			'get_author_profile_picture_data'      => array(
				'callback' => array( $this, 'get_author_profile_picture_data' ),
				'capability' => 'edit_posts',
				'fields'     => [
					'postID'      => 'integer',
				],
			),
			'get_academy_course_data'      => array(
				'callback' => array( $this, 'get_academy_course_data' ),
				'capability' => 'edit_posts',
				'fields'     => [
					'post_id'      => 'integer',
					'meta_key'      => 'string',
				],
			),
			'loop_builder'      => array(
				'callback' => array( $this, 'render_loop_builder' ),
				'allow_visitor_action' => true,
				'fields'     => [
					'loop_builder_block_id'      => 'string',
					'loop_template_block_id'      => 'string',
					'taxonomy'          => 'string',
					'post_id'           => 'integer',
					'term_id'           => 'integer',
					'term_slug'         => 'string',
					'page'              => 'integer',
					'is_archive'        => 'boolean',
					'archive_post_type' => 'string',
				],
			),
		);
	}

	public function get_taxonomies_data( $payload ) {
		$post_type = $payload['post_type'];

		if ( empty( $post_type ) ) {
			wp_send_json_error( __( 'Sorry, Post type is required!', 'ablocks' ) );
		}

		$all_taxonomies = Helper::get_taxonomies_data_for_post_type( $post_type );

		wp_send_json_success( $all_taxonomies );
	}

	public function get_taxonomy_term_data( $payload ) {
		if ( empty( $payload['taxonomy'] ) ) {
			wp_send_json_error( __( 'Sorry, Taxonomy is required', 'ablocks' ) );
		}
		$terms = get_terms([
			'taxonomy' => $payload['taxonomy'],
			'hide_empty' => false, // Set to true if you only want terms attached to posts
		]);

		$results = array_map(function( $term ) {
			return [
				'label' => $term->name,
				'value' => $term->term_id,
			];
		}, $terms);

		wp_send_json_success( $results );
	}

	public function get_terms_for_post( $payload ) {
		$terms = Helper::get_terms_for_post( $payload['taxonomy'], $payload['post_id'] );
		wp_send_json_success( $terms );
	}

	public function get_all_meta_keys_for_post_type( $payload ) {
		global $wpdb;
		$post_type = $payload['post_type'];

		if ( empty( $post_type ) ) {
			wp_send_json_error( __( 'Sorry, Post type is required!', 'ablocks' ) );
		}

		$query = "
            SELECT DISTINCT pm.meta_key
            FROM {$wpdb->postmeta} pm
            INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
            WHERE p.post_type = %s
            AND pm.meta_key NOT LIKE '\_%'
        ";

		$meta_keys = $wpdb->get_col( $wpdb->prepare( $query, $post_type ) );

		wp_send_json_success( $meta_keys );
	}

	public function get_post_meta( $payload ) {

		if ( empty( $payload['post_id'] ) || empty( $payload['meta_key'] ) ) {
			wp_send_json_error( __( 'Sorry, Post ID and Meta Key is required!', 'ablocks' ) );
		}

		$post_id = (int) $payload['post_id'];
		$meta_key = $payload['meta_key'];

		$result = get_post_meta( $post_id, $meta_key, true );
		wp_send_json_success( $result );
	}

	public function search_posts( $payload ) {
		global $wpdb;
		$search_query = $payload['query'];
		$included_post_types = get_post_types(
			[
				'public' => true,
				'show_in_rest' => true, // using `'show_in_rest' => true` because, unwanted post-types like `elementor_library` was being returned without this.
			],
			'names'
		);
		$included_post_types = array_values( $included_post_types );
		$excluded_post_types = [ 'attachment', 'elementor_library' ];
		$included_post_types = array_diff( $included_post_types, $excluded_post_types );
		$post_type_placeholders = implode( ',', array_fill( 0, count( $included_post_types ), '%s' ) );
		if ( empty( $search_query ) ) {
			$query = "
                SELECT ID, post_title, post_type
                FROM $wpdb->posts
                WHERE post_status = 'publish'
                AND post_type IN ($post_type_placeholders)
                ORDER BY post_date DESC
                LIMIT 5
            ";
			$prepared_query = $wpdb->prepare( $query, ...$included_post_types );
		} else {
			$query = "
                SELECT ID, post_title, post_type
                FROM $wpdb->posts
                WHERE post_status = 'publish'
                AND post_title LIKE %s
                AND post_type IN ($post_type_placeholders)
                LIMIT 5
            ";
			$safe_query = '%' . $wpdb->esc_like( $search_query ) . '%';
			$prepared_query = $wpdb->prepare( $query, array_merge( [ $safe_query ], $included_post_types ) );
		}//end if
		$results = $wpdb->get_results( $prepared_query, ARRAY_A );
		wp_send_json_success( $results );
	}

	public function get_author_data( $payload ) {
		$post_id = $payload['post_id'] ?? 0;
		$author_id = $payload['author_id'] ?? 0;

		if ( ! $post_id || ! get_post( $post_id ) ) {
			wp_send_json_error( 'Invalid post ID.', 400 );
		}

		$data = Helper::get_author_data( $post_id, $author_id );

		wp_send_json_success( $data );
	}

	public function get_author_meta( $payload ) {
		$meta_key = $payload['meta_key'];
		$author_id = $payload['author_id'];
		$data = get_user_meta( $author_id, $meta_key, true );
		wp_send_json_success( $data );
	}

	public function get_post_comments_count( $payload ) {
		$postID = $payload['postID'] ?? 0;
		$result = get_comments_number( $postID );
		wp_send_json_success( $result );
	}

	public function get_featured_media_data( $payload ) {
		$postID = $payload['postID'] ?? 0;

		$result = [];

		if ( $postID ) {
			$attachment_id = get_post_meta( $postID, '_thumbnail_id', true );

			if ( $attachment_id ) {
				$attachment = get_post( $attachment_id );

				if ( $attachment ) {
					$result = [
						'featured_image_title' => $attachment->post_title,
						'featured_image_alt' => get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ),
						'featured_image_caption' => $attachment->post_excerpt,
						'featured_image_description' => $attachment->post_content,
						'featured_image_file_url' => wp_get_attachment_url( $attachment_id ),
						'featured_image_attachment_url' => get_permalink( $attachment_id )
					];
				}
			}
		}

		wp_send_json_success( $result );
	}

	public function get_all_taxonomies_terms_data() {

		$taxonomies = get_taxonomies( [ 'public' => true ], 'objects' );
		$data = [];

		foreach ( $taxonomies as $taxonomy => $tax_obj ) {
			$terms = get_terms( [
				'taxonomy' => $taxonomy,
				'hide_empty' => false
			] );

			if ( empty( $terms ) || is_wp_error( $terms ) ) {
				continue;
			}

			foreach ( $terms as $term ) {
				$data[] = [
					'value' => $term->term_id,
					'term_link' => get_term_link( $term ),
					'label' => "{$tax_obj->label}: {$term->name}",
				];
			}
		}

		wp_send_json_success( $data );
	}

	public function search_medias( $payload ) {

		global $wpdb;
		$search_query = $payload['query'] ?? '';
		$media_id = $payload['mediaId'] ?? 0;

		if ( empty( $search_query ) ) {
			$prepared_query = $wpdb->prepare(
				"SELECT ID, post_title FROM {$wpdb->posts} 
                WHERE post_type = 'attachment' AND post_status = 'inherit'
                ORDER BY post_date DESC LIMIT 5"
			);
		} else {
			$prepared_query = $wpdb->prepare(
				"SELECT ID, post_title FROM {$wpdb->posts} 
                WHERE post_type = 'attachment' AND post_status = 'inherit'
                AND post_title LIKE %s 
                ORDER BY post_date DESC LIMIT 5",
				'%' . $wpdb->esc_like( $search_query ) . '%'
			);
		}

		$results = $wpdb->get_results( $prepared_query, ARRAY_A );

		$media_items = array_map(function ( $media ) {
			return [
				'value'     => $media['ID'],
				'label'     => $media['post_title'],
				'url'       => get_attachment_link( $media['ID'] )
			];
		}, $results);

		$media_item = null;
		$media_id_present_in_result = $media_id && ! in_array( "{$media_id}", array_column( $media_items, 'value' ) );

		if ( $media_id_present_in_result ) {
			$media_item = $wpdb->get_row($wpdb->prepare(
				"SELECT ID, post_title FROM {$wpdb->posts} 
                    WHERE ID = %d AND post_type = 'attachment' AND post_status = 'inherit'",
				$media_id
			), ARRAY_A);
		}

		if ( $media_item ) {
			array_unshift($media_items, [
				'value'    => $media_item['ID'],
				'label' => $media_item['post_title'],
				'url'   => get_attachment_link( $media_item['ID'] )
			]);
		}

		wp_send_json_success( $media_items );
	}

	public function get_all_authors_data() {

		$args = array(
			'has_published_posts' => true,
			'orderby'     => 'display_name',
			'order'       => 'ASC',
			'fields'      => array( 'ID', 'display_name', 'user_email' ),
		);

		$users = get_users( $args );
		$authors = array();

		foreach ( $users as $user ) {
			$authors[] = array(
				'value'    => $user->ID,
				'label'  => $user->display_name . " ({$user->user_email})",
			);
		}

		wp_send_json_success( $authors );
	}

	public function get_author_profile_picture_data( $payload ) {

		$post_id = $payload['postID'] ?? 0;

		if ( ! $post_id ) {
			$post_id = get_the_ID();
		}

		$author_id = get_post_field( 'post_author', $post_id );
		$result = get_avatar_url( $author_id );

		wp_send_json_success( $result );
	}

	public function get_academy_course_data( $payload ) {

		$post_id = $payload['post_id'] ?? 0;
		$meta_key = $payload['meta_key'] ?? '';

		if ( ! $post_id ) {
			$post_id = get_the_ID();
		}

		if ( ! Helper::is_active_academy() ) {
			wp_send_json_error( '' );
		}

		$Analytics = new \Academy\Classes\Analytics();
		$results = '';
		if ( 'numberOfTopics' === $meta_key ) {
			$curriculums = \Academy\Helper::get_course_curriculums_number_of_counts( $post_id );

			$results = isset( $curriculums['total_topics'] ) ? $curriculums['total_topics'] : 0;
		} elseif ( 'numberOfReviews' === $meta_key ) {
			$results = $Analytics->get_total_number_of_reviews_by_course_id( $post_id );
		} elseif ( 'numberOfEnrolled' === $meta_key ) {
			$results = $Analytics->get_total_number_of_enrolled_by_course_id( $post_id );
		}

		wp_send_json_success( $results );
	}

	public function render_loop_builder( $payload ) {
		$post_id = $payload['post_id'];
		$loop_builder_block_id = $payload['loop_builder_block_id'];
		$loop_template_block_id = $payload['loop_template_block_id'];
		$taxonomy = $payload['taxonomy'];
		$term_id = (int) $payload['term_id'];
		$page = (int) ( isset( $payload['page'] ) ? $payload['page'] : 1 );
		$is_archive = (bool) ( isset( $payload['is_archive'] ) ? $payload['is_archive'] : false );

		if ( $is_archive ) {
			if ( ! empty( $payload['archive_post_type'] ) ) {
				// Post type archive template
				$template_slug = 'archive-' . $payload['archive_post_type'];
			} elseif ( ! empty( $payload['taxonomy'] ) ) {
				if ( ! empty( $payload['term_slug'] ) ) {
					// Taxonomy term archive template
					$template_slug = 'archive-' . $payload['taxonomy'] . '-' . $payload['term_slug'];
				} else {
					// Taxonomy archive template without term
					$template_slug = 'archive-' . $payload['taxonomy'];
				}
			}

			$theme_slug = wp_get_theme()->get_stylesheet();
			$template_posts = get_posts([
				'post_type'      => 'wp_template',
				'post_status'    => 'publish',
				'numberposts'    => 1,
				'name'           => $template_slug, // post_name (slug)
				'tax_query'      => [
					[
						'taxonomy' => 'wp_theme',
						'field'    => 'slug',
						'terms'    => $theme_slug,
					],
				],
			]);

			$post_content = current( $template_posts )->post_content;
			$blocks = parse_blocks( $post_content );
			$block_data = Helper::get_block_attributes_recursive( $loop_builder_block_id, 'ablocks/loop-builder', $blocks );
		} else {
			$block_data = Helper::get_block_attributes( $payload['post_id'], $payload['loop_builder_block_id'], 'ablocks/loop-builder' );
				// Get and parse post content
			$post = get_post( $post_id );
			$post_content = $post->post_content;
			$blocks = parse_blocks( $post_content );
		}//end if

		$query_vars = isset( $block_data['parentAttributes']['query'] ) ? $this->convert_to_wp_query_args( $block_data['parentAttributes']['query'] ) : [
			'post_type' => 'post',
			'posts_per_page' => get_option( 'posts_per_page' )
		];
		if ( $is_archive && ! empty( $payload['archive_post_type'] ) ) {
			$query_vars['post_type'] = $payload['archive_post_type'];
		}
		$query_vars['posts_per_page'] = $query_vars['posts_per_page'] * $page;
		if ( ! empty( $taxonomy ) && $term_id ) {
			$query_vars['tax_query'] = [
				[
					'taxonomy' => $taxonomy,
					'field'    => 'term_id',
					'terms'    => [ $term_id ],
					'operator' => 'IN',
				],
			];
		}

		$blocks = $this->get_loop_builder_blocks_by_block_id( $blocks, $loop_builder_block_id );

		// Update the block attributes
		$updated_blocks = $this->update_loop_builder_query( $blocks, $loop_template_block_id, $query_vars, $term_id );

		// Serialize blocks back to content
		$html = '';
		foreach ( $updated_blocks as $block ) {
			$html .= serialize_block( $block );
		}

		$rendered = do_blocks( $html );

		wp_send_json_success([
			'html' => $rendered,
			'term_id' => $term_id,
			'post_id' => $post_id,
		]);
	}

	public function get_loop_builder_blocks_by_block_id( $blocks, $target_block_id ) {
		foreach ( $blocks as $block ) {
			if ( ! empty( $block['attrs']['block_id'] ) && $block['attrs']['block_id'] === $target_block_id ) {
				return [ $block ];
			}
			if ( ! empty( $block['innerBlocks'] ) ) {
				$found = $this->get_loop_builder_blocks_by_block_id( $block['innerBlocks'], $target_block_id );
				if ( ! empty( $found ) ) {
					return $found;
				}
			}
		}
		return [];
	}

	public function update_loop_builder_query( $blocks, $loop_template_block_id, $query_vars, $term_id ) {
		foreach ( $blocks as &$block ) {
			// perfectly not worked if multiple loop used
			// !empty($block['attrs']['block_id']) && $block['attrs']['block_id'] === $loop_template_block_id

			if ( ! empty( $block['blockName'] ) && $block['blockName'] === 'ablocks/loop-template' ) {
				if ( ! isset( $block['attrs']['query'] ) ) {
					$block['attrs']['query'] = [];
				}
				// Merge new query vars
				$block['attrs']['query'] = array_merge( $block['attrs']['query'], $query_vars );
			}

			if ( 'ablocks/loop-filter' === $block['blockName'] ) {
				$block['attrs']['active_term_id'] = $term_id;
			}

			// Recurse into inner blocks
			if ( ! empty( $block['innerBlocks'] ) ) {
				$block['innerBlocks'] = $this->update_loop_builder_query( $block['innerBlocks'], $loop_template_block_id, $query_vars, $term_id );
			}
		}//end foreach
		return $blocks;
	}

	public function convert_to_wp_query_args( array $input ): array {
		$args = [];

		// Basic pagination
		$args['posts_per_page'] = isset( $input['perPage'] ) ? (int) $input['perPage'] : get_option( 'posts_per_page' );
		$args['paged'] = isset( $input['pages'] ) && $input['pages'] > 0 ? (int) $input['pages'] : 1;

		// Post type
		if ( ! empty( $input['postType'] ) ) {
			$args['post_type'] = $input['postType'];
		}

		// Order and orderby
		if ( ! empty( $input['order'] ) ) {
			$args['order'] = $input['order'];
		}

		if ( ! empty( $input['orderBy'] ) ) {
			$args['orderby'] = $input['orderBy'];
		}

		// Author
		if ( ! empty( $input['author'] ) ) {
			$args['author'] = $input['author'];
		}

		// Search
		if ( ! empty( $input['search'] ) ) {
			$args['s'] = $input['search'];
		}

		// Exclude posts
		if ( ! empty( $input['exclude'] ) && is_array( $input['exclude'] ) ) {
			$args['post__not_in'] = array_map( 'intval', $input['exclude'] );
		}

		// Sticky posts
		if ( ! empty( $input['sticky'] ) ) {
			if ( $input['sticky'] === 'include' ) {
				$args['ignore_sticky_posts'] = 0;
			} elseif ( $input['sticky'] === 'exclude' ) {
				$args['ignore_sticky_posts'] = 1;
			}
		}

		// Post parent (for hierarchical post types like pages)
		if ( ! empty( $input['parents'] ) && is_array( $input['parents'] ) ) {
			$args['post_parent__in'] = array_map( 'intval', $input['parents'] );
		}

		// Offset
		if ( isset( $input['offset'] ) ) {
			$args['offset'] = (int) $input['offset'];
		}

		// Format (post format taxonomy)
		if ( ! empty( $input['format'] ) ) {
			$args['tax_query'][] = [
				'taxonomy' => 'post_format',
				'field'    => 'slug',
				'terms'    => [ 'post-format-' . sanitize_title( $input['format'] ) ],
			];
		}

		// Category
		if ( ! empty( $input['category'] ) ) {
			$args['category_name'] = $input['category'];
		}

		// Tag
		if ( ! empty( $input['tag'] ) ) {
			$args['tag'] = $input['tag'];
		}

		// Generic taxonomy query
		if ( ! empty( $input['taxonomy'] ) && ! empty( $input['value'] ) ) {
			$args['tax_query'][] = [
				'taxonomy' => $input['taxonomy'],
				'field'    => 'slug',
				'terms'    => is_array( $input['value'] ) ? $input['value'] : [ $input['value'] ],
			];
		}

		return $args;
	}


}
