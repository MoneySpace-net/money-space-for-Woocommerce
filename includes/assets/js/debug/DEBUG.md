# WooCommerce Blocks Debugging Guide

## Quick Debug Commands

### Enable Debug Mode
```javascript
// In browser console:
localStorage.setItem('ms_debug', 'true');
// Or add ?debug=1 to URL
```

### Check Payment Methods
```javascript
// List all registered payment methods
window.wp.data.select('wc/store/payment').getPaymentMethods();

// Check active payment method
window.wp.data.select('wc/store/checkout').getActivePaymentMethod();
```

### Inspect Block Registry
```javascript
// All registered blocks
window.wp.blocks.getBlockTypes();

// WooCommerce specific blocks
window.wp.blocks.getBlockTypes().filter(block => block.name.includes('woocommerce'));
```

### Check Cart & Checkout Data
```javascript
// Cart totals
window.wp.data.select('wc/store/cart').getCartTotals();

// Billing data
window.wp.data.select('wc/store/checkout').getBillingData();

// Payment processing state
window.wp.data.select('wc/store/checkout').isProcessing();
```

### MoneySpace Specific Debugging
```javascript
// Enable MoneySpace debug
window.msDebug.enable();

// Check blocks registry
window.msDebug.blocks();

// Check checkout store
window.msDebug.checkout();

// Custom log
window.msDebug.log('Test', { data: 'example' });
```

## Browser DevTools Setup

### 1. Open Developer Tools
- Chrome/Edge: F12 or Ctrl+Shift+I (Cmd+Opt+I on Mac)
- Firefox: F12 or Ctrl+Shift+I (Cmd+Opt+I on Mac)

### 2. Check Console Tab
Look for:
- React component errors
- JavaScript errors
- MoneySpace debug output (🔍 prefix)
- WooCommerce block warnings

### 3. Network Tab
Check for:
- Failed API requests
- 404 errors for JS/CSS files
- Slow loading resources

### 4. React DevTools (Install Extension)
- Chrome: [React Developer Tools](https://chrome.google.com/webstore/detail/react-developer-tools/fmkadmapgofadopljbjfkapdkoienihi)
- Firefox: [React DevTools](https://addons.mozilla.org/en-US/firefox/addon/react-devtools/)

## Common Issues & Solutions

### 1. Options Not Rendering
```javascript
// Check if data is reaching component
window.msDebug.log('Payment Data', window.wc?.wcBlocksRegistry?.getPaymentMethods?.());
```

### 2. Component Not Loading
```javascript
// Check if component is registered
window.wp.blocks.getBlockType('woocommerce/checkout-payment-block');
```

### 3. Props Not Passing
```javascript
// Inspect React component tree
// Use React DevTools to inspect CreditCardInstallmentForm props
```

## Testing URLs

### Checkout Page with Debug
```
/checkout?debug=1
```

### Block Editor with Debug
```
/wp-admin/post-new.php?post_type=page&debug=1
```

## Log Files

### WordPress Debug Log
```
/wp-content/debug.log
```

### WooCommerce System Status
```
WooCommerce > Status > System Status
```
