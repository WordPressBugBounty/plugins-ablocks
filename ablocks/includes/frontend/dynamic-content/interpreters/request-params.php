<?php
namespace ABlocks\Frontend\DynamicContent\Interpreters;

use ABlocks\Helper;
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class RequestParams extends Abstracts\Interpreter {
	protected string $field;
	protected string $param;
	protected string $pre;
	protected string $post;
	protected string $default;

	public function __construct( string $name, array $setting, bool $is_richtext = false ) {
		parent::__construct( $name, $setting, $is_richtext );
		$this->field   = strtolower( strval( $this->setting[0] ?? 'get' ) );
		$this->param   = strval( $this->setting[1] ?? '' );
		$this->pre     = strval( $this->setting[3] ?? '' );
		$this->post    = strval( $this->setting[4] ?? '' );
		$this->default = strval( $this->setting[5] ?? '' );
		$this->field = empty( $this->field ) ? 'get' : $this->field;
	}
	public function content() : string {
		if ( ! is_admin() ) {
			switch ( $this->field ) {
				case 'get':
						// phpcs:ignore  WordPress.Security.NonceVerification.Recommended 
						$this->content = $this->req_param_value( $_GET );
					break;

				case 'post':
						// phpcs:ignore  WordPress.Security.NonceVerification.Missing 
						$this->content = $this->req_param_value( $_POST );
					break;

				case 'query var':
						$value         = get_query_var( $this->param, '' );
						$this->content = is_scalar( $value ) ? sanitize_text_field( (string) $value ) : '';
					break;

				default:
						$this->content = '';
			}
		}

		// The value is the visitor's; the labels are the author's.
		$value = $this->content === '' ? $this->esc_label( $this->default ) : $this->esc_value( $this->content );
		return $this->esc_label( $this->pre ) . $value . $this->esc_label( $this->post );
	}
	public function req_param_value( array $params ) : string {
		return sanitize_text_field( $params[ $this->param ] ?? '' );
	}
}
