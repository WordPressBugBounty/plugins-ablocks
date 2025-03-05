<?php
namespace ABlocks\Classes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use \ABlocks\Helper;
use \ABlocks\Assets;

abstract class BlockBaseAbstract {

	protected $namespace = 'ablocks';

	protected $parent_block_name = '';

	protected $block_name = '';

	protected $style_depends = [];
	protected $script_depends = [];

	public function __construct( $keep_silent = false ) {
		if ( $this->is_enabled_block() && ! $keep_silent ) {
			add_action( 'init', array( $this, 'register_block' ), 20 );
			add_action( 'ablocks/before_render_' . $this->block_name . '_block_content', array( $this, 'enqueue_block_specific_static_assets' ) );
		}
	}

	public function is_enabled_block() {
		global $ablocks_blocks;
		$block_name = ! empty( $this->parent_block_name ) ? $this->parent_block_name : $this->block_name;
		if ( isset( $ablocks_blocks->{$block_name} ) ) {
			return (bool) $ablocks_blocks->{$block_name};
		}
		return false;
	}

	public function register_block() {
		$block_path = ABLOCKS_ASSETS_PATH . 'build/blocks/' . $this->block_name . '/block.json';
		// Register the block with the merged attributes and render callback
		register_block_type( $block_path, array(
			'render_callback' => array( $this, 'render_callback' ),
		) );
	}

	public function get_attributes() {
		$block_attributes = include ABLOCKS_BLOCKS_DIR_PATH . $this->block_name . '/attributes.php';
		return apply_filters( 'ablocks/register_block_attributes', $block_attributes, $this->block_name, $this->parent_block_name );
	}

	private function get_block_class( $css_class ) {
		$classes = array();
		if ( $css_class ) {
			if ( ! is_array( $css_class ) ) {
				$css_class = preg_split( '#\s+#', $css_class );
			}
			$classes = array_map( 'esc_attr', $css_class );
		} else {
			// Ensure that we always coerce class to being an array.
			$css_class = array();
		}

		return 'class="' . esc_attr( implode( ' ', $classes ) ) . '"';
	}
	private function get_block_data_settings_attributes( $settings ) {
		if ( ! is_array( $settings ) ) {
			return '';
		}
		// Sanitize each setting in the array
		$sanitized_settings = array_map( 'esc_attr', $settings );
		// Encode sanitized settings to JSON and escape it for safe output
		return sprintf( 'data-settings="%s"', esc_attr( wp_json_encode( $sanitized_settings ) ) );
	}
	private function get_dynamic_block_wrap( $attributes, $content, $block_instance ) {
		$block_id = ( isset( $attributes['block_id'] ) ? $attributes['block_id'] : '' );
		$animation = ( isset( $attributes['_animation'] ) ? $attributes['_animation'] : [] );
		$block_class_args = array( 'ablocks-block', 'ablocks-block-' . $block_id, 'ablocks-block--' . $this->block_name );
		if (
			count( $animation ) &&
			( $animation['animationType'] && $animation['animationType'] !== 'none' ) ||
			( $animation['animationTypeTablet'] && $animation['animationTypeTablet'] !== 'none' ) ||
			( $animation['animationTypeMobile'] && $animation['animationTypeMobile'] !== 'none' )
		) {
			array_push( $block_class_args, 'ablocks-invisible' );
		}
		ob_start();
		?>
		<div <?php echo $this->get_block_class( $block_class_args ) . $this->get_block_data_settings_attributes( $animation ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			<div class="ablocks-block-container">
				<?php
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo $this->render_block_content( $attributes, $content, $block_instance );
				?>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}


	public function render_block_content( $attributes, $content, $block_instance ) {
		return $content;
	}

	public function render_callback( $attributes, $content, $block_instance ) {
		$block_name = $block_instance->name;
		do_action( 'ablocks/before_render_' . explode( '/', $block_name )[1] . '_block_content', $block_name );

		// Dynamic block
		if ( ! $content ) {
			$content = $this->get_dynamic_block_wrap( $attributes, $content, $block_instance );
		}

		// Static Block but control render from php
		$content = apply_filters( 'ablocks/get_render_block_content', $content, $attributes, $block_instance );
		if ( ! $content ) {
			return $content;
		}

		// Pure Static block
		if ( apply_filters( 'ablocks/is_allow_block_inline_assets', ! is_admin() && ! Helper::is_enabled_assets_generation() ) ) {
			$build_css = '<style>' . $this->build_css( $attributes ) . '</style>';
			return $build_css . $content;
		}

		return $content;
	}

	private function enqueue_static_assets( $block_name ) {
		// Library
		if ( count( $this->get_style_depends() ) ) {
			foreach ( $this->get_style_depends() as $style_handler ) {
				wp_enqueue_style( $style_handler );
			}
		}

		// block static css
		if ( file_exists( ABLOCKS_ASSETS_PATH . 'build/blocks/' . $block_name . '/style.css' ) ) {
			wp_enqueue_style( 'ablocks-' . $block_name . '-block-static-style', ABLOCKS_ASSETS_URL . 'build/blocks/' . $block_name . '/style.css', array(), filemtime( ABLOCKS_ASSETS_PATH . 'build/blocks/' . $block_name . '/style.css' ), 'all' );
		}

		// Library
		if ( count( $this->get_script_depends() ) ) {
			foreach ( $this->get_script_depends() as $script_handler ) {
				wp_enqueue_script( $script_handler );
			}
		}

		if ( file_exists( ABLOCKS_ASSETS_PATH . 'build/blocks/' . $block_name . '/view.js' ) ) {
			$dependencies = include ABLOCKS_ASSETS_PATH . 'build/blocks/' . $block_name . '/view.asset.php';
			wp_enqueue_script(
				'ablocks-' . $block_name . '-block-static-script',
				ABLOCKS_ASSETS_URL . 'build/blocks/' . $block_name . '/view.js',
				$dependencies['dependencies'],
				$dependencies['version'],
				true
			);
			$Assets = new Assets();
			wp_localize_script(
				'ablocks-' . $block_name . '-block-static-script',
				'ABlocksGlobal',
				$Assets->get_localize_script_data()
			);
		}
	}

	public function enqueue_block_static_assets() {
		if ( ! is_admin() && ! Helper::is_enabled_assets_generation() ) {
			$this->enqueue_static_assets( $this->block_name );
		}//end if
	}
	public function enqueue_block_specific_static_assets( $block_name ) {
		if ( ! is_admin() && ! Helper::is_enabled_assets_generation() ) {
			$this->enqueue_static_assets( explode( '/', $block_name )[1] );
		}//end if
	}

	public function get_static_css() {
		if ( file_exists( ABLOCKS_ASSETS_PATH . 'build/blocks/' . $this->block_name . '/style.css' ) ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			$has_static_css = file_get_contents( ABLOCKS_ASSETS_PATH . 'build/blocks/' . $this->block_name . '/style.css' );
			if ( $has_static_css ) {
				return $has_static_css;
			}
		}
		return '';
	}
	public function get_static_js() {
		if ( file_exists( ABLOCKS_ASSETS_PATH . 'build/blocks/' . $this->block_name . '/view.js' ) ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			$has_static_js = file_get_contents( ABLOCKS_ASSETS_PATH . 'build/blocks/' . $this->block_name . '/view.js' );
			if ( $has_static_js ) {
				return $has_static_js;
			}
		}
		return '';
	}
	public function get_style_depends() {
		return apply_filters( 'ablocks/block_style_depends', array_merge( $this->style_depends, array( 'ablocks-animate-style', 'ablocks-common-style' ) ) );
	}
	public function get_script_depends() {
		return apply_filters( 'ablocks/block_script_depends', array_merge( $this->script_depends, array( 'ablocks-common-script' ) ) );
	}
	abstract public function build_css( $attributes);
}
