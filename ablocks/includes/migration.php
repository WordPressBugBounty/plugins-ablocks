<?php
namespace ABlocks;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ABlocks\Admin\Settings;

class Migration {

	public static function init() {
		$self = new self();
		$self->run_migration();
	}

	public function run_migration() {
		$ablocks_version = get_option( 'ablocks_version' );
		// Save Version Number, flash role management and save permalink
		if ( ABLOCKS_VERSION !== $ablocks_version ) {
			Settings::save_settings();
			update_option( 'ablocks_version', ABLOCKS_VERSION );
		}
	}
}
