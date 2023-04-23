<?php

global $wp_version;
global $woocommerce;

$order = wc_get_order($pid);

$payment_gateway_id = MNS_ID;
$payment_gateways = WC_Payment_Gateways::instance();

$payment_gateway = $payment_gateways->payment_gateways()[$payment_gateway_id];

$ms_secret_id = $payment_gateway->settings['secret_id'];
$ms_secret_key = $payment_gateway->settings['secret_key'];

$ms_body = array(
    "transaction_ID" => $order->get_meta("MNS_transaction"),
    "secret_id" => $ms_secret_id,
    "secret_key" => $ms_secret_key
);

$response = array();

$response = wp_remote_post(MNS_API_URL_CHECK_PAYMENT, array(
    'method' => 'POST',
    'timeout' => 120,
    'body' => $ms_body
));

$data_status = json_decode($response["body"]);
$transaction_ID = "transaction id";
$result = new stdClass();
$result->order_id = $order->get_id();
$result->transaction_id = $order->get_meta("MNS_transaction");
$result->status = $data_status[0]->$transaction_ID->status;

if ($result->status == "Pay Success")
{
    wp_remote_post(get_site_url() . "/process/payment/" . $order->get_id(), array(
        'method' => 'POST',
        'timeout' => 120
    ));
}

echo json_encode($result);