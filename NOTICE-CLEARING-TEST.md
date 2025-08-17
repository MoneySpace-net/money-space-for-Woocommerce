# Testing MoneySpace Notice Clearing

## Steps to Test the Fix

1. **Enable Debug Mode** (choose one):
   ```javascript
   // Option 1: In browser console
   localStorage.setItem('moneyspace_debug', 'true');
   location.reload();
   
   // Option 2: Add to URL
   ?debug=moneyspace
   ```

2. **Go to Checkout Page**:
   - Add a product with amount **less than 3,000 baht** to cart
   - Go to checkout page
   - Select "MoneySpace Installment" payment method
   - You should see error: "The amount of balance must be 3,000.01 baht or more"

3. **Test Notice Clearing**:
   - Switch to different payment method (Credit Card, QR Code, etc.)
   - **The installment error should disappear immediately**
   - Check browser console for debug messages:
     ```
     [MoneySpace Notice] 🧹 Clearing installment validation notices...
     [MoneySpace Notice] 🗑️ Cleared: "The amount of balance must be 3,000.01 baht or more..."
     [MoneySpace Notice] ✅ Cleared 1 installment notices
     ```

## Expected Results

### ✅ **Before Fix** (Problem):
- Installment validation error stays visible
- Error message persists when switching payment methods
- Confusing user experience

### ✅ **After Fix** (Solution):
- Error message clears immediately when switching payment methods
- Clean user interface
- Debug logs show notice clearing activity
- Multiple notice clearing methods ensure reliability

## Debug Information

When debug mode is enabled, you'll see console messages like:
```
[MoneySpace Notice] 🎧 Initializing payment method change listeners...
[MoneySpace Notice] ✅ Added listeners to 3 payment method radios
[MoneySpace Notice] 💳 Payment method changed {from: "moneyspace_installment", to: "moneyspace"}
[MoneySpace Notice] 🧹 Clearing installment validation notices...
[MoneySpace Notice] 🗑️ Cleared: "The amount of balance must be 3,000.01 baht or more in order to make the installment payment..."
[MoneySpace Notice] ✅ Cleared 1 installment notices
```

## Error Patterns Detected

The notice clearing system looks for these error patterns:
- "Installment validation failed"
- "Minimum amount not met"  
- "installment"
- "3,000" or "3000"
- "bank"
- "Minimum amount"
- "amount of balance must be"
- Thai text: "ยอดเงินขั้นต่ำ", "ผ่อนชำระ"
- Bank codes: "KTC", "BAY", "FCY"

## Manual Testing Commands

### Clear Notices Manually:
```javascript
// Clear installment notices immediately
MoneySpaceNoticeClearing.clearInstallmentNotices();

// Reinitialize listeners
MoneySpaceNoticeClearing.initPaymentMethodListeners();
```

### Check Status:
```javascript
// Check if utility is loaded
console.log(window.MoneySpaceNoticeClearing);

// Check current payment method
document.querySelector('input[name="radio-control-wc-payment-method-options"]:checked')?.value;
```

This fix ensures a clean user experience by automatically clearing irrelevant validation errors when customers switch between payment methods.
