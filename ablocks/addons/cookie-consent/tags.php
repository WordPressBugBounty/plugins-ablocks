<?php
namespace ABlocksCookieConsent;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Tags aBlocks used to add, kept alive for the sites that already asked it to.
 *
 * Placing a measurement tag is not this plugin's job. The plugins built for it
 * — Site Kit for Google's tags, Meta's own for the Pixel — do it better, stay
 * current with formats this never accepted (a `GT-` Google tag ID matched
 * neither field here), and are where a site owner already looks. aBlocks holds
 * tags back; it no longer places them, and there is no screen to enter one.
 *
 * What remains is the promise made to sites that entered an ID before that was
 * decided: their tag keeps being printed, still gated, so nobody's measurement
 * stops because the feature moved. The screen tells them to move it and clear
 * the field, and once the field is clear this prints nothing for ever.
 *
 * The provider check earns its keep either way — more, in fact, than it did as
 * a greyed-out field. With no UI left to stop someone entering an ID that Site
 * Kit is already placing, refusing to print it is the only thing standing
 * between a legacy value and every page view being counted twice.
 *
 * Two deliberate omissions, unchanged. Google's and Meta's copy-paste snippets
 * both carry a `<noscript>` beacon — a bare `<iframe>` or `<img>` that fires
 * without JavaScript. They are not printed: a beacon that ignores consent is
 * worse than no beacon. (The `Embeds` layer now strips those when another
 * plugin puts them in the page.) And GTM gets no dataLayer bootstrap beyond
 * the standard snippet, because Consent Mode created `dataLayer` and `gtag()`
 * further up the head.
 */
class Tags {

	/**
	 * Which category each tag answers to, and how its ID must look.
	 *
	 * The patterns are what Google and Meta actually issue. Rejecting a
	 * malformed ID here rather than printing it is the difference between an
	 * empty head and a broken one.
	 */
	const TAGS = [
		'tag_gtm'         => [
			'category' => 'analytics',
			'pattern'  => '/^GTM-[A-Z0-9]{4,}$/i',
		],
		'tag_ga4'         => [
			'category' => 'analytics',
			'pattern'  => '/^G-[A-Z0-9]{4,}$/i',
		],
		'tag_meta_pixel'  => [
			'category' => 'marketing',
			'pattern'  => '/^[0-9]{8,20}$/',
		],
	];

	/**
	 * Plugins that already put one of these tags on the page.
	 *
	 * Keyed by plugin file because that is the only stable way to recognise one
	 * — a slug is what the code is actually about here, so no display name is
	 * hard-coded: the label shown to the user is read from the plugin's own
	 * header at runtime, which also means it stays right when a plugin is
	 * renamed or translated.
	 *
	 * The list is deliberately limited to plugins whose whole job is to place a
	 * specific tag. A general header-snippet plugin could contain anything, so
	 * guessing from its presence would be wrong as often as right.
	 */
	const PROVIDERS = [
		'google-site-kit/google-site-kit.php'                              => [ 'tag_ga4', 'tag_gtm' ],
		'duracelltomi-google-tag-manager/duracelltomi-google-tag-manager.php' => [ 'tag_gtm' ],
		'google-analytics-for-wordpress/googleanalytics.php'               => [ 'tag_ga4' ],
		'google-analytics-premium/googleanalytics-premium.php'             => [ 'tag_ga4' ],
		'google-analytics-dashboard-for-wp/gadwp.php'                      => [ 'tag_ga4' ],
		'exactmetrics-premium/exactmetrics-premium.php'                    => [ 'tag_ga4' ],
		'pixelyoursite/facebook-pixel-master.php'                          => [ 'tag_ga4', 'tag_meta_pixel' ],
		'pixelyoursite-pro/pixelyoursite-pro.php'                          => [ 'tag_ga4', 'tag_meta_pixel' ],
		'official-facebook-pixel/facebook-pixel.php'                       => [ 'tag_meta_pixel' ],
		'facebook-for-woocommerce/facebook-for-woocommerce.php'            => [ 'tag_meta_pixel' ],
	];

	/**
	 * Which tags are already being placed by something else on this site.
	 *
	 * Entering an ID here as well would load the same tag twice and count every
	 * page view twice with it, so the screen says so rather than leaving the
	 * field looking like something that still needs filling in.
	 *
	 * @return array Tag key => the name of the plugin providing it.
	 */
	public static function detected_providers() {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$installed = get_plugins();
		$active    = array_filter(
			array_keys( self::PROVIDERS ),
			function ( $file ) use ( $installed ) {
				// Present as well as active. `is_plugin_active()` only reads a
				// list of paths, and a plugin deleted from disk without being
				// deactivated stays on it — claiming a tag is placed by
				// something that is not there would leave the field saying
				// there is nothing to enter when there is.
				return isset( $installed[ $file ] ) && is_plugin_active( $file );
			}
		);

		return self::providers_for(
			$active,
			wp_list_pluck( $installed, 'Name' )
		);
	}

	/**
	 * What a provider is actually putting on the page.
	 *
	 * The list above says what a plugin *can* place, which is not the same as
	 * what it *is* placing. Site Kit is the clearest case: it is commonly
	 * installed and never connected, and even connected it can be told not to
	 * write the snippet because the site places the tag some other way. Reading
	 * "installed" as "already added" would tell someone there is nothing to
	 * enter when nothing is being placed at all — and they would leave the
	 * field empty and have no analytics.
	 *
	 * So where a plugin records its own state, that is read rather than
	 * guessed. Where it does not, its presence is the answer: those plugins
	 * exist to place one tag and do nothing else.
	 *
	 * @param string $file Plugin file.
	 * @return array Tag key => the ID being placed, or '' when it is not known.
	 */
	private static function claims( $file ) {
		if ( 'google-site-kit/google-site-kit.php' === $file ) {
			return self::site_kit_claims();
		}

		return array_fill_keys( self::PROVIDERS[ $file ], '' );
	}

	/**
	 * Site Kit, according to Site Kit.
	 *
	 * Three things have to line up before it writes a tag: the module has to be
	 * active, it has to have an ID from a connected account, and `useSnippet`
	 * has to be on — that last one is a switch in its own UI for people who
	 * place the tag themselves, and it is off more often than you would think.
	 *
	 * @return array Tag key => ID.
	 */
	private static function site_kit_claims() {
		$modules = get_option( 'googlesitekit_active_modules', [] );
		$modules = is_array( $modules ) ? $modules : [];
		$claims  = [];

		$checks = [
			'tag_ga4' => [ 'analytics-4', 'googlesitekit_analytics-4_settings', 'measurementID' ],
			'tag_gtm' => [ 'tagmanager', 'googlesitekit_tagmanager_settings', 'containerID' ],
		];

		foreach ( $checks as $tag => $check ) {
			list( $module, $option, $field ) = $check;

			if ( ! in_array( $module, $modules, true ) ) {
				continue;
			}

			$settings = get_option( $option, [] );
			if ( ! is_array( $settings ) || empty( $settings['useSnippet'] ) || empty( $settings[ $field ] ) ) {
				continue;
			}

			$claims[ $tag ] = (string) $settings[ $field ];
		}

		return $claims;
	}

	/**
	 * The mapping itself, separated from the business of asking WordPress what
	 * is installed so it can be exercised directly.
	 *
	 * @param array $active Plugin files that are active.
	 * @param array $names  Plugin file => display name.
	 * @return array Tag key => [ 'name' => provider name, 'id' => ID or '' ].
	 */
	public static function providers_for( array $active, array $names = [] ) {
		$found = [];

		foreach ( $active as $file ) {
			if ( empty( self::PROVIDERS[ $file ] ) ) {
				continue;
			}

			$name = isset( $names[ $file ] ) ? $names[ $file ] : $file;

			foreach ( self::claims( $file ) as $tag => $id ) {
				// First one wins: naming one is enough to make the point, and a
				// list of them would only be noise on a single form row.
				if ( ! isset( $found[ $tag ] ) ) {
					$found[ $tag ] = [
						'name' => $name,
						'id'   => $id,
					];
				}
			}
		}

		return $found;
	}

	public static function init() {
		$self = new self();

		// After Consent Mode's defaults block (`wp_head` at -9999), which has
		// to be the first script in the document, and ahead of everything a
		// theme prints. Google asks for its tags as high in the head as
		// possible and this is as high as they can go.
		add_action( 'wp_head', [ $self, 'print_tags' ], 2 );
	}

	/**
	 * A configured, well-formed ID, or an empty string.
	 *
	 * @param string $key One of the keys in self::TAGS.
	 * @return string
	 */
	public static function id( $key ) {
		if ( empty( self::TAGS[ $key ] ) ) {
			return '';
		}

		$value = trim( (string) Helper::get( $key, '' ) );

		return preg_match( self::TAGS[ $key ]['pattern'], $value ) ? $value : '';
	}

	/**
	 * Whether any tag is configured at all.
	 */
	public static function has_any() {
		foreach ( array_keys( self::TAGS ) as $key ) {
			if ( self::id( $key ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Whether a tag for this category should be printed in its blocked form.
	 *
	 * When gating is not active — the addon is off, or the banner is in notice
	 * mode, which by definition gates nothing — the tag is printed as an
	 * ordinary script. Emitting a permanently blocked tag in that case would
	 * mean analytics that never runs and no way to tell why.
	 *
	 * A category the site has removed counts as ungated too: the visitor is
	 * never offered that choice, so there would be nothing to grant.
	 *
	 * @param string $category Category slug the tag belongs to.
	 */
	private function should_gate( $category ) {
		return Helper::is_gating_active() && $this->category_exists( $category );
	}

	/**
	 * @param string $slug Category slug.
	 */
	private function category_exists( $slug ) {
		foreach ( (array) Helper::get( 'categories', [] ) as $category ) {
			if ( isset( $category['slug'] ) && $category['slug'] === $slug && ! empty( $category['enabled'] ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * The snippet bodies, with `%2$s` standing in for the ID.
	 *
	 * Held as constants so each one can be printed through a single `printf`
	 * with its dynamic parts escaped in the same call — the alternative is
	 * echoing a built-up string, which no reader (or sniff) can verify at a
	 * glance is safe.
	 */
	const GTM_BODY = "/* aBlocks — Google Tag Manager */\n" .
		"(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});\n" .
		"var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';\n" .
		"j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);\n" .
		"})(window,document,'script','dataLayer','%2\$s');";

	const GA4_BODY = "/* aBlocks — Google Analytics 4 */\n" .
		"window.dataLayer = window.dataLayer || [];\n" .
		"function gtag(){dataLayer.push(arguments);}\n" .
		"gtag('js', new Date());\n" .
		"gtag('config', '%2\$s');";

	const PIXEL_BODY = "/* aBlocks — Meta Pixel */\n" .
		"!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?\n" .
		"n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;\n" .
		"n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;\n" .
		"t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,\n" .
		"document,'script','https://connect.facebook.net/en_US/fbevents.js');\n" .
		"fbq('init', '%2\$s');\n" .
		"fbq('track', 'PageView');";

	/**
	 * Print one inline tag, blocked or not.
	 *
	 * @param string $category Category slug.
	 * @param string $body     One of the *_BODY constants.
	 * @param string $id       The measurement ID.
	 */
	private function print_inline( $category, $body, $id ) {
		if ( $this->should_gate( $category ) ) {
			printf(
				'<script type="text/plain" data-ablocks-consent="%1$s">' . "\n" . $body . "\n</script>\n", // phpcs:ignore WordPress.Security.EscapingOutput.OutputNotEscaped -- $body is a class constant, and both substitutions below are escaped.
				esc_attr( $category ),
				esc_js( $id )
			);
			return;
		}

		printf(
			'<script>' . "\n" . $body . "\n</script>\n", // phpcs:ignore WordPress.Security.EscapingOutput.OutputNotEscaped -- As above.
			esc_attr( $category ),
			esc_js( $id )
		);
	}

	public function print_tags() {
		if ( ! Helper::get( 'enabled', true ) ) {
			return;
		}

		// A tag something else is already placing is not printed. This used to
		// be enforced by disabling the field; with the field gone it has to be
		// enforced here, or a value saved before Site Kit was installed would
		// silently double every figure the site reports.
		$providers = self::detected_providers();

		$gtm   = isset( $providers['tag_gtm'] ) ? '' : self::id( 'tag_gtm' );
		$ga4   = isset( $providers['tag_ga4'] ) ? '' : self::id( 'tag_ga4' );
		$pixel = isset( $providers['tag_meta_pixel'] ) ? '' : self::id( 'tag_meta_pixel' );

		if ( $gtm ) {
			$this->print_inline( self::TAGS['tag_gtm']['category'], self::GTM_BODY, $gtm );
		}

		if ( $ga4 ) {
			$this->print_ga4( $ga4 );
		}

		if ( $pixel ) {
			$this->print_inline( self::TAGS['tag_meta_pixel']['category'], self::PIXEL_BODY, $pixel );
		}
	}

	/**
	 * GA4 is the only one that needs a `src` tag of its own before its config
	 * runs, so it does not fit `print_inline()`.
	 *
	 * @param string $id Measurement ID.
	 */
	private function print_ga4( $id ) {
		$category = self::TAGS['tag_ga4']['category'];
		$src      = 'https://www.googletagmanager.com/gtag/js?id=' . rawurlencode( $id );

		if ( $this->should_gate( $category ) ) {
			printf(
				'<script type="text/plain" data-ablocks-consent="%1$s" src="%2$s"></script>' . "\n",
				esc_attr( $category ),
				esc_url( $src )
			);
		} else {
			printf( '<script async src="%s"></script>' . "\n", esc_url( $src ) );
		}

		$this->print_inline( $category, self::GA4_BODY, $id );
	}
}
