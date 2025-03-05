<?php

namespace ABlocks\Ajax;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ABlocks\Classes\AbstractAjaxHandler;
use ABlocks\Classes\Sanitizer;
use ABlocks\Helper;
use ABlocks\Admin\Settings\Base as BaseSettings;

class Settings extends AbstractAjaxHandler {
	public function __construct() {
		$this->actions = array(
			'get_blocks_visibility'      => array(
				'callback' => array( $this, 'get_blocks_visibility' ),
			),
			'save_block_visibility'      => array(
				'callback' => array( $this, 'save_block_visibility' ),
			),
			'get_settings'      => array(
				'callback' => array( $this, 'get_settings' ),
			),
			'save_settings'      => array(
				'callback' => array( $this, 'save_settings' ),
			),
			'fetch_posts'      => array(
				'callback' => array( $this, 'fetch_posts' ),
			),
		);
	}

	public function get_blocks_visibility() {
		global $ablocks_blocks;
		wp_send_json_success( $ablocks_blocks );
	}

	public function save_block_visibility() {
		$payload = Sanitizer::sanitize_payload([
			'block_name'        => 'string',
			'status'            => 'boolean',
			'required_plugin'   => 'json',
		], $_POST); // phpcs:ignore WordPress.Security.NonceVerification.Missing

		$block_name = $payload['block_name'];
		$status = $payload['status'];
		$required_plugin = $payload['required_plugin'];

		if ( empty( $block_name ) ) {
			wp_send_json_error( __( 'Block Name missing', 'ablocks' ) );
		}

		if ( $status && $required_plugin ) {
			if ( $required_plugin && is_array( $required_plugin ) ) {
				foreach ( $required_plugin as $plugin ) {
					if ( ! Helper::is_plugin_active( sanitize_text_field( $plugin['plugin_dir_path'] ) ) ) {
						$error_message = sprintf( '%s Plugin is required to activate %s block.', $plugin['plugin_name'], $block_name );
						wp_send_json_error( $error_message );
					}
				}
			}
		}

		// Saved Data
		$saved_blocks = (array) json_decode( get_option( ABLOCKS_BLOCKS_VISIBILITY_SETTINGS_NAME ), true );
		$saved_blocks[ $block_name ] = $status;
		update_option( ABLOCKS_BLOCKS_VISIBILITY_SETTINGS_NAME, wp_json_encode( $saved_blocks ) );
		// Fire Addon Action
		if ( $status ) {
			do_action( "ablocks/block/activated_{$block_name}", $status );
		} else {
			do_action( "ablocks/block/deactivated_{$block_name}", $status );
		}
		wp_send_json_success( $saved_blocks );
	}
	public function get_settings() {
		$settings = BaseSettings::get_saved_data();
		wp_send_json_success( $settings );
	}

	public function save_settings() {
        // phpcs:ignore WordPress.Security.NonceVerification.Missing
		do_action( 'ablocks/before_save_settings', $_POST, 'base' );
		$payload = Sanitizer::sanitize_payload([
			'default_container_width' => 'integer',
			'container_padding' => 'integer',
			'container_element_gap' => 'integer',
			'enabled_assets_file_generation' => 'boolean',
			'enabled_block_copy_paste_style' => 'boolean',
			'enabled_only_selected_fonts' => 'boolean',
			'selected_fonts' => 'json',
			'enabled_coming_soon_page' => 'boolean',
			'coming_soon_page' => 'integer',
			'enabled_maintenance_page' => 'boolean',
			'maintenance_page' => 'integer',
		], $_POST); // phpcs:ignore WordPress.Security.NonceVerification.Missing

		$default = BaseSettings::get_default_data();
		$is_update = BaseSettings::save_settings( [
			'default_container_width' => $payload['default_container_width'] ?? $default['default_container_width'],
			'container_padding' => $payload['container_padding'] ?? $default['container_padding'],
			'container_element_gap' => $payload['container_element_gap'] ?? $default['container_element_gap'],
			'enabled_assets_file_generation' => $payload['enabled_assets_file_generation'] ?? $default['enabled_assets_file_generation'],
			'enabled_block_copy_paste_style' => $payload['enabled_block_copy_paste_style'] ?? $default['enabled_block_copy_paste_style'],
			'enabled_only_selected_fonts' => $payload['enabled_only_selected_fonts'] ?? $default['enabled_only_selected_fonts'],
			'selected_fonts' => $payload['selected_fonts'] ?? $default['selected_fonts'],
			'enabled_coming_soon_page' => $payload['enabled_coming_soon_page'] ?? $default['enabled_coming_soon_page'],
			'coming_soon_page' => $payload['coming_soon_page'] ?? $default['coming_soon_page'],
			'enabled_maintenance_page' => $payload['enabled_maintenance_page'] ?? $default['enabled_maintenance_page'],
			'maintenance_page' => $payload['maintenance_page'] ?? $default['maintenance_page'],
		]);
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		do_action( 'ablocks/after_save_settings', $is_update, 'base', $_POST );
		wp_send_json_success( $is_update );
	}

	public function fetch_posts() {
		$payload = Sanitizer::sanitize_payload( [
			'postId'   => 'integer',
			'postType' => 'string',
			'keyword'  => 'string',
		], $_POST ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

		$post_type = ( isset( $payload['postType'] ) ? $payload['postType'] : 'page' );
		$postId    = ( isset( $payload['postId'] ) ? $payload['postId'] : 0 );
		$keyword   = ( isset( $payload['keyword'] ) ? $payload['keyword'] : '' );

		if ( $postId ) {
			$args = array(
				'post_type' => $post_type,
				'p'         => $postId,
			);
		} else {
			$args = array(
				'post_type'      => $post_type,
				'posts_per_page' => 10,
			);
			if ( ! empty( $keyword ) ) {
				$args['s'] = $keyword;
			}
			if ( ! current_user_can( 'manage_options' ) ) {
				$args['author'] = get_current_user_id();
			}
		}
		$results = array();
		$posts   = get_posts( $args );
		if ( is_array( $posts ) ) {
			foreach ( $posts as $post ) {
				$results[] = array(
					'label' => $post->post_title,
					'value' => $post->ID,
				);
			}
		}
		wp_send_json_success( $results );
	}
}
