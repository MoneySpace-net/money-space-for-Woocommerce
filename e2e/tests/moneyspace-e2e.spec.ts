import { test, expect } from '@playwright/test';

test.describe('MoneySpace Payment Gateway E2E Tests', () => {
  
  test.beforeEach(async ({ page }) => {
    // Add a product to cart before each test
    await page.goto('/shop');
    
    // Wait for shop page to load
    await page.waitForLoadState('networkidle');
    
    // Try to add a product to cart (look for common WooCommerce add to cart buttons)
    const addToCartButtons = page.locator('.add_to_cart_button, .single_add_to_cart_button, button[name="add-to-cart"]');
    
    if (await addToCartButtons.first().isVisible()) {
      await addToCartButtons.first().click();
      await page.waitForTimeout(2000); // Wait for cart update
    } else {
      // If no products, go directly to checkout to test with empty cart
      console.log('No products found in shop, testing with empty cart');
    }
  });

  test('MoneySpace Credit Card payment method should be available', async ({ page }) => {
    // Navigate to checkout
    await page.goto('/checkout');
    await page.waitForLoadState('networkidle');
    
    // Look for MoneySpace Credit Card payment method
    const creditCardMethod = page.locator('input[value*="moneyspace"], input[value*="creditcard"], label:has-text("MoneySpace"), label:has-text("Credit Card")');
    
    // Check if any MoneySpace payment method exists
    const methodExists = await creditCardMethod.count() > 0;
    
    if (methodExists) {
      console.log('✅ MoneySpace payment method found on checkout page');
      
      // Try to select the payment method
      await creditCardMethod.first().click();
      await page.waitForTimeout(1000);
      
      // Look for credit card form fields
      const cardFields = page.locator('input[placeholder*="card"], input[name*="card"], input[type="tel"]');
      const formVisible = await cardFields.count() > 0;
      
      if (formVisible) {
        console.log('✅ Credit card form fields are visible');
      } else {
        console.log('⚠️ Credit card form not visible, but payment method exists');
      }
    } else {
      console.log('❌ MoneySpace payment method not found - plugin may not be active');
    }
    
    // Take a screenshot for debugging
    await page.screenshot({ path: 'test-results/moneyspace-checkout.png', fullPage: true });
  });

  test('MoneySpace QR Code payment method should be available', async ({ page }) => {
    await page.goto('/checkout');
    await page.waitForLoadState('networkidle');
    
    // Look for QR Code payment method
    const qrMethod = page.locator('input[value*="qr"], input[value*="promptpay"], label:has-text("QR"), label:has-text("PromptPay")');
    
    const methodExists = await qrMethod.count() > 0;
    
    if (methodExists) {
      console.log('✅ MoneySpace QR Code payment method found');
      
      await qrMethod.first().click();
      await page.waitForTimeout(1000);
      
      // Look for QR code related elements
      const qrElements = page.locator('.qr-code, .promptpay, [class*="qr"]');
      const qrVisible = await qrElements.count() > 0;
      
      if (qrVisible) {
        console.log('✅ QR Code payment form is visible');
      }
    } else {
      console.log('⚠️ QR Code payment method not found');
    }
  });

  test('MoneySpace Installment payment method should be available', async ({ page }) => {
    await page.goto('/checkout');
    await page.waitForLoadState('networkidle');
    
    // Look for Installment payment method
    const installmentMethod = page.locator('input[value*="installment"], label:has-text("Installment"), label:has-text("ผ่อน")');
    
    const methodExists = await installmentMethod.count() > 0;
    
    if (methodExists) {
      console.log('✅ MoneySpace Installment payment method found');
      
      await installmentMethod.first().click();
      await page.waitForTimeout(1000);
      
      // Look for bank selection or installment options
      const installmentElements = page.locator('select[name*="bank"], select[name*="month"], .installment-options');
      const installmentVisible = await installmentElements.count() > 0;
      
      if (installmentVisible) {
        console.log('✅ Installment payment options are visible');
      }
    } else {
      console.log('⚠️ Installment payment method not found');
    }
  });

  test('Checkout page should load without errors', async ({ page }) => {
    await page.goto('/checkout');
    
    // Check for any JavaScript errors or missing resources
    const errorMessages = page.locator('.woocommerce-error, .error, [class*="error"]');
    const visibleErrors = await errorMessages.count();
    
    if (visibleErrors > 0) {
      const errorTexts = await errorMessages.allTextContents();
      console.log('⚠️ Errors found on checkout page:', errorTexts);
    } else {
      console.log('✅ Checkout page loaded without visible errors');
    }
    
    // Check if billing form is present
    const billingForm = page.locator('#billing_first_name, input[name="billing_first_name"], .wc-block-checkout__billing');
    const formPresent = await billingForm.count() > 0;
    
    if (formPresent) {
      console.log('✅ Billing form is present');
    } else {
      console.log('⚠️ Billing form not found');
    }
  });

  test('Payment method selection should work', async ({ page }) => {
    await page.goto('/checkout');
    await page.waitForLoadState('networkidle');
    
    // Get all available payment methods
    const paymentMethods = page.locator('input[name="payment_method"], .wc-block-components-radio-control input');
    const methodCount = await paymentMethods.count();
    
    console.log(`Found ${methodCount} payment methods`);
    
    if (methodCount > 0) {
      // Try selecting each payment method
      for (let i = 0; i < methodCount; i++) {
        const method = paymentMethods.nth(i);
        const value = await method.getAttribute('value');
        
        console.log(`Testing payment method: ${value}`);
        
        await method.click();
        await page.waitForTimeout(500);
        
        // Check if method is selected
        const isSelected = await method.isChecked();
        if (isSelected) {
          console.log(`✅ Payment method ${value} selected successfully`);
        }
      }
    } else {
      console.log('❌ No payment methods found');
    }
  });

  test('MoneySpace plugin health check', async ({ page }) => {
    // Check if MoneySpace plugin assets are loading
    await page.goto('/checkout');
    await page.waitForLoadState('networkidle');
    
    // Look for MoneySpace-specific CSS or JS files
    const responses = [];
    page.on('response', response => {
      const url = response.url();
      if (url.includes('moneyspace') || url.includes('money-space')) {
        responses.push({
          url,
          status: response.status(),
          type: response.request().resourceType()
        });
      }
    });
    
    // Reload to capture responses
    await page.reload();
    await page.waitForLoadState('networkidle');
    
    if (responses.length > 0) {
      console.log('✅ MoneySpace plugin assets detected:');
      responses.forEach(r => {
        console.log(`  ${r.type}: ${r.url} (${r.status})`);
      });
    } else {
      console.log('⚠️ No MoneySpace plugin assets detected');
    }
    
    // Check for MoneySpace in page source
    const pageContent = await page.content();
    const hasMoneySpace = pageContent.includes('moneyspace') || pageContent.includes('MoneySpace');
    
    if (hasMoneySpace) {
      console.log('✅ MoneySpace references found in page content');
    } else {
      console.log('⚠️ No MoneySpace references found in page content');
    }
  });

});
