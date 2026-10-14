<?php
namespace ABlocks\Frontend\DynamicContent\Interpreters;

use ABlocks\Helper;
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
abstract class SiteInfo extends Abstracts\Interpreter {
	abstract public function info() : string;

	public function content() : string {
		$this->setting = array_values( array_filter( $this->setting ) );
		$info = $this->esc_value( $this->info() );
		if ( count( $this->setting ) <= 2 ) {
			return $info;
		}
		return $this->esc_label( $this->setting[0] ?? '' ) . ( '' === $info ? $this->esc_label( $this->setting[2] ?? '' ) : $info ) . $this->esc_label( $this->setting[1] ?? '' );
	}
}
