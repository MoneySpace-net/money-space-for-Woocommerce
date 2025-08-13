# MoneySpace WooCommerce Plugin Development Instructions

## Project Overview
This is a WordPress/WooCommerce payment gateway plugin that integrates MoneySpace Payment Gateway for Thai e-commerce businesses. The plugin supports credit card payments, QR code (PromptPay) payments, and installment plans.

## Technology Stack
- **WordPress**: 6.0+ (target latest LTS)
- **WooCommerce**: 8.0+ with Blocks support
- **PHP**: 8.0+ (prefer 8.1+ for performance)
- **JavaScript/React**: ES6+ with @wordpress/element
- **Build Tools**: @wordpress/scripts with Webpack 5
- **CSS**: SCSS with PostCSS processing

## Code Structure and Architecture

### Plugin Architecture
```
money-space-for-woocommerce/
├── moneyspace_payment_gateway.php    # Main plugin file
├── MNS_Bootstrapper.php             # Plugin bootstrapper
├── includes/                        # Core functionality
│   ├── assets/js/                   # Frontend JavaScript/React
│   │   ├── components/              # Reusable React components
│   │   ├── payment-method/          # WooCommerce Blocks integration
│   │   └── debug/                   # Debug utilities
│   ├── css/                         # Stylesheets
│   ├── blocks/                      # WooCommerce Blocks backend
│   └── *.php                       # Helper classes and utilities
├── payment-gateway/                 # Payment method classes
├── router/                          # URL routing and endpoints
├── templates/                       # Frontend templates
└── view/                           # Legacy view files
```

### Namespace Convention
- **Root Namespace**: `MoneySpace\`
- **Payment Methods**: `MoneySpace\Payments\`
- **Utilities**: `MoneySpace\Utilities\`
- **Blocks**: `MoneySpace\Blocks\`

## Coding Standards and Best Practices

### PHP Development Guidelines

#### WordPress Standards
- Follow WordPress Coding Standards (WPCS)
- Use WordPress hooks and filters appropriately
- Implement proper sanitization and validation
- Use WordPress APIs for database operations, HTTP requests, and file handling

#### Modern PHP Practices
```php
// Use modern PHP syntax (8.0+)
class PaymentGateway {
    public function __construct(
        private string $secretId,
        private string $secretKey
    ) {}
    
    public function processPayment(array $data): array {
        return match($data['type']) {
            'credit_card' => $this->processCreditCard($data),
            'qr_code' => $this->processQRCode($data),
            'installment' => $this->processInstallment($data),
            default => throw new InvalidArgumentException('Invalid payment type')
        };
    }
}
```

#### Error Handling
```php
// Always use proper error handling
public function createTransaction(): array {
    try {
        $response = wp_remote_post($this->apiUrl, $args);
        
        if (is_wp_error($response)) {
            throw new Exception('HTTP request failed: ' . $response->get_error_message());
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON response');
        }
        
        return $data;
    } catch (Exception $e) {
        error_log('MoneySpace API Error: ' . $e->getMessage());
        return ['error' => $e->getMessage()];
    }
}
```

#### Database Operations
```php
// Use WordPress database API
global $wpdb;
$results = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value = %s",
    'moneyspace_transaction_id',
    $transaction_id
));
```

### JavaScript/React Development Guidelines

#### WooCommerce Blocks Integration
```javascript
// Use proper WooCommerce Blocks APIs
import { registerPaymentMethod } from '@woocommerce/blocks-registry';
import { getSetting } from '@woocommerce/settings';
import { useSelect } from '@wordpress/data';

const settings = getSetting('moneyspace_creditcard_data', {});

// Memoize components for performance
const CreditCardComponent = useMemo(() => {
    return <CreditCardForm {...props} />;
}, [props]);
```

#### React Component Structure
```javascript
// Use proper React patterns with error boundaries
import React, { useState, useEffect, useCallback, useMemo } from '@wordpress/element';

const CreditCardInstallmentForm = (props) => {
    // Destructure props safely
    const { 
        ccIns, 
        msfee, 
        i18n, 
        billing, 
        eventRegistration = {} 
    } = props || {};
    
    // Use proper state management
    const [paymentData, setPaymentData] = useState({
        selectbank: "",
        KTC_permonths: "",
        BAY_permonths: "",
        FCY_permonths: "",
        dirty: false
    });
    
    // Memoize expensive calculations
    const amount_total = useMemo(() => {
        if (!billing?.cartTotal?.value || !billing?.currency?.minorUnit) {
            return 0;
        }
        return billing.cartTotal.value / Math.pow(10, billing.currency.minorUnit);
    }, [billing]);
    
    // Use proper error handling
    const handleChange = useCallback((field) => (event) => {
        try {
            const value = event.target.value;
            setPaymentData(prev => ({
                ...prev,
                [field]: value,
                dirty: true
            }));
        } catch (error) {
            console.error('Error in handleChange:', error);
        }
    }, []);
    
    return (
        <div className="wc-block-components-credit-card-installment-form">
            {/* Component JSX */}
        </div>
    );
};

// Always wrap with error boundary
export default CreditCardInstallmentForm;
```

#### Build Configuration
```javascript
// Use @wordpress/scripts for modern build pipeline
// webpack.config.js should extend default configuration
const defaultConfig = require('@wordpress/scripts/config/webpack.config');

module.exports = {
    ...defaultConfig,
    entry: {
        'frontend/blocks-ms-creditcard': './includes/assets/js/payment-method/creditcard.js',
        'frontend/blocks-ms-creditcard-installment': './includes/assets/js/payment-method/creditcard-installment.js',
        'frontend/blocks-ms-qr': './includes/assets/js/payment-method/qrcode.js'
    }
};
```

### CSS/SCSS Guidelines
```scss
// Use BEM methodology for CSS classes
.wc-block-components-credit-card-installment-form {
    &__header {
        font-weight: 600;
        margin-bottom: 1rem;
    }
    
    &__option {
        border: 1px solid #ddd;
        border-radius: 4px;
        
        &--selected {
            border-color: #007cba;
            background-color: #f0f8ff;
        }
    }
}

// Use CSS custom properties for theming
:root {
    --moneyspace-primary-color: #007cba;
    --moneyspace-border-radius: 4px;
    --moneyspace-spacing-unit: 1rem;
}
```

## WooCommerce Integration Best Practices

### Payment Gateway Class Structure
```php
class MoneySpace_Payment_Gateway extends WC_Payment_Gateway {
    
    public function __construct() {
        $this->id = 'moneyspace_creditcard';
        $this->method_title = 'MoneySpace Credit Card';
        $this->method_description = 'Accept credit card payments via MoneySpace';
        $this->supports = [
            'products',
            'subscriptions',
            'subscription_cancellation',
            'subscription_suspension',
            'subscription_reactivation'
        ];
        
        $this->init_form_fields();
        $this->init_settings();
        
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, [$this, 'process_admin_options']);
    }
    
    public function process_payment($order_id): array {
        $order = wc_get_order($order_id);
        
        try {
            $result = $this->create_payment_transaction_v3($order);
            
            if (isset($result['error'])) {
                wc_add_notice($result['error'], 'error');
                return ['result' => 'failure'];
            }
            
            return [
                'result' => 'success',
                'redirect' => $result['payment_url']
            ];
            
        } catch (Exception $e) {
            wc_add_notice('Payment error: ' . $e->getMessage(), 'error');
            return ['result' => 'failure'];
        }
    }
}
```

### Blocks Integration
```php
// Register payment method for blocks
add_action('woocommerce_blocks_payment_method_type_registration', function($payment_method_registry) {
    $payment_method_registry->register(new MoneySpace_CreditCard_Blocks());
});

class MoneySpace_CreditCard_Blocks extends Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType {
    
    protected $name = 'moneyspace_creditcard';
    
    public function initialize() {
        $this->settings = get_option('woocommerce_moneyspace_creditcard_settings', []);
    }
    
    public function get_payment_method_script_handles(): array {
        wp_register_script(
            'moneyspace-creditcard-blocks',
            plugins_url('assets/js/frontend/blocks-ms-creditcard.js', __FILE__),
            ['wp-element', 'wp-i18n', 'wc-blocks-registry']
        );
        
        return ['moneyspace-creditcard-blocks'];
    }
}
```

## Security and Performance

### Security Best Practices
```php
// Always sanitize and validate input
$secret_id = sanitize_text_field($_POST['secret_id']);
$amount = floatval($_POST['amount']);

// Use nonces for forms
wp_nonce_field('moneyspace_payment_action', 'moneyspace_nonce');

// Verify nonces
if (!wp_verify_nonce($_POST['moneyspace_nonce'], 'moneyspace_payment_action')) {
    wp_die('Security check failed');
}

// Escape output
echo esc_html($transaction_id);
echo wp_kses_post($payment_description);
```

### Performance Optimization
```php
// Cache API responses
$cache_key = 'moneyspace_rates_' . md5($currency);
$rates = wp_cache_get($cache_key);

if (false === $rates) {
    $rates = $this->fetch_exchange_rates($currency);
    wp_cache_set($cache_key, $rates, '', HOUR_IN_SECONDS);
}

// Use transients for longer caching
set_transient('moneyspace_bank_config', $config, DAY_IN_SECONDS);
```

## Testing and Quality Assurance

### Unit Testing
```php
// Use PHPUnit for backend testing
class MoneySpace_Payment_Test extends WP_UnitTestCase {
    
    public function test_payment_creation() {
        $gateway = new MoneySpace_Payment_Gateway();
        $order = $this->factory->post->create(['post_type' => 'shop_order']);
        
        $result = $gateway->process_payment($order);
        
        $this->assertArrayHasKey('result', $result);
        $this->assertEquals('success', $result['result']);
    }
}
```

### Integration Testing
```javascript
// Use Jest for JavaScript testing
describe('CreditCardInstallmentForm', () => {
    test('renders installment options correctly', () => {
        const props = {
            ccIns: mockInstallmentData,
            billing: mockBillingData,
            i18n: mockTranslations
        };
        
        const wrapper = mount(<CreditCardInstallmentForm {...props} />);
        
        expect(wrapper.find('.installment-option')).toHaveLength(3);
    });
});
```

## Debugging and Logging

### Debug Configuration
```php
// Enable debug mode
if (defined('WP_DEBUG') && WP_DEBUG) {
    define('MONEYSPACE_DEBUG', true);
}

// Custom logging
function moneyspace_log($message, $level = 'info') {
    if (defined('MONEYSPACE_DEBUG') && MONEYSPACE_DEBUG) {
        error_log(sprintf('[MoneySpace][%s] %s', strtoupper($level), $message));
    }
}
```

### JavaScript Debugging
```javascript
// Debug helper utility
export const debugLog = (message, data = {}) => {
    if (window.location.search.includes('debug=1')) {
        console.group(`[MoneySpace Debug] ${message}`);
        console.log('Data:', data);
        console.log('Timestamp:', new Date().toISOString());
        console.groupEnd();
    }
};
```

## Deployment and Maintenance

### Version Management
```php
// Update version in main plugin file
define('MONEYSPACE_VERSION', '2.13.3');

// Database schema updates
function moneyspace_update_db() {
    $current_version = get_option('moneyspace_db_version', '0');
    
    if (version_compare($current_version, MONEYSPACE_VERSION, '<')) {
        // Run database updates
        moneyspace_create_tables();
        update_option('moneyspace_db_version', MONEYSPACE_VERSION);
    }
}
```

### Automated Updates
```php
// Implement update checker
class MoneySpace_Updater {
    
    public function check_for_updates() {
        $remote_version = $this->get_remote_version();
        $current_version = MONEYSPACE_VERSION;
        
        if (version_compare($current_version, $remote_version, '<')) {
            add_action('admin_notices', [$this, 'update_notice']);
        }
    }
}
```

## Documentation Requirements

### Code Documentation
```php
/**
 * Process payment transaction for MoneySpace gateway
 *
 * @param WC_Order $order The WooCommerce order object
 * @param array    $payment_data Payment form data
 * @return array   Payment processing result
 * @throws Exception When API communication fails
 * @since 2.13.0
 */
public function process_payment_transaction(WC_Order $order, array $payment_data): array {
    // Implementation
}
```

### README Updates
- Keep installation instructions current
- Document all configuration options
- Provide troubleshooting guide
- Include changelog for all releases

## Support and Maintenance

### Error Handling Protocol
1. **Log all errors** with sufficient context
2. **Graceful degradation** when possible
3. **User-friendly error messages** in frontend
4. **Admin notifications** for critical errors

### Monitoring
```php
// Health check endpoint
add_action('wp_ajax_nopriv_moneyspace_health', function() {
    $status = [
        'plugin_version' => MONEYSPACE_VERSION,
        'wp_version' => get_bloginfo('version'),
        'wc_version' => WC()->version,
        'ssl_enabled' => is_ssl(),
        'api_reachable' => $this->test_api_connection()
    ];
    
    wp_send_json($status);
});
```

This comprehensive guide ensures consistent, maintainable, and secure code throughout the MoneySpace WooCommerce plugin development lifecycle.
