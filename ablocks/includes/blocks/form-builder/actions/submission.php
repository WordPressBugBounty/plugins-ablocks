<?php

namespace  ABlocks\Blocks\FormBuilder\Actions;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
class Submission {
	protected $block;
	protected $response;
	public function __construct( $block ) {
		$this->block = $block;
		$this->response = array(
			'massage' => 'calling_from_submission',
		);

	}
	public function get_response() {
		return $this->response; // Return the response
	}
}
