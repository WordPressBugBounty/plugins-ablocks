<?php

namespace ABlocksLinkGuard;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use ABlocks\Interfaces\AddonInterface;
use ABlocks\Classes\CacheBackend;

/**
 * Link Guard addon.
 *
 * Writers link to posts that are scheduled but not live yet. Until the target
 * publishes, a visitor who follows that link gets an error page. This addon
 * takes those links out of the rendered content — the anchor text stays, the
 * `<a>` goes — and puts them back the moment the target goes live.
 *
 * Nothing is rewritten in the database. The decision is made at render time
 * from the target's current status, so publishing, unpublishing, trashing or
 * making a post private all take effect without touching the posts that link
 * to it. What *is* stored is only the expensive part: which post a URL points
 * at (see `Frontend::lookup()`).
 *
 * The rendered HTML never depends on who is viewing, so page caches stay valid.
 * The one thing a page cache cannot see is a *different* post changing status,
 * which is why `Invalidation` keeps a reverse index and announces the posts
 * whose output just changed.
 */
final class LinkGuard implements AddonInterface {

	private $addon_name = 'link-guard';

	private function __construct() {
		$this->define_constants();
		$this->init_addon();
	}

	public function define_constants() {
		define( 'ABLOCKS_LINK_GUARD_VERSION', '1.0' );
		define( 'ABLOCKS_LINK_GUARD_ADDON_NAME', $this->addon_name );
		define( 'ABLOCKS_LINK_GUARD_GENERATION_OPTION', 'ablocks_link_guard_generation' );
	}

	public function init_addon() {
		add_action( "ablocks/addons/activated_{$this->addon_name}", [ $this, 'addon_activation_hook' ] );

		if ( ! \ABlocks\Helper::get_addon_active_status( $this->addon_name ) ) {
			return;
		}

		// Status changes happen in the admin, over REST and in cron (a scheduled
		// post publishes from wp-cron), so invalidation listens everywhere.
		Invalidation::init();

		if ( ! is_admin() || wp_doing_ajax() ) {
			Frontend::init();
		}
	}

	public static function init() {
		static $instance = false;

		if ( ! $instance ) {
			$instance = new self();
		}

		return $instance;
	}

	/**
	 * While the addon was off, nothing tracked posts being created or renamed,
	 * so any "this URL is not a post" answer stored before then may be stale.
	 */
	public function addon_activation_hook() {
		CacheBackend::bump_generation( ABLOCKS_LINK_GUARD_GENERATION_OPTION );
	}
}
