<?php
namespace ABlocks\Blocks\Logout;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ABlocks\Classes\BlockBaseAbstract;
use ABlocks\Classes\CssGenerator;
use ABlocks\Controls\Alignment;
use ABlocks\Controls\Range;
use ABlocks\Controls\Border;
use ABlocks\Controls\Typography;
use ABlocks\Controls\TextShadow;
use ABlocks\Controls\TextStroke;


class Block extends BlockBaseAbstract {
	protected $block_name = 'logout';

	public function build_css( $attributes ) {

		// Generate CSS start
		$css_generator = new CssGenerator( $attributes, $this->block_name );

		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-block-logout__label',
			$this->get_log_out_label_color( $attributes )
		);

		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-block-logout',
			$this->get_log_out_css( $attributes ),
			$this->get_log_out_css( $attributes, 'Tablet' ),
			$this->get_log_out_css( $attributes, 'Mobile' )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-block-logout__name',
			$this->get_name_css( $attributes ),
		);

		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-block-logout__avatar',
			$this->get_avatar_css( $attributes ),
			$this->get_avatar_css( $attributes, 'Tablet' ),
			$this->get_avatar_css( $attributes, 'Mobile' )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-block-logout__avatar:hover',
			$this->get_avatar_border_hover_css( $attributes ),
			$this->get_avatar_border_hover_css( $attributes, 'Tablet' ),
			$this->get_avatar_border_hover_css( $attributes, 'Mobile' )
		);

		return $css_generator->generate_css();
	}


	public function get_log_out_label_color( $attributes, $device = '' ) {
		$log_out_label_color_css = [];
		if ( ! empty( $attributes['logOutLabelColor'] ) ) {
			$log_out_label_color_css['color'] = $attributes['logOutLabelColor'];
		}
		if ( ! empty( $attributes['logOutLabelBgColor'] ) ) {
			$log_out_label_color_css['background'] = $attributes['logOutLabelBgColor'];
		}

			$typography = isset( $attributes['labelTypography'] ) ? $attributes['labelTypography'] : '';
			$text_stroke = isset( $attributes['labelTextStroke'] ) ? $attributes['labelTextStroke'] : '';
			$text_shadow = isset( $attributes['labelTextShadow'] ) ? $attributes['labelTextShadow'] : '';

		return array_merge(
			Typography::get_css( $typography, '', $device ),
			TextStroke::get_css( $text_stroke, '', $device ),
			TextShadow::get_css( $text_shadow, '', $device ),
			$log_out_label_color_css
		);
	}

	public function get_log_out_css( $attributes, $device = '' ) {
		$log_out_css = [];

		if ( ! empty( $attributes['logOutLabelBgColor'] ) ) {
			$log_out_css ['background'] = $attributes['logOutLabelBgColor'];
		}
		if ( ! empty( $attributes['direction'][ 'value' . $device ] ) ) {
			$log_out_css['flex-direction'] = $attributes['direction'][ 'value' . $device ];
		}

		if ( isset( $attributes['labelAlignment'][ 'value' . $device ] ) ) {
			$log_out_css['justify-content'] = $attributes['labelAlignment'][ 'value' . $device ];
		}

		return $log_out_css;
	}


	public function get_avatar_css( $attributes, $device = '' ) {
		$avatar_css = [];

		if ( ! empty( $attributes['avatarWidth'][ 'value' . $device ] ) ) {
			$width_value = $attributes['avatarWidth'][ 'value' . $device ];
			$width_unit = $attributes['avatarWidth'][ 'valueUnit' . $device ] ?? 'px';
			$avatar_css['width'] = $width_value . $width_unit;
		}

		if ( ! empty( $attributes['avatarHeight'][ 'value' . $device ] ) ) {
			$height_value = $attributes['avatarHeight'][ 'value' . $device ];
			$height_unit = ! empty( $attributes['avatarHeight'][ 'valueUnit' . $device ] )
				? $attributes['avatarHeight'][ 'valueUnit' . $device ]
				: 'px';

			$avatar_css['height'] = $height_value . $height_unit;
		}

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
			$avatar_css,
		);
	}

	public function get_avatar_border_hover_css( $attributes, $device = '' ) {
		return array_merge(
			isset( $attributes['avatarBorder'] ) ? Border::get_hover_css( $attributes['avatarBorder'], '', $device ) : []
		);
	}

	public function get_name_css( $attributes, $device = '' ) {
		$name_css = [];

		if ( ! empty( $attributes['nameColor'] ) ) {
			$name_css ['color'] = $attributes['nameColor'];
		}
			$typography = isset( $attributes['nameTypography'] ) ? $attributes['nameTypography'] : '';
			$text_stroke = isset( $attributes['nameTextStroke'] ) ? $attributes['nameTextStroke'] : '';
			$text_shadow = isset( $attributes['nameTextShadow'] ) ? $attributes['nameTextShadow'] : '';

		return array_merge(
			Typography::get_css( $typography, '', $device ),
			TextStroke::get_css( $text_stroke, '', $device ),
			TextShadow::get_css( $text_shadow, '', $device ),
			$name_css
		);
	}



	public function render_block_content( $attributes, $content, $block_instance ) {
		$logout_redirect_option = isset( $attributes['logoutRedirect'] ) ? $attributes['logoutRedirect'] : 'current-url';

		if ( $logout_redirect_option === 'current-url' ) {
			$logout_redirect_url = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		} elseif ( $logout_redirect_option === 'custom-url' ) {
			$logout_redirect_url = isset( $attributes['logoutCustomUrl'] ) && ! empty( $attributes['logoutCustomUrl'] )
				? esc_url( $attributes['logoutCustomUrl'] )
				: home_url();
		}

		$login_redirect_option = isset( $attributes['loginRedirect'] ) ? $attributes['loginRedirect'] : 'current-url';

		if ( $login_redirect_option === 'current-url' ) {
			$login_redirect_url = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		} elseif ( $login_redirect_option === 'custom-url' ) {
			$login_redirect_url = isset( $attributes['loginCustomUrl'] ) && ! empty( $attributes['loginCustomUrl'] )
				? esc_url( $attributes['loginCustomUrl'] )
				: home_url();
		}

		$current_user    = wp_get_current_user();
		$profile_picture = get_avatar_url( $current_user->ID );
		$display_name    = $current_user->display_name;

		$button_icon_url = isset( $attributes['buttonIconUrl'] ) && ! empty( $attributes['buttonIconUrl'] )
			? esc_url( $attributes['buttonIconUrl'] )
			: '';

		$button_class = isset( $attributes['buttonClass'] ) ? esc_attr( $attributes['buttonClass'] ) : 'ablocks-block-logout__label';
		$is_logged_in = is_user_logged_in();
		$is_show_avatar = isset( $attributes['isShowAvatar'] ) && $attributes['isShowAvatar'];
		$is_show_name = isset( $attributes['isShowName'] ) && $attributes['isShowName'];

		$button_text = $is_logged_in
			? ( isset( $attributes['logOutLabel'] ) ? esc_html( $attributes['logOutLabel'] ) : __( '(Log Out)', 'ablocks' ) )
			: ( isset( $attributes['logInLabel'] ) ? esc_html( $attributes['logInLabel'] ) : __( '(Log In)', 'ablocks' ) );

		$action_url = $is_logged_in ? wp_logout_url( $logout_redirect_url ) : wp_login_url( $login_redirect_url );

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
