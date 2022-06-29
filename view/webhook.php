<?php

global $wpdb;

global $woocommerce;

if (sanitize_text_field($_POST["transactionID"]) != "") {

    $getorderid = sanitize_text_field($_POST["orderid"]);
    preg_match_all('!\d+!', $getorderid, $arroid);
    $order = wc_get_order($arroid[0][0]);
    
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
    
    $ms_stock_setting = $payment_gateway->settings['ms_stock_setting']; // credit card mode stock reduce
    $ms_qr_stock_setting = $payment_gateway_qr->settings['ms_stock_setting']; // qrnone mode stock reduce
    $ms_install_stock_setting = $payment_gateway_installment->settings['ms_stock_setting']; // installment mode stock reduce

    $ms_time = date("YmdHis");

    $MNS_transaction_orderid = get_post_meta($order->id, 'MNS_transaction_orderid', true);
    $MNS_PAYMENT_TYPE = get_post_meta($order->id, 'MNS_PAYMENT_TYPE', true);
    $order_amount = $order->get_total();

    $ms_order_select = $payment_gateway->settings['order_status_if_success'];
    $ms_order_select_qr = $payment_gateway_qr->settings['order_status_if_success'];
    $ms_order_select_installment = $payment_gateway_installment->settings['order_status_if_success'];

    $process_transactionID = sanitize_text_field($_POST["transectionID"]); 
    $amount = sanitize_text_field($_POST["amount"]);
    $status = sanitize_text_field($_POST["status"]);
    $hash = sanitize_text_field($_POST["hash"]);
    $process_payment_hash = hash_hmac('sha256', $process_transactionID.$amount.$status.$getorderid, $ms_secret_key);

    if ($hash == $process_payment_hash && $status == "paysuccess"){
        if($MNS_PAYMENT_TYPE == "Card"){

            if ($ms_stock_setting != "Disable") {
                $order->payment_complete();
                // $order->reduce_order_stock();
            }

            if(empty($ms_order_select)){
                $order->update_status("wc-processing");
            }else{
                $order->update_status($ms_order_select);
            }
        } else if($MNS_PAYMENT_TYPE == "Qrnone"){

            if ($ms_qr_stock_setting != "Disable") {
                $order->payment_complete();
                // $order->reduce_order_stock();
            }

            if(empty($ms_order_select_qr)){
                $order->update_status("wc-processing");
            }else{
                $order->update_status($ms_order_select_qr);
                wp_redirect(wc_get_order($order->id)->get_checkout_order_received_url());
            }
        } else if($MNS_PAYMENT_TYPE == "Installment"){

            if ($ms_order_select_installment != "Disable") {
                $order->payment_complete();
                // $order->reduce_order_stock();
            }

            if(empty($ms_order_select_installment)){
                // $order->update_status("wc-processing");
                $order->payment_complete();
            }else{
                $order->update_status($ms_order_select_installment);
            }
        }
        
    } else {
        $order->update_status("wc-failed");
    }
}

