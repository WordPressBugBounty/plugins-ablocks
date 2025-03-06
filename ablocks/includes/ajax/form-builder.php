<?php

namespace ABlocks\Ajax;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ABlocks\Classes\AbstractAjaxHandler;
use ABlocks\Helper;
use ABlocks\Classes\Sanitizer;

use ABlocks\Blocks\FormBuilder\ValidateFormData;
use ABlocks\Blocks\FormBuilder\Actions\SaveFormData;
use ABlocks\Blocks\FormBuilder\Actions\SendEmail;
use ABlocks\Blocks\FormBuilder\Actions\Subscribe;

use ABlocks\Blocks\FormBuilder\Subscribe\Mailchimp;
use Exception;

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
			'form_builder_forget_password_handler'      => array(
				'callback' => array( $this, 'form_builder_forget_password_handler' ),
				'allow_visitor_action' => true,
			),
			'form_builder_submit_handler'      => array(
				'callback' => array( $this, 'handle_form_submission' ),
				'allow_visitor_action' => true,
			),
			'form_builder_action_setting_data'      => array(
				'callback' => array( $this, 'action_setting_data' )
			),
			'registration_form_setting_data'      => array(
				'callback' => array( $this, 'registration_form_setting_data' ),
				'capability' => 'edit_posts'
			),
		);

	}
	public function form_builder_login_handler( $form_data ) {
		$payload = Sanitizer::sanitize_payload([
			'username'              => 'string',
			'password'              => 'string',
			'rememberme'            => 'string',
			'current_post_id'       => 'integer',
			'block_id'              => 'string',
		], $form_data);

		$block_data = Helper::get_block_attributes( $payload['current_post_id'], $payload['block_id'], 'ablocks/form-builder' );

		$redirect_url = '';
		if ( $block_data['parentAttributes']['loginRedirect'] ?? false ) {
			$redirect_url = \ABlocks\Blocks\FormBuilder\Helper::merge_query_params(
				$block_data['parentAttributes']['link']['href'] ?? '',
				$block_data['parentAttributes']['link']['keyValue'] ?? '',
				true
			);
		}

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

		if ( empty( $redirect_url ) ) {
			$redirect_url = home_url( '/' );
		}
		wp_send_json_success([
			'message'      => __( 'You have logged in successfully. Redirecting...', 'ablocks' ),
			'redirect_url' => esc_url( $redirect_url ),
			'no_follow'    => $block_data['parentAttributes']['link']['noFollow'] ?? '',
			'link_target'  => $block_data['parentAttributes']['link']['linkTarget'] ?? '',
		]);
	}

	public function registration_form_setting_data( $form_data ) {
		$roles = [
			'default' => __( 'Default', 'ablocks' )
		];

		if ( current_user_can( 'manage_options' ) ) {
			foreach ( wp_roles()->roles ?? [] as $slug => ['name' => $name] ) {
				$roles[ $slug ] = $name;
			}
		}
		wp_send_json_success( $roles );
	}
	public function form_builder_registration_handler( $form_data ) {
		$payload = Sanitizer::sanitize_payload([
			'current_post_id'    => 'integer',
			'block_id'           => 'string',
			'username'           => 'string',
			'email'              => 'email',
			'password'           => 'string',
			'confirm_password'   => 'string',
			'current_post_id'       => 'integer',
		], $form_data);

		$block_data = Helper::get_block_attributes( $payload['current_post_id'], $payload['block_id'], 'ablocks/form-builder' );

		if ( ! get_option( 'users_can_register' ) ) {
			wp_send_json_error([
				'message' => __( 'User registration is turned off. Turn it on to allow user registration.', 'ablocks' )
			]);
		}

		if ( ( $block_data['parentAttributes']['formType'] ?? '' ) !== 'registration' ) {
			wp_send_json_error([
				'message' => __( 'Error.', 'ablocks' )
			]);
		}

		$redirect_url = '';
		if ( $block_data['parentAttributes']['registerRedirect'] ?? false ) {
			$redirect_url = \ABlocks\Blocks\FormBuilder\Helper::merge_query_params(
				$block_data['parentAttributes']['link']['href'] ?? '',
				$block_data['parentAttributes']['link']['keyValue'] ?? '',
				true
			);
		}

		$errors = $this->validate_registration_data( $payload );
		if ( $errors ) {
			wp_send_json_error( [ 'message' => $errors ] );
		}

		$user_id = wp_create_user( $payload['username'], $payload['password'], $payload['email'] );
		if ( is_wp_error( $user_id ) ) {
			wp_send_json_error( [ 'message' => $user_id->get_error_message() ] );
		}
		$role = ( $block_data['parentAttributes']['roleSlug'] ?? '' );
		if (
			! empty( $role ) &&
			strtolower( $role ) !== 'default' &&
			array_key_exists( $role, wp_roles()->roles )
		) {
			$user = new \WP_User( $user_id );
			$user->set_role( $role );
		}

		$custom_fields = $this->get_custom_fields( $form_data );

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

		if ( empty( $redirect_url ) ) {
			$redirect_url = home_url( '/' );
		}
		wp_send_json_success([
			'message'      => __( 'Registration completed successfully. Redirecting...', 'ablocks' ),
			'redirect_url' => esc_url( $redirect_url ),
			'no_follow'    => $block_data['parentAttributes']['link']['noFollow'] ?? '',
			'link_target'  => $block_data['parentAttributes']['link']['linkTarget'] ?? '',
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

	public function form_builder_forget_password_handler( $form_data ) {
		$payload = Sanitizer::sanitize_payload([
			'email' => 'string',
		], $form_data);

		if ( empty( $payload['email'] ?? '' ) ) {
			wp_send_json_error([
				'message' => __( 'email field is required.', 'ablocks' )
			], 400);
		}

		if ( ! is_email( $payload['email'] ) ) {
			wp_send_json_error([
				'message' => __( 'provide a valid email.', 'ablocks' )
			], 400);
		}

		if ( ! email_exists( $payload['email'] ) ) {
			wp_send_json_error([
				'message' => __( 'this email is not exists.', 'ablocks' )
			], 400);
		}
		$error = retrieve_password( $payload['email'] );
		if ( is_wp_error( $error ) ) {
			wp_send_json_error([
				'message' => esc_html( $error->get_error_message() )
			], 500);
		}
		wp_send_json_success([
			'message' => __( 'Password reset email is sent.', 'ablocks' )
		]);
	}

	public function handle_form_submission( $form_data ) {
		$payload = Sanitizer::sanitize_payload([
			'current_post_id' => 'integer',
			'block_id' => 'string',
		], $form_data);

		$fields_to_skip = [ 'current_post_id', 'block_id', 'security', 'action' ];
		$all_fields = array_diff_key( $form_data, array_flip( $fields_to_skip ) );
		$block_data = Helper::get_block_attributes( $payload['current_post_id'], $payload['block_id'], 'ablocks/form-builder' );

		$actions = apply_filters('ablocks/form_builder/actions', [
			SaveFormData::class,
			SendEmail::class,
			Subscribe::class,
		]);

		$validate_data = new ValidateFormData( $block_data, $all_fields );

		$validate_data->actions( $actions );

		$output = $validate_data->get_output();
		if ( $validate_data->has_error() ) {
			$output['message'] = $validate_data->get_error_message();
			wp_send_json_error( $output );
		} elseif ( $validate_data->has_message() ) {
			$output['message'] = $validate_data->get_message();
			wp_send_json_success( $output );
		}

		wp_send_json_error( [ 'message' => __( 'Action is not defined.', 'ablocks' ) ] );
	}

	public function action_setting_data( $form_data ) {
		$payload = Sanitizer::sanitize_payload([
			'type' => 'string',
			'api'  => 'string',
			'list_id' => 'string',
		], $form_data);

		$type         = $payload['type'] ?? '';
		$settings_key = $type . '_api_key';
		$api          = $payload['api'] ?? $GLOBALS['ablocks_settings']->{$settings_key} ?? '';
		$list_id      = $payload['list_id'] ?? '';

		$api_key      = $api;

		if ( empty( $api_key ) ) {
			wp_send_json_error( [ 'message' => __( 'api key is required', 'ablocks' ) ] );
		}

		$action_config = apply_filters('ablocks/form_builder/action_config', [
			'mailchimp' => function( $api_key ) use ( $list_id ) {
				if ( ! class_exists( Mailchimp::class ) ) {
					return;
				}

				if ( empty( $list_id ) ) {
					$data = ( new Mailchimp(
						$api_key,
						'',
						''
					) )
					->get_lists();
					wp_send_json_success( $data );
				} else {

					$data = ( new Mailchimp(
						$api_key,
						'',
						''
					) )
					->get_groups( $list_id );
				}

				wp_send_json_success( $data );
			}
		]);

		if ( ! array_key_exists( $type, $action_config ) ) {
			wp_send_json_error( [ 'message' => __( 'Not a valid type', 'ablocks' ) ] );
		}

		try {
			if (
				array_key_exists( $type, $action_config ) &&
				is_callable( $action_config[ $type ] )
			) {
				$action_config[ $type ]( $api_key, $payload );
			}
		} catch ( Exception $e ) {
			wp_send_json_error( [ 'message' => esc_html( $e->getMessage() ) ], 400 );
		}

		wp_send_json_error( [ 'message' => __( 'Unspecified service type', 'ablocks' ) ], 400 );
	}
}
