# 🚀 MoneySpace Payment Gateway for WooCommerce - Installation Guide

[![WordPress](https://img.shields.io/badge/WordPress-6.0+-blue.svg)](https://wordpress.org/)
[![WooCommerce](https://img.shields.io/badge/WooCommerce-8.0+-purple.svg)](https://woocommerce.com/)
[![PHP](https://img.shields.io/badge/PHP-8.0+-777BB4.svg)](https://php.net/)
[![License](https://img.shields.io/badge/License-GPL--2.0+-red.svg)](LICENSE)

A comprehensive WordPress/WooCommerce payment gateway plugin that enables secure online payments through credit cards, QR codes (PromptPay), and installment plans. This plugin integrates with MoneySpace Payment Gateway to provide Thai businesses with a complete payment solution.

## 📋 Table of Contents

- [🎯 Overview](#-overview)
- [⚡ Quick Start](#-quick-start)
- [📦 Requirements](#-requirements)
- [🔧 Installation Methods](#-installation-methods)
- [🛠️ Configuration](#️-configuration)
- [💳 Payment Methods Setup](#-payment-methods-setup)
- [🔐 Security Setup](#-security-setup)
- [🧪 Testing](#-testing)
- [🚀 Going Live](#-going-live)
- [📚 Advanced Configuration](#-advanced-configuration)
- [❗ Troubleshooting](#-troubleshooting)
- [🔄 Updates](#-updates)
- [📞 Support](#-support)

## 🎯 Overview

### Features
- **Credit Card Payments** - Secure 3D-secured card processing
- **QR Code Payments** - PromptPay integration for instant mobile payments
- **Installment Plans** - Monthly installments with KTC, BAY, and FCY banks
- **WooCommerce Blocks** - Modern checkout experience with Gutenberg blocks
- **Multi-Currency** - Support for up to 27 foreign currencies
- **PCI DSS Compliance** - Bank of Thailand approved payment method
- **Mobile Responsive** - Optimized for all devices

### Supported Payment Methods
| Payment Type | Banks/Providers | Features |
|-------------|----------------|----------|
| Credit Cards | All major Thai banks | 3D Secure, International cards |
| QR Payments | PromptPay | Instant mobile banking |
| Installments | KTC, BAY, FCY | 3-12 months terms |

## ⚡ Quick Start

### 1. Download and Install
```bash
# Download the latest release
wget https://github.com/MoneySpace-net/money-space-for-Woocommerce/releases/latest/download/money-space-for-woocommerce.zip

# Extract to WordPress plugins directory
unzip money-space-for-woocommerce.zip -d /wp-content/plugins/
```

### 2. Activate Plugin
```bash
# Via WP-CLI (recommended)
wp plugin activate money-space-for-woocommerce

# Or via WordPress Admin: Plugins → Installed Plugins → Money Space → Activate
```

### 3. Configure Payment Gateway
1. Navigate to **WooCommerce → Settings → Payments**
2. Enable desired payment methods (Credit Card, QR Code, Installments)
3. Configure API credentials from MoneySpace dashboard
4. Test payments in sandbox mode

## 📦 Requirements

### Minimum System Requirements
| Component | Version | Notes |
|-----------|---------|-------|
| **WordPress** | 6.0+ | Latest LTS recommended |
| **WooCommerce** | 8.0+ | Required for e-commerce functionality |
| **PHP** | 8.0+ | PHP 8.1+ recommended for performance |
| **MySQL** | 5.7+ | Or MariaDB 10.3+ |
| **SSL Certificate** | Required | Essential for payment processing |

### PHP Extensions
```ini
# Required PHP extensions
extension=curl      # For API communication
extension=json      # For data processing
extension=mbstring  # For text handling
extension=openssl   # For encryption
extension=zip       # For updates
```

### WordPress Plugins
- **WooCommerce** (8.0+) - Core e-commerce functionality
- **WooCommerce Blocks** (included with WooCommerce 8.0+)

## 🔧 Installation Methods

### Method 1: WordPress Admin Panel (Recommended)

1. **Download Plugin**
   ```
   Visit: https://github.com/MoneySpace-net/money-space-for-Woocommerce/releases
   Download: money-space-for-woocommerce.zip
   ```

2. **Upload via Admin**
   ```
   WordPress Admin → Plugins → Add New → Upload Plugin
   Choose File → Select downloaded ZIP → Install Now
   ```

3. **Activate Plugin**
   ```
   Plugins → Installed Plugins → Money Space → Activate
   ```

### Method 2: FTP/SFTP Upload

1. **Extract Plugin**
   ```bash
   unzip money-space-for-woocommerce.zip
   ```

2. **Upload Files**
   ```bash
   # Upload to your WordPress installation
   scp -r money-space-for-woocommerce/ user@yourserver.com:/path/to/wp-content/plugins/
   ```

3. **Set Permissions**
   ```bash
   chmod 755 /wp-content/plugins/money-space-for-woocommerce/
   chmod 644 /wp-content/plugins/money-space-for-woocommerce/*.php
   ```

### Method 3: WP-CLI (Advanced)

1. **Download and Install**
   ```bash
   # Download from GitHub releases
   wp plugin install https://github.com/MoneySpace-net/money-space-for-Woocommerce/releases/latest/download/money-space-for-woocommerce.zip

   # Activate the plugin
   wp plugin activate money-space-for-woocommerce

   # Verify installation
   wp plugin list | grep money-space
   ```

## 🛠️ Configuration

### Initial Setup Wizard

After activation, follow the setup wizard:

1. **Access Configuration**
   ```
   WooCommerce → Settings → Payments → Money Space Payment Methods
   ```

2. **API Configuration**
   - Register at [MoneySpace.net](https://www.moneyspace.net)
   - Get API credentials from your dashboard
   - Configure webhook endpoints

### Environment Configuration

#### Development Environment
```php
# wp-config.php
define('MONEYSPACE_ENVIRONMENT', 'sandbox');
define('MONEYSPACE_DEBUG', true);
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

#### Production Environment
```php
# wp-config.php
define('MONEYSPACE_ENVIRONMENT', 'production');
define('MONEYSPACE_DEBUG', false);
```

## 💳 Payment Methods Setup

### 1. Credit Card Setup

#### Basic Configuration
```
Navigate to: WooCommerce → Settings → Payments → MoneySpace Credit Card

✅ Enable Payment Method
📝 Title: "Credit Card (Visa, MasterCard)"
📝 Description: "Pay securely with your credit or debit card"
🔐 Secret ID: [From MoneySpace Dashboard]
🔐 Secret Key: [From MoneySpace Dashboard]
💰 Fee Responsibility: Merchant/Customer
📱 UI Template: Modern/Classic
```

#### Advanced Options
```ini
# Customer Information in Transaction ID
✅ Include First Name
✅ Include Last Name  
✅ Include Email
✅ Include Phone
✅ Include Address
```

#### Security Settings
```ini
# 3D Secure Configuration
3ds_enabled = true
3ds_version = 2.0
fallback_3ds1 = true
```

### 2. QR Code (PromptPay) Setup

#### Basic Configuration
```
Navigate to: WooCommerce → Settings → Payments → MoneySpace QR Code

✅ Enable Payment Method
📝 Title: "QR Code Payment (PromptPay)"
📝 Description: "Scan to pay with mobile banking"
⏱️ Expiration Time: 15 minutes
🔄 Auto Check Status: Every 5 seconds
```

#### QR Code Features
```ini
# QR Code Options
qr_size = 256x256
qr_format = PNG
download_enabled = true
auto_refresh = true
timeout_warning = 120 # seconds before expiry
```

### 3. Installment Plans Setup

#### Basic Configuration
```
Navigate to: WooCommerce → Settings → Payments → MoneySpace Installments

✅ Enable Payment Method
📝 Title: "Monthly Installments"
📝 Description: "Pay in monthly installments"
💰 Interest Responsibility: Customer/Merchant
💰 Minimum Amount: 3,001 THB
```

#### Bank Configuration
```ini
# KTC Bank
✅ Enable KTC
📅 Max Months: 12
💳 Cards: All KTC credit cards

# BAY Bank  
✅ Enable BAY
📅 Max Months: 10
💳 Cards: All BAY credit cards

# FCY Bank
✅ Enable FCY
📅 Max Months: 12
💳 Cards: All FCY credit cards
```

#### Installment Terms
| Bank | Min Amount | Max Months | Interest Rate |
|------|------------|------------|---------------|
| KTC  | 3,001 THB  | 3-12       | 0.8% per month |
| BAY  | 3,001 THB  | 3-10       | 0.8% per month |
| FCY  | 3,001 THB  | 3-12       | 1.0% per month |

## 🔐 Security Setup

### 1. API Credentials Setup

#### Generate Webhook Credentials
1. **Login to MoneySpace Dashboard**
   ```
   URL: https://www.moneyspace.net
   Navigate: Dashboard → Webhooks
   ```

2. **Create Webhook**
   ```
   Domain: https://yourstore.com
   Webhook URL: https://yourstore.com/ms/webhook
   ```

3. **Copy Credentials**
   ```
   Secret ID: [Copy from dashboard]
   Secret Key: [Copy from dashboard]
   ```

#### WordPress Configuration
```php
# Store credentials securely
add_option('moneyspace_secret_id', 'your_secret_id_here');
add_option('moneyspace_secret_key', 'your_secret_key_here');
```

### 2. SSL Certificate
```bash
# Verify SSL is working
curl -I https://yourstore.com
# Should return: HTTP/2 200

# Test webhook endpoint
curl -X POST https://yourstore.com/ms/webhook
```

### 3. Security Headers
```apache
# .htaccess security headers
Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options DENY
Header always set X-XSS-Protection "1; mode=block"
Header always set Strict-Transport-Security "max-age=63072000"
```

## 🧪 Testing

### 1. Test Mode Configuration
```php
# Enable test mode
update_option('moneyspace_test_mode', 'yes');
```

### 2. Test Credit Card Numbers
| Card Type | Number | CVV | Expiry |
|-----------|--------|-----|--------|
| Visa Success | 4242424242424242 | 123 | 12/25 |
| Visa Decline | 4000000000000002 | 123 | 12/25 |
| MasterCard Success | 5555555555554444 | 123 | 12/25 |

### 3. Test Scenarios
```bash
# Test checkout process
1. Add product to cart
2. Proceed to checkout
3. Select payment method
4. Use test card numbers
5. Verify order status
6. Check webhook logs
```

### 4. Webhook Testing
```bash
# Test webhook endpoint
curl -X POST https://yourstore.com/ms/webhook \
  -H "Content-Type: application/json" \
  -d '{"test": "webhook"}'
```

## 🚀 Going Live

### Pre-Launch Checklist

#### 1. Environment Setup
```ini
✅ SSL Certificate installed and verified
✅ Production API credentials configured  
✅ Test mode disabled
✅ Debug logging disabled
✅ Security headers implemented
✅ Backup system in place
```

#### 2. Payment Testing
```ini
✅ Credit card payments tested
✅ QR code payments tested  
✅ Installment plans tested
✅ Webhook notifications working
✅ Order status updates verified
✅ Email notifications working
```

#### 3. Performance Optimization
```bash
# Enable object caching
wp plugin install redis-cache
wp redis enable

# Enable GZIP compression
echo "gzip on;" >> /etc/nginx/nginx.conf

# Optimize database
wp db optimize
```

### Production Configuration
```php
# wp-config.php production settings
define('MONEYSPACE_ENVIRONMENT', 'production');
define('MONEYSPACE_DEBUG', false);
define('WP_DEBUG', false);
define('WP_DEBUG_LOG', false);
define('SCRIPT_DEBUG', false);
```

## 📚 Advanced Configuration

### 1. Custom Templates
```php
# Override payment templates
copy_template(
    '/plugins/money-space-for-woocommerce/templates/',
    '/themes/your-theme/woocommerce/moneyspace/'
);
```

### 2. Hooks and Filters
```php
# Customize payment behavior
add_filter('moneyspace_payment_args', function($args) {
    $args['custom_field'] = 'custom_value';
    return $args;
});

# After successful payment
add_action('moneyspace_payment_complete', function($order_id) {
    // Custom logic after payment
});
```

### 3. Multi-Site Configuration
```php
# Network-wide settings
add_site_option('moneyspace_network_settings', array(
    'api_mode' => 'production',
    'shared_credentials' => true
));
```

### 4. Performance Tuning
```php
# Cache API responses
add_filter('moneyspace_cache_responses', '__return_true');

# Optimize database queries
add_filter('moneyspace_optimize_queries', '__return_true');
```

## ❗ Troubleshooting

### Common Issues

#### 1. Payment Not Processing
```bash
# Check logs
tail -f /wp-content/debug.log | grep moneyspace

# Verify API credentials
wp option get moneyspace_secret_id
wp option get moneyspace_secret_key

# Test API connection
curl -H "Authorization: Bearer YOUR_SECRET_KEY" \
  https://api.moneyspace.net/v1/health
```

#### 2. Webhook Not Receiving
```bash
# Check webhook URL
echo "https://yoursite.com/ms/webhook"

# Test webhook manually
curl -X POST "https://yoursite.com/ms/webhook" \
  -H "Content-Type: application/json" \
  -d '{"transaction_id": "test123"}'

# Check server logs
tail -f /var/log/nginx/access.log | grep ms/webhook
```

#### 3. SSL Certificate Issues
```bash
# Check SSL certificate
openssl s_client -connect yoursite.com:443 -servername yoursite.com

# Verify certificate chain
curl -I https://yoursite.com

# Test webhook with SSL
curl -k -X POST https://yoursite.com/ms/webhook
```

### Debug Mode
```php
# Enable detailed logging
define('MONEYSPACE_DEBUG', true);
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);

# View debug logs
tail -f /wp-content/debug.log
```

### Error Codes
| Code | Description | Solution |
|------|-------------|----------|
| 401 | Unauthorized | Check API credentials |
| 403 | Forbidden | Verify webhook URL |
| 404 | Not Found | Check endpoint configuration |
| 500 | Server Error | Check server logs |

## 🔄 Updates

### Automatic Updates
```php
# Enable automatic updates
add_filter('auto_update_plugin', function($update, $item) {
    if ($item->slug === 'money-space-for-woocommerce') {
        return true;
    }
    return $update;
}, 10, 2);
```

### Manual Updates
```bash
# Backup before updating
wp db export backup-$(date +%Y%m%d).sql

# Update via WP-CLI
wp plugin update money-space-for-woocommerce

# Verify update
wp plugin list | grep money-space
```

### Update Notifications
- **Email Notifications** - Automatic email when updates available
- **Admin Dashboard** - Update notices in WordPress admin
- **Version Checking** - Automatic check every 24 hours

## 📞 Support

### Documentation
- **Plugin Documentation**: [GitHub Wiki](https://github.com/MoneySpace-net/money-space-for-Woocommerce/wiki)
- **API Documentation**: [MoneySpace API Docs](https://docs.moneyspace.net)
- **WooCommerce Docs**: [WooCommerce Documentation](https://docs.woocommerce.com)

### Community Support
- **GitHub Issues**: [Report Bugs](https://github.com/MoneySpace-net/money-space-for-Woocommerce/issues)
- **WordPress Forums**: [WordPress Support](https://wordpress.org/support/plugin/money-space-for-woocommerce)
- **WooCommerce Community**: [WooCommerce Forums](https://wordpress.org/support/plugin/woocommerce)

### Commercial Support
- **MoneySpace Support**: support@moneyspace.net
- **Priority Support**: Available for enterprise customers
- **Implementation Services**: Professional setup and customization

### Emergency Contact
```
🚨 Critical Issues (Production Down):
📧 emergency@moneyspace.net
📞 +66 (0) 2-xxx-xxxx
⏰ 24/7 Support for Enterprise customers
```

---

## 📄 License

This plugin is licensed under the GPL-2.0+ License. See [LICENSE](LICENSE) file for details.

## 🤝 Contributing

We welcome contributions! Please see our [Contributing Guide](CONTRIBUTING.md) for details.

## 🏷️ Version History

See [CHANGELOG.md](CHANGELOG.md) for detailed version history and updates.

---

**Made with ❤️ by [MoneySpace](https://www.moneyspace.net) for the WordPress community**

*Last updated: January 2025*
