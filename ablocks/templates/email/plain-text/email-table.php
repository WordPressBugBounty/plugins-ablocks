<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$label = str_pad( esc_html__( 'Label', 'ablocks' ), 20, ' ' ); // Prepare raw string first.
$value = esc_html__( 'Value', 'ablocks' );

echo "\n\n\n" . esc_html( $label . $value ) . "\n";
echo "-------------------------------------\n";

// Ensure $data is an array to avoid errors
if ( ! empty( $data ) && is_array( $data ) ) {
	foreach ( $data as $input_id => $attr ) {
		$label_text = ucfirst( preg_replace( '/[_-]/m', ' ', $input_id ) );
		$label      = str_pad( esc_html( $label_text ), 18, ' ' );
		$value      = isset( $attr['value'] ) ? esc_html( $attr['value'] ) : '';

		// Output the formatted label and value
		echo esc_html( $label . ': ' . $value ) . "\n";
	}
}
