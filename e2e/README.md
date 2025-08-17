# MoneySpace E2E Test Configuration

## Environment Variables

Create a `.env` file in the e2e directory with these variables:

```bash
# WordPress Site Configuration
WP_BASE_URL=http://localhost:8000
WP_ADMIN_USER=admin
WP_ADMIN_PASSWORD=password

# WooCommerce Configuration
WC_SHOP_URL=/shop
WC_CHECKOUT_URL=/checkout
WC_CART_URL=/cart

# MoneySpace Plugin Configuration
MS_SECRET_ID=your_secret_id
MS_SECRET_KEY=your_secret_key
MS_SANDBOX_MODE=true

# Test User Configuration  
TEST_CUSTOMER_EMAIL=customer@test.com
TEST_CUSTOMER_PASSWORD=testpassword
TEST_CUSTOMER_FIRST_NAME=John
TEST_CUSTOMER_LAST_NAME=Doe

# Test Product Configuration
TEST_PRODUCT_ID=123
TEST_PRODUCT_PRICE=5000  # For installment testing (>3000 THB)
TEST_LOW_PRICE_PRODUCT_ID=124
TEST_LOW_PRICE=1000      # For testing minimum amount validation

# Payment Method Test Data
TEST_CARD_NUMBER=4111111111111111
TEST_CARD_EXPIRY=12/25
TEST_CARD_CVV=123
TEST_CARD_HOLDER=John Doe

# Test Configuration
HEADLESS=true
SLOW_MO=100
TIMEOUT=30000
```

## Docker Compose for Local Testing

Create a `docker-compose.test.yml` for isolated testing:

```yaml
version: '3.8'
services:
  wordpress-test:
    image: wordpress:latest
    ports:
      - "8000:80"
    environment:
      WORDPRESS_DB_HOST: db-test
      WORDPRESS_DB_USER: wp_test
      WORDPRESS_DB_PASSWORD: wp_test_pass
      WORDPRESS_DB_NAME: wp_test
    volumes:
      - ../:/var/www/html/wp-content/plugins/money-space-for-woocommerce
      - ./test-data:/var/www/html/wp-content/uploads/test-data
    depends_on:
      - db-test

  db-test:
    image: mysql:8.0
    environment:
      MYSQL_DATABASE: wp_test
      MYSQL_USER: wp_test
      MYSQL_PASSWORD: wp_test_pass
      MYSQL_ROOT_PASSWORD: root_pass
    volumes:
      - test_db_data:/var/lib/mysql

volumes:
  test_db_data:
```

## Running Tests

```bash
# Install dependencies
npm install

# Install Playwright browsers
npm run test:install

# Run all tests
npm test

# Run tests with UI
npm run test:ui

# Run specific test suites
npm run test:installment
npm run test:creditcard
npm run test:qrcode

# Run tests in headed mode (see browser)
npm run test:headed

# Debug tests
npm run test:debug

# View test reports
npm run test:report
```

## Test Structure

```
e2e/
├── tests/
│   ├── setup/
│   │   ├── global-setup.ts         # WordPress/WooCommerce setup
│   │   └── test-data-setup.ts      # Create test products, users
│   ├── payment-methods/
│   │   ├── installment.spec.ts     # Installment payment tests
│   │   ├── creditcard.spec.ts      # Credit card payment tests
│   │   └── qrcode.spec.ts          # QR code payment tests
│   ├── validation/
│   │   ├── form-validation.spec.ts # Form validation tests
│   │   └── amount-validation.spec.ts # Amount validation tests
│   ├── notices/
│   │   └── notice-clearing.spec.ts # Notice clearing tests
│   └── admin/
│       └── plugin-settings.spec.ts # Admin configuration tests
├── fixtures/
│   ├── auth.ts                     # Authentication helpers
│   ├── cart.ts                     # Cart management helpers
│   └── payment.ts                  # Payment flow helpers
├── utils/
│   ├── test-data.ts               # Test data generators
│   └── helpers.ts                 # Common helper functions
└── page-objects/
    ├── checkout-page.ts           # Checkout page object
    ├── admin-settings-page.ts    # Admin settings page object
    └── payment-forms.ts           # Payment form components
```

## CI/CD Integration

Add to `.github/workflows/e2e-tests.yml`:

```yaml
name: E2E Tests

on:
  push:
    branches: [main, develop]
  pull_request:
    branches: [main]

jobs:
  e2e-tests:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      
      - name: Setup Node.js
        uses: actions/setup-node@v3
        with:
          node-version: '18'
          
      - name: Install dependencies
        run: |
          cd e2e
          npm ci
          
      - name: Install Playwright
        run: |
          cd e2e
          npx playwright install --with-deps
          
      - name: Start WordPress test environment
        run: |
          cd e2e
          docker-compose -f docker-compose.test.yml up -d
          sleep 30
          
      - name: Run E2E tests
        run: |
          cd e2e
          npm test
          
      - name: Upload test results
        uses: actions/upload-artifact@v3
        if: always()
        with:
          name: playwright-report
          path: e2e/playwright-report/
```
