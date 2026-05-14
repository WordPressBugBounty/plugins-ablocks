<?php

namespace ABlocks\Ajax;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ABlocks\Classes\AbstractAjaxHandler;
use ABlocks\Classes\FileUpload;
use ABlocks\Classes\FontLoadLocally;
use ABlocks\Helper;

class Dashboard extends AbstractAjaxHandler {
	public function __construct() {
		$this->actions = array(
			'get_admin_menu_items'      => array(
				'callback' => array( $this, 'get_admin_menu_items' ),
				'capability'    => 'manage_options',
			),
			'regenerate_assets'      => array(
				'callback' => array( $this, 'regenerate_assets' ),
				'capability'    => 'manage_options',
			),
			'clear_demo_transients'      => array(
				'callback' => array( $this, 'clear_demo_transients' ),
				'capability'    => 'manage_options',
			),
			'install_academy_lms'      => array(
				'callback' => array( $this, 'install_academy_lms' ),
				'capability'    => 'manage_options',
			),
			'install_storeengine'      => array(
				'callback' => array( $this, 'install_storeengine' ),
				'capability'    => 'manage_options',
			),
			'install_ecm'      => array(
				'callback' => array( $this, 'install_ecm' ),
			),
			'download_google_fonts'      => array(
				'callback' => array( $this, 'download_google_fonts' ),
				'capability'    => 'manage_options',
			),
		);
	}

	public function get_admin_menu_items() {
		$menu_items = wp_json_encode( Helper::get_admin_menu_list() );
		wp_send_json_success( $menu_items );
	}
	public function regenerate_assets() {
		$FileUpload = new FileUpload();
		$has_delete = $FileUpload->delete_files();
		Helper::clear_third_party_plugin_cache();
		update_option( ABLOCKS_FONTS_SETTINGS_NAME, '{}' );
		wp_send_json_success( $has_delete );
	}

	public function clear_demo_transients() {
		global $wpdb;

		// phpcs:ignore  WordPress.DB.DirectDatabaseQuery.DirectQuery,  WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_ablocks_demo_%' OR option_name LIKE '_transient_timeout_ablocks_demo_%'" );

		if ( ! empty( $wpdb->last_error ) ) {
			wp_send_json_error( $wpdb->last_error );
		}

		wp_send_json_success();
	}
	public function install_academy_lms() {
		// Check user permissions
		if ( ! current_user_can( 'install_plugins' ) ) {
			wp_send_json_error( __( 'You do not have sufficient permissions to install plugins.', 'ablocks' ) );
		}

		// Check if the plugin is already installed
		if ( ! Helper::is_plugin_installed( 'academy/academy.php' ) ) {

			$plugin_status = $this->install_plugin( 'academy', true );
			if ( $plugin_status ) {
				wp_send_json_success( __( 'Plugin installed and activated successfully!', 'ablocks' ) );
			}
			wp_send_json_error( __( 'Sorry, failed to download.', 'ablocks' ) );
		}

		// Activate the plugin
		$activate_status = activate_plugin( 'academy/academy.php' );
		if ( is_wp_error( $activate_status ) ) {
			wp_send_json_error( 'Plugin activation failed: ' . $activate_status->get_error_message() );
		}
		wp_send_json_success( __( 'Plugin installed and activated successfully!', 'ablocks' ) );
	}
	public function install_storeengine() {
		// Check user permissions
		if ( ! current_user_can( 'install_plugins' ) ) {
			wp_send_json_error( __( 'You do not have sufficient permissions to install plugins.', 'ablocks' ) );
		}

		// Check if the plugin is already installed
		if ( ! Helper::is_plugin_installed( 'storeengine/storeengine.php' ) ) {

			$plugin_status = $this->install_plugin( 'storeengine', true );
			if ( $plugin_status ) {
				wp_send_json_success( __( 'Plugin installed and activated successfully!', 'ablocks' ) );
			}
			wp_send_json_error( __( 'Sorry, failed to download.', 'ablocks' ) );
		}

		// Activate the plugin
		$activate_status = activate_plugin( 'storeengine/storeengine.php' );
		if ( is_wp_error( $activate_status ) ) {
			wp_send_json_error( 'Plugin activation failed: ' . $activate_status->get_error_message() );
		}
		wp_send_json_success( __( 'Plugin installed and activated successfully!', 'ablocks' ) );
	}
	public function install_ecm() {
		// Check user permissions
		if ( ! current_user_can( 'install_plugins' ) ) {
			wp_send_json_error( __( 'You do not have sufficient permissions to install plugins.', 'ablocks' ) );
		}

		// Check if the plugin is already installed
		if ( ! Helper::is_plugin_installed( 'easy-content-manager/easy-content-manager.php' ) ) {

			$plugin_status = $this->install_plugin( 'easy-content-manager', true );
			if ( $plugin_status ) {
				wp_send_json_success( __( 'Plugin installed and activated successfully!', 'ablocks' ) );
			}
			wp_send_json_error( __( 'Sorry, failed to download.', 'ablocks' ) );
		}

		// Activate the plugin
		$activate_status = activate_plugin( 'easy-content-manager/easy-content-manager.php' );
		if ( is_wp_error( $activate_status ) ) {
			wp_send_json_error( 'Plugin activation failed: ' . $activate_status->get_error_message() );
		}
		wp_send_json_success( __( 'Plugin installed and activated successfully!', 'ablocks' ) );
	}
	public function install_plugin( $slug = '', $active = true ) {
		if ( empty( $slug ) ) {
			return new \WP_Error( 'empty_arg', __( 'Argument should not be empty.', 'ablocks' ) );
		}

		include_once ABSPATH . 'wp-admin/includes/file.php';
		include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		include_once ABSPATH . 'wp-admin/includes/class-automatic-upgrader-skin.php';

		$plugin_data = $this->get_remote_plugin_data( $slug );

		if ( is_wp_error( $plugin_data ) ) {
			return $plugin_data;
		}

		$upgrader = new \Plugin_Upgrader( new \Automatic_Upgrader_Skin() );

		// install plugin
		$install = $upgrader->install( $plugin_data->download_link );

		if ( is_wp_error( $install ) ) {
			return $install;
		}

		// activate plugin
		if ( $install === true && $active ) {
			$active = activate_plugin( $upgrader->plugin_info(), '', false, true );

			if ( is_wp_error( $active ) ) {
				return $active;
			}

			return $active === null;
		}

		return $install;
	}

	public function get_remote_plugin_data( $slug = '' ) {
		if ( empty( $slug ) ) {
			return new \WP_Error( 'empty_arg', __( 'Argument should not be empty.', 'ablocks' ) );
		}

		$response = wp_remote_post(
			'http://api.wordpress.org/plugins/info/1.0/',
			array(
				'body' => array(
					'action' => 'plugin_information',
					'request' => maybe_serialize( (object) array(
						'slug' => $slug,
						'fields' => array(
							'version' => false,
						),
					)),
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		return maybe_unserialize( wp_remote_retrieve_body( $response ) );
	}

	public function download_google_fonts() {
		global $ablocks_fonts;
		$fontDownloader = new FontLoadLocally();
		$fontDownloader->process_font_queue( $ablocks_fonts );
	}
}
