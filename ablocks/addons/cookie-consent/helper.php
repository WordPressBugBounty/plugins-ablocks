<?php
namespace ABlocksCookieConsent;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings access and the shipped defaults.
 *
 * Everything the addon does is driven from one option so the whole
 * configuration can be read in a single query on the front end, and so the
 * admin screen has exactly one thing to save.
 */
class Helper {

	/**
	 * Cached decoded settings for this request.
	 *
	 * @var array|null
	 */
	private static $settings = null;

	/**
	 * The categories the plugin ships with.
	 *
	 * `necessary` is locked: it cannot be refused, because refusing it would
	 * mean refusing the site. Everything else defaults to denied, which is the
	 * only default an opt-in regime allows.
	 */
	public static function default_categories() {
		return [
			[
				'slug'        => 'necessary',
				'label'       => __( 'Strictly necessary', 'ablocks' ),
				'description' => __( 'Required for the site to work — security, load balancing, and remembering your cookie choice. These cannot be switched off.', 'ablocks' ),
				'locked'      => true,
				'enabled'     => true,
				// Only the cookie this addon sets itself is listed. What else a
				// site stores depends on what it runs, and a pre-filled list of
				// cookies that are not actually set is worse than an empty one:
				// it is a disclosure that is wrong.
				'cookies'     => [
					[
						'name'     => 'ablocks_consent',
						'provider' => '',
						'duration' => __( '1 year', 'ablocks' ),
						'purpose'  => __( 'Stores which cookie categories you agreed to, so you are not asked again.', 'ablocks' ),
					],
				],
			],
			[
				'slug'        => 'functional',
				'label'       => __( 'Functional', 'ablocks' ),
				'description' => __( 'Remember choices you make, such as language or region, and enable embedded content.', 'ablocks' ),
				'locked'      => false,
				'enabled'     => true,
				'cookies'     => [],
			],
			[
				'slug'        => 'analytics',
				'label'       => __( 'Analytics', 'ablocks' ),
				'description' => __( 'Help us understand how the site is used, so we can improve it. The data is aggregated.', 'ablocks' ),
				'locked'      => false,
				'enabled'     => true,
				'cookies'     => [],
			],
			[
				'slug'        => 'marketing',
				'label'       => __( 'Marketing', 'ablocks' ),
				'description' => __( 'Used to show you relevant advertising on this site and elsewhere, and to measure how it performs.', 'ablocks' ),
				'locked'      => false,
				'enabled'     => true,
				'cookies'     => [],
			],
		];
	}

	/**
	 * The rules that classify a script as belonging to a category.
	 *
	 * Only well-known tags ship enabled. A rule that fires on something it does
	 * not understand takes a site down — a blocked checkout or chat widget is a
	 * worse outcome than an ungated analytics tag — so the shipped list is
	 * short, specific, and visible in the admin screen for editing.
	 *
	 * `src` matches the script's URL; `inline` matches the contents of an inline
	 * script. Both are regular-expression bodies, matched case-insensitively.
	 */
	public static function default_rules() {
		return [
			[
				'id'       => 'google-tag-manager',
				'label'    => 'Google Tag Manager',
				'category' => 'analytics',
				'src'      => 'googletagmanager\.com/gtm\.js',
				'inline'   => 'googletagmanager\.com/gtm\.js|\(window,document,[\'"]script[\'"],[\'"]dataLayer',
				'handles'  => '',
				'enabled'  => true,
			],
			[
				'id'       => 'google-analytics',
				'label'    => 'Google Analytics (GA4 / gtag.js)',
				'category' => 'analytics',
				'src'      => 'googletagmanager\.com/gtag/js|google-analytics\.com/(analytics|ga)\.js',
				'inline'   => 'gtag\s*\(\s*[\'"]config[\'"]|GoogleAnalyticsObject',
				'handles'  => '',
				'enabled'  => true,
			],
			[
				'id'       => 'google-ads',
				'label'    => 'Google Ads / DoubleClick',
				'category' => 'marketing',
				'src'      => 'googleadservices\.com|doubleclick\.net|googlesyndication\.com',
				'inline'   => '',
				'handles'  => '',
				'enabled'  => true,
			],
			[
				'id'       => 'meta-pixel',
				'label'    => 'Meta (Facebook) Pixel',
				'category' => 'marketing',
				'src'      => 'connect\.facebook\.net/[^/]+/fbevents\.js',
				'inline'   => 'fbq\s*\(|connect\.facebook\.net/[^/]+/fbevents\.js',
				'handles'  => '',
				'enabled'  => true,
			],
			[
				'id'       => 'hotjar',
				'label'    => 'Hotjar',
				'category' => 'analytics',
				'src'      => 'static\.hotjar\.com|script\.hotjar\.com',
				'inline'   => 'hjSettings|_hjSettings',
				'handles'  => '',
				'enabled'  => true,
			],
			[
				'id'       => 'microsoft-clarity',
				'label'    => 'Microsoft Clarity',
				'category' => 'analytics',
				'src'      => 'clarity\.ms',
				'inline'   => 'clarity\.ms/tag',
				'handles'  => '',
				'enabled'  => true,
			],
			[
				'id'       => 'linkedin',
				'label'    => 'LinkedIn Insight Tag',
				'category' => 'marketing',
				'src'      => 'snap\.licdn\.com',
				'inline'   => '_linkedin_partner_id',
				'handles'  => '',
				'enabled'  => true,
			],
			[
				'id'       => 'tiktok',
				'label'    => 'TikTok Pixel',
				'category' => 'marketing',
				'src'      => 'analytics\.tiktok\.com',
				'inline'   => 'ttq\.load|analytics\.tiktok\.com',
				'handles'  => '',
				'enabled'  => true,
			],
			[
				'id'       => 'x-ads',
				'label'    => 'X (Twitter) Pixel',
				'category' => 'marketing',
				'src'      => 'static\.ads-twitter\.com',
				'inline'   => 'twq\s*\(',
				'handles'  => '',
				'enabled'  => true,
			],
			[
				'id'       => 'pinterest',
				'label'    => 'Pinterest Tag',
				'category' => 'marketing',
				'src'      => 's\.pinimg\.com',
				'inline'   => 'pintrk\s*\(',
				'handles'  => '',
				'enabled'  => true,
			],
			[
				'id'       => 'matomo',
				'label'    => 'Matomo',
				'category' => 'analytics',
				'src'      => 'matomo\.js|piwik\.js',
				'inline'   => '_paq\.push',
				'handles'  => '',
				'enabled'  => true,
			],
			[
				'id'       => 'intercom',
				'label'    => 'Intercom',
				'category' => 'functional',
				'src'      => 'widget\.intercom\.io|js\.intercomcdn\.com',
				'inline'   => 'intercomSettings',
				'handles'  => '',
				'enabled'  => false,
			],
			[
				'id'       => 'crisp',
				'label'    => 'Crisp Chat',
				'category' => 'functional',
				'src'      => 'client\.crisp\.chat',
				'inline'   => '\$crisp',
				'handles'  => '',
				'enabled'  => false,
			],
		];
	}

	/**
	 * The banner's own defaults. Deliberately plain: a site owner will restyle
	 * it, and a default that already looks "designed" is harder to restyle than
	 * one that looks neutral.
	 */
	public static function default_banner() {
		return [
			'layout'              => 'bar',
			'position'            => 'bottom',
			'title'               => __( 'We use cookies', 'ablocks' ),
			'message'             => __( 'We use cookies to run this site, to understand how it is used, and — with your permission — to personalise what you see. You can change your mind at any time.', 'ablocks' ),
			'policy_url'          => '',
			'policy_label'        => __( 'Privacy policy', 'ablocks' ),
			'accept_label'        => __( 'Accept all', 'ablocks' ),
			'reject_label'        => __( 'Reject all', 'ablocks' ),
			'settings_label'      => __( 'Preferences', 'ablocks' ),
			'save_label'          => __( 'Save choices', 'ablocks' ),
			'prefs_title'         => __( 'Cookie preferences', 'ablocks' ),
			'prefs_intro'         => __( 'Choose which categories you allow. Strictly necessary cookies are always on.', 'ablocks' ),
			'show_reject'         => true,
			'show_settings'       => true,

			/*
			 * A way out of the banner that is not "yes".
			 *
			 * Off by default, because in an opt-in regime the honest default is
			 * that the question stays until it is answered. But a site that has
			 * turned Reject off — a legitimate choice, and a common one — leaves
			 * a visitor who will not accept with nothing to do but leave, and
			 * the banner in front of them on every page forever. That is worse
			 * for everyone than a close button.
			 *
			 * Closing is never consent. `dismiss` stores nothing and releases
			 * nothing: it only stops asking for a while. `reject` writes a
			 * refusal, which is the same outcome made durable.
			 */
			'show_banner_close'   => false,
			'close_behaviour'     => 'dismiss',
			// 0 keeps the marker until the browser closes — the visitor has not
			// answered, so they should be asked again on their next visit, just
			// not on every page of this one.
			'dismiss_days'        => 0,
			// How much weight the Preferences button carries. A link is the
			// quietest of the three and stays the default; an outline gives it
			// the same presence as Accept without the same pull, which is what
			// most consent managers settle on.
			'settings_style'      => 'link',
			'overlay'             => false,
			'delay'               => 0,
			'reopen'              => true,
			'reopen_label'        => __( 'Cookie preferences', 'ablocks' ),
			'reopen_position'     => 'bottom-left',

			/*
			 * The preferences panel.
			 *
			 * `inline` swaps the banner's own contents for the category list,
			 * which is what this addon has always done and what every existing
			 * install is configured for — so it stays the default. `modal`
			 * lifts the list into a centred dialog and leaves the banner where
			 * it is behind, which is the shape most commercial consent managers
			 * use and the only way to have a small corner notice open into
			 * something big enough to read.
			 */
			'prefs_layout'        => 'inline',
			'prefs_max_width'     => 560,
			'prefs_accordion'     => false,
			'prefs_open_first'    => false,
			'show_cookie_table'   => false,
			// Closes the preferences and goes back to the banner. It never
			// dismisses the banner itself and never records a decision:
			// closing a dialog is not an answer to the question.
			'show_close'          => true,
			'show_prefs_reject'   => true,
			'show_prefs_accept'   => false,
			'prefs_accept_label'  => __( 'Accept all', 'ablocks' ),
			'cookie_policy_url'   => '',
			'cookie_policy_label' => __( 'Cookie policy', 'ablocks' ),
			'locked_label'        => __( 'Always on', 'ablocks' ),
			'locked_style'        => 'text',
			'switch_style'        => 'switch',

			'bg'                  => '#ffffff',
			'text'                => '#1e1e1e',
			'muted'               => '#5c5f66',
			'border'              => '#e2e4e9',
			'accent'              => '#5033ec',
			'accent_text'         => '#ffffff',
			'secondary_bg'        => '#f2f2f5',
			'secondary_text'      => '#1e1e1e',
			// Save has its own pair rather than borrowing the accent, so a
			// panel can distinguish "save what I chose" from "accept
			// everything" when both are on screen. Shipped equal to the accent
			// so nothing moves for an install that never touches them.
			'save_bg'             => '#5033ec',
			'save_text'           => '#ffffff',
			'locked_bg'           => '#e7f6ec',
			'locked_text'         => '#1c7a41',
			'radius'              => 10,
			'max_width'           => 1180,
			'shadow'              => true,
		];
	}

	/**
	 * Everything, before any saved value is layered on.
	 */
	public static function defaults() {
		return [
			// `optin` blocks first and asks; `notice` shows the banner and gates
			// nothing, for sites that only need to tell visitors what they use.
			'mode'                  => 'optin',
			'enabled'               => true,
			'policy_version'        => 1,
			// 'default' keeps the shipped name and hides the field; only
			// 'custom' lets one be typed, because a rename is a migration and
			// not something to fall into by clicking in a text box.
			'cookie_name_mode'      => 'default',
			'cookie_name'           => 'ablocks_consent',
			// Set automatically when the name changes, so a rename does not
			// orphan every decision already made. Holds one hop only.
			'cookie_name_previous'  => '',
			'cookie_days'           => 365,
			// Tags aBlocks prints itself, already gated. Empty means the site
			// adds its own tags elsewhere and the rules below catch them.
			'tag_gtm'               => '',
			'tag_ga4'               => '',
			'tag_meta_pixel'        => '',
			'reconsent_days'        => 0,
			// The output-buffer layer. On by default because without it the
			// feature covers only enqueued scripts, which is most of what a
			// site owner would consider "not covered".
			'buffer_gating'         => true,
			// The iframe and pixel layer. Separate from `buffer_gating`
			// because it is the one that can change what the visitor sees: a
			// site that wants scripts held back but its videos left alone can
			// say so without giving up the rest.
			'embed_gating'          => true,
			'embed_rules'           => [],
			'pixel_rules'           => [],
			// Report what would be gated, gate nothing. The way to find out
			// what breaks before it breaks.
			'dry_run'               => false,
			'consent_mode'          => true,
			'consent_mode_wait'     => 500,
			// `ads_data_redaction` and `url_passthrough`. On by default: they
			// only change what happens once something has been refused, and
			// what they change is the cost of the refusal.
			'consent_mode_ads'      => true,
			'record_enabled'        => true,
			'record_ip'             => false,
			'record_retention_days' => 730,
			'hide_for_admins'       => false,
			'categories'            => self::default_categories(),
			'rules'                 => self::default_rules(),
			'banner'                => self::default_banner(),
		];
	}

	/**
	 * The current settings, defaults merged under whatever is saved.
	 *
	 * Merging one level deep on `banner` matters: a settings blob saved by an
	 * older version is missing keys a newer one reads, and a missing colour is
	 * an unreadable banner rather than a notice.
	 */
	/**
	 * Drop the per-request cache.
	 *
	 * The settings are read many times per page and change once, so they are
	 * memoised. Anything that writes the option in the same request — the save
	 * handler, and the test suite stepping through configurations — has to say
	 * so, or it keeps reading what was there before.
	 */
	public static function flush() {
		self::$settings = null;
	}

	public static function get_settings() {
		if ( null !== self::$settings ) {
			return self::$settings;
		}

		$defaults = self::defaults();

		// The option holds a JSON string, but nothing stops another process
		// writing an array into it — a migration, a staging sync, `wp option
		// update --format=json`. json_decode() is typed against a string in
		// PHP 8, so without this guard that mistake is a fatal on every page
		// of the site rather than a setting that reads oddly.
		$stored = get_option( ABLOCKS_COOKIE_CONSENT_SETTINGS_NAME, '{}' );
		$saved  = is_string( $stored ) ? json_decode( $stored, true ) : $stored;
		$saved  = is_array( $saved ) ? $saved : [];

		$settings           = array_merge( $defaults, $saved );
		$settings['banner'] = array_merge( $defaults['banner'], isset( $saved['banner'] ) && is_array( $saved['banner'] ) ? $saved['banner'] : [] );

		if ( empty( $settings['categories'] ) || ! is_array( $settings['categories'] ) ) {
			$settings['categories'] = $defaults['categories'];
		}
		if ( ! isset( $saved['rules'] ) || ! is_array( $saved['rules'] ) ) {
			$settings['rules'] = $defaults['rules'];
		}

		self::$settings = apply_filters( 'ablocks/cookie_consent/settings', $settings );
		return self::$settings;
	}

	/**
	 * One setting.
	 *
	 * @param string $key     Setting key.
	 * @param mixed  $default Fallback.
	 * @return mixed
	 */
	public static function get( $key, $default = null ) {
		$settings = self::get_settings();
		return array_key_exists( $key, $settings ) ? $settings[ $key ] : $default;
	}

	/**
	 * One banner setting.
	 *
	 * @param string $key     Banner key.
	 * @param mixed  $default Fallback.
	 * @return mixed
	 */
	public static function banner( $key, $default = null ) {
		$banner = self::get( 'banner', [] );
		return isset( $banner[ $key ] ) ? $banner[ $key ] : $default;
	}

	public static function save_settings( array $settings ) {
		self::flush();
		return update_option( ABLOCKS_COOKIE_CONSENT_SETTINGS_NAME, wp_json_encode( $settings ) );
	}

	/**
	 * The categories that are switched on, `necessary` always first.
	 */
	public static function active_categories() {
		$categories = array_values(
			array_filter(
				(array) self::get( 'categories', [] ),
				function ( $category ) {
					return ! empty( $category['slug'] ) && ! empty( $category['enabled'] );
				}
			)
		);

		usort(
			$categories,
			function ( $a, $b ) {
				if ( ! empty( $a['locked'] ) === ! empty( $b['locked'] ) ) {
					return 0;
				}
				return ! empty( $a['locked'] ) ? -1 : 1;
			}
		);

		return apply_filters( 'ablocks/cookie_consent/categories', $categories );
	}

	/**
	 * Whether a category exists and is switched on.
	 *
	 * A rule pointing at a category the site has deleted would gate against a
	 * choice the visitor is never offered — for a script that means it never
	 * runs, and for an embed it means a card that can never be dismissed.
	 *
	 * @param string $slug Category slug.
	 * @return bool
	 */
	public static function category_is_active( $slug ) {
		foreach ( self::active_categories() as $category ) {
			if ( isset( $category['slug'] ) && $category['slug'] === $slug ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Category slugs that a visitor can actually refuse.
	 */
	public static function refusable_slugs() {
		$slugs = [];
		foreach ( self::active_categories() as $category ) {
			if ( empty( $category['locked'] ) ) {
				$slugs[] = $category['slug'];
			}
		}
		return $slugs;
	}

	/**
	 * The enabled rules, keyed by nothing in particular — order is match order.
	 */
	public static function active_rules() {
		$refusable = self::refusable_slugs();
		$rules     = array_values(
			array_filter(
				(array) self::get( 'rules', [] ),
				function ( $rule ) use ( $refusable ) {
					// A rule pointing at a category that is off, or at
					// `necessary`, would gate a script that is never released.
					return ! empty( $rule['enabled'] )
						&& ! empty( $rule['category'] )
						&& in_array( $rule['category'], $refusable, true );
				}
			)
		);
		return apply_filters( 'ablocks/cookie_consent/rules', $rules );
	}

	/**
	 * Whether gating should run at all for this request.
	 *
	 * Dry run counts as "running": it walks the same rules, it just reports
	 * instead of rewriting.
	 *
	 * This has to answer false wherever `should_render_banner()` does, because
	 * the two halves are one mechanism: gating holds a tag back, and the banner
	 * is the only thing that ever lets it go. Gate without a banner and the tag
	 * is frozen for the rest of that visitor's session, with nothing on the
	 * page able to release it.
	 */
	public static function is_gating_active() {
		if ( ! self::get( 'enabled', true ) || 'optin' !== self::get( 'mode', 'optin' ) ) {
			return false;
		}
		// The earliest caller registers on `wp`, so the current user is
		// resolved by the time this runs and the capability check is safe here.
		if ( self::get( 'hide_for_admins', false ) && current_user_can( 'manage_options' ) ) {
			return false;
		}
		if ( is_admin() || wp_doing_ajax() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
			return false;
		}
		// Conditional query tags always answer false before the query has run,
		// so asking early would quietly gate a feed rather than skip it.
		if ( did_action( 'wp' ) && is_feed() ) {
			return false;
		}
		return (bool) apply_filters( 'ablocks/cookie_consent/is_gating_active', true );
	}

	/**
	 * Whether the banner should be printed for this request.
	 */
	public static function should_render_banner() {
		if ( ! self::get( 'enabled', true ) ) {
			return false;
		}
		if ( self::get( 'hide_for_admins', false ) && current_user_can( 'manage_options' ) ) {
			return false;
		}
		if ( is_admin() ) {
			return false;
		}
		if ( did_action( 'wp' ) && ( is_feed() || is_embed() ) ) {
			return false;
		}
		return (bool) apply_filters( 'ablocks/cookie_consent/should_render_banner', true );
	}

	/**
	 * Which parts of this addon are Pro.
	 *
	 * The line is drawn where a feature is a power-user or agency need rather
	 * than part of asking the question correctly. Two things are deliberately
	 * NOT here:
	 *
	 * - **Google Consent Mode v2.** Gating it would mean a free user's Google
	 *   Ads quietly under-delivers on EEA traffic. Charging for the thing that
	 *   stops a third party punishing you is the wrong shape of paywall.
	 * - **The scan report.** It is what tells a site owner their configuration
	 *   is incomplete. Hiding it makes the free version silently wrong, which
	 *   is worse than making it smaller.
	 *
	 * Everything about the banner — every layout, every colour — stays free.
	 * This is a design plugin; gating the design would be off-brand.
	 *
	 * @return array Feature slug => whether this install has it.
	 */
	public static function pro_features() {
		$has_pro = \ABlocks\Helper::is_active_ablocks_pro();

		return apply_filters(
			'ablocks/cookie_consent/pro_features',
			[
				// Writing your own matcher, rather than switching the shipped
				// ones on and off.
				'custom_rules' => $has_pro,
				// The server-side audit log. Free keeps the visitor's own
				// cookie, which is the decision; Pro keeps the evidence.
				'records'      => $has_pro,
			],
			$has_pro
		);
	}

	/**
	 * Whether this install has a given Pro feature.
	 *
	 * Checked on the server as well as in the UI. A disabled control is a
	 * courtesy, not a boundary.
	 *
	 * @param string $feature Feature slug.
	 * @return bool
	 */
	public static function can( $feature ) {
		$features = self::pro_features();
		return ! empty( $features[ $feature ] );
	}

	/**
	 * The ids of the rules that ship with the plugin.
	 *
	 * Without Pro these can be switched on and off and pointed at a different
	 * category — that is configuration — but their patterns are not editable
	 * and no new rule can be added.
	 *
	 * @return array
	 */
	public static function shipped_rule_ids() {
		return wp_list_pluck( self::default_rules(), 'id' );
	}

	/**
	 * A short hash of the banner's wording, stored with each consent record.
	 *
	 * Without it a record says "they agreed" but not what to; with it the text
	 * in force at the time can be identified even after the wording changes.
	 */
	public static function banner_hash() {
		$banner = self::get( 'banner', [] );
		$parts  = [
			isset( $banner['title'] ) ? $banner['title'] : '',
			isset( $banner['message'] ) ? $banner['message'] : '',
			isset( $banner['prefs_intro'] ) ? $banner['prefs_intro'] : '',
		];
		foreach ( self::active_categories() as $category ) {
			$parts[] = $category['slug'] . ':' . ( isset( $category['description'] ) ? $category['description'] : '' );
		}
		return substr( md5( implode( '|', $parts ) ), 0, 32 );
	}
}
