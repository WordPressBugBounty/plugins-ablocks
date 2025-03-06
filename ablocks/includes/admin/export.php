<?php

namespace ABlocks\admin;

class Export {

	public static function init() {
		$self = new self();
		add_action( 'rss2_head', [ $self, 'add_options_to_rss' ] );
		add_action( 'export_filters', [ $self, 'add_patterns_radio' ] );
	}

	/**
	 * Add options to WXR.
	 *
	 * @return void
	 */
	public function add_options_to_rss() {
		global $pagenow;
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Don't need nonce verification here, we're just checking if content's value.
		if ( 'export.php' !== $pagenow || ! isset( $_GET['content'] ) || 'all' !== sanitize_text_field( wp_unslash( $_GET['content'] ) ) ) {
			return;
		}

		$options = array(
			'show_on_front'  => get_option( 'show_on_front', 'posts' ),
			'page_on_front'  => get_option( 'page_on_front', 0 ),
			'page_for_posts' => get_option( 'page_for_posts', 0 ),
		);

		$custom_data_xml = '<ablocks_options>';
		foreach ( $options as $key => $value ) {
			$custom_data_xml .= sprintf(
				'<%1$s>%2$s</%1$s>',
				esc_xml( $key ),
				esc_xml( $value )
			);
		}
		$custom_data_xml .= '</ablocks_options>';

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Want to put valid xml in RSS head.
		echo $custom_data_xml;
	}

	/**
	 * Display "Patterns" radio in export page.
	 *
	 * @return void
	 */
	public function add_patterns_radio() {
		?>
		<p>
			<label>
				<input type="radio" name="content" value="wp_block" />
				<?php esc_html_e( 'Patterns', 'ablocks' ); ?>
			</label>
		</p>
		<?php
	}

}
