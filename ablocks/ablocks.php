<?php
/**
 * Plugin Name:       aBlocks
 * Description:       The WordPress plugin for creating beautiful and functional websites using the Gutenberg editor, with a variety of customizable blocks to design website pages.
 * Requires at least: 6.3
 * Requires PHP:      7.4
 * Version:           1.7.0
 * Author:            Academy LMS
 * Author URI:        https://ablocks.pro/
 * License:           GPL-3.0+
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       ablocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class ABlocks {
	public function __construct() {
		// define constants
		$this->define_constants();
		$this->load_dependency();
		$this->load_cli();
		register_activation_hook( __FILE__, [ $this, 'activate' ] );
		$this->set_global_settings();
		add_action( 'plugins_loaded', [ $this, 'on_plugins_loaded' ] );
		add_action( 'ablocks_loaded', [ $this, 'init_plugin' ] );
	}

	public static function init() {
		static $instance = false;
		if ( ! $instance ) {
			$instance = new self();
		}
		return $instance;
	}

	/**
	 * Define the plugin constants
	 */
	private function define_constants() {
		define( 'ABLOCKS_VERSION', '1.7.0' );
		define( 'ABLOCKS_PLUGIN_SLUG', 'ablocks' );
		define( 'ABLOCKS_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
		define( 'ABLOCKS_ROOT_URL', plugin_dir_url( __FILE__ ) );
		define( 'ABLOCKS_ASSETS_URL', ABLOCKS_ROOT_URL . 'assets/' );
		define( 'ABLOCKS_ROOT_DIR_PATH', plugin_dir_path( __FILE__ ) );
		define( 'ABLOCKS_ASSETS_PATH', ABLOCKS_ROOT_DIR_PATH . 'assets/' );
		define( 'ABLOCKS_INCLUDES_DIR_PATH', ABLOCKS_ROOT_DIR_PATH . 'includes/' );
		define( 'ABLOCKS_BLOCKS_DIR_PATH', ABLOCKS_ROOT_DIR_PATH . 'includes/blocks/' );
		define( 'ABLOCKS_BLOCKS_VISIBILITY_SETTINGS_NAME', 'ablocks_blocks' );
		define( 'ABLOCKS_FONTS_SETTINGS_NAME', 'ablocks_fonts' );
		define( 'ABLOCKS_SETTINGS_NAME', 'ablocks_settings' );
		define( 'ABLOCKS_TEMPLATE_LIB_HOST', 'template-kits.com' );
	}

	public function load_dependency() {
		require_once ABLOCKS_INCLUDES_DIR_PATH . 'autoload.php';
	}
	public function load_cli() {
		if ( file_exists( ABLOCKS_ROOT_DIR_PATH . 'dev-cli.php' ) ) {
			require_once ABLOCKS_ROOT_DIR_PATH . 'dev-cli.php';
		}
	}

	public function set_global_settings() {
		$GLOBALS['ablocks_fonts'] = json_decode( get_option( ABLOCKS_FONTS_SETTINGS_NAME, '{}' ), true );
		$GLOBALS['ablocks_blocks'] = json_decode( get_option( ABLOCKS_BLOCKS_VISIBILITY_SETTINGS_NAME, '{}' ) );
		$GLOBALS['ablocks_settings'] = json_decode( get_option( ABLOCKS_SETTINGS_NAME, '{}' ) );
	}

	/**
	 * When WP has loaded all plugins, trigger the `ablocks_loaded` hook.
	 *
	 * This ensures `ablocks_loaded` is called only after all other plugins
	 * are loaded, to avoid issues caused by plugin directory naming changing
	 *
	 * @since 1.0.0
	 */
	public function on_plugins_loaded() {
		do_action( 'ablocks_loaded' );
	}

	public function init_plugin() {
		ABlocks\Migration::init();
		ABlocks\Blocks::init();
		ABlocks\Assets::init();
		ABlocks\Ajax::init();
		if ( is_admin() ) {
			ABlocks\Admin::init();
		}
		ABlocks\Frontend::init();
	}

	public function activate() {
		ABlocks\Installer::init();
	}
}

/**
 * Kickoff
*/

ABlocks::init();


