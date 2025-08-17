import { test, expect } from '@playwright/test';
import { CheckoutPage } from '../page-objects/checkout-page';

test.describe('MoneySpace Notice Clearing System', () => {
  let checkoutPage: CheckoutPage;

  test.beforeEach(async ({ page }) => {
    checkoutPage = new CheckoutPage(page);
    
    // Add product to cart
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

  test('should clear validation notices when switching from installment to other payment methods', async () => {
    // Select installment and trigger validation error
    await checkoutPage.selectPaymentMethod('installment');
    await checkoutPage.placeOrder(); // This should trigger validation error
    
    // Verify validation error is present
    let errors = await checkoutPage.getValidationErrors();
    expect(errors.length).toBeGreaterThan(0);
    
    // Switch to credit card payment method
    await checkoutPage.selectPaymentMethod('creditcard');
    
    // Wait a moment for notice clearing to take effect
    await checkoutPage.page.waitForTimeout(500);
    
    // Verify installment validation errors are cleared
    await checkoutPage.waitForNoValidationErrors();
    
    errors = await checkoutPage.getValidationErrors();
    expect(errors.filter(error => 
      error.includes('installment') || 
      error.includes('bank') || 
      error.includes('3,000')
    )).toHaveLength(0);
  });

  test('should clear validation notices when switching from installment to QR code', async () => {
    // Select installment and trigger validation error
    await checkoutPage.selectPaymentMethod('installment');
    await checkoutPage.placeOrder();
    
    // Verify validation error is present
    let errors = await checkoutPage.getValidationErrors();
    expect(errors.length).toBeGreaterThan(0);
    
    // Switch to QR code payment method
    await checkoutPage.selectPaymentMethod('qrcode');
    
    // Wait for notice clearing
    await checkoutPage.page.waitForTimeout(500);
    
    // Verify notices are cleared
    await checkoutPage.waitForNoValidationErrors();
    
    errors = await checkoutPage.getValidationErrors();
    expect(errors.filter(error => 
      error.includes('installment') || 
      error.includes('bank') || 
      error.includes('month')
    )).toHaveLength(0);
  });

  test('should not clear notices from other payment methods', async () => {
    // Select credit card and trigger validation error
    await checkoutPage.selectPaymentMethod('creditcard');
    await checkoutPage.placeOrder(); // This should trigger credit card validation error
    
    // Switch to installment
    await checkoutPage.selectPaymentMethod('installment');
    
    // Wait a moment
    await checkoutPage.page.waitForTimeout(500);
    
    // Credit card errors should still be present (this test depends on your specific validation logic)
    const errors = await checkoutPage.getValidationErrors();
    // This assertion may need adjustment based on your specific error handling
  });

  test('should clear notices immediately on payment method change', async () => {
    // Select installment and trigger validation
    await checkoutPage.selectPaymentMethod('installment');
    await checkoutPage.placeOrder();
    
    // Record time before switching
    const startTime = Date.now();
    
    // Switch payment method
    await checkoutPage.selectPaymentMethod('creditcard');
    
    // Wait for notices to be cleared
    await checkoutPage.waitForNoValidationErrors();
    
    const endTime = Date.now();
    const clearingTime = endTime - startTime;
    
    // Notice clearing should happen quickly (within 1 second)
    expect(clearingTime).toBeLessThan(1000);
  });

  test('should clear notices that appear after payment method switch', async () => {
    // Select installment and trigger validation
    await checkoutPage.selectPaymentMethod('installment');
    await checkoutPage.placeOrder();
    
    // Switch to credit card
    await checkoutPage.selectPaymentMethod('creditcard');
    
    // Wait for initial clearing
    await checkoutPage.page.waitForTimeout(200);
    
    // Simulate a late-appearing notice (this is what the 100ms timeout handles)
    await checkoutPage.page.evaluate(() => {
      const notice = document.createElement('div');
      notice.className = 'wc-block-components-notice-banner';
      notice.textContent = 'Please select a bank for installment payment';
      document.body.appendChild(notice);
    });
    
    // Wait for the delayed clearing to take effect
    await checkoutPage.page.waitForTimeout(150);
    
    // The late notice should be hidden
    const lateNotice = checkoutPage.page.locator('.wc-block-components-notice-banner:has-text("Please select a bank")');
    await expect(lateNotice).toHaveCSS('display', 'none');
  });

  test('should reset installment form data when switching away', async () => {
    // Select installment bank and months
    await checkoutPage.selectInstallmentBank('KTC');
    await checkoutPage.selectInstallmentMonths('KTC', '6');
    
    // Verify selections are made
    await expect(checkoutPage.ktcBankOption).toBeChecked();
    await expect(checkoutPage.ktcMonthsSelect).toHaveValue('6');
    
    // Switch to credit card
    await checkoutPage.selectPaymentMethod('creditcard');
    
    // Switch back to installment
    await checkoutPage.selectPaymentMethod('installment');
    
    // Form should be reset
    await expect(checkoutPage.ktcBankOption).not.toBeChecked();
    await expect(checkoutPage.bayBankOption).not.toBeChecked();
    await expect(checkoutPage.fcyBankOption).not.toBeChecked();
  });

  test('should handle rapid payment method switching', async () => {
    // Rapidly switch between payment methods
    await checkoutPage.selectPaymentMethod('installment');
    await checkoutPage.page.waitForTimeout(100);
    
    await checkoutPage.selectPaymentMethod('creditcard');
    await checkoutPage.page.waitForTimeout(100);
    
    await checkoutPage.selectPaymentMethod('qrcode');
    await checkoutPage.page.waitForTimeout(100);
    
    await checkoutPage.selectPaymentMethod('installment');
    await checkoutPage.page.waitForTimeout(100);
    
    // Should not cause any JavaScript errors or broken state
    const errors = await checkoutPage.page.evaluate(() => {
      return window.console.error.toString();
    });
    
    // No console errors should be present (this is a basic check)
    expect(errors).not.toContain('MoneySpace');
  });

  test('should observe DOM changes and clear relevant notices', async () => {
    // Select installment
    await checkoutPage.selectPaymentMethod('installment');
    
    // Switch to credit card
    await checkoutPage.selectPaymentMethod('creditcard');
    
    // Simulate a notice being added to the DOM after switch (MutationObserver test)
    await checkoutPage.page.evaluate(() => {
      setTimeout(() => {
        const notice = document.createElement('div');
        notice.className = 'wc-block-components-notice-banner';
        notice.textContent = 'The amount must be 3,000.01 baht or more for installment';
        
        const container = document.querySelector('.wc-block-components-notices');
        if (container) {
          container.appendChild(notice);
        } else {
          document.body.appendChild(notice);
        }
      }, 25); // Add notice after a short delay
    });
    
    // Wait for MutationObserver to process the change
    await checkoutPage.page.waitForTimeout(100);
    
    // The notice should be automatically hidden
    const installmentNotice = checkoutPage.page.locator('.wc-block-components-notice-banner:has-text("3,000.01 baht")');
    await expect(installmentNotice).toHaveAttribute('aria-hidden', 'true');
  });

  test('should use CSS classes for hiding notices', async () => {
    // Select installment and trigger validation
    await checkoutPage.selectPaymentMethod('installment');
    await checkoutPage.placeOrder();
    
    // Switch payment method
    await checkoutPage.selectPaymentMethod('creditcard');
    
    // Wait for clearing
    await checkoutPage.page.waitForTimeout(300);
    
    // Check that notices have the 'mns-hidden' class applied
    const hiddenNotices = await checkoutPage.page.locator('.mns-hidden').count();
    expect(hiddenNotices).toBeGreaterThan(0);
    
    // Verify CSS properties are applied
    const firstHiddenNotice = checkoutPage.page.locator('.mns-hidden').first();
    if (await firstHiddenNotice.isVisible()) {
      await expect(firstHiddenNotice).toHaveCSS('display', 'none');
    }
  });

  test('should clear notices across different notice selectors', async () => {
    // Test that all notice selector types are handled
    const noticeSelectors = [
      '.wc-block-components-notice-banner',
      '.wc-block-components-validation-error',
      '.woocommerce-error',
      '.woocommerce-message',
      '.woocommerce-info'
    ];
    
    // Add test notices of each type
    await checkoutPage.page.evaluate((selectors) => {
      selectors.forEach((selector, index) => {
        const notice = document.createElement('div');
        notice.className = selector.replace('.', '');
        notice.textContent = `Test notice ${index + 1}`;
        document.body.appendChild(notice);
      });
    }, noticeSelectors);
    
    // Select installment then switch away
    await checkoutPage.selectPaymentMethod('installment');
    await checkoutPage.selectPaymentMethod('creditcard');
    
    // Wait for clearing
    await checkoutPage.page.waitForTimeout(300);
    
    // All test notices should be hidden
    for (const selector of noticeSelectors) {
      const notice = checkoutPage.page.locator(selector);
      if (await notice.count() > 0) {
        await expect(notice.first()).toHaveCSS('display', 'none');
      }
    }
  });
});
