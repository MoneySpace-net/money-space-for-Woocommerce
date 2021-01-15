<?php

global $wpdb;

global $woocommerce;

echo "Webhook !!!!!!!!!!";

if (isset($_POST["transectionID"])) {
    $getorderid = $_POST["orderid"];
    preg_match_all('!\d+!', $getorderid, $arroid);
    $order = wc_get_order($arroid[0][0]);
    $payment_gateway_id = MS_ID;
    $payment_gateways = WC_Payment_Gateways::instance();
    $payment_gateway = $payment_gateways->payment_gateways()[$payment_gateway_id];
    $gateways = WC()->payment_gateways->get_available_payment_gateways();
    $ms_secret_id = $gateways['moneyspace']->settings['secret_id'];
    $ms_secret_key = $gateways['moneyspace']->settings['secret_key'];
    $ms_order_select = $gateways['moneyspace']->settings['order_status_if_success'];
    $ms_stock_setting = $gateways['moneyspace']->settings['ms_stock_setting'];
    $ms_time = date("YmdHis");

    $MS_transaction_orderid = get_post_meta($order->id, 'MS_transaction_orderid', true);
    $MS_PAYMENT_TYPE = get_post_meta($order->id, 'MS_PAYMENT_TYPE', true);
    $order_amount = $order->get_total();

    $check_orderid = wp_remote_post(MS_API_URL_CHECK, array(
        'method' => 'POST',
        'timeout' => 120,
        'body' => array(
            'secret_id' => $ms_secret_id,
            'secret_key' => $ms_secret_key,
            'order_id' => $MS_transaction_orderid,
        )
    ));

    if (!is_wp_error($check_orderid)) {
        $oid = "order id";
        $json_status = json_decode($check_orderid["body"]);
        $ms_status = $json_status[0]->$oid;
        if ($ms_status->status == "Pay Success") {
            $order->update_status($ms_order_select);
            if ($ms_stock_setting != "Disable") {
                $order->reduce_order_stock();
            }

            update_post_meta($order->id, 'MS_PAYMENT_PAID', $ms_status->amount);
            update_post_meta($order->id, 'MS_PAYMENT_STATUS', $ms_status->status);
        } else if ($ms_status->status == "Fail") {
            $order->update_status("wc-failed");
        } else if ($ms_status->status == "Cancel") {
            $order->update_status("wc-failed");
        } else {
            $order->update_status("wc-failed");
        }
    } else {
        $order->update_status("wc-failed");
    }
}
