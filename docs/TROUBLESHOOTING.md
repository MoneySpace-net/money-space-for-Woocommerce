# MoneySpace Payment Gateway - Troubleshooting Guide

## Table of Contents
- [Notice Clearing Issues](#notice-clearing-issues)
- [Dropdown Interaction Problems](#dropdown-interaction-problems)
- [Transaction Failures](#transaction-failures)
- [Debugging Tools](#debugging-tools)

## Notice Clearing Issues

### Problem
WooCommerce validation notices (especially installment minimum amount errors) not clearing when switching payment methods.

### Solution
A comprehensive notice clearing system has been implemented:

1. **Global Notice Clearing Utility** (`moneyspace-notice-clearing.js`)
2. **Component-level clearing** in React components
3. **Event-driven clearing** when payment methods change

### Testing
1. **Enable Debug Mode**:
   ```javascript
   // In browser console
   localStorage.setItem('moneyspace_debug', 'true');
   location.reload();
   
   // Or add to URL: ?debug=moneyspace
   ```

2. **Test Steps**:
   - Add product with amount **less than 3,000 THB** to cart
   - Go to checkout page
   - Select "MoneySpace Installment" → See error message
   - Switch to "MoneySpace QR Code" → Error should clear automatically
   - Switch back to installment → Error should reappear

## Dropdown Interaction Problems

### Problem
Dropdown menus in installment payment forms not clickable or interactive.

### Root Cause
Conflicting event listeners and CSS z-index issues interfering with dropdown functionality.

### Solution Applied
1. **Removed conflicting event listeners** from payment container
2. **Enhanced dropdown styling** with proper z-index
3. **Specific event handling** only for payment method radio buttons
4. **CSS fixes** for pointer events and positioning

### Verification
- All dropdown menus in installment forms should be fully interactive
- No conflicts with payment method switching
- Proper styling and accessibility maintained

## Transaction Failures

### KTbank Transaction Analysis
For failed transactions like `KTBCC1708252158074498283`:

**Transaction ID Format**: `[BANK][PAYMENT_TYPE][TIMESTAMP][SEQUENCE]`
- `KTB` = KTbank
- `CC` = Credit Card
- `1708252158074` = Timestamp (2024-02-18 17:29:18)
- `498283` = Sequence number

**Common Failure Causes**:
1. **3D Secure Authentication Failure** (most common)
2. **Card declined by bank**
3. **Insufficient funds**
4. **Network timeout during payment**

**Resolution Steps**:
1. Check MoneySpace dashboard for detailed error message
2. Contact KTbank customer service: `02-208-8888`
3. Verify card status and limits with cardholder
4. Retry payment with different card if needed

## Debugging Tools

### Enable Debug Mode
```javascript
// Method 1: URL parameter
?debug=moneyspace

// Method 2: localStorage
localStorage.setItem('moneyspace_debug', 'true');

// Method 3: WordPress config (for developers)
define('MONEYSPACE_DEBUG', true);
```

### Debug Features
- **Component lifecycle logging**
- **Payment data validation**
- **Event handler tracking**
- **Notice clearing operations**
- **Transaction processing steps**

### Transaction Debug Tool
A PHP debugging tool is available for analyzing failed transactions:

```php
// Run: php debug-transaction.php TRANSACTION_ID
// Example: php debug-transaction.php KTBCC1708252158074498283
```

### Log Locations
- **Browser Console**: Real-time JavaScript debugging
- **WordPress Debug Log**: PHP errors and API responses
- **MoneySpace Dashboard**: Payment gateway specific logs

## Getting Help

### For Developers
1. Enable debug mode and check console logs
2. Review this troubleshooting guide
3. Check the main `DEBUG.md` for comprehensive technical details

### For Users
1. Contact website administrator
2. For payment issues, contact the bank directly
3. Keep transaction IDs for reference

### Support Contacts
- **MoneySpace Support**: Check dashboard or documentation
- **KTbank Customer Service**: `02-208-8888`
- **Plugin Issues**: Check GitHub repository issues

---

**Last Updated**: August 2025  
**Plugin Version**: 2.13.3+
