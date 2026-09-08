<?php

namespace ABlocksCookieConsent;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use ABlocks\Interfaces\AddonInterface;

/**
 * Cookie Consent addon.
 *
 * A consent banner is the visible tenth of this feature. The other nine tenths
 * is prior blocking — making sure a third-party tag has not already run by the
 * time the visitor is asked. That is what the two gating layers do, and it is
 * why this addon exists inside aBlocks rather than as a styled popup: the
 * plugin already owns a script-gating mechanism (see `ABlocks\Performance\
 * ScriptGate`) and this re-keys it from "first interaction" to "consent for
 * category X".
 *
 * One constraint shapes everything: nothing here may branch the rendered HTML
 * on the visitor's consent cookie. Page caches serve one document per URL, so a
 * PHP-side branch would hand the first visitor's consent state to everyone
 * after them. The banner therefore ships on every page, hidden, and the client
 * decides what to show and what to release.
 */
final class CookieConsent implements AddonInterface {

	private $addon_name = 'cookie-consent';

	private function __construct() {
		$this->define_constants();
		$this->init_addon();
	}

	public function define_constants() {
		define( 'ABLOCKS_COOKIE_CONSENT_VERSION', '1.0' );
		define( 'ABLOCKS_COOKIE_CONSENT_ADDON_NAME', $this->addon_name );
		define( 'ABLOCKS_COOKIE_CONSENT_SETTINGS_NAME', 'ablocks_cookie_consent' );
		define( 'ABLOCKS_COOKIE_CONSENT_DIR_PATH', ABLOCKS_ADDONS_DIR_PATH . 'cookie-consent/' );
	}

	public function init_addon() {
		add_action( "ablocks/addons/activated_{$this->addon_name}", [ $this, 'addon_activation_hook' ] );

		if ( ! \ABlocks\Helper::get_addon_active_status( $this->addon_name ) ) {
			return;
		}

		// The admin screen and the recording endpoint are always available; the
		// gating and the banner only exist on the front end.
		Ajax::init();
		Rest::init();
		Record::init();
		// Outside the front-end block below: a form can be submitted through the
		// REST controller or through admin-ajax, and only one of those counts as
		// the front end.
		FormConsent::init();

		if ( ! is_admin() ) {
			ConsentMode::init();
			Tags::init();
			Frontend::init();
			// Gating asks whether this request is a feed, and a conditional
			// query tag has no answer before the query has run. Registering on
			// `wp` still lands ahead of `template_redirect` and of any script
			// being printed, so nothing is missed by waiting.
			add_action(
				'wp',
				function () {
					Gating::init();
					Buffer::init();
				}
			);
		}

		Admin::init();
	}

	public static function init() {
		static $instance = false;

		if ( ! $instance ) {
			$instance = new self();
		}

		return $instance;
	}

	/**
	 * Create the consent record table the first time the addon is switched on.
	 * Doing it here rather than in the plugin installer keeps the table out of
	 * sites that never enable the feature.
	 */
	public function addon_activation_hook() {
		Database::create_table();
	}
}
