<?php
namespace ABlocks\Classes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class RegisterScripts {
	public static function get_register_styles() {
		return apply_filters('ablocks/register_styles', [
			'ablocks-leaflet-style' => [
				'path'  => ABLOCKS_ASSETS_PATH . 'library/leaflet/leaflet.css',
				'url'   => ABLOCKS_ASSETS_URL . 'library/leaflet/leaflet.css',
				'deps'  => array(),
				'ver'   => false,
				'media' => 'all'
			],
			'ablocks-leaflet-full-screen-style' => [
				'path'  => ABLOCKS_ASSETS_PATH . 'library/leaflet/leaflet.fullscreen.css',
				'url'   => ABLOCKS_ASSETS_URL . 'library/leaflet/leaflet.fullscreen.css',
				'deps'  => array(),
				'ver'   => false,
				'media' => 'all'
			],
			'ablocks-animate-style' => [
				'path'  => ABLOCKS_ASSETS_PATH . 'library/animate/animate.min.css',
				'url'   => ABLOCKS_ASSETS_URL . 'library/animate/animate.min.css',
				'deps'  => array(),
				'ver'   => false,
				'media' => 'all'
			],
			'ablocks-swiper-style' => [
				'path'  => ABLOCKS_ASSETS_PATH . 'library/swiper/swiper-bundle.min.css',
				'url'   => ABLOCKS_ASSETS_URL . 'library/swiper/swiper-bundle.min.css',
				'deps'  => array(),
				'ver'   => false,
				'media' => 'all'
			],
			'ablocks-common-style' => [
				'path'  => ABLOCKS_ASSETS_PATH . 'build/blocks-common.css',
				'url'   => ABLOCKS_ASSETS_URL . 'build/blocks-common.css',
				'deps'  => array(),
				'ver'   => false,
				'media' => 'all'
			],
		]);
	}
	public static function get_register_scripts() {
		return apply_filters('ablocks/register_scripts', [
			'ablocks-leaflet-script' => [
				'path'          => ABLOCKS_ASSETS_PATH . 'library/leaflet/leaflet.js',
				'url'           => ABLOCKS_ASSETS_URL . 'library/leaflet/leaflet.js',
				'deps'          => array(),
				'ver'           => false,
				'args'          => true,
			],
			'ablocks-leaflet-full-screen-script' => [
				'path'          => ABLOCKS_ASSETS_PATH . 'library/leaflet/Leaflet.fullscreen.js',
				'url'           => ABLOCKS_ASSETS_URL . 'library/leaflet/Leaflet.fullscreen.js',
				'deps'          => array(),
				'ver'           => false,
				'args'          => true,
			],
			'ablocks-swiper-script' => [
				'path'          => ABLOCKS_ASSETS_PATH . 'library/swiper/swiper-bundle.min.js',
				'url'           => ABLOCKS_ASSETS_URL . 'library/swiper/swiper-bundle.min.js',
				'deps'          => array(),
				'ver'           => false,
				'args'          => true,
			],
			'ablocks-common-script' => [
				'path'          => ABLOCKS_ASSETS_PATH . 'build/blocks-common.js',
				'url'           => ABLOCKS_ASSETS_URL . 'build/blocks-common.js',
				'deps'          => array(),
				'ver'           => false,
				'args'          => true,
				'dependencies'  => ABLOCKS_ASSETS_PATH . 'build/blocks-common.asset.php'
			],
		]);
	}
}
