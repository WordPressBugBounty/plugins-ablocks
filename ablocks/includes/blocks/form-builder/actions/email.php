<?php

namespace  ABlocks\Blocks\FormBuilder\Actions;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
class Email {
	protected $block;
	protected $response;
	protected $form_data;
	public function __construct( $block, $fields ) {
		$this->block = $block;
		$this->form_data = $fields;
		$line_break = true ? '<br>' : "\n";

		$data = $this->generate_email_content( $fields, $line_break );
	}
	public function get_response() {
		return $this->response; // Return the response
	}

	private function generate_email_content( $form_data, $line_break, $is_html = false ) {
		$content = '';

		if ( $is_html ) {
			$content .= '<table style="width:100%; border-collapse: collapse;">';
			foreach ( $form_data as $field_name => $field_value ) {
				$content .= sprintf(
					'<tr><td style="padding: 8px; border: 1px solid #ddd; font-weight: bold;">%s</td><td style="padding: 8px; border: 1px solid #ddd;">%s</td></tr>',
					ucfirst( esc_html( $field_name ) ),
					esc_html( $field_value )
				);
			}

			$content .= '</table>';
		} else {
			foreach ( $form_data as $field_name => $field_value ) {
				$content .= sprintf( '%s: %s', ucfirst( $field_name ), $field_value ) . $line_break;
			}
		}

		return $content;
	}
}
