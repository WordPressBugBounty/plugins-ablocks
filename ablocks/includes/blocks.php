<?php
namespace ABlocks;

if ( ! defined( 'ABSPATH' ) ) {
	exit(); // Exit if accessed directly.
}

use ABlocks\Classes\AssetsGenerator;
use ABlocks\Helper;
use ABlocks\Classes\FileUpload;
use ABlocks\Blocks\FormBuilder\BlockAttrSanitizer\Sanitizer as BlockSanitizer;
use ABlocks\Blocks\FormBuilder\Helper as FormBuilderHelper;

class Blocks {
	public static function init() {
		$self = new self();
		// block initialization
		add_action( 'init', [ $self, 'blocks_init' ] );
		add_action( 'enqueue_block_assets', [ $self, 'load_academy_core_scripts' ] );
		add_filter( 'block_categories_all', [ $self, 'register_block_category' ], 10, 2 );
		// Assets Generator
		add_action( 'save_post', [ $self, 'generate_block_assets' ], 10, 3 );

		$self->register_block_sanitizer();
		$self->form_builder_default_data();
	}

	public function blocks_init() {
		if ( Helper::is_plugin_active( 'academy/academy.php' ) ) {
			add_filter( 'academy/is_load_common_scripts', '__return_true' );
			if ( Helper::is_gutenberg_editor() ) {
				add_filter( 'academy/is_load_common_js_scripts', '__return_false' );
			}
			new \ABlocks\Blocks\AcademyCourses\Block();
			new \ABlocks\Blocks\AcademyEnrollForm\Block();
			new \ABlocks\Blocks\AcademyStudentRegistrationForm\Block();
			new \ABlocks\Blocks\AcademyCourseSearch\Block();
			new \ABlocks\Blocks\AcademyInstructorRegistrationForm\Block();
			new \ABlocks\Blocks\AcademyPdf\Block();
			new \ABlocks\Blocks\AcademyPasswordResetForm\Block();
			new \ABlocks\Blocks\AcademyLoginForm\Block();

			if ( ( helper::is_gutenberg_editor() && Helper::check_post_type_from_admin( 'academy_certificate' ) ) || ( ! helper::is_gutenberg_editor() && ! is_admin() ) ) {
				new \ABlocks\Blocks\AcademyCertificate\Block();
				new \ABlocks\Blocks\AcademyCertificateText\Block();
				if ( Helper::is_plugin_active( 'academy-pro/academy-pro.php' ) ) {
					new \ABlocks\Blocks\AcademyCertificateId\Block();
				}
			}
		}//end if
		new \ABlocks\Blocks\Container\Block();
		new \ABlocks\Blocks\Heading\Block();
		new \ABlocks\Blocks\Paragraph\Block();
		new \ABlocks\Blocks\Image\Block();
		new \ABlocks\Blocks\Button\Block();
		new \ABlocks\Blocks\DualButton\Block();
		new \ABlocks\Blocks\Icon\Block();
		new \ABlocks\Blocks\InfoBox\Block();
		new \ABlocks\Blocks\Lists\Block();
		new \ABlocks\Blocks\Counter\Block();
		new \ABlocks\Blocks\StarRatings\Block();
		new \ABlocks\Blocks\Divider\Block();
		new \ABlocks\Blocks\Spacer\Block();
		new \ABlocks\Blocks\Video\Block();
		new \ABlocks\Blocks\Search\Block();
		new \ABlocks\Blocks\Carousel\Block();
		new \ABlocks\Blocks\CarouselChild\Block();
		new \ABlocks\Blocks\Toggle\Block();
		new \ABlocks\Blocks\ToggleChild\Block();
		new \ABlocks\Blocks\Accordion\Block();
		new \ABlocks\Blocks\SingleAccordion\Block();
		new \ABlocks\Blocks\Tabs\Block();
		new \ABlocks\Blocks\TabsChild\Block();
		new \ABlocks\Blocks\Countdown\Block();
		new \ABlocks\Blocks\NewsTicker\Block();
		new \ABlocks\Blocks\ImageHotspot\Block();
		new \ABlocks\Blocks\ImageHotspotChild\Block();
		new \ABlocks\Blocks\ProgressTracker\Block();
		new \ABlocks\Blocks\ImageScroll\Block();
		new \ABlocks\Blocks\Menu\Block();
		new \ABlocks\Blocks\MenuItem\Block();
		new \ABlocks\Blocks\MenuChildSub\Block();
		new \ABlocks\Blocks\MenuChildMega\Block();
		new \ABlocks\Blocks\StripeButton\Block();
		new \ABlocks\Blocks\PaypalButton\Block();

		new \ABlocks\Blocks\Table\Block();
		new \ABlocks\Blocks\TableCell\Block();
		new \ABlocks\Blocks\TableRow\Block();
		new \ABlocks\Blocks\TableHeader\Block();
		new \ABlocks\Blocks\TableFooter\Block();
		new \ABlocks\Blocks\TableBody\Block();

		new \ABlocks\Blocks\Coupon\Block();
		new \ABlocks\Blocks\ContentTimeline\Block();
		new \ABlocks\Blocks\ContentTimelineChild\Block();
		new \ABlocks\Blocks\Map\Block();
		new \ABlocks\Blocks\TableOfContent\Block();
		new \ABlocks\Blocks\Modal\Block();
		new \ABlocks\Blocks\ModalTrigger\Block();
		new \ABlocks\Blocks\ModalPanel\Block();
		new \ABlocks\Blocks\ImageComparison\Block();
		new \ABlocks\Blocks\FlipBox\Block();
		new \ABlocks\Blocks\FlipBoxChild\Block();
		new \ABlocks\Blocks\PriceMenu\Block();
		new \ABlocks\Blocks\PriceMenuItem\Block();
		new \ABlocks\Blocks\SocialShares\Block();
		new \ABlocks\Blocks\Notice\Block();
		new \ABlocks\Blocks\SvgDraw\Block();
		// new \ABlocks\Blocks\Marquee\Block();
		// new \ABlocks\Blocks\MarqueeChild\Block();
		new \ABlocks\Blocks\Logout\Block();
		new \ABlocks\Blocks\FilterableCards\Block();
		new \ABlocks\Blocks\FilterableCardsItem\Block();
		new \ABlocks\Blocks\AdvanceLists\Block();
		new \ABlocks\Blocks\AdvanceListItem\Block();
		// Form Builder
		new \ABlocks\Blocks\FormBuilder\Block();
		new \ABlocks\Blocks\FormInput\Block();
		new \ABlocks\Blocks\FormPassword\Block();
		new \ABlocks\Blocks\FormTextarea\Block();
		new \ABlocks\Blocks\FormCheckbox\Block();
		new \ABlocks\Blocks\FormSelect\Block();
		new \ABlocks\Blocks\FormMultiStep\Block();
		new \ABlocks\Blocks\FormMultiStepChild\Block();
		new \ABlocks\Blocks\FormRadio\Block();
	}

	public function register_block_category( $categories, $post ) {
		return array_merge(
			[
				[
					'slug' => 'ablocks',
					'title' => __( 'ABlocks', 'ablocks' ),
				],
				[
					'slug' => 'academy',
					'title' => __( 'Academy LMS', 'ablocks' ),
				],
			],
			$categories
		);
	}

	public function load_academy_core_scripts() {
		if ( ! Helper::is_plugin_active( 'academy/academy.php' ) || ! is_admin() ) {
			return;
		}
		$ScriptsBase = new \Academy\Assets();
		$ScriptsBase->frontend_common_assets();
	}

	public function generate_block_assets( $post_id, $post, $update ) {
		if ( ! Helper::is_enabled_assets_generation() ) {
			return;
		}

		if ( isset( $post->post_status ) && 'auto-draft' === $post->post_status ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( false !== wp_is_post_revision( $post_id ) ) {
			return;
		}

		// Don't save FSE theme assets
		if ( ( function_exists( 'wp_is_block_theme' ) && wp_is_block_theme() ) ) {
			return;
		}

		AssetsGenerator::write_frontend_css_in_uploads_folder( $post_id );
	}

	public function form_builder_default_data(): void {
		add_filter(
			'ablocks/assets/editor_scripts_data',
			[ FormBuilderHelper::class, 'form_builder_default_data' ]
		);
	}
	public function register_block_sanitizer(): void {
		BlockSanitizer::init();
	}
}
