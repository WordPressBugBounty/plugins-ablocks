<?php

namespace ABlocks\Ajax;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ABlocks\Classes\AbstractAjaxHandler;
use ABlocks\Classes\FileUpload;
use ABlocks\Helper;
use ABlocks\Classes\Sanitizer;
use ABlocks\Blocks\FormBuilder\Actions\Email;
use ABlocks\Blocks\FormBuilder\Actions\Email2;
use ABlocks\Blocks\FormBuilder\Actions\Submission;


class FormBuilder extends AbstractAjaxHandler {
	public function __construct() {
		$this->actions = array(
			'form_builder_login_handler'      => array(
				'callback' => array( $this, 'form_builder_login_handler' ),
				'allow_visitor_action' => true,
			),
			'form_builder_registration_handler'      => array(
				'callback' => array( $this, 'form_builder_registration_handler' ),
				'allow_visitor_action' => true,
			),
			'form_builder_submit_handler'      => array(
				'callback' => array( $this, 'form_builder_submit_handler' ),
				'allow_visitor_action' => true,
			),
		);
	}
	public function form_builder_login_handler( $form_data ) {
		$payload = Sanitizer::sanitize_payload([
			'username'              => 'string',
			'password'              => 'string',
			'rememberme'            => 'string',
			'current_post_id'       => 'url',
			'redirect_url'          => 'url',
		], $form_data);

		$username = $payload['username'];
		$password = $payload['password'];
		$remember = isset( $payload['rememberme'] ) && $payload['rememberme'] === 'on';

		$secure_cookie = is_ssl();
		$user_signon = wp_signon( array(
			'user_login' => $username,
			'user_password' => $password,
			'remember'      => $remember,
		), $secure_cookie );

		if ( is_wp_error( $user_signon ) ) {
			wp_send_json_error( [ 'message' => $user_signon->get_error_message() ] );
		}

		wp_set_current_user( $user_signon->ID );

		$redirect_url = wp_validate_redirect( $payload['redirect_url'], get_permalink( $payload['current_post_id'] ) );
		if ( empty( $redirect_url ) ) {
			$redirect_url = home_url( '/' );
		}
		wp_send_json_success([
			'message' => __( 'You have logged in successfully. Redirecting...', 'ablocks' ),
			'redirect_url' => esc_url( $redirect_url )
		]);
	}

	public function form_builder_registration_handler( $form_data ) {
		$payload = Sanitizer::sanitize_payload([
			'current_post_id'    => 'integer',
			'block_id'           => 'string',
			'username'           => 'string',
			'email'              => 'email',
			'password'           => 'string',
			'confirm_password'   => 'string',
			'redirect_url'          => 'url',
		], $form_data);

		$errors = $this->validate_registration_data( $payload );
		if ( $errors ) {
			wp_send_json_error( [ 'message' => $errors ] );
		}

		$user_id = wp_create_user( $payload['username'], $payload['password'], $payload['email'] );
		if ( is_wp_error( $user_id ) ) {
			wp_send_json_error( [ 'message' => $user_id->get_error_message() ] );
		}

		$custom_fields = $this->get_custom_fields( $form_data );
		$block_data = Helper::get_block_attributes( $payload['current_post_id'], $payload['block_id'], 'ablocks/form-builder' );

		if ( ! empty( $block_data ) ) {
			$sorted_fields = Helper::sorted_input_fields_by_input_type( $block_data['innerBlocks'] );
			$schema = Helper::generate_schema_using_form_data( $sorted_fields );
			$sanitized_fields = Sanitizer::sanitize_payload( $schema, $custom_fields );

			array_walk($sanitized_fields, function ( $value, $key ) use ( $user_id ) {
				update_user_meta( $user_id, 'ablocks_' . $key, $value );
			});
		}

		wp_set_current_user( $user_id );
		wp_set_auth_cookie( $user_id );

		$redirect_url = wp_validate_redirect( $payload['redirect_url'], get_permalink( $payload['current_post_id'] ) );
		if ( empty( $redirect_url ) ) {
			$redirect_url = home_url( '/' );
		}
		wp_send_json_success([
			'message' => __( 'Registration completed successfully. Redirecting...', 'ablocks' ),
			'redirect_url' => esc_url( $redirect_url )
		]);
	}

	private function validate_registration_data( $payload ) {
		if ( empty( $payload['username'] ) || ! validate_username( $payload['username'] ) ) {
			return 'Invalid username.';
		} elseif ( username_exists( $payload['username'] ) ) {
			return 'Username already exists.';
		}

		if ( empty( $payload['email'] ) || ! is_email( $payload['email'] ) ) {
			return 'Invalid email address.';
		} elseif ( email_exists( $payload['email'] ) ) {
			return 'Email already exists.';
		}

		if ( empty( $payload['password'] ) || strlen( $payload['password'] ) < 6 ) {
			return 'Password must be at least 6 characters.';
		} elseif ( $payload['password'] !== $payload['confirm_password'] ) {
			return 'Passwords do not match.';
		}

		return null;
	}

	private function get_custom_fields( $form_data ) {
		$reserved_keys = [ 'username', 'email', 'password', 'confirm_password', 'current_post_id', 'block_id', 'security', 'action' ];
		return array_diff_key( $form_data, array_flip( $reserved_keys ) );
	}

	public function handle_form_submission( $form_data ) {
		$payload = Sanitizer::sanitize_payload([
			'current_post_id' => 'integer',
			'block_id' => 'string',
		], $form_data);

		$fields_to_skip = [ 'current_post_id', 'block_id', 'security', 'action' ];
		$all_fields = array_diff_key( $form_data, array_flip( $fields_to_skip ) );

		$block_data = Helper::get_block_attributes( $payload['current_post_id'], $payload['block_id'], 'ablocks/form-builder' );

		if ( ! empty( $block_data['parentAttributes']['formActions'] ) ) {
			$responses = array_map(function ( $action ) use ( $block_data ) {
				$action_class_map = [
					'email' => Email::class,
					'email2' => Email2::class,
					'submission' => Submission::class,
				];

				if ( isset( $action_class_map[ $action ] ) && class_exists( $action_class_map[ $action ] ) ) {
					return ( new $action_class_map[ $action ]( $block_data['innerBlocks'] ) )->get_response();
				}
				return null;
			}, $block_data['parentAttributes']['formActions']);

			wp_send_json_success( array_filter( $responses ) );
		} else {
			wp_send_json_error( [ 'message' => 'No form actions configured.' ] );
		}
	}


}
