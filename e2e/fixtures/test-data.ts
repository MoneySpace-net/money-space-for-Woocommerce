// Test fixtures for consistent test data across all E2E tests

export const testProducts = {
  // High value product for installment testing
  highValue: {
    id: 'test-product-high-value',
    name: 'High Value Test Product',
    price: 25000,
    sku: 'TEST-HIGH-001',
    description: 'Test product with high value for installment payments',
    categories: ['test-products'],
    attributes: {
      'test-product': 'true',
      'installment-eligible': 'true'
    }
  },

  // Medium value product for general testing
  mediumValue: {
    id: 'test-product-medium-value',
    name: 'Medium Value Test Product',
    price: 5000,
    sku: 'TEST-MED-001',
    description: 'Test product with medium value for general payments',
    categories: ['test-products'],
    attributes: {
      'test-product': 'true'
    }
  },

  // Low value product for minimum amount testing
  lowValue: {
    id: 'test-product-low-value',
    name: 'Low Value Test Product',
    price: 500,
    sku: 'TEST-LOW-001',
    description: 'Test product with low value for minimum amount validation',
    categories: ['test-products'],
    attributes: {
      'test-product': 'true',
      'below-minimum': 'true'
    }
  },

  // Variable product for complex scenarios
  variable: {
    id: 'test-product-variable',
    name: 'Variable Test Product',
    type: 'variable',
    sku: 'TEST-VAR-001',
    description: 'Variable test product with multiple options',
    categories: ['test-products'],
    variations: [
      {
        id: 'test-var-001',
        price: 3000,
        attributes: { size: 'small', color: 'red' }
      },
      {
        id: 'test-var-002',
        price: 6000,
        attributes: { size: 'large', color: 'blue' }
      }
    ]
  }
};

export const testUsers = {
  // Thai customer with complete details
  thaiCustomer: {
    firstName: 'สมชาย',
    lastName: 'ใจดี',
    email: 'somchai.jaidee@test.com',
    phone: '081-234-5678',
    address: '123/45 ถนนสุขุมวิท',
    city: 'กรุงเทพมหานคร',
    state: 'กรุงเทพมหานคร',
    postcode: '10110',
    country: 'TH'
  },

  // International customer
  internationalCustomer: {
    firstName: 'John',
    lastName: 'Doe',
    email: 'john.doe@test.com',
    phone: '+1-555-123-4567',
    address: '123 Main Street',
    city: 'New York',
    state: 'NY',
    postcode: '10001',
    country: 'US'
  },

  // Customer with minimal details
  minimalCustomer: {
    firstName: 'Test',
    lastName: 'User',
    email: 'test.user@test.com',
    phone: '0812345678',
    address: '123 Test St',
    city: 'Bangkok',
    postcode: '10100',
    country: 'TH'
  },

  // Customer with special characters
  specialCharCustomer: {
    firstName: 'Test\'s',
    lastName: 'User-Name',
    email: 'test+user@test-domain.co.th',
    phone: '081-234-5678',
    address: '123/4-5 Test Road, Building A',
    city: 'Bangkok',
    postcode: '10100',
    country: 'TH'
  }
};

export const testCreditCards = {
  visa: {
    number: '4111111111111111',
    expiry: '12/25',
    cvv: '123',
    holder: 'John Doe Test',
    type: 'visa'
  },

  visaDebit: {
    number: '4000000000000002',
    expiry: '12/25',
    cvv: '123',
    holder: 'John Doe Test',
    type: 'visa-debit'
  },

  mastercard: {
    number: '5555555555554444',
    expiry: '12/25',
    cvv: '123',
    holder: 'John Doe Test',
    type: 'mastercard'
  },

  amex: {
    number: '378282246310005',
    expiry: '12/25',
    cvv: '1234',
    holder: 'John Doe Test',
    type: 'amex'
  },

  // Test cards for specific scenarios
  insufficientFunds: {
    number: '4000000000000002',
    expiry: '12/25',
    cvv: '123',
    holder: 'Insufficient Funds',
    type: 'visa-insufficient'
  },

  expiredCard: {
    number: '4111111111111111',
    expiry: '01/20',
    cvv: '123',
    holder: 'Expired Card',
    type: 'visa-expired'
  },

  invalidCard: {
    number: '4000000000000127',
    expiry: '12/25',
    cvv: '123',
    holder: 'Invalid Card',
    type: 'visa-invalid'
  },

  // Cards for testing validation
  shortNumber: {
    number: '4111',
    expiry: '12/25',
    cvv: '123',
    holder: 'Short Number',
    type: 'invalid-short'
  },

  invalidCvv: {
    number: '4111111111111111',
    expiry: '12/25',
    cvv: '12',
    holder: 'Invalid CVV',
    type: 'invalid-cvv'
  }
};

export const testInstallmentConfig = {
  ktc: {
    bank: 'KTC',
    availableMonths: ['3', '6', '9', '12', '18', '24'],
    minimumMonthlyAmount: 300,
    minimumTotalAmount: 3000,
    maximumTotalAmount: 500000,
    interestRates: {
      '3': 0,
      '6': 0,
      '9': 0.65,
      '12': 0.65,
      '18': 0.65,
      '24': 0.65
    }
  },

  bay: {
    bank: 'BAY',
    availableMonths: ['3', '4', '6', '9', '10', '12', '18', '20', '24', '36'],
    minimumMonthlyAmount: 500,
    minimumTotalAmount: 3000,
    maximumTotalAmount: 500000,
    interestRates: {
      '3': 0,
      '4': 0,
      '6': 0,
      '9': 0.65,
      '10': 0.65,
      '12': 0.65,
      '18': 0.65,
      '20': 0.65,
      '24': 0.65,
      '36': 0.65
    }
  },

  fcy: {
    bank: 'FCY',
    availableMonths: ['3', '6', '9', '12', '18', '24'],
    minimumMonthlyAmount: 300,
    minimumTotalAmount: 3000,
    maximumTotalAmount: 500000,
    interestRates: {
      '3': 0,
      '6': 0,
      '9': 0.74,
      '12': 0.74,
      '18': 0.74,
      '24': 0.74
    }
  }
};

export const testAPIResponses = {
  // Successful payment responses
  creditCardSuccess: {
    status: 'success',
    payment_url: 'https://payment.moneyspace.net/redirect/test123',
    transaction_id: 'TXN_CC_123456789',
    order_id: 'ORDER_123',
    amount: 5000,
    currency: 'THB'
  },

  installmentSuccess: {
    status: 'success',
    payment_url: 'https://payment.moneyspace.net/installment/test123',
    transaction_id: 'TXN_INS_123456789',
    order_id: 'ORDER_123',
    amount: 15000,
    currency: 'THB',
    installment_details: {
      bank: 'KTC',
      months: 6,
      monthly_amount: 2500,
      total_amount: 15000,
      interest_rate: 0
    }
  },

  qrCodeSuccess: {
    status: 'success',
    qr_code_url: 'https://payment.moneyspace.net/qr/test123.png',
    qr_data: 'promptpay://0066123456789/5000.00',
    transaction_id: 'TXN_QR_123456789',
    order_id: 'ORDER_123',
    amount: 5000,
    currency: 'THB',
    expires_at: new Date(Date.now() + 15 * 60 * 1000).toISOString()
  },

  // Error responses
  validationError: {
    status: 'error',
    error_code: 'VALIDATION_ERROR',
    message: 'Validation failed',
    details: [
      'Card number is required',
      'Expiry date is invalid',
      'CVV is required'
    ]
  },

  insufficientFunds: {
    status: 'error',
    error_code: 'INSUFFICIENT_FUNDS',
    message: 'Insufficient funds available',
    transaction_id: 'TXN_FAIL_123456789'
  },

  invalidCard: {
    status: 'error',
    error_code: 'INVALID_CARD',
    message: 'Card number is invalid or expired',
    transaction_id: 'TXN_FAIL_123456789'
  },

  serverError: {
    status: 'error',
    error_code: 'INTERNAL_ERROR',
    message: 'Internal server error. Please try again later.'
  },

  networkTimeout: {
    status: 'error',
    error_code: 'TIMEOUT',
    message: 'Request timeout. Please check your connection and try again.'
  }
};

export const testWebhookPayloads = {
  paymentCompleted: {
    event: 'payment.completed',
    transaction_id: 'TXN_123456789',
    order_id: 'ORDER_123',
    status: 'completed',
    amount: 5000,
    currency: 'THB',
    payment_method: 'credit_card',
    timestamp: new Date().toISOString(),
    signature: 'webhook_signature_hash'
  },

  paymentFailed: {
    event: 'payment.failed',
    transaction_id: 'TXN_123456789',
    order_id: 'ORDER_123',
    status: 'failed',
    amount: 5000,
    currency: 'THB',
    payment_method: 'credit_card',
    error_code: 'PAYMENT_DECLINED',
    error_message: 'Payment was declined by issuer',
    timestamp: new Date().toISOString(),
    signature: 'webhook_signature_hash'
  },

  paymentCancelled: {
    event: 'payment.cancelled',
    transaction_id: 'TXN_123456789',
    order_id: 'ORDER_123',
    status: 'cancelled',
    amount: 5000,
    currency: 'THB',
    payment_method: 'qr_code',
    timestamp: new Date().toISOString(),
    signature: 'webhook_signature_hash'
  },

  refundProcessed: {
    event: 'refund.processed',
    transaction_id: 'TXN_123456789',
    refund_id: 'REF_123456789',
    order_id: 'ORDER_123',
    status: 'refunded',
    refund_amount: 2500,
    currency: 'THB',
    timestamp: new Date().toISOString(),
    signature: 'webhook_signature_hash'
  }
};

export const testAdminSettings = {
  creditCard: {
    enabled: 'yes',
    title: 'MoneySpace Credit Card',
    description: 'Pay securely with your credit card',
    secret_id: 'test_secret_id_123',
    secret_key: 'test_secret_key_456',
    sandbox: 'yes',
    debug: 'yes',
    webhook_url: 'https://test-site.com/wp-json/moneyspace/webhook'
  },

  installment: {
    enabled: 'yes',
    title: 'MoneySpace Installment',
    description: 'Pay in installments with 0% interest',
    secret_id: 'test_secret_id_123',
    secret_key: 'test_secret_key_456',
    sandbox: 'yes',
    debug: 'yes',
    min_amount: '3000',
    max_amount: '500000'
  },

  qrCode: {
    enabled: 'yes',
    title: 'MoneySpace QR Code',
    description: 'Pay with QR code via mobile banking',
    secret_id: 'test_secret_id_123',
    secret_key: 'test_secret_key_456',
    sandbox: 'yes',
    debug: 'yes',
    timeout: '900'
  }
};

export const testErrorMessages = {
  validation: {
    required_field: 'This field is required',
    invalid_email: 'Please enter a valid email address',
    invalid_phone: 'Please enter a valid phone number',
    invalid_card_number: 'Please enter a valid card number',
    invalid_expiry: 'Please enter a valid expiry date',
    invalid_cvv: 'Please enter a valid CVV',
    expired_card: 'This card has expired',
    minimum_amount: 'Minimum amount required is 3,000 THB',
    select_bank: 'Please select a bank',
    select_months: 'Please select number of months'
  },

  payment: {
    insufficient_funds: 'Insufficient funds available',
    card_declined: 'Your card was declined',
    expired_session: 'Your session has expired. Please try again',
    network_error: 'Network error. Please check your connection',
    processing_error: 'Error processing payment. Please try again',
    invalid_amount: 'Invalid payment amount',
    qr_expired: 'QR code has expired. Please generate a new one'
  },

  system: {
    maintenance: 'System is under maintenance. Please try again later',
    api_unavailable: 'Payment service is temporarily unavailable',
    configuration_error: 'Payment method is not properly configured',
    security_error: 'Security validation failed'
  }
};

export const testSelectors = {
  checkout: {
    billingFirstName: '#billing_first_name',
    billingLastName: '#billing_last_name',
    billingEmail: '#billing_email',
    billingPhone: '#billing_phone',
    billingAddress: '#billing_address_1',
    billingCity: '#billing_city',
    billingPostcode: '#billing_postcode',
    placeOrderButton: '#place_order, .wc-block-components-checkout-place-order-button'
  },

  paymentMethods: {
    creditCard: 'input[value="moneyspace_creditcard"]',
    installment: 'input[value="moneyspace_installment"]',
    qrCode: 'input[value="moneyspace_qrcode"]'
  },

  creditCardForm: {
    number: '#card_number, [name="card_number"]',
    expiry: '#card_expiry, [name="card_expiry"]',
    cvv: '#card_cvv, [name="card_cvv"]',
    holder: '#card_holder, [name="card_holder"]'
  },

  installmentForm: {
    bankSelect: '#selectbank',
    ktcMonths: '#ktc_permonths',
    bayMonths: '#bay_permonths',
    fcyMonths: '#fcy_permonths'
  },

  notifications: {
    error: '.woocommerce-error, .wc-block-components-notice-banner--error',
    success: '.woocommerce-message, .wc-block-components-notice-banner--success',
    info: '.woocommerce-info, .wc-block-components-notice-banner--info'
  }
};

export const testEnvironmentConfig = {
  baseURL: process.env.TEST_BASE_URL || 'http://localhost:8080',
  adminUser: process.env.WP_ADMIN_USER || 'admin',
  adminPassword: process.env.WP_ADMIN_PASS || 'password',
  testTimeout: parseInt(process.env.TEST_TIMEOUT || '30000'),
  slowMo: parseInt(process.env.SLOW_MO || '0'),
  headless: process.env.HEADLESS !== 'false',
  screenshotsDir: 'e2e/screenshots',
  videosDir: 'e2e/videos'
};
