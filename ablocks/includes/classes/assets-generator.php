<?php
namespace ABlocks\Classes;

use ABlocks\Classes\FileUpload;
use ABlocks\Classes\RegisterScripts;

class AssetsGenerator {
	public static function write_frontend_css_in_uploads_folder( $post_id ) {
		$post = get_post( $post_id );
		$post_type = get_post_type( $post_id );
		$parse_blocks_content = '';

		if ( empty( $post->post_content ) || $post_type === 'wp_template_part' || $post_type === 'wp_template' ) {
			$parse_blocks_content = parse_blocks( $post->post_content );
		} else {
			$parse_blocks_content = parse_blocks( $post->post_content );
		}

		$style_depends = [];
		$scripts_depends = [];
		$aBlocks = [];
		self::recursive_block_parser( $parse_blocks_content, $aBlocks, $style_depends, $scripts_depends );

		$register_styles = RegisterScripts::get_register_styles();
		$register_scripts = RegisterScripts::get_register_scripts();
		$library_css = '';
		$static_css = '';
		$dynamic_css = '';
		$static_js = '';
		$library_js = '';

		foreach ( $aBlocks as $aBlock ) {
			if ( isset( $aBlock['static_js'] ) ) {
				$static_js .= $aBlock['static_js'] . "\n";
			}
			if ( isset( $aBlock['dynamic_style'] ) ) {
				$dynamic_css .= $aBlock['dynamic_style'] . "\n";
			}

			if ( isset( $aBlock['static_style'] ) ) {
				$static_css .= self::minify_css( $aBlock['static_style'] ) . "\n";
			}
		}

		// css
		foreach ( $style_depends as $style_depend ) {
			if ( isset( $register_styles[ $style_depend ]['path'] ) ) {
				 // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
				$library_css .= file_get_contents( $register_styles[ $style_depend ]['path'] ) . "\n";
			}
		}

		// js
		foreach ( $scripts_depends as $script_depend ) {
			if ( isset( $register_scripts[ $script_depend ]['path'] ) ) {
				// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
				$library_js .= file_get_contents( $register_scripts[ $script_depend ]['path'] ) . "\n";
			}
		}

		$FileUpload = new FileUpload();
		$destination_folder = $FileUpload->get_upload_dir();
		self::copy_build_image_folder_to_uploads( $destination_folder );
		$FileUpload->create_file( $post_id . '.min.css', $library_css . $static_css . $dynamic_css );
		$FileUpload->create_file( $post_id . '.min.js', $library_js . $static_js );

		return $aBlocks;
	}

	public static function recursive_block_parser( $parse_content, &$aBlocks, &$style_depends, &$scripts_depends ) {
		if ( count( $parse_content ) > 0 ) {
			foreach ( $parse_content as $item ) {
				if ( ! empty( $item['blockName'] ) ) {
					// Handle reusable blocks or patterns using "ref"
					if ( $item['blockName'] === 'core/block' && ! empty( $item['attrs']['ref'] ) ) {
						$ref_post_id = $item['attrs']['ref'];
						$ref_post = get_post( $ref_post_id ); // Get the reusable block or pattern
						if ( $ref_post ) {
							$ref_content = parse_blocks( $ref_post->post_content ); // Parse the reusable block's content
							self::recursive_block_parser( $ref_content, $aBlocks, $style_depends, $scripts_depends ); // Recursively parse the referenced block/pattern
						}
					} elseif ( strpos( $item['blockName'], 'ablocks' ) !== false ) {
						$block_name_class = str_replace( ' ', '', ucwords( str_replace( '-', ' ', explode( '/', $item['blockName'] )[1] ) ) );
						$dynamic_class = '\\ABlocks\\Blocks\\' . $block_name_class . '\\Block';

						if ( class_exists( $dynamic_class ) ) {
							$instance = new $dynamic_class( true );
							$attributes = wp_parse_args( $item['attrs'], array_map( function( $attr ) {
								return isset( $attr['default'] ) ? $attr['default'] : '';
							}, $instance->get_attributes() ));

							// Capture static CSS/JS
							if ( ! isset( $aBlocks[ $item['blockName'] ] ) ) {
								$aBlocks[ $item['blockName'] ] = [
									'static_style' => $instance->get_static_css(),
									'static_js' => $instance->get_static_js(),
								];
							}

							// Capture library scripts
							$style_depends = array_unique( array_merge( $style_depends, $instance->get_style_depends() ) );
							$scripts_depends = array_unique( array_merge( $scripts_depends, $instance->get_script_depends() ) );

							// Capture dynamic CSS
							if ( isset( $item['attrs']['ref'] ) || isset( $item['attrs']['block_id'] ) ) {
								$block_id_or_ref = ! empty( $item['attrs']['block_id'] ) ? $item['attrs']['block_id'] : 'core_pattern_ref_' . $item['attrs']['ref'];
								$aBlocks[ $block_id_or_ref ] = [
									'block_name' => $item['blockName'],
									'dynamic_style' => $instance->build_css( $attributes ),
								];
							}
						}//end if
					}//end if

					// Check for inner blocks and recursively process them
					if ( is_array( $item['innerBlocks'] ) && count( $item['innerBlocks'] ) ) {
						self::recursive_block_parser( $item['innerBlocks'], $aBlocks, $style_depends, $scripts_depends );
					}
				}//end if
			}//end foreach
		}//end if
	}

	public static function minify_css( $css_string ) {
		// Remove comments (/* ... */) using regex
		$css_string = preg_replace( '/\/\*[\s\S]*?\*\//', '', $css_string );

		// Remove double spaces, tabs, and newlines
		$css_string = preg_replace( '/\s+/', ' ', $css_string );

		// Remove spaces around colons, semicolons, curly braces, and commas
		$css_string = preg_replace( '/\s?([:,;{}])\s?/', '$1', $css_string );

		return $css_string;
	}

	public static function copy_build_image_folder_to_uploads( $destination_folder ) {
		global $wp_filesystem;
		if ( empty( $wp_filesystem ) ) {
			require_once ABSPATH . '/wp-admin/includes/file.php';
			WP_Filesystem();
		}

		$source_folders = [
			ABLOCKS_ASSETS_PATH . 'build/images/',     // First folder
			ABLOCKS_ASSETS_PATH . 'library/leaflet/',     // Add more folders as needed
			// Add more source folders here
		];

		if ( ! file_exists( $destination_folder ) ) {
			wp_mkdir_p( $destination_folder );
		}

		// Loop through each source folder and copy its contents
		foreach ( $source_folders as $source_folder ) {
			if ( file_exists( $source_folder ) ) {
				self::recursive_copy( $source_folder, $destination_folder );
			}
		}
	}

	public static function recursive_copy( $source, $destination ) {
		global $wp_filesystem;
		if ( empty( $wp_filesystem ) ) {
			require_once ABSPATH . '/wp-admin/includes/file.php';
			WP_Filesystem();
		}

		$valid_extensions = [ 'jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp' ];

		// Ensure WP_Filesystem is initialized
		if ( ! is_object( $GLOBALS['wp_filesystem'] ) ) {
			request_filesystem_credentials( '', '', true );
		}
	
		// Initialize the WP_Filesystem object
		$wp_filesystem = $GLOBALS['wp_filesystem'];
	
		// Open the source directory
		$dir = opendir( $source );
	
		// Create the destination directory using WP_Filesystem
		if ( ! $wp_filesystem->is_dir( $destination ) ) {
			$wp_filesystem->mkdir( $destination );
		}
	
		// Loop through files and directories in the source
		while ( false !== ( $file = readdir( $dir ) ) ) {
			if ( ( $file !== '.' ) && ( $file !== '..' ) ) {
				$source_file_path = $source . '/' . $file;
				$destination_file_path = $destination . '/' . $file;
	
				// If it's a directory, recursively copy its contents
				if ( is_dir( $source_file_path ) ) {
					self::recursive_copy( $source_file_path, $destination_file_path );
				} else {
					// Get file extension
					$file_extension = pathinfo( $file, PATHINFO_EXTENSION );
	
					// Check if the file has a valid image extension
					if ( in_array( strtolower( $file_extension ), $valid_extensions, true ) ) {
						// Copy only image files
						$wp_filesystem->copy( $source_file_path, $destination_file_path );
					}
				}
			}
		}
	
		// Close the directory handle
		closedir( $dir );
	}
	
}
