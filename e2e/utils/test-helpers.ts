// Test utilities and helper functions for MoneySpace E2E tests

export class TestDataGenerator {
  /**
   * Generate test billing details with Thai-specific data
   */
  static generateBillingDetails(overrides: Partial<BillingDetails> = {}): BillingDetails {
    const defaults = {
      firstName: 'สมชาย',
      lastName: 'ใจดี',
      email: `test.${Date.now()}@example.com`,
      phone: '081-234-5678',
      address: '123 ถนนสุขุมวิท',
      city: 'กรุงเทพมหานคร',
      postcode: '10110',
      country: 'TH'
    };
    
    return { ...defaults, ...overrides };
  }

  /**
   * Generate test credit card details
   */
  static generateCreditCardDetails(cardType: 'visa' | 'mastercard' | 'amex' = 'visa'): CreditCardDetails {
    const cardNumbers = {
      visa: '4111111111111111',
      mastercard: '5555555555554444',
      amex: '378282246310005'
    };

    return {
      number: cardNumbers[cardType],
      expiry: '12/25',
      cvv: cardType === 'amex' ? '1234' : '123',
      holder: 'John Doe Test'
    };
  }

  /**
   * Generate test product data
   */
  static generateProductData(type: 'simple' | 'variable' = 'simple', price: number = 5000) {
    return {
      name: `Test Product ${Date.now()}`,
      type,
      price,
      sku: `TEST-${Date.now()}`,
      description: 'Test product for E2E testing'
    };
  }
}

export class APITestHelper {
  /**
   * Mock MoneySpace API responses
   */
  static async mockSuccessfulPayment(page: any, paymentType: 'creditcard' | 'installment' | 'qrcode') {
    await page.route('**/moneyspace/**', (route: any) => {
      const mockResponses = {
        creditcard: {
          status: 'success',
          payment_url: 'https://payment.moneyspace.net/redirect',
          transaction_id: `TXN_${Date.now()}`
        },
        installment: {
          status: 'success',
          payment_url: 'https://payment.moneyspace.net/installment',
          transaction_id: `INS_${Date.now()}`,
          monthly_amount: 500,
          total_amount: 3000
        },
        qrcode: {
          status: 'success',
          qr_code_url: 'https://payment.moneyspace.net/qr/test123.png',
          qr_data: 'promptpay://test123',
          transaction_id: `QR_${Date.now()}`,
          expires_at: new Date(Date.now() + 15 * 60 * 1000).toISOString()
        }
      };

      route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify(mockResponses[paymentType])
      });
    });
  }

  /**
   * Mock API failures
   */
  static async mockAPIFailure(page: any, errorType: 'network' | 'validation' | 'server') {
    await page.route('**/moneyspace/**', (route: any) => {
      const mockErrors = {
        network: () => route.abort('failed'),
        validation: () => route.fulfill({
          status: 400,
          contentType: 'application/json',
          body: JSON.stringify({
            error: 'Validation failed',
            details: ['Invalid card number', 'Missing required field']
          })
        }),
        server: () => route.fulfill({
          status: 500,
          contentType: 'application/json',
          body: JSON.stringify({
            error: 'Internal server error',
            message: 'Service temporarily unavailable'
          })
        })
      };

      mockErrors[errorType]();
    });
  }

  /**
   * Mock webhook responses
   */
  static async mockWebhookSuccess(page: any, transactionId: string) {
    await page.route('**/webhook/**', (route: any) => {
      route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify({
          status: 'completed',
          transaction_id: transactionId,
          payment_status: 'paid',
          timestamp: new Date().toISOString()
        })
      });
    });
  }
}

export class DatabaseTestHelper {
  /**
   * Clean up test data after tests
   */
  static async cleanupTestOrders(page: any) {
    await page.evaluate(() => {
      // This would typically call a WP REST API endpoint to clean up test data
      fetch('/wp-json/wc/v3/orders', {
        method: 'DELETE',
        headers: {
          'X-Test-Cleanup': 'true'
        }
      });
    });
  }

  /**
   * Create test products via API
   */
  static async createTestProduct(page: any, productData: any) {
    return await page.evaluate((data) => {
      return fetch('/wp-json/wc/v3/products', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Test-Data': 'true'
        },
        body: JSON.stringify(data)
      }).then(response => response.json());
    }, productData);
  }
}

export class WaitHelper {
  /**
   * Wait for payment processing to complete
   */
  static async waitForPaymentRedirect(page: any, timeout: number = 30000) {
    await page.waitForURL(/payment|order-received|success|moneyspace/, { timeout });
  }

  /**
   * Wait for form validation to complete
   */
  static async waitForValidation(page: any, timeout: number = 3000) {
    await page.waitForFunction(() => {
      const loadingElements = document.querySelectorAll('.loading, .processing, .validating');
      return loadingElements.length === 0;
    }, { timeout });
  }

  /**
   * Wait for AJAX requests to complete
   */
  static async waitForAjaxComplete(page: any) {
    await page.waitForFunction(() => {
      return window.jQuery && jQuery.active === 0;
    });
  }

  /**
   * Wait for React components to render
   */
  static async waitForReactRender(page: any, selector: string) {
    await page.waitForFunction((sel) => {
      const element = document.querySelector(sel);
      return element && element.children.length > 0;
    }, selector);
  }
}

export class AssertionHelper {
  /**
   * Assert payment form is in valid state
   */
  static async assertPaymentFormValid(page: any, paymentMethod: string) {
    const formSelector = `.wc-block-components-${paymentMethod}-form, .payment_method_moneyspace_${paymentMethod}`;
    const form = page.locator(formSelector);
    
    await expect(form).toBeVisible();
    
    // Check for any validation errors
    const errors = page.locator('.woocommerce-error, .wc-block-components-validation-error');
    await expect(errors).toHaveCount(0);
  }

  /**
   * Assert order was created successfully
   */
  static async assertOrderCreated(page: any, orderData: any) {
    // Check order confirmation page
    await expect(page.locator('.woocommerce-order-received')).toBeVisible();
    
    // Check order details
    const orderNumber = await page.locator('.woocommerce-order-overview__order strong').textContent();
    expect(orderNumber).toBeTruthy();
    
    // Check billing details match
    const billingDetails = await page.locator('.woocommerce-customer-details').textContent();
    expect(billingDetails).toContain(orderData.billing.firstName);
    expect(billingDetails).toContain(orderData.billing.email);
  }

  /**
   * Assert payment method configuration is correct
   */
  static async assertPaymentMethodConfig(page: any, methodId: string, expectedConfig: any) {
    await page.goto(`/wp-admin/admin.php?page=wc-settings&tab=checkout&section=${methodId}`);
    
    for (const [field, expectedValue] of Object.entries(expectedConfig)) {
      const fieldElement = page.locator(`#woocommerce_${methodId}_${field}`);
      
      if (typeof expectedValue === 'boolean') {
        const isChecked = await fieldElement.isChecked();
        expect(isChecked).toBe(expectedValue);
      } else {
        const value = await fieldElement.inputValue();
        expect(value).toBe(expectedValue);
      }
    }
  }
}

export class ScreenshotHelper {
  /**
   * Take screenshot with timestamp
   */
  static async takeTimestampedScreenshot(page: any, name: string) {
    const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
    await page.screenshot({ 
      path: `e2e/screenshots/${name}-${timestamp}.png`,
      fullPage: true 
    });
  }

  /**
   * Take screenshot on test failure
   */
  static async takeFailureScreenshot(page: any, testInfo: any) {
    if (testInfo.status !== testInfo.expectedStatus) {
      const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
      await page.screenshot({ 
        path: `e2e/screenshots/failure-${testInfo.title}-${timestamp}.png`,
        fullPage: true 
      });
    }
  }
}

export class SecurityHelper {
  /**
   * Check for XSS vulnerabilities in form inputs
   */
  static async testXSSPrevention(page: any, inputSelector: string) {
    const xssPayload = '<script>window.xssTest = true;</script>';
    
    await page.fill(inputSelector, xssPayload);
    await page.keyboard.press('Enter');
    
    // Check that script was not executed
    const xssExecuted = await page.evaluate(() => window.xssTest);
    expect(xssExecuted).toBeUndefined();
    
    // Check that payload was sanitized in DOM
    const inputValue = await page.inputValue(inputSelector);
    expect(inputValue).not.toContain('<script>');
  }

  /**
   * Check for CSRF protection
   */
  static async testCSRFProtection(page: any, formSelector: string) {
    // Look for nonce fields
    const nonceField = page.locator(`${formSelector} input[name*="nonce"], ${formSelector} input[name*="_wpnonce"]`);
    await expect(nonceField).toBeVisible();
    
    const nonceValue = await nonceField.getAttribute('value');
    expect(nonceValue).toBeTruthy();
    expect(nonceValue?.length).toBeGreaterThan(10);
  }

  /**
   * Test SQL injection prevention
   */
  static async testSQLInjectionPrevention(page: any, inputSelector: string) {
    const sqlPayload = "'; DROP TABLE wp_posts; --";
    
    await page.fill(inputSelector, sqlPayload);
    
    // Should not cause any database errors or breaks
    const errors = page.locator('.error, .sql-error, .database-error');
    await expect(errors).toHaveCount(0);
  }
}

export class PerformanceHelper {
  /**
   * Measure page load time
   */
  static async measurePageLoadTime(page: any): Promise<number> {
    const startTime = Date.now();
    await page.waitForLoadState('networkidle');
    return Date.now() - startTime;
  }

  /**
   * Check for performance issues
   */
  static async checkPerformanceMetrics(page: any) {
    const metrics = await page.evaluate(() => {
      const navigation = performance.getEntriesByType('navigation')[0] as PerformanceNavigationTiming;
      
      return {
        loadTime: navigation.loadEventEnd - navigation.loadEventStart,
        domContentLoaded: navigation.domContentLoadedEventEnd - navigation.domContentLoadedEventStart,
        firstPaint: performance.getEntriesByType('paint')[0]?.startTime || 0,
        resourceCount: performance.getEntriesByType('resource').length
      };
    });
    
    // Assert reasonable performance
    expect(metrics.loadTime).toBeLessThan(5000); // 5 seconds
    expect(metrics.domContentLoaded).toBeLessThan(3000); // 3 seconds
    expect(metrics.resourceCount).toBeLessThan(100); // Reasonable resource count
    
    return metrics;
  }

  /**
   * Monitor JavaScript errors
   */
  static setupErrorMonitoring(page: any) {
    const errors: any[] = [];
    
    page.on('console', (message: any) => {
      if (message.type() === 'error') {
        errors.push(message.text());
      }
    });
    
    page.on('pageerror', (error: any) => {
      errors.push(error.message);
    });
    
    return () => errors;
  }
}

// Type definitions
export interface BillingDetails {
  firstName: string;
  lastName: string;
  email: string;
  phone: string;
  address: string;
  city: string;
  postcode: string;
  country?: string;
}

export interface CreditCardDetails {
  number: string;
  expiry: string;
  cvv: string;
  holder: string;
}

export interface InstallmentDetails {
  bank: 'KTC' | 'BAY' | 'FCY';
  months: string;
}

export interface TestContext {
  page: any;
  orderData?: any;
  paymentData?: any;
  testStartTime: number;
}
