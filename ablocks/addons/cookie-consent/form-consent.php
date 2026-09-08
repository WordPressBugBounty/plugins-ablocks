<?php
namespace ABlocksCookieConsent;

use ABlocks\Blocks\FormBuilder\Query;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * What the visitor had consented to at the moment they became a lead.
 *
 * A consent record on its own says an anonymous browser agreed to something. A
 * form entry on its own says a named person got in touch. Neither answers the
 * question that actually gets asked later — "why are you emailing me?" — and
 * the answer is only defensible if the two are joined at the moment of
 * submission, because consent can be changed or withdrawn afterwards and the
 * record has to say what was true *then*.
 *
 * So each submission carries a copy: the categories that were granted, the
 * policy version the visitor was shown, when they decided, and the reference
 * that leads back to the row in the consent table.
 *
 * Note the one thing this is not: it is not marketing consent. Agreeing to
 * analytics cookies is not agreeing to be emailed — that needs its own,
 * separately ticked, box on the form itself. What is stored here is evidence
 * about cookies, and treating it as a mailing-list opt-in would be exactly the
 * bundling that makes both permissions invalid.
 */
class FormConsent {

	/**
	 * Meta keys written onto the form entry.
	 *
	 * No leading underscore: the submissions screen builds a label from the key
	 * for anything it does not recognise as a form field, and a leading
	 * underscore turns into a leading space in that label. The real labels come
	 * from `relabel()` below.
	 */
	const META = [
		'categories' => 'ablocks_consent_categories',
		'policy'     => 'ablocks_consent_policy',
		'given'      => 'ablocks_consent_given',
		'reference'  => 'ablocks_consent_reference',
	];

	public static function init() {
		$self = new self();

		// Both submission paths end here — the REST controller and the
		// admin-ajax handler — so this is registered outside the front-end-only
		// block that the banner and the gating live in.
		add_action( 'ablocks/form_builder/after_submission', [ $self, 'attach' ], 10, 3 );
		add_filter( 'ablocks/form_builder/meta_output', [ $self, 'relabel' ] );
	}

	/**
	 * The visitor's decision, read from their cookie.
	 *
	 * Reading the consent cookie in PHP is forbidden while rendering a page —
	 * a page cache would then serve one visitor's consent state to everyone
	 * after them. A form submission is not a cached render: it is a POST that
	 * belongs to one visitor and is never stored, so the cookie can be read
	 * here and nowhere else on the server.
	 *
	 * @return array|null `[ categories, version, decided_at, id ]`, or null.
	 */
	public static function decision() {
		$names = array_filter(
			[
				Helper::get( 'cookie_name', 'ablocks_consent' ),
				Helper::get( 'cookie_name_previous', '' ),
			]
		);

		foreach ( $names as $name ) {
			if ( empty( $_COOKIE[ $name ] ) ) {
				continue;
			}

			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Decoded as JSON and each field sanitised below.
			$raw    = wp_unslash( $_COOKIE[ $name ] );
			$parsed = json_decode( is_string( $raw ) ? $raw : '', true );

			if ( ! is_array( $parsed ) || ! isset( $parsed['c'] ) || ! is_array( $parsed['c'] ) ) {
				continue;
			}

			return [
				'categories' => array_values(
					array_filter( array_map( 'sanitize_key', $parsed['c'] ) )
				),
				'version'    => isset( $parsed['v'] ) ? (int) $parsed['v'] : 0,
				'decided_at' => isset( $parsed['t'] ) ? (int) $parsed['t'] : 0,
				'id'         => isset( $parsed['id'] ) ? sanitize_text_field( $parsed['id'] ) : '',
			];
		}

		return null;
	}

	/**
	 * Copy the decision onto the entry that was just saved.
	 *
	 * @param array  $form_info  Submission payload.
	 * @param array  $block_data Resolved form block attributes.
	 * @param object $validate   Validation object; `state_data` holds the entry id.
	 */
	public function attach( $form_info, $block_data, $validate ) {
		if ( ! Helper::get( 'enabled', true ) ) {
			return;
		}

		// A form can be configured not to store submissions at all, in which
		// case there is no entry to attach anything to.
		$entry_id = isset( $validate->state_data['submission_id'] )
			? (int) $validate->state_data['submission_id']
			: 0;

		if ( ! $entry_id ) {
			return;
		}

		$decision = self::decision();

		// No cookie means the visitor submitted without ever answering the
		// banner. Writing nothing is the honest record of that — an absent row
		// says "no consent was given", which is not the same as a row saying
		// none was granted.
		if ( null === $decision ) {
			return;
		}

		$optional = array_values( array_diff( $decision['categories'], [ 'necessary' ] ) );

		$this->write(
			$entry_id,
			[
				self::META['categories'] => $optional
					? implode( ', ', $optional )
					: __( 'Necessary only — everything optional refused', 'ablocks' ),
				self::META['policy']     => (string) $decision['version'],
				self::META['given']      => $decision['decided_at']
					? wp_date( 'Y-m-d H:i', $decision['decided_at'] )
					: '',
				self::META['reference']  => $decision['id'],
			]
		);
	}

	/**
	 * @param int   $entry_id Form entry.
	 * @param array $rows     Meta key => value.
	 */
	private function write( $entry_id, array $rows ) {
		global $wpdb;

		$table = $wpdb->prefix . ABLOCKS_PLUGIN_SLUG . '_form_meta';

		foreach ( $rows as $key => $value ) {
			if ( '' === $value ) {
				continue;
			}

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- The form entry tables are the plugin's own and have no WP API.
			$wpdb->insert(
				$table,
				[
					'entry_id'   => $entry_id,
					'meta_key'   => $key,
					'meta_value' => $value,
				],
				[ '%d', '%s', '%s' ]
			);
		}
	}

	/**
	 * Give the four rows readable labels on the submissions screen.
	 *
	 * Without this they inherit a label derived from the meta key, which reads
	 * as "Ablocks consent categories".
	 *
	 * @param array $field One row of an entry's meta.
	 * @return array
	 */
	public function relabel( $field ) {
		$labels = [
			self::META['categories'] => __( 'Consent — allowed', 'ablocks' ),
			self::META['policy']     => __( 'Consent — policy version', 'ablocks' ),
			self::META['given']      => __( 'Consent — given at', 'ablocks' ),
			self::META['reference']  => __( 'Consent — reference', 'ablocks' ),
		];

		$key = $field['meta_key'] ?? '';

		if ( isset( $labels[ $key ] ) ) {
			$field['label']     = $labels[ $key ];
			$field['inputType'] = 'text';
		}

		return $field;
	}

	/**
	 * The consent rows for one entry, for anything that needs them back.
	 *
	 * @param int $entry_id Form entry.
	 * @return array Meta key => value, empty when the entry carries none.
	 */
	public static function for_entry( $entry_id ) {
		global $wpdb;

		$table = $wpdb->prefix . ABLOCKS_PLUGIN_SLUG . '_form_meta';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT meta_key, meta_value FROM {$table} WHERE entry_id = %d AND meta_key LIKE %s",
				(int) $entry_id,
				$wpdb->esc_like( 'ablocks_consent_' ) . '%'
			),
			ARRAY_A
		);

		return wp_list_pluck( (array) $rows, 'meta_value', 'meta_key' );
	}
}
