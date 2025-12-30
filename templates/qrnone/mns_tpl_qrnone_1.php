<?php
$moneyspace_order_id = $args['order_id'];
$moneyspace_payment_gateway_qr = $args['payment_gateway_qr'];
$moneyspace_image_qrprom = $args['image_qrprom'];

$moneyspace_order = wc_get_order($moneyspace_order_id);

include_once 'qrnone_form.php';

wc_get_template(
    'emails/email-order-details.php',
    array(
        'order' => $moneyspace_order,
        'sent_to_admin' => false,
        'plain_text' => false,
        'email' => null
    )
);

?>
