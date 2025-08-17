const { Locator } = require('@playwright/test');

class CheckoutPage {
  constructor(page) {
    this.page = page;
    
    // Navigation
    this.checkoutUrl = '/checkout';
    
    // Billing form elements (WooCommerce standard and Blocks)
    this.billingFirstName = page.locator('#billing_first_name, input[name="billing_first_name"]').first();
    this.billingLastName = page.locator('#billing_last_name, input[name="billing_last_name"]').first();
    this.billingEmail = page.locator('#billing_email, input[name="billing_email"]').first();
    this.billingPhone = page.locator('#billing_phone, input[name="billing_phone"]').first();
    this.billingAddress = page.locator('#billing_address_1, input[name="billing_address_1"]').first();
    this.billingCity = page.locator('#billing_city, input[name="billing_city"]').first();
    this.billingPostcode = page.locator('#billing_postcode, input[name="billing_postcode"]').first();
    this.billingCountry = page.locator('#billing_country, select[name="billing_country"]').first();
    
    // Payment method radio buttons
    this.paymentMethodCreditCard = page.locator('input[value="moneyspace_creditcard"], input[value="moneyspace-creditcard"]');
    this.paymentMethodInstallment = page.locator('input[value="moneyspace_installment"], input[value="moneyspace-installment"]');
    this.paymentMethodQRCode = page.locator('input[value="moneyspace_qrcode"], input[value="moneyspace-qrcode"]');
    
    // Credit card form elements
    this.cardNumberInput = page.locator('#card_number, input[name="card_number"], [data-element-type="cardNumber"]').first();
    this.cardExpiryInput = page.locator('#card_expiry, input[name="card_expiry"], [data-element-type="cardExpiry"]').first();
    this.cardCvvInput = page.locator('#card_cvv, input[name="card_cvv"], [data-element-type="cardCvc"]').first();
    this.cardHolderInput = page.locator('#card_holder, input[name="card_holder"], [data-element-type="cardholderName"]').first();
    
    // Installment form elements
    this.bankSelect = page.locator('#selectbank, select[name="selectbank"]').first();
    this.ktcMonthsSelect = page.locator('#KTC_permonths, #ktc_permonths, select[name="KTC_permonths"]').first();
    this.bayMonthsSelect = page.locator('#BAY_permonths, #bay_permonths, select[name="BAY_permonths"]').first();
    this.fcyMonthsSelect = page.locator('#FCY_permonths, #fcy_permonths, select[name="FCY_permonths"]').first();
    
    // Common elements
    this.placeOrderButton = page.locator('#place_order, .wc-block-components-checkout-place-order-button, button[type="submit"]').first();
    this.orderTotal = page.locator('.order-total .amount, .wc-block-components-totals-footer-item .wc-block-formatted-money-amount').first();
    this.noticeContainer = page.locator('.woocommerce-notices-wrapper, .wc-block-components-notices').first();
  }

  /**
   * Navigate to checkout page
   */
  async goto() {
    await this.page.goto(this.checkoutUrl);
    await this.page.waitForLoadState('networkidle');
  }

  /**
   * Fill billing details form
   */
  async fillBillingDetails(details) {
    await this.billingFirstName.fill(details.firstName);
    await this.billingLastName.fill(details.lastName);
    await this.billingEmail.fill(details.email);
    await this.billingPhone.fill(details.phone);
    await this.billingAddress.fill(details.address);
    await this.billingCity.fill(details.city);
    await this.billingPostcode.fill(details.postcode);
    
    if (details.country && await this.billingCountry.isVisible()) {
      await this.billingCountry.selectOption(details.country);
    }
  }

  /**
   * Select payment method
   */
  async selectPaymentMethod(method) {
    const methodMap = {
      creditcard: this.paymentMethodCreditCard,
      installment: this.paymentMethodInstallment,
      qrcode: this.paymentMethodQRCode
    };
    
    const paymentMethod = methodMap[method];
    
    // Wait for payment method to be available
    await paymentMethod.waitFor({ state: 'visible', timeout: 10000 });
    
    // Click the payment method
    await paymentMethod.click();
    
    // Wait for form to update
    await this.page.waitForTimeout(500);
  }

  /**
   * Fill credit card details
   */
  async fillCreditCardDetails(details) {
    await this.cardNumberInput.fill(details.number);
    await this.cardExpiryInput.fill(details.expiry);
    await this.cardCvvInput.fill(details.cvv);
    await this.cardHolderInput.fill(details.holder);
  }

  /**
   * Select installment bank
   */
  async selectInstallmentBank(bank) {
    await this.bankSelect.selectOption(bank);
    await this.page.waitForTimeout(500); // Wait for months dropdown to update
  }

  /**
   * Select installment months
   */
  async selectInstallmentMonths(bank, months) {
    const monthsSelectors = {
      KTC: this.ktcMonthsSelect,
      BAY: this.bayMonthsSelect,
      FCY: this.fcyMonthsSelect
    };
    
    const monthsSelect = monthsSelectors[bank];
    await monthsSelect.selectOption(months);
  }

  /**
   * Place order
   */
  async placeOrder() {
    await this.placeOrderButton.click();
    await this.page.waitForTimeout(2000); // Wait for form submission
  }

  /**
   * Get cart total
   */
  async getCartTotal() {
    return await this.orderTotal.textContent() || '0';
  }

  /**
   * Get validation errors
   */
  async getValidationErrors() {
    const errorSelectors = [
      '.woocommerce-error',
      '.woocommerce-message',
      '.wc-block-components-validation-error',
      '.wc-block-components-notice-banner--error',
      '.error',
      '.field-error',
      '.invalid-feedback'
    ];
    
    const errors = [];
    
    for (const selector of errorSelectors) {
      const elements = await this.page.locator(selector).all();
      for (const element of elements) {
        if (await element.isVisible()) {
          const text = await element.textContent();
          if (text && text.trim()) {
            errors.push(text.trim());
          }
        }
      }
    }
    
    return errors;
  }

  /**
   * Check if credit card form is visible
   */
  async isCreditCardFormVisible() {
    return await this.cardNumberInput.isVisible();
  }

  /**
   * Check if installment form is visible
   */
  async isInstallmentFormVisible() {
    return await this.bankSelect.isVisible();
  }

  /**
   * Check if QR code form is visible
   */
  async isQRCodeFormVisible() {
    const qrElements = this.page.locator('.qr-form, .qr-code-form, .moneyspace-qr, .payment_method_moneyspace_qrcode .payment_box');
    return await qrElements.first().isVisible();
  }

  /**
   * Check if payment method is available
   */
  async isPaymentMethodAvailable(method) {
    const methodMap = {
      creditcard: this.paymentMethodCreditCard,
      installment: this.paymentMethodInstallment,
      qrcode: this.paymentMethodQRCode
    };
    
    return await methodMap[method].isVisible();
  }

  /**
   * Wait for notices to clear
   */
  async waitForNoticesClear() {
    await this.page.waitForFunction(() => {
      const notices = document.querySelectorAll('.woocommerce-error, .wc-block-components-notice-banner--error');
      return notices.length === 0;
    }, { timeout: 5000 }).catch(() => {
      // Ignore timeout - notices might persist
    });
  }

  /**
   * Check if minimum amount warning is visible
   */
  async isMinimumAmountWarningVisible() {
    const warningSelectors = [
      '.minimum-amount-warning',
      '.amount-error',
      '[data-testid="minimum-amount-error"]',
      '.woocommerce-error:has-text("minimum")',
      '.wc-block-components-notice-banner:has-text("3000")'
    ];
    
    for (const selector of warningSelectors) {
      try {
        const element = this.page.locator(selector).first();
        if (await element.isVisible()) {
          return true;
        }
      } catch {
        // Continue checking other selectors
      }
    }
    
    return false;
  }

  /**
   * Get all available installment months for a bank
   */
  async getAvailableMonths(bank) {
    const monthsSelectors = {
      KTC: this.ktcMonthsSelect,
      BAY: this.bayMonthsSelect,
      FCY: this.fcyMonthsSelect
    };
    
    const monthsSelect = monthsSelectors[bank];
    
    if (!(await monthsSelect.isVisible())) {
      return [];
    }
    
    const options = await monthsSelect.locator('option').all();
    const months = [];
    
    for (const option of options) {
      const value = await option.getAttribute('value');
      if (value && value !== '') {
        months.push(value);
      }
    }
    
    return months;
  }

  /**
   * Check if installment amount meets minimum requirements
   */
  async checkInstallmentMinimum(bank, months) {
    const cartTotal = parseFloat((await this.getCartTotal()).replace(/[^\d.]/g, ''));
    const monthlyAmount = cartTotal / parseInt(months);
    
    const minimums = {
      KTC: 300,
      BAY: 500,
      FCY: 300
    };
    
    return monthlyAmount >= minimums[bank];
  }

  /**
   * Clear form notices
   */
  async clearNotices() {
    const notices = await this.page.locator('.woocommerce-error, .wc-block-components-notice-banner--error').all();
    
    for (const notice of notices) {
      if (await notice.isVisible()) {
        const closeButton = notice.locator('.notice-dismiss, .wc-block-components-notice-banner__remove-button, .close');
        if (await closeButton.isVisible()) {
          await closeButton.click();
        }
      }
    }
  }

  /**
   * Wait for form to be ready
   */
  async waitForFormReady() {
    // Wait for any loading indicators to disappear
    await this.page.waitForFunction(() => {
      const loadingElements = document.querySelectorAll('.loading, .spinner, .wc-block-checkout__spinner');
      return loadingElements.length === 0;
    }, { timeout: 10000 }).catch(() => {
      // Continue if timeout - form might still be usable
    });
    
    // Wait for payment methods to be available
    await this.page.waitForSelector('input[name="payment_method"], .wc-block-components-radio-control', { timeout: 10000 });
  }
}

module.exports = { CheckoutPage };
