<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

\ABlocks\Helper::get_template( 'email/template-header.php' );
?>
<div class="ablocks-container">
	<div class="ablocks-content"> 
		<div class="ablocks-wrapper">
			<h5 class="ablocks-main-heading"><?php esc_html_e( 'New Contact Form Submission', 'ablocks' ); ?></h5>
			<div class="ablocks-entry-content">
				<h2><?php esc_html_e( 'You have a new contact form submission!', 'ablocks' ); ?></h2>
				<p><strong><?php esc_html_e( 'Email:', 'ablocks' ); ?></strong> <?php echo esc_html( $email ); ?></p>
				<p><strong><?php esc_html_e( 'Message:', 'ablocks' ); ?></strong></p>
				<p><?php echo wp_kses_post( $message ); ?></p>
				<p><a href="<?php echo esc_url( get_admin_url() ); ?>"><?php esc_html_e( 'Login to your admin panel to view the submission.', 'ablocks' ); ?></a></p>
			</div>
			<div class="ablocks-footer">
				&copy; <?php
						$url = wp_parse_url( get_bloginfo( 'url' ) );
						echo sanitize_text_field( $url['host'] . ':' . $url['port'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				?>
				<?php // echo wp_kses_post( $footer );
					// phpcs::ignore Squiz.PHP.CommentedOutCode.Found
				?>
			</div>
		</td>
	</div>
</div>
<?php
	\ABlocks\Helper::get_template( 'email/template-footer.php' );
