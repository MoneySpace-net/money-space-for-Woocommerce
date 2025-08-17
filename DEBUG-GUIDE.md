# MoneySpace Payment Gateway - Debug Mode

## How to Enable Debug Mode

### Option 1: URL Parameter (Temporary)
Add `?debug=moneyspace` to your checkout URL:
```
https://yoursite.com/checkout/?debug=moneyspace
```

### Option 2: Browser Console (Temporary)
In browser console, run:
```javascript
localStorage.setItem('moneyspace_debug', 'true');
// Reload page to activate
location.reload();
```

### Option 3: WordPress Debug Constant (Persistent)
Add to your `wp-config.php`:
```php
define('MONEYSPACE_DEBUG', true);
```

## Debug Features Available

When debug mode is active, you'll see:
- ✅ Payment method selection changes
- ✅ Installment form state changes  
- ✅ Validation results
- ✅ Payment data being sent to server
- ✅ Error details

## Disable Debug Mode

### Remove URL parameter or run in console:
```javascript
localStorage.removeItem('moneyspace_debug');
location.reload();
```

## Production Notes

- Debug logs are automatically removed in production builds
- Only error logs remain for troubleshooting
- Performance is optimized for production use
- Clean console output for end users

## Troubleshooting

If you need debug logs for support:

1. Enable debug mode using one of the methods above
2. Reproduce the issue
3. Check browser console (F12 → Console)
4. Copy relevant logs to share with support

## Log Types

- 🔧 **Form Changes**: Field updates and validation
- 🏦 **Bank Selection**: Bank and installment changes  
- 💳 **Payment Data**: Data sent to payment gateway
- ❌ **Errors**: Critical errors and validation failures
- 🔍 **Validation**: Form validation results
