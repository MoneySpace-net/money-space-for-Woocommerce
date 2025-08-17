import { test, expect } from '@playwright/test';
import { CheckoutPage } from '../page-objects/checkout-page';

test.describe('MoneySpace Installment Payment Method', () => {
  let checkoutPage: CheckoutPage;

  test.beforeEach(async ({ page }) => {
    checkoutPage = new CheckoutPage(page);
    
    // Add a high-value product to cart (>3000 THB for installment eligibility)
    await page.goto('/shop');
    await page.click('[data-product_id="123"]'); // Assuming product ID 123 costs >3000 THB
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

  test('should display installment payment method for eligible amounts', async () => {
    // Check if installment payment method is available
    await expect(checkoutPage.installmentPaymentMethod).toBeVisible();
    
    // Select installment payment method
    await checkoutPage.selectPaymentMethod('installment');
    
    // Verify installment form is displayed
    await expect(checkoutPage.isInstallmentFormVisible()).resolves.toBe(true);
  });

  test('should hide installment payment method for amounts below minimum', async ({ page }) => {
    // Add a low-value product instead
    await page.goto('/shop');
    await page.click('[data-product_id="124"]'); // Assuming product ID 124 costs <3000 THB
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
    
    // Check if minimum amount warning is displayed
    await checkoutPage.selectPaymentMethod('installment');
    await expect(checkoutPage.isMinimumAmountWarningVisible()).resolves.toBe(true);
  });

  test('should allow KTC bank selection and month selection', async () => {
    await checkoutPage.selectInstallmentBank('KTC');
    
    // Verify KTC bank is selected
    await expect(checkoutPage.ktcBankOption).toBeChecked();
    
    // Verify KTC months dropdown is visible
    await expect(checkoutPage.ktcMonthsSelect).toBeVisible();
    
    // Select 6 months
    await checkoutPage.selectInstallmentMonths('KTC', '6');
    
    // Verify selection
    await expect(checkoutPage.ktcMonthsSelect).toHaveValue('6');
  });

  test('should allow BAY bank selection and month selection', async () => {
    await checkoutPage.selectInstallmentBank('BAY');
    
    // Verify BAY bank is selected
    await expect(checkoutPage.bayBankOption).toBeChecked();
    
    // Verify BAY months dropdown is visible
    await expect(checkoutPage.bayMonthsSelect).toBeVisible();
    
    // Select 12 months
    await checkoutPage.selectInstallmentMonths('BAY', '12');
    
    // Verify selection
    await expect(checkoutPage.bayMonthsSelect).toHaveValue('12');
  });

  test('should allow FCY bank selection and month selection', async () => {
    await checkoutPage.selectInstallmentBank('FCY');
    
    // Verify FCY bank is selected
    await expect(checkoutPage.fcyBankOption).toBeChecked();
    
    // Verify FCY months dropdown is visible
    await expect(checkoutPage.fcyMonthsSelect).toBeVisible();
    
    // Select 3 months
    await checkoutPage.selectInstallmentMonths('FCY', '3');
    
    // Verify selection
    await expect(checkoutPage.fcyMonthsSelect).toHaveValue('3');
  });

  test('should reset month selection when switching banks', async () => {
    // Select KTC and set months
    await checkoutPage.selectInstallmentBank('KTC');
    await checkoutPage.selectInstallmentMonths('KTC', '6');
    await expect(checkoutPage.ktcMonthsSelect).toHaveValue('6');
    
    // Switch to BAY
    await checkoutPage.selectInstallmentBank('BAY');
    
    // Verify KTC months is reset and BAY has default value
    await expect(checkoutPage.ktcMonthsSelect).toHaveValue('');
    await expect(checkoutPage.bayMonthsSelect).toHaveValue('3'); // Default value
  });

  test('should validate minimum monthly amount for KTC (300 THB)', async () => {
    await checkoutPage.selectInstallmentBank('KTC');
    
    // Try to select high number of months that would result in <300 THB per month
    const cartTotal = parseFloat(await checkoutPage.getCartTotal());
    const maxMonths = Math.floor(cartTotal / 300);
    const invalidMonths = (maxMonths + 6).toString(); // More than allowed
    
    if (await checkoutPage.ktcMonthsSelect.locator(`option[value="${invalidMonths}"]`).isVisible()) {
      await checkoutPage.selectInstallmentMonths('KTC', invalidMonths);
      await checkoutPage.placeOrder();
      
      // Should show validation error
      const errors = await checkoutPage.getValidationErrors();
      expect(errors.some(error => error.includes('300') || error.includes('minimum'))).toBe(true);
    }
  });

  test('should validate minimum monthly amount for BAY (500 THB)', async () => {
    await checkoutPage.selectInstallmentBank('BAY');
    
    // Try to select high number of months that would result in <500 THB per month
    const cartTotal = parseFloat(await checkoutPage.getCartTotal());
    const maxMonths = Math.floor(cartTotal / 500);
    const invalidMonths = (maxMonths + 6).toString();
    
    if (await checkoutPage.bayMonthsSelect.locator(`option[value="${invalidMonths}"]`).isVisible()) {
      await checkoutPage.selectInstallmentMonths('BAY', invalidMonths);
      await checkoutPage.placeOrder();
      
      // Should show validation error
      const errors = await checkoutPage.getValidationErrors();
      expect(errors.some(error => error.includes('500') || error.includes('minimum'))).toBe(true);
    }
  });

  test('should complete successful installment payment flow', async () => {
    // Select KTC with 6 months
    await checkoutPage.selectInstallmentBank('KTC');
    await checkoutPage.selectInstallmentMonths('KTC', '6');
    
    // Enable debug mode to verify payment data
    await checkoutPage.enableDebugMode();
    
    // Place order
    await checkoutPage.placeOrder();
    
    // Should redirect to order received page or payment gateway
    const currentUrl = checkoutPage.page.url();
    expect(
      currentUrl.includes('order-received') || 
      currentUrl.includes('moneyspace') || 
      currentUrl.includes('payment')
    ).toBe(true);
  });

  test('should validate required bank selection', async () => {
    await checkoutPage.selectPaymentMethod('installment');
    
    // Try to place order without selecting bank
    await checkoutPage.placeOrder();
    
    // Should show validation error
    const errors = await checkoutPage.getValidationErrors();
    expect(errors.some(error => 
      error.includes('bank') || 
      error.includes('select') || 
      error.includes('required')
    )).toBe(true);
  });

  test('should validate required month selection', async () => {
    // Select bank but no months
    await checkoutPage.selectInstallmentBank('KTC');
    // Don't select months
    
    await checkoutPage.placeOrder();
    
    // Should show validation error
    const errors = await checkoutPage.getValidationErrors();
    expect(errors.some(error => 
      error.includes('month') || 
      error.includes('select') || 
      error.includes('required')
    )).toBe(true);
  });

  test('should display correct monthly amounts in dropdown options', async () => {
    await checkoutPage.selectInstallmentBank('KTC');
    
    // Get cart total
    const cartTotal = parseFloat(await checkoutPage.getCartTotal());
    
    // Check if dropdown options show correct monthly amounts
    const options = await checkoutPage.ktcMonthsSelect.locator('option').allTextContents();
    
    options.forEach(option => {
      if (option.includes('months')) {
        // Extract month number from option text
        const monthMatch = option.match(/(\d+)\s+months/);
        if (monthMatch) {
          const months = parseInt(monthMatch[1]);
          const expectedMonthlyAmount = (cartTotal / months).toFixed(2);
          
          // Option should contain the calculated monthly amount
          expect(option).toContain(expectedMonthlyAmount);
        }
      }
    });
  });

  test('should handle dropdowns correctly after previous interaction issues', async () => {
    // This test specifically addresses the dropdown interaction issues we fixed
    await checkoutPage.selectInstallmentBank('KTC');
    
    // Verify dropdown is clickable and interactive
    await expect(checkoutPage.ktcMonthsSelect).toBeVisible();
    await expect(checkoutPage.ktcMonthsSelect).toBeEnabled();
    
    // Click dropdown multiple times to ensure it's responsive
    await checkoutPage.ktcMonthsSelect.click();
    await checkoutPage.page.waitForTimeout(200);
    
    // Select a value
    await checkoutPage.selectInstallmentMonths('KTC', '6');
    await expect(checkoutPage.ktcMonthsSelect).toHaveValue('6');
    
    // Switch to another bank and verify dropdown still works
    await checkoutPage.selectInstallmentBank('BAY');
    await expect(checkoutPage.bayMonthsSelect).toBeVisible();
    await expect(checkoutPage.bayMonthsSelect).toBeEnabled();
    
    await checkoutPage.selectInstallmentMonths('BAY', '12');
    await expect(checkoutPage.bayMonthsSelect).toHaveValue('12');
  });
});
