<?php
namespace ABlocks\Blocks\TableOfContent;

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
use ABlocks\Controls\Alignment;
use ABlocks\Controls\Range;

class Block extends BlockBaseAbstract {
	protected $block_name = 'table-of-content';


	public function build_css( $attributes ) {
		$css_generator = new CssGenerator( $attributes );

		$css_generator->add_class_styles(
			'{{WRAPPER}}  .ablocks-toc__header-title',
			$this->get_toc_title_css( $attributes ),
			$this->get_toc_title_css( $attributes, 'Tablet' ),
			$this->get_toc_title_css( $attributes, 'Mobile' )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}}  .ablocks-toc__header',
			$this->get_toc_header_css( $attributes ),
			$this->get_toc_header_css( $attributes, 'Tablet' ),
			$this->get_toc_header_css( $attributes, 'Mobile' )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}}  .ablocks-toc-body',
			$this->get_toc_body_css( $attributes ),
			$this->get_toc_body_css( $attributes, 'Tablet' ),
			$this->get_toc_body_css( $attributes, 'Mobile' )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}}  .ablocks-toc__header-toggle-icon svg',
			$this->get_toc_header_icon_css( $attributes ),
			$this->get_toc_header_icon_css( $attributes, 'Tablet' ),
			$this->get_toc_header_icon_css( $attributes, 'Mobile' )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}}  .ablocks-block-container .ablocks-toc-list,
		    {{WRAPPER}}  .ablocks-block-container .ablocks-toc-list li a',
			$this->get_list_item_css( $attributes ),
			$this->get_list_item_css( $attributes, 'Tablet' ),
			$this->get_list_item_css( $attributes, 'Mobile' )
		);

		return $css_generator->generate_css();
	}



	public function get_toc_title_css( $attributes, $device = '' ) {
		$css = array();
		$toc_title_typography_css = ! empty( $attributes['titleTypography'] ) ? Typography::get_css( $attributes['titleTypography'], '', $device ) : array();
		if ( isset( $attributes['titleColor'] ) ) {
			$css['color'] = $attributes['titleColor'];
		}
		return array_merge( $toc_title_typography_css, $css );
	}
	public function get_toc_header_css( $attributes, $device = '' ) {
		$css = array();
		$headerorder = ! empty( $attributes['headerBorder'] ) ? Border::get_css( $attributes['headerBorder'], '', $device ) : array();
		$header_padding_css = ! empty( $attributes['header_padding'] ) ? Dimensions::get_css( $attributes['header_padding'], 'padding', $device ) : array();
		if ( isset( $attributes['headerBG'] ) ) {
			$css['background'] = $attributes['headerBG'];
		}
		return array_merge( $css, $headerorder, $header_padding_css );
	}
	public function get_toc_body_css( $attributes, $device = '' ) {
		$css = array();
		$listing_padding_css = ! empty( $attributes['list_padding'] ) ? Dimensions::get_css( $attributes['list_padding'], 'padding', $device ) : array();
		if ( isset( $attributes['bodyBG'] ) ) {
			$css['background'] = $attributes['bodyBG'];
		}
		return array_merge( $listing_padding_css, $css );
	}
	public function get_toc_header_icon_css( $attributes, $device = '' ) {
		$css = array_merge(
			Range::get_css([
				'attributeValue' => $attributes['iconSize'],
				'isResponsive' => false,
				'defaultValue' => 20,
				'unitDefaultValue' => 'px',
				'property' => 'width',
				'device' => $device,
			]),
			Range::get_css([
				'attributeValue' => $attributes['iconSize'],
				'isResponsive' => false,
				'defaultValue' => 20,
				'unitDefaultValue' => 'px',
				'property' => 'height',
				'device' => $device,
			]),
		);
		if ( ! empty( $attributes['iconSize'] ) ) {
			$css['width'] = $attributes['iconSize'] . 'px';
			$css['height'] = $attributes['iconSize'] . 'px';
		}
		if ( isset( $attributes['iconColor'] ) ) {
			$css['fill'] = $attributes['iconColor'];
		}
		return $css;
	}

	public function get_list_item_css( $attributes, $device = '' ) {
		$css = array();

		$contentTypography = ! empty( $attributes['contentTypography'] ) ? Typography::get_css( $attributes['contentTypography'], '', $device ) : array();

		if ( ! empty( $attributes['itemColor'] ) ) {
			$css['color'] = $attributes['itemColor'];
		}
		return array_merge(
			Range::get_css([
				'attributeValue' => $attributes['listItemGap'],
				'attribute_object_key' => 'value',
				'isResponsive' => false,
				'defaultValue' => 30,
				'unitDefaultValue' => 'px',
				'property' => 'line-height',
				'hasUnit' => true,
				'device' => $device,
			]),
			$contentTypography,
			$css,
		);
	}




	public function add_toc_to_post_content( $content ) {

		$content = preg_replace_callback('/<h([1-6])(.*?)>(.*?)<\/h\1>/i', function ( $matches ) {
			$level = $matches[1];  // Heading level (1-6)
			$attributes = $matches[2];  // Existing attributes (classes, styles, etc.)
			$heading = $matches[3];  // Heading text/content
			$anchor = sanitize_title( $heading );  // Generate a sanitized ID
			// Check if the id attribute is already present
			if ( strpos( $attributes, 'id=' ) === false ) {
				// Insert the id attribute after the opening <h> tag, preserving existing attributes
				return '<h' . $level . $attributes . ' id="' . $anchor . '">' . $heading . '</h' . $level . '>';
			}
			return $matches[0];  // If id is already present, return the original match
		}, $content);
		return $content;
	}


	public function render_block_content( $attributes, $content, $block_instance ) {
		$post = get_post();
		if ( ! $post ) {
			return '';
		}

		add_filter( 'the_content', [ $this, 'add_toc_to_post_content' ] );

		// Sanitize and escape icon attributes
		$open_icon_attributes = array(
			'path'      => esc_attr( $attributes['openIconSvgPath'] ),
			'viewBox'   => esc_attr( $attributes['openIconSvgViewBox'] ),
			'className' => esc_attr( $attributes['openIconClass'] ),
			'width'     => '20',
			'height'    => '20',
		);
		$close_icon_attributes = array(
			'path'      => esc_attr( $attributes['closeIconSvgPath'] ),
			'viewBox'   => esc_attr( $attributes['closeIconSvgViewBox'] ),
			'className' => esc_attr( $attributes['closeIconClass'] ),
			'width'     => '20',
			'height'    => '20',
		);

		$post_content = $post->post_content;
		preg_match_all( '/<h([1-6])[^>]*>(.*?)<\/h\1>/', $post_content, $matches, PREG_SET_ORDER );
		$toc = '';

		// Build TOC header
		if ( (bool) $attributes['hideTitle'] === true ) :
			$toc = '<div class="ablocks-toc__header">';
			$toc .= '<span class="ablocks-toc__header-title">' . esc_html( $attributes['tocTableTitle'] ) . '</span>';
			if ( $attributes['collapSible'] ) :
				$toc .= '<div class="ablocks-toc__header-toggle-icon">';
				$toc .= '<span class="ablocks-toc__show">' . Helper::render_svg_icon_using_attr( $close_icon_attributes ) . '</span>';
				$toc .= '<span class="ablocks-toc__hide">' . Helper::render_svg_icon_using_attr( $open_icon_attributes ) . '</span>';
				$toc .= '</div>';
			endif;
			$toc .= '</div>';
		endif;

		$headings = [];
		$unique_anchors = [];

		// Process headings
		foreach ( $matches as $match ) {
			$level = intval( $match[1] );
			$heading = trim( $match[2] );
			$base_anchor = strtolower( sanitize_title( $heading ) );
			$anchor = $base_anchor;
			$count = 1;

			// Ensure unique anchors
			while ( in_array( $anchor, $unique_anchors, true ) ) {
				$anchor = $base_anchor . '-' . $count;
				$count++;
			}

			// Add heading if enabled in attributes
			if ( (
				( $level === 1 && $attributes['H1'] ) ||
				( $level === 2 && $attributes['H2'] ) ||
				( $level === 3 && $attributes['H3'] ) ||
				( $level === 4 && $attributes['H4'] ) ||
				( $level === 5 && $attributes['H5'] ) ||
				( $level === 6 && $attributes['H6'] ) ) ) {
				$headings[] = [
					'level'   => $level,
					'heading' => esc_html( $heading ),
					'anchor'  => esc_attr( $anchor ),
				];
				$unique_anchors[] = $anchor;
			}
		}//end foreach

		// Build TOC body
		$toc .= '<div class="ablocks-toc-body">';
		$toc .= $this->generate_toc_list( $attributes, $headings );
		$toc .= '</div>';

		return $toc;
	}

	private function generate_toc_list( $attributes, $headings ) {
		if ( empty( $headings ) ) {
			return '';
		}

		$toc = '';
		$marker_view = in_array( $attributes['markerView'], [ 'ul', 'ol' ], true ) ? $attributes['markerView'] : 'ul';
		$current_level = 0;
		$open_lists = [];

		foreach ( $headings as $index => $heading ) {
			if ( ! isset( $heading['level'], $heading['heading'], $heading['anchor'] ) ) {
				continue;
			}

			$level = (int) $heading['level'];

			// If the first item, open the root list
			if ( $index === 0 ) {
				$toc .= '<' . esc_attr( $marker_view ) . ' class="ablocks-toc-list">';
				$open_lists[] = $marker_view;
				$current_level = $level;
			}

			// If deeper heading level, open a nested list
			while ( $level > $current_level ) {
				$toc .= '<' . esc_attr( $marker_view ) . ' class="ablocks-toc-list">';
				$open_lists[] = $marker_view;
				$current_level++;
			}

			// If shallower heading level, close open lists
			while ( $level < $current_level ) {
				$toc .= '</' . esc_attr( array_pop( $open_lists ) ) . '>';
				$current_level--;
			}

			// Close previous <li> before adding a new one (except for the first)
			if ( $index > 0 ) {
				$toc .= '</li>';
			}

			// Add list item
			$toc .= '<li class="ablocks-toc-item">';
			$toc .= '<a class="ablocks-toc-item-link" href="#' . esc_attr( $heading['anchor'] ) . '">' . esc_html( $heading['heading'] ) . '</a>';
		}//end foreach

		// Close any remaining open lists
		while ( ! empty( $open_lists ) ) {
			$toc .= '</li></' . esc_attr( array_pop( $open_lists ) ) . '>';
		}

		return $toc;
	}









}
