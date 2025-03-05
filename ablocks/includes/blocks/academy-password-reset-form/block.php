<?php
namespace ABlocks\Blocks\AcademyPasswordResetForm;

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
	protected $block_name = 'academy-password-reset-form';

	public function __construct() {
		parent::__construct();

		add_filter( 'academy/shortcode/password_reset_form_is_user_logged_in', [ $this, 'force_showing_reset_form_in_editor' ] );

	}

	public function force_showing_reset_form_in_editor( $flag ) {
		if ( Helper::is_gutenberg_editor() ) {
			return false;
		}
		return $flag;
	}

	public function build_css( $attributes ) {
		$css_generator = new CssGenerator( $attributes );

		$css_generator->add_class_styles(
			'{{WRAPPER}} .academy-password-reset-form-wrapper',
			$this->getResetFormCss( $attributes ),
			$this->getResetFormCss( $attributes, 'Tablet' ),
			$this->getResetFormCss( $attributes, 'Mobile' )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .academy-password-reset-form-wrapper:hover',
			$this->getResetFormHoverCss( $attributes ),
			$this->getResetFormHoverCss( $attributes, 'Tablet' ),
			$this->getResetFormHoverCss( $attributes, 'Mobile' )
		);

		$css_generator->add_class_styles(
			'{{WRAPPER}} .academy-password-reset-form-wrapper .academy-password-reset-form .academy-form-group label',
			$this->getResetFormLabelCss( $attributes ),
			$this->getResetFormLabelCss( $attributes, 'Tablet' ),
			$this->getResetFormLabelCss( $attributes, 'Mobile' )
		);

		$css_generator->add_class_styles(
			'{{WRAPPER}} .academy-password-reset-form-wrapper .academy-password-reset-form .academy-form-group input',
			$this->getResetFormInputCss( $attributes ),
			$this->getResetFormInputCss( $attributes, 'Tablet' ),
			$this->getResetFormInputCss( $attributes, 'Mobile' )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .academy-password-reset-form-wrapper .academy-password-reset-form .academy-form-group input:hover',
			$this->getResetFormInputHoverCss( $attributes ),
			$this->getResetFormInputHoverCss( $attributes, 'Tablet' ),
			$this->getResetFormInputHoverCss( $attributes, 'Mobile' )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .academy-password-reset-form-wrapper .academy-password-reset-form .academy-form-group  button',
			$this->getResetFormButtonCss( $attributes ),
			$this->getResetFormButtonCss( $attributes, 'Tablet' ),
			$this->getResetFormButtonCss( $attributes, 'Mobile' )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .academy-password-reset-form-wrapper .academy-password-reset-form .academy-form-group  button:hover',
			$this->getResetFormButtonHoverCss( $attributes ),
			$this->getResetFormButtonHoverCss( $attributes, 'Tablet' ),
			$this->getResetFormButtonHoverCss( $attributes, 'Mobile' )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .academy-password-reset-form-wrapper h2.academy-password-reset-form-heading',
			$this->getResetFormHeaderCss( $attributes ),
			$this->getResetFormHeaderCss( $attributes, 'Tablet' ),
			$this->getResetFormHeaderCss( $attributes, 'Mobile' )
		);

		$css_generator->add_class_styles(
			'{{WRAPPER}} .academy-password-reset-form-wrapper .academy-password-reset-form-info a',
			$this->getResetFormFooterCss( $attributes ),
			$this->getResetFormFooterCss( $attributes, 'Tablet' ),
			$this->getResetFormFooterCss( $attributes, 'Mobile' )
		);

		return $css_generator->generate_css();
	}

	public function getResetFormCss( $attributes, $device = '' ) {
		$css = array();
		$form_border_css = ! empty( $attributes['form_border'] ) ? Border::get_css( $attributes['form_border'], '', $device ) : array();
		$form_padding_css = ! empty( $attributes['form_padding'] ) ? Dimensions::get_css( $attributes['form_padding'], 'padding', $device ) : array();
		if ( ! empty( $attributes['form_background_color'] ) ) {
			$css['background'] = $attributes['form_background_color'];
		}
		return array_merge(
			$form_border_css,
			$form_padding_css,
			$css
		);
	}
	public function getResetFormHoverCss( $attributes, $device = '' ) {
		$css = array();
		$form_border_hover_css = ! empty( $attributes['form_border'] ) ? Border::get_hover_css( $attributes['form_border'], '', $device ) : array();
		if ( ! empty( $attributes['form_background_hover_color'] ) ) {
			$css['background'] = $attributes['form_background_hover_color'];
		}
		return array_merge(
			$form_border_hover_css,
			$css
		);
	}
	public function getResetFormLabelCss( $attributes, $device = '' ) {
		$css = array();
		$reset_form_label_typography_css = ! empty( $attributes['label_typography'] ) ? Typography::get_css( $attributes['label_typography'], '', $device ) : array();
		if ( ! empty( $attributes['label_color'] ) ) {
			$css['color'] = $attributes['label_color'];
		}
		return array_merge(
			$reset_form_label_typography_css,
			$css
		);
	}
	public function getResetFormInputCss( $attributes, $device = '' ) {
		$css = array();
		$form_input_border_css = ! empty( $attributes['input_border'] ) ? Border::get_css( $attributes['input_border'], '', $device ) : array();
		$form_input_padding_css = ! empty( $attributes['input_padding'] ) ? Dimensions::get_css( $attributes['input_padding'], 'padding', $device ) : array();
		$form_input_typography_css = ! empty( $attributes['input_field_typography'] ) ? Typography::get_css( $attributes['input_field_typography'], '', $device ) : array();
		if ( ! empty( $attributes['input_field_color'] ) ) {
			$css['color'] = $attributes['input_field_color'];
		}
		return array_merge(
			$form_input_border_css,
			$form_input_padding_css,
			$form_input_typography_css,
			$css
		);
	}
	public function getResetFormInputHoverCss( $attributes, $device = '' ) {
		$css = array();
		$form_input_border_hover_css = ! empty( $attributes['input_border'] ) ? Border::get_hover_css( $attributes['input_border'], '', $device ) : array();
		return array_merge(
			$form_input_border_hover_css,
			$css
		);
	}

	public function getResetFormButtonCss( $attributes, $device = '' ) {
		$css = array();
		$button_border = ! empty( $attributes['button_border'] ) ? Border::get_css( $attributes['button_border'], '', $device ) : array();
		$button_padding = ! empty( $attributes['button_padding'] ) ? Dimensions::get_css( $attributes['button_padding'], 'padding', $device ) : array();
		$button_typography = ! empty( $attributes['button_typography'] ) ? Typography::get_css( $attributes['button_typography'], '', $device ) : array();
		if ( ! empty( $attributes['button_color'] ) ) {
			$css['color'] = $attributes['button_color'];
		}
		if ( ! empty( $attributes['button_background_color'] ) ) {
			$css['background'] = $attributes['button_background_color'];
		}
		return array_merge(
			$button_border,
			$button_padding,
			$button_typography,
			$css
		);
	}
	public function getResetFormButtonHoverCss( $attributes, $device = '' ) {
		$css = array();
		$button_hover_border = ! empty( $attributes['button_border'] ) ? Border::get_hover_css( $attributes['button_border'], '', $device ) : array();
		if ( ! empty( $attributes['button_hover_color'] ) ) {
			$css['color'] = $attributes['button_hover_color'];
		}
		if ( ! empty( $attributes['button_background_hover_color'] ) ) {
			$css['background'] = $attributes['button_background_hover_color'];
		}
		return array_merge(
			$button_hover_border,
			$css
		);
	}
	public function getResetFormHeaderCss( $attributes, $device = '' ) {
		$css = array();
		$form_title_typography = ! empty( $attributes['form_title_typography'] ) ? Typography::get_css( $attributes['form_title_typography'], '', $device ) : array();
		if ( ! empty( $attributes['form_title_color'] ) ) {
			$css['color'] = $attributes['form_title_color'];
		}
		return array_merge(
			$form_title_typography,
			$css
		);
	}
	public function getResetFormFooterCss( $attributes, $device = '' ) {
		$css = array();
		$form_footer_title_typography = ! empty( $attributes['form_footer_title_typography'] ) ? Typography::get_css( $attributes['form_footer_title_typography'], '', $device ) : array();
		if ( ! empty( $attributes['form_footer_title_color'] ) ) {
			$css['color'] = $attributes['form_footer_title_color'];
		}
		return array_merge(
			$form_footer_title_typography,
			$css
		);
	}


	public function render_block_content( $attributes, $content, $block_instance ) {
		$attr_array = [
			'form_title'                 => Helper::get_attribute_value( $attributes, 'form_title' ),
			'username_label'        => Helper::get_attribute_value( $attributes, 'username_label' ),
			'reset_button_label'        => Helper::get_attribute_value( $attributes, 'reset_button_label' ),
			'login_button_label'        => Helper::get_attribute_value( $attributes, 'login_button_label' ),
			'show_logged_in_message'          => Helper::get_attribute_value( $attributes, 'show_logged_in_message' ),
		];
		$shortcode = '[academy_password_reset_form ' . Helper::attr_shortcode( $attr_array ) . ']';
		echo do_shortcode( $shortcode );
	}

}
