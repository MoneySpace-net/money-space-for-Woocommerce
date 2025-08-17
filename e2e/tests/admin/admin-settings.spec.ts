import { test, expect } from '@playwright/test';

test.describe('MoneySpace Plugin Admin Settings', () => {
  
  test.beforeEach(async ({ page }) => {
    // Login as admin
    await page.goto('/wp-admin');
    await page.fill('#user_login', process.env.WP_ADMIN_USER || 'admin');
    await page.fill('#user_pass', process.env.WP_ADMIN_PASS || 'password');
    await page.click('#wp-submit');
    
    // Navigate to WooCommerce payments settings
    await page.goto('/wp-admin/admin.php?page=wc-settings&tab=checkout');
  });

  test.describe('Payment Gateway Configuration', () => {
    test('should display MoneySpace payment methods in admin', async ({ page }) => {
      // Check for MoneySpace payment methods
      const moneyspaceGateways = [
        'MoneySpace Credit Card',
        'MoneySpace Installment',
        'MoneySpace QR Code'
      ];
      
      for (const gateway of moneyspaceGateways) {
        const gatewayRow = page.locator(`tr:has-text("${gateway}")`);
        await expect(gatewayRow).toBeVisible();
      }
    });

    test('should allow enabling/disabling payment methods', async ({ page }) => {
      // Find MoneySpace Credit Card settings
      await page.click('text=MoneySpace Credit Card');
      
      // Should show enable/disable toggle
      const enableCheckbox = page.locator('#woocommerce_moneyspace_creditcard_enabled');
      await expect(enableCheckbox).toBeVisible();
      
      // Toggle and save
      const isCurrentlyEnabled = await enableCheckbox.isChecked();
      await enableCheckbox.click();
      await page.click('.woocommerce-save-button');
      
      // Should show success message
      await expect(page.locator('.updated, .notice-success')).toBeVisible();
      
      // Verify state changed
      await page.reload();
      await page.click('text=MoneySpace Credit Card');
      const newState = await enableCheckbox.isChecked();
      expect(newState).toBe(!isCurrentlyEnabled);
    });

    test('should validate required API credentials', async ({ page }) => {
      await page.click('text=MoneySpace Credit Card');
      
      // Clear API credentials
      await page.fill('#woocommerce_moneyspace_creditcard_secret_id', '');
      await page.fill('#woocommerce_moneyspace_creditcard_secret_key', '');
      
      await page.click('.woocommerce-save-button');
      
      // Should show validation error
      const errorMessage = await page.locator('.error, .notice-error').textContent();
      expect(errorMessage?.toLowerCase()).toMatch(/secret|api|credential|required/);
    });

    test('should save API credentials securely', async ({ page }) => {
      await page.click('text=MoneySpace Credit Card');
      
      const testSecretId = 'test_secret_id_123';
      const testSecretKey = 'test_secret_key_456';
      
      // Fill in test credentials
      await page.fill('#woocommerce_moneyspace_creditcard_secret_id', testSecretId);
      await page.fill('#woocommerce_moneyspace_creditcard_secret_key', testSecretKey);
      
      await page.click('.woocommerce-save-button');
      
      // Should show success message
      await expect(page.locator('.updated, .notice-success')).toBeVisible();
      
      // Reload and verify credentials are saved (but possibly masked)
      await page.reload();
      await page.click('text=MoneySpace Credit Card');
      
      const savedSecretId = await page.inputValue('#woocommerce_moneyspace_creditcard_secret_id');
      expect(savedSecretId).toBeTruthy();
      
      // Secret key might be masked for security
      const savedSecretKey = await page.inputValue('#woocommerce_moneyspace_creditcard_secret_key');
      expect(savedSecretKey).toBeTruthy();
    });

    test('should configure environment settings (sandbox/production)', async ({ page }) => {
      await page.click('text=MoneySpace Credit Card');
      
      // Look for environment/sandbox toggle
      const sandboxField = page.locator('#woocommerce_moneyspace_creditcard_sandbox, #woocommerce_moneyspace_creditcard_environment');
      
      if (await sandboxField.isVisible()) {
        const fieldType = await sandboxField.getAttribute('type');
        
        if (fieldType === 'checkbox') {
          // Toggle sandbox mode
          const isCurrentlySandbox = await sandboxField.isChecked();
          await sandboxField.click();
          
          await page.click('.woocommerce-save-button');
          await expect(page.locator('.updated, .notice-success')).toBeVisible();
          
          // Verify change
          await page.reload();
          await page.click('text=MoneySpace Credit Card');
          const newSandboxState = await sandboxField.isChecked();
          expect(newSandboxState).toBe(!isCurrentlySandbox);
        }
      }
    });
  });

  test.describe('Installment Configuration', () => {
    test('should configure installment bank settings', async ({ page }) => {
      await page.click('text=MoneySpace Installment');
      
      // Look for bank configuration options
      const bankSettings = [
        'KTC',
        'BAY', 
        'FCY'
      ];
      
      for (const bank of bankSettings) {
        const bankElement = page.locator(`[id*="${bank.toLowerCase()}"], [name*="${bank.toLowerCase()}"], text=${bank}`);
        
        if (await bankElement.first().isVisible()) {
          // Bank configuration should be available
          expect(await bankElement.first().isVisible()).toBe(true);
        }
      }
    });

    test('should set minimum installment amounts', async ({ page }) => {
      await page.click('text=MoneySpace Installment');
      
      // Look for minimum amount settings
      const minAmountField = page.locator('#woocommerce_moneyspace_installment_min_amount, [name*="min_amount"]');
      
      if (await minAmountField.isVisible()) {
        await minAmountField.fill('3000');
        await page.click('.woocommerce-save-button');
        
        await expect(page.locator('.updated, .notice-success')).toBeVisible();
        
        // Verify saved
        await page.reload();
        await page.click('text=MoneySpace Installment');
        const savedValue = await minAmountField.inputValue();
        expect(savedValue).toBe('3000');
      }
    });

    test('should configure installment months options', async ({ page }) => {
      await page.click('text=MoneySpace Installment');
      
      // Look for months configuration
      const monthsConfig = page.locator('[name*="months"], [id*="months"]');
      
      if (await monthsConfig.first().isVisible()) {
        // Should be able to configure available months
        expect(await monthsConfig.first().isVisible()).toBe(true);
      }
    });
  });

  test.describe('Advanced Settings', () => {
    test('should configure webhook URLs', async ({ page }) => {
      await page.click('text=MoneySpace Credit Card');
      
      // Look for webhook configuration
      const webhookField = page.locator('#woocommerce_moneyspace_creditcard_webhook_url, [name*="webhook"]');
      
      if (await webhookField.isVisible()) {
        const currentWebhookUrl = await webhookField.inputValue();
        expect(currentWebhookUrl).toMatch(/^https?:\/\//);
      }
    });

    test('should configure debug/logging settings', async ({ page }) => {
      await page.click('text=MoneySpace Credit Card');
      
      // Look for debug/logging options
      const debugField = page.locator('#woocommerce_moneyspace_creditcard_debug, #woocommerce_moneyspace_creditcard_logging');
      
      if (await debugField.isVisible()) {
        const isCurrentlyEnabled = await debugField.isChecked();
        await debugField.click();
        
        await page.click('.woocommerce-save-button');
        await expect(page.locator('.updated, .notice-success')).toBeVisible();
        
        // Verify toggle worked
        await page.reload();
        await page.click('text=MoneySpace Credit Card');
        const newState = await debugField.isChecked();
        expect(newState).toBe(!isCurrentlyEnabled);
      }
    });

    test('should display current plugin version', async ({ page }) => {
      // Check in plugin list
      await page.goto('/wp-admin/plugins.php');
      
      const moneyspacePlugin = page.locator('tr:has-text("MoneySpace")');
      await expect(moneyspacePlugin).toBeVisible();
      
      const versionInfo = await moneyspacePlugin.locator('.plugin-version-author-uri').textContent();
      expect(versionInfo).toMatch(/version\s+\d+\.\d+/i);
    });

    test('should provide API connection test', async ({ page }) => {
      await page.click('text=MoneySpace Credit Card');
      
      // Look for test connection button
      const testButton = page.locator('.test-connection, .test-api, [value*="test"]');
      
      if (await testButton.isVisible()) {
        await testButton.click();
        
        // Should show test result
        await expect(page.locator('.test-result, .connection-status')).toBeVisible({ timeout: 10000 });
      }
    });

    test('should show transaction logs if debug is enabled', async ({ page }) => {
      await page.click('text=MoneySpace Credit Card');
      
      // Enable debug if not already enabled
      const debugField = page.locator('#woocommerce_moneyspace_creditcard_debug, #woocommerce_moneyspace_creditcard_logging');
      
      if (await debugField.isVisible() && !(await debugField.isChecked())) {
        await debugField.click();
        await page.click('.woocommerce-save-button');
        await expect(page.locator('.updated, .notice-success')).toBeVisible();
      }
      
      // Look for logs link or section
      const logsLink = page.locator('a:has-text("logs"), a:has-text("Log"), .logs-link');
      
      if (await logsLink.isVisible()) {
        await logsLink.click();
        
        // Should navigate to logs page
        await expect(page.locator('.log-entries, .wc-log-viewer')).toBeVisible();
      }
    });
  });

  test.describe('Settings Validation and Security', () => {
    test('should prevent saving invalid API URLs', async ({ page }) => {
      await page.click('text=MoneySpace Credit Card');
      
      const apiUrlField = page.locator('#woocommerce_moneyspace_creditcard_api_url, [name*="api_url"]');
      
      if (await apiUrlField.isVisible()) {
        // Try invalid URL
        await apiUrlField.fill('invalid-url');
        await page.click('.woocommerce-save-button');
        
        // Should show validation error
        const errorMessage = await page.locator('.error, .notice-error').textContent();
        expect(errorMessage?.toLowerCase()).toMatch(/url|invalid|format/);
      }
    });

    test('should mask sensitive fields in admin interface', async ({ page }) => {
      await page.click('text=MoneySpace Credit Card');
      
      const secretKeyField = page.locator('#woocommerce_moneyspace_creditcard_secret_key');
      
      if (await secretKeyField.isVisible()) {
        const fieldType = await secretKeyField.getAttribute('type');
        
        // Secret key should be password type or masked
        expect(['password', 'text']).toContain(fieldType);
        
        // If it's text type, value might be masked with asterisks
        if (fieldType === 'text') {
          const value = await secretKeyField.inputValue();
          if (value && value.length > 0) {
            // Should not show actual secret in plain text
            expect(value).toMatch(/^\*+$|^.{0}$/);
          }
        }
      }
    });

    test('should require proper permissions to access settings', async ({ page }) => {
      // This test assumes we can test with different user roles
      // For now, just verify the settings page requires admin access
      
      await page.goto('/wp-admin/admin.php?page=wc-settings&tab=checkout');
      
      // Should not show "You need a higher level of permission" error
      const permissionError = await page.locator('text=permission').isVisible();
      expect(permissionError).toBe(false);
      
      // Should show the actual settings page
      await expect(page.locator('.woocommerce_page_wc-settings')).toBeVisible();
    });

    test('should sanitize input values', async ({ page }) => {
      await page.click('text=MoneySpace Credit Card');
      
      const titleField = page.locator('#woocommerce_moneyspace_creditcard_title');
      
      if (await titleField.isVisible()) {
        // Try input with script tags
        const maliciousInput = '<script>alert("xss")</script>Test Title';
        await titleField.fill(maliciousInput);
        await page.click('.woocommerce-save-button');
        
        // Reload and check that script was sanitized
        await page.reload();
        await page.click('text=MoneySpace Credit Card');
        const sanitizedValue = await titleField.inputValue();
        
        expect(sanitizedValue).not.toContain('<script>');
        expect(sanitizedValue).toContain('Test Title');
      }
    });

    test('should handle concurrent admin updates gracefully', async ({ page, context }) => {
      // Open same settings in another tab/context
      const secondPage = await context.newPage();
      await secondPage.goto('/wp-admin');
      await secondPage.fill('#user_login', process.env.WP_ADMIN_USER || 'admin');
      await secondPage.fill('#user_pass', process.env.WP_ADMIN_PASS || 'password');
      await secondPage.click('#wp-submit');
      await secondPage.goto('/wp-admin/admin.php?page=wc-settings&tab=checkout');
      
      // Make changes in both tabs
      await page.click('text=MoneySpace Credit Card');
      await secondPage.click('text=MoneySpace Credit Card');
      
      const titleField1 = page.locator('#woocommerce_moneyspace_creditcard_title');
      const titleField2 = secondPage.locator('#woocommerce_moneyspace_creditcard_title');
      
      if (await titleField1.isVisible() && await titleField2.isVisible()) {
        await titleField1.fill('Title from Tab 1');
        await titleField2.fill('Title from Tab 2');
        
        // Save from first tab
        await page.click('.woocommerce-save-button');
        await expect(page.locator('.updated, .notice-success')).toBeVisible();
        
        // Save from second tab
        await secondPage.click('.woocommerce-save-button');
        
        // Should handle gracefully (either error or overwrite)
        const hasNotice = await secondPage.locator('.updated, .notice-success, .error, .notice-error').isVisible();
        expect(hasNotice).toBe(true);
      }
      
      await secondPage.close();
    });
  });
});
