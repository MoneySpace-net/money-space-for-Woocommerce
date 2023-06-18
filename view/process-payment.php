<?php

global $wpdb;

global $woocommerce;
$order = wc_get_order($pid);

if ($order && $pid) {

    $payment_gateway_id = MNS_ID;
    $payment_gateway_qr_id = MNS_ID_QRPROM;
    $payment_gateway_installment_id = MNS_ID_INSTALLMENT;


    $payment_gateways = WC_Payment_Gateways::instance();
    $payment_gateway = $payment_gateways->payment_gateways()[$payment_gateway_id];
    $payment_gateway_qr = $payment_gateways->payment_gateways()[$payment_gateway_qr_id];
    $payment_gateway_installment = $payment_gateways->payment_gateways()[$payment_gateway_installment_id];

    $gateways = WC()->payment_gateways->get_available_payment_gateways();


    $ms_secret_id = $payment_gateway->settings['secret_id'];
    $ms_secret_key = $payment_gateway->settings['secret_key'];
    $ms_stock_setting = $payment_gateway->settings['ms_stock_setting'];

    $ms_order_select = $payment_gateway->settings['order_status_if_success'];
    $ms_order_select_qr = $payment_gateway_qr->settings['order_status_if_success'];
    $ms_order_select_installment = $payment_gateway_installment->settings['order_status_if_success'];

    $enable_auto_check_result = $payment_gateway_qr->settings['enable_auto_check_result'];

    $ms_time = date("YmdHis");
    $MNS_transaction_orderid = get_post_meta($order->id, 'MNS_transaction_orderid', true);
    $MNS_PAYMENT_TYPE = get_post_meta($order->id, 'MNS_PAYMENT_TYPE', true);
    $order_amount = $order->get_total();
    $check_orderid = wp_remote_post(MNS_API_URL_CHECK, array(
        'method' => 'POST',
        'timeout' => 120,
        'body' => array(
            'secret_id' => $ms_secret_id,
            'secret_key' => $ms_secret_key,
            'order_id' => $MNS_transaction_orderid,
        )
    ));

    if (!is_wp_error($check_orderid)) {
        $oid = "order id";
        $json_status = json_decode($check_orderid["body"]);
        $ms_status = $json_status[0]->$oid;

        cancel_payment($order->id, $payment_gateway);

        if ($ms_status->status == "Pay Success") {

            if($MNS_PAYMENT_TYPE == "Card"){

                if(empty($ms_order_select)){
                    $order->update_status("wc-processing");
                }else{
                    $order->update_status($ms_order_select);
                }
            }else if($MNS_PAYMENT_TYPE == "Qrnone"){

                if(empty($ms_order_select_qr)){
                    $order->update_status("wc-processing");
                }else{
                    $order->update_status($ms_order_select_qr);

                    if($enable_auto_check_result == "yes" || $enable_auto_check_result == "") {
                        wp_redirect(wc_get_order($order->id)->get_checkout_order_received_url());
                    }
                }
            } else if($MNS_PAYMENT_TYPE == "Installment"){

                if(empty($ms_order_select_installment)){
                    $order->update_status("wc-processing");
                }else{
                    $order->update_status($ms_order_select_installment);
                }
            }

            if ($ms_stock_setting != "Disable") {
                wc_reduce_stock_levels($order->id);
                // $order->reduce_order_stock();
            }
            update_post_meta($order->id, 'MNS_PAYMENT_PAID', $ms_status->amount);
            update_post_meta($order->id, 'MNS_PAYMENT_STATUS', $ms_status->status);
            wp_redirect(wc_get_order($order->id)->get_checkout_order_received_url());
        } else if ($ms_status->status == "Cancel") {
            $order->update_status("wc-cancelled");
            wp_redirect(wc_get_order($order->id)->get_cancel_order_url());
        } else {
            # Fail case
            $order->update_status("wc-failed");
            wp_redirect(wc_get_order($order->id)->get_checkout_order_received_url());
        }
    } else {
        $order->update_status("wc-failed");
        wp_redirect(wc_get_order($order->id)->get_checkout_order_received_url());
    }
    WC()->cart->empty_cart();
} else {
    wp_redirect(wc_get_order($order->id)->get_checkout_order_received_url());
}

function cancel_payment($order_id, $payment_gateway)
{
    $MNS_transaction = get_post_meta($order_id, 'MNS_transaction', true);

    $ms_secret_id = $payment_gateway->settings['secret_id'];
    $ms_secret_key = $payment_gateway->settings['secret_key'];
    // trigger kill transaction id
    $call_cancel = wp_remote_post(MNS_CANCEL_TRANSACTION, array(
        'method' => 'POST',
        'timeout' => 120,
        'body' => array(
            'secret_id' => $ms_secret_id,
            'secret_key' => $ms_secret_key,
            'transaction_ID' => $MNS_transaction,
        )
    ));
    $json_status = json_decode($call_cancel["body"]);
    if($json_status[0]->status == "success")
    {
        // TODO
        
    }
}
