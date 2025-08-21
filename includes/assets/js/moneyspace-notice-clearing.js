// MoneySpace Notice Clearing Utility
// Global function to clear installment validation notices from any payment method

// Import debug utility
import { debugLog, debugError } from './utils/debug';

window.MoneySpaceNoticeClearing = window.MoneySpaceNoticeClearing || {
    
    // Clear installment validation notices specifically
    clearInstallmentNotices: () => {
        try {
            debugLog('🧹 Clearing installment validation notices...');
            
            const noticeSelectors = [
                '.wc-block-components-notice-banner',
                '.wc-block-components-validation-error',
                '.wc-block-components-notices',
                '.woocommerce-error',
                '.woocommerce-message',
                '.woocommerce-info',
                '.wc-block-components-notices__notice'
            ];
            
            let clearedCount = 0;
            
            noticeSelectors.forEach(selector => {
                const notices = document.querySelectorAll(selector);
                notices.forEach((notice) => {
                    if (notice && notice.style) {
                        const text = notice.textContent || notice.innerText || '';
                        
                        // Check for installment-related error messages
                        const shouldClear = 
                            text.includes('Installment validation failed') ||
                            text.includes('Minimum amount not met') ||
                            text.includes('installment') ||
                            text.includes('3,000') ||
                            text.includes('3000') ||
                            text.includes('bank') ||
                            text.includes('Minimum amount') ||
                            text.includes('minimum amount') ||
                            text.includes('amount of balance must be') ||
                            text.includes('ยอดเงินขั้นต่ำ') ||
                            text.includes('ผ่อนชำระ') ||
                            text.includes('KTC') ||
                            text.includes('BAY') ||
                            text.includes('FCY') ||
                            notice.classList.contains('moneyspace-installment-error') ||
                            notice.getAttribute('data-type') === 'installment-error';
                        
                        if (shouldClear) {
                            notice.style.display = 'none';
                            notice.setAttribute('aria-hidden', 'true');
                            notice.classList.add('mns-hidden-installment');
                            clearedCount++;
                            debugLog(`🗑️ Cleared: "${text.substring(0, 60)}..."`);
                        }
                    }
                });
            });
            
            // Also clear from common notice containers
            const containers = [
                '.wc-block-components-notices',
                '.woocommerce-notices-wrapper',
                '.checkout-notices'
            ];
            
            containers.forEach(containerSelector => {
                const container = document.querySelector(containerSelector);
                if (container) {
                    const noticeItems = container.querySelectorAll('.wc-block-components-notice-banner, .woocommerce-error, .notice');
                    noticeItems.forEach(item => {
                        const text = item.textContent || '';
                        if (text.includes('Installment validation failed') || 
                            text.includes('Minimum amount not met') ||
                            text.includes('installment') || 
                            text.includes('3,000') || 
                            text.includes('bank')) {
                            item.style.display = 'none';
                            item.setAttribute('aria-hidden', 'true');
                            clearedCount++;
                        }
                    });
                }
            });
            
            debugLog(`✅ Cleared ${clearedCount} installment notices`);
            return clearedCount;
            
        } catch (error) {
            debugError('MoneySpace installment notice clearing error:', error);
            return 0;
        }
    },

    // Initialize payment method change listeners
    initPaymentMethodListeners: () => {
        debugLog('🎧 Initializing payment method change listeners...');
        
        const handlePaymentMethodChange = (event) => {
            if (!event.target?.name?.includes('radio-control-wc-payment-method-options')) {
                return;
            }
            
            debugLog('💳 Payment method changed', { 
                from: event.target?.previousValue, 
                to: event.target?.value 
            });
            
            // Clear installment notices when switching payment methods
            window.MoneySpaceNoticeClearing.clearInstallmentNotices();
            
            // Clear again after a delay to catch late notices
            setTimeout(() => {
                window.MoneySpaceNoticeClearing.clearInstallmentNotices();
            }, 150);
        };
        
        // Add listeners to payment method radio buttons
        const paymentRadios = document.querySelectorAll('input[name="radio-control-wc-payment-method-options"]');
        
        paymentRadios.forEach(radio => {
            // Remove existing listener if any
            radio.removeEventListener('change', handlePaymentMethodChange);
            // Add new listener
            radio.addEventListener('change', handlePaymentMethodChange);
        });
        
        debugLog(`✅ Added listeners to ${paymentRadios.length} payment method radios`);
        
        return paymentRadios.length;
    },

    // Auto-initialize when DOM is ready
    autoInit: () => {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                setTimeout(window.MoneySpaceNoticeClearing.initPaymentMethodListeners, 500);
            });
        } else {
            setTimeout(window.MoneySpaceNoticeClearing.initPaymentMethodListeners, 500);
        }
        
        // Also reinitialize when page changes (for SPAs)
        const observer = new MutationObserver((mutations) => {
            const hasPaymentMethods = mutations.some(mutation => {
                return Array.from(mutation.addedNodes).some(node => {
                    return node.nodeType === 1 && (
                        node.querySelector?.('input[name="radio-control-wc-payment-method-options"]') ||
                        node.matches?.('input[name="radio-control-wc-payment-method-options"]')
                    );
                });
            });
            
            if (hasPaymentMethods) {
                debugLog('🔄 Payment methods detected, reinitializing...');
                setTimeout(window.MoneySpaceNoticeClearing.initPaymentMethodListeners, 200);
            }
        });
        
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }
};

// Auto-initialize
window.MoneySpaceNoticeClearing.autoInit();
