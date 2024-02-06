<?php

global $wp_version;
global $woocommerce;

$order = wc_get_order($pid);

$payment_gateway_id = MNS_ID;
$payment_gateways = WC_Payment_Gateways::instance();

$gateways = WC()->payment_gateways->get_available_payment_gateways();
$ms_secret_id = $gateways[$payment_gateway_id]->settings['secret_id'];
$ms_secret_key = $gateways[$payment_gateway_id]->settings['secret_key'];

$ms_body = array(
    "transaction_ID" => get_post_meta($pid, 'MNS_transaction', true),
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
$result->transaction_id = get_post_meta($pid, 'MNS_transaction', true);
$result->status = $data_status[0]->$transaction_ID->status;

if ($result->status == "Pay Success")
{
    wp_remote_post(get_site_url() . "/process/payment/" . $order->get_id(), array(
        'method' => 'POST',
        'timeout' => 120
    ));
}

echo json_encode($result);