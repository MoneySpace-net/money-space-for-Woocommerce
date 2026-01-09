<?php

if ( ! defined( 'ABSPATH' ) ) exit;

global $wpdb;

global $woocommerce;


$moneyspace_pid = isset($pid) ? absint($pid) : 0;
$moneyspace_order = wc_get_order($moneyspace_pid);
$moneyspace_provided_key = filter_input(INPUT_GET, 'key', FILTER_SANITIZE_STRING);
$moneyspace_nonce = filter_input(INPUT_GET, 'ms_nonce', FILTER_SANITIZE_STRING);
$moneyspace_nonce_valid = $moneyspace_nonce ? wp_verify_nonce($moneyspace_nonce, 'moneyspace_cancel_payment') : false;
$moneyspace_order_id = $moneyspace_order ? $moneyspace_order->get_id() : 0;

if (!$moneyspace_order || (function_exists('moneyspace_can_access_order') && !moneyspace_can_access_order($moneyspace_order, $moneyspace_provided_key))) {
    status_header(404);
    nocache_headers();
    exit;
}

// If a nonce is provided for cancel route, ensure validity.
if (isset($_GET['ms_nonce']) && ! $moneyspace_nonce_valid) {
    status_header(403);
    nocache_headers();
    exit;
}

$moneyspace_force_cancelling = false;

if ($moneyspace_order && $moneyspace_pid) {
    $moneyspace_payment_gateway_id = defined('MONEYSPACE_ID_CREDITCARD') ? MONEYSPACE_ID_CREDITCARD : (defined('MONEYSPACE_ID') ? MONEYSPACE_ID : 'moneyspace');
    $moneyspace_payment_gateway_qr_id = defined('MONEYSPACE_ID_QRPROM') ? MONEYSPACE_ID_QRPROM : (defined('MONEYSPACE_ID_QRPROM') ? MONEYSPACE_ID_QRPROM : 'moneyspace_qrnone');
    $moneyspace_payment_gateway_installment_id = defined('MONEYSPACE_ID_INSTALLMENT') ? MONEYSPACE_ID_INSTALLMENT : (defined('MONEYSPACE_ID_INSTALLMENT') ? MONEYSPACE_ID_INSTALLMENT : 'moneyspace_installment');

    $moneyspace_payment_gateways = WC_Payment_Gateways::instance();
    $moneyspace_payment_gateway = $moneyspace_payment_gateways->payment_gateways()[$moneyspace_payment_gateway_id] ?? null;
    $moneyspace_payment_gateway_qr = $moneyspace_payment_gateways->payment_gateways()[$moneyspace_payment_gateway_qr_id] ?? null;
    $moneyspace_payment_gateway_installment = $moneyspace_payment_gateways->payment_gateways()[$moneyspace_payment_gateway_installment_id] ?? null;

    $moneyspace_gateways = WC()->payment_gateways->get_available_payment_gateways();

    $moneyspace_secret_id = $moneyspace_payment_gateway->settings['secret_id'] ?? '';
    $moneyspace_secret_key = $moneyspace_payment_gateway->settings['secret_key'] ?? '';
    
    $moneyspace_order_id = $moneyspace_order->get_id();
    $moneyspace_payment_type = get_post_meta($moneyspace_order_id, 'MNS_PAYMENT_TYPE', true);

    if ($moneyspace_payment_type == "Qrnone") {

        $moneyspace_transaction = get_post_meta($moneyspace_order_id, 'MNS_transaction', true);
        $moneyspace_qr_time = get_post_meta($moneyspace_order_id, 'MNS_QR_TIME', true);
        $moneyspace_auto_cancel = $moneyspace_payment_gateway_qr->settings['auto_cancel'] ?? '';

        if(empty($moneyspace_auto_cancel)){
            $moneyspace_limit_time = 1200;
        }else{
            $moneyspace_limit_time = $moneyspace_auto_cancel;
        }

        if ((time() - $moneyspace_qr_time) > $moneyspace_limit_time){
            if (moneyspace_check_payment_status($moneyspace_secret_id, $moneyspace_secret_key, $moneyspace_transaction) != true) {
                $moneyspace_call_cancel = wp_remote_post(MONEYSPACE_CANCEL_TRANSACTION, array(
                    'method' => 'POST',
                    'timeout' => 120,
                    'body' => array(
                        'secret_id' => $moneyspace_secret_id,
                        'secret_key' => $moneyspace_secret_key,
                        'transaction_ID' => $moneyspace_transaction,
                    )
                ));
    
                if (!is_wp_error($moneyspace_call_cancel)) {
    
                    $moneyspace_json_status = json_decode($moneyspace_call_cancel["body"]);
                    $moneyspace_text_check = "Transaction id : ".$moneyspace_transaction." Canceled";
    
                    if($moneyspace_json_status[0]->status == "success" && $moneyspace_json_status[0]->message == $moneyspace_text_check){
                        $moneyspace_force_cancelling = true;
    
                    }else{
                        $moneyspace_force_cancelling = true;
                    }
                }else{
                    $moneyspace_force_cancelling = true;
                } 
            } else {
                wp_safe_redirect(esc_url_raw($moneyspace_order->get_checkout_order_received_url()));
                exit;
            }
        }else{
            $moneyspace_force_cancelling = true;
        }
    }else{
        $moneyspace_force_cancelling = true;
    }
} else {
    $moneyspace_force_cancelling = true;
}

if ($moneyspace_force_cancelling) {
    do_action( 'woocommerce_cancelled_order', $moneyspace_order_id);
    $moneyspace_order->update_status("wc-cancelled");
    wp_safe_redirect(esc_url_raw($moneyspace_order->get_cancel_order_url()));
    exit;
}

function moneyspace_check_payment_status($moneyspace_secret_id, $moneyspace_secret_key, $moneyspace_transaction) {
    $payment_status = wp_remote_post(MONEYSPACE_CHECK_PAYMENT, array(
        'method' => 'POST',
        'timeout' => 120,
        'body' => array(
            'secret_id' => $moneyspace_secret_id,
            'secret_key' => $moneyspace_secret_key,
            'transaction_ID' => $moneyspace_transaction,
        )
    ));
    if (!is_wp_error($payment_status)) {
        $moneyspace_json_status = json_decode($payment_status["body"]);
        $moneyspace_transaction_field = "transaction id";
        $status = $moneyspace_json_status[0]->$moneyspace_transaction_field->status;
        if (strtolower($status) == strtolower("Pay Success")) {
            return true;
        }
        // other status return false
    }

    return false;
}