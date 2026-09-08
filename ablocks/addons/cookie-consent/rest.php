<?php
namespace ABlocksCookieConsent;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WP_REST_Server;
use WP_REST_Response;

/**
 * The endpoint that records a decision.
 *
 * Public and unauthenticated by necessity — the visitors whose consent matters
 * most are the ones who are not logged in — so everything arriving here is
 * untrusted input, and the endpoint is rate-limited per address. There is
 * nothing here worth attacking except the ability to fill a table, and that is
 * what the limit is for.
 *
 * The cookie itself is written by the client, not here. A `Set-Cookie` on a
 * response would work, but making the client the single writer keeps one
 * source of truth and keeps consent working if the request fails.
 */
class Rest {

	const LIMIT_PER_HOUR = 30;

	public static function init() {
		add_action( 'rest_api_init', [ new self(), 'register_routes' ] );
	}

	public function register_routes() {
		register_rest_route(
			ABLOCKS_REST_NAMESPACE,
			'/consent',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'record' ],
				'permission_callback' => '__return_true',
				'args'                => [
					'consent_id'  => [
						'required'          => true,
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
					],
					'categories'  => [
						'required' => false,
						'type'     => 'array',
						'items'    => [ 'type' => 'string' ],
						'default'  => [],
					],
					'decision'    => [
						'required'          => false,
						'type'              => 'string',
						'default'           => 'save',
						'sanitize_callback' => 'sanitize_key',
					],
					'page_url'    => [
						'required'          => false,
						'type'              => 'string',
						'default'           => '',
						'sanitize_callback' => 'esc_url_raw',
					],
				],
			]
		);
	}

	/**
	 * @param \WP_REST_Request $request Incoming request.
	 * @return WP_REST_Response
	 */
	public function record( $request ) {
		if ( ! Helper::get( 'record_enabled', true ) ) {
			return new WP_REST_Response( [ 'recorded' => false ], 200 );
		}
		if ( $this->is_rate_limited() ) {
			return new WP_REST_Response( [ 'recorded' => false ], 429 );
		}

		$consent_id = preg_replace( '/[^a-zA-Z0-9\-]/', '', (string) $request->get_param( 'consent_id' ) );
		if ( strlen( $consent_id ) < 8 ) {
			return new WP_REST_Response( [ 'recorded' => false ], 400 );
		}

		$categories = array_map( 'sanitize_key', (array) $request->get_param( 'categories' ) );

		$id = Record::insert(
			[
				'consent_id' => $consent_id,
				'categories' => $categories,
				'decision'   => $request->get_param( 'decision' ),
				'page_url'   => $request->get_param( 'page_url' ),
			]
		);

		return new WP_REST_Response( [ 'recorded' => (bool) $id ], 200 );
	}

	/**
	 * The address the cap is counted against.
	 *
	 * `REMOTE_ADDR` and nothing else by default, because every forwarded-for
	 * header is set by the client and trusting one by default would make the
	 * cap trivially bypassable.
	 *
	 * Behind a CDN or a reverse proxy that is the proxy's address for every
	 * visitor, so the whole site shares one allowance and recording stops after
	 * thirty decisions an hour. A site in that position knows which header its
	 * own proxy sets and can say so:
	 *
	 *     add_filter( 'ablocks/cookie_consent/client_ip', function () {
	 *         return $_SERVER['HTTP_CF_CONNECTING_IP'] ?? '';
	 *     } );
	 *
	 * Nothing a visitor sees depends on this either way: the decision is
	 * written to their cookie and applied before the request is made, and a
	 * failure here is deliberately not surfaced. What is lost when the cap is
	 * hit is audit rows, which is exactly what a proxied site would want back.
	 *
	 * @return string
	 */
	private function client_ip() {
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
		$ip = (string) apply_filters( 'ablocks/cookie_consent/client_ip', $ip );

		// A filter that returns something that is not an address would key
		// every visitor to the same bucket, which is the failure it was added
		// to fix.
		$valid = filter_var( $ip, FILTER_VALIDATE_IP );

		return $valid ? $valid : 'unknown';
	}

	/**
	 * A coarse per-address cap. Consent is a handful of decisions per visitor
	 * per year; anything approaching thirty an hour is not a visitor.
	 *
	 * @return bool
	 */
	private function is_rate_limited() {
		$key = 'ablocks_cc_rl_' . md5( $this->client_ip() );

		$count = (int) get_transient( $key );
		if ( $count >= self::LIMIT_PER_HOUR ) {
			return true;
		}

		set_transient( $key, $count + 1, HOUR_IN_SECONDS );
		return false;
	}
}
