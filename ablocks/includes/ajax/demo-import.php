<?php

namespace ABlocks\Ajax;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ABlocks\Classes\AbstractAjaxHandler;
use ABlocks\Classes\Sanitizer;
use ABlocks\Helper;

class DemoImport extends AbstractAjaxHandler {

	public function __construct() {
		$this->actions = array(
			'check_dependencies'      => array(
				'callback' => array( $this, 'check_dependencies' ),
			),
			'install_and_active'      => array(
				'callback' => array( $this, 'install_and_active' ),
			),
			'import_template'      => array(
				'callback' => array( $this, 'import_template' ),
			),
		);
	}

	/**
	 * Check dependencies before importing pattern/page/template.
	 *
	 * @param array $data Dependency Data.
	 *
	 * @return void
	 */
	public function check_dependencies( array $data ) {
		if ( ! isset( $data['dependencies'] ) ) {
			return;
		}

		$dependencies = Sanitizer::sanitize_array_field( json_decode( $data['dependencies'], true ) );
		foreach ( $dependencies as $index => $dependency ) {
			$type = 'check_' . $dependency['type'];
			$status = Helper::$type( $dependency['slug'] );
			$dependencies[ $index ]['status'] = $status;
		}

		wp_send_json_success( $dependencies );
	}

	/**
	 * Install and active theme/plugin from ajax request.
	 *
	 * @param array $data post-data.
	 *
	 * @return void
	 */
	public function install_and_active( array $data ) {
		if ( ! isset( $data['dependency'] ) ) {
			return;
		}
		$dependency = Sanitizer::sanitize_array_field( json_decode( $data['dependency'], true ) );
		$method = 'install_and_active_' . $dependency['type'];
		$activated = Helper::$method( $dependency['slug'] );
		if ( is_wp_error( $activated ) ) {
			wp_send_json_error(array(
				'message' => $activated->get_error_message(),
			));
		}

		wp_send_json_success();
	}

	/**
	 * Import template from file url.
	 *
	 * @param array $data post-data.
	 *
	 * @return void
	 */
	public function import_template( array $data ) {
		if ( ! isset( $data['file_url'] ) ) {
			return;
		}
		$data = Sanitizer::sanitize_payload(array(
			'file_url' => 'string',
		), $data);
		$file_url = $data['file_url'];

		$template_import = Helper::import_template( $file_url );
		if ( is_wp_error( $template_import ) ) {
			wp_send_json_error(array(
				'message' => $template_import->get_error_message(),
			));
		}

		wp_send_json_success();
	}
}
