import { test, expect } from '@playwright/test';

test.describe('MoneySpace Plugin Status Check', () => {
  
  test('Check if MoneySpace plugin is active', async ({ page }) => {
    // Navigate to WordPress admin login (you may need to log in manually)
    await page.goto('/wp-admin');
    
    // Check if we can access the admin area
    const isLoggedIn = await page.locator('body.wp-admin').isVisible();
    
    if (!isLoggedIn) {
      console.log('Not logged into WordPress admin - manual login required');
      console.log('Please navigate to http://localhost:9000/wp-admin and ensure MoneySpace plugin is activated');
      return;
    }
    
    // Navigate to plugins page
    await page.goto('/wp-admin/plugins.php');
    
    // Look for MoneySpace plugin
    const moneyspacePlugin = page.locator('tr[data-slug*="moneyspace"], tr:has-text("MoneySpace"), tr:has-text("money-space")');
    
    if (await moneyspacePlugin.isVisible()) {
      const isActive = await moneyspacePlugin.locator('.activate, .deactivate').first().textContent();
      console.log(`MoneySpace plugin found - Status: ${isActive}`);
    } else {
      console.log('MoneySpace plugin not found in plugins list');
    }
  });

  test('Check WooCommerce payment settings', async ({ page }) => {
    await page.goto('/wp-admin');
    
    const isLoggedIn = await page.locator('body.wp-admin').isVisible();
    
    if (!isLoggedIn) {
      console.log('Not logged into WordPress admin - cannot check payment settings');
      return;
    }
    
    // Navigate to WooCommerce payment settings
    await page.goto('/wp-admin/admin.php?page=wc-settings&tab=checkout');
    
    // Look for MoneySpace payment methods
    const paymentMethods = page.locator('table.wc_gateways tbody tr');
    const methodCount = await paymentMethods.count();
    
    console.log(`Found ${methodCount} payment methods in WooCommerce settings`);
    
    for (let i = 0; i < methodCount; i++) {
      const method = paymentMethods.nth(i);
      const name = await method.locator('.wc-payment-gateway-method-name').textContent();
      const status = await method.locator('.wc-payment-gateway-method-toggle-enabled').isVisible() ? 'Enabled' : 'Disabled';
      
      if (name && name.toLowerCase().includes('moneyspace')) {
        console.log(`MoneySpace method found: ${name} - ${status}`);
      }
    }
  });

  test('Check if WooCommerce is using classic or block checkout', async ({ page }) => {
    await page.goto('/checkout');
    await page.waitForLoadState('networkidle');
    
    // Check for block checkout
    const isBlockCheckout = await page.locator('.wp-block-woocommerce-checkout').isVisible();
    const hasClassicForm = await page.locator('#billing_first_name').isVisible();
    
    if (isBlockCheckout) {
      console.log('✅ Using WooCommerce Block Checkout');
      
      // Check for block-specific payment methods
      const blockPaymentMethods = page.locator('.wc-block-components-payment-method-options .wc-block-components-payment-method-label');
      const blockMethodCount = await blockPaymentMethods.count();
      console.log(`Found ${blockMethodCount} payment methods in blocks checkout`);
      
      if (blockMethodCount > 0) {
        const methods = await blockPaymentMethods.allTextContents();
        console.log('Available payment methods:', methods);
      }
    } else if (hasClassicForm) {
      console.log('✅ Using Classic WooCommerce Checkout');
      
      // Check for classic payment methods
      const classicPaymentMethods = page.locator('ul.wc_payment_methods li label');
      const classicMethodCount = await classicPaymentMethods.count();
      console.log(`Found ${classicMethodCount} payment methods in classic checkout`);
      
      if (classicMethodCount > 0) {
        const methods = await classicPaymentMethods.allTextContents();
        console.log('Available payment methods:', methods);
      }
    } else {
      console.log('⚠️ Checkout type not determined - may need products in cart');
    }
    
    // Take a screenshot for debugging
    await page.screenshot({ path: 'test-results/checkout-type-debug.png', fullPage: true });
  });

  test('Add a simple product and test checkout with item', async ({ page }) => {
    // First, let's try to create a simple test product if logged in as admin
    await page.goto('/wp-admin');
    
    const isLoggedIn = await page.locator('body.wp-admin').isVisible();
    
    if (isLoggedIn) {
      console.log('Creating a test product for checkout testing...');
      
      // Navigate to add new product
      await page.goto('/wp-admin/post-new.php?post_type=product');
      
      // Fill basic product details
      await page.locator('#title').fill('Test Product for E2E');
      await page.locator('#_regular_price').fill('100');
      await page.locator('#_virtual').check(); // Make it virtual to avoid shipping
      
      // Publish the product
      await page.locator('#publish').click();
      await page.waitForTimeout(2000);
      
      console.log('Test product created');
      
      // Get the product URL
      const viewLink = page.locator('.notice-success a[href*="?p="]');
      if (await viewLink.isVisible()) {
        const productUrl = await viewLink.getAttribute('href');
        console.log(`Product URL: ${productUrl}`);
        
        // Visit the product and add to cart
        await page.goto(productUrl);
        await page.waitForLoadState('networkidle');
        
        const addToCartButton = page.locator('.single_add_to_cart_button');
        if (await addToCartButton.isVisible()) {
          await addToCartButton.click();
          await page.waitForTimeout(2000);
          
          console.log('Product added to cart, testing checkout...');
          
          // Now go to checkout
          await page.goto('/checkout');
          await page.waitForLoadState('networkidle');
          
          // Check payment methods with product in cart
          const paymentMethods = page.locator('input[name="payment_method"], .wc-block-components-payment-method-options input');
          const methodCount = await paymentMethods.count();
          
          console.log(`With product in cart, found ${methodCount} payment methods`);
          
          if (methodCount > 0) {
            for (let i = 0; i < methodCount; i++) {
              const method = paymentMethods.nth(i);
              const value = await method.getAttribute('value');
              console.log(`Payment method ${i + 1}: ${value}`);
            }
          }
          
          // Take screenshot with product in cart
          await page.screenshot({ path: 'test-results/checkout-with-product.png', fullPage: true });
        }
      }
    } else {
      console.log('Not logged in - cannot create test product');
    }
  });

});
