<?php
namespace ABlocks\Blocks\Map;

use ABlocks\Classes\BlockBaseAbstract;
use ABlocks\Classes\CssGenerator;
use ABlocks\Controls\CssFilter;
use ABlocks\Controls\Range;

class Block extends BlockBaseAbstract {
	protected $block_name = 'map';
	protected $style_depends = [ 'ablocks-leaflet-style', 'ablocks-leaflet-full-screen-style' ];
	protected $script_depends = [ 'ablocks-leaflet-script', 'ablocks-leaflet-full-screen-script' ];

	public function build_css( $attributes ) {
		$css_generator = new CssGenerator( $attributes, $this->block_name );

		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-map-block',
			$this->get_map_size_css( $attributes ),
			$this->get_map_size_css( $attributes, 'Tablet' ),
			$this->get_map_size_css( $attributes, 'Mobile' )
		);

		return $css_generator->generate_css();
	}

	private function get_map_size_css( $attributes, $device = '' ) {
		$size_css = [];

		if ( ! empty( $attributes['mapWidth'][ 'value' . $device ] ) ) {
			$width_value = $attributes['mapWidth'][ 'value' . $device ];
			$width_unit = $attributes['mapWidth'][ 'valueUnit' . $device ] ?? '%';
			$size_css['width'] = $width_value . $width_unit;
		}

		if ( ! empty( $attributes['mapHeight'][ 'value' . $device ] ) ) {
			$height_value = $attributes['mapHeight'][ 'value' . $device ];
			$height_unit = ! empty( $attributes['mapHeight'][ 'valueUnit' . $device ] )
				? $attributes['mapHeight'][ 'valueUnit' . $device ]
				: 'px';

			$size_css['height'] = $height_value . $height_unit;
		}

		return array_merge(
			Range::get_css([
				'attributeValue' => $attributes['mapWidth'],
				'attribute_object_key' => 'value',
				'isResponsive' => true,
				'defaultValue' => 100,
				'hasUnit' => true,
				'unitDefaultValue' => '%',
				'property' => 'width',
				'device' => $device,
			]),
			Range::get_css([
				'attributeValue' => $attributes['mapHeight'],
				'attribute_object_key' => 'value',
				'isResponsive' => true,
				'defaultValue' => 500,
				'hasUnit' => true,
				'unitDefaultValue' => 'px',
				'property' => 'height',
				'device' => $device,
			]),
			$size_css,
			isset( $attributes['cssFilter'] ) ? CssFilter::get_css( $attributes['cssFilter'], '', $device ) : []
		);
	}

	public static function escaping_array_data( $array ) {
		foreach ( $array as $key => &$value ) {
			if ( is_array( $value ) ) {
				$value = self::escaping_array_data( $value );
			} else {
				$value = esc_attr( $value );
			}
		}
		return $array;
	}

	public function render_block_content( $attributes, $content, $block_instance ) {
		$custom_icon_url = '';
		if ( isset( $attributes['iconImageUrl'] ) && ! empty( $attributes['iconImageUrl'] ) ) {
			$custom_icon_url = esc_url( $attributes['iconImageUrl'] );
		} else {
			$custom_icon_url = esc_url( ABLOCKS_ASSETS_URL . 'images/marker-icon.png' );
		}
		$settings = array(
			'mapMarkerList'   => $this->escaping_array_data( isset( $attributes['mapMarkerList'] ) ? $attributes['mapMarkerList'] : array() ),
			'mapZoom'         => isset( $attributes['mapZoom'] ) ? esc_attr( $attributes['mapZoom'] ) : 10,
			'scrollWheelZoom' => isset( $attributes['scrollWheelZoom'] ) ? esc_attr( $attributes['scrollWheelZoom'] ) : false,
			'mapType'         => isset( $attributes['mapType'] ) ? esc_attr( $attributes['mapType'] ) : 'GM',
			'centerIndex'     => isset( $attributes['centerIndex'] ) ? intval( esc_attr( $attributes['centerIndex'] ) ) : 0,
			'defaultMarkerIcon' => $custom_icon_url,
			'iconHeight' => isset( $attributes['iconHeight'] ) ? esc_attr( $attributes['iconHeight'] ) : 40,
			'iconWidth' => isset( $attributes['iconWidth'] ) ? esc_attr( $attributes['iconWidth'] ) : 25,
		);

		ob_start();
		?>
		<div 
			data-settings='<?php
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo htmlspecialchars( wp_json_encode( $settings ), ENT_QUOTES, 'UTF-8' ); 
			?>' 
			class="ablocks-map-block"
		>
		</div>
		<?php
		$output = ob_get_clean();
		return $output;
	}

}
