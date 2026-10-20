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

	/**
	 * Whether a meta key may be printed. A tag is written by anyone who can
	 * edit a post, and its output can be sent anywhere (an image URL, a link),
	 * so secrets — hashes, tokens, roles, protected `_` keys — never resolve.
	 *
	 * @param string $key       Meta key or user field.
	 * @param string $meta_type 'post' or 'user'.
	 * @return bool
	 */
	protected function is_allowed_meta_key( $key, string $meta_type ) : bool {
		$key = is_string( $key ) ? trim( $key ) : '';
		if ( '' === $key || is_protected_meta( $key, $meta_type ) ) {
			return false;
		}
		if ( 'user' === $meta_type ) {
			global $wpdb;
			$lower = strtolower( $key );
			if ( in_array( $lower, [ 'user_pass', 'user_activation_key', 'session_tokens', 'user_level', 'capabilities', 'community-events-location' ], true ) ) {
				return false;
			}
			if ( 0 === strpos( $lower, strtolower( $wpdb->prefix ) ) || 0 === strpos( $lower, strtolower( $wpdb->base_prefix ) ) ) {
				return false;
			}
		}
		return ! preg_match( '/pass(word|wd)?(_|-|$)|user_pass|token|secret|api[_-]?key|private[_-]?key|nonce|(^|_)otp($|_)|2fa|totp|auth[_-]?(key|code)|salt|hash|activation/i', $key );
	}

	abstract public function content() : string;
}
