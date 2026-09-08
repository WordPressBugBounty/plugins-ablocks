<?php
namespace ABlocks\Classes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ABlocks\Helper;

/**
 * Reusable CSS class library (Tailwind-style + user-created classes).
 *
 * A class is a record { id, label, css } stored in the `ablocks_global_classes`
 * option. Blocks reference classes by id (never copying the styles), so editing
 * one class updates every block using it. The compiled `.ablocks-gc-{id}{css}`
 * rules are enqueued once on the front end and in the editor.
 *
 * This is the v1 storage layer for the atomic class system — `css` is a raw
 * declaration string for now; it will grow into structured variants
 * (breakpoint/state) as the system matures.
 */
class GlobalClasses {

	const OPTION = 'ablocks_global_classes';
	const HANDLE = 'ablocks-global-classes';

	/**
	 * Timestamp of the last change to the library.
	 *
	 * A generated page stylesheet embeds the compiled CSS of the classes that
	 * page uses, so editing a class has to make every one of those files stale.
	 * Comparing this stamp against a file's mtime does that without writing
	 * anything into the file or its name (see Assets::is_assets_generated).
	 */
	const REV_OPTION = 'ablocks_global_classes_rev';

	/** Compiled whole-library CSS, kept until the revision moves. */
	const CSS_TRANSIENT = 'ablocks_global_classes_css';

	/** One-time marker for the autoload repair below. */
	const AUTOLOAD_FIXED = 'ablocks_global_classes_autoload_fixed';

	/**
	 * Cross-request mutex for the library.
	 *
	 * Every write is a read-modify-write of one option holding the whole list,
	 * and the editor makes them constantly — a debounced save per style change,
	 * from every open block and every open tab. Two of those overlapping used to
	 * mean the second request read the list before the first had written its
	 * copy back, so the first one's class simply vanished. On a slow connection,
	 * where a request can be in flight for many seconds, a single stale write
	 * could take the whole afternoon's classes with it.
	 */
	const LOCK_OPTION = 'ablocks_global_classes.lock';

	/** Seconds after which a held lock is assumed to belong to a dead request. */
	const LOCK_TIMEOUT = 20;

	/** How long a writer waits for the lock before giving up on it. */
	const LOCK_WAIT = 5;

	/**
	 * Whether the compiled CSS has already been attached this request.
	 *
	 * Both enqueue hooks can fire in one request, and core fires
	 * `enqueue_block_assets` from inside `wp_enqueue_scripts` — which attached
	 * the entire stylesheet twice on every front-end page.
	 *
	 * @var bool
	 */
	private static $enqueued = false;

	public static function init() {
		$self = new self();
		add_action( 'wp_ajax_ablocks/global_classes/get', [ $self, 'ajax_get' ] );
		add_action( 'wp_ajax_ablocks/global_classes/save', [ $self, 'ajax_save' ] );
		add_action( 'wp_ajax_ablocks/global_classes/delete', [ $self, 'ajax_delete' ] );

		// Compiled class CSS on the front end and in the editor canvas. The two
		// hooks are not interchangeable and must not both run on one request:
		// `enqueue_block_assets` is what reaches the editor's iframed canvas,
		// while the front-end pass runs on `wp_enqueue_scripts` at 100 — after
		// Assets::enqueue_frontend_assets (99) — so it can see whether the
		// generated page stylesheet already carries these rules.
		add_action( 'wp_enqueue_scripts', [ $self, 'enqueue_css' ], 100 );
		add_action( 'enqueue_block_assets', [ $self, 'enqueue_editor_css' ], 100 );

		add_action( 'admin_init', [ $self, 'maybe_drop_autoload' ] );
	}

	public static function get_all() {
		$raw = get_option( self::OPTION, '[]' );
		$data = json_decode( (string) $raw, true );
		return is_array( $data ) ? array_values( $data ) : [];
	}

	private function persist( array $classes ) {
		$json = wp_json_encode( array_values( $classes ) );

		// A save that changes nothing is common — renaming a class to the name
		// it already has, or a debounced style write that lands after an
		// identical one. Bumping the revision for it would expire the compiled
		// CSS and stale every generated page stylesheet on the site for no
		// reason at all.
		if ( (string) get_option( self::OPTION, '[]' ) === $json ) {
			return false;
		}

		// Explicitly never autoloaded. WordPress only excludes an option on its
		// own above ~150 KB, which leaves a library of a few hundred classes
		// loaded on every request — including admin-ajax, REST and cron, none
		// of which render a block.
		update_option( self::OPTION, $json, false );
		self::bump_revision();
		return true;
	}

	/**
	 * Take the library mutex.
	 *
	 * `INSERT IGNORE` on the options table is the only primitive available here
	 * that is atomic across concurrent requests — `option_name` is unique, so
	 * exactly one of them can create the row. `add_option()` cannot stand in for
	 * it: it reads and then writes, which is the very race this closes. Core
	 * takes its upgrade lock the same way (see WP_Upgrader::create_lock).
	 *
	 * A caller that cannot get the lock still writes. Losing a class to a race
	 * is much worse than the theoretical risk of one, and a lock this short is
	 * only ever unavailable because another writer is mid-flight.
	 *
	 * @return bool Whether the lock is now held by this request.
	 */
	private static function acquire_lock() {
		global $wpdb;

		$deadline = microtime( true ) + self::LOCK_WAIT;

		do {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery -- atomicity is the point; no cache to prime.
			$acquired = $wpdb->query(
				$wpdb->prepare(
					"INSERT IGNORE INTO `$wpdb->options` ( `option_name`, `option_value`, `autoload` ) VALUES (%s, %s, 'off') /* LOCK */",
					self::LOCK_OPTION,
					time()
				)
			);

			if ( $acquired ) {
				return true;
			}

			// Held by someone else — or left behind by a request that died
			// between taking the lock and releasing it, in which case nothing
			// but the timeout will ever clear it.
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery -- must not read a cached copy of a lock.
			$held = (int) $wpdb->get_var(
				$wpdb->prepare( "SELECT option_value FROM `$wpdb->options` WHERE option_name = %s", self::LOCK_OPTION )
			);

			if ( ! $held || ( time() - $held ) > self::LOCK_TIMEOUT ) {
				self::release_lock();
				continue;
			}

			usleep( 50000 );
		} while ( microtime( true ) < $deadline );

		return false;
	}

	private static function release_lock() {
		delete_option( self::LOCK_OPTION );
	}

	/**
	 * Read the library past any cached copy.
	 *
	 * Inside the lock the point is to see what the request we just waited for
	 * actually wrote, which a value cached earlier in this request would hide.
	 */
	private static function get_all_fresh() {
		wp_cache_delete( self::OPTION, 'options' );
		// And the "this option does not exist" cache: on a site with no library
		// yet, the first read caches its absence, and the request we waited for
		// is very likely the one that just created it.
		wp_cache_delete( 'notoptions', 'options' );
		return self::get_all();
	}

	/**
	 * Mark the library as changed: expires the compiled-CSS cache and makes
	 * every generated page stylesheet stale, so they rebuild on next visit.
	 */
	public static function bump_revision() {
		$now = time();
		update_option( self::REV_OPTION, $now, true );
		delete_transient( self::CSS_TRANSIENT );
		return $now;
	}

	public static function get_revision() {
		return (int) get_option( self::REV_OPTION, 0 );
	}

	/**
	 * One-time repair for a library saved before persist() passed the autoload
	 * flag explicitly.
	 */
	public function maybe_drop_autoload() {
		if ( get_option( self::AUTOLOAD_FIXED ) ) {
			return;
		}
		if ( function_exists( 'wp_set_option_autoload' ) ) {
			wp_set_option_autoload( self::OPTION, false );
		}
		update_option( self::AUTOLOAD_FIXED, 1, true );
	}

	private function sanitize_id( $id ) {
		return sanitize_html_class( $id );
	}

	/**
	 * Insert or update a class. Returns the stored record. Pass a structured
	 * $styles array ({normal,hover}) for controls-editable classes, or a raw
	 * $css declaration string (legacy).
	 */
	public function upsert( $id, $label, $css = '', $styles = null ) {
		$id = $this->sanitize_id( $id );
		if ( '' === $id ) {
			return null;
		}

		$record = null;
		$locked = self::acquire_lock();

		try {
			// Read INSIDE the lock. Reading first and locking afterwards would
			// leave exactly the window this is here to close.
			$classes = self::get_all_fresh();
			$index   = $this->index_of( $classes, $id );
			$existing = null === $index ? null : $classes[ $index ];
			$record  = $this->build_record( $id, $label, $css, $styles, $existing );

			if ( null === $index ) {
				$classes[] = $record;
			} else {
				$classes[ $index ] = $record;
			}

			$this->persist( $classes );
		} finally {
			if ( $locked ) {
				self::release_lock();
			}
		}

		return $record;
	}

	/**
	 * Where a class sits in the list, or null.
	 *
	 * @param array  $classes The library.
	 * @param string $id      Class id.
	 * @return int|null The index, or null when the class is new.
	 */
	private function index_of( array $classes, $id ) {
		foreach ( $classes as $i => $c ) {
			if ( isset( $c['id'] ) && $c['id'] === $id ) {
				return $i;
			}
		}
		return null;
	}

	/**
	 * The record to store for a class.
	 *
	 * @param string     $id       Class id.
	 * @param string     $label    Display label.
	 * @param string     $css      Raw declaration string (legacy form).
	 * @param array|null $styles   Structured styles, when the caller sent them.
	 * @param array|null $existing The record already stored, if any.
	 * @return array The record.
	 */
	private function build_record( $id, $label, $css, $styles, $existing ) {
		$record = [
			'id'    => $id,
			'label' => sanitize_text_field( $label ),
		];

		if ( is_array( $styles ) ) {
			$record['styles'] = $styles;
			return $record;
		}

		if ( '' !== trim( (string) $css ) ) {
			// Declaration string only — strip braces/at-rules so a class can't
			// break out of its own selector.
			$record['css'] = trim( preg_replace( '/[{}<>]/', '', (string) $css ) );
			return $record;
		}

		// Neither form came in, so this is a save that only touches the label.
		// The record is rebuilt from scratch here, so carrying the look over
		// explicitly is what stops a rename from emptying the class of
		// everything it styles.
		if ( isset( $existing['styles'] ) ) {
			$record['styles'] = $existing['styles'];
		} elseif ( isset( $existing['css'] ) ) {
			$record['css'] = $existing['css'];
		} else {
			$record['css'] = '';
		}

		return $record;
	}

	public function remove( $id ) {
		$id     = $this->sanitize_id( $id );
		$locked = self::acquire_lock();

		try {
			$classes = array_filter( self::get_all_fresh(), function ( $c ) use ( $id ) {
				return ! ( isset( $c['id'] ) && $c['id'] === $id );
			} );
			$this->persist( $classes );
		} finally {
			if ( $locked ) {
				self::release_lock();
			}
		}
	}

	/**
	 * Compile the library, or only the classes named in $ids.
	 *
	 * Order follows the LIBRARY, not $ids: when two classes on one element set
	 * the same property, library order decides the winner on the front end, and
	 * the editor preview mirrors that (see buildPreviewCss). Emitting in caller
	 * order would let a block preview differently from the published page.
	 *
	 * @param array|null $ids Class ids to compile, or null for the whole library.
	 */
	public static function compiled_css_for( $ids = null ) {
		$wanted = null;
		if ( is_array( $ids ) ) {
			if ( empty( $ids ) ) {
				return '';
			}
			$wanted = array_flip( array_map( 'strval', $ids ) );
		}

		$css = '';
		foreach ( self::get_all() as $c ) {
			if ( empty( $c['id'] ) ) {
				continue;
			}
			$id = $c['id'];
			if ( null !== $wanted && ! isset( $wanted[ $id ] ) ) {
				continue;
			}
			if ( ! empty( $c['styles'] ) && is_array( $c['styles'] ) ) {
				$css .= AtomicStyles::compile_variants( '.ablocks-gc-' . $id, $c['styles'] );
			} elseif ( ! empty( $c['css'] ) ) {
				$css .= '.ablocks-gc-' . $id . '{' . $c['css'] . '}';
			}
		}
		return $css;
	}

	public function compiled_css() {
		return self::compiled_css_for( null );
	}

	/**
	 * The whole library, compiled once per revision.
	 *
	 * This path cannot know which classes the page uses — that needs the block
	 * tree, and only the asset generator walks it — so it still emits every
	 * class. Caching the result at least stops each uncached request from
	 * recompiling byte-identical CSS.
	 */
	private static function cached_css() {
		$rev    = self::get_revision();
		$cached = get_transient( self::CSS_TRANSIENT );

		if ( is_array( $cached ) && isset( $cached['rev'], $cached['css'] ) && (int) $cached['rev'] === $rev ) {
			return (string) $cached['css'];
		}

		$css = self::compiled_css_for( null );
		set_transient(
			self::CSS_TRANSIENT,
			[
				'rev' => $rev,
				'css' => $css,
			],
			WEEK_IN_SECONDS
		);

		return $css;
	}

	/** The editor canvas copy; the front end is served from enqueue_css(). */
	public function enqueue_editor_css() {
		if ( ! is_admin() ) {
			return;
		}
		$this->enqueue_css();
	}

	public function enqueue_css() {
		if ( self::$enqueued ) {
			return;
		}

		// When the generated page stylesheet is in play it already contains the
		// classes this page uses, compiled by AssetsGenerator. A second, whole-
		// library copy inline would be pure weight.
		if ( ! is_admin() && wp_style_is( 'ablocks-blocks-combine-style', 'enqueued' ) ) {
			self::$enqueued = true;
			return;
		}

		$css = self::cached_css();
		if ( '' === $css ) {
			return;
		}

		self::$enqueued = true;

		if ( ! wp_style_is( self::HANDLE, 'registered' ) ) {
			wp_register_style( self::HANDLE, false, [], ABLOCKS_VERSION );
		}
		wp_enqueue_style( self::HANDLE );
		wp_add_inline_style( self::HANDLE, $css );
	}

	// ---- AJAX ----

	private function verify() {
		check_ajax_referer( 'ablocks_nonce', 'security' );
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( [ 'message' => 'forbidden' ], 403 );
		}
	}

	public function ajax_get() {
		// Same gate as save/delete below. Reading the library is not sensitive,
		// but leaving one of the three endpoints open to any logged-in user is
		// the kind of inconsistency that turns into a hole when the payload
		// grows.
		$this->verify();
		wp_send_json_success( self::get_all() );
	}

	private function sanitize_styles( $value ) {
		if ( is_array( $value ) ) {
			$out = [];
			foreach ( $value as $k => $v ) {
				$out[ sanitize_text_field( $k ) ] = $this->sanitize_styles( $v );
			}
			return $out;
		}
		return sanitize_text_field( (string) $value );
	}

	public function ajax_save() {
		$this->verify();
		$id    = isset( $_POST['id'] ) ? sanitize_text_field( wp_unslash( $_POST['id'] ) ) : '';
		$label = isset( $_POST['label'] ) ? sanitize_text_field( wp_unslash( $_POST['label'] ) ) : '';
		$css   = isset( $_POST['css'] ) ? wp_unslash( $_POST['css'] ) : '';

		$styles = null;
		if ( isset( $_POST['styles'] ) ) {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- sanitize_styles() walks the decoded tree.
			$decoded = json_decode( wp_unslash( $_POST['styles'] ), true );

			// A styles payload that will not decode is a truncated or corrupted
			// request, not an instruction to empty the class. Saving it anyway
			// used to replace everything the class styled with nothing.
			if ( ! is_array( $decoded ) ) {
				wp_send_json_error( [ 'message' => 'invalid styles' ], 400 );
			}

			$styles = $this->sanitize_styles( $decoded );
		}

		$record = $this->upsert( $id, $label, $css, $styles );
		if ( ! $record ) {
			wp_send_json_error( [ 'message' => 'invalid id' ], 400 );
		}
		wp_send_json_success( $record );
	}

	public function ajax_delete() {
		$this->verify();
		$id = isset( $_POST['id'] ) ? sanitize_text_field( wp_unslash( $_POST['id'] ) ) : '';
		$this->remove( $id );
		wp_send_json_success();
	}
}
