<?php
namespace ABlocks;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use ABlocks\Classes\FileUpload;
use ABlocks\Classes\BlockGlobal;
use ABlocks\traits\Importer;

class Helper {

	use Importer;

	public static function get_time() {
		return time() + ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS );
	}

	public static function get_settings( $key, $default = null ) {
		global $ablocks_settings;

		if ( isset( $ablocks_settings->{$key} ) ) {
			return $ablocks_settings->{$key};
		}

		return $default;
	}


	public static function is_plugin_installed( $path ) {
		$installed_plugins = get_plugins();
		return isset( $installed_plugins[ $path ] );
	}

	public static function is_active_academy() {
		$academy = 'academy/academy.php';
		return self::is_plugin_active( $academy );
	}


	public static function is_active_storeengine() {
		$storeengine = 'storeengine/storeengine.php';
		return self::is_plugin_active( $storeengine );
	}

	public static function is_active_ablocks_pro() {
		$ablocks = 'ablocks-pro/ablocks-pro.php';
		return self::is_plugin_active( $ablocks );
	}
	public static function is_active_wp_map_block() {
		$wp_map_block = 'wp-map-block/wp-map-block.php';
		return self::is_plugin_active( $wp_map_block );
	}

	public static function is_enabled_assets_generation() {
		$flag = false;
		if ( (bool) self::get_settings( 'enabled_assets_file_generation' ) ) {
			$flag = function_exists( 'wp_is_block_theme' ) ? ! wp_is_block_theme() : true;
		}
		return apply_filters( 'ablocks/is_enabled_assets_generation', $flag );
	}

	public static function is_plugin_active( $basename ) {
		if ( ! function_exists( 'get_plugins' ) ) {
			include_once ABSPATH . '/wp-admin/includes/plugin.php';
		}
		return is_plugin_active( $basename );
	}

	public static function is_dev_mode_enable() {
		$environment = wp_get_environment_type();
		if ( 'local' === $environment || 'development' === $environment ) {
			return true;
		}
	}

	public static function get_admin_menu_list() {
		$menu                                     = [];
		$menu[ ABLOCKS_PLUGIN_SLUG ]              = [
			'parent_slug' => ABLOCKS_PLUGIN_SLUG,
			'title'       => __( 'Dashboard', 'ablocks' ),
			'capability'  => 'manage_options',
		];
		$menu[ ABLOCKS_PLUGIN_SLUG . '-settings' ]   = [
			'parent_slug' => ABLOCKS_PLUGIN_SLUG,
			'title'       => __( 'Settings', 'ablocks' ),
			'capability'  => 'manage_options',
		];
		if ( ! defined( 'ABLOCKS_PRO_VERSION' ) ) {
			$menu[ ABLOCKS_PLUGIN_SLUG . '-get-pro' ] = [
				'parent_slug' => ABLOCKS_PLUGIN_SLUG,
				'title'       => '<span class="dashicons dashicons-awards academy-blue-color"></span> ' . __( 'Get Pro', 'ablocks' ),
				'capability'  => 'manage_options',
			];
		}
		return apply_filters( 'ablocks/admin_menu_list', $menu );
	}

	public static function get_preloader_html() {
		ob_start();
		?>
			<div class="ablocks-initial-preloader"><?php esc_html_e( 'Loading...', 'ablocks' ); ?></div>
		<?php
		return ob_get_clean();
	}
	public static function has_value( $value ) {
		return isset( $value ) && ! empty( $value );
	}
	public static function is_gutenberg_editor() {
		global $pagenow;
		if ( $pagenow === 'post.php' || $pagenow === 'post-new.php' ) {
			return true;
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return ( isset( $_GET['context'] ) && 'edit' === $_GET['context'] ) || ( isset( $_GET['action'] ) && 'edit' === $_GET['action'] );
	}
	public static function attr_shortcode( $attr_array ) {
		$html_attr = '';
		foreach ( $attr_array as $attr_name => $attr_val ) {
			if ( empty( $attr_val ) ) {
				continue;
			}
			if ( is_array( $attr_val ) ) {
				$html_attr .= $attr_name . '="' . implode( ',', $attr_val ) . '" ';
			} else {
				$html_attr .= $attr_name . '="' . $attr_val . '" ';
			}
		}
		return $html_attr;
	}

	public static function get_attribute_value( $attributes, $attribute_name ) {
		return isset( $attributes[ $attribute_name ] ) ? $attributes[ $attribute_name ] : '';
	}

	public static function get_terms_list( $taxonomy = 'category' ) {
		$options = [];
		$terms   = get_terms( [
			'taxonomy'   => $taxonomy,
			'hide_empty' => true,
		] );

		if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				$options[] = [
					'label' => $term->name,
					'value' => $term->term_id,
				];
			}
		}

		return $options;
	}

	public static function get_icon_picker_attribute( $attributePrefix = 'icon', $defaultValue = [] ) {
		$svgPathKey = $attributePrefix . 'SvgPath';
		$svgViewBoxKey = $attributePrefix . 'SvgViewBox';
		$svgClassKey = $attributePrefix . 'Class';

		$attribute = [
			$svgPathKey => [
				'type' => 'string',
				'source' => 'attribute',
				'selector' => 'svg.ablocks-svg-icon path',
				'attribute' => 'd',
			],
			$svgViewBoxKey => [
				'type' => 'string',
				'source' => 'attribute',
				'selector' => 'svg.ablocks-svg-icon',
				'attribute' => 'viewBox',
			],
			$svgClassKey => [
				'type' => 'string',
			],
		];

		if ( isset( $defaultValue['path'] ) && isset( $defaultValue['viewBox'] ) ) {
			$attribute[ $svgPathKey ]['default'] = $defaultValue['path'];
			$attribute[ $svgViewBoxKey ]['default'] = $defaultValue['viewBox'];
		}
		if ( isset( $defaultValue['className'] ) ) {
			$attribute[ $svgClassKey ]['default'] = $defaultValue['className'];
		}
		return $attribute;
	}
	public static function is_fse_theme() {
		return function_exists( 'wp_is_block_theme' ) && wp_is_block_theme();
	}

	public static function check_post_type_from_admin( $post_type ) {
		global $post;
		if ( is_admin() ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( $post && get_post_type( $post ) === $post_type ) {
				return true;
				// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			} elseif ( isset( $_GET['post_type'] ) && $_GET['post_type'] === $post_type ) {
				return true;
				// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			} elseif ( isset( $_GET['post'] ) ) {
				// phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$queried_post_type = get_post_type( sanitize_text_field(wp_unslash($_GET['post'])) );
				if ( $queried_post_type === $post_type ) {
					return true;
				}
			}
		}
		return false;
	}

	public static function get_block_attributes( $post_id, $block_id, $block_name ) {
		$post_content = get_post_field( 'post_content', $post_id );
		$blocks = parse_blocks( $post_content );

		foreach ( $blocks as $block ) {
			if ( $block['blockName'] === $block_name && $block['attrs']['block_id'] === $block_id ) {
				return [
					'parentAttributes' => $block['attrs'],
					'innerBlocks' => self::extract_inner_blocks( $block['innerBlocks'] ),
				];
			}
		}
		return [];
	}

	public static function extract_inner_blocks( $innerBlocks ) {
		return array_map(function ( $inner_block ) {
			$block_data = [
				'blockName' => $inner_block['blockName'],
				'attributes' => $inner_block['attrs'],
			];
			if ( ! empty( $inner_block['innerBlocks'] ) ) {
				$block_data['innerBlocks'] = self::extract_inner_blocks( $inner_block['innerBlocks'] );
			}
			return $block_data;
		}, $innerBlocks);
	}

	public static function generate_schema_using_form_data( $customFields ) {
		$schema = [];

		foreach ( $customFields as $inputField ) {
			// Check if 'name' exists in the input field
			if ( isset( $inputField['name'] ) ) {
				$field_name = $inputField['name'];
				$input_type = $inputField['inputType'] ?? 'text'; // Default to 'text' if not specified

				// Map the input types to schema types
				switch ( $input_type ) {
					case 'Text':
						$schema[ $field_name ] = 'string';
						break;
					case 'Email':
						$schema[ $field_name ] = 'email';
						break;
					case 'Password':
						$schema[ $field_name ] = 'string';
						break;
					case 'Number':
						$schema[ $field_name ] = 'number';
						break;
					case 'Url':
						$schema[ $field_name ] = 'url';
						break;
					case 'Boolean':
						$schema[ $field_name ] = 'boolean';
						break;
					case 'Textarea':
						$schema[ $field_name ] = 'textarea';
						break;
					default:
						$schema[ $field_name ] = 'string'; // Default to string for unknown types
						break;
				}//end switch
			}//end if
		}//end foreach

		return $schema;
	}

	public static function sorted_input_fields_by_input_type( $blockData ) {
		$sorted_custom_fields = [];
		foreach ( $blockData as $block ) {
			$name = $block['attributes']['name'] ?? null;
			$inputType = $block['attributes']['inputType'] ?? null;

			if ( $name && isset( $custom_fields[ $name ] ) ) {
				$sorted_custom_fields[] = [
					'name' => $name,
					'inputType' => $inputType,
					'value' => $custom_fields[ $name ]
				];
			}
		}

		return $sorted_custom_fields;
	}


	public static function render_svg_icon_using_attr( $attributes = array() ) {
		$default_attributes = array(
			'path' => '',
			'viewBox' => '0 0 24 24',
			'className' => 'icon-class',
			'width' => '24',
			'height' => '24',
		);

		// Merge passed attributes with default values
		$attributes = array_merge( $default_attributes, $attributes );

		// Sanitize attributes for safety
		$path = esc_attr( $attributes['path'] );
		$viewBox = esc_attr( $attributes['viewBox'] );
		$className = esc_attr( $attributes['className'] );
		$width = esc_attr( $attributes['width'] );
		$height = esc_attr( $attributes['height'] );

		// Output the SVG
		return '
		<svg 
			xmlns="http://www.w3.org/2000/svg" 
			viewBox="' . $viewBox . '" 
			class="' . $className . '" 
			width="' . $width . '" 
			height="' . $height . '">
			<path d="' . $path . '"></path>
		</svg>';
	}

	public static function get_template( $template_name, $args = array(), $template_path = '', $default_path = '' ) {
		$template = false;

		if ( ! $template ) {
			$template = self::locate_template( $template_name, $template_path, $default_path );
		}

		// Allow 3rd party plugin filter template file from their plugin.
		$filter_template = apply_filters( 'ablocks/get_template', $template, $template_name, $args, $template_path, $default_path );

		if ( $filter_template !== $template ) {
			if ( ! file_exists( $filter_template ) ) {
				/* translators: %s template */
				wc_doing_it_wrong( __FUNCTION__, sprintf( __( '%s does not exist.', 'ablocks' ), '<code>' . $filter_template . '</code>' ), '1.0.0' );

				return;
			}
			$template = $filter_template;
		}

		$action_args = array(
			'template_name' => $template_name,
			'template_path' => $template_path,
			'located'       => $template,
			'args'          => $args,
		);

		if ( ! empty( $args ) && is_array( $args ) ) {
			if ( isset( $args['action_args'] ) ) {
				wc_doing_it_wrong(
					__FUNCTION__,
					__( 'action_args should not be overwritten when calling ablocks/get_template.', 'ablocks' ),
					'1.0.0'
				);
				unset( $args['action_args'] );
			}
			extract( $args ); // @codingStandardsIgnoreLine
		}

		do_action( 'ablocks/before_template_part', $action_args['template_name'], $action_args['template_path'], $action_args['located'], $action_args['args'] );
		include $action_args['located'];

		do_action( 'ablocks/after_template_part', $action_args['template_name'], $action_args['template_path'], $action_args['located'], $action_args['args'] );
	}

	public static function locate_template( $template_name, $template_path = '', $default_path = '' ) {
		if ( ! $template_path ) {
			$template_path = self::template_path();
		}

		if ( ! $default_path ) {
			$default_path = self::plugin_path() . 'templates/';
		}

		if ( empty( $template ) ) {
			$template = locate_template(
				array(
					trailingslashit( $template_path ) . $template_name,
					$template_name,
				)
			);
		}
		if ( ! $template ) {
			$template = $default_path . $template_name;
		}

		// Return what we found.
		return apply_filters( 'ablocks/locate_template', $template, $template_name, $template_path );
	}

	public static function template_path() {
		return apply_filters( 'ablocks/template_path', 'ablocks/' );
	}
	public static function plugin_path() {
		return apply_filters( 'ablocks/plugin_path', ABLOCKS_ROOT_DIR_PATH );
	}

}
