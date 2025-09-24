<?php
namespace ABlocks\Blocks\Breadcrumb;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ABlocks\Classes\BlockBaseAbstract;
use ABlocks\Classes\CssGeneratorV2;
use ABlocks\Helper;
use ABlocks\Controls\Color;
use ABlocks\Controls\Typography;
use ABlocks\Controls\Alignment;
use ABlocks\Controls\Dimensions;

class Block extends BlockBaseAbstract {
	protected $block_name = 'breadcrumb';

	public function build_css( $attributes ) {
		$css_generator = new CssGeneratorV2( $attributes, $this->block_name );

		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-breadcrumbs',
			$this->get_wrapper_css( $attributes ),
			$this->get_wrapper_css( $attributes, 'Tablet' ),
			$this->get_wrapper_css( $attributes, 'Mobile' )
		);

		// Normal link color
		$css_generator->add_class_styles(
			'{{WRAPPER}} div.ablocks-breadcrumbs span a',
			$this->get_breadcrumb_normal_title_css( $attributes ),
			$this->get_breadcrumb_normal_title_css( $attributes, 'Tablet' ),
			$this->get_breadcrumb_normal_title_css( $attributes, 'Mobile' )
		);

		// Hover link color
		$css_generator->add_class_styles(
			'{{WRAPPER}} div.ablocks-breadcrumbs span a:hover',
			$this->get_breadcrumb_hover_title_css( $attributes ),
			$this->get_breadcrumb_hover_title_css( $attributes, 'Tablet' ),
			$this->get_breadcrumb_hover_title_css( $attributes, 'Mobile' )
		);

		// Title (typography + color + direction)
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-breadcrumbs__item',
			$this->get_breadcrumb_title_css( $attributes ),
			$this->get_breadcrumb_title_css( $attributes, 'Tablet' ),
			$this->get_breadcrumb_title_css( $attributes, 'Mobile' )
		);

		// Separator (color + font-size)
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-breadcrumbs__separator',
			$this->get_breadcrumb_separator_css( $attributes ),
			$this->get_breadcrumb_separator_css( $attributes, 'Tablet' ),
			$this->get_breadcrumb_separator_css( $attributes, 'Mobile' )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-breadcrumbs___before-image',
			$this->get_breadcrumb_before_text_image_css( $attributes ),
			$this->get_breadcrumb_before_text_image_css( $attributes, 'Tablet' ),
			$this->get_breadcrumb_before_text_image_css( $attributes, 'Mobile' )
		);

		return $css_generator->generate_css();

	}


	public function get_wrapper_css( $attributes, $device = '' ) {
		$css = [];

		if ( ! empty( $attributes['breadcrumbSpaceBetween'] ) ) {
			$css['gap'] = $attributes['breadcrumbSpaceBetween'] . 'px';
		}

		if ( isset( $attributes['positionBreadcrumb'][ 'value' . $device ] ) ) {
			$css['justify-content'] = $attributes['positionBreadcrumb'][ 'value' . $device ];
		}
		return array_merge(
			$css,
		);

	}

	public function get_breadcrumb_title_css( $attributes, $device = '' ) {
		$css = [];

		if ( ! empty( $attributes['breadcrumbTitlecolor'] ) ) {
			$css['color'] = $attributes['breadcrumbTitlecolor'];
		}

		$dir     = isset( $attributes['taxonomyTitleDirection'] ) ? $attributes['taxonomyTitleDirection'] : null;
		$dirCss  = ! is_null( $dir ) ? get_alignment_css( $dir, 'justify-content', $device ) : [];
		if ( ! empty( $attributes['breadcrumbItemBackground'] ) ) {
			$css['background-color'] = $attributes['breadcrumbItemBackground'];
		}
		return array_merge(
			$css, (array) $dirCss,
			Typography::get_css( $attributes['breadcrumbTitleTypography'] ?? [], '', $device ),
			Dimensions::get_css( $attributes['breadcrumbItemPadding'] ?? [], 'padding', $device ),
			Dimensions::get_css( $attributes['BreadcrumbBorderRadius'] ?? [], 'border-radius', $device ),
		);
	}

	public function get_breadcrumb_normal_title_css( $attributes, $device = '' ) {
		$css = [];

		if ( ! empty( $attributes['breadcrumbLinkcolor'] ) ) {
			$css['color'] = $attributes['breadcrumbLinkcolor'];
		}

		return $css;
	}

	public function get_breadcrumb_hover_title_css( $attributes, $device = '' ) {
		$css = [];

		if ( ! empty( $attributes['breadcrumbHoverLinkcolor'] ) ) {
			$css['color'] = $attributes['breadcrumbHoverLinkcolor'];
		}

		return $css;
	}

	public function get_breadcrumb_separator_css( $attributes, $device = '' ) {
		$css = [];

		if ( ! empty( $attributes['breadcrumbseparatorcolor'] ) ) {
			$css['color'] = $attributes['breadcrumbseparatorcolor'];
		}

		if ( ! empty( $attributes['breadcrumbseparsize'] ) ) {
			// IMPORTANT: CSS property is kebab-case
			$css['font-size'] = $attributes['breadcrumbseparsize'] . 'px';
		}

		return $css;
	}
	public function get_breadcrumb_before_text_image_css( $attributes, $device = '' ) {
		$css = [];

		if ( ! empty( $attributes['beforeBreadcrumbBackgroundcolor'] ) ) {
			$css['background-color'] = $attributes['beforeBreadcrumbBackgroundcolor'];
		}

		return array_merge(
			$css,
			Dimensions::get_css( $attributes['beforeBreadcrumbPaddingcolor'] ?? [], 'padding', $device ),
			Dimensions::get_css( $attributes['beforeBreadcrumbBorderRadius'] ?? [], 'border-radius', $device ),
		);
	}


	public function render( $attributes = [] ) {
		$breadcrumb_normalize     = $this->normalize( $attributes );
		$breadcrumb_items = $this->build_items( $breadcrumb_normalize );
		if ( empty( $breadcrumb_items ) ) {
			return;
		}
		$breadcrumb_items = $this->limit_items( $breadcrumb_items, (int) $breadcrumb_normalize['max'] );
		echo $this->render_html_breadcrumbs( $breadcrumb_items, $breadcrumb_normalize );
	}

	/**
	 * Defaults
	 */
	private function normalize( $attributes ) {
		$separator = isset( $attributes['SeparatorChange'] ) && $attributes['SeparatorChange'] !== ''
				? (string) $attributes['SeparatorChange']
				: '>';

		$home_text = ( isset( $attributes['homeBreadcrumbs'] ) && $attributes['homeBreadcrumbs'] !== '' )
				? (string) $attributes['homeBreadcrumbs']
				: __( 'Home', 'ablocks' );

		$beforeBreadcrumbImage = ( isset( $attributes['beforeBreadcrumbImage'] ) && $attributes['beforeBreadcrumbImage'] !== '' )
				? (string) $attributes['beforeBreadcrumbImage']
				: __( '', 'ablocks' );

		$beforeBreadcrumbText = ( isset( $attributes['beforeBreadcrumbText'] ) && $attributes['beforeBreadcrumbText'] !== '' )
				? (string) $attributes['beforeBreadcrumbText']
				: __( 'Before Text', 'ablocks' );

		$beforeBreadcrumbTextImage = ( isset( $attributes['beforeBreadcrumbTextImage'] ) && $attributes['beforeBreadcrumbTextImage'] !== '' )
				? (string) $attributes['beforeBreadcrumbTextImage']
				: __( '', 'ablocks' );

		$beforeTextImage = ( isset( $attributes['beforeTextImage'] ) && $attributes['beforeTextImage'] !== '' )
				? (string) $attributes['beforeTextImage']
				: __( '', 'ablocks' );

		$beforeSeparator = ( isset( $attributes['beforeSeparator'] ) && $attributes['beforeSeparator'] !== '' )
				? (string) $attributes['beforeSeparator']
				: __( '', 'ablocks' );

		return wp_parse_args( (array) $attributes, [
			'separator'       => $separator,
			'home_text'       => $home_text,
			'before_breadcrumb_image'       => $beforeBreadcrumbImage,
			'before_breadcrumb_text'       => $beforeBreadcrumbText,
			'before_breadcrumb_text_image'       => $beforeBreadcrumbTextImage,
			'before_text_image'       => $beforeTextImage,
			'before_separator'       => $beforeSeparator,
			'include_current' => true,
			'tax'             => '',
			'max'             => 0,
		] );
	}

	/**
	 * Detect editor (post/site editor, AJAX, REST block render)
	 */
	private function in_editor() : bool {
		// More precise REST check: block renderer routes
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			$uri = $_SERVER['REQUEST_URI'] ?? '';
			if ( strpos( $uri, '/wp/v2/block-renderer' ) !== false
					|| strpos( $uri, '/wp-block-editor/v1/block-renderer' ) !== false ) {
				return true;
			}
		}

		if ( is_admin() ) {
			if ( function_exists( 'get_current_screen' ) ) {
				$scr = get_current_screen();
				if ( $scr ) {
					if ( ! empty( $scr->is_block_editor ) ) {
						return true;
					}
					if ( isset( $scr->base ) && $scr->base === 'site-editor' ) {
						return true;
					}
				}
			}
			if ( function_exists( 'wp_doing_ajax' ) && wp_doing_ajax() ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Build breadcrumb items (editor-aware)
	 * Each: ['label' => string, 'url' => string|false]
	 */
	private function build_items( array $breadcrumb_normalize ) {
		if ( $this->in_editor() ) {
			return $this->build_items_editor( $breadcrumb_normalize );
		}
		$breadcrumb_items = [];
		$breadcrumb_before_image = [
			'label' => (string) $breadcrumb_normalize['before_breadcrumb_image'],
			'url' => home_url( '>' )
		];
		// Front page
		if ( is_front_page() ) {
			$breadcrumb_items[] = [
				'label' => (string) $breadcrumb_normalize['home_text'],
				'url' => false
			];
			return $breadcrumb_items;
		}

		// Home
		$breadcrumb_items[] = [
			'label' => (string) $breadcrumb_normalize['home_text'],
			'url' => home_url( '>' )
		];
		// Home

		// Posts page (static front)
		if ( is_home() && ! is_front_page() ) {
			$pid = (int) get_option( 'page_for_posts' );
			$breadcrumb_items[] = [
				'label' => $pid ? get_the_title( $pid ) : __( 'Blog', 'ablocks' ),
				'url'   => false,
			];
			return $breadcrumb_items;
		}

		// Singular
		if ( is_singular() ) {
			global $post;

			if ( ! $post ) {
				if ( ! empty( $breadcrumb_normalize['include_current'] ) ) {
					$breadcrumb_items[] = [
						'label' => $this->current_title_safe(),
						'url' => false
					];
				}
				return $breadcrumb_items;
			}

			$breadcrumb_pt  = get_post_type( $post );
			$breadcrumb_pto = $breadcrumb_pt ? get_post_type_object( $breadcrumb_pt ) : null;

			// WooCommerce: prepend Shop for product
			$is_wc_product = ( 'product' === $breadcrumb_pt ) && function_exists( 'wc_get_page_id' ) && is_singular( 'product' );
			if ( $is_wc_product ) {
				$shop_id = wc_get_page_id( 'shop' );
				if ( $shop_id && 'publish' === get_post_status( $shop_id ) ) {
					$breadcrumb_items[] = [
						'label' => get_the_title( $shop_id ),
						'url' => get_permalink( $shop_id )
					];
				}
			}

			// Post type label/archive (avoid for product since Shop already added)
			if ( $breadcrumb_pto && ! $is_wc_product ) {
				if ( ! empty( $breadcrumb_pto->has_archive ) ) {
					$breadcrumb_items[] = [
						'label' => $breadcrumb_pto->labels->name,
						'url' => get_post_type_archive_link( $breadcrumb_pt )
					];
				} elseif ( ! in_array( $breadcrumb_pt, [ 'post', 'page' ], true ) ) {
					$breadcrumb_items[] = [
						'label' => $breadcrumb_pto->labels->name,
						'url' => false
					];
				}
			}

			// Hierarchical (page / hierarchical CPT)
			if ( is_page() || ( $breadcrumb_pto && is_post_type_hierarchical( $breadcrumb_pt ) ) ) {
				foreach ( array_reverse( get_post_ancestors( $post ) ) as $aid ) {
					if ( (int) $aid === (int) get_option( 'page_on_front' ) ) {
						continue; }
					$breadcrumb_items[] = [
						'label' => get_the_title( $aid ),
						'url' => get_permalink( $aid )
					];
				}
			} else {
				// Non-hierarchical: taxonomy trail or archive fallback
				$breadcrumb_tax = $this->preferred_tax( $breadcrumb_pt, $breadcrumb_normalize['tax'] );
				if ( $breadcrumb_tax ) {
					$primary = $this->primary_term( $post->ID, $breadcrumb_tax );
					if ( $primary ) {
						foreach ( $this->term_chain( $primary ) as $breadcrumb_t ) {
							$breadcrumb_items[] = [
								'label' => $breadcrumb_t->name,
								'url' => get_term_link( $breadcrumb_t )
							];
						}
					}
				} elseif ( $breadcrumb_pto && ! empty( $breadcrumb_pto->has_archive ) && ! $is_wc_product ) {
					$breadcrumb_items[] = [
						'label' => $breadcrumb_pto->labels->name,
						'url' => get_post_type_archive_link( $breadcrumb_pt )
					];
				}
			}//end if

			if ( ! empty( $breadcrumb_normalize['include_current'] ) ) {
				$breadcrumb_items[] = [
					'label' => $this->current_title_safe( $post ),
					'url' => false
				];
			}
			return $breadcrumb_items;
		}//end if

		// Tax archives
		if ( is_category() || is_tag() || is_tax() ) {
			$term = get_queried_object();
			if ( $term && ! is_wp_error( $term ) ) {
				if ( $term->parent ) {
					foreach ( array_reverse( get_ancestors( $term->term_id, $term->taxonomy, 'taxonomy' ) ) as $id ) {
						$breadcrumb_t = get_term( $id, $term->taxonomy );
						if ( $breadcrumb_t && ! is_wp_error( $breadcrumb_t ) ) {
							$breadcrumb_items[] = [
								'label' => $breadcrumb_t->name,
								'url' => get_term_link( $breadcrumb_t )
							];
						}
					}
				}
				$breadcrumb_items[] = [
					'label' => single_term_title( '', false ),
					'url' => false
				];
				return $breadcrumb_items;
			}
		}//end if

		// Post type archive
		if ( is_post_type_archive() ) {
			$breadcrumb_pt = get_query_var( 'post_type' );
			if ( is_array( $breadcrumb_pt ) ) {
				$breadcrumb_pt = reset( $breadcrumb_pt ); }
			$po = $breadcrumb_pt ? get_post_type_object( $breadcrumb_pt ) : null;
			if ( $po ) {
				$breadcrumb_items[] = [
					'label' => $po->labels->name,
					'url' => false
				];
				return $breadcrumb_items;
			}
		}

		// Author
		if ( is_author() ) {
			$breadcrumb_u = get_queried_object();
			if ( $breadcrumb_u ) {
				$breadcrumb_items[] = [
					'label' => sprintf( __( 'Author: %s', 'ablocks' ), $breadcrumb_u->display_name ),
					'url' => false
				];
				return $breadcrumb_items;
			}
		}

		// Date
		if ( is_year() || is_month() || is_day() ) {
			$breadcrumb_items[] = [
				'label' => get_the_date( is_year() ? 'Y' : ( is_month() ? 'F Y' : 'F j, Y' ) ),
				'url' => false
			];
			return $breadcrumb_items;
		}

		// Search
		if ( is_search() ) {
			$breadcrumb_items[] = [
				'label' => sprintf( __( 'Search: %s', 'ablocks' ), get_search_query() ),
				'url' => false
			];
			return $breadcrumb_items;
		}

		// 404
		if ( is_404() ) {
			$breadcrumb_items[] = [
				'label' => __( '404 Not Found', 'ablocks' ),
				'url' => false
			];
			return $breadcrumb_items;
		}

		return $breadcrumb_items;
	}

	/**
	 * Editor builder (mirrors frontend; robust current title)
	 */
	private function build_items_editor( array $breadcrumb_normalize ) : array {
		$breadcrumb_items   = [];
		$home_label = (string) $breadcrumb_normalize['home_text']; // attributes → fallback "Home"
		$breadcrumb_items[] = [
			'label' => $home_label,
			'url' => home_url( '>' )
		];

		global $post;
		if ( $post instanceof WP_Post ) {
			$breadcrumb_pt  = get_post_type( $post );
			$breadcrumb_pto = $breadcrumb_pt ? get_post_type_object( $breadcrumb_pt ) : null;

			if ( is_post_type_hierarchical( $breadcrumb_pt ) ) {
				foreach ( array_reverse( get_post_ancestors( $post ) ) as $aid ) {
					if ( (int) $aid === (int) get_option( 'page_on_front' ) ) {
						continue; }
					$breadcrumb_items[] = [
						'label' => get_the_title( $aid ),
						'url' => get_permalink( $aid )
					];
				}
			} else {
				if ( $breadcrumb_pto ) {
					if ( ! empty( $breadcrumb_pto->has_archive ) ) {
						$breadcrumb_items[] = [
							'label' => $breadcrumb_pto->labels->name,
							'url' => get_post_type_archive_link( $breadcrumb_pt )
						];
					} elseif ( ! in_array( $breadcrumb_pt, [ 'post', 'page' ], true ) ) {
						$breadcrumb_items[] = [
							'label' => $breadcrumb_pto->labels->name,
							'url' => false
						];
					}
				}
				$breadcrumb_tax = $this->preferred_tax( $breadcrumb_pt, $breadcrumb_normalize['tax'] );
				if ( $breadcrumb_tax ) {
					$primary = $this->primary_term( $post->ID, $breadcrumb_tax );
					if ( $primary ) {
						foreach ( $this->term_chain( $primary ) as $breadcrumb_t ) {
							$breadcrumb_items[] = [
								'label' => $breadcrumb_t->name,
								'url' => get_term_link( $breadcrumb_t )
							];
						}
					}
				}
			}//end if

			if ( ! empty( $breadcrumb_normalize['include_current'] ) ) {
				$breadcrumb_items[] = [
					'label' => $this->current_title_safe( $post ),
					'url' => false
				];
			}
			return $breadcrumb_items;
		}//end if

		// No $post in editor (e.g., site editor template) → sample trail
		$breadcrumb_items[] = [
			'label' => __( 'Template', 'ablocks' ),
			'url' => home_url( '/Template/' )
		];
		if ( ! empty( $breadcrumb_normalize['include_current'] ) ) {
			$breadcrumb_items[] = [
				'label' => __( 'Currents Page', 'ablocks' ),
				'url' => false
			];
		}
		return $breadcrumb_items;
	}

	/**
	 * Safe current title (fallbacks when get_the_title() empty in editor)
	 */
	private function current_title_safe( $p = null ) : string {
		if ( $p instanceof WP_Post ) {
			$breadcrumb_t = get_the_title( $p );
			if ( $breadcrumb_t === '' ) {
				$breadcrumb_t = $p->post_title;
			}
			if ( $breadcrumb_t === '' ) {
				$breadcrumb_t = __( '(no title)', 'ablocks' );
			}
			return $breadcrumb_t;
		}
		$breadcrumb_t = single_post_title( '', false );
		if ( $breadcrumb_t === '' ) {
			$breadcrumb_t = __( '(no title)', 'ablocks' );
		}
		return $breadcrumb_t;
	}

	/**
	 * Preferred taxonomy
	 */
	private function preferred_tax( $post_type, $preferred = '' ) {
		if ( $preferred && taxonomy_exists( $preferred ) ) {
			return $preferred;
		}
		$taxes = get_object_taxonomies( $post_type, 'objects' );
		if ( empty( $taxes ) ) {
			return false;
		}
		foreach ( $taxes as $breadcrumb_t ) {
			if ( $breadcrumb_t->public && $breadcrumb_t->hierarchical ) {
				return $breadcrumb_t->name;
			}
		}
		foreach ( $taxes as $breadcrumb_t ) {
			if ( $breadcrumb_t->public ) {
				return $breadcrumb_t->name;
			}
		}
		return false;
	}

	/**
	 * Primary term (Yoast) or first term
	 */
	private function primary_term( $post_id, $taxonomy ) {
		if ( class_exists( 'WPSEO_Primary_Term' ) ) {
			$yoast = new \WPSEO_Primary_Term( $taxonomy, $post_id );
			$id    = (int) $yoast->get_primary_term();
			if ( $id ) {
				$breadcrumb_t = get_term( $id, $taxonomy );
				if ( $breadcrumb_t && ! is_wp_error( $breadcrumb_t ) ) {
					return $breadcrumb_t;
				}
			}
		}
		$terms = get_the_terms( $post_id, $taxonomy );
		return ( $terms && ! is_wp_error( $terms ) ) ? array_shift( $terms ) : null;
	}

	/**
	 * Term ancestors + self
	 */
	private function term_chain( $term ) {
		$out = [];
		if ( $term && $term->parent ) {
			$ids = array_reverse( get_ancestors( $term->term_id, $term->taxonomy, 'taxonomy' ) );
			foreach ( $ids as $id ) {
				$breadcrumb_t = get_term( $id, $term->taxonomy );
				if ( $breadcrumb_t && ! is_wp_error( $breadcrumb_t ) ) {
					$out[] = $breadcrumb_t;
				}
			}
		}
		if ( $term ) {
			$out[] = $term;
		}
		return $out;
	}

	/**
	 * Tail limit
	 */
	private function limit_items( array $breadcrumb_items, int $max ) {
		if ( $max > 0 && count( $breadcrumb_items ) > $max ) {
			return array_slice( $breadcrumb_items, - $max );
		}
		return $breadcrumb_items;
	}

	/**
	 * Breadcrumbs-style HTML (matches Breadcrumbs classes; includes microdata)
	 */
	private function render_html_breadcrumbs( array $breadcrumb_items, array $breadcrumb_normalize ) {

		ob_start();
		?>
		<div aria-label="<?php esc_attr_e( 'breadcrumb', 'ablocks' ); ?>"
			 class="ablocks-breadcrumbs">

			<?php

			if ( $breadcrumb_normalize['beforeTextImage'] == 'true' ) : ?>
				<?php if ( $breadcrumb_normalize['beforeBreadcrumbTextImage'] == 'text' ) : ?>
					<span class="ablocks-breadcrumbs__before-text ablocks-breadcrumbs__item">
						<?php echo esc_html( $breadcrumb_normalize['beforeBreadcrumbText'] ); ?>
					</span>
					<?php if ( $breadcrumb_normalize['beforeSeparator'] == 'true' ) : ?>
					<span class="ablocks-breadcrumbs__separator"><?php echo esc_html( $breadcrumb_normalize['separator'] ); ?></span>
					<?php endif; ?>
				<?php elseif ( $breadcrumb_normalize['beforeBreadcrumbTextImage'] == 'image' ) : ?>
					<span class="ablocks-breadcrumbs___before-image"><img src="<?php echo $breadcrumb_normalize['before_breadcrumb_image']; ?>" alt="">
					</span>
					<?php if ( $breadcrumb_normalize['beforeSeparator'] == 'true' ) : ?>
						<span class="ablocks-breadcrumbs__separator"><?php echo esc_html( $breadcrumb_normalize['separator'] ); ?></span>
					<?php endif; ?>
				<?php endif; ?>
			<?php endif; ?>

			<?php foreach ( $breadcrumb_items as $i => $breadcrumb_it ) : ?>
								<span class="ablocks-breadcrumbs__item" >

					<?php if ( ! empty( $breadcrumb_it['url'] ) ) : ?>
						<a  href="<?php echo esc_url( $breadcrumb_it['url'] ); ?>">
							<span><?php echo esc_html( $breadcrumb_it['label'] ); ?></span>
						</a>
					<?php else : ?>
						<span  class="ablocks-breadcrumbs__item-current"><?php echo esc_html( $breadcrumb_it['label'] ); ?></span>
					<?php endif; ?>
					<meta content="<?php echo (int) ( $i + 1 ); ?>" />
				</span>
				<?php if ( $i < count( $breadcrumb_items ) - 1 ) : ?>
					<span class="ablocks-breadcrumbs__separator"><?php echo esc_html( $breadcrumb_normalize['separator'] ); ?></span>
				<?php endif; ?>
			<?php endforeach; ?>
		</div>
		<?php
		return trim( ob_get_clean() );
	}


	/**
	 * Block → PHP SSR entry (use in render_callback)
	 */
	public function render_block_content( $attributes, $content = '', $block_instance = null ) {
		$breadcrumb_normalize = $this->normalize( $attributes );

		// --- Ensure correct $post in editor/preview (REST) ---
		$restore_post = null;

		if ( $block_instance && ! empty( $block_instance->context['postId'] ) ) {
			$maybe = get_post( (int) $block_instance->context['postId'] );

			if ( $maybe instanceof WP_Post ) {
				global $post;
				$restore_post = $post ?? null;
				$post = $maybe;
				setup_postdata( $post );
			}
		}

		$breadcrumb_items = $this->build_items( $breadcrumb_normalize );

		if ( $restore_post !== null ) {
			wp_reset_postdata();
			global $post;
			$post = $restore_post;
		}

		if ( empty( $breadcrumb_items ) ) {
			return '';
		}
		$breadcrumb_items = $this->limit_items( $breadcrumb_items, (int) $breadcrumb_normalize['max'] );

		return $this->render_html_breadcrumbs( $breadcrumb_items, $breadcrumb_normalize );
	}

	/**
	 * Public helper for theme templates
	 */

}
