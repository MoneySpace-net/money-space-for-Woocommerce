<?php

global $wp_version;
global $woocommerce;

$pid = absint($pid);
$order = wc_get_order($pid);
$provided_key = isset($_GET['key']) ? sanitize_text_field(wp_unslash($_GET['key'])) : '';

header('Content-Type: application/json; charset=' . get_option('blog_charset'));

if (!$order || (function_exists('moneyspace_can_access_order') && !moneyspace_can_access_order($order, $provided_key))) {
    status_header(403);
    echo wp_json_encode(['error' => 'forbidden']);
    exit;
}

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

$body = is_wp_error($response) ? '' : wp_remote_retrieve_body($response);
$data_status = $body ? json_decode($body) : null;
$transaction_ID = "transaction id";
$result = new stdClass();
$result->order_id = $order->get_id();
$result->transaction_id = get_post_meta($pid, 'MNS_transaction', true);
$result->status = (is_array($data_status) && isset($data_status[0]->$transaction_ID->status)) ? $data_status[0]->$transaction_ID->status : 'Unknown';

if ($result->status == "Pay Success")
{
    $process_url = add_query_arg(
        'key',
        $order->get_order_key(),
        trailingslashit(get_site_url()) . 'process/payment/' . $order->get_id()
    );
    wp_remote_post($process_url, array(
        'method' => 'POST',
        'timeout' => 120
    ));
}

echo json_encode($result);