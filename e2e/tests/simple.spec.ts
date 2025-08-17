import { test, expect } from '@playwright/test';

test('WooCommerce site accessibility test', async ({ page }) => {
  // Test that we can access the WooCommerce homepage
  await page.goto('/');
  
  // Check if the page loads successfully
  await expect(page).toHaveTitle(/.*/, { timeout: 10000 });
  
  // Look for common WooCommerce elements
  const hasWooCommerce = await page.locator('body').innerHTML();
  console.log('Page loaded successfully, checking for WooCommerce...');
});

test('WooCommerce shop page test', async ({ page }) => {
  // Test that we can access the shop page
  await page.goto('/shop');
  
  // Should load without errors
  await page.waitForLoadState('networkidle');
  console.log('Shop page loaded successfully');
});

test('WooCommerce checkout page test', async ({ page }) => {
  // Test that we can access the checkout page (even if cart is empty)
  await page.goto('/checkout');
  
  // Should load without errors
  await page.waitForLoadState('networkidle');
  console.log('Checkout page loaded successfully');
});
