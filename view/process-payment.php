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
    $order_id = $order->get_id();
    $MNS_transaction_orderid = get_post_meta($order_id, 'MNS_transaction_orderid', true);
    $MNS_PAYMENT_TYPE = get_post_meta($order_id, 'MNS_PAYMENT_TYPE', true);
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

    // Debug logging - only active when WP_DEBUG is enabled
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('MoneySpace Payment Status Check - Order ID: ' . $order_id);
        error_log('MoneySpace Payment Status Check - Transaction Order ID: ' . $MNS_transaction_orderid);
        error_log('MoneySpace Payment Status Check - Payment Type: ' . $MNS_PAYMENT_TYPE);
    }

    if (!is_wp_error($check_orderid)) {
        $response_body = wp_remote_retrieve_body($check_orderid);
        
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('MoneySpace Payment Status Check - Raw Response: ' . $response_body);
        }
        
        $json_status = json_decode($response_body);
        
        // Add safety check for API response
        if (empty($json_status) || !is_array($json_status) || !isset($json_status[0])) {
            error_log('MoneySpace API: Invalid response format in process-payment.php - Response: ' . $response_body);
            $order->update_status("wc-failed");
            wp_redirect($order->get_checkout_order_received_url());
            return;
        }
        
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('MoneySpace Payment Status Check - Parsed Response: ' . json_encode($json_status[0]));
        }
        
        // Access the order data using proper property notation
        $response_data = $json_status[0];
        $ms_status = isset($response_data->{'order id'}) ? $response_data->{'order id'} : null;
        
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('MoneySpace Payment Status Check - Order Status Object: ' . json_encode($ms_status));
        }
        
        // Additional safety check for order status
        if (empty($ms_status)) {
            error_log('MoneySpace API: No order status found in response in process-payment.php');
            error_log('MoneySpace API: Available properties: ' . json_encode(array_keys((array)$response_data)));
            $order->update_status("wc-failed");
            wp_redirect($order->get_checkout_order_received_url());
            return;
        }

        cancel_payment($order_id, $payment_gateway);

        // Handle different success status values based on payment type
        $is_payment_successful = false;
        if (isset($ms_status->status)) {
            $status = $ms_status->status;
            
            // Check for all possible success status values
            if ($status == "Pay Success" || $status == "Success") {
                $is_payment_successful = true;
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log('MoneySpace Payment: Payment successful with status "' . $status . '" for payment type: ' . $MNS_PAYMENT_TYPE);
                }
            }
        }

        if ($is_payment_successful) {

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
                        wp_redirect($order->get_checkout_order_received_url());
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
                wc_reduce_stock_levels($order_id);
            }
            update_post_meta($order_id, 'MNS_PAYMENT_PAID', $ms_status->amount);
            update_post_meta($order_id, 'MNS_PAYMENT_STATUS', $ms_status->status);
            wp_redirect($order->get_checkout_order_received_url());
        } else if (isset($ms_status->status) && $ms_status->status == "Cancel") {
            $order->update_status("wc-cancelled");
            wp_redirect($order->get_cancel_order_url());
        } else if (isset($ms_status->status) && $ms_status->status == "Pending") {
            // Handle pending payments - keep order in pending status and redirect to payment page
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('MoneySpace Payment Status: Pending - keeping order in pending status');
            }
            $order->update_status("wc-pending");
            update_post_meta($order_id, 'MNS_PAYMENT_STATUS', $ms_status->status);
            
            // For installment payments, redirect back to complete the payment
            if ($MNS_PAYMENT_TYPE == "Installment") {
                // Check if we have a payment link in the transaction data
                $payment_link = get_post_meta($order_id, 'MNS_PAYMENT_LINK', true);
                if (!empty($payment_link)) {
                    wp_redirect($payment_link);
                } else {
                    // Redirect to a pending payment page or back to checkout
                    wc_add_notice(__('Your payment is pending. Please complete the payment process.', 'woocommerce'), 'notice');
                    wp_redirect($order->get_checkout_payment_url(true));
                }
            } else {
                wp_redirect($order->get_checkout_order_received_url());
            }
        } else {
            # Fail case - log the received status for debugging
            $received_status = isset($ms_status->status) ? $ms_status->status : 'Unknown';
            
            // Always log payment failures for troubleshooting
            error_log('MoneySpace Payment Status: "' . $received_status . '" for payment type: ' . $MNS_PAYMENT_TYPE . ' - treating as failed');
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('MoneySpace Payment Debug: Recognized success statuses are "Success" and "Pay Success"');
            }
            
            // Add detailed order note for admin reference
            $failure_reason = 'MoneySpace payment declined. Status: "' . $received_status . '". Payment Type: ' . $MNS_PAYMENT_TYPE;
            if (isset($MNS_transaction_orderid)) {
                $failure_reason .= '. Transaction Order ID: ' . $MNS_transaction_orderid;
            }
            $order->add_order_note($failure_reason);
            
            $order->update_status("wc-failed");
            wp_redirect($order->get_checkout_order_received_url());
        }
    } else {
        $order->update_status("wc-failed");
        wp_redirect($order->get_checkout_order_received_url());
    }
    WC()->cart->empty_cart();
} else {
    if ($order) {
        wp_redirect($order->get_checkout_order_received_url());
    } else {
        wp_redirect(wc_get_cart_url());
    }
}
