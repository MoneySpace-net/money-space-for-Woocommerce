<?php

if ( ! defined( 'ABSPATH' ) ) exit;

// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- External webhook uses HMAC signature verification instead of nonces.
$moneyspace_transection_id = filter_input(INPUT_POST, 'transectionID', FILTER_SANITIZE_STRING);
if (!empty($moneyspace_transection_id)) {

    $moneyspace_getorderid = filter_input(INPUT_POST, 'orderid', FILTER_SANITIZE_STRING);
    preg_match_all('!\d+!', $moneyspace_getorderid, $moneyspace_arroid);
    $moneyspace_order = wc_get_order($moneyspace_arroid[0][0] ?? 0);
    
    $moneyspace_payment_gateway_id = defined('MONEYSPACE_ID_CREDITCARD') ? MONEYSPACE_ID_CREDITCARD : (defined('MNS_ID') ? MNS_ID : 'moneyspace');
    $moneyspace_payment_gateway_qr_id = defined('MONEYSPACE_ID_QRPROM') ? MONEYSPACE_ID_QRPROM : (defined('MNS_ID_QRPROM') ? MNS_ID_QRPROM : 'moneyspace_qrnone');
    $moneyspace_payment_gateway_installment_id = defined('MONEYSPACE_ID_INSTALLMENT') ? MONEYSPACE_ID_INSTALLMENT : (defined('MNS_ID_INSTALLMENT') ? MNS_ID_INSTALLMENT : 'moneyspace_installment');

    $moneyspace_payment_gateways = WC_Payment_Gateways::instance();
    $moneyspace_payment_gateway = $moneyspace_payment_gateways->payment_gateways()[$moneyspace_payment_gateway_id] ?? null;
    $moneyspace_payment_gateway_qr = $moneyspace_payment_gateways->payment_gateways()[$moneyspace_payment_gateway_qr_id] ?? null;
    $moneyspace_payment_gateway_installment = $moneyspace_payment_gateways->payment_gateways()[$moneyspace_payment_gateway_installment_id] ?? null;

    $moneyspace_gateways = WC()->payment_gateways->get_available_payment_gateways();

    $moneyspace_secret_id = $moneyspace_payment_gateway->settings['secret_id'] ?? '';
    $moneyspace_secret_key = $moneyspace_payment_gateway->settings['secret_key'] ?? '';
    
    $moneyspace_stock_setting = $moneyspace_payment_gateway->settings['ms_stock_setting'] ?? ''; // credit card mode stock reduce
    $moneyspace_qr_stock_setting = $moneyspace_payment_gateway_qr->settings['ms_stock_setting'] ?? ''; // qrnone mode stock reduce
    $moneyspace_install_stock_setting = $moneyspace_payment_gateway_installment->settings['ms_stock_setting'] ?? ''; // installment mode stock reduce

    $moneyspace_time = gmdate("YmdHis");
    $moneyspace_order_id = $moneyspace_order ? $moneyspace_order->get_id() : 0;

    $moneyspace_transaction_orderid = get_post_meta($moneyspace_order_id, 'MNS_transaction_orderid', true);
    $moneyspace_payment_type = get_post_meta($moneyspace_order_id, 'MNS_PAYMENT_TYPE', true);
    $moneyspace_order_amount = $moneyspace_order ? $moneyspace_order->get_total() : 0;

    $moneyspace_order_select = $moneyspace_payment_gateway->settings['order_status_if_success'] ?? '';
    $moneyspace_order_select_qr = $moneyspace_payment_gateway_qr->settings['order_status_if_success'] ?? '';
    $moneyspace_order_select_installment = $moneyspace_payment_gateway_installment->settings['order_status_if_success'] ?? '';

    $moneyspace_process_transactionID = $moneyspace_transection_id; 
    $moneyspace_amount = filter_input(INPUT_POST, 'amount', FILTER_SANITIZE_STRING);
    $moneyspace_status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);
    $moneyspace_hash = filter_input(INPUT_POST, 'hash', FILTER_SANITIZE_STRING);
    $moneyspace_process_payment_hash = hash_hmac('sha256', $moneyspace_process_transactionID . $moneyspace_amount . $moneyspace_status . $moneyspace_getorderid, $moneyspace_secret_key);

    if ($moneyspace_hash === $moneyspace_process_payment_hash && $moneyspace_status === "paysuccess"){
        if($moneyspace_payment_type === "Card"){

            if ($moneyspace_stock_setting !== "Disable") {
                wc_reduce_stock_levels($moneyspace_order_id);
            }

            if(empty($moneyspace_order_select)){
                $moneyspace_order->update_status("wc-processing");
            }else{
                $moneyspace_order->update_status($moneyspace_order_select);
            }
        } else if($moneyspace_payment_type === "Qrnone"){

            if ($moneyspace_qr_stock_setting !== "Disable") {
                wc_reduce_stock_levels($moneyspace_order_id);
            }

            if(empty($moneyspace_order_select_qr)){
                $moneyspace_order->update_status("wc-processing");
            }else{
                $moneyspace_order->update_status($moneyspace_order_select_qr);
                wp_safe_redirect(esc_url_raw($moneyspace_order->get_checkout_order_received_url()));
                exit;
            }
        } else if($moneyspace_payment_type === "Installment"){

            if ($moneyspace_install_stock_setting !== "Disable") {
                wc_reduce_stock_levels($moneyspace_order_id);
            }

            if(empty($moneyspace_order_select_installment)){
                $moneyspace_order->update_status("wc-processing");
            }else{
                $moneyspace_order->update_status($moneyspace_order_select_installment);
            }
        }
        
    } else {
        $moneyspace_order->update_status("wc-failed");
    }
}

