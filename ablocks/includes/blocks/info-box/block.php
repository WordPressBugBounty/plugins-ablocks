<?php
namespace ABlocks\Blocks\InfoBox;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ABlocks\Classes\BlockBaseAbstract;
use ABlocks\Classes\CssGenerator;
use ABlocks\Controls\Alignment;
use ABlocks\Controls\Typography;
use ABlocks\Controls\TextShadow;
use ABlocks\Controls\TextStroke;
use ABlocks\Controls\Border;
use ABlocks\Controls\Dimensions;
use ABlocks\Controls\Icon;
use ABlocks\Controls\Range;

class Block extends BlockBaseAbstract {
	protected $block_name = 'info-box';

	public function build_css( $attributes ) {
		$css_generator = new CssGenerator( $attributes, $this->block_name );
		$css_generator->add_class_styles(
			'{{WRAPPER}}',
			$this->get_wrapper_css( $attributes ),
			$this->get_wrapper_css( $attributes, 'Tablet' ),
			$this->get_wrapper_css( $attributes, 'Mobile' )
		);

		$css_generator->add_class_styles(
			'{{WRAPPER}}.ablocks-block--info-box > .ablocks-block-container',
			$this->get_info_box( $attributes ),
			$this->get_info_box( $attributes, 'Tablet' ),
			$this->get_info_box( $attributes, 'Mobile' )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}}.ablocks-block--info-box > .ablocks-block-container > .ablocks-block--info-box__content',
			$this->get_info_box_content( $attributes ),
			$this->get_info_box_content( $attributes, 'Tablet' ),
			$this->get_info_box_content( $attributes, 'Mobile' )
		);

		// badge starts
		$css_generator->add_class_styles(
			'{{WRAPPER}}.ablocks-block--info-box > .ablocks-block-container > .ablocks-info-box-badge-link',
			$this->get_badge_css( $attributes ),
			$this->get_badge_css( $attributes, 'Tablet' ),
			$this->get_badge_css( $attributes, 'Mobile' )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}}.ablocks-block--info-box > .ablocks-block-container > .ablocks-info-box-badge-link:hover',
			$this->get_badge_hover_css( $attributes ),
			$this->get_badge_hover_css( $attributes, 'Tablet' ),
			$this->get_badge_hover_css( $attributes, 'Mobile' )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}}.ablocks-block--info-box > .ablocks-block-container > .ablocks-info-box-badge-link > .ablocks-info-box-badge-link-text',
			$this->get_badge_text_css( $attributes )
		);

		// Icon Style
		$css_generator->add_class_styles(
			'{{WRAPPER}}.ablocks-block--info-box .ablocks-block-container > .ablocks-icon-wrap, {{WRAPPER}}.ablocks-block--info-box .ablocks-block-container > a .ablocks-icon-wrap',
			Icon::get_wrapper_css( $attributes ),
			Icon::get_wrapper_css( $attributes, 'Tablet' ),
			Icon::get_wrapper_css( $attributes, 'Mobile' )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}}.ablocks-block--info-box .ablocks-block-container > .ablocks-icon-wrap img.ablocks-image-icon , {{WRAPPER}}.ablocks-block--info-box .ablocks-block-container > a .ablocks-icon-wrap img.ablocks-image-icon',
			Icon::get_element_image_css( $attributes )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}}.ablocks-block--info-box .ablocks-block-container > .ablocks-icon-wrap svg.ablocks-svg-icon , {{WRAPPER}}.ablocks-block--info-box .ablocks-block-container > a .ablocks-icon-wrap svg.ablocks-svg-icon',
			Icon::get_element_css( $attributes ),
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}}.ablocks-block--info-box:hover .ablocks-block-container > .ablocks-icon-wrap svg.ablocks-svg-icon , {{WRAPPER}}.ablocks-block--info-box:hover .ablocks-block-container > a .ablocks-icon-wrap svg.ablocks-svg-icon',
			$this->get_icon_css_hover( $attributes ),
		);

		// Heading starts
		$desktop_heading_text_styles = $this->get_heading_text_css( $attributes );
		if ( ! empty( $attributes['headingTextColor'] ) ) {
			$desktop_heading_text_styles['color'] = $attributes['headingTextColor'];
		}
		$css_generator->add_class_styles(
			'{{WRAPPER}}.ablocks-block--info-box > .ablocks-block-container > .ablocks-block--info-box__content > .ablocks-info-box-heading',
			$desktop_heading_text_styles,
			$this->get_heading_text_css( $attributes, 'Tablet' ),
			$this->get_heading_text_css( $attributes, 'Mobile' )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}}.ablocks-block--info-box:hover > .ablocks-block-container > .ablocks-block--info-box__content > .ablocks-info-box-heading',
			$this->get_heading_text_css_hover( $attributes ),
			$this->get_heading_text_css_hover( $attributes, 'Tablet' ),
			$this->get_heading_text_css_hover( $attributes, 'Mobile' )
		);

		// Sub Heading starts
		$desktop_sub_heading_text_styles = $this->get_sub_heading_text_css( $attributes );
		if ( ! empty( $attributes['subHeadingTextColor'] ) ) {
			$desktop_sub_heading_text_styles['color'] = $attributes['subHeadingTextColor'];
		}
		$css_generator->add_class_styles(
			'{{WRAPPER}}.ablocks-block--info-box > .ablocks-block-container > .ablocks-block--info-box__content > .ablocks-info-box-sub-heading',
			$desktop_sub_heading_text_styles,
			$this->get_sub_heading_text_css( $attributes, 'Tablet' ),
			$this->get_sub_heading_text_css( $attributes, 'Mobile' )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}}.ablocks-block--info-box:hover > .ablocks-block-container > .ablocks-block--info-box__content > .ablocks-info-box-sub-heading',
			$this->get_sub_heading_text_css_hover( $attributes ),
			$this->get_sub_heading_text_css_hover( $attributes, 'Tablet' ),
			$this->get_sub_heading_text_css_hover( $attributes, 'Mobile' )
		);

		// Description starts
		$desktop_des_text_style = $this->get_des_text_css( $attributes );
		if ( ! empty( $attributes['desTextColor'] ) ) {
			$desktop_des_text_style['color'] = $attributes['desTextColor'];
		}
		$css_generator->add_class_styles(
			'{{WRAPPER}}.ablocks-block--info-box > .ablocks-block-container > .ablocks-block--info-box__content > .ablocks-info-box-text',
			$desktop_des_text_style,
			$this->get_des_text_css( $attributes, 'Tablet' ),
			$this->get_des_text_css( $attributes, 'Mobile' ),
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}}.ablocks-block--info-box:hover > .ablocks-block-container > .ablocks-block--info-box__content > .ablocks-info-box-text',
			$this->get_des_text_css_hover( $attributes ),
			$this->get_des_text_css_hover( $attributes, 'Tablet' ),
			$this->get_des_text_css_hover( $attributes, 'Mobile' )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}}.ablocks-block--info-box > .ablocks-block-container > .ablocks-block--info-box__content > .ablocks-info-box-text-drop-caps::first-letter',
			$this->get_des_drop_text_css( $attributes ),
			$this->get_des_drop_text_css( $attributes, 'Tablet' ),
			$this->get_des_drop_text_css( $attributes, 'Mobile' ),
		);

		// rating icon css
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-info-box-star-ratings',
			$this->get_container_css( $attributes ),
			$this->get_container_css( $attributes, 'Tablet' ),
			$this->get_container_css( $attributes, 'Mobile' ),
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-info-box-star-ratings',
			$this->get_rating_css( $attributes ),
			$this->get_rating_css( $attributes, 'Tablet' ),
			$this->get_rating_css( $attributes, 'Mobile' ),
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-info-box-star-ratings-icons > .ablocks-info-box-rating > .ablocks-info-box-rating__fill > .ablocks-icon-wrap, {{WRAPPER}} .ablocks-info-box-star-ratings-icons > .ablocks-info-box-rating > .ablocks-info-box-rating__unfill > .ablocks-icon-wrap',
			Icon::get_wrapper_css( $attributes, '', 'starIcon' ),
			Icon::get_wrapper_css( $attributes, 'Tablet', 'starIcon' ),
			Icon::get_wrapper_css( $attributes, 'Mobile', 'starIcon' )
		);
		// rating icon spacing css
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-info-box-star-ratings-icons',
			$this->get_rating_icon_spacing_css( $attributes ),
			$this->get_rating_icon_spacing_css( $attributes, 'Tablet' ),
			$this->get_rating_icon_spacing_css( $attributes, 'Mobile' ),
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-info-box-star-ratings-icons > .ablocks-info-box-rating > .ablocks-info-box-rating__fill > .ablocks-icon-wrap > svg',
			$this->get_fill_rating_css( $attributes ),
			$this->get_fill_rating_css( $attributes, 'Tablet' ),
			$this->get_fill_rating_css( $attributes, 'Mobile' ),
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-info-box-star-ratings-icons > .ablocks-info-box-rating > .ablocks-info-box-rating__unfill > .ablocks-icon-wrap > svg',
			$this->get_unfill_rating_css( $attributes ),
			$this->get_unfill_rating_css( $attributes, 'Tablet' ),
			$this->get_unfill_rating_css( $attributes, 'Mobile' ),
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}}.ablocks-block--info-box:hover > .ablocks-block-container > .ablocks-block--info-box__content > .ablocks-info-box-star-ratings > .ablocks-info-box-star-ratings-icons > .ablocks-info-box-rating > .ablocks-info-box-rating__fill > .ablocks-icon-wrap > svg',
			$this->get_fill_rating_css_hover( $attributes ),
			$this->get_fill_rating_css_hover( $attributes, 'Tablet' ),
			$this->get_fill_rating_css_hover( $attributes, 'Mobile' ),
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}}.ablocks-block--info-box:hover > .ablocks-block-container > .ablocks-block--info-box__content > .ablocks-info-box-star-ratings > .ablocks-info-box-star-ratings-icons > .ablocks-info-box-rating > .ablocks-info-box-rating__unfill > .ablocks-icon-wrap > svg',
			$this->get_unfill_rating_css_hover( $attributes ),
			$this->get_unfill_rating_css_hover( $attributes, 'Tablet' ),
			$this->get_unfill_rating_css_hover( $attributes, 'Mobile' ),
		);
		// rating number css
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-info-box-star-rating-number',
			$this->get_rating_number_css( $attributes ),
			$this->get_rating_number_css( $attributes, 'Tablet' ),
			$this->get_rating_number_css( $attributes, 'Mobile' ),
		);

		// Generate button CSS start
		$css_generator->add_class_styles(
			'{{WRAPPER}}.ablocks-block--info-box > .ablocks-block-container > .ablocks-block--info-box__content > .ablocks-info-box-btn-link',
			$this->get_button_css( $attributes ),
			$this->get_button_css( $attributes, 'Tablet' ),
			$this->get_button_css( $attributes, 'Mobile' )
		);
		// Generate button CSS end
		// Generate button hover CSS start
		$css_generator->add_class_styles(
			'{{WRAPPER}}.ablocks-block--info-box > .ablocks-block-container > .ablocks-block--info-box__content > .ablocks-info-box-btn-link:hover',
			$this->get_button_hover_css( $attributes ),
			$this->get_button_hover_css( $attributes, 'Tablet' ),
			$this->get_button_hover_css( $attributes, 'Mobile' )
		);
		// Generate button icon hover CSS
		$css_generator->add_class_styles(
			'{{WRAPPER}}.ablocks-block--info-box > .ablocks-block-container > .ablocks-block--info-box__content > .ablocks-info-box-btn-link:hover > .ablocks-icon-wrap > svg.ablocks-svg-icon',
			$this->get_button_icon_hover_css( $attributes ),
			$this->get_button_icon_hover_css( $attributes, 'Tablet' ),
			$this->get_button_icon_hover_css( $attributes, 'Mobile' )
		);
		// Generate button text CSS start
		$css_generator->add_class_styles(
			'{{WRAPPER}}.ablocks-block--info-box > .ablocks-block-container > .ablocks-block--info-box__content > .ablocks-info-box-btn-link > .ablocks-info-box-btn-link-text',
			$this->get_button_text_css( $attributes )
		);
		// Generate button icon CSS start
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-info-box-btn-link > .ablocks-icon-wrap',
			Icon::get_wrapper_css( $attributes, '', 'btnIcon' ),
			Icon::get_wrapper_css( $attributes, 'Tablet', 'btnIcon' ),
			Icon::get_wrapper_css( $attributes, 'Mobile', 'btnIcon' )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-info-box-btn-link > .ablocks-icon-wrap > svg.ablocks-svg-icon',
			Icon::get_element_css( $attributes, '', 'btnIcon' ),
		);

		return $css_generator->generate_css();
	}

	public function get_wrapper_css( $attributes, $device = '' ) {
		return isset( $attributes['alignment'] ) ? Alignment::get_css( $attributes['alignment'], 'text-align', $device ) : [];
	}

	public function get_info_box( $attributes, $device = '' ) {
		$css = [];

		if ( isset( $attributes['stack'] ) ) {
			$css['display'] = 'flex';
			$css['flex-direction'] = $attributes['stack'];
		}

		if ( isset( $attributes['iconAlignment'][ 'value' . $device ] ) ) {
			$css['align-items'] = $attributes['iconAlignment'][ 'value' . $device ];
		}

		return array_merge(
			Range::get_css([
				'attributeValue' => $attributes['iconGap'],
				'attribute_object_key' => 'value',
				'isResponsive' => true,
				'defaultValue' => 16,
				'hasUnit' => true,
				'unitDefaultValue' => 'px',
				'property' => 'gap',
				'device' => $device,
			]),
			$css,
		);
	}

	public function get_badge_css( $attributes, $device = '' ) {
		$css = [];

		if ( ! empty( $attributes['badgePosition'][ 'value' . $device ] ) ) {
			$position = $attributes['badgePosition'][ 'value' . $device ];
			if ( $position === 'top-left' ) {
				$css['top'] = '0';
				$css['left'] = '0';
				$css['bottom'] = 'auto';
				$css['right'] = 'auto';
			} elseif ( $position === 'top-right' ) {
				$css['top'] = '0';
				$css['right'] = '0';
				$css['bottom'] = 'auto';
				$css['left'] = 'auto';
			} elseif ( $position === 'bottom-left' ) {
				$css['bottom'] = '0';
				$css['left'] = '0';
				$css['top'] = 'auto';
				$css['right'] = 'auto';
			} elseif ( $position === 'bottom-right' ) {
				$css['bottom'] = '0';
				$css['right'] = '0';
				$css['top'] = 'auto';
				$css['left'] = 'auto';
			}//end if
		}//end if
		if ( ! empty( $attributes['badgeTextColor'] ) ) {
			$css['color'] = $attributes['badgeTextColor'];
		}
		if ( ! empty( $attributes['badgeBackground'] ) ) {
			$css['background'] = $attributes['badgeBackground'];
		} elseif ( ! empty( $attributes['badgeType'] ) ) {
			$css['background'] = $attributes['badgeType'];
		}

		return array_merge(
			Range::get_css([
				'attributeValue' => $attributes['badgeTransition'],
				'attribute_object_key' => 'value',
				'defaultValue' => 0,
				'unitDefaultValue' => 's',
				'property' => 'transition-duration',
				'device' => $device,
			]),
			$css,
			Border::get_css( $attributes['badgeBorder'], '', $device ),
			Typography::get_css( $attributes['badgeTypography'], '', $device ),
			Dimensions::get_css( $attributes['badgePadding'], 'padding', $device ),
		);
	}
	public function get_badge_hover_css( $attributes, $device = '' ) {
		$css = [];

		if ( ! empty( $attributes['badgeTextColorH'] ) ) {
			$css['color'] = $attributes['badgeTextColorH'];
		}
		if ( ! empty( $attributes['badgeBackgroundH'] ) ) {
			$css['background'] = $attributes['badgeBackgroundH'];
		}

		return array_merge(
			$css,
			Border::get_css( $attributes['badgeBorder'], '', $device ),
		);
	}
	public function get_badge_text_css( $attributes, $device = '' ) {
		return TextShadow::get_css( $attributes['badgeTextShadow'] );
	}



	public function get_icon_css_hover( $attributes, $device = '' ) {
		$css = [];
		if ( ! empty( $attributes['iconPrimaryColorH'] ) ) {
			$css['fill'] = $attributes['iconPrimaryColorH'];
		}
		return array_merge(
			Range::get_css([
				'attributeValue' => $attributes['iconTransition'],
				'attribute_object_key' => 'value',
				'defaultValue' => 0,
				'unitDefaultValue' => 's',
				'property' => 'transition-duration',
				'device' => $device,
			]),
			$css
		);
	}

	public function get_info_box_content( $attributes, $device = '' ) {
		$css = [];

		if ( isset( $attributes['alignment'][ 'value' . $device ] ) ) {
			$css['align-items'] = $attributes['alignment'][ 'value' . $device ];
		}

		return array_merge(
			Range::get_css([
				'attributeValue' => $attributes['contentGap'],
				'attribute_object_key' => 'value',
				'isResponsive' => true,
				'defaultValue' => 10,
				'hasUnit' => true,
				'unitDefaultValue' => 'px',
				'property' => 'gap',
				'device' => $device,
			]),
			$css,
		);
	}

	public function get_heading_text_css( $attributes, $device = '' ) {
		$typography_css = ! empty( $attributes['headingTypography'] ) ? Typography::get_css( $attributes['headingTypography'], '', $device ) : array();
		$textShadowCss = ! empty( $attributes['headingTextShadow'] ) ? TextShadow::get_css( $attributes['headingTextShadow'], '', $device ) : array();
		$textStrokeCss = ! empty( $attributes['headingTextStroke'] ) ? TextStroke::get_css( $attributes['headingTextStroke'], '', $device ) : array();

		$css = [];

		if ( ! empty( $attributes['alignment'][ 'value' . $device ] ) ) {
			if ( $attributes['alignment'][ 'value' . $device ] === 'flex-start' ) {
				$css['text-align'] = 'left';
			} elseif ( $attributes['alignment'][ 'value' . $device ] === 'flex-end' ) {
				$css['text-align'] = 'right';
			} else {
				$css['text-align'] = $attributes['alignment'][ 'value' . $device ];
			}
		}

		return array_merge( $css, $typography_css, $textShadowCss, $textStrokeCss );
	}
	public function get_heading_text_css_hover( $attributes, $device = '' ) {
		$css = [];
		if ( ! empty( $attributes['headingTextColorHover'] ) ) {
			$css['color'] = $attributes['headingTextColorHover'];
		}
		if ( ! empty( $attributes['headingTransition'] ) ) {
			$css['transition-duration'] = $attributes['headingTransition'] . 's';
		}

		return array_merge(
			Range::get_css([
				'attributeValue' => $attributes['headingTransition'],
				'attribute_object_key' => 'value',
				'defaultValue' => 0,
				'unitDefaultValue' => 's',
				'property' => 'transition-duration',
				'device' => $device,
			]),
			$css
		);
	}


	public function get_sub_heading_text_css( $attributes, $device = '' ) {
		$typography_css = ! empty( $attributes['subHeadingTypography'] ) ? Typography::get_css( $attributes['subHeadingTypography'], '', $device ) : array();
		$textShadowCss = ! empty( $attributes['subHeadingTextShadow'] ) ? TextShadow::get_css( $attributes['subHeadingTextShadow'], '', $device ) : array();
		$textStrokeCss = ! empty( $attributes['subHeadingTextStroke'] ) ? TextStroke::get_css( $attributes['subHeadingTextStroke'], '', $device ) : array();

		$css = [];

		if ( ! empty( $attributes['alignment'][ 'value' . $device ] ) ) {
			if ( $attributes['alignment'][ 'value' . $device ] === 'flex-start' ) {
				$css['text-align'] = 'left';
			} elseif ( $attributes['alignment'][ 'value' . $device ] === 'flex-end' ) {
				$css['text-align'] = 'right';
			} else {
				$css['text-align'] = $attributes['alignment'][ 'value' . $device ];
			}
		}

		return array_merge( $css, $typography_css, $textShadowCss, $textStrokeCss );
	}
	public function get_sub_heading_text_css_hover( $attributes, $device = '' ) {
		$css = [];
		if ( ! empty( $attributes['subHeadingTextColorHover'] ) ) {
			$css['color'] = $attributes['subHeadingTextColorHover'];
		}
		return array_merge(
			Range::get_css([
				'attributeValue' => $attributes['subHeadingTransition'],
				'attribute_object_key' => 'value',
				'defaultValue' => 0,
				'unitDefaultValue' => 's',
				'property' => 'transition-duration',
				'device' => $device,
			]),
			$css
		);
	}

	public function get_des_text_css( $attributes, $device = '' ) {
		$css = [];

		if ( ! empty( $attributes['alignment'][ 'value' . $device ] ) ) {
			if ( $attributes['alignment'][ 'value' . $device ] === 'flex-start' ) {
				$css['text-align'] = 'left';
			} elseif ( $attributes['alignment'][ 'value' . $device ] === 'flex-end' ) {
				$css['text-align'] = 'right';
			} else {
				$css['text-align'] = $attributes['alignment'][ 'value' . $device ];
			}
		}
		return array_merge(
			$css,
			isset( $attributes['desTypography'] ) ? Typography::get_css( $attributes['desTypography'], '', $device ) : [],
			isset( $attributes['desTextStroke'] ) ? TextStroke::get_css( $attributes['desTextStroke'], '', $device ) : [],
			isset( $attributes['desTextShadow'] ) ? TextShadow::get_css( $attributes['desTextShadow'], '', $device ) : [],
		);
	}
	public function get_des_text_css_hover( $attributes, $device = '' ) {
		$css = [];
		if ( ! empty( $attributes['desTextColorHover'] ) ) {
			$css['color'] = $attributes['desTextColorHover'];
		}
		return array_merge(
			Range::get_css([
				'attributeValue' => $attributes['desTransition'],
				'attribute_object_key' => 'value',
				'defaultValue' => 0,
				'unitDefaultValue' => 's',
				'property' => 'transition-duration',
				'device' => $device,
			]),
			$css
		);
	}
	public function get_des_drop_text_css( $attributes, $device = '' ) {
		$css = [];
		if ( ! empty( $attributes['desDropCapsTextColor'] ) ) {
			$css['color'] = $attributes['desDropCapsTextColor'];
		}
		return $css;
	}

	public function get_container_css( $attributes, $device = '' ) {
		$css = [];

		return array_merge(
			Range::get_css([
				'attributeValue' => $attributes['ratingNumberGap'],
				'attribute_object_key' => 'value',
				'isResponsive' => true,
				'defaultValue' => 0,
				'hasUnit' => true,
				'unitDefaultValue' => 'px',
				'property' => 'gap',
				'device' => $device,
			]),
			$css,
			isset( $attributes['alignment'] ) ? Alignment::get_css( $attributes['alignment'], 'justify-content', $device ) : [],
		);
	}
	public function get_rating_number_css( $attributes, $device = '' ) {
		$css = [];
		if ( ! empty( $attributes['ratingNumberColor'] ) ) {
			$css['color'] = $attributes['ratingNumberColor'];
		}
		if ( ! empty( $attributes['ratingNumberPosition'] ) ) {
			if ( 'left' === $attributes['ratingNumberPosition'] ) {
				$css['order'] = '-5';
			} else {
				$css['order'] = '10';
			}
		}
		return array_merge(
			$css,
			isset( $attributes['ratingNumberTypography'] ) ? Typography::get_css( $attributes['ratingNumberTypography'], '', $device ) : [],
		);
	}
	public function get_rating_css( $attributes, $device = '' ) {
		$rating_css = [];
		return array_merge(
			Range::get_css([
				'attributeValue' => $attributes['size'] ?? [],
				'attribute_object_key' => 'value',
				'isResponsive' => true,
				'defaultValue' => 0,
				'hasUnit' => true,
				'unitDefaultValue' => 'px',
				'property' => 'font-size',
				'device' => $device,
			]),
			$rating_css,
		);
	}


	public function get_rating_icon_spacing_css( $attributes, $device = '' ) {
		$rating_icon_spacing_css = [];

		return array_merge(
			Range::get_css([
				'attributeValue' => $attributes['spacing'],
				'attribute_object_key' => 'value',
				'isResponsive' => true,
				'defaultValue' => 0,
				'hasUnit' => true,
				'unitDefaultValue' => 'px',
				'property' => 'gap',
				'device' => $device,
			]),
			$rating_icon_spacing_css
		);
	}

	public function get_fill_rating_css( $attributes, $device = '' ) {
		$css = [];
		if ( ! empty( $attributes['ratingColor'] ) ) {
			$css['fill'] = $attributes['ratingColor'];
		}

		return array_merge(
			Range::get_css([
				'attributeValue' => $attributes['ratingTransition'],
				'attribute_object_key' => 'value',
				'defaultValue' => 0,
				'unitDefaultValue' => 's',
				'property' => 'transition-duration',
				'device' => $device,
			]),
			$css
		);
	}
	public function get_unfill_rating_css( $attributes, $device = '' ) {
		$css = [];
		if ( ! empty( $attributes['ratingColor'] ) ) {
			$css['fill'] = $attributes['ratingUnmarkedColor'];
		}
		return array_merge(
			Range::get_css([
				'attributeValue' => $attributes['ratingTransition'],
				'attribute_object_key' => 'value',
				'defaultValue' => 0,
				'unitDefaultValue' => 's',
				'property' => 'transition-duration',
				'device' => $device,
			]),
			$css
		);
	}
	public function get_fill_rating_css_hover( $attributes, $device = '' ) {
		$css = [];
		if ( ! empty( $attributes['ratingColorHover'] ) ) {
			$css['fill'] = $attributes['ratingColorHover'];
		}

		return array_merge(
			Range::get_css([
				'attributeValue' => $attributes['ratingTransition'],
				'attribute_object_key' => 'value',
				'defaultValue' => 0,
				'unitDefaultValue' => 's',
				'property' => 'transition-duration',
				'device' => $device,
			]),
			$css
		);
	}
	public function get_unfill_rating_css_hover( $attributes, $device = '' ) {
		$css = [];
		if ( ! empty( $attributes['ratingColorHover'] ) ) {
			$css['fill'] = $attributes['ratingUnmarkedColorHover'];
		}
		return array_merge(
			Range::get_css([
				'attributeValue' => $attributes['ratingTransition'],
				'attribute_object_key' => 'value',
				'defaultValue' => 0,
				'unitDefaultValue' => 's',
				'property' => 'transition-duration',
				'device' => $device,
			]),
			$css
		);
	}

	public function get_button_css( $attributes, $device = '' ) {
		$css = [];

		if ( ! empty( $attributes['btnBackground'] ) ) {
			$css['background'] = $attributes['btnBackground'];
		} else {
			$css['background'] = $attributes['btnType'];
		}

		if ( isset( $attributes['btnAlignment'][ 'value' . $device ] ) ) {
			$css['justify-content'] = $attributes['btnAlignment'][ 'value' . $device ];
		}

		return array_merge(
			Range::get_css([
				'attributeValue' => $attributes['btnIconSpace'],
				'attribute_object_key' => 'value',
				'defaultValue' => 10,
				'property' => 'column-gap',
				'device' => $device,
			]),
			Range::get_css([
				'attributeValue' => $attributes['btnTransition'],
				'attribute_object_key' => 'value',
				'defaultValue' => 0,
				'unitDefaultValue' => 's',
				'property' => 'transition-duration',
				'device' => $device,
			]),
			$css,
			[ 'color' => $attributes['btnTextColor'] ?? '#000000' ],
			Border::get_css( $attributes['btnBorder'], '', $device ),
			Typography::get_css( $attributes['btnTypography'], '', $device ),
			Dimensions::get_css( $attributes['btnPadding'], 'padding', $device ),
		);
	}
	public function get_button_hover_css( $attributes, $device = '' ) {
		$css = [];
		if ( ! empty( $attributes['btnTextColorH'] ) ) {
			$css['color'] = $attributes['btnTextColorH'];
		}

		if ( ! empty( $attributes['btnBackgroundH'] ) ) {
			$css['background'] = $attributes['btnBackgroundH'];
		}

		return array_merge(
			$css,
			Border::get_hover_css( $attributes['btnBorder'], '', $device )
		);
	}
	public function get_button_text_css( $attributes, $device = '' ) {
		return TextShadow::get_css( $attributes['btnTextShadow'] );
	}
	public function get_button_icon_hover_css( $attributes, $device = '' ) {
		$css = [];

		if ( ! empty( $attributes['btnTextColorH'] ) ) {
			$css['fill'] = $attributes['btnTextColorH'];
		}

		return $css;
	}
}
