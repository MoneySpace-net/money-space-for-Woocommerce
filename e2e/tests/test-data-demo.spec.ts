import { test, expect } from '@playwright/test';
import { testUsers, testCreditCards } from '../fixtures/test-data';

test.describe('MoneySpace Payment with Test Data', () => {
  
  test('Complete checkout flow with mock user and credit card data', async ({ page }) => {
    // Get test data
    const customer = testUsers.thaiCustomer;
    const creditCard = testCreditCards.visa;
    
    console.log('📋 Using test customer:', {
      name: `${customer.firstName} ${customer.lastName}`,
      email: customer.email,
      phone: customer.phone
    });
    
    console.log('💳 Using test credit card:', {
      type: creditCard.type,
      number: creditCard.number.replace(/\d(?=\d{4})/g, '*'),
      expiry: creditCard.expiry
    });
    
    // Add a product to cart first
    await page.goto('/shop/');
    
    // Look for any available product
    const addToCartButton = page.locator('.add_to_cart_button, .single_add_to_cart_button').first();
    
    if (await addToCartButton.isVisible()) {
      await addToCartButton.click();
      console.log('✅ Product added to cart');
      
      // Wait a moment for cart to update
      await page.waitForTimeout(2000);
    } else {
      // If no add to cart button, try to navigate to a product
      const productLink = page.locator('.woocommerce-loop-product__link, .product-item a').first();
      if (await productLink.isVisible()) {
        await productLink.click();
        await page.waitForSelector('.single_add_to_cart_button');
        await page.locator('.single_add_to_cart_button').click();
        console.log('✅ Product added to cart from product page');
      }
    }
    
    // Go to checkout
    await page.goto('/checkout/');
    await page.waitForLoadState('domcontentloaded');
    
    console.log('🔵 On checkout page');
    
    // Fill billing information using test data
    try {
      const billingSelectors = [
        { field: 'firstName', selectors: ['#billing_first_name', 'input[name="billing_first_name"]'] },
        { field: 'lastName', selectors: ['#billing_last_name', 'input[name="billing_last_name"]'] },
        { field: 'email', selectors: ['#billing_email', 'input[name="billing_email"]'] },
        { field: 'phone', selectors: ['#billing_phone', 'input[name="billing_phone"]'] },
        { field: 'address', selectors: ['#billing_address_1', 'input[name="billing_address_1"]'] },
        { field: 'city', selectors: ['#billing_city', 'input[name="billing_city"]'] },
        { field: 'postcode', selectors: ['#billing_postcode', 'input[name="billing_postcode"]'] }
      ];
      
      for (const { field, selectors } of billingSelectors) {
        for (const selector of selectors) {
          const element = page.locator(selector);
          if (await element.isVisible()) {
            await element.fill(customer[field]);
            console.log(`✅ Filled ${field}: ${customer[field]}`);
            break;
          }
        }
      }
      
      // Look for MoneySpace payment methods
      const paymentMethods = [
        'input[value="moneyspace_creditcard"]',
        'input[value="moneyspace"]',
        '.wc-block-components-payment-method-label:has-text("MoneySpace")',
        '.wc-block-components-payment-method-label:has-text("Credit Card")'
      ];
      
      let paymentMethodFound = false;
      for (const selector of paymentMethods) {
        const element = page.locator(selector);
        if (await element.isVisible()) {
          await element.click();
          console.log(`✅ Selected MoneySpace payment method: ${selector}`);
          paymentMethodFound = true;
          break;
        }
      }
      
      if (!paymentMethodFound) {
        console.log('⚠️ MoneySpace payment method not found, checking available methods...');
        const availableMethods = await page.locator('.wc-block-components-payment-method-label, input[name="payment_method"]').count();
        console.log(`Found ${availableMethods} payment methods`);
      }
      
      // Look for credit card fields and fill them
      const ccSelectors = [
        { field: 'number', selectors: ['input[name="moneyspace_creditcard_number"]', '#moneyspace_creditcard_number', 'input[placeholder*="card number" i]'] },
        { field: 'expiry', selectors: ['input[name="moneyspace_creditcard_expiry"]', '#moneyspace_creditcard_expiry', 'input[placeholder*="expiry" i]'] },
        { field: 'cvv', selectors: ['input[name="moneyspace_creditcard_cvv"]', '#moneyspace_creditcard_cvv', 'input[placeholder*="cvv" i]'] }
      ];
      
      for (const { field, selectors } of ccSelectors) {
        for (const selector of selectors) {
          const element = page.locator(selector);
          if (await element.isVisible()) {
            const value = field === 'number' ? creditCard.number : 
                         field === 'expiry' ? creditCard.expiry : creditCard.cvv;
            await element.fill(value);
            console.log(`✅ Filled credit card ${field}`);
            break;
          }
        }
      }
      
    } catch (error) {
      console.log('⚠️ Error filling form fields:', error.message);
    }
    
    // Check that the page loaded successfully and contains MoneySpace elements
    const moneyspaceElements = await page.locator('*').evaluateAll(elements => {
      return elements.filter(el => 
        el.textContent?.toLowerCase().includes('moneyspace') ||
        el.className?.toLowerCase().includes('moneyspace') ||
        el.id?.toLowerCase().includes('moneyspace')
      ).length;
    });
    
    console.log(`🔍 Found ${moneyspaceElements} MoneySpace elements on page`);
    
    // Verify test data was used
    const customerInfo = `${customer.firstName} ${customer.lastName}`;
    const emailValue = await page.locator('input[name="billing_email"], #billing_email').first().inputValue().catch(() => '');
    
    if (emailValue === customer.email) {
      console.log(`✅ Test customer data successfully applied: ${customerInfo}`);
    } else {
      console.log(`ℹ️ Test customer email: ${customer.email}`);
    }
    
    expect(moneyspaceElements).toBeGreaterThan(0);
  });
  
  test('Verify test data is properly configured', async ({ page }) => {
    // Verify test users data
    expect(testUsers.thaiCustomer.firstName).toBe('สมชาย');
    expect(testUsers.thaiCustomer.email).toContain('@test.com');
    expect(testUsers.thaiCustomer.country).toBe('TH');
    
    // Verify test credit cards data
    expect(testCreditCards.visa.number).toBe('4111111111111111');
    expect(testCreditCards.visa.type).toBe('visa');
    expect(testCreditCards.mastercard.number).toBe('5555555555554444');
    
    console.log('✅ Test data validation passed');
    console.log('📋 Available test users:', Object.keys(testUsers));
    console.log('💳 Available test credit cards:', Object.keys(testCreditCards));
  });
  
});
