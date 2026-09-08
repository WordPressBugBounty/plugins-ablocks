<?php
namespace ABlocksCookieConsent;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The consent record.
 *
 * GDPR Art. 7(1) puts the burden of proof on the controller: they must be able
 * to demonstrate that consent was given. That means keeping a record — and a
 * record of consent is itself personal-data processing, which argues for
 * keeping as little as will do the job.
 *
 * So: an opaque id the visitor already carries in their own cookie, when they
 * decided, which policy version and which wording they were shown, and what
 * they granted. No IP address unless the site owner switches it on and accepts
 * that it needs its own lawful basis.
 */
class Record {

	public static function init() {
		$self = new self();
		add_filter( 'wp_privacy_personal_data_exporters', [ $self, 'register_exporter' ] );
		add_filter( 'wp_privacy_personal_data_erasers', [ $self, 'register_eraser' ] );
		add_action( 'ablocks_cookie_consent_prune', [ __CLASS__, 'prune' ] );

		if ( ! wp_next_scheduled( 'ablocks_cookie_consent_prune' ) ) {
			wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', 'ablocks_cookie_consent_prune' );
		}
	}

	/**
	 * Store one decision.
	 *
	 * @param array $decision consent_id, categories, decision, page_url.
	 * @return int|false Insert id.
	 */
	public static function insert( array $decision ) {
		// The audit log is Pro. Free still has a record of the decision — the
		// visitor's own cookie — it just is not kept server-side.
		if ( ! Helper::can( 'records' ) ) {
			return false;
		}
		if ( ! Helper::get( 'record_enabled', true ) || ! Database::table_exists() ) {
			return false;
		}

		global $wpdb;

		$categories = isset( $decision['categories'] ) ? (array) $decision['categories'] : [];
		$categories = array_values( array_intersect( $categories, wp_list_pluck( Helper::active_categories(), 'slug' ) ) );

		$row = [
			'consent_id'     => substr( (string) $decision['consent_id'], 0, 64 ),
			'user_id'        => get_current_user_id() ? get_current_user_id() : null,
			'policy_version' => (int) Helper::get( 'policy_version', 1 ),
			'categories'     => implode( ',', $categories ),
			'decision'       => substr( (string) ( isset( $decision['decision'] ) ? $decision['decision'] : 'save' ), 0, 20 ),
			'banner_hash'    => Helper::banner_hash(),
			'page_url'       => isset( $decision['page_url'] ) ? substr( (string) $decision['page_url'], 0, 190 ) : '',
			'created_at'     => current_time( 'mysql', true ),
		];

		if ( Helper::get( 'record_ip', false ) ) {
			$row['ip']         = self::client_ip();
			$row['user_agent'] = self::user_agent();
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$inserted = $wpdb->insert( Database::table_name(), $row );

		return $inserted ? (int) $wpdb->insert_id : false;
	}

	/**
	 * @return int Total stored records.
	 */
	public static function count() {
		if ( ! Database::table_exists() ) {
			return 0;
		}
		global $wpdb;
		$table = Database::table_name();
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" );
	}

	/**
	 * @param int $limit  Rows to return.
	 * @param int $offset Rows to skip.
	 * @return array
	 */
	public static function recent( $limit = 20, $offset = 0 ) {
		if ( ! Database::table_exists() ) {
			return [];
		}
		global $wpdb;
		$table = Database::table_name();
		// The table name comes from $wpdb->prefix, which prepare() cannot
		// placeholder; the two bound values are placeholdered.
		$sql = $wpdb->prepare( "SELECT * FROM {$table} ORDER BY id DESC LIMIT %d OFFSET %d", $limit, $offset ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		return (array) $wpdb->get_results( $sql, ARRAY_A );
	}

	public static function delete_all() {
		if ( ! Database::table_exists() ) {
			return 0;
		}
		global $wpdb;
		$table = Database::table_name();
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return (int) $wpdb->query( "TRUNCATE TABLE {$table}" );
	}

	/**
	 * Drop records older than the retention window.
	 *
	 * A record kept forever is a record kept without a purpose, which is the
	 * thing storage limitation exists to prevent.
	 */
	public static function prune() {
		$days = (int) Helper::get( 'record_retention_days', 730 );
		if ( $days < 1 || ! Database::table_exists() ) {
			return;
		}
		global $wpdb;
		$table  = Database::table_name();
		$cutoff = gmdate( 'Y-m-d H:i:s', time() - ( $days * DAY_IN_SECONDS ) );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$table} WHERE created_at < %s", $cutoff ) );
	}

	/**
	 * WordPress' privacy tools work from an email address, so they can only
	 * reach records left by a logged-in visitor. That is not a shortcoming to
	 * paper over: an anonymous record deliberately holds nothing that ties it
	 * to a person, and the visitor's own copy of the id lives in their cookie.
	 *
	 * @param array $exporters Registered exporters.
	 * @return array
	 */
	public function register_exporter( $exporters ) {
		$exporters['ablocks-cookie-consent'] = [
			'exporter_friendly_name' => __( 'aBlocks Cookie Consent', 'ablocks' ),
			'callback'               => [ $this, 'export' ],
		];
		return $exporters;
	}

	/**
	 * @param string $email Email address being exported.
	 * @param int    $page  Page number.
	 * @return array
	 */
	public function export( $email, $page = 1 ) {
		$user = get_user_by( 'email', $email );
		if ( ! $user || ! Database::table_exists() ) {
			return [
				'data' => [],
				'done' => true,
			];
		}

		global $wpdb;
		$table = Database::table_name();
		$page  = max( 1, (int) $page );
		$sql = $wpdb->prepare( "SELECT * FROM {$table} WHERE user_id = %d ORDER BY id DESC LIMIT 100 OFFSET %d", $user->ID, ( $page - 1 ) * 100 ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		$rows = (array) $wpdb->get_results( $sql, ARRAY_A );

		$export = [];
		foreach ( $rows as $row ) {
			$data = [
				[
					'name'  => __( 'Recorded', 'ablocks' ),
					'value' => $row['created_at'],
				],
				[
					'name'  => __( 'Categories allowed', 'ablocks' ),
					'value' => $row['categories'] ? $row['categories'] : __( 'None', 'ablocks' ),
				],
				[
					'name'  => __( 'Decision', 'ablocks' ),
					'value' => $row['decision'],
				],
				[
					'name'  => __( 'Policy version', 'ablocks' ),
					'value' => $row['policy_version'],
				],
			];
			if ( ! empty( $row['ip'] ) ) {
				$data[] = [
					'name'  => __( 'IP address', 'ablocks' ),
					'value' => $row['ip'],
				];
			}

			$export[] = [
				'group_id'    => 'ablocks-cookie-consent',
				'group_label' => __( 'Cookie consent', 'ablocks' ),
				'item_id'     => 'ablocks-consent-' . $row['id'],
				'data'        => $data,
			];
		}//end foreach

		return [
			'data' => $export,
			'done' => count( $rows ) < 100,
		];
	}

	/**
	 * @param array $erasers Registered erasers.
	 * @return array
	 */
	public function register_eraser( $erasers ) {
		$erasers['ablocks-cookie-consent'] = [
			'eraser_friendly_name' => __( 'aBlocks Cookie Consent', 'ablocks' ),
			'callback'             => [ $this, 'erase' ],
		];
		return $erasers;
	}

	/**
	 * @param string $email Email address being erased.
	 * @param int    $page  Page number.
	 * @return array
	 */
	public function erase( $email, $page = 1 ) {
		$response = [
			'items_removed'  => false,
			'items_retained' => false,
			'messages'       => [],
			'done'           => true,
		];

		$user = get_user_by( 'email', $email );
		if ( ! $user || ! Database::table_exists() ) {
			return $response;
		}

		global $wpdb;
		$table = Database::table_name();
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$removed = (int) $wpdb->query( $wpdb->prepare( "DELETE FROM {$table} WHERE user_id = %d", $user->ID ) );

		$response['items_removed'] = $removed > 0;
		return $response;
	}

	/**
	 * The address stored against a record, when the site has opted into storing
	 * one at all.
	 *
	 * Reads the same filter the rate limiter does, so a site behind a proxy
	 * that has told the addon where to find the real address gets it in both
	 * places rather than a throttle keyed one way and an audit row written
	 * another.
	 *
	 * @return string
	 */
	private static function client_ip() {
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
		$ip = (string) apply_filters( 'ablocks/cookie_consent/client_ip', $ip );

		$valid = filter_var( $ip, FILTER_VALIDATE_IP );

		return $valid ? $valid : '';
	}

	private static function user_agent() {
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		$agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';
		return substr( $agent, 0, 190 );
	}
}
