<?php

namespace ABlocks\API;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
use ABlocks\Helper;
use ABlocks\Blocks\FormBuilder\ValidateFormData;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FormBuilderController {

	public function register_routes() {

		register_rest_route(
			ABLOCKS_REST_NAMESPACE,
			'/form-builder/login',
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'login' ],
				'permission_callback' => '__return_true',
				'args'                => $this->login_schema(),
			]
		);

		register_rest_route(
			ABLOCKS_REST_NAMESPACE,
			'/form-builder/registration',
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'register' ],
				'permission_callback' => '__return_true',
				'args'                => $this->register_schema(),
			]
		);

		register_rest_route(
			ABLOCKS_REST_NAMESPACE,
			'/form-builder/forget_password',
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'forget_password' ],
				'permission_callback' => '__return_true',
				'args'                => $this->forget_schema(),
			]
		);

		register_rest_route(
			ABLOCKS_REST_NAMESPACE,
			'/form-builder/submit',
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'submit' ],
				'permission_callback' => '__return_true',
				'args'                => $this->submit_schema(),
			]
		);

		register_rest_route(
			ABLOCKS_REST_NAMESPACE,
			'/form-builder/subscription',
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'submit' ],
				'permission_callback' => '__return_true',
				'args'                => $this->submit_schema(),
			]
		);
	}

	private function prepare_res( array $data, array $block_data, string $redirect_url ) : array {

		$formType = $block_data['parentAttributes']['formType'] ?? '';

		if ( $formType === 'login' ) {
			$confirmationType = ( $block_data['parentAttributes']['loginRedirect'] ?? false )
				? 'redirect'
				: 'success';

		} elseif ( $formType === 'registration' ) {
			$confirmationType = ( $block_data['parentAttributes']['registerRedirect'] ?? false )
				? 'redirect'
				: 'success';

		} else {
			$confirmationType = $block_data['parentAttributes']['confirmationType'] ?? 'success';
		}

		return array_merge(
			$data,
			[
				'afterFormSubmission' => $block_data['parentAttributes']['afterFormSubmission'] ?? 'reset',
				'confirmationType'    => $confirmationType,
				'confirmationNotice'  => $block_data['parentAttributes']['confirmationNotice']
					?? $data['message']
					?? __( 'Form successfully submitted!', 'ablocks' ),
				'redirect_url'        => esc_url( $redirect_url ),
				'no_follow'           => $block_data['parentAttributes']['link']['noFollow'] ?? '',
				'link_target'         => $block_data['parentAttributes']['link']['linkTarget'] ?? '',
				'formType'            => $formType,
			]
		);
	}

	public function login( WP_REST_Request $request ) {

		$params = $request->get_params();

		$block_data = Helper::get_block_attributes(
			$params['current_post_id'],
			$params['block_id'],
			'ablocks/form-builder'
		);

		$redirect_url = '';

		if ( $block_data['parentAttributes']['loginRedirect'] ?? false ) {
			$redirect_url =
				\ABlocks\Blocks\FormBuilder\Helper::merge_query_params(
					$block_data['parentAttributes']['link']['href'] ?? '',
					$block_data['parentAttributes']['link']['keyValue'] ?? '',
					true
				);
		}

		$user = wp_signon(
			[
				'user_login'    => $params['username'],
				'user_password' => $params['password'],
				'remember'      => (bool) $params['rememberme'],
			],
			is_ssl()
		);

		if ( is_wp_error( $user ) ) {
			return new WP_REST_Response(
				[
					'success' => false,
					'data'    => $this->prepare_res(
						[ 'message' => $user->get_error_message() ],
						$block_data,
						$redirect_url
					),
				],
				400
			);
		}

		wp_set_current_user( $user->ID );

		if ( empty( $redirect_url ) ) {
			$redirect_url = home_url( '/' );
		}

		return new WP_REST_Response(
			[
				'success' => true,
				'data'    => $this->prepare_res(
					[
						'message' => __( 'You have logged in successfully. Redirecting...', 'ablocks' ),
					],
					$block_data,
					$redirect_url
				),
			],
			200
		);
	}

	public function register( WP_REST_Request $request ) {

		$params = $request->get_params();

		$block_data = Helper::get_block_attributes(
			$params['current_post_id'],
			$params['block_id'],
			'ablocks/form-builder'
		);

		$redirect_url = '';

		if ( $block_data['parentAttributes']['registerRedirect'] ?? false ) {
			$redirect_url =
				\ABlocks\Blocks\FormBuilder\Helper::merge_query_params(
					$block_data['parentAttributes']['link']['href'] ?? '',
					$block_data['parentAttributes']['link']['keyValue'] ?? '',
					true
				);
		}

		if ( ! get_option( 'users_can_register' ) ) {
			return new WP_REST_Response(
				[
					'success' => false,
					'data'    => $this->prepare_res(
						[ 'message' => __( 'User registration is turned off.', 'ablocks' ) ],
						$block_data,
						$redirect_url
					),
				],
				403
			);
		}

		if ( ! empty( $params['confirm_password'] ) && $params['password'] !== $params['confirm_password'] ) {
			return new WP_REST_Response(
				[
					'success' => false,
					'data'    => $this->prepare_res(
						[ 'message' => __( 'Passwords do not match.', 'ablocks' ) ],
						$block_data,
						$redirect_url
					),
				],
				400
			);
		}

		if ( username_exists( $params['username'] ) ) {
			return new WP_REST_Response(
				[
					'success' => false,
					'data'    => $this->prepare_res(
						[ 'message' => __( 'Username already exists.', 'ablocks' ) ],
						$block_data,
						$redirect_url
					),
				],
				400
			);
		}

		if ( email_exists( $params['email'] ) ) {
			return new WP_REST_Response(
				[
					'success' => false,
					'data'    => $this->prepare_res(
						[ 'message' => __( 'Email already exists.', 'ablocks' ) ],
						$block_data,
						$redirect_url
					),
				],
				400
			);
		}

		if ( strlen( $params['password'] ) < 6 ) {
			return new WP_REST_Response(
				[
					'success' => false,
					'data'    => $this->prepare_res(
						[ 'message' => __( 'Password must be at least 6 characters.', 'ablocks' ) ],
						$block_data,
						$redirect_url
					),
				],
				400
			);
		}

		$user_id = wp_create_user(
			$params['username'],
			$params['password'],
			$params['email']
		);

		if ( is_wp_error( $user_id ) ) {
			return new WP_REST_Response(
				[
					'success' => false,
					'data'    => $this->prepare_res(
						[ 'message' => $user_id->get_error_message() ],
						$block_data,
						$redirect_url
					),
				],
				400
			);
		}

		// Assign role if defined
		$role = $block_data['parentAttributes']['roleSlug'] ?? '';

		if (
			! empty( $role ) &&
			strtolower( $role ) !== 'default' &&
			array_key_exists( $role, wp_roles()->roles )
		) {
			( new \WP_User( $user_id ) )->set_role( $role );
		}

		// Save custom fields
		$reserved = [ 'username', 'email', 'password', 'current_post_id', 'block_id', 'confirm_password' ];
		$custom   = array_diff_key( $params, array_flip( $reserved ) );

		foreach ( $custom as $key => $value ) {
			update_user_meta(
				$user_id,
				'ablocks_' . sanitize_key( $key ),
				sanitize_text_field( $value )
			);
		}

		wp_set_current_user( $user_id );
		wp_set_auth_cookie( $user_id );

		if ( empty( $redirect_url ) ) {
			$redirect_url = home_url( '/' );
		}

		return new WP_REST_Response(
			[
				'success' => true,
				'data'    => $this->prepare_res(
					[
						'message' => __( 'Registration completed successfully. Redirecting...', 'ablocks' ),
					],
					$block_data,
					$redirect_url
				),
			],
			201
		);
	}

	public function forget_password( WP_REST_Request $request ) {

		$params = $request->get_params();

		$block_data = Helper::get_block_attributes(
			$params['current_post_id'],
			$params['block_id'],
			'ablocks/form-builder'
		);

		if ( empty( $block_data ) ) {
			return new WP_REST_Response(
				[
					'success' => false,
					'data'    => [
						'message' => __( 'Invalid form block.', 'ablocks' ),
					],
				],
				400
			);
		}

		$redirect_url = '';

		if ( $block_data['parentAttributes']['registerRedirect'] ?? false ) {
			$redirect_url = \ABlocks\Blocks\FormBuilder\Helper::merge_query_params(
				$block_data['parentAttributes']['link']['href'] ?? '',
				$block_data['parentAttributes']['link']['keyValue'] ?? '',
				true
			);
		}

		if ( empty( $params['email'] ?? '' ) ) {
			return new WP_REST_Response(
				[
					'success' => false,
					'data'    => $this->prepare_res(
						[ 'message' => __( 'Email field is required', 'ablocks' ) ],
						$block_data,
						$redirect_url
					),
				],
				400
			);
		}

		if ( ! is_email( $params['email'] ) ) {
			return new WP_REST_Response(
				[
					'success' => false,
					'data'    => $this->prepare_res(
						[ 'message' => __( 'Provide a valid email', 'ablocks' ) ],
						$block_data,
						$redirect_url
					),
				],
				400
			);
		}

		if ( ! email_exists( $params['email'] ) ) {
			return new WP_REST_Response(
				[
					'success' => false,
					'data'    => $this->prepare_res(
						[ 'message' => __( 'This email does not exist', 'ablocks' ) ],
						$block_data,
						$redirect_url
					),
				],
				400
			);
		}

		$result = retrieve_password( $params['email'] );

		if ( is_wp_error( $result ) ) {
			return new WP_REST_Response(
				[
					'success' => false,
					'data'    => $this->prepare_res(
						[ 'message' => esc_html( $result->get_error_message() ) ],
						$block_data,
						$redirect_url
					),
				],
				400
			);
		}

		return new WP_REST_Response(
			[
				'success' => true,
				'data'    => $this->prepare_res(
					[
						'message' => __( 'Password reset email is sent', 'ablocks' ),
					],
					$block_data,
					$redirect_url
				),
			],
			200
		);
	}


	public function submit( WP_REST_Request $request ) {

		$params = $request->get_params();

		$block_data = Helper::get_block_attributes(
			$params['current_post_id'],
			$params['block_id'],
			'ablocks/form-builder'
		);

		if ( empty( $block_data ) ) {
			return new WP_Error(
				'invalid_block',
				__( 'Invalid form block.', 'ablocks' ),
				[ 'status' => 400 ]
			);
		}

		$fields_to_skip = [ 'current_post_id', 'block_id' ];
		$all_fields     = array_diff_key( $params, array_flip( $fields_to_skip ) );

		$actions = apply_filters(
			'ablocks/form_builder/actions',
			[
				\ABlocks\Blocks\FormBuilder\Actions\SendEmails::class,
				\ABlocks\Blocks\FormBuilder\Actions\SaveFormData::class,
				\ABlocks\Blocks\FormBuilder\Actions\SendEmail::class,
				\ABlocks\Blocks\FormBuilder\Actions\Subscribe::class,
			]
		);

		$validate = new ValidateFormData( $block_data, $all_fields );
		$validate->actions( $actions );

		$output = $validate->get_output();
		$output['afterFormSubmission'] = $block_data['parentAttributes']['afterFormSubmission'] ?? 'reset';
		$output['confirmationType'] = $block_data['parentAttributes']['confirmationType'] ?? 'success';
		$output['formType'] = $block_data['parentAttributes']['formType'] ?? '';
		$output['redirect_url'] = $block_data['parentAttributes']['link']['href'] ?? '';
		$output['link_target'] = $block_data['parentAttributes']['link']['linkTarget'] ?? '';
		if ( $validate->has_error() ) {
			$output['message'] = $validate->get_error_message();
			wp_send_json_error( $output );
		} elseif ( $validate->has_message() ) {
			$output['confirmationNotice'] = $validate->apply_vars( $block_data['parentAttributes']['confirmationNotice'] ?? __( 'Form successfully submitted!', 'ablocks' ) );
			$output['message'] = $validate->get_message();

			wp_send_json_success( $output );
		}

		wp_send_json_error( [ 'message' => __( 'Action is not defined.', 'ablocks' ) ] );

		if ( $validate->has_error() ) {
			return new WP_Error(
				'form_error',
				$validate->get_error_message(),
				[ 'status' => 400 ]
			);
		}

		return new WP_REST_Response( $output, 200 );
	}

	private function login_schema() {
		return [

			'username' => [
				'required'          => true,
				'type'              => 'string',
				'minLength'         => 3,
				'maxLength'         => 60,
				'sanitize_callback' => 'sanitize_user',
				'validate_callback' => function( $value ) {
					return validate_username( $value );
				},
			],

			'password' => [
				'required'  => true,
				'type'      => 'string',
				'minLength' => 6,
				'maxLength' => 128,
			],

			'rememberme' => [
				'required'          => false,
				'type'              => 'boolean',
				'sanitize_callback' => 'rest_sanitize_boolean',
			],

			'current_post_id' => [
				'required'          => true,
				'type'              => 'integer',
				'sanitize_callback' => 'absint',
				'validate_callback' => function( $value ) {
					return $value > 0 && get_post( $value );
				},
			],

			'block_id' => [
				'required'          => true,
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'validate_callback' => function( $value ) {
					return preg_match( '/^[a-zA-Z0-9_\-]+$/', $value );
				},
			],
		];
	}


	private function register_schema() {
		return [

			'username' => [
				'required'          => true,
				'type'              => 'string',
				'minLength'         => 3,
				'maxLength'         => 60,
				'sanitize_callback' => 'sanitize_user',
				'validate_callback' => function( $value ) {
					return validate_username( $value );
				},
			],

			'email' => [
				'required'          => true,
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_email',
				'validate_callback' => function( $value ) {
					return is_email( $value );
				},
			],

			'password' => [
				'required'  => true,
				'type'      => 'string',
				'minLength' => 6,
				'maxLength' => 128,
				'validate_callback' => function( $value ) {
					return strlen( $value ) >= 6;
				},
			],

			'current_post_id' => [
				'required'          => true,
				'type'              => 'integer',
				'sanitize_callback' => 'absint',
				'validate_callback' => function( $value ) {
					return $value > 0 && get_post( $value );
				},
			],

			'block_id' => [
				'required'          => true,
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'validate_callback' => function( $value ) {
					return preg_match( '/^[a-zA-Z0-9_\-]+$/', $value );
				},
			],
		];
	}


	private function forget_schema() {
		return [
			'email' => [
				'required'          => true,
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_email',
			],
			'current_post_id' => [
				'required'          => true,
				'type'              => 'integer',
				'sanitize_callback' => 'absint',
			],
			'block_id' => [
				'required'          => true,
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			],
		];
	}


	private function submit_schema() {
		return [
			'current_post_id' => [
				'required'          => true,
				'type'     => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			],
			'block_id' => [
				'required'          => true,
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			],
		];
	}
}
