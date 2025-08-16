<?php
/**
 * MoneySpace Plugin Health Check
 * Run this script to verify the plugin is working correctly
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    die('Direct access not permitted');
}

function moneyspace_health_check() {
    $results = array();
    
    // Check if plugin constants are defined
    $results['constants'] = array(
        'MNS_ID' => defined('MNS_ID') ? '✅ Defined: ' . MNS_ID : '❌ Not defined (using fallback)',
        'MNS_ID_INSTALLMENT' => defined('MNS_ID_INSTALLMENT') ? '✅ Defined: ' . MNS_ID_INSTALLMENT : '❌ Not defined (using fallback)',
        'MNS_ID_QRPROM' => defined('MNS_ID_QRPROM') ? '✅ Defined: ' . MNS_ID_QRPROM : '❌ Not defined (using fallback)',
    );
    
    // Check if WooCommerce is active
    $results['woocommerce'] = class_exists('WC') ? '✅ WooCommerce is active' : '❌ WooCommerce is not active';
    
    // Check if payment gateways are available
    if (class_exists('WC') && function_exists('WC')) {
        $gateways = WC()->payment_gateways ? WC()->payment_gateways->get_available_payment_gateways() : array();
        $results['gateways'] = array(
            'total' => count($gateways) . ' payment gateways available',
            'moneyspace' => isset($gateways['moneyspace']) ? '✅ MoneySpace gateway found' : '❌ MoneySpace gateway not found',
            'moneyspace_installment' => isset($gateways['moneyspace_installment']) ? '✅ Installment gateway found' : '❌ Installment gateway not found',
            'moneyspace_qrprom' => isset($gateways['moneyspace_qrprom']) ? '✅ QR gateway found' : '❌ QR gateway not found',
        );
    } else {
        $results['gateways'] = '❌ WooCommerce not initialized';
    }
    
    // Check if blocks are registered
    $results['blocks'] = array(
        'credit_card' => class_exists('MoneySpace\Payments\MoneySpace_CreditCard') ? '✅ Credit Card block class exists' : '❌ Credit Card block class missing',
        'installment' => class_exists('MoneySpace\Payments\MoneySpace_CreditCard_Installment') ? '✅ Installment block class exists' : '❌ Installment block class missing',
        'qr_code' => class_exists('MoneySpace\Payments\MoneySpace_QRCode') ? '✅ QR Code block class exists' : '❌ QR Code block class missing',
    );
    
    // Check if JavaScript assets exist
    $results['assets'] = array(
        'credit_card_js' => file_exists(plugin_dir_path(__FILE__) . 'assets/js/frontend/blocks-ms-creditcard.js') ? '✅ Credit card JS exists' : '❌ Credit card JS missing',
        'installment_js' => file_exists(plugin_dir_path(__FILE__) . 'assets/js/frontend/blocks-ms-creditcard-installment.js') ? '✅ Installment JS exists' : '❌ Installment JS missing',
        'qr_js' => file_exists(plugin_dir_path(__FILE__) . 'assets/js/frontend/blocks-ms-qr.js') ? '✅ QR code JS exists' : '❌ QR code JS missing',
    );
    
    return $results;
}

// Only show health check in admin or when debugging
if (is_admin() || (defined('WP_DEBUG') && WP_DEBUG)) {
    add_action('admin_notices', function() {
        if (isset($_GET['moneyspace_health_check'])) {
            $health = moneyspace_health_check();
            echo '<div class="notice notice-info"><p><strong>MoneySpace Plugin Health Check:</strong></p>';
            echo '<pre>' . print_r($health, true) . '</pre>';
            echo '</div>';
        }
    });
}
