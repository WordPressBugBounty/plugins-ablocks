<?php
namespace ABlocks;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ABlocks\Admin\Settings;

class Installer {

	public $ablocks_version;
	public static function init() {
		$self = new self();
		$self->ablocks_version = get_option( 'ablocks_version' );
		Database::create_initial_custom_table();
		$self->save_main_settings();
		// Save option table data
		$self->save_option();

	}
	public function save_main_settings() {
		Settings::save_settings();
	}
	public function save_option() {
		if ( ! $this->ablocks_version ) {
			add_option( 'ablocks_version', ABLOCKS_VERSION );
			add_option( 'ablocks_fonts', '{}' );
		}
		if ( ! get_option( 'ablocks_first_install_time' ) ) {
			add_option( 'ablocks_first_install_time', Helper::get_time(), '', false );
		}
		add_option( 'ablocks_need_activation_redirect', true );
	}
}
