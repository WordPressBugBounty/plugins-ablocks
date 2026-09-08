<?php
namespace ABlocksCookieConsent;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Database {

	public static function table_name() {
		global $wpdb;
		return $wpdb->prefix . ABLOCKS_PLUGIN_SLUG . '_consent_records';
	}

	/**
	 * Created when the addon is switched on rather than at plugin install, so
	 * a site that never enables consent never grows the table.
	 */
	public static function create_table() {
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		global $wpdb;

		$table           = self::table_name();
		$charset_collate = $wpdb->get_charset_collate();

		// `consent_id` is not unique: a visitor who changes their mind produces
		// a second row, and the history is the point — a record that only ever
		// shows the latest state cannot demonstrate what was agreed and when.
		$sql = "CREATE TABLE $table (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			consent_id VARCHAR(64) NOT NULL,
			user_id BIGINT(20) UNSIGNED NULL,
			policy_version INT(11) UNSIGNED NOT NULL DEFAULT 1,
			categories VARCHAR(255) NOT NULL DEFAULT '',
			decision VARCHAR(20) NOT NULL DEFAULT 'save',
			banner_hash VARCHAR(32) NOT NULL DEFAULT '',
			page_url VARCHAR(190) NULL,
			ip VARCHAR(100) NULL,
			user_agent VARCHAR(190) NULL,
			created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY consent_id (consent_id),
			KEY user_id (user_id),
			KEY created_at (created_at)
		) $charset_collate;";

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		if ( $wpdb->get_var( "SHOW TABLES LIKE '{$table}'" ) !== $table ) {
			dbDelta( $sql );
		}
	}

	public static function table_exists() {
		global $wpdb;
		$table = self::table_name();
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return $wpdb->get_var( "SHOW TABLES LIKE '{$table}'" ) === $table;
	}
}
