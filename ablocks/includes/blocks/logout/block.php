<?php
namespace ABlocks\Blocks\Logout;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ABlocks\Classes\BlockBaseAbstract;
use ABlocks\Classes\CssGeneratorV2;
use ABlocks\Controls\Alignment;
use ABlocks\Controls\Range;
use ABlocks\Controls\Border;
use ABlocks\Controls\Typography;
use ABlocks\Controls\TextShadow;
use ABlocks\Controls\TextStroke;
use ABlocks\Controls\Color;


class Block extends BlockBaseAbstract {
	protected $block_name = 'logout';

	/**
	 * Query arg carrying the signature of a block-issued logout redirect URL.
	 */
	const REDIRECT_SIGNATURE_ARG = 'ablocks_rs';

	public function __construct( $keep_silent = false ) {
		parent::__construct( $keep_silent );

		if ( $this->is_enabled_block() && ! $keep_silent ) {
			add_filter( 'allowed_redirect_hosts', array( $this, 'allow_signed_logout_redirect_host' ) );
		}
	}

	/**
	 * wp-login.php ends a logout with wp_safe_redirect(), which swaps any host
	 * outside `allowed_redirect_hosts` for admin_url() — so a custom URL on
	 * another domain never took effect. Allow that host for this one request,
	 * and only when the redirect carries a signature this block produced from
	 * the site's secret salts: a hand-made or edited `redirect_to` stays
	 * rejected, so this is not an open redirect.
	 *
	 * @param string[] $hosts Allowed hosts.
	 * @return string[]
	 */
	public function allow_signed_logout_redirect_host( $hosts ) {
		global $pagenow;

		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- wp-login.php verifies the log-out nonce itself; this only reads the redirect target.
		if (
			'wp-login.php' !== $pagenow ||
			! isset( $_REQUEST['action'], $_REQUEST['redirect_to'], $_REQUEST[ self::REDIRECT_SIGNATURE_ARG ] ) ||
			'logout' !== $_REQUEST['action'] ||
			! is_string( $_REQUEST['redirect_to'] ) ||
			! is_string( $_REQUEST[ self::REDIRECT_SIGNATURE_ARG ] )
		) {
			return $hosts;
		}

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- compared against an HMAC, never output.
		$redirect  = wp_unslash( $_REQUEST['redirect_to'] );
		$signature = sanitize_text_field( wp_unslash( $_REQUEST[ self::REDIRECT_SIGNATURE_ARG ] ) );
		// phpcs:enable WordPress.Security.NonceVerification.Recommended

		if ( ! hash_equals( self::sign_redirect_url( $redirect ), $signature ) ) {
			return $hosts;
		}

		$host = wp_parse_url( $redirect, PHP_URL_HOST );
		if ( $host ) {
			$hosts[] = $host;
		}

		return $hosts;
	}

	private static function sign_redirect_url( $url ) {
		return wp_hash( 'ablocks-logout-redirect|' . $url, 'nonce' );
	}

	/**
	 * Normalize the configured custom URL, falling back to the home URL for an
	 * empty, non-HTTP(S) or malformed value.
	 *
	 * @param string $url Configured URL.
	 * @return string
	 */
	private static function sanitize_custom_redirect_url( $url ) {
		$url = esc_url_raw( trim( (string) $url ), array( 'http', 'https' ) );
		if ( '' === $url ) {
			return home_url();
		}

		$parts = wp_parse_url( $url );
		if ( false === $parts ) {
			return home_url();
		}

		// esc_url_raw() turns bare text such as "not a url" into "http://notaurl";
		// a real destination is either this site's host or a dotted domain name.
		if ( isset( $parts['host'] ) ) {
			$home_host = wp_parse_url( home_url(), PHP_URL_HOST );
			if ( $parts['host'] !== $home_host && false === strpos( $parts['host'], '.' ) ) {
				return home_url();
			}
		}

		return $url;
	}

	public function build_css( $attributes ) {
		$css_generator = new CssGeneratorV2( $attributes, $this->block_name );

		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-block-logout__label',
			$this->get_log_out_label_color( $attributes )
		);

		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-block-logout',
			$this->get_log_out_css( $attributes ),
			$this->get_log_out_css( $attributes, 'Tablet' ),
			$this->get_log_out_css( $attributes, 'Mobile' ),
			$css_generator->custom_device_map( function ( $device ) use ( $attributes ) { return $this->get_log_out_css( $attributes, $device ); } )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-block-logout__name',
			$this->get_name_css( $attributes )
		);

		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-block-logout__avatar',
			$this->get_avatar_css( $attributes ),
			$this->get_avatar_css( $attributes, 'Tablet' ),
			$this->get_avatar_css( $attributes, 'Mobile' ),
			$css_generator->custom_device_map( function ( $device ) use ( $attributes ) { return $this->get_avatar_css( $attributes, $device ); } )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-block-logout__avatar:hover',
			$this->get_avatar_border_hover_css( $attributes ),
			$this->get_avatar_border_hover_css( $attributes, 'Tablet' ),
			$this->get_avatar_border_hover_css( $attributes, 'Mobile' ),
			$css_generator->custom_device_map( function ( $device ) use ( $attributes ) { return $this->get_avatar_border_hover_css( $attributes, $device ); } )
		);

		return $css_generator->generate_css();
	}

	public function get_log_out_label_color( $attributes, $device = '' ) {
			$typography = isset( $attributes['labelTypography'] ) ? $attributes['labelTypography'] : '';
			$text_stroke = isset( $attributes['labelTextStroke'] ) ? $attributes['labelTextStroke'] : '';
			$text_shadow = isset( $attributes['labelTextShadow'] ) ? $attributes['labelTextShadow'] : '';
			$typographyglobal = ! empty( $attributes['labelTypographyGlobal'] ) ? $attributes['labelTypographyGlobal'] : array();
		return array_merge(
			[ 'color' => Color::get_css( isset( $attributes['logOutLabelColor'] ) ? $attributes['logOutLabelColor'] : '' ) ],
			[ 'background' => Color::get_css( isset( $attributes['logOutLabelBgColor'] ) ? $attributes['logOutLabelBgColor'] : '' ) ],
			Typography::get_css( $typography, '', $device, $typographyglobal ),
			TextStroke::get_css( $text_stroke, '', $device ),
			TextShadow::get_css( $text_shadow, '', $device ),
		);
	}

	public function get_log_out_css( $attributes, $device = '' ) {
		$log_out_css = [];
		if ( ! empty( $attributes['direction'][ 'value' . $device ] ) ) {
			$log_out_css['flex-direction'] = $attributes['direction'][ 'value' . $device ];
		}

		if ( isset( $attributes['labelAlignment'][ 'value' . $device ] ) ) {
			$log_out_css['justify-content'] = $attributes['labelAlignment'][ 'value' . $device ];
		}

		return array_merge(
			[ 'background' => Color::get_css( isset( $attributes['logOutLabelBgColor'] ) ? $attributes['logOutLabelBgColor'] : '' ) ],
			$log_out_css
		);
	}


	public function get_avatar_css( $attributes, $device = '' ) {

		return array_merge(
			Range::get_css([
				'attributeValue' => $attributes['avatarWidth'],
				'attribute_object_key' => 'value',
				'isResponsive' => true,
				'defaultValue' => 40,
				'hasUnit' => true,
				'unitDefaultValue' => 'px',
				'property' => 'width',
				'device' => $device,
			]),
			Range::get_css([
				'attributeValue' => $attributes['avatarHeight'],
				'attribute_object_key' => 'value',
				'isResponsive' => true,
				'defaultValue' => 40,
				'hasUnit' => true,
				'unitDefaultValue' => 'px',
				'property' => 'height',
				'device' => $device,
			]),
			isset( $attributes['avatarBorder'] ) ? Border::get_css( $attributes['avatarBorder'], '', $device ) : [],
		);
	}

	public function get_avatar_border_hover_css( $attributes, $device = '' ) {
		return array_merge(
			isset( $attributes['avatarBorder'] ) ? Border::get_hover_css( $attributes['avatarBorder'], '', $device ) : []
		);
	}

	public function get_name_css( $attributes, $device = '' ) {
			$typography = isset( $attributes['nameTypography'] ) ? $attributes['nameTypography'] : '';
			$text_stroke = isset( $attributes['nameTextStroke'] ) ? $attributes['nameTextStroke'] : '';
			$text_shadow = isset( $attributes['nameTextShadow'] ) ? $attributes['nameTextShadow'] : '';
			$typographyglobal = ! empty( $attributes['nameTypographyGlobal'] ) ? $attributes['nameTypographyGlobal'] : array();
		return array_merge(
			[ 'color' => Color::get_css( isset( $attributes['nameColor'] ) ? $attributes['nameColor'] : '' ) ],
			Typography::get_css( $typography, '', $device, $typographyglobal ),
			TextStroke::get_css( $text_stroke, '', $device ),
			TextShadow::get_css( $text_shadow, '', $device )
		);
	}

	public function render_block_content( $attributes, $content, $block_instance ) {
		$logout_redirect_option = isset( $attributes['logoutRedirect'] ) ? $attributes['logoutRedirect'] : 'current-url';

		if ( $logout_redirect_option === 'current-url' ) {
			// phpcs:ignore  WordPress.Security.ValidatedSanitizedInput.InputNotValidated, WordPress.Security.ValidatedSanitizedInput.MissingUnslash 
			$logout_redirect_url = ( is_ssl() ? 'https://' : 'http://' ) . sanitize_text_field( $_SERVER['HTTP_HOST'] ) . sanitize_text_field( $_SERVER['REQUEST_URI'] );
		} elseif ( $logout_redirect_option === 'custom-url' ) {
			$logout_redirect_url = self::sanitize_custom_redirect_url( isset( $attributes['logoutCustomUrl'] ) ? $attributes['logoutCustomUrl'] : '' );
		}

		$login_redirect_option = isset( $attributes['loginRedirect'] ) ? $attributes['loginRedirect'] : 'current-url';

		if ( $login_redirect_option === 'current-url' ) {
			// phpcs:ignore  WordPress.Security.ValidatedSanitizedInput.InputNotValidated, WordPress.Security.ValidatedSanitizedInput.MissingUnslash 
			$login_redirect_url = ( is_ssl() ? 'https://' : 'http://' ) . sanitize_text_field( $_SERVER['HTTP_HOST'] ) . sanitize_text_field( $_SERVER['REQUEST_URI'] );
		} elseif ( $login_redirect_option === 'custom-url' ) {
			$login_redirect_url = isset( $attributes['loginCustomUrl'] ) && ! empty( $attributes['loginCustomUrl'] )
				? esc_url( $attributes['loginCustomUrl'] )
				: home_url();
		}

		$current_user    = wp_get_current_user();
		$profile_picture = esc_url( get_avatar_url( $current_user->ID ) );
		$display_name    = sanitize_text_field( $current_user->display_name );

		$button_icon_url = isset( $attributes['buttonIconUrl'] ) && ! empty( $attributes['buttonIconUrl'] )
			? esc_url( $attributes['buttonIconUrl'] )
			: '';

		$button_class = isset( $attributes['buttonClass'] ) ? sanitize_html_class( $attributes['buttonClass'] ) : 'ablocks-block-logout__label';
		$is_logged_in = is_user_logged_in();
		$is_show_avatar = isset( $attributes['isShowAvatar'] ) && $attributes['isShowAvatar'];
		$is_show_name = isset( $attributes['isShowName'] ) && $attributes['isShowName'];

		$button_text = $is_logged_in
			? ( isset( $attributes['logOutLabel'] ) ? sanitize_text_field( $attributes['logOutLabel'] ) : __( '(Log Out)', 'ablocks' ) )
			: ( isset( $attributes['logInLabel'] ) ? sanitize_text_field( $attributes['logInLabel'] ) : __( '(Log In)', 'ablocks' ) );

		$action_url = $is_logged_in ? wp_logout_url( $logout_redirect_url ) : wp_login_url( $login_redirect_url );

		if ( $is_logged_in && 'custom-url' === $logout_redirect_option ) {
			// wp_logout_url() returns an HTML-escaped URL; decode it before adding
			// the arg, the anchor below escapes it again.
			$action_url = add_query_arg(
				self::REDIRECT_SIGNATURE_ARG,
				self::sign_redirect_url( $logout_redirect_url ),
				html_entity_decode( $action_url, ENT_QUOTES )
			);
		}

		ob_start();
		?>
		<div class="ablocks-block-logout">
			<?php if ( $is_logged_in && $is_show_avatar ) : ?>
				<img
						src="<?php echo esc_url( $profile_picture ); ?>"
						alt="<?php echo esc_attr( $display_name ); ?>"
						class="ablocks-block-logout__avatar"
				/>
			<?php endif; ?>
			<?php if ( $is_logged_in && $is_show_name ) : ?>
				<span class="ablocks-block-logout__name"><?php echo esc_html( $display_name ); ?></span>
			<?php endif; ?>
			<a href="<?php echo esc_url( $action_url ); ?>" class="<?php echo esc_attr( $button_class ); ?>">
				(<span><?php echo esc_html( $button_text ); ?></span>)
			</a>
		</div>
		<?php

		return ob_get_clean();
	}


}
