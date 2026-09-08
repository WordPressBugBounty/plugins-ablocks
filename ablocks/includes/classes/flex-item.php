<?php
namespace ABlocks\Classes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Server-side half of `src/blocks/atomic-shared/flex-item-filter.js`.
 *
 * That filter registers one extra attribute — `ablocksFlexItem` — on every
 * non-atomic block, so anything dropped into an atomic Flex or Grid container
 * gets a "Flex item" panel. Registered in JS alone it leaves the two block
 * registries disagreeing about what a block's attributes are, and the editor
 * asks the server to render with the JS set: WP_REST_Block_Renderer_Controller
 * validates `attributes` against the PHP-registered schema with
 * `additionalProperties => false`, so one attribute the server has never heard
 * of fails validation and the render returns 400 `rest_invalid_param`. That
 * takes down every block rendered through ServerSideRender — aBlocks' own
 * dynamic blocks and third-party ones alike.
 *
 * Declaring it here rather than in BlockGlobal is deliberate: BlockGlobal feeds
 * the attributes of aBlocks' own blocks (through each block's attributes.php and
 * the block.json built from it), while the editor-side filter reaches every
 * block on the site. `register_block_type_args` is the one seam with the same
 * reach, and it needs no rebuild to take effect on blocks aBlocks does not own.
 */
class FlexItem {

	const ATTRIBUTE = 'ablocksFlexItem';

	/**
	 * Atomic blocks keep their flex-item properties in the styles model and
	 * render them through their own scoped CSS, so the editor filter skips
	 * them. Keep this list in step with SKIP in flex-item-filter.js.
	 */
	const SKIP = [
		'ablocks/atomic-div',
		'ablocks/atomic-flex',
		'ablocks/atomic-grid',
		'ablocks/atomic-image',
		'ablocks/atomic-svg',
		'ablocks/atomic-text',
	];

	public static function init() {
		// Registered from `plugins_loaded`, before anything registers a block
		// type on `init` — core's own blocks included.
		add_filter( 'register_block_type_args', [ __CLASS__, 'add_attribute' ], 10, 2 );
	}

	/**
	 * @param array  $args       Block type registration arguments.
	 * @param string $block_name Block name, e.g. `ablocks/dynamic-text`.
	 * @return array
	 */
	public static function add_attribute( $args, $block_name ) {
		if ( in_array( $block_name, self::SKIP, true ) ) {
			return $args;
		}

		if ( ! isset( $args['attributes'] ) || ! is_array( $args['attributes'] ) ) {
			$args['attributes'] = [];
		}

		// A block that declares it itself keeps its own definition.
		if ( ! isset( $args['attributes'][ self::ATTRIBUTE ] ) ) {
			$args['attributes'][ self::ATTRIBUTE ] = [
				'type'    => 'object',
				'default' => [],
			];
		}

		return $args;
	}
}
