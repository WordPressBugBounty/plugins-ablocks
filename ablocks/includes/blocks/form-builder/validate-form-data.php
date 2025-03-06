<?php

namespace ABlocks\Blocks\FormBuilder;

use ABlocks\Blocks\FormBuilder\Actions\Interfaces\FormSubmissionAction;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * @class Validateformdata
 * Validate form data by whitelised key & sanitize input value
 * Add & call action
 */
final class ValidateFormData {


	/** @var $is_form_data_empty */
	private bool $is_form_data_empty;
	/** @var $stop_action_exec */
	public array $state_data = [];

	/** @var $block_data */
	private ?array $block_data;
	/** @var $input_data */
	private ?array $input_data;

	/** @var $filtered_data */
	private ?array $filtered_data = [];
	/** @var $errors */
	private ?array $errors = [];
	/** @var $messages */
	private ?array $messages = [
		'default' => [],
	];
	/** @var $output */
	private ?array $output = [];

	/** @var $form_info */
	public ?array $form_info = [];

	/**
	 * Constructor
	 *
	 * @param array $block_data
	 * @param array $input_data
	 */
	public function __construct( ?array $block_data, ?array $input_data ) {
		$this->block_data = $block_data;
		$this->input_data = $input_data;

		$this->is_form_data_empty = count( $input_data ) === 0;

		$this->filter_data_by_whitelisted_key();

		$this->form_info = [
			'info' => [
				'type'    => sanitize_text_field( $block_data['parentAttributes']['formType'] ?? null ),
				'postId'  => sanitize_text_field( $block_data['parentAttributes']['postId'] ?? null ),
				'email'   => sanitize_email( $this->filtered_data['email']['value'] ?? null ),
				'actions' => $block_data['parentAttributes']['formActions'] ?? [],
				'config'  => $block_data['parentAttributes'] ?? [],
			],
			'data' => $this->filtered_data
		];

		switch ( true ) {
			case $this->is_form_data_empty || count( $this->filtered_data ) === 0:
				$this->set_error_message( __( 'Form is empty.', 'ablocks' ) );
				break;
		}
	}
	/**
	 * @method filter_data_by_whitelisted_key
	 * @return array
	 */
	private function filter_data_by_whitelisted_key(): array {
		if ( ( $this->block_data['innerBlocks'][0]['blockName'] ?? '' ) === 'ablocks/form-multi-step' ) {

			foreach ( $this->block_data['innerBlocks'][0]['innerBlocks'] ?? [] as $block ) {
				foreach ( $block['innerBlocks'] as ['attributes' => $attr] ) {
					$name  = $attr['name'] ?? false;
					// check current input name exists in $all_fields
					if ( $name && array_key_exists( $name, $this->input_data ) ) {
						$this->filtered_data[ $name ] = [
							'value' => sanitize_text_field( $this->input_data[ $name ] ), // sanitize value
						];
					}
				}
			}
		} elseif (
			isset( $this->block_data['innerBlocks'] ) &&
			! $this->is_form_data_empty
		) {
			$this->filtered_data = [];
			foreach ( $this->block_data['innerBlocks'] as $input_element_data ) {
				$name  = $input_element_data['attributes']['name'] ?? false;
				[ 'placeholder' ] ?? false;
				// check current input name exists in $all_fields
				if ( $name && array_key_exists( $name, $this->input_data ) ) {
					$this->filtered_data[ $name ] = [
						'value' => sanitize_text_field( $this->input_data[ $name ] ), // sanitize value
					];
				}
			}
		}//end if

		return $this->filtered_data;
	}
	/**
	 * Get Filtered data
	 *
	 * @return void
	 */
	// phpcs:ignore Squiz.Commenting.FunctionComment.WrongStyle
	public function get_data() {
		return $this->filtered_data;
	}
	/**
	 * Get errors as string
	 *
	 * @return string
	 */
	public function get_error_message() {
		return implode( "\n", $this->errors );
	}
	/**
	 * Set error
	 *
	 * @param string $error
	 * @return void
	 */
	public function set_error_message( string $error ) {
		// phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
		if ( in_array( $error, $this->errors ) ) {
			return;
		}
		$this->errors[] = sanitize_text_field( $error );
	}
	/**
	 * Check error
	 *
	 * @return bool
	 */
	public function has_error() {
		return count( $this->errors ) > 0;
	}
	/**
	 * Get msg as string
	 *
	 * @param string $key
	 * @return string
	 */
	public function get_message( string $key = 'default' ) {
		return implode( "\n", $this->messages[ $key ] );
	}

	/**
	 * Set msg
	 *
	 * @param string $msg
	 * @param string $key
	 * @return void
	 */
	public function set_message( string $msg, string $key = 'default' ) {
		if ( in_array( $msg, array_key_exists( $key, $this->messages ) ? $this->messages[ $key ] : [], true ) ) {
			return;
		}
		$this->messages[ $key ][] = sanitize_text_field( $msg );
	}
	/**
	 * Check msg
	 *
	 * @return bool
	 */
	public function has_message() {
		return count( $this->messages ) > 0;
	}
	/**
	 * Get output as array
	 *
	 * @return string
	 */
	public function get_output(): array {
		return $this->output;
	}
	/**
	 * Set output
	 *
	 * @param string $key
	 * @param mixed  $data
	 * @return void
	 */
	public function set_output( string $key, $data ): void {
		$this->output[ $key ] = $data;
	}
	/**
	 * Check output
	 *
	 * @return bool
	 */
	public function has_output(): bool {
		return count( $this->output ) > 0;
	}
	/**
	 * Add and call action
	 *
	 * @param array $classes
	 */
	public function actions( array $classes = [] ) {
		// check classes var is not  empty & no error
		if ( count( $classes ) && $this->has_error() === false ) {
			// iterate
			foreach ( $classes as $class ) {
				// instantiate action class
				if ( ! class_exists( $class ) ) {
					continue;
				}
				$action_obj = new $class( $this );
				// check for  object is a instance of  valid action class
				if ( $action_obj instanceof FormSubmissionAction ) {
					// call action
					$action_obj->action();
				}
			}
		}
	}
}
