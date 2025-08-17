import { test, expect } from '@playwright/test';

test.describe('MoneySpace Payment Gateway with Products', () => {
  
  test('Add product to cart and test MoneySpace payment methods', async ({ page }) => {
    // Go to shop and add iPad Pro to cart
    await page.goto('/shop');
    await page.waitForLoadState('networkidle');
    
    // Click on "Add to cart" for iPad Pro
    const addToCartButton = page.locator('a[data-product_sku="ipad-pro"]').first();
    await addToCartButton.click();
    
    // Wait for cart to update
    await page.waitForTimeout(3000);
    
    console.log('✅ iPad Pro added to cart');
    
    // Navigate to checkout
    await page.goto('/checkout');
    await page.waitForLoadState('networkidle');
    
    // Check what type of checkout we have
    const isBlockCheckout = await page.locator('.wp-block-woocommerce-checkout').isVisible();
    const hasClassicForm = await page.locator('#billing_first_name').isVisible();
    
    if (isBlockCheckout) {
      console.log('🔵 Using WooCommerce Block Checkout');
      
      // Look for payment methods in blocks checkout
      await page.waitForSelector('.wc-block-components-payment-method-options', { timeout: 10000 });
      
      const paymentMethodOptions = page.locator('.wc-block-components-payment-method-options .wc-block-components-payment-method-label');
      const methodCount = await paymentMethodOptions.count();
      
      console.log(`Found ${methodCount} payment methods in block checkout`);
      
      if (methodCount > 0) {
        const methods = await paymentMethodOptions.allTextContents();
        console.log('Available payment methods:', methods);
        
        // Check for MoneySpace methods specifically
        const moneyspaceMethods = methods.filter(method => 
          method.toLowerCase().includes('moneyspace') || 
          method.toLowerCase().includes('credit card') ||
          method.toLowerCase().includes('installment') ||
          method.toLowerCase().includes('qr')
        );
        
        if (moneyspaceMethods.length > 0) {
          console.log('✅ MoneySpace payment methods found:', moneyspaceMethods);
          
          // Try to select the first MoneySpace method
          for (let i = 0; i < methodCount; i++) {
            const methodText = await paymentMethodOptions.nth(i).textContent();
            if (methodText && (methodText.toLowerCase().includes('moneyspace') || methodText.toLowerCase().includes('credit'))) {
              await paymentMethodOptions.nth(i).click();
              console.log(`Selected payment method: ${methodText}`);
              await page.waitForTimeout(2000);
              
              // Look for payment form that appears
              const paymentForms = page.locator('.wc-block-checkout__payment-method, .payment_method_content');
              const formCount = await paymentForms.count();
              console.log(`Payment forms visible: ${formCount}`);
              
              break;
            }
          }
        } else {
          console.log('⚠️ No MoneySpace payment methods found in block checkout');
        }
      }
      
    } else if (hasClassicForm) {
      console.log('🔵 Using Classic WooCommerce Checkout');
      
      // Look for payment methods in classic checkout
      const paymentMethods = page.locator('ul.wc_payment_methods li');
      const methodCount = await paymentMethods.count();
      
      console.log(`Found ${methodCount} payment methods in classic checkout`);
      
      if (methodCount > 0) {
        for (let i = 0; i < methodCount; i++) {
          const method = paymentMethods.nth(i);
          const label = await method.locator('label').textContent();
          const input = method.locator('input[type="radio"]');
          const value = await input.getAttribute('value');
          
          console.log(`Payment method ${i + 1}: ${label} (${value})`);
          
          if (value && (value.includes('moneyspace') || label?.toLowerCase().includes('moneyspace'))) {
            console.log(`✅ Found MoneySpace method: ${label}`);
            
            // Select this payment method
            await input.click();
            await page.waitForTimeout(1000);
            
            // Look for payment form
            const paymentBox = method.locator('.payment_box');
            const isVisible = await paymentBox.isVisible();
            console.log(`Payment form visible: ${isVisible}`);
          }
        }
      }
    } else {
      console.log('⚠️ Cannot determine checkout type');
    }
    
    // Take screenshot for debugging
    await page.screenshot({ path: 'test-results/checkout-with-ipad-pro.png', fullPage: true });
  });

  test('Test checkout with iPhone 16 Pro Max', async ({ page }) => {
    // Go to shop and add iPhone to cart
    await page.goto('/shop');
    await page.waitForLoadState('networkidle');
    
    // Click on "Add to cart" for iPhone 16 Pro Max
    const addToCartButton = page.locator('a[data-product_sku="iphone-16-pro-max"]').first();
    await addToCartButton.click();
    
    // Wait for cart to update
    await page.waitForTimeout(3000);
    
    console.log('✅ iPhone 16 Pro Max added to cart');
    
    // Navigate to checkout
    await page.goto('/checkout');
    await page.waitForLoadState('networkidle');
    
    // Check for billing form
    const billingFormSelectors = [
      '#billing_first_name',
      'input[name="billing_first_name"]',
      '.wc-block-components-address-form',
      '.wc-block-checkout__billing'
    ];
    
    let formFound = false;
    for (const selector of billingFormSelectors) {
      if (await page.locator(selector).isVisible()) {
        console.log(`✅ Billing form found: ${selector}`);
        formFound = true;
        break;
      }
    }
    
    if (!formFound) {
      console.log('⚠️ No billing form found');
    }
    
    // Check cart total
    const cartTotalSelectors = [
      '.order-total .amount',
      '.wc-block-components-totals-footer-item .wc-block-formatted-money-amount',
      '.cart-subtotal .amount',
      '.woocommerce-Price-amount'
    ];
    
    for (const selector of cartTotalSelectors) {
      const totalElement = page.locator(selector);
      if (await totalElement.isVisible()) {
        const total = await totalElement.textContent();
        console.log(`💰 Cart total: ${total}`);
        break;
      }
    }
    
    // Look for any MoneySpace related text on the page
    const pageContent = await page.content();
    const hasMoneySpaceContent = pageContent.toLowerCase().includes('moneyspace');
    console.log(`MoneySpace content found on page: ${hasMoneySpaceContent}`);
    
    // Look for specific MoneySpace elements
    const moneyspaceElements = page.locator('[class*="moneyspace"], [id*="moneyspace"], [name*="moneyspace"]');
    const elementCount = await moneyspaceElements.count();
    console.log(`MoneySpace elements found: ${elementCount}`);
    
    if (elementCount > 0) {
      for (let i = 0; i < elementCount; i++) {
        const element = moneyspaceElements.nth(i);
        const tagName = await element.evaluate(el => el.tagName);
        const className = await element.getAttribute('class');
        const id = await element.getAttribute('id');
        console.log(`Element ${i + 1}: ${tagName} class="${className}" id="${id}"`);
      }
    }
    
    // Take screenshot
    await page.screenshot({ path: 'test-results/checkout-with-iphone.png', fullPage: true });
  });

  test('Check MoneySpace plugin files are loading', async ({ page }) => {
    // Track network requests
    const requests = [];
    page.on('request', request => {
      const url = request.url();
      if (url.includes('moneyspace') || url.includes('money-space')) {
        requests.push({
          url,
          method: request.method(),
          resourceType: request.resourceType()
        });
      }
    });
    
    const responses = [];
    page.on('response', response => {
      const url = response.url();
      if (url.includes('moneyspace') || url.includes('money-space')) {
        responses.push({
          url,
          status: response.status(),
          statusText: response.statusText()
        });
      }
    });
    
    // Add product and go to checkout
    await page.goto('/shop');
    await page.locator('a[data-product_sku="ipad-pro"]').first().click();
    await page.waitForTimeout(2000);
    
    await page.goto('/checkout');
    await page.waitForLoadState('networkidle');
    
    console.log(`🌐 MoneySpace requests: ${requests.length}`);
    requests.forEach((req, i) => {
      console.log(`  ${i + 1}. ${req.method} ${req.resourceType}: ${req.url}`);
    });
    
    console.log(`📦 MoneySpace responses: ${responses.length}`);
    responses.forEach((res, i) => {
      console.log(`  ${i + 1}. ${res.status} ${res.statusText}: ${res.url}`);
    });
    
    // Check if any JS errors occurred
    const jsErrors = [];
    page.on('pageerror', error => {
      jsErrors.push(error.message);
    });
    
    // Wait a bit to catch any delayed errors
    await page.waitForTimeout(3000);
    
    if (jsErrors.length > 0) {
      console.log('❌ JavaScript errors found:');
      jsErrors.forEach((error, i) => {
        console.log(`  ${i + 1}. ${error}`);
      });
    } else {
      console.log('✅ No JavaScript errors detected');
    }
  });

});
