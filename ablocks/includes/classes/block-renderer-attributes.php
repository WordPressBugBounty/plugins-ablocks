<?php
namespace ABlocks\Classes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Keeps aBlocks' server-side previews rendering when another plugin adds an
 * attribute to every block in the editor alone.
 *
 * A plugin can register an extra attribute on every block type from JavaScript,
 * through the `blocks.registerBlockType` filter, without registering it in PHP.
 * The editor then sends that attribute with every ServerSideRender request, and
 * WP_REST_Block_Renderer_Controller validates `attributes` against the
 * PHP-registered schema with `additionalProperties => false`. One key the
 * server has never heard of rejects the whole render with a 400
 * `rest_invalid_param`, and every dynamic aBlocks block shows "Error loading
 * block" for as long as that plugin is active.
 *
 * aBlocks cannot register attributes it does not know about, so before the
 * request is validated it drops, from its own blocks' render requests, every
 * attribute the server has not registered. The server would not use those keys
 * anyway. Attributes a plugin does mirror on the server (see FlexItem) stay.
 */
class BlockRendererAttributes {

	const ROUTE = '/wp/v2/block-renderer/';

	public static function init() {
		// `rest_pre_dispatch` runs before WP_REST_Server validates the request's
		// parameters, which is where the unknown key would fail it.
		add_filter( 'rest_pre_dispatch', [ __CLASS__, 'drop_unregistered_attributes' ], 10, 3 );
	}

	/**
	 * @param mixed            $result  A response to short-circuit with; passed through untouched.
	 * @param \WP_REST_Server  $server  Server instance.
	 * @param \WP_REST_Request $request The request about to be dispatched.
	 * @return mixed
	 */
	public static function drop_unregistered_attributes( $result, $server, $request ) {
		$route = $request->get_route();

		if ( 0 !== strpos( $route, self::ROUTE . 'ablocks/' ) ) {
			return $result;
		}

		$attributes = $request->get_param( 'attributes' );
		if ( ! is_array( $attributes ) ) {
			return $result;
		}

		$block_type = \WP_Block_Type_Registry::get_instance()->get_registered( substr( $route, strlen( self::ROUTE ) ) );
		if ( ! $block_type ) {
			return $result;
		}

		$registered = array_intersect_key( $attributes, $block_type->get_attributes() );
		if ( count( $registered ) !== count( $attributes ) ) {
			$request->set_param( 'attributes', $registered );
		}

		return $result;
	}
}
