<?php
namespace ABlocks\Frontend\DynamicContent\Interpreters;

use ABlocks\Helper;
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class Shortcode extends Abstracts\Interpreter {
	protected string $shortcode;
	protected string $pre;
	protected string $post;
	protected string $default;

	public function __construct( string $name, array $setting, bool $is_richtext = false ) {
		parent::__construct( $name, $setting, $is_richtext );
		$this->shortcode = strval( $this->setting[0] ?? '' );
		$this->pre     = strval( $this->setting[3] ?? '' );
		$this->post    = strval( $this->setting[4] ?? '' );
		$this->default = strval( $this->setting[5] ?? '' );
	}
	public function content() : string {
		$this->content = do_shortcode( $this->shortcode );
		// Shortcode HTML is fine in text, but in an attribute it would end
		// the attribute; flatten it there.
		$value = $this->content === '' ? $this->esc_label( $this->default ) : ( $this->in_attribute ? esc_attr( wp_strip_all_tags( $this->content ) ) : $this->content );
		return $this->esc_label( $this->pre ) . $value . $this->esc_label( $this->post );
	}
}
