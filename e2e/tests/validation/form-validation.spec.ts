import { test, expect } from '@playwright/test';
import { CheckoutPage } from '../page-objects/checkout-page';

test.describe('MoneySpace Form Validation', () => {
  let checkoutPage: CheckoutPage;

  test.beforeEach(async ({ page }) => {
    checkoutPage = new CheckoutPage(page);
    
    // Add high-value product to cart for installment testing
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

  test.describe('Installment Payment Validation', () => {
    test('should validate minimum amount requirement (3000 THB)', async ({ page }) => {
      // Add low-value product
      await page.goto('/shop');
      await page.click('[data-product_id="124"]'); // Product < 3000 THB
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
      
      await checkoutPage.selectPaymentMethod('installment');
      
      // Should show minimum amount warning
      await expect(checkoutPage.isMinimumAmountWarningVisible()).resolves.toBe(true);
    });

    test('should validate bank selection is required', async () => {
      await checkoutPage.selectPaymentMethod('installment');
      await checkoutPage.placeOrder();
      
      const errors = await checkoutPage.getValidationErrors();
      expect(errors.some(error => 
        error.toLowerCase().includes('bank') || 
        error.toLowerCase().includes('select')
      )).toBe(true);
    });

    test('should validate month selection is required after bank selection', async () => {
      await checkoutPage.selectPaymentMethod('installment');
      await checkoutPage.selectInstallmentBank('KTC');
      
      // Clear the default month selection
      await checkoutPage.page.evaluate(() => {
        const select = document.querySelector('#ktc_permonths') as HTMLSelectElement;
        if (select) select.value = '';
      });
      
      await checkoutPage.placeOrder();
      
      const errors = await checkoutPage.getValidationErrors();
      expect(errors.some(error => 
        error.toLowerCase().includes('month') || 
        error.toLowerCase().includes('select')
      )).toBe(true);
    });

    test('should validate KTC monthly minimum (300 THB)', async () => {
      const cartTotal = parseFloat(await checkoutPage.getCartTotal());
      
      // Find a month value that would result in < 300 THB per month
      const invalidMonths = Math.ceil(cartTotal / 250); // This should make monthly < 300
      
      await checkoutPage.selectInstallmentBank('KTC');
      
      // Check if such an option exists (it shouldn't be shown, but test validates)
      const monthOptions = await checkoutPage.ktcMonthsSelect.locator('option').allTextContents();
      const hasInvalidOption = monthOptions.some(option => option.includes(invalidMonths.toString()));
      
      if (hasInvalidOption) {
        await checkoutPage.selectInstallmentMonths('KTC', invalidMonths.toString());
        await checkoutPage.placeOrder();
        
        const errors = await checkoutPage.getValidationErrors();
        expect(errors.some(error => 
          error.includes('300') || 
          error.toLowerCase().includes('minimum')
        )).toBe(true);
      }
    });

    test('should validate BAY monthly minimum (500 THB)', async () => {
      const cartTotal = parseFloat(await checkoutPage.getCartTotal());
      
      // Find a month value that would result in < 500 THB per month
      const invalidMonths = Math.ceil(cartTotal / 450); // This should make monthly < 500
      
      await checkoutPage.selectInstallmentBank('BAY');
      
      const monthOptions = await checkoutPage.bayMonthsSelect.locator('option').allTextContents();
      const hasInvalidOption = monthOptions.some(option => option.includes(invalidMonths.toString()));
      
      if (hasInvalidOption) {
        await checkoutPage.selectInstallmentMonths('BAY', invalidMonths.toString());
        await checkoutPage.placeOrder();
        
        const errors = await checkoutPage.getValidationErrors();
        expect(errors.some(error => 
          error.includes('500') || 
          error.toLowerCase().includes('minimum')
        )).toBe(true);
      }
    });

    test('should validate FCY monthly minimum (300 THB)', async () => {
      const cartTotal = parseFloat(await checkoutPage.getCartTotal());
      
      // Find a month value that would result in < 300 THB per month
      const invalidMonths = Math.ceil(cartTotal / 250);
      
      await checkoutPage.selectInstallmentBank('FCY');
      
      const monthOptions = await checkoutPage.fcyMonthsSelect.locator('option').allTextContents();
      const hasInvalidOption = monthOptions.some(option => option.includes(invalidMonths.toString()));
      
      if (hasInvalidOption) {
        await checkoutPage.selectInstallmentMonths('FCY', invalidMonths.toString());
        await checkoutPage.placeOrder();
        
        const errors = await checkoutPage.getValidationErrors();
        expect(errors.some(error => 
          error.includes('300') || 
          error.toLowerCase().includes('minimum')
        )).toBe(true);
      }
    });

    test('should validate maximum months allowed per bank', async () => {
      await checkoutPage.selectInstallmentBank('KTC');
      
      // Get all available month options
      const monthOptions = await checkoutPage.ktcMonthsSelect.locator('option').allTextContents();
      const monthValues = monthOptions
        .map(option => {
          const match = option.match(/(\d+)\s+months/);
          return match ? parseInt(match[1]) : null;
        })
        .filter(value => value !== null) as number[];
      
      if (monthValues.length > 0) {
        const maxAllowedMonths = Math.max(...monthValues);
        
        // Try to manually set a higher value (if possible through DOM manipulation)
        const higherMonths = maxAllowedMonths + 6;
        
        await checkoutPage.page.evaluate((months) => {
          const select = document.querySelector('#ktc_permonths') as HTMLSelectElement;
          if (select) {
            const option = document.createElement('option');
            option.value = months.toString();
            option.textContent = `${months} months (Invalid)`;
            select.appendChild(option);
            select.value = months.toString();
          }
        }, higherMonths);
        
        await checkoutPage.placeOrder();
        
        const errors = await checkoutPage.getValidationErrors();
        expect(errors.some(error => 
          error.toLowerCase().includes('exceed') || 
          error.toLowerCase().includes('maximum') ||
          error.toLowerCase().includes('available')
        )).toBe(true);
      }
    });

    test('should only validate when installment payment method is selected', async () => {
      // Select a different payment method
      await checkoutPage.selectPaymentMethod('creditcard');
      
      // Validation should pass even without installment data
      await checkoutPage.placeOrder();
      
      // Should not show installment-specific validation errors
      const errors = await checkoutPage.getValidationErrors();
      expect(errors.filter(error => 
        error.toLowerCase().includes('bank') ||
        error.toLowerCase().includes('installment') ||
        error.includes('3,000')
      )).toHaveLength(0);
    });
  });

  test.describe('Credit Card Payment Validation', () => {
    test('should validate credit card number format', async () => {
      await checkoutPage.selectPaymentMethod('creditcard');
      
      // Fill invalid card number
      await checkoutPage.fillCreditCardDetails({
        number: '1234', // Invalid short number
        expiry: '12/25',
        cvv: '123',
        holder: 'John Doe'
      });
      
      await checkoutPage.placeOrder();
      
      const errors = await checkoutPage.getValidationErrors();
      expect(errors.some(error => 
        error.toLowerCase().includes('card') ||
        error.toLowerCase().includes('number') ||
        error.toLowerCase().includes('invalid')
      )).toBe(true);
    });

    test('should validate credit card expiry date', async () => {
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
        error.toLowerCase().includes('expired') ||
        error.toLowerCase().includes('date')
      )).toBe(true);
    });

    test('should validate CVV code', async () => {
      await checkoutPage.selectPaymentMethod('creditcard');
      
      await checkoutPage.fillCreditCardDetails({
        number: '4111111111111111',
        expiry: '12/25',
        cvv: '12', // Invalid short CVV
        holder: 'John Doe'
      });
      
      await checkoutPage.placeOrder();
      
      const errors = await checkoutPage.getValidationErrors();
      expect(errors.some(error => 
        error.toLowerCase().includes('cvv') ||
        error.toLowerCase().includes('security') ||
        error.toLowerCase().includes('code')
      )).toBe(true);
    });

    test('should validate cardholder name', async () => {
      await checkoutPage.selectPaymentMethod('creditcard');
      
      await checkoutPage.fillCreditCardDetails({
        number: '4111111111111111',
        expiry: '12/25',
        cvv: '123',
        holder: '' // Empty cardholder name
      });
      
      await checkoutPage.placeOrder();
      
      const errors = await checkoutPage.getValidationErrors();
      expect(errors.some(error => 
        error.toLowerCase().includes('holder') ||
        error.toLowerCase().includes('name') ||
        error.toLowerCase().includes('required')
      )).toBe(true);
    });
  });

  test.describe('General Validation Behavior', () => {
    test('should show validation errors in user-friendly format', async () => {
      await checkoutPage.selectPaymentMethod('installment');
      await checkoutPage.placeOrder();
      
      const errors = await checkoutPage.getValidationErrors();
      
      // Errors should be user-friendly, not technical
      errors.forEach(error => {
        expect(error).not.toContain('undefined');
        expect(error).not.toContain('null');
        expect(error).not.toContain('NaN');
        expect(error.length).toBeGreaterThan(10); // Should be descriptive
      });
    });

    test('should clear validation errors when correcting form', async () => {
      // Trigger validation error
      await checkoutPage.selectPaymentMethod('installment');
      await checkoutPage.placeOrder();
      
      let errors = await checkoutPage.getValidationErrors();
      expect(errors.length).toBeGreaterThan(0);
      
      // Correct the form
      await checkoutPage.selectInstallmentBank('KTC');
      await checkoutPage.selectInstallmentMonths('KTC', '6');
      
      // Wait for validation to clear
      await checkoutPage.page.waitForTimeout(500);
      
      // Errors should be reduced or cleared
      errors = await checkoutPage.getValidationErrors();
      const installmentErrors = errors.filter(error => 
        error.toLowerCase().includes('bank') ||
        error.toLowerCase().includes('month')
      );
      
      expect(installmentErrors.length).toBe(0);
    });

    test('should handle validation during rapid form changes', async () => {
      await checkoutPage.selectPaymentMethod('installment');
      
      // Rapidly change selections
      await checkoutPage.selectInstallmentBank('KTC');
      await checkoutPage.selectInstallmentBank('BAY');
      await checkoutPage.selectInstallmentBank('FCY');
      await checkoutPage.selectInstallmentMonths('FCY', '6');
      
      // Should not cause validation errors or broken state
      const errors = await checkoutPage.getValidationErrors();
      expect(errors.filter(error => 
        error.toLowerCase().includes('error') &&
        error.toLowerCase().includes('javascript')
      )).toHaveLength(0);
    });

    test('should validate form only when attempting to place order', async () => {
      await checkoutPage.selectPaymentMethod('installment');
      
      // Form should not show validation errors initially
      let errors = await checkoutPage.getValidationErrors();
      expect(errors.length).toBe(0);
      
      // Select bank but not months - still no errors
      await checkoutPage.selectInstallmentBank('KTC');
      await checkoutPage.page.waitForTimeout(200);
      
      errors = await checkoutPage.getValidationErrors();
      expect(errors.length).toBe(0);
      
      // Only when placing order should validation trigger
      await checkoutPage.placeOrder();
      
      errors = await checkoutPage.getValidationErrors();
      expect(errors.length).toBeGreaterThan(0);
    });
  });
});
