<?php
namespace ABlocks\Blocks\AcademyLoginForm;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ABlocks\Classes\BlockBaseAbstract;
use ABlocks\Classes\CssGenerator;
use ABlocks\Helper;
use ABlocks\Controls\Typography;
use ABlocks\Controls\Background;
use ABlocks\Controls\Border;
use ABlocks\Controls\Dimensions;


class Block extends BlockBaseAbstract {
	protected $block_name = 'academy-login-form';

	public function __construct() {
		parent::__construct();

		add_filter( 'academy/shortcode/login_form_is_user_logged_in', [ $this, 'force_showing_login_form_in_editor' ] );

	}

	public function force_showing_login_form_in_editor( $flag ) {
		if ( Helper::is_gutenberg_editor() ) {
			return false;
		}
		return $flag;
	}

	public function build_css( $attributes ) {
		$css_generator = new CssGenerator( $attributes );

		$login_form_button_desktop_css = $this->get_login_form_button_css( $attributes );
		if ( ! empty( $attributes['login_btn_color'] ) ) {
			$login_form_button_desktop_css['color'] = $attributes['login_btn_color'];
		}
		$css_generator->add_class_styles(
			'{{WRAPPER}} .academy-login-form-wrapper .academy-login-form .academy-form-group button',
			$login_form_button_desktop_css,
			$this->get_login_form_button_css( $attributes, 'Tablet' ),
			$this->get_login_form_button_css( $attributes, 'Mobile' )
		);

		$css_generator->add_class_styles(
			'{{WRAPPER}} .academy-login-form-wrapper .academy-login-form .academy-form-group button:hover',
			$this->get_login_btn_hover_css( $attributes ),
			$this->get_login_btn_hover_css( $attributes, 'Tablet' ),
			$this->get_login_btn_hover_css( $attributes, 'Mobile' )
		);

		$login_form_footer_desktop_css = $this->get_login_form_footer_css( $attributes );
		if ( ! empty( $attributes['form_footer_title_color'] ) ) {
			$login_form_footer_desktop_css['color'] = $attributes['form_footer_title_color'];
		}
		$css_generator->add_class_styles(
			'{{WRAPPER}} .academy-login-form-wrapper .academy-login-form-info,
			{{WRAPPER}} .academy-login-form-wrapper .academy-login-form-info a ',
			$login_form_footer_desktop_css,
			$this->get_login_form_footer_css( $attributes, 'Tablet' ),
			$this->get_login_form_footer_css( $attributes, 'Mobile' )
		);

		$form_title_desktop_css = $this->get_form_title_css( $attributes );
		if ( ! empty( $attributes['title_color'] ) ) {
			$form_title_desktop_css['color'] = $attributes['title_color'];
		}
		$css_generator->add_class_styles(
			'{{WRAPPER}} .academy-login-form-wrapper .academy-login-form-heading',
			$form_title_desktop_css,
			$this->get_form_title_css( $attributes, 'Tablet' ),
			$this->get_form_title_css( $attributes, 'Mobile' )
		);

		$form_title_desktop_hover_css = [];
		if ( ! empty( $attributes['title_hover_color'] ) ) {
			$form_title_desktop_hover_css['color'] = $attributes['title_hover_color'];
		}
		$css_generator->add_class_styles(
			'{{WRAPPER}} .academy-login-form-wrapper .academy-login-form-heading:hover',
			$form_title_desktop_hover_css,
		);

		$input_field_label_desktop_css = $this->get_input_field_label_css( $attributes );
		if ( ! empty( $attributes['input_field_label_color'] ) ) {
			$input_field_label_desktop_css['color'] = $attributes['input_field_label_color'];
		}
		$css_generator->add_class_styles(
			'{{WRAPPER}} .academy-login-form-wrapper .academy-login-form label,
			{{WRAPPER}} .academy-login-form-wrapper .academy-login-form a',
			$input_field_label_desktop_css,
			$this->get_input_field_label_css( $attributes, 'Tablet' ),
			$this->get_input_field_label_css( $attributes, 'Mobile' )
		);

		$css_generator->add_class_styles(
			'{{WRAPPER}} .academy-login-form-wrapper .academy-login-form label:hover,
			{{WRAPPER}} .academy-login-form-wrapper .academy-login-form a:hover',
			$this->get_input_field_label_hover_css( $attributes ),
			$this->get_input_field_label_hover_css( $attributes, 'Tablet' ),
			$this->get_input_field_label_hover_css( $attributes, 'Mobile' )
		);

		$css_generator->add_class_styles(
			'{{WRAPPER}} .academy-login-form-wrapper .academy-login-form .academy-form-group input',
			$this->get_input_field_css( $attributes ),
			$this->get_input_field_css( $attributes, 'Tablet' ),
			$this->get_input_field_css( $attributes, 'Mobile' )
		);

		$css_generator->add_class_styles(
			'{{WRAPPER}} .academy-login-form-wrapper .academy-login-form .academy-form-group input::placeholder',
			$this->get_input_field_placeholder_css( $attributes ),
			$this->get_input_field_placeholder_css( $attributes, 'Tablet' ),
			$this->get_input_field_placeholder_css( $attributes, 'Mobile' )
		);

		$css_generator->add_class_styles(
			'{{WRAPPER}} .academy-login-form-wrapper',
			$this->get_form_card_css( $attributes ),
			$this->get_form_card_css( $attributes, 'Tablet' ),
			$this->get_form_card_css( $attributes, 'Mobile' )
		);

		$css_generator->add_class_styles(
			'{{WRAPPER}} .academy-login-form-wrapper:hover',
			$this->get_form_card_hover_css( $attributes ),
			$this->get_form_card_hover_css( $attributes, 'Tablet' ),
			$this->get_form_card_hover_css( $attributes, 'Mobile' )
		);

		return $css_generator->generate_css();
	}

	public function get_login_form_button_css( $attributes, $device = '' ) {
		$login_form_button_typography_css = ! empty( $attributes['login_btn_typography'] ) ? Typography::get_css( $attributes['login_btn_typography'], '', $device ) : array();
		$login_form_button_bg_css = array();

		if ( ! empty( $attributes['login_btn_bg_color'] ) ) {
			$login_form_button_bg_css['background'] = $attributes['login_btn_bg_color'];
		}
		return array_merge(
			$login_form_button_typography_css,
			$login_form_button_bg_css,
		);
	}

	public function get_login_form_footer_css( $attributes, $device = '' ) {
		$login_form_footer_typography_css = ! empty( $attributes['form_footer_title_typography'] ) ? Typography::get_css( $attributes['form_footer_title_typography'], '', $device ) : array();
		return $login_form_footer_typography_css;
	}

	public function get_form_title_css( $attributes, $device = '' ) {
		$form_title_typography_css = ! empty( $attributes['title_typography'] ) ? Typography::get_css( $attributes['title_typography'], '', $device ) : array();
		return $form_title_typography_css;
	}

	public function get_course_card_title_css( $attributes, $device = '' ) {
		$course_title_typography_css = ! empty( $attributes['title_typography'] ) ? Typography::get_css( $attributes['title_typography'], '', $device ) : array();
		return $course_title_typography_css;
	}

	public function get_input_field_label_css( $attributes, $device = '' ) {
		$input_field_label_typography_css = ! empty( $attributes['input_field_label_typography'] ) ? Typography::get_css( $attributes['input_field_label_typography'], '', $device ) : array();
		return $input_field_label_typography_css;
	}

	public function get_input_field_label_hover_css( $attributes, $device = '' ) {
		$input_field_label_hover_css = array();

		if ( ! empty( $attributes['input_field_label_hover_color'] ) ) {
			$input_field_label_hover_css['color'] = $attributes['input_field_label_hover_color'];
		}
		return $input_field_label_hover_css;
	}

	public function get_input_field_css( $attributes, $device = '' ) {
		$input_field_css = array();
		$input_border_css = ! empty( $attributes['input_field_border'] ) ? Border::get_css( $attributes['input_field_border'], '', $device ) : array();
		$input_field_padding = ! empty( $attributes['input_field_padding'] ) ? Dimensions::get_css( $attributes['input_field_padding'], 'padding', $device ) : array();
		if ( ! empty( $attributes['input_field_bg_color'] ) ) {
			$input_field_css['background'] = $attributes['input_field_bg_color'];
		}
		if ( ! empty( $attributes['inputFieldColor'] ) ) {
			$input_field_css['color'] = $attributes['inputFieldColor'];
		}
		return array_merge(
			$input_border_css,
			$input_field_css,
			$input_field_padding
		);
	}
	public function get_input_field_placeholder_css( $attributes, $device = '' ) {
		$input_field_css = array();
		if ( ! empty( $attributes['inputFieldColor'] ) ) {
			$input_field_css['color'] = $attributes['inputFieldColor'];
		}
		return $input_field_css;
	}

	public function get_login_btn_hover_css( $attributes, $device = '' ) {
		$login_btn_background_css = array();
		$login_btn_hover_color = array();

		if ( ! empty( $attributes['login_btn_hover_color'] ) ) {
			$login_btn_hover_color['color'] = $attributes['login_btn_hover_color'];
		}
		if ( ! empty( $attributes['login_btn_bg_hover_color'] ) ) {
			$login_btn_background_css['background'] = $attributes['login_btn_bg_hover_color'];
		}

		return array_merge(
			$login_btn_background_css,
			$login_btn_hover_color,
		);
	}

	public function get_form_card_css( $attributes, $device = '' ) {
		$form_background_css = ! empty( $attributes['form_bg_color'] ) ? Background::get_css( $attributes['form_bg_color'], 'background', $device ) : array();
		$form_border = ! empty( $attributes['form_border'] ) ? Border::get_css( $attributes['form_border'], '', $device ) : array();
		$form_padding = ! empty( $attributes['form_padding'] ) ? Dimensions::get_css( $attributes['form_padding'], 'padding', $device ) : array();

		return array_merge(
			$form_background_css,
			$form_border,
			$form_padding
		);
	}

	public function get_form_card_hover_css( $attributes, $device = '' ) {
		$form_background_hover_css = ! empty( $attributes['form_bg_color'] ) ? Background::get_hover_css( $attributes['form_bg_color'], 'background', $device ) : array();

		return $form_background_hover_css;
	}





	public function render_block_content( $attributes, $content, $block_instance ) {
		$attr_array = [
			'form_title'                 => Helper::get_attribute_value( $attributes, 'form_title' ),
			'username_label'             => Helper::get_attribute_value( $attributes, 'username_label' ),
			'username_placeholder'       => Helper::get_attribute_value( $attributes, 'username_placeholder' ),
			'password_label'             => Helper::get_attribute_value( $attributes, 'password_label' ),
			'password_placeholder'       => Helper::get_attribute_value( $attributes, 'password_placeholder' ),
			'remember_label'             => Helper::get_attribute_value( $attributes, 'remember_label' ),
			'login_button_label'         => Helper::get_attribute_value( $attributes, 'login_button_label' ),
			'reset_password_label'       => Helper::get_attribute_value( $attributes, 'reset_password_label' ),
			'show_logged_in_message'     => Helper::get_attribute_value( $attributes, 'show_logged_in_message' ),
			'student_register_url'       => Helper::get_attribute_value( $attributes, 'student_register_url' ),
			'login_redirect_url'       => Helper::get_attribute_value( $attributes, 'login_redirect_url' ),
			'logout_redirect_url'       => Helper::get_attribute_value( $attributes, 'logout_redirect_url' ),
		];

		$shortcode = '[academy_login_form ' . Helper::attr_shortcode( $attr_array ) . ']';
		echo do_shortcode( $shortcode );
	}

}
