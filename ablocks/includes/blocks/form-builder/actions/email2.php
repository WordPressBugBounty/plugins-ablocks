<?php

namespace  ABlocks\Blocks\FormBuilder\Actions;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
class Email2 {
	protected $block;
	protected $response;
	public function __construct( $block ) {
		$this->block = $block;
		$this->response = array(
			'massage' => 'calling_from_email2',
		);

	}
	public function get_response() {
		return $this->response; // Return the response
	}
}
