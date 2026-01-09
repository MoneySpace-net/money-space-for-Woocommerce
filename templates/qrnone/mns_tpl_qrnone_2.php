<?php

if ( ! defined( 'ABSPATH' ) ) exit;

$moneyspace_order_id = $args['order_id'];
$moneyspace_payment_gateway_qr = $args['payment_gateway_qr'];
$moneyspace_image_qrprom = $args['image_qrprom'];

$moneyspace_order = wc_get_order($moneyspace_order_id);

$moneyspace_text_align = is_rtl() ? 'right' : 'left';
?>
<style>
  .money-space img, .money-space-page img {
    padding: 10px;
  }
</style>
<div style="margin-bottom: 40px;">
	<table class="td" cellspacing="0" cellpadding="6" style="width: 100%; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;" border="1">
		<thead>
			<tr>
				<th class="td" scope="col" style="text-align:<?php echo esc_attr( $moneyspace_text_align ); ?>;"><?php esc_html_e( 'Product', 'money-space' ); ?></th>
				<th class="td" scope="col" style="text-align:<?php echo esc_attr( $moneyspace_text_align ); ?>;"><?php esc_html_e( 'Quantity', 'money-space' ); ?></th>
				<th class="td" scope="col" style="text-align:<?php echo esc_attr( $moneyspace_text_align ); ?>;"><?php esc_html_e( 'Price', 'money-space' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php
			echo wc_get_email_order_items( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				$moneyspace_order,
				array(
					'show_sku'      => false,
					'show_image'    => true,
					'image_size'    => array( 100, 100 ),
					'plain_text'    => false,
					'sent_to_admin' => false,
				)
			);
			?>
		</tbody>
		<tfoot>
			<?php
			$moneyspace_item_totals = $moneyspace_order->get_order_item_totals();

			if ( $moneyspace_item_totals ) {
				$moneyspace_i = 0;
				foreach ( $moneyspace_item_totals as $moneyspace_total ) {
					$moneyspace_i++;
					?>
					<tr>
						<th class="td" scope="row" colspan="2" style="text-align:<?php echo esc_attr( $moneyspace_text_align ); ?>; <?php echo ( 1 === $moneyspace_i ) ? 'border-top-width: 4px;' : ''; ?>"><?php echo wp_kses_post( $moneyspace_total['label'] ); ?></th>
						<td class="td" style="text-align:<?php echo esc_attr( $moneyspace_text_align ); ?>; <?php echo ( 1 === $moneyspace_i ) ? 'border-top-width: 4px;' : ''; ?>"><?php echo wp_kses_post( $moneyspace_total['value'] ); ?></td>
					</tr>
					<?php
				}
			}
			if ( $moneyspace_order->get_customer_note() ) {
				?>
				<tr>
					<th class="td" scope="row" colspan="2" style="text-align:<?php echo esc_attr( $moneyspace_text_align ); ?>;"><?php esc_html_e( 'Note:', 'money-space' ); ?></th>
					<td class="td" style="text-align:<?php echo esc_attr( $moneyspace_text_align ); ?>;"><?php echo wp_kses_post( nl2br( wptexturize( $moneyspace_order->get_customer_note() ) ) ); ?></td>
				</tr>
				<?php
			}
			?>
		</tfoot>
	</table>
</div>

<?php

include_once 'qrnone_form.php';

?>

<div style="text-align: center;">
	<h3 id="showTime"></h3>
</div>

