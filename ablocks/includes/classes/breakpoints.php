<?php
namespace ABlocks\Classes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ABlocks\Helper;

/**
 * Read/write the custom responsive breakpoints (the `breakpoint_custom` key of
 * the aBlocks settings option) from the block editor, so breakpoints can be
 * managed from the editor modal as well as the admin settings page.
 */
class Breakpoints {

	public static function init() {
		$self = new self();
		add_action( 'wp_ajax_ablocks/breakpoints/get', [ $self, 'ajax_get' ] );
		add_action( 'wp_ajax_ablocks/breakpoints/save', [ $self, 'ajax_save' ] );
	}

	/** The stored custom-breakpoint list (raw items). */
	public static function get_custom() {
		$c = Helper::get_settings( 'breakpoint_custom', [] );
		return is_array( $c ) ? array_values( $c ) : [];
	}

	/** Response payload shared by get + save: the raw list + resolved devices. */
	private static function payload() {
		return [
			'custom'  => self::get_custom(),
			'devices' => Helper::get_responsive_devices(),
		];
	}

	public function ajax_get() {
		check_ajax_referer( 'ablocks_nonce', 'security' );
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( [ 'message' => 'forbidden' ], 403 );
		}
		wp_send_json_success( self::payload() );
	}

	public function ajax_save() {
		check_ajax_referer( 'ablocks_nonce', 'security' );
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( [ 'message' => 'forbidden' ], 403 );
		}

		$list = [];
		if ( isset( $_POST['breakpoints'] ) ) {
			$decoded = json_decode( wp_unslash( $_POST['breakpoints'] ), true );
			if ( is_array( $decoded ) ) {
				$list = $this->sanitize( $decoded );
			}
		}

		// Merge into the settings option without disturbing other keys.
		$raw      = get_option( ABLOCKS_SETTINGS_NAME );
		$settings = $raw ? json_decode( $raw, true ) : [];
		if ( ! is_array( $settings ) ) {
			$settings = [];
		}
		$settings['breakpoint_custom'] = array_values( $list );
		update_option( ABLOCKS_SETTINGS_NAME, wp_json_encode( $settings ) );

		// Refresh the runtime settings global so this request sees the new list.
		global $ablocks_settings;
		$ablocks_settings = json_decode( get_option( ABLOCKS_SETTINGS_NAME ) );
		Helper::flush_responsive_devices_cache();

		wp_send_json_success( self::payload() );
	}

	private function sanitize( $list ) {
		$out  = [];
		$seen = [];
		foreach ( $list as $item ) {
			$item = (array) $item;
			$key  = isset( $item['key'] ) ? preg_replace( '/[^a-zA-Z0-9]/', '', (string) $item['key'] ) : '';
			if ( '' === $key || isset( $seen[ $key ] ) ) {
				$key = 'k' . substr( md5( wp_json_encode( $item ) . wp_rand() ), 0, 6 );
			}
			$seen[ $key ] = true;

			$label = isset( $item['label'] ) ? sanitize_text_field( $item['label'] ) : '';
			$min   = isset( $item['minWidth'] ) ? (int) $item['minWidth'] : 0;
			$max   = isset( $item['maxWidth'] ) ? (int) $item['maxWidth'] : ( isset( $item['width'] ) ? (int) $item['width'] : 0 );
			if ( $min < 1 && $max < 1 ) {
				continue; // needs at least one bound
			}

			$out[] = [
				'key'      => $key,
				'label'    => $label,
				'minWidth' => $min > 0 ? $min : '',
				'maxWidth' => $max > 0 ? $max : '',
			];
		}
		return $out;
	}
}
