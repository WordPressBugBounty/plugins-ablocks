<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<table>
<tr>
	<th style="text-align:center;padding:5px;background:gray;color:white"><?php esc_html_e( 'Label', 'ablocks' ); ?></th>
	<th style="text-align:center;padding:5px;background:gray;color:white"><?php esc_html_e( 'Value', 'ablocks' ); ?></th>
</tr>
<?php
foreach ( $data as $input_id => $attr ) : ?>
	<tr id="<?php echo esc_attr( $input_id ); ?>">
		<td style="text-align:center;padding:5px;border:solid 1px gray;"><?php echo \esc_html( ucfirst( preg_replace( '/[_-]/m', ' ', $input_id ) ) ); ?>: </td>
		<td style="text-align:center;padding:5px;border:solid 1px gray;"><?php echo \esc_html( $attr['value'] ); ?></td>
	</tr>
	<?php
endforeach;
?>
</table>
