<?php
namespace ABlocks\Blocks\ImageComparison;

use ABlocks\Classes\BlockBaseAbstract;
use ABlocks\Classes\CssGenerator;
use ABlocks\Controls\Alignment;
use ABlocks\Controls\Border;
use ABlocks\Controls\Range;

class Block extends BlockBaseAbstract {
	protected $block_name = 'image-comparison';

	public function build_css( $attributes ) {
		$css_generator = new CssGenerator( $attributes );

		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-image-comparison__images-container .ablocks-image-comparison__before-image',
			$this->get_before_image_width_css( $attributes )
		);

		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-image-comparison__images-container-vertical .ablocks-image-comparison__before-image',
			$this->get_before_image_height_css( $attributes )
		);

		$css_generator->add_class_styles(
			'{{WRAPPER}}  .ablocks-image-comparison__images-container .ablocks-image-comparison__slider-line',
			$this->get_slider_line_horizontal_css( $attributes ),
			$this->get_slider_line_horizontal_css( $attributes, 'Tablet' ),
			$this->get_slider_line_horizontal_css( $attributes, 'Mobile' )
		);

		$css_generator->add_class_styles(
			'{{WRAPPER}}  .ablocks-image-comparison__images-container-vertical .ablocks-image-comparison__slider-line',
			$this->get_slider_line_vertical_css( $attributes ),
			$this->get_slider_line_vertical_css( $attributes, 'Tablet' ),
			$this->get_slider_line_vertical_css( $attributes, 'Mobile' )
		);

		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-image-comparison__images-container .ablocks-image-comparison__slider-icon',
			$this->get_horizontal_slider_icon_css( $attributes ),
			$this->get_horizontal_slider_icon_css( $attributes, 'Tablet' ),
			$this->get_horizontal_slider_icon_css( $attributes, 'Mobile' )
		);

		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-image-comparison__images-container-vertical .ablocks-image-comparison__slider-icon',
			$this->get_vertical_slider_icon_css( $attributes ),
			$this->get_vertical_slider_icon_css( $attributes, 'Tablet' ),
			$this->get_vertical_slider_icon_css( $attributes, 'Mobile' )
		);

		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-image-comparison__beforeImage-label',
			$this->get_image_overlay_css( $attributes ),
			$this->get_image_overlay_css( $attributes, 'Tablet' ),
			$this->get_image_overlay_css( $attributes, 'Mobile' )
		);

		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-image-comparison__beforeImage-label:hover',
			$this->get_image_overlay_hover_css( $attributes ),
			$this->get_image_overlay_hover_css( $attributes, 'Tablet' ),
			$this->get_image_overlay_hover_css( $attributes, 'Mobile' )
		);

		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-image-comparison__afterImage-label',
			$this->get_image_overlay_css( $attributes ),
			$this->get_image_overlay_css( $attributes, 'Tablet' ),
			$this->get_image_overlay_css( $attributes, 'Mobile' )
		);

		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-image-comparison__afterImage-label:hover',
			$this->get_image_overlay_hover_css( $attributes ),
			$this->get_image_overlay_hover_css( $attributes, 'Tablet' ),
			$this->get_image_overlay_hover_css( $attributes, 'Mobile' )
		);

		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-image-comparison__overlay',
			$this->get_overlay_css( $attributes ),
		);

		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-image-comparison__overlay--hover',
			$this->get_overlay_css( $attributes ),
		);

		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-image-comparison__beforeImage-label--horizontal',
			$this->get_before_image_overlay_horizontal_css( $attributes )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-image-comparison__beforeImage-label--vertical',
			$this->get_before_image_overlay_vertical_css( $attributes )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-image-comparison__afterImage-label--horizontal',
			$this->get_after_image_overlay_horizontal_css( $attributes )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-image-comparison__afterImage-label--vertical',
			$this->get_after_image_overlay_vertical_css( $attributes )
		);

		return $css_generator->generate_css();
	}

	public function get_before_image_width_css( $attributes ) {
		$slider_position = isset( $attributes['sliderPosition'] ) ? $attributes['sliderPosition'] : 50;
		return [ 'width' => $slider_position . '%' ];
	}

	public function get_before_image_height_css( $attributes ) {
		$slider_position = isset( $attributes['sliderPosition'] ) ? $attributes['sliderPosition'] : 50;
		return [ 'height' => $slider_position . '%' ];
	}

	public function get_slider_line_horizontal_css( $attributes, $device = '' ) {
		$slider_position = isset( $attributes['sliderPosition'] ) ? $attributes['sliderPosition'] : 50;
		$handle_color = isset( $attributes['handleColor'] ) ? $attributes['handleColor'] : '';
		$css = [];
		$css['left'] = $slider_position . '%';
		$css['background'] = $handle_color;
		return array_merge(
			Range::get_css([
				'attributeValue' => $attributes['sliderBarSize'],
				'attribute_object_key' => 'value',
				'isResponsive' => true,
				'defaultValue' => 4,
				'unitDefaultValue' => 'px',
				'property' => 'width',
				'device' => $device,
			]),
			$css
		);

	}

	public function get_slider_line_vertical_css( $attributes, $device = '' ) {
		$slider_top = isset( $attributes['sliderPosition'] ) ? $attributes['sliderPosition'] : 50;
		$slider_background = isset( $attributes['handleColor'] ) ? $attributes['handleColor'] : '';

		$css = [];
		$css['top'] = $slider_top . '%';
		$css['background'] = $slider_background;
		return array_merge(
			Range::get_css([
				'attributeValue' => $attributes['sliderBarSize'],
				'attribute_object_key' => 'value',
				'isResponsive' => true,
				'defaultValue' => 4,
				'unitDefaultValue' => 'px',
				'property' => 'height',
				'device' => $device,
			]),
			$css,
		);
	}

	public function get_horizontal_slider_icon_css( $attributes, $device = '' ) {
		$slider_position = isset( $attributes['sliderPosition'] ) ? $attributes['sliderPosition'] : 50;
		$slider_icon_border_size = isset( $attributes['sliderIconBorderSize'][ 'value' . $device ] ) ? $attributes['sliderIconBorderSize'][ 'value' . $device ] : [ 'value' => 2 ];
		$handle_color = isset( $attributes['handleColor'] ) ? $attributes['handleColor'] : '';

		$border_size = is_array( $slider_icon_border_size ) && isset( $slider_icon_border_size[ 'value' . $device ] )
			? $slider_icon_border_size[ 'value' . $device ]
			: ( is_array( $slider_icon_border_size ) ? $slider_icon_border_size['value'] : $slider_icon_border_size );
		$css = [];
		$css['left'] = $slider_position . '%';
		$css['border-weight'] = 'solid';
		$css['border-color'] = $handle_color;
		$css['color'] = $handle_color;

		return array_merge(
			Range::get_css([
				'attributeValue' => $attributes['sliderIconSize'],
				'attribute_object_key' => 'value',
				'isResponsive' => true,
				'defaultValue' => 50,
				'unitDefaultValue' => 'px',
				'property' => 'width',
				'device' => $device,
			]),
			Range::get_css([
				'attributeValue' => $attributes['sliderIconSize'],
				'attribute_object_key' => 'value',
				'isResponsive' => true,
				'defaultValue' => 50,
				'unitDefaultValue' => 'px',
				'property' => 'height',
				'device' => $device,
			]),
			Range::get_css([
				'attributeValue' => $attributes['sliderIconBorderSize'],
				'attribute_object_key' => 'value',
				'isResponsive' => true,
				'defaultValue' => 2,
				'unitDefaultValue' => 'px',
				'property' => 'border-width',
				'device' => $device,
			]),
			$css
		);
	}

	public function get_vertical_slider_icon_css( $attributes, $device = '' ) {
		$slider_position = isset( $attributes['sliderPosition'] ) ? $attributes['sliderPosition'] : 50;
		$handle_color = isset( $attributes['handleColor'] ) ? $attributes['handleColor'] : '';

		$css = [];
		$css['left'] = $slider_position . '%';
		$css['border-weight'] = 'solid';
		$css['border-color'] = $handle_color;
		$css['color'] = $handle_color;
			return array_merge(
				Range::get_css([
					'attributeValue' => $attributes['sliderIconSize'],
					'attribute_object_key' => 'value',
					'isResponsive' => true,
					'defaultValue' => 50,
					'unitDefaultValue' => 'px',
					'property' => 'width',
					'device' => $device,
				]),
				Range::get_css([
					'attributeValue' => $attributes['sliderIconSize'],
					'attribute_object_key' => 'value',
					'isResponsive' => true,
					'defaultValue' => 50,
					'unitDefaultValue' => 'px',
					'property' => 'height',
					'device' => $device,
				]),
				Range::get_css([
					'attributeValue' => $attributes['sliderIconBorderSize'],
					'attribute_object_key' => 'value',
					'isResponsive' => true,
					'defaultValue' => 2,
					'unitDefaultValue' => 'px',
					'property' => 'border-width',
					'device' => $device,
				]),
				$css
			);
	}

	public function get_overlay_css( $attributes, $device = '' ) {
		$overlay_color = isset( $attributes['labelOverlayColor'] ) ? $attributes['labelOverlayColor'] : '';

		return [
			'background' => $overlay_color
		];
	}

	public function get_image_overlay_css( $attributes, $device = '' ) {
		$label_bg_color = isset( $attributes['labelBgColor'] ) ? $attributes['labelBgColor'] : '';
		$label_text_color = isset( $attributes['labelTextColor'] ) ? $attributes['labelTextColor'] : '';
		$label_border = isset( $attributes['labelBorder'] ) ? $attributes['labelBorder'] : [];
		$slider_orientation = isset( $attributes['sliderOrientation'] ) ? $attributes['sliderOrientation'] : '';
		$label_position = isset( $attributes['labelPosition'] ) ? $attributes['labelPosition'] : '';

		// Determine transform value based on the conditions
		$transform = 'none';
		if ( $label_position === 45 && $slider_orientation === 'vertical' ) {
			$transform = 'translateX(-50%)';
		} elseif ( $label_position === 95 && $slider_orientation === 'vertical' ) {
			$transform = 'translateX(-100%)';
		} elseif ( $label_position === 90 && $slider_orientation === 'horizontal' ) {
			$transform = 'translateY(-100%)';
		}

		return array_merge(
			Border::get_css( $label_border, '', $device ),
			[
				'background'  => $label_bg_color,
				'color'       => $label_text_color,
				'transform'   => $transform,  // Add transform to the CSS array
				'max-width'   => '30%'
			]
		);
	}


	public function get_image_overlay_hover_css( $attributes, $device = '' ) {
		$label_hover_bg_color = isset( $attributes['labelHoverBgColor'] ) ? $attributes['labelHoverBgColor'] : '';
		$label_hover_text_color = isset( $attributes['labelHoverTextColor'] ) ? $attributes['labelHoverTextColor'] : '';
		$label_border_hover = isset( $attributes['labelBorderHover'] ) ? $attributes['labelBorderHover'] : [];

		return array_merge(
			Border::get_hover_css( $attributes['labelBorder'], '', $device ),
			[
				'background' => $label_hover_bg_color,
				'color' => $label_hover_text_color,
			]
		);
	}

	public function get_before_image_overlay_horizontal_css( $attributes ) {
		$label_position = isset( $attributes['labelPosition'] ) ? $attributes['labelPosition'] : 45;

		return [
			'top' => $label_position . '%',
			'left' => '10px'
		];
	}

	public function get_before_image_overlay_vertical_css( $attributes ) {
		$label_position = isset( $attributes['labelPosition'] ) ? $attributes['labelPosition'] : 45;

		return [
			'left' => $label_position . '%',
			'top' => '10px'
		];
	}

	public function get_after_image_overlay_horizontal_css( $attributes ) {
		$label_position = isset( $attributes['labelPosition'] ) ? $attributes['labelPosition'] : 45;

		return [
			'top' => $label_position . '%',
			'right' => '10px',
		];
	}

	public function get_after_image_overlay_vertical_css( $attributes ) {
		$label_position = isset( $attributes['labelPosition'] ) ? $attributes['labelPosition'] : 45;

		return [
			'bottom' => '10px',
			'left' => $label_position . '%',
		];
	}
}
