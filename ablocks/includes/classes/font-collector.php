<?php
namespace ABlocks\Classes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ABlocks\Helper;

/**
 * Determines which fonts a page actually uses by reading saved block
 * attributes (deterministic, design-time truth) instead of discovering them
 * by scanning generated CSS at render time.
 *
 * - Per-post fonts are stored in post meta and refreshed on save.
 * - Global typography fonts are stored in an option and refreshed when the
 *   plugin's global settings are saved.
 * - Self-hosting (local download) is the default loading model.
 *
 * See docs/FONT-MANAGEMENT-PLAN.md.
 */
class FontCollector {

	const POST_META_KEY      = '_ablocks_fonts';
	const GLOBAL_OPTION_NAME = 'ablocks_global_fonts';

	/**
	 * Recursively collect [ family => [weights] ] from a list of parsed blocks.
	 *
	 * @param array $blocks Parsed blocks (from parse_blocks()).
	 * @param array $fonts  Accumulator.
	 * @return array
	 */
	public static function collect_from_blocks( $blocks, &$fonts = [] ) {
		if ( ! is_array( $blocks ) ) {
			return $fonts;
		}

		foreach ( $blocks as $block ) {
			if ( empty( $block['blockName'] ) ) {
				continue;
			}

			// Reusable blocks / synced patterns referenced by id.
			if ( 'core/block' === $block['blockName'] && ! empty( $block['attrs']['ref'] ) ) {
				$ref_post = get_post( (int) $block['attrs']['ref'] );
				if ( $ref_post instanceof \WP_Post ) {
					self::collect_from_blocks( parse_blocks( $ref_post->post_content ), $fonts );
				}
			}

			if ( ! empty( $block['attrs'] ) ) {
				self::collect_from_attributes( $block['attrs'], $fonts );
			}

			if ( ! empty( $block['innerBlocks'] ) ) {
				self::collect_from_blocks( $block['innerBlocks'], $fonts );
			}
		}

		return $fonts;
	}

	/**
	 * Walk an attribute tree and record any typography object that carries a
	 * non-empty fontFamily. Attribute-shape driven, so it works for every block
	 * without a per-block schema (typography attrs are objects with fontFamily).
	 *
	 * @param mixed $node  Attributes (array) or sub-node.
	 * @param array $fonts Accumulator.
	 */
	protected static function collect_from_attributes( $node, &$fonts ) {
		if ( ! is_array( $node ) ) {
			return;
		}

		if ( isset( $node['fontFamily'] ) && is_string( $node['fontFamily'] ) ) {
			$family = trim( $node['fontFamily'] );
			if ( '' !== $family && 'Default' !== $family ) {
				$weight = '400';
				if ( isset( $node['weight'] ) && '' !== $node['weight'] && 'Default' !== $node['weight'] ) {
					$weight = (string) $node['weight'];
				}
				self::add( $fonts, $family, $weight );
			}
		}

		foreach ( $node as $value ) {
			if ( is_array( $value ) ) {
				self::collect_from_attributes( $value, $fonts );
			}
		}
	}

	/**
	 * Add a family/weight pair to the accumulator without duplicates.
	 */
	protected static function add( &$fonts, $family, $weight ) {
		if ( ! isset( $fonts[ $family ] ) ) {
			$fonts[ $family ] = [];
		}
		if ( ! in_array( $weight, $fonts[ $family ], true ) ) {
			$fonts[ $family ][] = $weight;
		}
	}

	/**
	 * Union two [family => weights] maps.
	 */
	public static function merge( $a, $b ) {
		$a = is_array( $a ) ? $a : [];
		$b = is_array( $b ) ? $b : [];
		foreach ( $b as $family => $weights ) {
			foreach ( (array) $weights as $weight ) {
				self::add( $a, $family, (string) $weight );
			}
		}
		return $a;
	}

	/* -------------------------------------------------------------------------
	 * Per-post
	 * ---------------------------------------------------------------------- */

	/**
	 * Collect fonts directly from a post's saved content.
	 */
	public static function collect_from_post( $post_id ) {
		$post = get_post( $post_id );
		if ( ! $post instanceof \WP_Post ) {
			return [];
		}
		return self::collect_from_blocks( parse_blocks( $post->post_content ) );
	}

	/**
	 * Recompute and store a post's fonts, then self-host them. Called on save.
	 */
	public static function save_post_fonts( $post_id ) {
		$fonts = self::collect_from_post( $post_id );
		update_post_meta( $post_id, self::POST_META_KEY, $fonts );
		self::download( $fonts );
		return $fonts;
	}

	/**
	 * Get a post's fonts, computing + caching on first access (lazy backfill for
	 * content saved before this system existed).
	 */
	public static function get_post_fonts( $post_id ) {
		$fonts = get_post_meta( $post_id, self::POST_META_KEY, true );
		if ( ! is_array( $fonts ) ) {
			$fonts = self::save_post_fonts( $post_id );
		}
		return is_array( $fonts ) ? $fonts : [];
	}

	/* -------------------------------------------------------------------------
	 * Global typography
	 * ---------------------------------------------------------------------- */

	/**
	 * Collect global typography fonts from the plugin's global settings.
	 */
	public static function collect_from_globals() {
		$fonts = [];
		$keys  = [
			'global_typography',
			'global_body_typography',
			'global_link_typography',
			'global_link_hover_typography',
			'global_h1_typography',
			'global_h2_typography',
			'global_h3_typography',
			'global_h4_typography',
			'global_h5_typography',
			'global_h6_typography',
		];
		foreach ( $keys as $key ) {
			$setting = Helper::get_settings( $key, [] );
			// Normalise stdClass → array so the recursive walk works uniformly.
			$normalised = json_decode( wp_json_encode( $setting ), true );
			self::collect_from_attributes( $normalised, $fonts );
		}
		return $fonts;
	}

	/**
	 * Recompute and store global fonts, then self-host. Called on settings save.
	 */
	public static function update_global_fonts() {
		$fonts = self::collect_from_globals();
		update_option( self::GLOBAL_OPTION_NAME, $fonts );
		self::download( $fonts );
		return $fonts;
	}

	/**
	 * Get global fonts, computing + caching on first access.
	 */
	public static function get_global_fonts() {
		$fonts = get_option( self::GLOBAL_OPTION_NAME, null );
		if ( ! is_array( $fonts ) ) {
			$fonts = self::update_global_fonts();
		}
		return is_array( $fonts ) ? $fonts : [];
	}

	/* -------------------------------------------------------------------------
	 * Frontend
	 * ---------------------------------------------------------------------- */

	/**
	 * The complete set of fonts the current request needs: global typography
	 * (every page) plus the current singular post's fonts. Cached per request.
	 */
	public static function get_page_fonts() {
		static $cache = null;
		if ( null !== $cache ) {
			return $cache;
		}

		$fonts = self::get_global_fonts();

		if ( is_singular() ) {
			$post_id = get_queried_object_id();
			if ( $post_id ) {
				$fonts = self::merge( $fonts, self::get_post_fonts( $post_id ) );
			}
		}

		$cache = $fonts;
		return $cache;
	}

	/* -------------------------------------------------------------------------
	 * Self-hosting
	 * ---------------------------------------------------------------------- */

	/**
	 * Download any missing font files locally (self-host is the default model).
	 */
	protected static function download( $fonts ) {
		if ( empty( $fonts ) ) {
			return;
		}
		( new FontLoadLocally() )->process_font_queue( $fonts );
	}
}
