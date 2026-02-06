<?php
namespace ABlocks;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ABlocks\Classes\FileUpload;
use ABlocks\Classes\AssetsGenerator;
use ABlocks\Classes\RegisterScripts;
use ABlocks\Classes\GlobalCssGenerator;
use ABlocks\Classes\FontLoadLocally;
use ABlocks\Admin\Menu;
use ABlocks\Helper;

class Assets {
	public $current_page_template_part = [];
	public $current_page_blocks = [];
	private $FileUpload;
	private $current_page_slug = '';
	private $theme_builder_locations = [];
	public static function init() {
		$self = new self();
		$self->FileUpload = new FileUpload();
		$self->current_page_slug = '';
		add_action( 'admin_enqueue_scripts', [ $self, 'dashboard_scripts' ], 10 );
		add_action( 'admin_enqueue_scripts', [ $self, 'demo_importer_scripts' ], 10 );
		add_action( 'enqueue_block_assets', [ $self, 'block_editor_assets' ] );
		add_action( 'enqueue_block_assets', [ $self, 'register_scripts' ] );
		add_action( 'wp_enqueue_scripts', [ $self, 'front_end_google_fonts' ], 999 );

		add_action( 'wp_enqueue_scripts', [ $self, 'enqueue_frontend_assets' ], 99 );
		// Global CSS
		add_action( 'wp_enqueue_block_assets', [ $self, 'global_css_variable' ] );
		add_action( 'wp_enqueue_scripts', [ $self, 'global_css_variable' ] );
		add_action( 'enqueue_block_editor_assets', [ $self, 'global_css_variable' ] );
		add_action( 'enqueue_block_editor_assets', [ $self, 'add_editor_inline_css' ] );

		// Detect page
		add_action( 'wp', array( $self, 'detect_page' ) );

		if ( ! is_admin() && Helper::is_enabled_assets_generation() ) {
			if ( Helper::is_fse_theme() ) {
				add_filter( 'pre_render_block', [ $self, 'set_current_page_template_part' ], 5, 2 );
				add_action( 'ablocks/before_enqueue_frontend_scripts', [ $self, 'regenerate_missing_assets' ] );
			} else {
				add_action( 'ablocks_theme_builder_after_dispatch', [ $self, 'set_theme_builder_locations' ] );
				add_action( 'wp', [ $self, 'regenerate_missing_assets' ], 20 );
			}
		}
	}

	public function detect_page() {
		if ( is_404() ) {
			$this->current_page_slug = '404';
		} elseif ( is_search() ) {
			$this->current_page_slug = 'search';
		} elseif ( is_home() ) {
			$posts_page_id = get_option( 'page_for_posts' );
			$this->current_page_slug = $posts_page_id ? (string) $posts_page_id : 'blog';
		} elseif ( is_post_type_archive() ) {
			$this->current_page_slug = 'archive-' . get_post_type();
		} elseif ( is_category() ) {
			$term = get_queried_object();
			$this->current_page_slug = 'category-' . $term->term_id; // or use slug
		} elseif ( is_tag() ) {
			$term = get_queried_object();
			$this->current_page_slug = 'tag-' . $term->term_id;
		} elseif ( is_tax() ) {
			$term = get_queried_object();
			$this->current_page_slug = 'tax-' . $term->taxonomy . '-' . $term->term_id;
		} elseif ( is_author() ) {
			$this->current_page_slug = 'author-' . get_the_author_meta( 'ID' );
		} elseif ( is_date() ) {
			$this->current_page_slug = 'date-archive';
		} elseif ( is_singular() ) {
			$this->current_page_slug = (string) get_the_ID(); // just the post/page/custom ID
		} else {
			$this->current_page_slug = (string) get_the_ID(); // fallback to ID
		}//end if
	}

	public function get_current_archive_post_type() {
		if ( is_post_type_archive() ) {
			$post_type = get_query_var( 'post_type' );
			if ( is_array( $post_type ) ) {
				$post_type = reset( $post_type );
			}
			return $post_type;
		}
		return null;
	}

	public function get_localize_script_data() {
		return [
			'rest_url'              => esc_url_raw( rest_url() ),
			'namespace'             => ABLOCKS_PLUGIN_SLUG . '/v1/',
			'plugin_root_url'       => ABLOCKS_ROOT_URL,
			'plugin_root_path'      => ABLOCKS_ROOT_DIR_PATH,
			'admin_url'             => admin_url(),
			'site_url'              => site_url(),
			'route_path'            => wp_parse_url( admin_url(), PHP_URL_PATH ),
			'ajax_url'              => esc_url( admin_url( 'admin-ajax.php' ) ),
			'is_pro'                => (bool) Helper::is_active_ablocks_pro(),
			'is_archive' => (bool) is_archive(),
			'archive_post_type' => $this->get_current_archive_post_type(),
		];
	}

	public function get_localize_dashboard_data() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return $this->get_localize_script_data();
		}
		return array_merge($this->get_localize_script_data(), [
			'nonce'                 => wp_create_nonce( 'wp_rest' ),
			'ablocks_nonce'         => wp_create_nonce( 'ablocks_nonce' ),
		]);
	}
	public function get_localize_editor_data() {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return $this->get_localize_script_data();
		}
		return array_merge($this->get_localize_script_data(), [
			'nonce'                 => wp_create_nonce( 'wp_rest' ),
			'ablocks_nonce'         => wp_create_nonce( 'ablocks_nonce' ),
		]);
	}
	public function get_localize_frontend_data() {
		return array_merge($this->get_localize_script_data(), [
			'nonce'                 => wp_create_nonce( 'wp_rest' ),
			'ablocks_nonce'         => wp_create_nonce( 'ablocks_nonce' ),
		]);
	}

	public function get_dashboard_localize_script_data() {
		$menu = new Menu();
		$args = array(
			'menu'                  => wp_json_encode( Helper::get_admin_menu_list() ),
			'toplevel_menu_icon_url'    => $menu->get_toplevel_menu_icon_url(),
			'settings'         => [
				'default_container_width' => Helper::get_settings( 'default_container_width', 1280 ),
				'container_padding' => Helper::get_settings( 'container_padding', 10 ),
				'container_element_gap' => Helper::get_settings( 'container_element_gap', 20 ),
				'enabled_assets_file_generation' => (bool) Helper::get_settings( 'enabled_assets_file_generation', false ),
				'enabled_block_copy_paste_style' => (bool) Helper::get_settings( 'enabled_block_copy_paste_style', false ),
				'enabled_load_google_font_locally' => (bool) Helper::get_settings( 'enabled_load_google_font_locally', false ),
				'enabled_only_selected_fonts' => (bool) Helper::get_settings( 'enabled_only_selected_fonts', false ),
				'selected_fonts' => (array) Helper::get_settings( 'selected_fonts', [] ),
			],
			'is_gutenberg_editor'   => Helper::is_gutenberg_editor(),
			'is_fse_theme'          => Helper::is_fse_theme(),
			'third_party_plugin_status' => [
				'academy_lms' => Helper::is_active_academy(),
				'storeengine' => Helper::is_active_storeengine(),
				'wp_map_block' => Helper::is_active_wp_map_block(),
				'easy_content_manager' => Helper::is_active_easy_content_manager(),
			]
		);
		return apply_filters(
			'ablocks/assets/dashboard_scripts_data',
			array_merge(
				$this->get_localize_dashboard_data(),
				$args
			)
		);
	}
	public function get_editor_localize_script_data() {
		global $ablocks_blocks;
		$post_types = Helper::get_public_post_type_options();
		$args = array(
			'settings'         => [
				'default_container_width' => Helper::get_settings( 'default_container_width', 1280 ),
				'container_padding' => Helper::get_settings( 'container_padding', 10 ),
				'container_element_gap' => Helper::get_settings( 'container_element_gap', 20 ),
				'enabled_assets_file_generation' => (bool) Helper::get_settings( 'enabled_assets_file_generation', false ),
				'enabled_block_copy_paste_style' => (bool) Helper::get_settings( 'enabled_block_copy_paste_style', false ),
				'enabled_load_google_font_locally' => (bool) Helper::get_settings( 'enabled_load_google_font_locally', false ),
				'enabled_only_selected_fonts' => (bool) Helper::get_settings( 'enabled_only_selected_fonts', false ),
				'selected_fonts' => (array) Helper::get_settings( 'selected_fonts', [] ),
				'frontend_dashboard_page' => (int) Helper::get_settings( 'frontend_dashboard_page' ),
				'global_color' => (array) Helper::get_settings( 'global_color', [] ),
				'global_typography' => (array) Helper::get_settings( 'global_typography', [] ),
				'global_typography_list' => wp_list_pluck( Helper::get_settings( 'global_typography', [] ), 'value', 'id' ),
				'global_font_family_fallback' => (string) Helper::get_settings( 'global_font_family_fallback', 'Sans-serif' ),
			],
			'is_gutenberg_editor' => Helper::is_gutenberg_editor(),
			'has_required_block_attribute_migration' => get_option( 'ablocks_has_required_block_attribute_migration' ),
			'third_party_plugin_status' => [
				'academy_lms' => Helper::is_active_academy(),
				'storeengine' => Helper::is_active_storeengine(),
				'wp_map_block' => Helper::is_active_wp_map_block(),
				'quizpress' => Helper::is_active_quizpress(),
			],
			'blocks_status' => $ablocks_blocks,
			'post_types' => $post_types
		);
		return apply_filters(
			'ablocks/assets/editor_scripts_data',
			array_merge(
				$this->get_localize_editor_data(),
				$args
			)
		);
	}

	public function dashboard_scripts( $hook ) {
		$demo_config = Helper::get_theme_demo_config();
		if ( strpos( $hook, '_page_' . ABLOCKS_PLUGIN_SLUG ) !== false && strpos( $hook, $demo_config['menu_slug'] ) === false ) {
			// Remove Notices
			remove_all_actions( 'admin_notices' );
			// dequeue third party plugin assets
			add_action(
				'wp_print_scripts',
				function () {
					$isSkip = apply_filters( 'ablocks/skip_no_conflict_backend_scripts', Helper::is_dev_mode_enable() );

					if ( $isSkip ) {
						return;
					}

					global $wp_scripts;
					if ( ! $wp_scripts ) {
						return;
					}

					$pluginUrl = plugins_url();
					foreach ( $wp_scripts->queue as $script ) {
						$src = $wp_scripts->registered[ $script ]->src;
						if ( strpos( $src, $pluginUrl ) !== false && ! strpos( $src, ABLOCKS_PLUGIN_SLUG ) !== false ) {
							wp_dequeue_script( $wp_scripts->registered[ $script ]->handle );
						}
					}
				},
				1
			);

			wp_enqueue_style( 'ablocks-fonts', $this->web_fonts_url( 'Roboto:ital,wght@0,300;0,400;0,500;0,700;1,300&display=swap' ), array(), ABLOCKS_VERSION );
			wp_enqueue_style( 'ablocks-icon', ABLOCKS_ASSETS_URL . 'library/css/ablocks-icon/style.css', array( 'wp-components' ), filemtime( ABLOCKS_ASSETS_PATH . 'library/css/ablocks-icon/style.css' ), 'all' );
			wp_enqueue_style( 'ablocks-dashboard-style', ABLOCKS_ASSETS_URL . 'build/dashboard.css', array( 'wp-components' ), filemtime( ABLOCKS_ASSETS_PATH . 'build/dashboard.css' ), 'all' );

			$dependencies = include ABLOCKS_ASSETS_PATH . 'build/dashboard.asset.php';
			wp_enqueue_script(
				'ablocks-dashboard-scripts',
				ABLOCKS_ASSETS_URL . 'build/dashboard.js',
				$dependencies['dependencies'],
				$dependencies['version'],
				true
			);
			wp_localize_script( 'ablocks-dashboard-scripts', 'ABlocksGlobal', $this->get_dashboard_localize_script_data() );
			wp_set_script_translations( 'ablocks-dashboard-scripts', 'ablocks', ABLOCKS_ROOT_DIR_PATH . 'languages' );
		}//end if
	}

	public function demo_importer_scripts( $hook ) {
		$demo_config = Helper::get_theme_demo_config();
		if ( strpos( $hook, $demo_config['menu_slug'] ) !== false ) {
			// Remove Notices
			remove_all_actions( 'admin_notices' );
			// dequeue third party plugin assets
			add_action(
				'wp_print_scripts',
				function () {
					$isSkip = apply_filters( 'ablocks/skip_no_conflict_demo_importer_scripts', Helper::is_dev_mode_enable() );

					if ( $isSkip ) {
						return;
					}

					global $wp_scripts;
					if ( ! $wp_scripts ) {
						return;
					}

					$pluginUrl = plugins_url();
					foreach ( $wp_scripts->queue as $script ) {
						$src = $wp_scripts->registered[ $script ]->src;
						if ( strpos( $src, $pluginUrl ) !== false && ! strpos( $src, ABLOCKS_PLUGIN_SLUG ) !== false ) {
							wp_dequeue_script( $wp_scripts->registered[ $script ]->handle );
						}
					}
				},
				1
			);

			$this->demo_template_importer_scripts();
		}//end if
	}

	public function demo_template_importer_scripts() {
		wp_enqueue_style( 'ablocks-fonts', $this->web_fonts_url( 'Roboto:ital,wght@0,300;0,400;0,500;0,700;1,300&display=swap' ), array(), ABLOCKS_VERSION );
		wp_enqueue_style( 'ablocks-icon', ABLOCKS_ASSETS_URL . 'library/css/ablocks-icon/style.css', array( 'wp-components' ), filemtime( ABLOCKS_ASSETS_PATH . 'library/css/ablocks-icon/style.css' ), 'all' );
		wp_enqueue_style( 'ablocks-dashboard-style', ABLOCKS_ASSETS_URL . 'build/demo-import.css', array( 'wp-components' ), filemtime( ABLOCKS_ASSETS_PATH . 'build/demo-import.css' ), 'all' );

		$dependencies = include ABLOCKS_ASSETS_PATH . 'build/demo-import.asset.php';
		wp_enqueue_script(
			'ablocks-demo-import-scripts',
			ABLOCKS_ASSETS_URL . 'build/demo-import.js',
			$dependencies['dependencies'],
			$dependencies['version'],
			true
		);
		wp_localize_script( 'ablocks-demo-import-scripts', 'ABlocksGlobal', $this->get_dashboard_localize_script_data() );
		wp_set_script_translations( 'ablocks-demo-import-scripts', 'ablocks', ABLOCKS_ROOT_DIR_PATH . 'languages' );
	}

	public function web_fonts_url( $font ) {
		$font_url = '';
		if ( 'off' !== _x( 'on', 'Google font: on or off', 'ablocks' ) ) {
			$font_url = add_query_arg( 'family', rawurlencode( $font ), '//fonts.googleapis.com/css' );
		}
		return $font_url;
	}

	public function front_end_google_fonts() {
		global $ablocks_fonts;
		if ( empty( $ablocks_fonts ) || ! is_array( $ablocks_fonts ) ) {
			return false;
		}

		if ( Helper::get_settings( 'enabled_load_google_font_locally', false ) ) {
			$FontLoadLocally = new FontLoadLocally();
			$FontLoadLocally->enqueue_fonts( $ablocks_fonts ); // Attempt to load local fonts (fallback inside method)
		} else {
			$font_families = [];
			foreach ( $ablocks_fonts as $family => $weights ) {
				$font_family_string = $family;
				$total_weights = count( $weights );

				if ( $total_weights > 0 ) {
					$font_family_string .= ':wght@' . implode( ';', $weights );
				}

				$font_families[] = $font_family_string;
			}
			// Generate the URL using the web_fonts_url method
			$google_fonts_url = $this->web_fonts_url( implode( '|', $font_families ) ) . '&display=swap';
			wp_register_style( 'ablocks-frontend-google-fonts', esc_url( $google_fonts_url ), array(), ABLOCKS_VERSION );
		}
	}


	public function block_editor_assets() {
		if ( is_admin() ) {
			wp_enqueue_style( 'ablocks-editor-font-awesome', ABLOCKS_ASSETS_URL . 'library/font-awesome/css/all.min.css', array(), ABLOCKS_VERSION, 'all' );
			wp_enqueue_style( 'ablocks-editor-fonts', $this->web_fonts_url( 'Roboto:ital,wght@0,300;0,400;0,500;0,700;1,300&display=swap' ), array(), ABLOCKS_VERSION );
			wp_enqueue_style( 'ablocks-editor-icon', ABLOCKS_ASSETS_URL . 'library/css/ablocks-icon/style.css', array( 'wp-components' ), filemtime( ABLOCKS_ASSETS_PATH . 'library/css/ablocks-icon/style.css' ), 'all' );
			wp_enqueue_style( 'ablocks-editor-style', ABLOCKS_ASSETS_URL . 'build/blocks.css', array( 'wp-components' ), filemtime( ABLOCKS_ASSETS_PATH . 'build/blocks.css' ), 'all' );

			// js
			$dependencies = include ABLOCKS_ASSETS_PATH . 'build/blocks.asset.php';
			wp_enqueue_script(
				'ablocks-editor-scripts',
				ABLOCKS_ASSETS_URL . 'build/blocks.js',
				$dependencies['dependencies'],
				$dependencies['version'],
				true
			);
			wp_localize_script( 'ablocks-editor-scripts', 'ABlocksGlobal', $this->get_editor_localize_script_data() );
			wp_set_script_translations( 'ablocks-editor-scripts', 'ablocks', ABLOCKS_ROOT_DIR_PATH . 'languages' );
		}
	}
	public function enqueue_frontend_assets() {
		if ( ! Helper::is_enabled_assets_generation() ) {
			return;
		}

		do_action( 'ablocks/before_enqueue_frontend_scripts' );

		$script_loading_strategy = Helper::get_script_loading_strategy();

		if ( ! $this->is_assets_generated() ) {
			return;
		}

		if ( $this->current_page_slug ) {
			// Enqueue google fonts
			wp_enqueue_style( 'ablocks-frontend-google-fonts' );
			wp_enqueue_style( 'ablocks-blocks-combine-style', $this->FileUpload->get_file_url( $this->current_page_slug . '.min.css' ), array(), filemtime( $this->FileUpload->get_file_path( $this->current_page_slug . '.min.css' ) ), 'all' );

			wp_enqueue_script( 'ablocks-blocks-combine-script', $this->FileUpload->get_file_url( $this->current_page_slug . '.min.js' ), array(), filemtime( $this->FileUpload->get_file_path( $this->current_page_slug . '.min.js' ) ), true, [ 'strategy' => $script_loading_strategy ] );
			wp_localize_script( 'ablocks-blocks-combine-script', 'ABlocksGlobal', $this->get_localize_frontend_data() );
			wp_set_script_translations( 'ablocks-blocks-combine-script', 'ablocks', ABLOCKS_ROOT_DIR_PATH . 'languages' );
		} else {
			// fallback if assets not available
			add_filter( 'ablocks/is_allow_block_inline_assets', '__return_true' );
		}
	}

	public function regenerate_missing_assets() {
		if ( $this->is_assets_generated() ) {
			return;
		}

		// classic
		if ( ! Helper::is_fse_theme() ) {
			$is_parse_main_content = false;
			if ( is_array( $this->theme_builder_locations ) ) {
				foreach ( $this->theme_builder_locations as $location_name => $location_id ) {
					$post = get_post( $location_id );
					if ( is_object( $post ) ) {
						$this->set_current_page_template_part( $post->post_content, parse_blocks( $post->post_content ) );
					}
					if ( $location_name === 'header' ) {
						$post = get_post( get_the_ID() );
						if ( is_object( $post ) ) {
							$this->set_current_page_template_part( $post->post_content, parse_blocks( $post->post_content ) );
						}
						$is_parse_main_content = true;
					}
				}
			}
			if ( false === $is_parse_main_content ) {
				$post = get_post( get_the_ID() );
				$this->set_current_page_template_part( $post->post_content, parse_blocks( $post->post_content ) );
			}
		}//end if

		if ( count( $this->current_page_blocks ) ) {
			$file_name  = $this->current_page_slug;
			AssetsGenerator::write_frontend_css_in_uploads_folder( $file_name, $this->current_page_blocks );
		}
	}
	public function register_scripts() {
		$register_styles = RegisterScripts::get_register_styles();
		foreach ( $register_styles as $register_handler => $register_style ) {
			wp_register_style( $register_handler, $register_style['url'], $register_style['deps'], ABLOCKS_VERSION, $register_style['media'] );
		}
		$register_scripts = RegisterScripts::get_register_scripts();
		foreach ( $register_scripts as $register_handler => $register_script ) {
			if ( isset( $register_script['dependencies'] ) ) {
				$dependencies = include $register_script['dependencies'];
				$register_script['deps'] = $dependencies['dependencies'];
				$register_script['ver'] = $dependencies['version'];
			}
			wp_register_script(
				$register_handler,
				$register_script['url'],
				$register_script['deps'],
				$register_script['ver'],
				$register_script['args']
			);
		}
	}
	public function global_css_variable() {
		wp_register_style( 'ablocks-editor-global-styles', false, array(), ABLOCKS_VERSION );
		wp_enqueue_style( 'ablocks-editor-global-styles' );

		$container_padding = Helper::get_settings( 'container_padding', 10 ) . 'px';
		$css = ":root, body .editor-styles-wrapper {\n";
		$css .= "    --ablocks-container-padding: $container_padding;\n";

		// Colors
		$global_color = (array) Helper::get_settings( 'global_color', [] );
		foreach ( $global_color as $color ) {
			if ( isset( $color->id, $color->value ) ) {
				$css .= "    --ablocks-{$color->id}: {$color->value};\n";
			}
		}

		// Typography
		$global_typography = (array) Helper::get_settings( 'global_typography', [] );
		foreach ( $global_typography as $typography ) {
			if ( isset( $typography->id, $typography->value ) ) {
				$id    = $typography->id;
				$value = $typography->value;

				// Desktop values
				if ( ! empty( $value->fontFamily ) ) {
					$css .= "    --ablocks-{$id}-font-family: {$value->fontFamily};\n";
				}
				if ( ! empty( $value->weight ) ) {
					$css .= "    --ablocks-{$id}-weight: {$value->weight};\n";
				}
				if ( ! empty( $value->transform ) ) {
					$css .= "    --ablocks-{$id}-transform: {$value->transform};\n";
				}
				if ( ! empty( $value->style ) ) {
					$css .= "    --ablocks-{$id}-style: {$value->style};\n";
				}
				if ( ! empty( $value->decoration ) ) {
					$css .= "    --ablocks-{$id}-decoration: {$value->decoration};\n";
				}
				if ( ! empty( $value->fontSize ) ) {
					$unit = ! empty( $value->fontSizeUnit ) ? $value->fontSizeUnit : 'px';
					$css .= "    --ablocks-{$id}-font-size: {$value->fontSize}{$unit};\n";
				}
				if ( ! empty( $value->lineHeight ) ) {
					$unit = ! empty( $value->lineHeightUnit ) ? $value->lineHeightUnit : 'px';
					$css .= "    --ablocks-{$id}-line-height: {$value->lineHeight}{$unit};\n";
				}
				if ( ! empty( $value->letterSpacing ) ) {
					$unit = ! empty( $value->letterSpacingUnit ) ? $value->letterSpacingUnit : 'px';
					$css .= "    --ablocks-{$id}-letter-spacing: {$value->letterSpacing}{$unit};\n";
				}
				if ( ! empty( $value->wordSpacing ) ) {
					$unit = ! empty( $value->wordSpacingUnit ) ? $value->wordSpacingUnit : 'px';
					$css .= "    --ablocks-{$id}-word-spacing: {$value->wordSpacing}{$unit};\n";
				}

				// Tablet values
				if ( ! empty( $value->fontSizeTablet ) ) {
					$unit = ! empty( $value->fontSizeUnitTablet ) ? $value->fontSizeUnitTablet : 'px';
					$css .= "    --ablocks-{$id}-font-size-tablet: {$value->fontSizeTablet}{$unit};\n";
				}
				if ( ! empty( $value->lineHeightTablet ) ) {
					$unit = ! empty( $value->lineHeightUnitTablet ) ? $value->lineHeightUnitTablet : 'px';
					$css .= "    --ablocks-{$id}-line-height-tablet: {$value->lineHeightTablet}{$unit};\n";
				}
				if ( ! empty( $value->letterSpacingTablet ) ) {
					$unit = ! empty( $value->letterSpacingUnitTablet ) ? $value->letterSpacingUnitTablet : 'px';
					$css .= "    --ablocks-{$id}-letter-spacing-tablet: {$value->letterSpacingTablet}{$unit};\n";
				}
				if ( ! empty( $value->wordSpacingTablet ) ) {
					$unit = ! empty( $value->wordSpacingUnitTablet ) ? $value->wordSpacingUnitTablet : 'px';
					$css .= "    --ablocks-{$id}-word-spacing-tablet: {$value->wordSpacingTablet}{$unit};\n";
				}

				// Mobile values
				if ( ! empty( $value->fontSizeMobile ) ) {
					$unit = ! empty( $value->fontSizeUnitMobile ) ? $value->fontSizeUnitMobile : 'px';
					$css .= "    --ablocks-{$id}-font-size-mobile: {$value->fontSizeMobile}{$unit};\n";
				}
				if ( ! empty( $value->lineHeightMobile ) ) {
					$unit = ! empty( $value->lineHeightUnitMobile ) ? $value->lineHeightUnitMobile : 'px';
					$css .= "    --ablocks-{$id}-line-height-mobile: {$value->lineHeightMobile}{$unit};\n";
				}
				if ( ! empty( $value->letterSpacingMobile ) ) {
					$unit = ! empty( $value->letterSpacingUnitMobile ) ? $value->letterSpacingUnitMobile : 'px';
					$css .= "    --ablocks-{$id}-letter-spacing-mobile: {$value->letterSpacingMobile}{$unit};\n";
				}
				if ( ! empty( $value->wordSpacingMobile ) ) {
					$unit = ! empty( $value->wordSpacingUnitMobile ) ? $value->wordSpacingUnitMobile : 'px';
					$css .= "    --ablocks-{$id}-word-spacing-mobile: {$value->wordSpacingMobile}{$unit};\n";
				}
			}//end if
		}//end foreach

		$css .= "}\n";

		if ( ! is_admin() && ! Helper::is_gutenberg_editor() ) {
			$css .= $this->get_common_css();
		}
		wp_add_inline_style( 'ablocks-editor-global-styles', $css );
		wp_add_inline_style( 'ablocks-common-style', $css );
	}

	public function add_editor_inline_css() {
		$custom_css = $this->get_common_css( '.editor-styles-wrapper ' );
		add_theme_support( 'editor-styles' );
		add_editor_style();
		wp_add_inline_style( 'wp-block-library', $custom_css );
	}

	public function get_common_css( $parent_selector = '' ) {
		global $ablocks_settings;
		$attributes = [
			'global_body_text_color' => $ablocks_settings->global_body_text_color,
			'global_body_typography' => $ablocks_settings->global_body_typography,
			'global_body_paragraph_space' => $ablocks_settings->global_body_paragraph_space,
			'global_link_color' => $ablocks_settings->global_link_color,
			'global_link_hover_color' => $ablocks_settings->global_link_hover_color,
			'global_link_typography' => $ablocks_settings->global_link_typography,
			'global_link_hover_typography' => $ablocks_settings->global_link_hover_typography,
			'global_h1_color' => $ablocks_settings->global_h1_color,
			'global_h1_typography' => $ablocks_settings->global_h1_typography,
			'global_h2_color' => $ablocks_settings->global_h2_color,
			'global_h2_typography' => $ablocks_settings->global_h2_typography,
			'global_h3_color' => $ablocks_settings->global_h3_color,
			'global_h3_typography' => $ablocks_settings->global_h3_typography,
			'global_h4_color' => $ablocks_settings->global_h4_color,
			'global_h4_typography' => $ablocks_settings->global_h4_typography,
			'global_h5_color' => $ablocks_settings->global_h5_color,
			'global_h5_typography' => $ablocks_settings->global_h5_typography,
			'global_h6_color' => $ablocks_settings->global_h6_color,
			'global_h6_typography' => $ablocks_settings->global_h6_typography,
		];
		$css_generator = new GlobalCssGenerator( $attributes );

		$css_generator->add_class_styles(
			$parent_selector . 'body',
			$css_generator->get_body_css( $attributes ),
			$css_generator->get_body_css( $attributes, 'Tablet' ),
			$css_generator->get_body_css( $attributes, 'Mobile' )
		);
		$css_generator->add_class_styles(
			$parent_selector . 'p',
			$css_generator->get_body_paragraph_css( $attributes ),
			$css_generator->get_body_paragraph_css( $attributes, 'Tablet' ),
			$css_generator->get_body_paragraph_css( $attributes, 'Mobile' )
		);
		$css_generator->add_class_styles(
			$parent_selector . 'a',
			$css_generator->get_body_anchor_css( $attributes ),
			$css_generator->get_body_anchor_css( $attributes, 'Tablet' ),
			$css_generator->get_body_anchor_css( $attributes, 'Mobile' )
		);
		$css_generator->add_class_styles(
			$parent_selector . 'a:hover',
			$css_generator->get_body_anchor_hover_css( $attributes ),
			$css_generator->get_body_anchor_hover_css( $attributes, 'Tablet' ),
			$css_generator->get_body_anchor_hover_css( $attributes, 'Mobile' )
		);
		$css_generator->add_class_styles(
			$parent_selector . 'h1',
			$css_generator->get_global_h1_css( $attributes ),
			$css_generator->get_global_h1_css( $attributes, 'Tablet' ),
			$css_generator->get_global_h1_css( $attributes, 'Mobile' )
		);
		$css_generator->add_class_styles(
			$parent_selector . 'h2',
			$css_generator->get_global_h2_css( $attributes ),
			$css_generator->get_global_h2_css( $attributes, 'Tablet' ),
			$css_generator->get_global_h2_css( $attributes, 'Mobile' )
		);
		$css_generator->add_class_styles(
			$parent_selector . 'h3',
			$css_generator->get_global_h3_css( $attributes ),
			$css_generator->get_global_h3_css( $attributes, 'Tablet' ),
			$css_generator->get_global_h3_css( $attributes, 'Mobile' )
		);
		$css_generator->add_class_styles(
			$parent_selector . 'h4',
			$css_generator->get_global_h4_css( $attributes ),
			$css_generator->get_global_h4_css( $attributes, 'Tablet' ),
			$css_generator->get_global_h4_css( $attributes, 'Mobile' )
		);
		$css_generator->add_class_styles(
			$parent_selector . 'h5',
			$css_generator->get_global_h5_css( $attributes ),
			$css_generator->get_global_h5_css( $attributes, 'Tablet' ),
			$css_generator->get_global_h5_css( $attributes, 'Mobile' )
		);
		$css_generator->add_class_styles(
			$parent_selector . 'h6',
			$css_generator->get_global_h6_css( $attributes ),
			$css_generator->get_global_h6_css( $attributes, 'Tablet' ),
			$css_generator->get_global_h6_css( $attributes, 'Mobile' )
		);
		return $css_generator->generate_css();
	}


	public function is_assets_generated() {
		$file_name = $this->current_page_slug;
		$css_file_path = $this->FileUpload->get_file_path( $file_name . '.min.css' );
		return file_exists( $css_file_path );
	}

	public function set_current_page_template_part( $content, $block ) {
		if ( ! isset( $block['blockName'] ) && is_array( $block ) ) {
			foreach ( $block as $block_item ) {
				if ( ! empty( $block_item['blockName'] ) && strpos( $block_item['blockName'], 'ablocks/' ) !== false ) {
					$this->current_page_blocks[] = $block_item;
				}
			}
		}
		if ( ! empty( $block['blockName'] ) && strpos( $block['blockName'], 'ablocks/' ) !== false ) {
			$this->current_page_blocks[] = $block;
		}
		return $content;
	}

	public function set_theme_builder_locations( $args ) {
		$this->theme_builder_locations = $args;
	}
}
