<?php
namespace ABlocksCookieConsent;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin-side plumbing for the settings screen.
 *
 * The dashboard app already speaks admin-ajax with a shared nonce, so this
 * follows that rather than introducing a second convention for one screen.
 */
class Ajax {

	public static function init() {
		$self = new self();
		add_action( 'wp_ajax_ablocks/cookie_consent/get_settings', [ $self, 'get_settings' ] );
		add_action( 'wp_ajax_ablocks/cookie_consent/save_settings', [ $self, 'save_settings' ] );
		add_action( 'wp_ajax_ablocks/cookie_consent/get_report', [ $self, 'get_report' ] );
		add_action( 'wp_ajax_ablocks/cookie_consent/clear_report', [ $self, 'clear_report' ] );
		add_action( 'wp_ajax_ablocks/cookie_consent/get_records', [ $self, 'get_records' ] );
		add_action( 'wp_ajax_ablocks/cookie_consent/delete_records', [ $self, 'delete_records' ] );
	}

	/**
	 * Every handler calls this first.
	 *
	 * The nonce sniff cannot see through a helper, so the `$_POST` reads below
	 * are flagged despite always running after `check_ajax_referer`.
	 *
	 * phpcs:disable WordPress.Security.NonceVerification.Missing
	 */
	private function guard() {
		check_ajax_referer( 'ablocks_nonce', 'security' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'You are not allowed to do that.', 'ablocks' ), 403 );
		}
	}

	public function get_settings() {
		$this->guard();
		wp_send_json_success( Helper::get_settings() );
	}

	public function save_settings() {
		$this->guard();

		$defaults = Helper::defaults();
		$saved    = [];

		foreach ( [ 'enabled', 'buffer_gating', 'dry_run', 'consent_mode', 'record_enabled', 'record_ip', 'hide_for_admins' ] as $key ) {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			$saved[ $key ] = isset( $_POST[ $key ] ) ? \ABlocks\Helper::sanitize_checkbox_field( $_POST[ $key ] ) : $defaults[ $key ];
		}

		foreach ( [ 'policy_version', 'cookie_days', 'reconsent_days', 'consent_mode_wait', 'record_retention_days' ] as $key ) {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			$saved[ $key ] = isset( $_POST[ $key ] ) ? absint( $_POST[ $key ] ) : $defaults[ $key ];
		}

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		$mode          = isset( $_POST['mode'] ) ? sanitize_key( $_POST['mode'] ) : $defaults['mode'];
		$saved['mode'] = in_array( $mode, [ 'optin', 'notice' ], true ) ? $mode : 'optin';

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		// Measurement IDs. Kept to the characters Google and Meta issue, so a
		// pasted snippet or a stray quote cannot reach the head; the printer
		// checks the full shape again before writing anything.
		foreach ( [ 'tag_gtm', 'tag_ga4', 'tag_meta_pixel' ] as $key ) {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			$value         = isset( $_POST[ $key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) : '';
			$saved[ $key ] = preg_replace( '/[^A-Za-z0-9\-]/', '', $value );
		}

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		$mode                      = isset( $_POST['cookie_name_mode'] ) ? sanitize_key( $_POST['cookie_name_mode'] ) : '';
		$saved['cookie_name_mode'] = 'custom' === $mode ? 'custom' : 'default';

		// On 'default' the field is not even shown, so whatever the browser
		// last sent for it is ignored rather than trusted.
		if ( 'custom' === $saved['cookie_name_mode'] ) {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			$cookie_name          = isset( $_POST['cookie_name'] ) ? sanitize_text_field( wp_unslash( $_POST['cookie_name'] ) ) : '';
			$cookie_name          = preg_replace( '/[^A-Za-z0-9_\-]/', '', $cookie_name );
			$saved['cookie_name'] = $cookie_name ? $cookie_name : $defaults['cookie_name'];
		} else {
			$saved['cookie_name'] = $defaults['cookie_name'];
		}

		// Renaming the cookie would otherwise make every decision already given
		// unreadable — the banner would come back for the whole audience and
		// the old cookie would sit in their browser until its own expiry.
		// Remembering the previous name lets the client carry the decision
		// across once. One hop only: rename twice before visitors return and
		// the older one is genuinely gone.
		$current                       = Helper::get( 'cookie_name', $defaults['cookie_name'] );
		$saved['cookie_name_previous'] = $saved['cookie_name'] === $current
			? Helper::get( 'cookie_name_previous', '' )
			: $current;

		$saved['categories'] = $this->sanitize_categories( $this->post_json( 'categories' ), $defaults['categories'] );
		$saved['rules']      = $this->sanitize_rules( $this->post_json( 'rules' ) );
		$saved['banner']     = $this->sanitize_banner( $this->post_json( 'banner' ), $defaults['banner'] );

		Helper::save_settings( $saved );

		// A table that was never created — the addon enabled before this
		// version shipped, say — is created on first save rather than leaving
		// recording silently broken.
		if ( $saved['record_enabled'] && Helper::can( 'records' ) && ! Database::table_exists() ) {
			Database::create_table();
		}

		wp_send_json_success( Helper::get_settings() );
	}

	public function get_report() {
		$this->guard();
		wp_send_json_success(
			[
				'entries' => Report::get(),
				'dry_run' => (bool) Helper::get( 'dry_run', false ),
			]
		);
	}

	public function clear_report() {
		$this->guard();
		Report::clear();
		wp_send_json_success( [ 'entries' => [] ] );
	}

	public function get_records() {
		$this->guard();
		if ( ! Helper::can( 'records' ) ) {
			wp_send_json_success(
				[
					'records'  => [],
					'total'    => 0,
					'per_page' => 20,
					'locked'   => true,
				]
			);
		}
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		$page = isset( $_POST['page'] ) ? max( 1, absint( $_POST['page'] ) ) : 1;
		$per  = 20;

		wp_send_json_success(
			[
				'records' => Record::recent( $per, ( $page - 1 ) * $per ),
				'total'   => Record::count(),
				'per_page' => $per,
			]
		);
	}

	public function delete_records() {
		$this->guard();
		if ( ! Helper::can( 'records' ) ) {
			wp_send_json_error( __( 'Consent records are a Pro feature.', 'ablocks' ), 403 );
		}
		Record::delete_all();
		wp_send_json_success( [ 'total' => 0 ] );
	}

	/**
	 * Read one POST field that the dashboard sent as JSON.
	 *
	 * @param string $key Field name.
	 * @return array
	 */
	private function post_json( $key ) {
		if ( ! isset( $_POST[ $key ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- guard() ran first.
			return [];
		}
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.NonceVerification.Missing
		$decoded = json_decode( wp_unslash( $_POST[ $key ] ), true );
		return is_array( $decoded ) ? $decoded : [];
	}

	// phpcs:enable WordPress.Security.NonceVerification.Missing

	/**
	 * @param array $input    Submitted categories.
	 * @param array $defaults Shipped categories.
	 * @return array
	 */
	private function sanitize_categories( $input, $defaults ) {
		if ( empty( $input ) ) {
			return $defaults;
		}

		$locked_slugs = wp_list_pluck( array_filter( $defaults, function ( $c ) {
			return ! empty( $c['locked'] );
		} ), 'slug' );

		$clean = [];
		foreach ( $input as $category ) {
			$slug = isset( $category['slug'] ) ? sanitize_key( $category['slug'] ) : '';
			if ( '' === $slug ) {
				continue;
			}
			$is_locked = in_array( $slug, $locked_slugs, true );
			$clean[]   = [
				'slug'        => $slug,
				'label'       => isset( $category['label'] ) ? sanitize_text_field( $category['label'] ) : $slug,
				'description' => isset( $category['description'] ) ? sanitize_textarea_field( $category['description'] ) : '',
				// The locked flag is not the submitter's to set: a category
				// that cannot be refused is a decision about the site, not a
				// field on a form.
				'locked'      => $is_locked,
				'enabled'     => $is_locked ? true : ! empty( $category['enabled'] ),
				'cookies'     => $this->sanitize_cookies( isset( $category['cookies'] ) ? $category['cookies'] : [] ),
			];
		}

		return $clean ? $clean : $defaults;
	}

	/**
	 * The per-category cookie disclosure table.
	 *
	 * Every field is plain text shown to a visitor, so all four go through the
	 * same sanitiser. A row with no name is dropped rather than kept as an
	 * empty line: the table is a disclosure, and a blank row discloses nothing
	 * while looking like it does.
	 *
	 * The cap is there because this list is printed on every page of the site.
	 *
	 * @param array $input Submitted cookie rows.
	 * @return array
	 */
	private function sanitize_cookies( $input ) {
		$clean = [];
		foreach ( (array) $input as $cookie ) {
			if ( ! is_array( $cookie ) ) {
				continue;
			}
			$name = isset( $cookie['name'] ) ? sanitize_text_field( $cookie['name'] ) : '';
			if ( '' === $name ) {
				continue;
			}
			$clean[] = [
				'name'     => $name,
				'provider' => isset( $cookie['provider'] ) ? sanitize_text_field( $cookie['provider'] ) : '',
				'duration' => isset( $cookie['duration'] ) ? sanitize_text_field( $cookie['duration'] ) : '',
				'purpose'  => isset( $cookie['purpose'] ) ? sanitize_text_field( $cookie['purpose'] ) : '',
			];
			if ( count( $clean ) >= 50 ) {
				break;
			}
		}
		return $clean;
	}

	/**
	 * @param array $input Submitted rules.
	 * @return array
	 */
	private function sanitize_rules( $input ) {
		$can_author = Helper::can( 'custom_rules' );
		$shipped    = Helper::shipped_rule_ids();
		$defaults   = [];
		foreach ( Helper::default_rules() as $rule ) {
			$defaults[ $rule['id'] ] = $rule;
		}

		$clean = [];
		foreach ( (array) $input as $rule ) {
			$id = isset( $rule['id'] ) ? sanitize_key( $rule['id'] ) : '';
			if ( '' === $id ) {
				$id = 'rule-' . substr( md5( wp_json_encode( $rule ) ), 0, 8 );
			}

			// Without Pro, only the shipped rules survive, and only their
			// on/off state and category are the submitter's to change. The
			// patterns come back from the defaults, so a crafted request
			// cannot author a matcher the UI does not offer.
			if ( ! $can_author ) {
				if ( ! in_array( $id, $shipped, true ) ) {
					continue;
				}
				$clean[] = array_merge(
					$defaults[ $id ],
					[
						'category' => isset( $rule['category'] ) ? sanitize_key( $rule['category'] ) : $defaults[ $id ]['category'],
						'enabled'  => ! empty( $rule['enabled'] ),
					]
				);
				continue;
			}

			$clean[] = [
				'id'       => $id,
				'label'    => isset( $rule['label'] ) ? sanitize_text_field( $rule['label'] ) : $id,
				'category' => isset( $rule['category'] ) ? sanitize_key( $rule['category'] ) : '',
				// Patterns are regular expressions, so they cannot be run
				// through a sanitiser that strips punctuation. They are only
				// ever used as the body of a preg_match, never echoed
				// unescaped, and a malformed one fails closed in Gating.
				'src'      => isset( $rule['src'] ) ? wp_strip_all_tags( (string) $rule['src'] ) : '',
				'inline'   => isset( $rule['inline'] ) ? wp_strip_all_tags( (string) $rule['inline'] ) : '',
				'handles'  => isset( $rule['handles'] ) ? sanitize_text_field( $rule['handles'] ) : '',
				'enabled'  => ! empty( $rule['enabled'] ),
			];
		}//end foreach
		return $clean;
	}

	/**
	 * @param array $input    Submitted banner settings.
	 * @param array $defaults Shipped banner settings.
	 * @return array
	 */
	private function sanitize_banner( $input, $defaults ) {
		$clean = [];

		foreach ( $defaults as $key => $default ) {
			if ( ! array_key_exists( $key, $input ) ) {
				$clean[ $key ] = $default;
				continue;
			}
			$value = $input[ $key ];

			if ( is_bool( $default ) ) {
				$clean[ $key ] = (bool) filter_var( $value, FILTER_VALIDATE_BOOLEAN );
			} elseif ( is_int( $default ) ) {
				$clean[ $key ] = absint( $value );
			} elseif ( in_array( $key, [ 'policy_url', 'cookie_policy_url' ], true ) ) {
				$clean[ $key ] = esc_url_raw( $value );
			} elseif ( in_array( $key, [ 'message', 'prefs_intro' ], true ) ) {
				$clean[ $key ] = wp_kses_post( $value );
			} else {
				$clean[ $key ] = sanitize_text_field( $value );
			}
		}

		$clean['layout']          = in_array( $clean['layout'], [ 'bar', 'box', 'popup' ], true ) ? $clean['layout'] : 'bar';
		$clean['position']        = in_array( $clean['position'], [ 'bottom', 'top', 'bottom-left', 'bottom-right', 'center' ], true ) ? $clean['position'] : 'bottom';
		$clean['reopen_position'] = in_array( $clean['reopen_position'], [ 'bottom-left', 'bottom-right' ], true ) ? $clean['reopen_position'] : 'bottom-left';
		$clean['prefs_layout']    = in_array( $clean['prefs_layout'], [ 'inline', 'modal' ], true ) ? $clean['prefs_layout'] : 'inline';
		$clean['settings_style']  = in_array( $clean['settings_style'], [ 'link', 'outline', 'solid' ], true ) ? $clean['settings_style'] : 'link';
		$clean['close_behaviour'] = in_array( $clean['close_behaviour'], [ 'dismiss', 'reject' ], true ) ? $clean['close_behaviour'] : 'dismiss';
		$clean['dismiss_days']    = min( 365, (int) $clean['dismiss_days'] );
		$clean['locked_style']    = in_array( $clean['locked_style'], [ 'text', 'badge' ], true ) ? $clean['locked_style'] : 'text';
		$clean['switch_style']    = in_array( $clean['switch_style'], [ 'switch', 'checkbox' ], true ) ? $clean['switch_style'] : 'switch';

		// A modal that is narrower than the banner it opens from is a modal
		// nobody asked for; a bar's 1180px is not a dialog width either.
		$clean['prefs_max_width'] = min( 1000, max( 320, (int) $clean['prefs_max_width'] ) );

		return $clean;
	}
}
