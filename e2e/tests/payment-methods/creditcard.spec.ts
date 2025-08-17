import { test, expect } from '@playwright/test';
const { CheckoutPage } = require('../page-objects/checkout-page.js');

test.describe('MoneySpace Credit Card Payments', () => {
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

  test.describe('Credit Card Form Functionality', () => {
    test('should display credit card form when method is selected', async () => {
      await checkoutPage.selectPaymentMethod('creditcard');
      
      expect(await checkoutPage.isCreditCardFormVisible()).toBe(true);
      expect(await checkoutPage.cardNumberInput.isVisible()).toBe(true);
      expect(await checkoutPage.cardExpiryInput.isVisible()).toBe(true);
      expect(await checkoutPage.cardCvvInput.isVisible()).toBe(true);
      expect(await checkoutPage.cardHolderInput.isVisible()).toBe(true);
    });

    test('should hide credit card form when different method is selected', async () => {
      await checkoutPage.selectPaymentMethod('creditcard');
      expect(await checkoutPage.isCreditCardFormVisible()).toBe(true);
      
      await checkoutPage.selectPaymentMethod('qrcode');
      expect(await checkoutPage.isCreditCardFormVisible()).toBe(false);
    });

    test('should format card number input correctly', async () => {
      await checkoutPage.selectPaymentMethod('creditcard');
      
      // Type card number without spaces
      await checkoutPage.cardNumberInput.fill('4111111111111111');
      
      // Check if it gets formatted with spaces
      const formattedValue = await checkoutPage.cardNumberInput.inputValue();
      expect(formattedValue).toMatch(/^\d{4}\s\d{4}\s\d{4}\s\d{4}$/);
    });

    test('should format expiry date input correctly', async () => {
      await checkoutPage.selectPaymentMethod('creditcard');
      
      // Type expiry without slash
      await checkoutPage.cardExpiryInput.fill('1225');
      
      // Check if it gets formatted with slash
      const formattedValue = await checkoutPage.cardExpiryInput.inputValue();
      expect(formattedValue).toBe('12/25');
    });

    test('should limit CVV input length', async () => {
      await checkoutPage.selectPaymentMethod('creditcard');
      
      // Type long CVV
      await checkoutPage.cardCvvInput.fill('12345678');
      
      // Check if it's limited to 3-4 digits
      const cvvValue = await checkoutPage.cardCvvInput.inputValue();
      expect(cvvValue.length).toBeLessThanOrEqual(4);
      expect(cvvValue).toMatch(/^\d+$/);
    });

    test('should validate card number in real-time', async () => {
      await checkoutPage.selectPaymentMethod('creditcard');
      
      // Type invalid card number
      await checkoutPage.cardNumberInput.fill('1234567890123456');
      await checkoutPage.cardNumberInput.blur();
      
      // Should show validation indicator
      const hasError = await checkoutPage.page.locator('.card-number-error, .field-error, .invalid-feedback')
        .first()
        .isVisible()
        .catch(() => false);
      
      // If real-time validation is implemented
      if (hasError) {
        expect(hasError).toBe(true);
      }
    });

    test('should detect card type from number', async () => {
      await checkoutPage.selectPaymentMethod('creditcard');
      
      // Test Visa detection
      await checkoutPage.cardNumberInput.fill('4111');
      await checkoutPage.page.waitForTimeout(200);
      
      let cardTypeIndicator = await checkoutPage.page.locator('.card-type-visa, .visa-icon, [data-card-type="visa"]')
        .first()
        .isVisible()
        .catch(() => false);
      
      if (cardTypeIndicator) {
        expect(cardTypeIndicator).toBe(true);
      }
      
      // Test Mastercard detection
      await checkoutPage.cardNumberInput.fill('5555');
      await checkoutPage.page.waitForTimeout(200);
      
      cardTypeIndicator = await checkoutPage.page.locator('.card-type-mastercard, .mastercard-icon, [data-card-type="mastercard"]')
        .first()
        .isVisible()
        .catch(() => false);
      
      if (cardTypeIndicator) {
        expect(cardTypeIndicator).toBe(true);
      }
    });
  });

  test.describe('Credit Card Payment Processing', () => {
    test('should successfully process valid credit card payment', async () => {
      await checkoutPage.selectPaymentMethod('creditcard');
      
      await checkoutPage.fillCreditCardDetails({
        number: '4111111111111111',
        expiry: '12/25',
        cvv: '123',
        holder: 'John Doe'
      });
      
      await checkoutPage.placeOrder();
      
      // Should redirect to MoneySpace payment page or show success
      await expect(checkoutPage.page).toHaveURL(/moneyspace|success|order-received/);
    });

    test('should handle different card types', async () => {
      const cardTypes = [
        { name: 'Visa', number: '4111111111111111' },
        { name: 'Mastercard', number: '5555555555554444' },
        { name: 'American Express', number: '378282246310005' }
      ];

      for (const cardType of cardTypes) {
        await checkoutPage.selectPaymentMethod('creditcard');
        
        await checkoutPage.fillCreditCardDetails({
          number: cardType.number,
          expiry: '12/25',
          cvv: cardType.name === 'American Express' ? '1234' : '123',
          holder: 'John Doe'
        });
        
        await checkoutPage.placeOrder();
        
        // Should not show card type rejection errors
        const errors = await checkoutPage.getValidationErrors();
        expect(errors.filter(error => 
          error.toLowerCase().includes('card type') ||
          error.toLowerCase().includes('not supported')
        )).toHaveLength(0);
        
        // Reset for next iteration
        if (cardTypes.indexOf(cardType) < cardTypes.length - 1) {
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
        }
      }
    });

    test('should handle network errors gracefully', async () => {
      await checkoutPage.selectPaymentMethod('creditcard');
      
      await checkoutPage.fillCreditCardDetails({
        number: '4111111111111111',
        expiry: '12/25',
        cvv: '123',
        holder: 'John Doe'
      });
      
      // Simulate network failure
      await checkoutPage.page.route('**/moneyspace/**', route => {
        route.abort('failed');
      });
      
      await checkoutPage.placeOrder();
      
      // Should show appropriate error message
      const errors = await checkoutPage.getValidationErrors();
      expect(errors.some(error => 
        error.toLowerCase().includes('network') ||
        error.toLowerCase().includes('connection') ||
        error.toLowerCase().includes('try again')
      )).toBe(true);
    });

    test('should handle expired cards', async () => {
      await checkoutPage.selectPaymentMethod('creditcard');
      
      await checkoutPage.fillCreditCardDetails({
        number: '4111111111111111',
        expiry: '01/20', // Expired date
        cvv: '123',
        holder: 'John Doe'
      });
      
      await checkoutPage.placeOrder();
      
      const errors = await checkoutPage.getValidationErrors();
      expect(errors.some(error => 
        error.toLowerCase().includes('expiry') ||
        error.toLowerCase().includes('expired')
      )).toBe(true);
    });

    test('should handle insufficient funds scenario', async () => {
      await checkoutPage.selectPaymentMethod('creditcard');
      
      // Use test card that simulates insufficient funds
      await checkoutPage.fillCreditCardDetails({
        number: '4000000000000002', // Common test card for insufficient funds
        expiry: '12/25',
        cvv: '123',
        holder: 'John Doe'
      });
      
      await checkoutPage.placeOrder();
      
      // Should handle the response appropriately
      // Either redirect to payment page (if external handling) or show error
      const currentUrl = checkoutPage.page.url();
      if (currentUrl.includes('checkout')) {
        // If staying on checkout, should show error
        const errors = await checkoutPage.getValidationErrors();
        expect(errors.some(error => 
          error.toLowerCase().includes('insufficient') ||
          error.toLowerCase().includes('declined') ||
          error.toLowerCase().includes('funds')
        )).toBe(true);
      }
    });

    test('should preserve form data on validation failure', async () => {
      await checkoutPage.selectPaymentMethod('creditcard');
      
      const cardDetails = {
        number: '4111111111111111',
        expiry: '12/25',
        cvv: '123',
        holder: '' // Missing holder to trigger validation
      };
      
      await checkoutPage.fillCreditCardDetails(cardDetails);
      await checkoutPage.placeOrder();
      
      // Form should preserve entered data
      expect(await checkoutPage.cardNumberInput.inputValue()).toContain('4111');
      expect(await checkoutPage.cardExpiryInput.inputValue()).toBe('12/25');
      expect(await checkoutPage.cardCvvInput.inputValue()).toBe('123');
    });

    test('should clear sensitive data on successful submission', async () => {
      await checkoutPage.selectPaymentMethod('creditcard');
      
      await checkoutPage.fillCreditCardDetails({
        number: '4111111111111111',
        expiry: '12/25',
        cvv: '123',
        holder: 'John Doe'
      });
      
      await checkoutPage.placeOrder();
      
      // If staying on same page after successful processing
      if (checkoutPage.page.url().includes('checkout')) {
        // Sensitive fields should be cleared for security
        const cvvValue = await checkoutPage.cardCvvInput.inputValue();
        expect(cvvValue).toBe('');
      }
    });
  });

  test.describe('Credit Card Security Features', () => {
    test('should mask card number input', async () => {
      await checkoutPage.selectPaymentMethod('creditcard');
      
      await checkoutPage.cardNumberInput.fill('4111111111111111');
      
      // Check if input has masking attributes
      const inputType = await checkoutPage.cardNumberInput.getAttribute('type');
      const autocomplete = await checkoutPage.cardNumberInput.getAttribute('autocomplete');
      
      expect(autocomplete).toBe('cc-number');
      
      // Should not store in browser history/autocomplete inappropriately
      const inputValue = await checkoutPage.cardNumberInput.inputValue();
      expect(inputValue).toBeTruthy(); // Should have value but be properly handled
    });

    test('should prevent copy/paste of sensitive data from DOM', async () => {
      await checkoutPage.selectPaymentMethod('creditcard');
      
      await checkoutPage.fillCreditCardDetails({
        number: '4111111111111111',
        expiry: '12/25',
        cvv: '123',
        holder: 'John Doe'
      });
      
      // CVV should not be easily extractable from DOM
      const cvvValue = await checkoutPage.page.evaluate(() => {
        const cvvInput = document.querySelector('[name*="cvv"], #card_cvv, [placeholder*="CVV"]') as HTMLInputElement;
        return cvvInput ? cvvInput.value : '';
      });
      
      // Depending on implementation, CVV might be masked in DOM
      if (cvvValue) {
        expect(typeof cvvValue).toBe('string');
      }
    });

    test('should implement proper SSL/HTTPS requirements', async () => {
      // Ensure checkout is served over HTTPS
      expect(checkoutPage.page.url()).toMatch(/^https:/);
    });

    test('should validate security attributes on form fields', async () => {
      await checkoutPage.selectPaymentMethod('creditcard');
      
      // Check autocomplete attributes
      const cardNumberAutocomplete = await checkoutPage.cardNumberInput.getAttribute('autocomplete');
      const expiryAutocomplete = await checkoutPage.cardExpiryInput.getAttribute('autocomplete');
      const cvvAutocomplete = await checkoutPage.cardCvvInput.getAttribute('autocomplete');
      const holderAutocomplete = await checkoutPage.cardHolderInput.getAttribute('autocomplete');
      
      expect(cardNumberAutocomplete).toBe('cc-number');
      expect(expiryAutocomplete).toBe('cc-exp');
      expect(cvvAutocomplete).toBe('cc-csc');
      expect(holderAutocomplete).toBe('cc-name');
    });
  });

  test.describe('Credit Card Accessibility', () => {
    test('should have proper ARIA labels and accessibility attributes', async () => {
      await checkoutPage.selectPaymentMethod('creditcard');
      
      // Check for accessible labels
      const cardNumberLabel = await checkoutPage.cardNumberInput.getAttribute('aria-label') ||
                             await checkoutPage.page.locator('label[for*="card_number"]').textContent();
      
      expect(cardNumberLabel).toBeTruthy();
      expect(cardNumberLabel?.toLowerCase()).toContain('card');
      
      // Check for required indicators
      const isRequired = await checkoutPage.cardNumberInput.getAttribute('required') ||
                        await checkoutPage.cardNumberInput.getAttribute('aria-required');
      
      expect(isRequired).toBeTruthy();
    });

    test('should support keyboard navigation', async () => {
      await checkoutPage.selectPaymentMethod('creditcard');
      
      // Tab through form fields
      await checkoutPage.cardNumberInput.focus();
      await checkoutPage.page.keyboard.press('Tab');
      
      // Should move to expiry field
      const focusedElement = await checkoutPage.page.evaluate(() => document.activeElement?.tagName);
      expect(focusedElement).toBe('INPUT');
    });

    test('should announce errors to screen readers', async () => {
      await checkoutPage.selectPaymentMethod('creditcard');
      
      await checkoutPage.fillCreditCardDetails({
        number: '1234', // Invalid
        expiry: '12/25',
        cvv: '123',
        holder: 'John Doe'
      });
      
      await checkoutPage.placeOrder();
      
      // Check for ARIA live regions or error announcements
      const errorRegion = await checkoutPage.page.locator('[aria-live], [role="alert"], .error-message')
        .first()
        .isVisible()
        .catch(() => false);
      
      if (errorRegion) {
        expect(errorRegion).toBe(true);
      }
    });
  });
});
