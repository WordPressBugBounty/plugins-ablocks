<?php
namespace ABlocks\Performance;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ABlocks\Helper;

/**
 * Performance Suite — image loading optimizations for aBlocks blocks.
 *
 * Adds `loading="lazy"` + `decoding="async"` to images, while keeping the first
 * N images eager with `fetchpriority="high"` so the LCP image isn't deferred.
 *
 * Opt-in via `perf_lazy_images`. Operates on rendered block HTML (scoped to
 * aBlocks blocks) so no per-block markup changes are needed.
 */
class ImageOptimizer {

	private $image_index = 0;
	private $eager_count = 1;

	public static function init() {
		if ( is_admin() ) {
			return;
		}
		$enabled = (bool) apply_filters(
			'ablocks/perf/perf_lazy_images',
			(bool) Helper::get_settings( 'perf_lazy_images', true )
		);
		if ( ! $enabled ) {
			return;
		}
		$self = new self();
		$self->eager_count = (int) apply_filters(
			'ablocks/perf/lcp_eager_count',
			(int) Helper::get_settings( 'perf_lcp_eager_count', 1 )
		);
		add_filter( 'render_block', [ $self, 'process' ], 20, 2 );
	}

	public function process( $content, $block ) {
		if ( empty( $block['blockName'] ) || false === strpos( $block['blockName'], 'ablocks' ) ) {
			return $content;
		}
		if ( false === strpos( $content, '<img' ) ) {
			return $content;
		}

		return preg_replace_callback(
			'/<img\b(?![^>]*\bloading=)[^>]*>/i',
			[ $this, 'rewrite_img' ],
			$content
		);
	}

	private function rewrite_img( $matches ) {
		$tag = $matches[0];
		$this->image_index++;

		if ( $this->image_index <= $this->eager_count ) {
			// Likely-LCP images: load eagerly with high priority.
			$attrs = ' loading="eager" fetchpriority="high"';
		} else {
			$attrs = ' loading="lazy"';
		}

		if ( false === stripos( $tag, 'decoding=' ) ) {
			$attrs .= ' decoding="async"';
		}

		return preg_replace( '/^<img\b/', '<img' . $attrs, $tag, 1 );
	}
}
