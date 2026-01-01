<?php

$moneyspace_pid = absint($moneyspace_pid);
$moneyspace_order = wc_get_order($moneyspace_pid);
$moneyspace_provided_key = isset($moneyspace__GET['key']) ? sanitize_text_field(wp_unslash($moneyspace__GET['key'])) : '';

header('Content-Type: application/json; charset=' . get_option('blog_charset'));

if (!$moneyspace_order || (function_exists('moneyspace_can_access_order') && !moneyspace_can_access_order($moneyspace_order, $moneyspace_provided_key))) {
    status_header(403);
    echo wp_json_encode(['error' => 'forbidden']);
    exit;
}

$moneyspace_payment_gateway_id = MONEYSPACE_ID;
$moneyspace_payment_gateways = WC_Payment_Gateways::instance();

$moneyspace_gateways = WC()->payment_gateways->get_available_payment_gateways();
$moneyspace_ms_secret_id = $moneyspace_gateways[$moneyspace_payment_gateway_id]->settings['secret_id'];
$moneyspace_ms_secret_key = $moneyspace_gateways[$moneyspace_payment_gateway_id]->settings['secret_key'];

$moneyspace_ms_body = array(
    "transaction_ID" => get_post_meta($moneyspace_pid, 'MONEYSPACE_transaction', true),
    "secret_id" => $moneyspace_ms_secret_id,
    "secret_key" => $moneyspace_ms_secret_key
);

$moneyspace_response = array();

$moneyspace_response = wp_remote_post(MONEYSPACE_API_URL_CHECK_PAYMENT, array(
    'method' => 'POST',
    'timeout' => 120,
    'body' => $moneyspace_ms_body
));

$moneyspace_body = is_wp_error($moneyspace_response) ? '' : wp_remote_retrieve_body($moneyspace_response);
$moneyspace_data_status = $moneyspace_body ? json_decode($moneyspace_body) : null;
$moneyspace_transaction_ID = "transaction id";
$moneyspace_result = new stdClass();
$moneyspace_result->order_id = $moneyspace_order->get_id();
$moneyspace_result->transaction_id = get_post_meta($moneyspace_pid, 'MNS_transaction', true);
$moneyspace_result->status = (is_array($moneyspace_data_status) && isset($moneyspace_data_status[0]->$moneyspace_transaction_ID->status)) ? $moneyspace_data_status[0]->$moneyspace_transaction_ID->status : 'Unknown';

if ($moneyspace_result->status == "Pay Success")
{
    $moneyspace_process_url = add_query_arg(
        'key',
        $moneyspace_order->get_order_key(),
        trailingslashit(get_site_url()) . 'process/payment/' . $moneyspace_order->get_id()
    );
    wp_remote_post($moneyspace_process_url, array(
        'method' => 'POST',
        'timeout' => 120
    ));
}

echo json_encode($moneyspace_result);