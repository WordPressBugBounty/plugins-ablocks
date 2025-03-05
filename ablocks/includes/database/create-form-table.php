<?php
namespace ABlocks\Database;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CreateFormTable {
	public static function up( $prefix, $charset_collate ) {
		$table_entries = $prefix . ABLOCKS_PLUGIN_SLUG . '_form_entries';
		$table_meta = $prefix . ABLOCKS_PLUGIN_SLUG . '_form_meta';

		$sql_entries = "CREATE TABLE $table_entries (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            form_type VARCHAR(50) NOT NULL,
            user_name VARCHAR(100) NULL,
            user_email VARCHAR(100) NOT NULL,
            status VARCHAR(50) NOT NULL,
            parent BIGINT(20) UNSIGNED DEFAULT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            FOREIGN KEY (parent) REFERENCES $table_entries(id) ON DELETE CASCADE
        ) $charset_collate;";

		// SQL to create the custom_form_meta table
		$sql_meta = "CREATE TABLE $table_meta (
            meta_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            entry_id BIGINT(20) UNSIGNED NOT NULL,
            meta_key VARCHAR(255) NOT NULL,
            meta_value TEXT NOT NULL,
            PRIMARY KEY (meta_id),
            FOREIGN KEY (entry_id) REFERENCES $table_entries(id) ON DELETE CASCADE
        ) $charset_collate;";

		dbDelta( $sql_entries );
		dbDelta( $sql_meta );
	}
}
