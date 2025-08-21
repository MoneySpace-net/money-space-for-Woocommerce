# MoneySpace Payment Gateway - Complete Debug Guide

## 📋 **Table of Contents**
- [Quick Start](#quick-start)
- [Security & Production Use](#security--production-use)
- [Debug Modes](#debug-modes)
- [WooCommerce Blocks Debugging](#woocommerce-blocks-debugging)
- [Implementation Status](#implementation-status)
- [Troubleshooting](#troubleshooting)

---

## 🚀 **Quick Start**

### **Enable Debug Mode**

#### **Development/Staging**
```javascript
// Method 1: URL parameter
https://your-site.com/checkout/?debug=1

// Method 2: Browser console
window.moneyspaceDebug = true;

// Method 3: localStorage
localStorage.setItem('moneyspace_debug', 'true');
```

#### **Production (Secure)**
```javascript
// For Administrators (after WordPress login)
window.moneyspaceDebug = true;

// For Support Staff (time-limited token)
https://your-site.com/checkout/?debug_token=secure_hash
```

### **Verify Debug Mode**
```javascript
// Check if debug is active
console.log('Debug active:', window.moneyspaceConfig?.userCanDebug);

// Test debug output
debugLog('Test message', { data: 'example' });
```

---

## 🔒 **Security & Production Use**

### **⚠️ Security Assessment**

**Original `?debug=1` Implementation: ❌ INSECURE**
- Anyone could enable debug mode
- Sensitive information exposed in logs
- No authentication or time limits
- Performance impact from excessive logging

**Enhanced Implementation: ✅ SECURE**
- Multi-layer security controls
- User authentication required
- Time-limited access tokens
- Environment-aware behavior

### **🛡️ Security Architecture**

#### **Environment Detection**
```javascript
// Automatically detects production vs development
const isProduction = window.moneyspaceConfig?.environment === 'production';
```

#### **User Authorization**
```php
// PHP Backend - Only authorized users
current_user_can('manage_options') ||        // Administrators
current_user_can('moneyspace_debug') ||      // Custom capability  
current_user_can('manage_woocommerce');      // Shop managers
```

#### **Token-Based Access**
```php
// Secure, time-limited tokens (30 minutes)
$token = base64_encode($user_id . '|' . $expiry . '|' . $hmac_hash);
```

### **📊 Security Comparison**

| Feature | Before (Insecure) | After (Secure) |
|---------|-------------------|----------------|
| **Public Access** | ❌ Anyone | ✅ Authorized only |
| **Authentication** | ❌ None | ✅ WordPress login |
| **Time Limits** | ❌ Permanent | ✅ 30 minutes |
| **Environment Aware** | ❌ No | ✅ Production/dev |
| **Audit Trail** | ❌ None | ✅ Full logging |
| **Token Security** | ❌ None | ✅ HMAC signed |

---

## 🎯 **Debug Modes**

### **Production Mode (Default)**
- Clean console output
- No debug messages
- Professional user experience
- Security controls active

### **Debug Mode (`?debug=1` or authorized access)**
- Detailed logging for all components
- Payment validation tracking
- Component lifecycle monitoring
- Error context and stack traces

### **Global Debug Functions**
```javascript
// Enable/disable debug
window.msDebug.enable();
window.msDebug.disable();

// Check debug status
window.msDebug.isEnabled();

// Debug specific components
window.msDebug.blocks();     // WooCommerce Blocks registry
window.msDebug.checkout();   // Checkout store data
window.msDebug.log(msg, data); // Custom logging
```

---

## 🧪 **WooCommerce Blocks Debugging**

### **Payment Method Registry**
```javascript
// List all registered payment methods
window.wp.data.select('wc/store/payment').getPaymentMethods();

// Check active payment method
window.wp.data.select('wc/store/checkout').getActivePaymentMethod();

// MoneySpace specific payment methods
window.wp.data.select('wc/store/payment').getPaymentMethods()
    .filter(method => method.name.includes('moneyspace'));
```

### **Checkout Store Data**
```javascript
// Cart totals and data
window.wp.data.select('wc/store/cart').getCartTotals();

// Billing information
window.wp.data.select('wc/store/checkout').getBillingData();

// Payment processing state
window.wp.data.select('wc/store/checkout').isProcessing();

// Validation errors
window.wp.data.select('wc/store/checkout').getCheckoutErrors();
```

### **Block Registry Inspection**
```javascript
// All registered blocks
window.wp.blocks.getBlockTypes();

// WooCommerce specific blocks
window.wp.blocks.getBlockTypes()
    .filter(block => block.name.includes('woocommerce'));

// Check if MoneySpace blocks are registered
window.wp.blocks.getBlockType('woocommerce/checkout-payment-block');
```

### **Component State Debugging**
```javascript
// Debug React component props (use React DevTools)
// 1. Install React Developer Tools browser extension
// 2. Navigate to Components tab
// 3. Search for "CreditCardForm" or "CreditCardInstallmentForm"
// 4. Inspect props and state

// Debug payment data submission
debugLog('Payment Data Submitted', {
    paymentMethod: 'moneyspace_creditcard',
    formData: { /* form state */ },
    validationErrors: { /* errors */ }
});
```

---

## ✅ **Implementation Status**

### **Debug Utility System: COMPLETE**

#### **Core Components**
- ✅ **Centralized Debug Utility** (`includes/assets/js/utils/debug.js`)
- ✅ **Security Configuration** (`includes/debug-config.php`)
- ✅ **Environment Detection** - Production vs development
- ✅ **User Authorization** - WordPress capability system
- ✅ **Token-Based Access** - Secure, time-limited debug URLs

#### **Files Updated**
- ✅ `CreditCardForm.js` - 15+ debug calls implemented
- ✅ `CreditCardInstallmentForm.js` - Local debug replaced with centralized
- ✅ `qrcode.js` - 3 debug calls implemented  
- ✅ `moneyspace-notice-clearing.js` - 6+ debug calls implemented
- ✅ `debug-helper.js` - Updated to use centralized system

#### **Build Status**
- ✅ **Webpack Build**: All files compile successfully
- ✅ **No Breaking Changes**: Backwards compatible
- ✅ **Production Ready**: Clean console output

### **Before vs After Enhancement**

#### **Before**
```javascript
// Always logged (production spam)
console.log('Payment method changed:', data);
console.error('Validation failed:', error);
```

#### **After**  
```javascript
// Only logs when debug enabled
debugLog('Payment method changed:', data);
debugError('Validation failed:', error);
```

---

## 🔍 **Troubleshooting**

### **Common Debug Scenarios**

#### **Payment Method Not Working**
1. Add `?debug=1` to checkout URL (dev) or enable secure debug (production)
2. Open browser console (F12)
3. Look for `[MoneySpace]` prefixed messages
4. Check for validation errors or component issues
5. Verify payment method registration:
   ```javascript
   window.wp.data.select('wc/store/payment').getPaymentMethods()
   ```

#### **Component Not Rendering**
1. Check React DevTools for component tree
2. Verify payment method registration:
   ```javascript
   window.wp.blocks.getBlockType('woocommerce/checkout-payment-block');
   ```
3. Check for JavaScript errors in console
4. Verify component props:
   ```javascript
   debugLog('Component Props', { props, state });
   ```

#### **Validation Issues**
1. Enable debug mode
2. Monitor validation messages:
   ```javascript
   debugLog('Validation Errors', validationErrors);
   ```
3. Check form data:
   ```javascript
   debugLog('Form Data', formData);
   ```

#### **API/Payment Processing Issues**
1. Check Network tab for failed requests
2. Look for CORS or authentication errors  
3. Verify API endpoint responses
4. Check payment data submission:
   ```javascript
   debugLog('Payment API Call', { endpoint, payload, response });
   ```

### **Browser DevTools Setup**

#### **Open Developer Tools**
- **Chrome/Edge**: F12 or Ctrl+Shift+I (Cmd+Opt+I on Mac)
- **Firefox**: F12 or Ctrl+Shift+I (Cmd+Opt+I on Mac)

#### **Essential Tabs**
- **Console**: Debug messages and errors
- **Network**: API requests and responses  
- **Elements**: DOM inspection
- **React DevTools**: Component state (install extension)

#### **Debug Output Filtering**
```javascript
// Filter console output to MoneySpace only
// In browser console, click "Filter" and enter: [MoneySpace]
```

---

## 📞 **Support Integration**

### **For Support Staff**
1. **Request debug access** from administrator
2. **Receive secure debug URL** (30-minute expiry)
3. **Capture debug output** from browser console
4. **Look for specific error patterns**:
   - `[MoneySpace Error]` - Critical errors
   - `[MoneySpace]` - General debug info
   - Validation failures
   - API communication issues

### **For Administrators**
1. **Generate debug tokens** for support staff
2. **Monitor debug access** via audit logs
3. **Review security settings** regularly

### **Debug Information to Collect**
- Browser console output (with `[MoneySpace]` messages)
- Network tab showing API requests/responses
- WordPress admin error logs
- WooCommerce system status report

---

## 📁 **File Organization**

```
money-space-for-woocommerce/
├── DEBUG.md                           # This complete guide
├── test-debug-utility.html            # Interactive debug test page
├── includes/
│   ├── debug-config.php              # Security configuration
│   └── assets/js/
│       └── utils/debug.js            # Centralized debug utility
└── docs/
    └── TROUBLESHOOTING.md            # User-facing troubleshooting
```

---

## 🎉 **Summary**

The MoneySpace Payment Gateway now features a **production-safe, comprehensive debug system** that provides:

- **🔒 Security**: Authentication required, time-limited access
- **🧪 Comprehensive Debugging**: All components instrumented  
- **🎯 Environment Aware**: Different behavior for production vs development
- **📊 Professional UX**: Clean console in production, rich debugging when needed
- **🛠️ Developer Friendly**: Easy activation and detailed information

**Result**: A secure, maintainable, and professional debug system suitable for production deployment! ✨
