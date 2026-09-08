<?php
namespace ABlocksCookieConsent;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Google Consent Mode v2.
 *
 * Google's tags read consent signals rather than being blocked outright, and
 * they read them *at load*. A `consent update` issued after gtag.js has already
 * initialised does not retroactively change the default state, so this block
 * has to be the first script in the document — before any enqueued script,
 * before any `wp_head` snippet a theme prints, before GTM.
 *
 * It is exempt from gating by construction: it is the thing that makes the
 * gating legible to Google, and blocking it would defeat the purpose.
 *
 * Note this is complementary to, not a replacement for, blocking the tags.
 * Consent Mode changes what Google's tags *do*; the gating layers stop them
 * loading at all. A site can run either or both, and running both is what most
 * European guidance expects.
 */
class ConsentMode {

	/**
	 * Category → the Consent Mode signals it controls.
	 *
	 * @var array
	 */
	private static $signal_map = [
		'analytics'  => [ 'analytics_storage' ],
		'marketing'  => [ 'ad_storage', 'ad_user_data', 'ad_personalization' ],
		'functional' => [ 'functionality_storage', 'personalization_storage' ],
	];

	public static function init() {
		// The settings are read inside the callback rather than here. Reading
		// them at plugin-load time would evaluate the translated defaults
		// before `init`, which WordPress rightly complains about.
		//
		// Negative priority so this beats anything hooked at 0 or 1, including
		// core's own head output. Being first is the whole requirement.
		add_action( 'wp_head', [ new self(), 'print_defaults' ], -9999 );
	}

	/**
	 * All signals, denied, except the one that is never optional.
	 *
	 * @return array
	 */
	public static function denied_defaults() {
		$defaults = [];
		foreach ( self::$signal_map as $signals ) {
			foreach ( $signals as $signal ) {
				$defaults[ $signal ] = 'denied';
			}
		}
		$defaults['security_storage'] = 'granted';
		return $defaults;
	}

	/**
	 * @return array Category => signals, for the client to build its updates.
	 */
	public static function signal_map() {
		return apply_filters( 'ablocks/cookie_consent/consent_mode_signals', self::$signal_map );
	}

	public function print_defaults() {
		if ( ! Helper::get( 'enabled', true ) || ! Helper::get( 'consent_mode', true ) ) {
			return;
		}
		if ( ! Helper::should_render_banner() ) {
			return;
		}

		$defaults = self::denied_defaults();
		$wait     = (int) Helper::get( 'consent_mode_wait', 500 );
		if ( $wait > 0 ) {
			$defaults['wait_for_update'] = $wait;
		}

		// Both names, because this runs before the main script and has to find
		// the decision even on the first page view after a rename.
		$cookies = array_values(
			array_filter(
				[
					Helper::get( 'cookie_name', 'ablocks_consent' ),
					Helper::get( 'cookie_name_previous', '' ),
				]
			)
		);
		$map     = self::signal_map();
		?>
<script id="ablocks-consent-mode" data-ablocks-consent-skip="1">
/* aBlocks Consent Mode v2 — must stay the first script in the document. */
window.dataLayer = window.dataLayer || [];
function gtag(){ dataLayer.push( arguments ); }
gtag( 'consent', 'default', <?php echo wp_json_encode( $defaults ); ?> );
( function () {
	var names = <?php echo wp_json_encode( $cookies ); ?>;
	var map = <?php echo wp_json_encode( $map ); ?>;
	var raw = null;
	document.cookie.split( ';' ).forEach( function ( part ) {
		var pair = part.split( '=' );
		var key = pair.shift().trim();
		if ( null === raw && names.indexOf( key ) > -1 ) { raw = pair.join( '=' ); }
	} );
	if ( ! raw ) { return; }
	var saved;
	try { saved = JSON.parse( decodeURIComponent( raw ) ); } catch ( e ) { return; }
		<?php // Number() rather than a bare strict compare: a cookie written by an older build may hold the version as a string. ?>
	if ( ! saved || Number( saved.v ) !== <?php echo (int) Helper::get( 'policy_version', 1 ); ?> ) { return; }
	var granted = saved.c || [];
	var update = {};
	Object.keys( map ).forEach( function ( category ) {
		var state = granted.indexOf( category ) > -1 ? 'granted' : 'denied';
		map[ category ].forEach( function ( signal ) { update[ signal ] = state; } );
	} );
	gtag( 'consent', 'update', update );
	<?php // Recorded so the main script does not send the same state a second time on every page view of a visitor who already decided. ?>
	window.ABlocksConsentSignals = update;
} )();
</script>
		<?php
	}
}
