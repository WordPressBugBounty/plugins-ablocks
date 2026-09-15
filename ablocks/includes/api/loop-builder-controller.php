<?php

namespace ABlocks\API;

use WP_REST_Server;
use WP_REST_Response;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LoopBuilderController {

	public function __construct() {
		add_action( 'rest_api_init', [ $this, 'register_routes' ] );
	}

	public function register_routes() {

		register_rest_route(
			ABLOCKS_REST_NAMESPACE,
			'/loop-builder',
			[
				'methods'  => WP_REST_Server::READABLE,
				'callback' => [ $this, 'render_loop_builder' ],
				'permission_callback' => '__return_true',
				'args' => [
					'loop_builder_block_id' => [
						'required' => true,
						'type'     => 'string',
						'sanitize_callback' => 'sanitize_text_field',
					],
					'loop_template_block_id' => [
						'required' => true,
						'type'     => 'string',
						'sanitize_callback' => 'sanitize_text_field',
					],
					'taxonomy' => [
						'required' => false,
						'type'     => 'string',
						'sanitize_callback' => 'sanitize_key',
					],
					'post_id' => [
						'required' => false,
						'type'     => 'integer',
						'sanitize_callback' => 'absint',
					],
					'term_id' => [
						'required' => false,
						'type'     => 'integer',
						'sanitize_callback' => 'absint',
					],
					'page' => [
						'required' => false,
						'type'     => 'integer',
						'default'  => 1,
						'sanitize_callback' => 'absint',
					],
					'is_archive' => [
						'required' => false,
						'type'     => 'boolean',
						'default'  => false,
					],
					'archive_post_type' => [
						'required' => false,
						'type'     => 'string',
						'sanitize_callback' => 'sanitize_key',
					],
				],
			]
		);
	}

	public function render_loop_builder( $payload ) {

		$post_id                = $payload['post_id'];
		$loop_builder_block_id  = $payload['loop_builder_block_id'];
		$loop_template_block_id = $payload['loop_template_block_id'];
		$taxonomy               = $payload['taxonomy'];
		$term_id                = (int) $payload['term_id'];
		$page                   = (int) ( isset( $payload['page'] ) ? $payload['page'] : 1 );
		$is_archive             = (bool) ( isset( $payload['is_archive'] ) ? $payload['is_archive'] : false );

		// This route is public, so the request may only narrow the loop to a
		// post type or taxonomy the site already exposes to visitors. sanitize_key
		// fixes the spelling, not the visibility: without this an anonymous
		// request could point an archive loop at an internal post type.
		if ( ! empty( $payload['archive_post_type'] ) && ! is_post_type_viewable( (string) $payload['archive_post_type'] ) ) {
			return $this->empty_response( $term_id, $post_id, 400 );
		}
		if ( ! empty( $taxonomy ) && ! is_taxonomy_viewable( (string) $taxonomy ) ) {
			return $this->empty_response( $term_id, $post_id, 400 );
		}

		/* ---------------- ARCHIVE LOGIC (UNCHANGED) ---------------- */

		if ( $is_archive ) {

			$template_slug = '';
			if ( ! empty( $payload['archive_post_type'] ) ) {
				$template_slug = 'archive-' . $payload['archive_post_type'];

			} elseif ( ! empty( $payload['taxonomy'] ) ) {

				if ( ! empty( $payload['term_slug'] ) ) {
					$template_slug = 'archive-' . $payload['taxonomy'] . '-' . $payload['term_slug'];
				} else {
					$template_slug = 'archive-' . $payload['taxonomy'];
				}
			}

			$theme_slug = wp_get_theme()->get_stylesheet();

			$template_posts = get_posts([
				'post_type'   => 'wp_template',
				'post_status' => 'publish',
				'numberposts' => 1,
				'name'        => $template_slug,
				'tax_query'   => [
					[
						'taxonomy' => 'wp_theme',
						'field'    => 'slug',
						'terms'    => $theme_slug,
					],
				],
			]);

			$template_post = current( $template_posts );
			$blocks        = $template_post ? parse_blocks( $template_post->post_content ) : [];

		} else {

			$post   = get_post( $post_id );
			$blocks = $this->is_post_readable( $post ) ? parse_blocks( $post->post_content ) : [];
		}//end if

		// The loop may sit inside a synced pattern or template part referenced
		// from the content, or in the theme template rendered around the post.
		$loop_builder_block = $this->find_block( $blocks, $loop_builder_block_id, 'ablocks/loop-builder' );
		if ( ! $loop_builder_block ) {
			$loop_builder_block = $this->find_block_in_theme_templates( $loop_builder_block_id, 'ablocks/loop-builder' );
		}

		if ( ! $loop_builder_block ) {
			return $this->empty_response( $term_id, $post_id, 404 );
		}

		$block_data = [ 'parentAttributes' => $loop_builder_block['attrs'] ];
		$blocks     = [ $loop_builder_block ];

		/* ---------------- QUERY LOGIC (UNCHANGED) ---------------- */

		$query_vars = isset( $block_data['parentAttributes']['query'] )
			? $this->convert_to_wp_query_args( $block_data['parentAttributes']['query'] )
			: [
				'post_type'      => 'post',
				'post_status'    => 'publish',
				'posts_per_page' => get_option( 'posts_per_page' ),
			];

		if ( $is_archive && ! empty( $payload['archive_post_type'] ) ) {
			$query_vars['post_type'] = $payload['archive_post_type'];
		}

		// "Load more" asks for page N and receives the first N pages at once, so
		// the request's page number multiplies the stored page size. It comes
		// from an anonymous request, so bound the result: at most
		// `ablocks/loop_builder/max_posts_per_request` posts (never fewer than
		// one stored page), however large the page number.
		$per_page  = (int) $query_vars['posts_per_page'];
		$max_posts = (int) apply_filters( 'ablocks/loop_builder/max_posts_per_request', 100, $block_data['parentAttributes'] );
		if ( $per_page < 1 ) {
			// A loop set to show every post still gets the bound.
			$query_vars['posts_per_page'] = max( 1, $max_posts );
		} else {
			$max_posts                    = max( $per_page, $max_posts );
			$page                         = max( 1, min( $page, (int) ceil( $max_posts / $per_page ) ) );
			$query_vars['posts_per_page'] = min( $per_page * $page, $max_posts );
		}

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

		$updated_blocks = $this->update_loop_builder_query(
			$blocks,
			$loop_template_block_id,
			$query_vars,
			$term_id
		);

		$html = '';

		foreach ( $updated_blocks as $block ) {
			$html .= serialize_block( $block );
		}

		// Render the fragment the way block templates are rendered. Running the
		// already-rendered markup through the_content re-applied wpautop (stray
		// <p> tags) and let other plugins append their post-content extras.
		$rendered = do_shortcode( shortcode_unautop( $html ) );
		$rendered = do_blocks( $rendered );
		$rendered = wptexturize( $rendered );
		$rendered = convert_smilies( $rendered );
		$rendered = wp_filter_content_tags( $rendered, 'template' );

		/* ---------------- REST RESPONSE (ONLY CHANGE) ---------------- */

		return new WP_REST_Response(
			[
				'success' => true,
				'data' => [
					'html'    => $rendered,
					'term_id' => $term_id,
					'post_id' => $post_id,
				],
			],
			200
		);
	}

	/**
	 * The response for a request that cannot be rendered.
	 */
	private function empty_response( $term_id, $post_id, $status ) {
		return new WP_REST_Response(
			[
				'success' => false,
				'data'    => [
					'html'    => '',
					'term_id' => $term_id,
					'post_id' => $post_id,
				],
			],
			$status
		);
	}

	/**
	 * Only published, non-password-protected content is public; anything else
	 * needs the current user to be able to read it.
	 */
	private function is_post_readable( $post ) {
		if ( ! $post instanceof \WP_Post ) {
			return false;
		}
		if ( 'publish' === $post->post_status && ! post_password_required( $post ) ) {
			return true;
		}
		return current_user_can( 'read_post', $post->ID );
	}

	/**
	 * Find a block by name + block_id, following synced pattern references
	 * (core/block) and template parts (core/template-part).
	 */
	private function find_block( array $blocks, $block_id, $block_name, array $visited = [] ) {
		foreach ( $blocks as $block ) {
			$name = isset( $block['blockName'] ) ? $block['blockName'] : '';

			if ( $name === $block_name && ( isset( $block['attrs']['block_id'] ) ? $block['attrs']['block_id'] : '' ) === $block_id ) {
				return $block;
			}

			if ( ! empty( $block['innerBlocks'] ) ) {
				$found = $this->find_block( $block['innerBlocks'], $block_id, $block_name, $visited );
				if ( $found ) {
					return $found;
				}
			}

			$ref_key = '';
			$content = null;
			if ( 'core/block' === $name && ! empty( $block['attrs']['ref'] ) ) {
				$ref_key = 'wp_block:' . (int) $block['attrs']['ref'];
				if ( ! isset( $visited[ $ref_key ] ) ) {
					$ref_post = get_post( (int) $block['attrs']['ref'] );
					if ( $ref_post && 'wp_block' === $ref_post->post_type && $this->is_post_readable( $ref_post ) ) {
						$content = $ref_post->post_content;
					}
				}
			} elseif ( 'core/template-part' === $name && ! empty( $block['attrs']['slug'] ) ) {
				$theme   = ! empty( $block['attrs']['theme'] ) ? $block['attrs']['theme'] : get_stylesheet();
				$ref_key = 'wp_template_part:' . $theme . '//' . $block['attrs']['slug'];
				if ( ! isset( $visited[ $ref_key ] ) ) {
					$part = get_block_template( $theme . '//' . $block['attrs']['slug'], 'wp_template_part' );
					if ( $part && 'publish' === $part->status ) {
						$content = $part->content;
					}
				}
			}

			if ( is_string( $content ) && '' !== $content ) {
				$visited[ $ref_key ] = true;
				$found = $this->find_block( parse_blocks( $content ), $block_id, $block_name, $visited );
				if ( $found ) {
					return $found;
				}
			}
		}//end foreach
		return null;
	}

	/**
	 * Fallback for loops placed in the active theme's templates or template
	 * parts rather than in the post content itself.
	 */
	private function find_block_in_theme_templates( $block_id, $block_name ) {
		foreach ( [ 'wp_template', 'wp_template_part' ] as $template_type ) {
			foreach ( get_block_templates( [], $template_type ) as $template ) {
				$content = (string) $template->content;
				if (
					'publish' !== $template->status ||
					( false === strpos( $content, $block_id ) && false === strpos( $content, '<!-- wp:block ' ) && false === strpos( $content, '<!-- wp:template-part ' ) )
				) {
					continue;
				}
				$found = $this->find_block( parse_blocks( $content ), $block_id, $block_name );
				if ( $found ) {
					return $found;
				}
			}
		}
		return null;
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
		$args = [
			'post_status'    => 'publish',
		];

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
