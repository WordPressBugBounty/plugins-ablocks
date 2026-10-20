<?php
namespace ABlocks\Frontend\DynamicContent\Interpreters;

use ABlocks\Helper;
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class Link extends Abstracts\Interpreter {
	protected int $post_id;
	protected string $field;

	public function __construct( string $name, array $setting, bool $is_richtext = false, $context = [] ) {
		parent::__construct( $name, $setting, $is_richtext );
		$this->field = strval( $this->setting[0] ?? '' );
		// Inside a loop the block's context names the card's post.
		$this->post_id = isset( $context['postId'] ) ? (int) $context['postId'] : (int) get_the_ID();
	}
	public function content() : string {
		switch ( $this->field ) {
			case 'internal-url-content':
				$this->content = get_permalink( $this->setting[2] );
				break;

			case 'internal-url-taxonomy':
				$term_link     = get_term_link( (int) $this->setting[2] );
				$this->content = is_string( $term_link ) ? $term_link : '';
				break;

			case 'internal-url-media':
				$this->content = get_attachment_link( $this->setting[2] );
				break;

			case 'internal-url-author':
				$this->content = get_author_posts_url( $this->setting[2] );
				break;

			case 'post-url':
				$this->content = get_permalink( $this->post_id );
				break;

			case 'site-url':
				$this->content = get_site_url();
				break;

			case 'author-url':
				$this->content = get_author_posts_url( get_the_author_meta( 'ID' ) );
				break;

			case 'comments-url':
				$this->content = get_comments_link( $this->post_id );
				break;

			case 'featured-image-file-url':
				$this->content = get_the_post_thumbnail_url( $this->post_id, 'full' );
				break;

			case 'featured-image-attachment-url':
				$attachment_id = get_post_meta( $this->post_id, '_thumbnail_id', true );
				$this->content = $attachment_id ? get_permalink( $attachment_id ) : '';
				break;

			case 'post-custom-field':
				$key = strval( $this->setting[1] ?? '' );
				// Same rules as the text field: no protected or secret keys,
				// and nothing from a password-protected post until it is unlocked.
				$meta_data     = $this->is_allowed_meta_key( $key, 'post' ) && ! post_password_required( $this->post_id )
					? get_post_meta( $this->post_id, $key, true )
					: '';
				$this->content = is_string( $meta_data ) ? trim( $meta_data ) : '';
				break;
		}//end switch
		return $this->esc_url_value( $this->content );
	}
}
