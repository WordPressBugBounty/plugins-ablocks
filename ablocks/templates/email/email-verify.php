<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

	\ABlocks\Helper::get_template( 'email/template-header.php' );
?>
<div class="ablocks-container">
	<div class="ablocks-content"> 
		<div class="ablocks-wrapper">
			<h5 class="ablocks-main-heading"><?php esc_html_e( 'Email verification', 'ablocks' ); ?></h5>
			<div class="ablocks-entry-content">
				<p><?php esc_html_e( 'Hello', 'ablocks' ); ?> <?php echo esc_html( $email ); ?>,</p>
				<p><?php esc_html_e( 'Thank you for registering with us. Please click the link below to verify your email address:', 'ablocks' ); ?></p>
				<p><a href="<?php echo esc_url( $url ); ?>"><?php esc_html_e( 'Verify Your Email', 'ablocks' ); ?></a></p>
				<p><?php esc_html_e( 'If you did not register, please ignore this email.', 'ablocks' ); ?></p>
				<p><?php esc_html_e( 'Best regards', 'ablocks' ); ?>,<br><?php echo esc_attr( get_bloginfo( 'name' ) ); ?></p>
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
