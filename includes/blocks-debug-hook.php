<?php
/**
 * Debug hooks for WooCommerce Blocks payment processing
 */

// Hook to debug payment method data from blocks
add_action('woocommerce_rest_checkout_process_payment_with_context', function($context, $result) {
    if ($context->payment_method === 'moneyspace') {
        error_log('MoneySpace Debug Hook: Payment context - ' . json_encode([
            'payment_method' => $context->payment_method,
            'payment_data' => $context->payment_data,
            'order_id' => $context->order ? $context->order->get_id() : 'N/A'
        ]));
        
        // Map the payment data to $_POST for the legacy gateway
        if (!empty($context->payment_data)) {
            foreach ($context->payment_data as $key => $value) {
                $_POST[$key] = sanitize_text_field($value);
            }
            error_log('MoneySpace Debug Hook: Mapped to $_POST - ' . json_encode($_POST));
        }
    }
}, 10, 2);

// Hook to debug when payment is being processed
add_action('woocommerce_checkout_process_payment', function($payment_method) {
    if ($payment_method === 'moneyspace') {
        error_log('MoneySpace Debug Hook: Processing payment for method - ' . $payment_method);
        error_log('MoneySpace Debug Hook: $_POST data - ' . json_encode($_POST));
    }
}, 10, 1);

// Hook to debug payment result
add_filter('woocommerce_payment_successful_result', function($result, $order_id) {
    $order = wc_get_order($order_id);
    if ($order && $order->get_payment_method() === 'moneyspace') {
        error_log('MoneySpace Debug Hook: Payment result - ' . json_encode($result));
    }
    return $result;
}, 10, 2);
