<?php

namespace ABlocks\Blocks\FormBuilder;

use ABlocks\Classes\BlockBaseAbstract;
use ABlocks\Classes\CssGenerator;
use ABlocks\Controls\Alignment;
use ABlocks\Controls\Typography;
use ABlocks\Controls\Dimensions;
use ABlocks\Controls\Border;
use ABlocks\Controls\Range;

class Block extends BlockBaseAbstract {

	protected $block_name = 'form-builder';

	public function __construct() {
		parent::__construct();
		add_filter( 'ablocks/get_render_block_content', [ $this, 'render_static_block_content' ], 10, 3 );
	}

	public function render_static_block_content( $content, $attributes, $block_instance ) {
		if ( $block_instance->name === $this->namespace . '/' . $this->block_name ) {
			if ( is_user_logged_in() &&
				in_array( $attributes['formType'], array( 'login', 'registration', 'forget_password' ), true )
			) {
				return '';
			}
			return $content;
		}
		return $content;
	}

	public function build_css( $attributes ) {
		$css_generator = new CssGenerator( $attributes, $this->block_name );

		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-form-builder__field',
			$this->get_field_css( $attributes ),
			$this->get_field_css( $attributes, 'Tablet' ),
			$this->get_field_css( $attributes, 'Mobile' ),
		);

		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-form-builder__label',
			$this->get_label_css( $attributes ),
			$this->get_label_css( $attributes, 'Tablet' ),
			$this->get_label_css( $attributes, 'Mobile' ),
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-form-builder__helper-text',
			$this->get_helper_text_css( $attributes ),
			$this->get_helper_text_css( $attributes, 'Tablet' ),
			$this->get_helper_text_css( $attributes, 'Mobile' ),
		);

		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-form-builder__input',
			$this->get_input_css( $attributes ),
			$this->get_input_css( $attributes, 'Tablet' ),
			$this->get_input_css( $attributes, 'Mobile' ),
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-form-builder__input:hover',
			$this->get_input_hover_css( $attributes ),
			$this->get_input_hover_css( $attributes, 'Tablet' ),
			$this->get_input_hover_css( $attributes, 'Mobile' ),
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-form-builder__input:focus',
			$this->get_input_focus_css( $attributes ),
			$this->get_input_focus_css( $attributes, 'Tablet' ),
			$this->get_input_focus_css( $attributes, 'Mobile' ),
		);

		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-form-builder__input::placeholder',
			$this->get_input_placeholder_css( $attributes ),
			$this->get_input_placeholder_css( $attributes, 'Tablet' ),
			$this->get_input_placeholder_css( $attributes, 'Mobile' ),
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-form-builder__input-icon,{{WRAPPER}} .ablocks-form-builder__input-toggle-password',
			$this->get_input_position_css( $attributes ),
			$this->get_input_position_css( $attributes, 'Tablet' ),
			$this->get_input_position_css( $attributes, 'Mobile' ),
		);

		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-form-builder__submit-button',
			$this->get_alignment_button_css( $attributes ),
			$this->get_alignment_button_css( $attributes, 'Tablet' ),
			$this->get_alignment_button_css( $attributes, 'Mobile' ),
		);

		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-form-builder__submit-button',
			$this->get_submit_button_css( $attributes ),
			$this->get_submit_button_css( $attributes, 'Tablet' ),
			$this->get_submit_button_css( $attributes, 'Mobile' ),
		);

		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-form-builder__submit-button:hover',
			$this->get_submit_button_hover_css( $attributes ),
			$this->get_submit_button_hover_css( $attributes, 'Tablet' ),
			$this->get_submit_button_hover_css( $attributes, 'Mobile' ),
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-block--form-builder__navigator',
			$this->get_navigator_css( $attributes ),
			$this->get_navigator_css( $attributes, 'Tablet' ),
			$this->get_navigator_css( $attributes, 'Mobile' ),
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-block--form-builder__navigator-redirect-page',
			$this->get_navigator_spacing_css( $attributes ),
			$this->get_navigator_spacing_css( $attributes, 'Tablet' ),
			$this->get_navigator_spacing_css( $attributes, 'Mobile' ),
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-block--form-builder__success',
			$this->get_succsss_styles_css( $attributes ),
			$this->get_succsss_styles_css( $attributes, 'Tablet' ),
			$this->get_succsss_styles_css( $attributes, 'Mobile' ),
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-block--form-builder__error',
			$this->get_error_styles_css( $attributes ),
			$this->get_error_styles_css( $attributes, 'Tablet' ),
			$this->get_error_styles_css( $attributes, 'Mobile' ),
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-block--form-builder__feedback-message',
			$this->get_success_error_common_styles_css( $attributes ),
			$this->get_success_error_common_styles_css( $attributes, 'Tablet' ),
			$this->get_success_error_common_styles_css( $attributes, 'Mobile' ),
		);
		return $css_generator->generate_css();
	}

	public function get_field_css( $attributes, $device = '' ) {
		$field_css = array_merge(
			Range::get_css([
				'attributeValue' => $attributes['rowsSpacing'],
				'attributeObjectKey' => 'value',
				'isResponsive' => true,
				'defaultValue' => 0,
				'unitDefaultValue' => 'px',
				'property' => 'margin-top',
				'device' => $device,
			]),
			Range::get_css([
				'attributeValue' => $attributes['rowsSpacing'],
				'attributeObjectKey' => 'value',
				'isResponsive' => true,
				'defaultValue' => 0,
				'unitDefaultValue' => 'px',
				'property' => 'margin-bottom',
				'device' => $device,
			]),
		);
		return $field_css;
	}

	public function get_label_css( $attributes, $device = '' ) {
		$label_css = array_merge(
			Typography::get_css( $attributes['labelTypography'], '', $device ),
			Alignment::get_css( $attributes['labelAlignment'], 'text-align', $device ),
			Range::get_css([
				'attributeValue' => $attributes['labelSpacing'],
				'attributeObjectKey' => 'value',
				'isResponsive' => true,
				'defaultValue' => 10,
				'unitDefaultValue' => 'px',
				'property' => 'margin-bottom',
				'device' => $device,
			]),
		);

		if ( ! empty( $attributes['labelColor'] ) ) {
			$label_css['color'] = $attributes['labelColor'];
		}

		if ( ! $attributes['showLabels'] ) {
			$label_css['display'] = 'none';
		}

		return $label_css;
	}
	public function get_helper_text_css( $attributes, $device = '' ) {
		$helper_text_css = array_merge(
			Alignment::get_css( $attributes['labelAlignment'], 'text-align', $device )
		);

		if ( ! $attributes['showLabels'] ) {
			$helper_text_css['display'] = 'none';
		}

		if ( ! empty( $attributes['labelSpacing'][ 'value' . $device ] ) ) {
			$helper_text_css['margin-top'] = '-' . $attributes['labelSpacing'][ 'value' . $device ] . 'px';
			$helper_text_css['margin-bottom'] = $attributes['labelSpacing'][ 'value' . $device ] . 'px';
		}

		return $helper_text_css;
	}

	public function get_input_css( $attributes, $device = '' ) {
		// Initialize the $css array before using it
		$css = array();

		// Check if the inputBorder's borderStyle is 'default' and apply default border styles
		if ( isset( $attributes['inputBorder']['borderStyle'] ) && $attributes['inputBorder']['borderStyle'] === 'default' ) {
			$css['border'] = '1px solid #A7AAAD';
			$css['border-radius'] = '5px';
		}

		// Merge the base CSS with typography, border, and padding CSS
		$input_css = array_merge(
			$css, // Include the default border styles if set
			Alignment::get_css( $attributes['inputAlignment'], 'text-align', $device ),
			Typography::get_css( $attributes['inputTypography'], '', $device ),
			Border::get_css( $attributes['inputBorder'], '', $device ),
			Dimensions::get_css( $attributes['inputPadding'], 'padding', $device )
		);

		// Add input text color if it's set in the attributes
		if ( ! empty( $attributes['inputColor'] ) ) {
			$input_css['color'] = $attributes['inputColor'] . '!important';
		}

		// Add background color if it's set in the attributes
		if ( ! empty( $attributes['inputBgColor'] ) ) {
			$input_css['background-color'] = $attributes['inputBgColor'];
		}

		// Ensure box-sizing is set to 'border-box'
		$input_css['box-sizing'] = 'border-box';

		return $input_css;
	}

	public function get_input_hover_css( $attributes, $device = '' ) {
		$css = array();

		// Check if the inputBorder's borderStyle is 'default' and apply default border styles
		if ( isset( $attributes['inputBorder']['borderStyle'] ) && $attributes['inputBorder']['borderStyle'] === 'default' ) {
			$css['border'] = '1px solid #A7AAAD';
			$css['border-radius'] = '5px';
		}
		// Get the border hover CSS based on inputBorder attribute
		$input_focus_css = array_merge(
			$css,
			Border::get_hover_css( $attributes['inputBorder'], '', $device )
		);

		return $input_focus_css;
	}
	public function get_input_focus_css( $attributes, $device = '' ) {
		$css = array();

		// Check if the inputBorder's borderStyle is 'default' and apply default border styles
		if ( isset( $attributes['inputBorder']['borderStyle'] ) && $attributes['inputBorder']['borderStyle'] === 'default' ) {
			$css['border'] = '1px solid #A7AAAD';
			$css['border-radius'] = '5px';
		}
		// Get the border hover CSS based on inputBorder attribute
		$input_focus_css = array_merge(
			$css,
			Border::get_hover_css( $attributes['inputBorder'], '', $device )
		);

		return $input_focus_css;
	}
	public function get_input_placeholder_css( $attributes, $device = '' ) {
		// Initialize placeholder CSS array
		$placeholder_css = array_merge(
			// Apply typography for the placeholder (font size, font family, etc.)
			Typography::get_css( $attributes['inputTypography'], '', $device ),
			// Apply alignment if specified
			Alignment::get_css( $attributes['inputAlignment'], 'text-align', $device )
		);
		// Check if placeholder color is defined, and apply it
		if ( ! empty( $attributes['inputPlaceholderColor'] ) ) {
			$placeholder_css['color'] = $attributes['inputPlaceholderColor'] . '!important'; // Add !important to override conflicting styles
		}
		return $placeholder_css;

	}
	public function get_input_position_css( $attributes, $device = '' ) {
		// Access the inputIconPosition attribute from the attributes array
		$css = array_merge(
			Range::get_css([
				'attributeValue' => $attributes['inputIconPosition'],
				'isResponsive' => false,
				'defaultValue' => 60,
				'unitDefaultValue' => '%',
				'property' => 'top',
				'device' => $device,
			]),
		);
		return $css;
	}
	public function get_alignment_button_css( $attributes, $device = '' ) {
		$button_alignment_css = array_merge(
			Alignment::get_css( $attributes['buttonTextAlignment'], 'text-align', $device ),
			Alignment::get_css( $attributes['buttonAlignment'], 'align-self', $device )
		);

		return $button_alignment_css;
	}


	public function get_submit_button_css( $attributes, $device = '' ) {
		$button_css = array_merge(
			Border::get_css( $attributes['buttonBorder'], '', $device ),
			Typography::get_css( $attributes['buttonTypography'], '', $device ),
			Dimensions::get_css( $attributes['buttonPadding'], 'padding', $device ),
		);
		if ( ! empty( $attributes['buttonColor'] ) ) {
			$button_css['color'] = $attributes['buttonColor'];
		}

		if ( ! empty( $attributes['buttonBgColor'] ) ) {
			$button_css['background-color'] = $attributes['buttonBgColor'];
		}

		return $button_css;
	}

	public function get_submit_button_hover_css( $attributes, $device = '' ) {
		$button_hover_css = Border::get_hover_css( $attributes['buttonBorder'], '', $device );

		if ( ! empty( $attributes['buttonHColor'] ) ) {
			$button_hover_css['color'] = $attributes['buttonHColor'];
		}

		if ( ! empty( $attributes['buttonBgHColor'] ) ) {
			$button_hover_css['background-color'] = $attributes['buttonBgHColor'];
		}

		return $button_hover_css;
	}
	public function get_navigator_css( $attributes, $device = '' ) {
		$css = [];
		if ( ! empty( $attributes['navigatorColor'] ) ) {
			$css['color'] = $attributes['navigatorColor'];
		}
		return array_merge(
			Typography::get_css( $attributes['navigatorTypography'], '', $device ),
			Dimensions::get_css( $attributes['navigatorPadding'], 'padding', $device ),
			Alignment::get_css( $attributes['navigatorAlignment'], 'text-align', $device ),
			$css
		);
	}
	public function get_navigator_spacing_css( $attributes, $device = '' ) {
			// Access the inputIconPosition attribute from the attributes array
			$css = array_merge(
				Range::get_css([
					'attributeValue' => $attributes['navigatorSpacing'],
					'isResponsive' => false,
					'defaultValue' => 10,
					'unitDefaultValue' => 'px',
					'property' => 'margin-bottom',
					'device' => $device,
				]),
			);
			return $css;

	}
	public function get_succsss_styles_css( $attributes, $device = '' ) {
		$css = [];
		if ( ! empty( $attributes['successBackground'] ) ) {
			$css['background'] = $attributes['successBackground'];
		}
		if ( ! empty( $attributes['successColor'] ) ) {
			$css['color'] = $attributes['successColor'];
		}
		return $css;
	}
	public function get_error_styles_css( $attributes, $device = '' ) {
		$css = [];
		if ( ! empty( $attributes['errorBackground'] ) ) {
			$css['background'] = $attributes['errorBackground'];
		}
		if ( ! empty( $attributes['errorColor'] ) ) {
			$css['color'] = $attributes['errorColor'];
		}
		return $css;
	}
	public function get_success_error_common_styles_css( $attributes, $device = '' ) {
		$css = [];
		return array_merge(
			Typography::get_css( $attributes['successErrorTypography'], '', $device ),
			Alignment::get_css( $attributes['successErrorAlignment'], 'text-align', $device ),
			Dimensions::get_css( $attributes['successErrorPadding'], 'padding', $device ),
			$css,
		);
	}

}
