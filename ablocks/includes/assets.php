<?php
namespace ABlocks;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ABlocks\Classes\FileUpload;
use ABlocks\Classes\AssetsGenerator;
use ABlocks\Classes\RegisterScripts;
use ABlocks\Admin\Menu;
use ABlocks\Helper;

class Assets {

	public static function init() {
		$self = new self();
		add_action( 'admin_enqueue_scripts', [ $self, 'dashboard_scripts' ], 10 );
		add_action( 'enqueue_block_assets', [ $self, 'block_editor_assets' ] );
		add_action( 'enqueue_block_assets', [ $self, 'register_scripts' ] );
		add_action( 'wp_enqueue_scripts', [ $self, 'front_end_google_fonts' ] );
		add_action( 'wp_enqueue_scripts', [ $self, 'register_scripts' ] );

		add_action( 'wp_enqueue_scripts', [ $self, 'enqueue_frontend_assets' ], 99 );
		add_action( 'wp_enqueue_scripts', [ $self, 'enqueue_frontend_localize_script' ] );
		add_action( 'wp', [ $self, 'regenerate_missing_assets' ] );
		// Global CSS
		add_action( 'wp_enqueue_block_assets', [ $self, 'global_css_variable' ], 999 );
		add_action( 'wp_enqueue_scripts', [ $self, 'global_css_variable' ], 999 );
		add_action( 'enqueue_block_editor_assets', [ $self, 'global_css_variable' ], 999 );
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
			'nonce'                 => wp_create_nonce( 'wp_rest' ),
			'ablocks_nonce'         => wp_create_nonce( 'ablocks_nonce' ),
			'is_pro'                => (bool) Helper::is_active_ablocks_pro(),
		];
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
				'enabled_only_selected_fonts' => (bool) Helper::get_settings( 'enabled_only_selected_fonts', false ),
				'selected_fonts' => (array) Helper::get_settings( 'selected_fonts', [] ),
			],
			'is_gutenberg_editor' => Helper::is_gutenberg_editor(),
			'third_party_plugin_status' => [
				'academy_lms' => Helper::is_active_academy(),
				'storeengine' => Helper::is_active_storeengine(),
				'wp_map_block' => Helper::is_active_wp_map_block(),
			]
		);
		return apply_filters(
			'ablocks/assets/dashboard_scripts_data',
			array_merge(
				$this->get_localize_script_data(),
				$args
			)
		);
	}
	public function get_editor_localize_script_data() {
		$args = array(
			'settings'         => [
				'default_container_width' => Helper::get_settings( 'default_container_width', 1280 ),
				'container_padding' => Helper::get_settings( 'container_padding', 10 ),
				'container_element_gap' => Helper::get_settings( 'container_element_gap', 20 ),
				'enabled_assets_file_generation' => (bool) Helper::get_settings( 'enabled_assets_file_generation', false ),
				'enabled_block_copy_paste_style' => (bool) Helper::get_settings( 'enabled_block_copy_paste_style', false ),
				'enabled_only_selected_fonts' => (bool) Helper::get_settings( 'enabled_only_selected_fonts', false ),
				'selected_fonts' => (array) Helper::get_settings( 'selected_fonts', [] ),
			],
			'is_gutenberg_editor' => Helper::is_gutenberg_editor(),
			'third_party_plugin_status' => [
				'academy_lms' => Helper::is_active_academy(),
				'storeengine' => Helper::is_active_storeengine(),
				'wp_map_block' => Helper::is_active_wp_map_block(),
			]
		);
		return apply_filters(
			'ablocks/assets/editor_scripts_data',
			array_merge(
				$this->get_localize_script_data(),
				$args
			)
		);
	}

	public function dashboard_scripts( $hook ) {
		if ( strpos( $hook, '_page_' . ABLOCKS_PLUGIN_SLUG ) !== false ) {
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

		wp_enqueue_style( 'ablocks-frontend-google-fonts', esc_url( $google_fonts_url ), array(), ABLOCKS_VERSION );
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
		if ( ( function_exists( 'wp_is_block_theme' ) && wp_is_block_theme() ) ) {
			return;
		}

		$post_id = get_the_ID();
		if ( $post_id ) {
			$FileUpload = new FileUpload();
			$css_file_path = $FileUpload->get_file_path( get_the_ID() . '.min.css' );
			if ( file_exists( $css_file_path ) ) {
				$css_file_url = $FileUpload->get_file_url( get_the_ID() . '.min.css' );
				wp_enqueue_style( 'ablocks-blocks-combine-style', $css_file_url, array(), filemtime( $css_file_path ), 'all' );

			}
			$js_file_path = $FileUpload->get_file_path( get_the_ID() . '.min.js' );
			if ( file_exists( $js_file_path ) ) {
				$js_file_url = $FileUpload->get_file_url( get_the_ID() . '.min.js' );
				wp_enqueue_script( 'ablocks-blocks-combine-script', $js_file_url, array(), filemtime( $js_file_path ), true );
				wp_localize_script( 'ablocks-blocks-combine-script', 'ABlocksGlobal', $this->get_localize_script_data() );
			}
		}
	}

	public function regenerate_missing_assets() {
		if ( ! Helper::is_enabled_assets_generation() ) {
			return;
		}

		$post_id = (int) get_the_ID();
		if ( $post_id ) {
			$FileUpload = new FileUpload();
			$has_upload_file = $FileUpload->has_upload_file( get_the_ID() . '.min.css' );
			if ( ! $has_upload_file ) {
				AssetsGenerator::write_frontend_css_in_uploads_folder( $post_id );
			}
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
		$css = ":root, body .editor-styles-wrapper {
			--ablocks-container-padding: $container_padding;
		}";
		wp_add_inline_style( 'ablocks-editor-global-styles', $css );
	}

	public function enqueue_frontend_localize_script() {
		wp_localize_script( 'ablocks-blocks-combine-script', 'ABlocksGlobal', $this->get_localize_script_data() );
		wp_set_script_translations( 'ablocks-blocks-combine-script', 'ablocks', ABLOCKS_ROOT_DIR_PATH . 'languages' );

		wp_localize_script( 'ablocks-common-script', 'ABlocksGlobal', $this->get_localize_script_data() );
		wp_set_script_translations( 'ablocks-common-script', 'ablocks', ABLOCKS_ROOT_DIR_PATH . 'languages' );
	}

}
