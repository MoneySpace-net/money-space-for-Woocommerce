<?php

global $wp_version;
global $woocommerce;

$order = wc_get_order($pid);

$result = new stdClass();
$result->order_id = $order->get_id();
$result->transaction_id = $order->get_meta("MNS_transaction");
$result->status = $order->get_status();
echo json_encode($result);
 

// $payment_gateway_id = MNS_ID;
// $payment_gateway_qr_id = MNS_ID_QRPROM;
// $payment_gateway_installment_id = MNS_ID_INSTALLMENT;
// $payment_gateways = WC_Payment_Gateways::instance();

// $payment_gateway = $payment_gateways->payment_gateways()[$payment_gateway_id];
// $payment_gateway_qr = $payment_gateways->payment_gateways()[$payment_gateway_qr_id];
// $payment_gateway_installment = $payment_gateways->payment_gateways()[$payment_gateway_installment_id];

// $ms_secret_id = $payment_gateway->settings['secret_id'];
// $ms_secret_key = $payment_gateway->settings['secret_key'];
