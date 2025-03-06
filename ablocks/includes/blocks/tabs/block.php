<?php

namespace ABlocks\Blocks\Tabs;

use ABlocks\Classes\BlockBaseAbstract;
use ABlocks\Classes\CssGenerator;
use ABlocks\Controls\Typography;
use ABlocks\Controls\Dimensions;
use ABlocks\Controls\Border;
use ABlocks\Controls\Icon;
use ABlocks\Controls\BoxShadow;
use ABlocks\Controls\Range;

class Block extends BlockBaseAbstract {

	protected $block_name = 'tabs';

	public function build_css( $attributes ) {
		$css_generator = new CssGenerator( $attributes, $this->block_name );

		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-block-tabs',
			$this->get_tabs_css( $attributes ),
			$this->get_tabs_css( $attributes, 'Tablet' ),
			$this->get_tabs_css( $attributes, 'Mobile' )
		);

		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-block-tabs__tab-panel',
			$this->get_tabs_panel_css( $attributes ),
			$this->get_tabs_panel_css( $attributes, 'Tablet' ),
			$this->get_tabs_panel_css( $attributes, 'Mobile' )
		);

		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-block-tabs__tab',
			$this->get_tabs_menu_content_css( $attributes ),
			$this->get_tabs_menu_content_css( $attributes, 'Tablet' ),
			$this->get_tabs_menu_content_css( $attributes, 'Mobile' )
		);

		$css_generator->add_class_styles(
			'{{WRAPPER}}  .ablocks-block-tabs__tab--active',
			$this->get_tabs_menu_content_active_css( $attributes ),
			$this->get_tabs_menu_content_active_css( $attributes, 'Tablet' ),
			$this->get_tabs_menu_content_active_css( $attributes, 'Mobile' )
		);

		$css_generator->add_class_styles(
			'{{WRAPPER}}  .ablocks-block-tabs__tab:hover',
			$this->get_tabs_menu_content_hover_css( $attributes ),
			$this->get_tabs_menu_content_hover_css( $attributes, 'Tablet' ),
			$this->get_tabs_menu_content_hover_css( $attributes, 'Mobile' )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-block-tabs__tab-menu-title',
			$this->get_tabs_title_css( $attributes ),
			$this->get_tabs_title_css( $attributes, 'Tablet' ),
			$this->get_tabs_title_css( $attributes, 'Mobile' )
		);

		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-block-tabs__tab--active .ablocks-block-tabs__tab-menu-title',
			$this->get_tabs_active_title_css( $attributes ),
			$this->get_tabs_active_title_css( $attributes, 'Tablet' ),
			$this->get_tabs_active_title_css( $attributes, 'Mobile' )
		);

		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-block-tabs__tab-menu-subtitle',
			$this->get_tabs_subtitle_css( $attributes ),
			$this->get_tabs_subtitle_css( $attributes, 'Tablet' ),
			$this->get_tabs_subtitle_css( $attributes, 'Mobile' )
		);

		$css_generator->add_class_styles(
			'{{WRAPPER}}  .ablocks-block-tabs__tab--active .ablocks-block-tabs__tab-menu-subtitle',
			$this->get_tabs_active_subtitle_text_css( $attributes ),
			$this->get_tabs_active_subtitle_text_css( $attributes, 'Tablet' ),
			$this->get_tabs_active_subtitle_text_css( $attributes, 'Mobile' )
		);
		if ( isset( $attributes['showActiveSubTitle'] ) && $attributes['showActiveSubTitle'] === false ) {
			$css_generator->add_class_styles(
				'{{WRAPPER}} .ablocks-block-tabs__icon',
				$this->get_icon_position_css( $attributes ),
				$this->get_icon_position_css( $attributes, 'Tablet' ),
				$this->get_icon_position_css( $attributes, 'Mobile' )
			);
		} else {
			$css_generator->add_class_styles(
				'{{WRAPPER}} .ablocks-block-tabs__tab--active .ablocks-block-tabs__icon',
				$this->get_icon_position_css( $attributes ),
				$this->get_icon_position_css( $attributes, 'Tablet' ),
				$this->get_icon_position_css( $attributes, 'Mobile' )
			);
		}
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-block-tabs__tab--active .ablocks-block-tabs__progressbar',
			$this->progress_bar_style_css( $attributes ),
			$this->progress_bar_style_css( $attributes, 'Tablet' ),
			$this->progress_bar_style_css( $attributes, 'Mobile' )
		);
		$tabs_element_icon_wrapper_styles = Icon::get_wrapper_css( $attributes );

			$spacing_value = [];

		switch ( $attributes['iconPosition'] ) {
			case 'left':
				$spacing_value = Range::get_css([
					'attributeValue' => $attributes['spacing'] ?? null,
					'attributeObjectKey' => 'value',
					'defaultValue' => 0,
					'isResponsive' => true,
					'hasUnit' => true,
					'property' => 'margin-right',
					'unitDefaultValue' => 'px',
				]);
				break;
			case 'right':
				$spacing_value = Range::get_css([
					'attributeValue' => $attributes['spacing'] ?? null,
					'attributeObjectKey' => 'value',
					'defaultValue' => 0,
					'isResponsive' => true,
					'hasUnit' => true,
					'property' => 'margin-left',
					'unitDefaultValue' => 'px',
				]);
				break;
			case 'bottom':
				$spacing_value = Range::get_css([
					'attributeValue' => $attributes['spacing'] ?? null,
					'attributeObjectKey' => 'value',
					'defaultValue' => 0,
					'isResponsive' => true,
					'hasUnit' => true,
					'property' => 'margin-top',
					'unitDefaultValue' => 'px',
				]);
				break;
			case 'top':
				$spacing_value = Range::get_css([
					'attributeValue' => $attributes['spacing'] ?? null,
					'attributeObjectKey' => 'value',
					'defaultValue' => 0,
					'isResponsive' => true,
					'hasUnit' => true,
					'property' => 'margin-bottom',
					'unitDefaultValue' => 'px',
				]);
				break;
		}//end switch

		// Merge $spacing_value into icon wrapper styles
		$tabs_element_icon_wrapper_styles = array_merge(
			Icon::get_wrapper_css( $attributes ),
			$spacing_value
		);

		// Generate tablet and mobile styles for the icon wrapper
		$tablet_styles = array_merge(
			Icon::get_wrapper_css( $attributes, 'Tablet' ),
			$spacing_value
		);
		$mobile_styles = array_merge(
			Icon::get_wrapper_css( $attributes, 'Mobile' ),
			$spacing_value
		);

		// Add icon wrapper styles to the CSS generator
		if ( ! empty( $tabs_element_icon_wrapper_styles ) || ! empty( $tablet_styles ) || ! empty( $mobile_styles ) ) {
			$css_generator->add_class_styles(
				'{{WRAPPER}} .ablocks-block-tabs__icon .ablocks-icon-wrap',
				$tabs_element_icon_wrapper_styles,
				$tablet_styles,
				$mobile_styles
			);
		}

		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-block-tabs__icon .ablocks-icon-wrap img.ablocks-image-icon',
			Icon::get_element_image_css( $attributes ),
			Icon::get_element_image_css( $attributes, 'Tablet' ),
			Icon::get_element_image_css( $attributes, 'Mobile' ),
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-block-tabs__icon .ablocks-icon-wrap img.ablocks-image-icon:hover',
			Icon::get_element_image_hover_css( $attributes ),
			Icon::get_element_image_hover_css( $attributes, 'Tablet' ),
			Icon::get_element_image_hover_css( $attributes, 'Mobile' ),
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-block-tabs__icon .ablocks-icon-wrap svg.ablocks-svg-icon',
			Icon::get_element_css( $attributes ),
			Icon::get_element_css( $attributes, 'Tablet' ),
			Icon::get_element_css( $attributes, 'Mobile' ),
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-block-tabs__icon .ablocks-icon-wrap svg.ablocks-svg-icon:hover',
			Icon::get_element_image_hover_css( $attributes ),
			Icon::get_element_image_hover_css( $attributes, 'Tablet' ),
			Icon::get_element_image_hover_css( $attributes, 'Mobile' ),
		);

		$css_generator->add_class_styles(
			'{{WRAPPER}}  .ablocks-block-tabs__body',
			$this->get_tabs_content_css( $attributes ),
			$this->get_tabs_content_css( $attributes, 'Tablet' ),
			$this->get_tabs_content_css( $attributes, 'Mobile' )
		);

		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-block-tabs__body:hover',
			$this->get_tabs_content_hover_css( $attributes ),
			$this->get_tabs_content_hover_css( $attributes, 'Tablet' ),
			$this->get_tabs_content_hover_css( $attributes, 'Mobile' )
		);

		return $css_generator->generate_css();
	}


	public function get_tabs_css( $attributes, $device = '' ) {
		$tabs_css = [];

		// Determine the tabs menu position for the given device
		$menuPosition = $attributes['tabsMenuPositioning'][ 'value' . $device ] ?? '';

		// Apply CSS rules based on the device type and menu position
		if ( $device === 'Mobile' ) {
			if ( empty( $menuPosition ) || $menuPosition === 'left' || $menuPosition === 'top' ) {
				$tabs_css['flex-direction'] = 'column';
			} elseif ( $menuPosition === 'right' || $menuPosition === 'bottom' ) {
				$tabs_css['flex-direction'] = 'column-reverse';
			}
		} else {
			// For desktop and tablet
			if ( $menuPosition === 'top' ) {
				$tabs_css['flex-direction'] = 'column';
			} elseif ( $menuPosition === 'bottom' ) {
				$tabs_css['flex-direction'] = 'column-reverse';
			} elseif ( $menuPosition === 'right' ) {
				$tabs_css['flex-direction'] = 'row-reverse';
			} elseif ( $menuPosition === 'left' ) {
				$tabs_css['flex-direction'] = 'row';
			}
		}

		return $tabs_css;
	}

	public function get_tabs_panel_css( $attributes, $device = '' ) {
		$tabs_panel_css = [];

		// Handle tabMenuAlign for the given device
		if ( isset( $attributes['tabMenuAlignment'][ 'value' . $device ] ) ) {
			$tabs_panel_css['justify-content'] = $attributes['tabMenuAlignment'][ 'value' . $device ];
		}

		// Determine the tabsMenuPosition and apply styles accordingly
		$tabsMenuPosition = $attributes['tabsMenuPositioning'][ 'value' . $device ] ?? '';

		if ( $tabsMenuPosition === 'top' || $tabsMenuPosition === 'bottom' ) {
			// For column layout
			$tabs_panel_css['flex-direction'] = ( $device === 'Tablet' || $device === 'Mobile' ) ? 'column' : 'row';
			$tabs_panel_css['max-width'] = '100%';
			$tabs_panel_css['flex-wrap'] = 'wrap';
		} elseif ( empty( $tabsMenuPosition ) || $tabsMenuPosition === 'left' || $tabsMenuPosition === 'right' ) {
			// For row layout
			$tabs_panel_css['flex-direction'] = 'column';
			$tabs_panel_css['max-width'] = ( $device === 'Mobile' ) ? '100%' : '30%';
			$tabs_panel_css['min-width'] = ( $device === 'Mobile' ) ? '100%' : '30%';
			$tabs_panel_css['flex-grow'] = 1;
		}

		return $tabs_panel_css;
	}
	public function get_tabs_menu_content_css( $attributes, $device = '' ) {
		$css = [];
		$css['display'] = 'flex';

		// Handling icon position
		if ( isset( $attributes['iconPosition'] ) && $attributes['iconPosition'] === 'top' ) {
			$css['flex-direction'] = 'column';
			if ( isset( $attributes['menuContentAlignment'][ 'value' . $device ] ) ) {
				$css['align-items'] = $attributes['menuContentAlignment'][ 'value' . $device ];
			}
		}
		if ( isset( $attributes['iconPosition'] ) && $attributes['iconPosition'] === 'bottom' ) {
			$css['flex-direction'] = 'column-reverse';
			if ( isset( $attributes['menuContentAlignment'][ 'value' . $device ] ) ) {
				$css['align-items'] = $attributes['menuContentAlignment'][ 'value' . $device ];
			}
		}
		if ( isset( $attributes['iconPosition'] ) && $attributes['iconPosition'] === 'left' ) {
			$css['flex-direction'] = 'row';
			$css['align-items'] = 'center';
			if ( isset( $attributes['menuContentAlignment'][ 'value' . $device ] ) ) {
				$css['justify-content'] = $attributes['menuContentAlignment'][ 'value' . $device ];
			}
		}
		if ( isset( $attributes['iconPosition'] ) && $attributes['iconPosition'] === 'right' ) {
			$css['flex-direction'] = 'row-reverse';
			$css['align-items'] = 'center';
			if ( isset( $attributes['menuContentAlignment'][ 'value' . $device ] ) ) {
				$css['justify-content'] = $attributes['menuContentAlignment'][ 'value' . $device ];
			}
		}

		// Tab background color
		if ( isset( $attributes['tabBackgroundColor'] ) ) {
			$css['background-color'] = $attributes['tabBackgroundColor'];
		}

		// Handling tabs gap
		$tabs_gap_value = [];
		$tabs_menu_position = $attributes['tabsMenuPositioning'][ 'value' . $device ] ?? '';

		if ( ( $tabs_menu_position === 'left' || $tabs_menu_position === 'right' ) ) {
			$tabs_gap_value = array_merge(
				Range::get_css([
					'attributeValue' => $attributes['tabsGap'],
					'attribute_object_key' => 'value',
					'isResponsive' => true,
					'defaultValue' => 10,
					'hasUnit' => true,
					'unitDefaultValue' => 'px',
					'property' => 'margin-top',
					'device' => $device,
				]),
				Range::get_css([
					'attributeValue' => $attributes['tabsGap'],
					'attribute_object_key' => 'value',
					'isResponsive' => true,
					'defaultValue' => 10,
					'hasUnit' => true,
					'unitDefaultValue' => 'px',
					'property' => 'margin-bottom',
					'device' => $device,
				]),
			);
		}//end if
		if ( ( $tabs_menu_position === 'top' || $tabs_menu_position === 'bottom' ) ) {
			if ( $device === 'Tablet' || $device === 'Mobile' ) {
				$tabs_gap_value = array_merge(
					Range::get_css([
						'attributeValue' => $attributes['tabsGap'],
						'attribute_object_key' => 'value',
						'isResponsive' => true,
						'defaultValue' => 10,
						'hasUnit' => true,
						'unitDefaultValue' => 'px',
						'property' => 'margin-top',
						'device' => $device,
					]),
					Range::get_css([
						'attributeValue' => $attributes['tabsGap'],
						'attribute_object_key' => 'value',
						'isResponsive' => true,
						'defaultValue' => 10,
						'hasUnit' => true,
						'unitDefaultValue' => 'px',
						'property' => 'margin-bottom',
						'device' => $device,
					]),
				);
			} else {
				$tabs_gap_value = array_merge(
					Range::get_css([
						'attributeValue' => $attributes['tabsGap'],
						'attribute_object_key' => 'value',
						'isResponsive' => true,
						'defaultValue' => 10,
						'hasUnit' => true,
						'unitDefaultValue' => 'px',
						'property' => 'margin-left',
						'device' => $device,
					]),
					Range::get_css([
						'attributeValue' => $attributes['tabsGap'],
						'attribute_object_key' => 'value',
						'isResponsive' => true,
						'defaultValue' => 10,
						'hasUnit' => true,
						'unitDefaultValue' => 'px',
						'property' => 'margin-right',
						'device' => $device,
					]),
				);
			}//end if
		}//end if

		// Merging with margin, padding, and border styles
		return array_merge(
			$css,
			$tabs_gap_value,
			Dimensions::get_css( $attributes['menuContentPadding'] ?? [], 'padding', $device ),
			Border::get_css( $attributes['menuContentBorder'] ?? [], '', $device ),
			BoxShadow::get_css( $attributes['boxShadow'], '', $device ),
		);
	}


	public function get_tabs_menu_content_active_css( $attributes, $device = '' ) {
		$tabs_menu_content_active_css = [];
		if ( isset( $attributes['tabsMenuPositioning'][ 'value' . $device ] ) ) {
			switch ( $attributes['tabsMenuPositioning'][ 'value' . $device ] ) {
				case 'top':
					$tabs_menu_content_active_css['border-bottom-color'] = 'none';
					break;
				case 'bottom':
					$tabs_menu_content_active_css['border-top-color'] = 'none';
					break;
				case 'left':
					$tabs_menu_content_active_css['border-right-color'] = 'none';
					break;
				case 'right':
					$tabs_menu_content_active_css['border-left-color'] = 'none';
					break;
			}
		}
		if ( isset( $attributes['tabActiveBackgroundColor'] ) ) {
			$tabs_menu_content_active_css['background-color'] = $attributes['tabActiveBackgroundColor'];
		}
		if ( isset( $attributes['activeBorderColor'] ) ) {
			$tabs_menu_content_active_css['border-color'] = $attributes['activeBorderColor'] . '!important';
		}
		return $tabs_menu_content_active_css;
	}


	public function get_tabs_menu_content_hover_css( $attributes, $device = '' ) {

		return array_merge(
			Border::get_hover_css( $attributes['menuContentBorder'] ?? [], '', $device ),
			BoxShadow::get_hover_css( $attributes['boxShadow'], '', $device )
		);
	}
	public function get_tabs_title_css( $attributes, $device = '' ) {
		$css = [];
		if ( isset( $attributes['titleTextColor'] ) ) {
			$css['color'] = $attributes['titleTextColor'];
		}
		return array_merge(
			$css,
		Typography::get_css( $attributes['titleTypography'] ?? [], '', $device ));
	}

	public function get_tabs_active_title_css( $attributes, $device = '' ) {
		$css = [];
		if ( isset( $attributes['titleTextActiveColor'] ) ) {
			$css['color'] = $attributes['titleTextActiveColor'];
		}
		return $css;
	}

	public function get_tabs_subtitle_css( $attributes, $device = '' ) {
		$tabsSubtitleCSS = [];
		if ( isset( $attributes['showActiveSubTitle'] ) && $attributes['showActiveSubTitle'] === true ) {
			$tabsSubtitleCSS['display'] = 'none';
		}
		// Get subtitle text color
		if ( isset( $attributes['subTitleTextColor'] ) ) {
			$tabsSubtitleCSS['color'] = $attributes['subTitleTextColor'];
		}

		// Get typography styles
		$typographyStyles = Typography::get_css( $attributes['subTitleTypography'] ?? [], '', $device );
		$tabsSubtitleCSS = array_merge( $tabsSubtitleCSS, $typographyStyles );

		// Determine width based on menu position
		$position = $attributes['tabsMenuPositioning'][ 'value' . $device ] ?? '';
		if ( $position === 'top' || $position === 'bottom' ) {
			$tabsSubtitleCSS['width'] = '160px';
		} else {
			$tabsSubtitleCSS['width'] = '100%';
		}

		return $tabsSubtitleCSS;
	}


	public function get_tabs_active_subtitle_text_css( $attributes, $device = '' ) {
		$css = [];
		if ( isset( $attributes['showActiveSubTitle'] ) && $attributes['showActiveSubTitle'] === true ) {
			$css['display'] = 'block';
		}
		if ( isset( $attributes['subTitleTextActiveColor'] ) ) {
			$css['color'] = $attributes['subTitleTextActiveColor'] . '!important';
		}
		return $css;
	}
	public function get_icon_position_css( $attributes, $device = '' ) {
		return array_merge(
			Dimensions::get_css( $attributes['iconPositionMargin'] ?? [], 'margin', $device )
		);
	}
	public function progress_bar_style_css( $attributes, $device = '' ) {
		$css = [];
		if ( isset( $attributes['progressBarColor'] ) ) {
			$css['background-color'] = $attributes['progressBarColor'];
		}
		return $css;
	}

	public function get_tabs_icon_css( $attributes, $device = '' ) {
		$css = [];
		$iconType = $attributes['iconType'] ?? 'default';
		$iconShape = $attributes['iconShape'] ?? 'square';
		$iconColor = $attributes['iconColor'] ?? '#69727d';
		$iconBackground = $attributes['iconBackground'] ?? 'transparent';
		$size = $attributes['size'][ 'value' . $device ] ?? null;
		$spacing = $attributes['spacing'][ 'value' . $device ] ?? null;
		$iconPosition = $attributes['iconPosition'] ?? '';

		// Determine icon view CSS based on type and shape
		$iconViewCSS = [];

		if ( $iconType !== 'default' ) {
			if ( $iconType === 'stacked' ) {
				$iconViewCSS['background'] = $iconBackground;
				$iconViewCSS['padding'] = '.5em';

				if ( $iconShape === 'circle' ) {
					$iconViewCSS['border-radius'] = '50px';
				}
			} elseif ( $iconType === 'framed' ) {
				$iconViewCSS['background'] = 'transparent';
				$iconViewCSS['padding'] = '.5em';
				$iconViewCSS['border'] = '2px solid ' . ( $iconColor ? $iconColor : '#69727d' );

				if ( $iconShape === 'circle' ) {
					$iconViewCSS['border-radius'] = '50px';
				}
			}
		}

		// Apply size CSS if available
		if ( $size ) {
			$css['width'] = $size . 'px !important';
			$css['height'] = $size . 'px !important';
		}

		// Apply icon color if available
		if ( isset( $attributes['iconColor'] ) ) {
			$css['fill'] = $attributes['iconColor'];
		}

		// Apply spacing based on position
		if ( $spacing ) {
			switch ( $iconPosition ) {
				case 'left':
					$css['margin-right'] = $spacing . 'px';
					break;
				case 'right':
					$css['margin-left'] = $spacing . 'px';
					break;
				case 'bottom':
					$css['margin-top'] = $spacing . 'px';
					break;
				case 'top':
					$css['margin-bottom'] = $spacing . 'px';
					break;
			}
		}
		// Merge the icon view CSS with the main CSS
		return array_merge( $css, $iconViewCSS );
	}

	public function get_tabs_active_icon_css( $attributes ) {
		$css = [];

		// Check for active icon color
		if ( isset( $attributes['iconActiveColor'] ) ) {
			$css['fill'] = $attributes['iconActiveColor'];
		}

		// Check for icon type and active background color
		if ( isset( $attributes['iconType'] ) && $attributes['iconType'] !== 'default' && $attributes['iconType'] !== 'framed' ) {
			if ( isset( $attributes['iconActiveBackground'] ) ) {
				$css['background-color'] = $attributes['iconActiveBackground'];
			}
		}

		return $css;
	}

	public function get_tabs_content_css( $attributes, $device = '' ) {
		$tabs_content_css = [];

		// Merge margin, padding, and border CSS
		$tabs_content_css = array_merge(
			$tabs_content_css,
			Dimensions::get_css( $attributes['contentMargin'] ?? [], 'margin', $device ),
			Dimensions::get_css( $attributes['contentPadding'] ?? [], 'padding', $device ),
			Border::get_css( $attributes['contentBorder'] ?? [], '', $device )
		);

		// Apply background color if set
		if ( isset( $attributes['contentBackgroundColor'] ) ) {
			$tabs_content_css['background-color'] = $attributes['contentBackgroundColor'];
		}

		// Apply max-width based on device and tabsMenuPosition
		if ( $device === 'Mobile' ) {
			$tabs_content_css['max-width'] = '100%';
		} else {
			$menuPosition = $attributes['tabsMenuPositioning'][ 'value' . $device ] ?? '';
			if ( $menuPosition === 'top' || $menuPosition === 'bottom' ) {
				$tabs_content_css['max-width'] = '100% !important';
			} elseif ( $menuPosition === 'left' || $menuPosition === 'right' ) {
				$tabs_content_css['max-width'] = '70%';
				$tabs_content_css['flex-grow'] = 3;
			}
		}

		return $tabs_content_css;
	}


	public function get_tabs_content_hover_css( $attributes, $device = '' ) {
		return array_merge(
			Border::get_hover_css( $attributes['contentBorder'] ?? [], '', $device ),
		);
	}
}
