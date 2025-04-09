<?php
namespace ABlocks\Frontend\DynamicContent;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class Interpreter {

	protected array $interpreters = [
		'post-type' => Interpreters\PostType::class,
		'current'   => Interpreters\CurrentPost::class,
		'current-date-time' => Interpreters\CurrentDateTime::class,
		'site-title'   => Interpreters\SiteTitle::class,
		'site-tagline' => Interpreters\SiteTagline::class,
		'user-info'    => Interpreters\UserInfo::class,
		'request-parameter' => Interpreters\RequestParams::class,
		'shortcode' => Interpreters\Shortcode::class,
		'link'  => Interpreters\Link::class,
		'image' => Interpreters\Image::class,
	];
	protected string $content;
	public function __construct( string $content ) {
		$this->content = $content;
		$this->parse();// exit;
	}
	public function parse() : void {
		$this->content = preg_replace_callback(
			'~ablocks_dc:(.+?):ablocks_dc|<span\s+(?=[^>]*\bclass=["\']ablocks-richtext-dynamic-content["\'])(?=[^>]*\bdata-source=["\']([^"\']+)["\'])(?=[^>]*\bdata-field=["\']([^"\']+)["\'])[^>]*>(.*?)<\/span>~ims',
			[ $this, 'apply_changes' ],
			$this->content
		);
	}
	public function apply_changes( array $matches ) : string {
		return is_null( $ins = ( new ArgumentParser( ...$this->filter( $matches ), ...[ $this->interpreters ] ) )->interpret() ) ? '' : $ins->content();
	}

	public function filter( array $matches ) : array {
		$is_richtext = false;
		array_shift( $matches );
		if ( count( $matches ) > 2 ) {
			array_shift( $matches );
			$is_richtext = true;
		}
		return [
			implode( '|', $matches ),
			$is_richtext
		];
	}

	public static function init( string $content ) : string {
		$ins = new self( $content );
		return $ins->content;
	}
}
