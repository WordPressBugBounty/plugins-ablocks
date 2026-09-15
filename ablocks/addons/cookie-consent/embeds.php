<?php
namespace ABlocksCookieConsent;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The half of the page that is not a script.
 *
 * Gating every `<script>` on a site and then leaving a YouTube embed in the
 * page is not a partial job, it is a broken one: the iframe sets its cookies
 * the moment the document parses, before the banner has painted, and it does
 * it without running a line of the site's own JavaScript. The same is true of
 * a tracking pixel, which is an `<img>` and therefore also invisible to
 * everything the script layers do.
 *
 * Two element types, handled differently because what they cost is different.
 * An iframe is content the visitor came for, so blocking it has to leave
 * something in its place and a way to get it back. A pixel is not content at
 * all: it is removed and nothing is said, because there is nothing to say.
 *
 * The list below is an allowlist and that is the whole safety argument. A
 * consent plugin that guesses at iframes will eventually blank a payment form
 * or a captcha, and a checkout that does not load is a worse failure than an
 * ungated embed. Nothing matches unless it is named here.
 */
class Embeds {

	/**
	 * Embeds worth blocking, and what to call them when they are.
	 *
	 * `label` is shown to the visitor in the placeholder, so it is the name
	 * they would recognise rather than the domain it happens to load from.
	 */
	public static function default_rules() {
		return [
			[
				'id'       => 'youtube',
				'label'    => 'YouTube',
				'category' => 'marketing',
				// `youtube-nocookie` is included deliberately. It withholds
				// cookies until playback, not for ever, so it still needs
				// consent — the name is a description of the first second.
				'src'      => '(youtube\.com|youtube-nocookie\.com)/embed/',
				'enabled'  => true,
			],
			[
				'id'       => 'vimeo',
				'label'    => 'Vimeo',
				'category' => 'marketing',
				'src'      => 'player\.vimeo\.com/video/',
				'enabled'  => true,
			],
			[
				'id'       => 'google-maps',
				'label'    => 'Google Maps',
				'category' => 'functional',
				'src'      => '(google\.[a-z.]+/maps/embed|maps\.google\.[a-z.]+/maps)',
				'enabled'  => true,
			],
			[
				'id'       => 'soundcloud',
				'label'    => 'SoundCloud',
				'category' => 'marketing',
				'src'      => 'w\.soundcloud\.com/player',
				'enabled'  => true,
			],
			[
				'id'       => 'spotify',
				'label'    => 'Spotify',
				'category' => 'marketing',
				'src'      => 'open\.spotify\.com/embed',
				'enabled'  => true,
			],
			[
				'id'       => 'x-twitter',
				'label'    => 'X (Twitter)',
				'category' => 'marketing',
				'src'      => '(platform\.twitter\.com|syndication\.twitter\.com)',
				'enabled'  => true,
			],
			[
				'id'       => 'facebook-embed',
				'label'    => 'Facebook',
				'category' => 'marketing',
				'src'      => 'facebook\.com/plugins/',
				'enabled'  => true,
			],
			[
				'id'       => 'instagram-embed',
				'label'    => 'Instagram',
				'category' => 'marketing',
				'src'      => 'instagram\.com/(p|reel|tv)/[^/]+/embed',
				'enabled'  => true,
			],
			[
				'id'       => 'tiktok-embed',
				'label'    => 'TikTok',
				'category' => 'marketing',
				'src'      => 'tiktok\.com/(embed|player)',
				'enabled'  => true,
			],
			[
				// The `<noscript>` half of the GTM snippet: a 0×0 iframe that
				// exists to fire without JavaScript. `beacon` because there is
				// nothing to offer to load — a card standing where a hidden
				// pixel used to be would announce a loss the visitor never had.
				'id'       => 'gtm-noscript',
				'label'    => 'Google Tag Manager',
				'category' => 'analytics',
				'src'      => 'googletagmanager\.com/ns\.html',
				'enabled'  => true,
				'beacon'   => true,
			],
		];
	}

	/**
	 * Beacons: an `<img>` whose only purpose is the request it makes.
	 *
	 * These get no placeholder. A 1×1 image is not content the visitor is
	 * missing, and drawing a consent card where one used to be would be
	 * inventing a loss to apologise for.
	 *
	 * Note these are the `<noscript>` halves of the same snippets the Tags
	 * screen refuses to print — the difference being that here they are
	 * already in the page and can be taken out.
	 */
	public static function default_pixel_rules() {
		return [
			[
				'id'       => 'meta-pixel-img',
				'label'    => 'Meta Pixel',
				'category' => 'marketing',
				'src'      => 'facebook\.com/tr',
				'enabled'  => true,
			],
			[
				'id'       => 'google-ads-img',
				'label'    => 'Google Ads',
				'category' => 'marketing',
				'src'      => '(googleads\.g\.doubleclick\.net|google\.[a-z.]+/ads/ga-audiences|google\.[a-z.]+/pagead)',
				'enabled'  => true,
			],
			[
				'id'       => 'linkedin-img',
				'label'    => 'LinkedIn',
				'category' => 'marketing',
				'src'      => 'px\.ads\.linkedin\.com',
				'enabled'  => true,
			],
			[
				'id'       => 'x-ads-img',
				'label'    => 'X (Twitter)',
				'category' => 'marketing',
				'src'      => 't\.co/i/adsct',
				'enabled'  => true,
			],
		];
	}

	/**
	 * The rules actually in force, with the site's own enable/category edits
	 * folded over the shipped patterns.
	 *
	 * Patterns are not editable and no rule can be added: unlike the script
	 * rules, a wrong match here blanks something the visitor can see.
	 *
	 * @param string $kind 'embed' or 'pixel'.
	 * @return array
	 */
	public static function active_rules( $kind = 'embed' ) {
		$shipped = 'pixel' === $kind ? self::default_pixel_rules() : self::default_rules();
		$saved   = Helper::get( 'pixel' === $kind ? 'pixel_rules' : 'embed_rules', [] );
		$saved   = is_array( $saved ) ? $saved : [];

		$edits = [];
		foreach ( $saved as $rule ) {
			if ( ! empty( $rule['id'] ) ) {
				$edits[ $rule['id'] ] = $rule;
			}
		}

		$active = [];
		foreach ( $shipped as $rule ) {
			$edit = isset( $edits[ $rule['id'] ] ) ? $edits[ $rule['id'] ] : [];

			if ( isset( $edit['enabled'] ) && ! $edit['enabled'] ) {
				continue;
			}
			if ( ! empty( $edit['category'] ) ) {
				$rule['category'] = (string) $edit['category'];
			}

			// A rule pointing at a category the site has removed would gate
			// against a choice the visitor is never offered, and the embed
			// would never come back.
			if ( ! Helper::category_is_active( $rule['category'] ) ) {
				continue;
			}

			$active[] = $rule;
		}

		return apply_filters( 'ablocks/cookie_consent/embed_rules', $active, $kind );
	}

	/**
	 * Which rule, if any, claims this URL.
	 *
	 * @param string $src  Element source.
	 * @param string $kind 'embed' or 'pixel'.
	 * @return array|null The matching rule.
	 */
	public static function match( $src, $kind = 'embed' ) {
		$src = (string) $src;
		if ( '' === trim( $src ) ) {
			return null;
		}

		foreach ( self::active_rules( $kind ) as $rule ) {
			// Silenced for the same reason the script matcher silences: a
			// pattern can be pointed at a different category from the admin
			// screen, and a bad edit must not warn on every page view.
			$hit = @preg_match( '#' . str_replace( '#', '\#', $rule['src'] ) . '#i', $src ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
			if ( 1 === $hit ) {
				return $rule;
			}
		}

		return null;
	}

	/**
	 * The card that stands where the embed was.
	 *
	 * The original element is carried across in an attribute rather than left
	 * in the document with its `src` moved aside. Two reasons, and both are
	 * about being sure: markup inside an attribute is text and cannot make a
	 * request under any circumstances, and putting it back is an exact
	 * restoration rather than a reconstruction of what the author wrote.
	 *
	 * The card replaces the element rather than wrapping it, because a wrapper
	 * is a new node in the middle of whatever the theme's responsive-embed CSS
	 * was selecting, and blanking a video is a smaller failure than moving
	 * every video on the site half a column to the left.
	 *
	 * @param string $original The whole original element.
	 * @param array  $rule     The rule that matched.
	 * @return string
	 */
	public static function placeholder( $original, $rule ) {
		$label = isset( $rule['label'] ) ? $rule['label'] : __( 'external', 'ablocks' );

		return sprintf(
			'<div class="ablocks-consent-embed" data-ablocks-consent="%1$s" data-ablocks-embed="%2$s" data-ablocks-embed-html="%3$s">' .
				'<div class="ablocks-consent-embed__inner">' .
					'<p class="ablocks-consent-embed__title">%4$s</p>' .
					'<p class="ablocks-consent-embed__text">%5$s</p>' .
					'<span class="ablocks-consent-embed__actions">' .
						'<button type="button" class="ablocks-consent-embed__btn" data-ablocks-consent-action="load-embed">%6$s</button>' .
						'<button type="button" class="ablocks-consent-embed__link" data-ablocks-consent-action="allow-embeds">%7$s</button>' .
					'</span>' .
				'</div>' .
			'</div>',
			esc_attr( $rule['category'] ),
			esc_attr( $label ),
			esc_attr( $original ),
			esc_html(
				sprintf(
					/* translators: %s: name of the embed provider, e.g. YouTube. */
					__( '%s content is blocked', 'ablocks' ),
					$label
				)
			),
			esc_html(
				sprintf(
					/* translators: %s: name of the embed provider, e.g. YouTube. */
					__( 'Loading this would let %s set cookies on your device. Nothing has been sent to them yet.', 'ablocks' ),
					$label
				)
			),
			esc_html__( 'Load this once', 'ablocks' ),
			esc_html(
				sprintf(
					/* translators: %s: name of the embed provider, e.g. YouTube. */
					__( 'Always allow %s', 'ablocks' ),
					$label
				)
			)
		);
	}
}
