<?php

if ( ! defined( 'ABSPATH' ) ) exit;

global $wpdb;

global $woocommerce;
$moneyspace_pid = isset($pid) ? absint($pid) : 0;
$moneyspace_order = wc_get_order($moneyspace_pid);
$moneyspace_provided_key = filter_input(INPUT_GET, 'key', FILTER_SANITIZE_STRING);
$moneyspace_nonce = filter_input(INPUT_GET, 'ms_nonce', FILTER_SANITIZE_STRING);
$moneyspace_nonce_valid = $moneyspace_nonce ? wp_verify_nonce($moneyspace_nonce, 'moneyspace_process_payment') : false;
$moneyspace_logger = wc_get_logger();

if (!$moneyspace_order || (function_exists('moneyspace_can_access_order') && !moneyspace_can_access_order($moneyspace_order, $moneyspace_provided_key))) {
    status_header(404);
    nocache_headers();
    exit;
}

// If a nonce is provided, ensure it is valid before proceeding with any state changes.
if (isset($_GET['ms_nonce']) && ! $moneyspace_nonce_valid) {
    status_header(403);
    nocache_headers();
    exit;
}

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
    $moneyspace_stock_setting = $moneyspace_payment_gateway->settings['ms_stock_setting'] ?? '';

    $moneyspace_order_select = $moneyspace_payment_gateway->settings['order_status_if_success'] ?? '';
    $moneyspace_order_select_qr = $moneyspace_payment_gateway_qr->settings['order_status_if_success'] ?? '';
    $moneyspace_order_select_installment = $moneyspace_payment_gateway_installment->settings['order_status_if_success'] ?? '';

    $moneyspace_enable_auto_check_result = $moneyspace_payment_gateway_qr->settings['enable_auto_check_result'] ?? '';

    $moneyspace_time = gmdate("YmdHis");
    $moneyspace_order_id = $moneyspace_order ? $moneyspace_order->get_id() : 0;
    $moneyspace_transaction_orderid = get_post_meta($moneyspace_order_id, 'MONEYSPACE_transaction_orderid', true);
    $moneyspace_payment_type = get_post_meta($moneyspace_order_id, 'MONEYSPACE_PAYMENT_TYPE', true);
    $moneyspace_order_amount = $moneyspace_order ? $moneyspace_order->get_total() : 0;
    $moneyspace_check_orderid = wp_remote_post(MONEYSPACE_API_URL_CHECK, array(
        'method' => 'POST',
        'timeout' => 120,
        'body' => array(
            'secret_id' => $moneyspace_secret_id,
            'secret_key' => $moneyspace_secret_key,
            'order_id' => $MONEYSPACE_transaction_orderid,
        )
    ));

    if (!is_wp_error($moneyspace_check_orderid)) {
        $moneyspace_response_body = wp_remote_retrieve_body($moneyspace_check_orderid);

        $moneyspace_json_status = json_decode($moneyspace_response_body);
        
        // Add safety check for API response
        if (empty($moneyspace_json_status) || !is_array($moneyspace_json_status) || !isset($moneyspace_json_status[0])) {
            $moneyspace_logger->error( 'MoneySpace API: Invalid response format in process-payment.php - Response: ' . $moneyspace_response_body, [ 'source' => 'moneyspace' ] );
            $moneyspace_order->update_status("wc-failed");
            wp_safe_redirect(esc_url_raw($moneyspace_order->get_checkout_order_received_url()));
            exit;
        }
        
        // Access the order data using proper property notation
        $moneyspace_response_data = $moneyspace_json_status[0];
        $moneyspace_status_obj = isset($moneyspace_response_data->{'order id'}) ? $moneyspace_response_data->{'order id'} : null;
        
        // Additional safety check for order status
        if (empty($moneyspace_status_obj)) {
            $moneyspace_logger->error( 'MoneySpace API: No order status found in response in process-payment.php', [ 'source' => 'moneyspace' ] );
            $moneyspace_logger->error( 'MoneySpace API: Available properties: ' . json_encode(array_keys((array)$moneyspace_response_data)), [ 'source' => 'moneyspace' ] );
            $moneyspace_order->update_status("wc-failed");
            wp_safe_redirect(esc_url_raw($moneyspace_order->get_checkout_order_received_url()));
            exit;
        }

        moneyspace_cancel_payment($moneyspace_order_id, $moneyspace_payment_gateway);

        // Handle different success status values based on payment type
        $moneyspace_is_payment_successful = false;
        if (isset($moneyspace_status_obj->status)) {
            $moneyspace_status = $moneyspace_status_obj->status;
            
            // Check for all possible success status values
            if ($moneyspace_status == "Pay Success" || $moneyspace_status == "Success") {
                $moneyspace_is_payment_successful = true;
            }
        }

        if ($moneyspace_is_payment_successful) {

            if($moneyspace_payment_type == "Card"){

                if(empty($moneyspace_order_select)){
                    $moneyspace_order->update_status("wc-processing");
                }else{
                    $moneyspace_order->update_status($moneyspace_order_select);
                }
            }else if($moneyspace_payment_type == "Qrnone"){

                if(empty($moneyspace_order_select_qr)){
                    $moneyspace_order->update_status("wc-processing");
                }else{
                    $moneyspace_order->update_status($moneyspace_order_select_qr);

                    if($moneyspace_enable_auto_check_result == "yes" || $moneyspace_enable_auto_check_result == "") {
                        wp_safe_redirect(esc_url_raw($moneyspace_order->get_checkout_order_received_url()));
                        exit;
                    }
                }
            } else if($moneyspace_payment_type == "Installment"){

                if(empty($moneyspace_order_select_installment)){
                    $moneyspace_order->update_status("wc-processing");
                }else{
                    $moneyspace_order->update_status($moneyspace_order_select_installment);
                }
            }

            if ($moneyspace_stock_setting != "Disable") {
                wc_reduce_stock_levels($moneyspace_order_id);
            }
            update_post_meta($moneyspace_order_id, 'MONEYSPACE_PAYMENT_PAID', $moneyspace_status_obj->amount);
            update_post_meta($moneyspace_order_id, 'MONEYSPACE_PAYMENT_STATUS', $moneyspace_status_obj->status);
            wp_safe_redirect(esc_url_raw($moneyspace_order->get_checkout_order_received_url()));
            exit;
        } else if (isset($moneyspace_status_obj->status) && $moneyspace_status_obj->status == "Cancel") {
            $moneyspace_order->update_status("wc-cancelled");
            wp_safe_redirect(esc_url_raw($moneyspace_order->get_cancel_order_url()));
            exit;
        } else if (isset($moneyspace_status_obj->status) && $moneyspace_status_obj->status == "Pending") {
            // Handle pending payments - keep order in pending status and redirect to payment page
            if (defined('WP_DEBUG') && WP_DEBUG) {
                $moneyspace_logger->error('MoneySpace Payment Status: Pending - keeping order in pending status', [ 'source' => 'moneyspace' ]);
            }
            $moneyspace_order->update_status("wc-pending");
            update_post_meta($moneyspace_order_id, 'MONEYSPACE_PAYMENT_STATUS', $moneyspace_status_obj->status);
            
            // For installment payments, redirect back to complete the payment
            if ($moneyspace_payment_type == "Installment") {
                // Check if we have a payment link in the transaction data
                $moneyspace_payment_link = get_post_meta($moneyspace_order_id, 'MONEYSPACE_PAYMENT_LINK', true);
                if (!empty($moneyspace_payment_link)) {
                    $moneyspace_allowed_host = wp_parse_url($moneyspace_payment_link, PHP_URL_HOST);
                    if (!empty($moneyspace_allowed_host)) {
                        $moneyspace_allowed_redirect_filter = static function ($hosts) use ($moneyspace_allowed_host) {
                            $hosts[] = $moneyspace_allowed_host;
                            return array_unique($hosts);
                        };
                        add_filter('allowed_redirect_hosts', $moneyspace_allowed_redirect_filter);
                        wp_safe_redirect(esc_url_raw($moneyspace_payment_link));
                        remove_filter('allowed_redirect_hosts', $moneyspace_allowed_redirect_filter);
                        exit;
                    }

                    wp_safe_redirect(esc_url_raw($moneyspace_payment_link));
                    exit;
                } else {
                    // Redirect to a pending payment page or back to checkout
                    wc_add_notice(__('Your payment is pending. Please complete the payment process.', 'money-space'), 'notice');
                    wp_safe_redirect(esc_url_raw($moneyspace_order->get_checkout_payment_url(true)));
                    exit;
                }
            } else {
                wp_safe_redirect(esc_url_raw($moneyspace_order->get_checkout_order_received_url()));
                exit;
            }
        } else {
            # Fail case - log the received status for debugging
            $moneyspace_received_status = isset($moneyspace_status_obj->status) ? $moneyspace_status_obj->status : 'Unknown';
            
            // Add detailed order note for admin reference
            $moneyspace_failure_reason = 'MoneySpace payment declined. Status: "' . $moneyspace_received_status . '". Payment Type: ' . $moneyspace_payment_type;
            if (isset($moneyspace_transaction_orderid)) {
                $moneyspace_failure_reason .= '. Transaction Order ID: ' . $moneyspace_transaction_orderid;
            }
            $moneyspace_order->add_order_note($moneyspace_failure_reason);
            
            $moneyspace_order->update_status("wc-failed");
            wp_safe_redirect(esc_url_raw($moneyspace_order->get_checkout_order_received_url()));
            exit;
        }
    } else {
        $moneyspace_order->update_status("wc-failed");
        wp_safe_redirect(esc_url_raw($moneyspace_order->get_checkout_order_received_url()));
        exit;
    }
    WC()->cart->empty_cart();
} else {
    if ($moneyspace_order) {
        wp_safe_redirect(esc_url_raw($moneyspace_order->get_checkout_order_received_url()));
        exit;
    } else {
        wp_safe_redirect(esc_url_raw(wc_get_cart_url()));
        exit;
    }
}
