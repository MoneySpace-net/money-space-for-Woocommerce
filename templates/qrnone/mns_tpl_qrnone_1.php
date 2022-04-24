<?php
$order_id = $args['order_id'];
$payment_gateway_qr = $args['payment_gateway_qr'];
$image_qrprom = $args['image_qrprom'];

$order = wc_get_order($order_id);

include_once 'qrnone_form.php';

wc_get_template(
    'emails/email-order-details.php',
    array(
      'order' => $order
    )
);

?>
