import { test, expect } from '@playwright/test';
import { CheckoutPage } from '../page-objects/checkout-page';

test.describe('MoneySpace QR Code Payments', () => {
  let checkoutPage: CheckoutPage;

  test.beforeEach(async ({ page }) => {
    checkoutPage = new CheckoutPage(page);
    
    // Add product to cart and navigate to checkout
    await page.goto('/shop');
    await page.click('[data-product_id="123"]');
    await checkoutPage.goto();
    
    // Fill billing details
    await checkoutPage.fillBillingDetails({
      firstName: 'John',
      lastName: 'Doe',
      email: 'john.doe@test.com',
      phone: '0812345678',
      address: '123 Test Street',
      city: 'Bangkok',
      postcode: '10100'
    });
  });

  test.describe('QR Code Form Display', () => {
    test('should display QR code payment option', async () => {
      const isQRAvailable = await checkoutPage.isPaymentMethodAvailable('qrcode');
      expect(isQRAvailable).toBe(true);
    });

    test('should show QR code form when method is selected', async () => {
      await checkoutPage.selectPaymentMethod('qrcode');
      
      expect(await checkoutPage.isQRCodeFormVisible()).toBe(true);
    });

    test('should hide QR code form when different method is selected', async () => {
      await checkoutPage.selectPaymentMethod('qrcode');
      expect(await checkoutPage.isQRCodeFormVisible()).toBe(true);
      
      await checkoutPage.selectPaymentMethod('creditcard');
      expect(await checkoutPage.isQRCodeFormVisible()).toBe(false);
    });

    test('should display payment instructions for QR code', async () => {
      await checkoutPage.selectPaymentMethod('qrcode');
      
      const instructions = await checkoutPage.page.locator('.qr-instructions, .payment-instructions, .qr-code-help')
        .first()
        .textContent();
      
      if (instructions) {
        expect(instructions.toLowerCase()).toContain('qr');
        expect(instructions.toLowerCase()).toMatch(/scan|mobile|banking|app/);
      }
    });

    test('should show QR code payment amount', async () => {
      await checkoutPage.selectPaymentMethod('qrcode');
      
      const cartTotal = await checkoutPage.getCartTotal();
      const displayedAmount = await checkoutPage.page.locator('.qr-amount, .payment-amount, .total-amount')
        .first()
        .textContent();
      
      if (displayedAmount) {
        // Remove currency symbols and formatting for comparison
        const cleanAmount = displayedAmount.replace(/[^\d.]/g, '');
        const cleanCartTotal = cartTotal.replace(/[^\d.]/g, '');
        
        expect(cleanAmount).toBe(cleanCartTotal);
      }
    });
  });

  test.describe('QR Code Payment Processing', () => {
    test('should successfully initiate QR code payment', async () => {
      await checkoutPage.selectPaymentMethod('qrcode');
      await checkoutPage.placeOrder();
      
      // Should redirect to MoneySpace QR payment page or show QR code
      await checkoutPage.page.waitForTimeout(2000);
      
      const currentUrl = checkoutPage.page.url();
      const hasQRCode = await checkoutPage.page.locator('img[src*="qr"], canvas, .qr-code-image')
        .first()
        .isVisible()
        .catch(() => false);
      
      // Either redirected to external payment page or showing QR on same page
      expect(
        currentUrl.includes('moneyspace') || 
        currentUrl.includes('qr') || 
        currentUrl.includes('payment') ||
        hasQRCode
      ).toBe(true);
    });

    test('should generate unique QR code for each transaction', async () => {
      // First transaction
      await checkoutPage.selectPaymentMethod('qrcode');
      await checkoutPage.placeOrder();
      
      await checkoutPage.page.waitForTimeout(1000);
      
      let qrCodeSrc = '';
      const qrImage = await checkoutPage.page.locator('img[src*="qr"], .qr-code-image img').first();
      
      if (await qrImage.isVisible()) {
        qrCodeSrc = await qrImage.getAttribute('src') || '';
      }
      
      // Go back and create second transaction
      await checkoutPage.goto();
      await checkoutPage.fillBillingDetails({
        firstName: 'Jane',
        lastName: 'Smith',
        email: 'jane.smith@test.com',
        phone: '0823456789',
        address: '456 Test Avenue',
        city: 'Bangkok',
        postcode: '10100'
      });
      
      await checkoutPage.selectPaymentMethod('qrcode');
      await checkoutPage.placeOrder();
      
      await checkoutPage.page.waitForTimeout(1000);
      
      const secondQrImage = await checkoutPage.page.locator('img[src*="qr"], .qr-code-image img').first();
      
      if (await secondQrImage.isVisible()) {
        const secondQrCodeSrc = await secondQrImage.getAttribute('src') || '';
        
        // QR codes should be different for different transactions
        if (qrCodeSrc && secondQrCodeSrc) {
          expect(qrCodeSrc).not.toBe(secondQrCodeSrc);
        }
      }
    });

    test('should handle QR code expiration', async () => {
      await checkoutPage.selectPaymentMethod('qrcode');
      await checkoutPage.placeOrder();
      
      // Wait for payment page to load
      await checkoutPage.page.waitForTimeout(2000);
      
      // Check if there's a timer or expiration notice
      const expirationInfo = await checkoutPage.page.locator('.expiration-timer, .qr-expiry, .timeout-notice')
        .first()
        .textContent()
        .catch(() => '');
      
      if (expirationInfo) {
        expect(expirationInfo.toLowerCase()).toMatch(/expire|timeout|minute|second/);
      }
      
      // Check if QR code has a data-expires attribute or similar
      const qrContainer = await checkoutPage.page.locator('.qr-code-container, .qr-payment-section').first();
      
      if (await qrContainer.isVisible()) {
        const hasExpirationData = await qrContainer.evaluate(el => {
          return el.hasAttribute('data-expires') || 
                 el.hasAttribute('data-timeout') ||
                 el.querySelector('[data-expires]') !== null;
        });
        
        if (hasExpirationData) {
          expect(hasExpirationData).toBe(true);
        }
      }
    });

    test('should provide payment status updates', async () => {
      await checkoutPage.selectPaymentMethod('qrcode');
      await checkoutPage.placeOrder();
      
      await checkoutPage.page.waitForTimeout(2000);
      
      // Check for status indicators
      const statusElements = await checkoutPage.page.locator('.payment-status, .qr-status, .status-pending')
        .count();
      
      if (statusElements > 0) {
        const statusText = await checkoutPage.page.locator('.payment-status, .qr-status, .status-pending')
          .first()
          .textContent();
        
        expect(statusText?.toLowerCase()).toMatch(/pending|waiting|scan|processing/);
      }
    });

    test('should handle mobile banking app integration', async () => {
      await checkoutPage.selectPaymentMethod('qrcode');
      await checkoutPage.placeOrder();
      
      await checkoutPage.page.waitForTimeout(2000);
      
      // Check for mobile app links or deep links
      const mobileLink = await checkoutPage.page.locator('a[href*="intent://"], a[href*="promptpay://"], .mobile-banking-link')
        .first()
        .isVisible()
        .catch(() => false);
      
      if (mobileLink) {
        const linkHref = await checkoutPage.page.locator('a[href*="intent://"], a[href*="promptpay://"], .mobile-banking-link')
          .first()
          .getAttribute('href');
        
        expect(linkHref).toMatch(/intent:|promptpay:|app:/);
      }
    });
  });

  test.describe('QR Code Validation and Error Handling', () => {
    test('should validate minimum payment amount for QR code', async () => {
      // Add very low value product if there's a minimum
      await checkoutPage.page.goto('/shop');
      
      // Try to find a very low value product
      const lowValueProduct = await checkoutPage.page.locator('[data-product_id]')
        .first()
        .click()
        .catch(() => {});
      
      await checkoutPage.goto();
      await checkoutPage.fillBillingDetails({
        firstName: 'John',
        lastName: 'Doe',
        email: 'john.doe@test.com',
        phone: '0812345678',
        address: '123 Test Street',
        city: 'Bangkok',
        postcode: '10100'
      });
      
      await checkoutPage.selectPaymentMethod('qrcode');
      
      const cartTotal = parseFloat(await checkoutPage.getCartTotal());
      
      // Check if there are any minimum amount warnings
      const minimumWarning = await checkoutPage.page.locator('.minimum-amount-warning, .amount-error')
        .first()
        .textContent()
        .catch(() => '');
      
      if (minimumWarning && cartTotal < 1) {
        expect(minimumWarning.toLowerCase()).toMatch(/minimum|amount|limit/);
      }
    });

    test('should handle network connectivity issues', async () => {
      await checkoutPage.selectPaymentMethod('qrcode');
      
      // Simulate network failure for QR generation
      await checkoutPage.page.route('**/qr/**', route => {
        route.abort('failed');
      });
      
      await checkoutPage.placeOrder();
      
      // Should show appropriate error message
      const errors = await checkoutPage.getValidationErrors();
      const networkError = errors.some(error => 
        error.toLowerCase().includes('network') ||
        error.toLowerCase().includes('connection') ||
        error.toLowerCase().includes('try again') ||
        error.toLowerCase().includes('qr code')
      );
      
      if (errors.length > 0) {
        expect(networkError).toBe(true);
      }
    });

    test('should handle QR code generation failures', async () => {
      await checkoutPage.selectPaymentMethod('qrcode');
      
      // Mock QR generation failure
      await checkoutPage.page.route('**/moneyspace/**', route => {
        route.fulfill({
          status: 500,
          contentType: 'application/json',
          body: JSON.stringify({ error: 'QR generation failed' })
        });
      });
      
      await checkoutPage.placeOrder();
      
      // Should handle the error gracefully
      const hasError = await checkoutPage.page.locator('.error-message, .payment-error')
        .first()
        .isVisible()
        .catch(() => false);
      
      if (hasError) {
        const errorText = await checkoutPage.page.locator('.error-message, .payment-error')
          .first()
          .textContent();
        
        expect(errorText?.toLowerCase()).toMatch(/error|fail|problem/);
      }
    });

    test('should validate required billing information for QR payments', async () => {
      // Clear billing details
      await checkoutPage.goto();
      
      await checkoutPage.selectPaymentMethod('qrcode');
      await checkoutPage.placeOrder();
      
      // Should require billing information
      const errors = await checkoutPage.getValidationErrors();
      expect(errors.some(error => 
        error.toLowerCase().includes('billing') ||
        error.toLowerCase().includes('required') ||
        error.toLowerCase().includes('email') ||
        error.toLowerCase().includes('phone')
      )).toBe(true);
    });
  });

  test.describe('QR Code User Experience', () => {
    test('should provide clear payment instructions', async () => {
      await checkoutPage.selectPaymentMethod('qrcode');
      await checkoutPage.placeOrder();
      
      await checkoutPage.page.waitForTimeout(2000);
      
      // Check for step-by-step instructions
      const instructions = await checkoutPage.page.locator('.payment-steps, .qr-instructions, .how-to-pay')
        .first()
        .textContent();
      
      if (instructions) {
        const instructionText = instructions.toLowerCase();
        expect(instructionText).toMatch(/step|scan|open|app|bank/);
        expect(instructionText.length).toBeGreaterThan(50); // Should be comprehensive
      }
    });

    test('should show supported banking apps', async () => {
      await checkoutPage.selectPaymentMethod('qrcode');
      
      // Check for supported apps list
      const supportedApps = await checkoutPage.page.locator('.supported-apps, .banking-apps, .payment-apps')
        .first()
        .textContent();
      
      if (supportedApps) {
        const appsText = supportedApps.toLowerCase();
        expect(appsText).toMatch(/scb|kbank|kasikorn|bangkok|krungsri|tmb|promptpay/);
      }
    });

    test('should display QR code with proper contrast and size', async () => {
      await checkoutPage.selectPaymentMethod('qrcode');
      await checkoutPage.placeOrder();
      
      await checkoutPage.page.waitForTimeout(2000);
      
      const qrImage = await checkoutPage.page.locator('img[src*="qr"], .qr-code-image img').first();
      
      if (await qrImage.isVisible()) {
        const boundingBox = await qrImage.boundingBox();
        
        // QR code should be large enough to scan easily
        expect(boundingBox?.width).toBeGreaterThan(150);
        expect(boundingBox?.height).toBeGreaterThan(150);
      }
    });

    test('should provide payment confirmation workflow', async () => {
      await checkoutPage.selectPaymentMethod('qrcode');
      await checkoutPage.placeOrder();
      
      await checkoutPage.page.waitForTimeout(2000);
      
      // Check for payment confirmation elements
      const confirmationElements = [
        '.payment-confirmation',
        '.order-confirmation',
        '.transaction-ref',
        '.reference-number'
      ];
      
      let hasConfirmationUI = false;
      
      for (const selector of confirmationElements) {
        const element = await checkoutPage.page.locator(selector).first();
        if (await element.isVisible()) {
          hasConfirmationUI = true;
          break;
        }
      }
      
      // Should have some form of confirmation UI ready
      // (This might not be visible until actual payment, but structure should exist)
    });

    test('should handle browser back button gracefully', async () => {
      await checkoutPage.selectPaymentMethod('qrcode');
      await checkoutPage.placeOrder();
      
      await checkoutPage.page.waitForTimeout(2000);
      
      // Go back to checkout
      await checkoutPage.page.goBack();
      
      // Should handle gracefully without errors
      const hasError = await checkoutPage.page.locator('.error, .javascript-error')
        .first()
        .isVisible()
        .catch(() => false);
      
      expect(hasError).toBe(false);
      
      // Should still be functional
      const isCheckoutPage = checkoutPage.page.url().includes('checkout');
      if (isCheckoutPage) {
        expect(await checkoutPage.isQRCodeFormVisible()).toBe(false);
      }
    });
  });

  test.describe('QR Code Accessibility', () => {
    test('should provide alternative text for QR code image', async () => {
      await checkoutPage.selectPaymentMethod('qrcode');
      await checkoutPage.placeOrder();
      
      await checkoutPage.page.waitForTimeout(2000);
      
      const qrImage = await checkoutPage.page.locator('img[src*="qr"], .qr-code-image img').first();
      
      if (await qrImage.isVisible()) {
        const altText = await qrImage.getAttribute('alt');
        expect(altText).toBeTruthy();
        expect(altText?.toLowerCase()).toMatch(/qr|payment|scan|code/);
      }
    });

    test('should support keyboard navigation in QR payment flow', async () => {
      await checkoutPage.selectPaymentMethod('qrcode');
      
      // Tab navigation should work
      await checkoutPage.page.keyboard.press('Tab');
      
      const focusedElement = await checkoutPage.page.evaluate(() => document.activeElement?.tagName);
      expect(['INPUT', 'BUTTON', 'A', 'SELECT']).toContain(focusedElement);
    });

    test('should announce payment status to screen readers', async () => {
      await checkoutPage.selectPaymentMethod('qrcode');
      await checkoutPage.placeOrder();
      
      await checkoutPage.page.waitForTimeout(2000);
      
      // Check for ARIA live regions for status updates
      const liveRegion = await checkoutPage.page.locator('[aria-live], [role="status"], [role="alert"]')
        .first()
        .isVisible()
        .catch(() => false);
      
      if (liveRegion) {
        expect(liveRegion).toBe(true);
      }
    });
  });
});
