<?php
namespace ABlocksCookieConsent;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The banner, and the script that drives it.
 *
 * The markup ships on every page whether or not the visitor has already
 * decided, and it ships hidden. That is not laziness — it is the only shape
 * that survives a page cache. A cache stores one document per URL; if PHP
 * decided whether to include the banner, the first visitor through the cache
 * would decide for everyone behind them.
 *
 * So the server renders one document and the client decides what to do with it.
 */
class Frontend {

	public static function init() {
		$self = new self();
		add_action( 'wp_enqueue_scripts', [ $self, 'enqueue' ] );
		add_action( 'wp_footer', [ $self, 'render' ], 20 );
		add_shortcode( 'ablocks_cookie_preferences', [ $self, 'preferences_shortcode' ] );
	}

	public function enqueue() {
		if ( ! Helper::should_render_banner() ) {
			return;
		}

		$asset_file = ABLOCKS_ASSETS_PATH . 'build/cookie-consent.asset.php';
		$asset      = file_exists( $asset_file ) ? require $asset_file : [
			'dependencies' => [],
			'version'      => ABLOCKS_VERSION,
		];

		wp_enqueue_style(
			'ablocks-cookie-consent',
			ABLOCKS_ASSETS_URL . 'build/cookie-consent.css',
			[],
			$asset['version']
		);

		wp_enqueue_script(
			'ablocks-cookie-consent',
			ABLOCKS_ASSETS_URL . 'build/cookie-consent.js',
			[],
			$asset['version'],
			true
		);

		// `wp_localize_script` casts every scalar to a string, which would turn
		// the policy version into "1" and break the strict comparison the
		// Consent Mode reader in <head> makes against the integer. An inline
		// JSON assignment keeps the types.
		wp_add_inline_script(
			'ablocks-cookie-consent',
			'window.ABlocksConsentConfig = ' . wp_json_encode( $this->script_config() ) . ';',
			'before'
		);
	}

	/**
	 * Everything the client needs to decide what to show and what to release.
	 *
	 * @return array
	 */
	private function script_config() {
		$categories = [];
		foreach ( Helper::active_categories() as $category ) {
			$categories[] = [
				'slug'   => $category['slug'],
				'locked' => ! empty( $category['locked'] ),
			];
		}

		return [
			'cookie'        => Helper::get( 'cookie_name', 'ablocks_consent' ),
			'cookiePrev'    => Helper::get( 'cookie_name_previous', '' ),
			'days'          => (int) Helper::get( 'cookie_days', 365 ),
			'version'       => (int) Helper::get( 'policy_version', 1 ),
			'reconsentDays' => (int) Helper::get( 'reconsent_days', 0 ),
			'mode'          => Helper::get( 'mode', 'optin' ),
			'categories'    => $categories,
			'consentMode'   => (bool) Helper::get( 'consent_mode', true ),
			'consentModeAds' => (bool) Helper::get( 'consent_mode_ads', true ),
			'signals'       => ConsentMode::signal_map(),
			'record'        => (bool) Helper::get( 'record_enabled', true ),
			'endpoint'      => rest_url( ABLOCKS_REST_NAMESPACE . '/consent' ),
			'delay'         => (int) Helper::banner( 'delay', 0 ),
			'reopen'        => (bool) Helper::banner( 'reopen', true ),
			'canClose'      => (bool) Helper::banner( 'show_banner_close', false ),
			'closeAction'   => Helper::banner( 'close_behaviour', 'dismiss' ),
			'dismissDays'   => (int) Helper::banner( 'dismiss_days', 0 ),
		];
	}

	/**
	 * The CSS custom properties the stylesheet reads.
	 *
	 * Emitted as variables rather than as rules so the whole banner can be
	 * restyled from the settings screen without the stylesheet knowing anything
	 * about a particular site's palette, and so a theme can override any one of
	 * them without fighting specificity.
	 *
	 * @return string
	 */
	private function style_vars() {
		$banner = Helper::get( 'banner', [] );
		$vars   = [
			'--ablocks-cc-bg'              => $banner['bg'],
			'--ablocks-cc-text'            => $banner['text'],
			'--ablocks-cc-muted'           => $banner['muted'],
			'--ablocks-cc-border'          => $banner['border'],
			'--ablocks-cc-accent'          => $banner['accent'],
			'--ablocks-cc-accent-text'     => $banner['accent_text'],
			'--ablocks-cc-secondary-bg'    => $banner['secondary_bg'],
			'--ablocks-cc-secondary-text'  => $banner['secondary_text'],
			'--ablocks-cc-save-bg'         => $banner['save_bg'],
			'--ablocks-cc-save-text'       => $banner['save_text'],
			'--ablocks-cc-locked-bg'       => $banner['locked_bg'],
			'--ablocks-cc-locked-text'     => $banner['locked_text'],
			'--ablocks-cc-radius'          => (int) $banner['radius'] . 'px',
			'--ablocks-cc-max-width'       => (int) $banner['max_width'] . 'px',
			'--ablocks-cc-prefs-max-width' => (int) $banner['prefs_max_width'] . 'px',
		];

		$out = '';
		foreach ( $vars as $name => $value ) {
			$out .= sprintf( '%s:%s;', $name, esc_attr( $value ) );
		}
		return $out;
	}

	public function render() {
		if ( ! Helper::should_render_banner() ) {
			return;
		}

		$banner  = Helper::get( 'banner', [] );
		$modal   = 'modal' === $banner['prefs_layout'];
		$classes = [
			'ablocks-consent',
			'ablocks-consent--' . sanitize_html_class( $banner['layout'] ),
			'ablocks-consent--' . sanitize_html_class( $banner['position'] ),
		];
		if ( ! empty( $banner['shadow'] ) ) {
			$classes[] = 'has-shadow';
		}
		?>
		<div
			id="ablocks-consent"
			class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>"
			style="<?php echo esc_attr( $this->style_vars() ); ?>"
			role="dialog"
			aria-modal="<?php echo ! empty( $banner['overlay'] ) ? 'true' : 'false'; ?>"
			aria-labelledby="ablocks-consent-title"
			aria-describedby="ablocks-consent-message"
			data-ablocks-consent-root="1"
			data-ablocks-consent-prefs="<?php echo $modal ? 'modal' : 'inline'; ?>"
			hidden
		>
			<?php if ( ! empty( $banner['overlay'] ) ) : ?>
				<div class="ablocks-consent__overlay"></div>
			<?php endif; ?>

			<div class="ablocks-consent__panel<?php echo ! empty( $banner['show_banner_close'] ) ? ' has-close' : ''; ?>">
				<?php
				/*
				 * Closing is not an answer, and it is not treated as one: the
				 * client either stores nothing at all or stores a refusal,
				 * depending on `close_behaviour`. Either way no category is
				 * released. It exists so that a visitor who will not accept —
				 * on a banner whose owner has turned Reject off — has something
				 * to do other than leave.
				 */
				if ( ! empty( $banner['show_banner_close'] ) ) :
					?>
					<button
						type="button"
						class="ablocks-consent__close ablocks-consent__close--banner"
						data-ablocks-consent-action="close-banner"
						aria-label="<?php esc_attr_e( 'Close without accepting', 'ablocks' ); ?>"
					>
						<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
							<path d="M6 6l12 12M18 6L6 18" />
						</svg>
					</button>
				<?php endif; ?>

				<div class="ablocks-consent__main">
					<div class="ablocks-consent__body">
						<?php if ( ! empty( $banner['title'] ) ) : ?>
							<p class="ablocks-consent__title" id="ablocks-consent-title">
								<?php echo esc_html( $banner['title'] ); ?>
							</p>
						<?php endif; ?>
						<div class="ablocks-consent__message" id="ablocks-consent-message">
							<?php echo wp_kses_post( wpautop( $banner['message'] ) ); ?>
							<?php $this->render_policy_links( $banner ); ?>
						</div>
					</div>

					<?php // Reject sits before Accept and carries the same weight of styling: a refusal that is harder to reach than a grant is not a free choice. ?>
					<div class="ablocks-consent__actions">
						<?php if ( ! empty( $banner['show_settings'] ) ) : ?>
							<button type="button" class="ablocks-consent__btn ablocks-consent__btn--<?php echo esc_attr( 'solid' === $banner['settings_style'] ? 'secondary' : $banner['settings_style'] ); ?>" data-ablocks-consent-action="settings">
								<?php echo esc_html( $banner['settings_label'] ); ?>
							</button>
						<?php endif; ?>
						<?php if ( ! empty( $banner['show_reject'] ) ) : ?>
							<button type="button" class="ablocks-consent__btn ablocks-consent__btn--secondary" data-ablocks-consent-action="reject">
								<?php echo esc_html( $banner['reject_label'] ); ?>
							</button>
						<?php endif; ?>
						<button type="button" class="ablocks-consent__btn ablocks-consent__btn--primary" data-ablocks-consent-action="accept">
							<?php echo esc_html( $banner['accept_label'] ); ?>
						</button>
					</div>
				</div>

				<?php
				// Inline preferences live in the banner's own panel and replace
				// its contents. Modal preferences are printed once, below, as a
				// dialog of their own — see render_prefs().
				if ( ! $modal ) :
					?>
					<div class="ablocks-consent__prefs" hidden>
						<?php $this->render_prefs( $banner ); ?>
					</div>
				<?php endif; ?>
			</div>
		</div>

		<?php if ( $modal ) : ?>
			<?php
			/*
			 * A separate root rather than a second panel inside the banner.
			 *
			 * The banner is positioned by the site owner — a bar across the
			 * bottom, a box in a corner — and the category list has to be
			 * readable wherever that is. Nesting it would inherit that
			 * position and that width; lifting it out lets a 380px corner
			 * notice open into a centred dialog, and lets the notice stay
			 * visible behind it, which is what a visitor expects from
			 * something called "Customize".
			 *
			 * It borrows the banner's own classes so every rule in the
			 * stylesheet applies unchanged; only the width and the stacking
			 * order are its own.
			 */
			$modal_classes = [
				'ablocks-consent',
				'ablocks-consent-modal',
				'ablocks-consent--center',
			];
			if ( ! empty( $banner['shadow'] ) ) {
				$modal_classes[] = 'has-shadow';
			}
			?>
			<div
				id="ablocks-consent-prefs"
				class="<?php echo esc_attr( implode( ' ', $modal_classes ) ); ?>"
				style="<?php echo esc_attr( $this->style_vars() ); ?>"
				role="dialog"
				aria-modal="true"
				aria-labelledby="ablocks-consent-prefs-title"
				hidden
			>
				<?php // Clicking away closes the dialog and returns to the banner. It records nothing: leaving a dialog is not an answer. ?>
				<div class="ablocks-consent__overlay" data-ablocks-consent-action="close-prefs"></div>
				<div class="ablocks-consent__panel">
					<div class="ablocks-consent__prefs">
						<?php $this->render_prefs( $banner ); ?>
					</div>
				</div>
			</div>
		<?php endif; ?>

		<?php if ( ! empty( $banner['reopen'] ) ) : ?>
			<?php // Withdrawal has to be as easy as granting, so the way back in is always on screen once a decision exists. ?>
			<button
				type="button"
				class="ablocks-consent-reopen ablocks-consent-reopen--<?php echo esc_attr( sanitize_html_class( $banner['reopen_position'] ) ); ?>"
				style="<?php echo esc_attr( $this->style_vars() ); ?>"
				data-ablocks-consent-action="open"
				hidden
			>
				<span class="ablocks-consent-reopen__icon" aria-hidden="true">
					<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
						<path d="M21.5 12a9.5 9.5 0 1 1-6.2-8.9 3.2 3.2 0 0 0 3.4 4.3 3.2 3.2 0 0 0 2.6 3.9c.1.2.2.5.2.7Z" />
						<circle cx="9" cy="10" r="1" fill="currentColor" stroke="none" />
						<circle cx="14.5" cy="15" r="1" fill="currentColor" stroke="none" />
						<circle cx="8.5" cy="15.5" r="1" fill="currentColor" stroke="none" />
					</svg>
				</span>
				<span class="ablocks-consent-reopen__label"><?php echo esc_html( $banner['reopen_label'] ); ?></span>
			</button>
		<?php endif; ?>
		<?php
	}

	/**
	 * The privacy and cookie policy links.
	 *
	 * Two of them because they are two documents: a cookie policy lists what is
	 * stored and for how long, a privacy policy says what happens to it. A
	 * banner that links only to the second is missing the one a visitor
	 * deciding about cookies actually wants.
	 *
	 * @param array $banner Banner settings.
	 */
	private function render_policy_links( $banner ) {
		$links = [
			[ $banner['policy_url'], $banner['policy_label'] ],
			[ $banner['cookie_policy_url'], $banner['cookie_policy_label'] ],
		];

		$out = '';
		foreach ( $links as $link ) {
			list( $url, $label ) = $link;
			if ( empty( $url ) || '' === trim( (string) $label ) ) {
				continue;
			}
			$out .= sprintf(
				'<a class="ablocks-consent__policy" href="%s">%s</a>',
				esc_url( $url ),
				esc_html( $label )
			);
		}

		if ( '' === $out ) {
			return;
		}

		// Wrapped so two links get something between them. Adjacent inline
		// anchors with no separator run together into one word.
		echo '<span class="ablocks-consent__policies">' . $out . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from esc_url/esc_html above.
	}

	/**
	 * The preferences body: heading, categories, and the buttons under them.
	 *
	 * Written once and called from either placement, so the inline panel and
	 * the modal cannot drift apart. Only its container differs between the two.
	 *
	 * @param array $banner Banner settings.
	 */
	private function render_prefs( $banner ) {
		$accordion  = ! empty( $banner['prefs_accordion'] );
		$open_first = $accordion && ! empty( $banner['prefs_open_first'] );
		$show_table = ! empty( $banner['show_cookie_table'] );
		$checkbox   = 'checkbox' === $banner['switch_style'];
		$badge      = 'badge' === $banner['locked_style'];
		$index      = 0;
		?>
		<div class="ablocks-consent__prefs-head">
			<p class="ablocks-consent__prefs-title" id="ablocks-consent-prefs-title">
				<?php echo esc_html( $banner['prefs_title'] ); ?>
			</p>
			<?php if ( ! empty( $banner['show_close'] ) ) : ?>
				<?php // Closes the preferences only. The banner stays, and no decision is written — there is nothing here that should be dismissible into a "yes". ?>
				<button
					type="button"
					class="ablocks-consent__close"
					data-ablocks-consent-action="close-prefs"
					aria-label="<?php esc_attr_e( 'Close', 'ablocks' ); ?>"
				>
					<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
						<path d="M6 6l12 12M18 6L6 18" />
					</svg>
				</button>
			<?php endif; ?>
		</div>

		<div class="ablocks-consent__prefs-intro">
			<?php echo wp_kses_post( wpautop( $banner['prefs_intro'] ) ); ?>
			<?php $this->render_policy_links( $banner ); ?>
		</div>

		<ul class="ablocks-consent__cats">
			<?php
			foreach ( Helper::active_categories() as $category ) :
				$index++;
				$panel_id = 'ablocks-consent-cat-' . sanitize_html_class( $category['slug'] );
				$is_open  = ! $accordion || ( 1 === $index && $open_first );
				$cookies  = $show_table && ! empty( $category['cookies'] ) ? (array) $category['cookies'] : [];
				$has_body = ! empty( $category['description'] ) || $cookies;
				?>
				<li class="ablocks-consent__cat<?php echo $accordion && $has_body ? ' is-collapsible' : ''; ?>">
					<div class="ablocks-consent__cat-head">
						<?php
						/*
						 * A button with aria-expanded rather than
						 * <details>/<summary>: the disclosure sits in the same
						 * row as the category's own switch, and a summary with
						 * an interactive control inside it is both invalid and
						 * a known screen-reader trap.
						 */
						if ( $accordion && $has_body ) :
							?>
							<button
								type="button"
								class="ablocks-consent__cat-toggle"
								data-ablocks-consent-action="toggle-category"
								aria-expanded="<?php echo $is_open ? 'true' : 'false'; ?>"
								aria-controls="<?php echo esc_attr( $panel_id ); ?>"
							>
								<span class="ablocks-consent__cat-arrow" aria-hidden="true">
									<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
										<path d="M9 6l6 6-6 6" />
									</svg>
								</span>
								<span class="ablocks-consent__cat-label"><?php echo esc_html( $category['label'] ); ?></span>
							</button>
						<?php else : ?>
							<span class="ablocks-consent__cat-label"><?php echo esc_html( $category['label'] ); ?></span>
						<?php endif; ?>

						<?php if ( ! empty( $category['locked'] ) ) : ?>
							<span class="ablocks-consent__cat-locked<?php echo $badge ? ' is-badge' : ''; ?>">
								<?php echo esc_html( $banner['locked_label'] ); ?>
							</span>
						<?php else : ?>
							<label class="ablocks-consent__switch<?php echo $checkbox ? ' ablocks-consent__switch--checkbox' : ''; ?>">
								<input
									type="checkbox"
									data-ablocks-consent-category="<?php echo esc_attr( $category['slug'] ); ?>"
								/>
								<span class="<?php echo $checkbox ? 'ablocks-consent__switch-box' : 'ablocks-consent__switch-track'; ?>" aria-hidden="true"></span>
								<span class="screen-reader-text"><?php echo esc_html( $category['label'] ); ?></span>
							</label>
						<?php endif; ?>
					</div>

					<?php if ( $has_body ) : ?>
						<div
							class="ablocks-consent__cat-panel"
							id="<?php echo esc_attr( $panel_id ); ?>"
							<?php echo $is_open ? '' : 'hidden'; ?>
						>
							<?php if ( ! empty( $category['description'] ) ) : ?>
								<p class="ablocks-consent__cat-desc"><?php echo esc_html( $category['description'] ); ?></p>
							<?php endif; ?>
							<?php if ( $cookies ) : ?>
								<?php $this->render_cookie_table( $cookies ); ?>
							<?php endif; ?>
						</div>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ul>

		<div class="ablocks-consent__prefs-actions">
			<?php if ( ! empty( $banner['show_prefs_reject'] ) ) : ?>
				<button type="button" class="ablocks-consent__btn ablocks-consent__btn--secondary" data-ablocks-consent-action="reject">
					<?php echo esc_html( $banner['reject_label'] ); ?>
				</button>
			<?php endif; ?>
			<button type="button" class="ablocks-consent__btn ablocks-consent__btn--save" data-ablocks-consent-action="save">
				<?php echo esc_html( $banner['save_label'] ); ?>
			</button>
			<?php if ( ! empty( $banner['show_prefs_accept'] ) ) : ?>
				<button type="button" class="ablocks-consent__btn ablocks-consent__btn--primary" data-ablocks-consent-action="accept">
					<?php echo esc_html( $banner['prefs_accept_label'] ); ?>
				</button>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * One category's cookie disclosure.
	 *
	 * A real table, because it is tabular and because a visitor reading it with
	 * a screen reader needs the column a cell belongs to announced with it.
	 *
	 * @param array $cookies Cookie rows.
	 */
	private function render_cookie_table( $cookies ) {
		?>
		<div class="ablocks-consent__cookies-scroll">
			<table class="ablocks-consent__cookies">
				<thead>
					<tr>
						<th scope="col"><?php esc_html_e( 'Cookie', 'ablocks' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Provider', 'ablocks' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Expires', 'ablocks' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Purpose', 'ablocks' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $cookies as $cookie ) : ?>
						<tr>
							<td><code><?php echo esc_html( isset( $cookie['name'] ) ? $cookie['name'] : '' ); ?></code></td>
							<td><?php echo esc_html( isset( $cookie['provider'] ) ? $cookie['provider'] : '' ); ?></td>
							<td><?php echo esc_html( isset( $cookie['duration'] ) ? $cookie['duration'] : '' ); ?></td>
							<td><?php echo esc_html( isset( $cookie['purpose'] ) ? $cookie['purpose'] : '' ); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<?php
	}

	/**
	 * `[ablocks_cookie_preferences]` — a link that reopens the preferences,
	 * for a privacy policy page.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public function preferences_shortcode( $atts ) {
		$atts = shortcode_atts(
			[
				'label' => Helper::banner( 'settings_label', __( 'Cookie preferences', 'ablocks' ) ),
				'class' => '',
			],
			$atts,
			'ablocks_cookie_preferences'
		);

		return sprintf(
			'<button type="button" class="ablocks-consent-link %s" data-ablocks-consent-action="open">%s</button>',
			esc_attr( $atts['class'] ),
			esc_html( $atts['label'] )
		);
	}
}
