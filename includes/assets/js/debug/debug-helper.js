/**
 * Debug Helper for WooCommerce Blocks
 * Use this to debug your payment gateway components
 */

import { debugLog, isDebugMode } from '../utils/debug';

export const debugPaymentData = (componentName, props) => {
    debugLog(`${componentName} Props`, {
        props,
        timestamp: new Date().toISOString(),
        userAgent: navigator.userAgent,
        url: window.location.href
    });
};

export const debugBlocksRegistry = () => {
    debugLog('WC Blocks Registry', {
        blocks: window.wp?.blocks?.getBlockTypes?.() || 'No blocks found',
        components: window.wc?.blocksCheckout || 'No checkout components',
        woocommerce: window.wc || 'WooCommerce not loaded'
    });
};

export const debugCheckoutStore = () => {
    if (window.wp?.data) {
        const checkoutStore = window.wp.data.select('wc/store/checkout');
        debugLog('Checkout Store', {
            paymentMethods: checkoutStore?.getActivePaymentMethod?.() || 'No payment methods',
            cartData: checkoutStore?.getCartData?.() || 'No cart data',
            billingData: checkoutStore?.getBillingData?.() || 'No billing data'
        });
    }
};

// Auto-enable debug mode if URL contains debug=1
if (window.location.search.includes('debug=1')) {
    localStorage.setItem('moneyspace_debug', 'true');
    debugLog('🔍 MoneySpace Debug Mode Enabled');
}

// Global debug functions
window.msDebug = {
    enable: () => localStorage.setItem('moneyspace_debug', 'true'),
    disable: () => localStorage.removeItem('moneyspace_debug'),
    blocks: debugBlocksRegistry,
    checkout: debugCheckoutStore,
    log: debugLog,
    isEnabled: isDebugMode
};
