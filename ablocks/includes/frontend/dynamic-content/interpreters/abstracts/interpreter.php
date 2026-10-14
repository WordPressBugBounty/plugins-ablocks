<?php
namespace ABlocks\Frontend\DynamicContent\Interpreters\Abstracts;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
abstract class Interpreter {
	protected string $name;
	protected array $setting;
	protected string $content = '';
	protected bool $is_richtext;

	public function __construct( string $name, array $setting, bool $is_richtext = false ) {
		$this->name    = $name;
		$this->setting = $setting;
		$this->is_richtext = $is_richtext;
	}
	/**
	 * Whether the tag sits inside an HTML attribute rather than in text.
	 *
	 * @var bool
	 */
	protected bool $in_attribute = false;

	public function set_in_attribute( bool $in_attribute ) : void {
		$this->in_attribute = $in_attribute;
	}

	/**
	 * Escape a value that came from a visitor or a user profile.
	 *
	 * @param mixed $value Raw value.
	 * @return string
	 */
	protected function esc_value( $value ) : string {
		return $this->in_attribute ? esc_attr( (string) $value ) : esc_html( (string) $value );
	}

	/**
	 * Escape text the author typed around a value — labels, prefixes,
	 * fallbacks. Formatting survives in text; only script does not.
	 *
	 * @param mixed $value Raw value.
	 * @return string
	 */
	protected function esc_label( $value ) : string {
		return $this->in_attribute ? esc_attr( (string) $value ) : wp_kses_post( (string) $value );
	}

	/**
	 * Escape a URL. In an attribute it is entity-encoded as usual; in text it
	 * may sit in inline CSS, where `&#038;` would break a query string, so it
	 * is only cleaned — esc_url_raw() already drops `<`, `>`, `"` and spaces.
	 *
	 * @param mixed $value Raw URL.
	 * @return string
	 */
	protected function esc_url_value( $value ) : string {
		if ( ! is_string( $value ) || '' === $value ) {
			return '';
		}
		return $this->in_attribute ? esc_url( $value ) : esc_url_raw( $value );
	}

	abstract public function content() : string;
}
