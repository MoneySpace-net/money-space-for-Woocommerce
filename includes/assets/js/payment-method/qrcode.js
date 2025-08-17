import { getSetting } from '@woocommerce/settings';
import { decodeEntities } from '@wordpress/html-entities';
import { registerPaymentMethod  } from '@woocommerce/blocks-registry';
import { useEffect } from '@wordpress/element';
import './styles.scss';

const id = "moneyspace_qrprom";
const settings = getSetting( `${id}_data`, {} );
const label = decodeEntities( settings.title );

/**
 * Content component with notice clearing functionality
 */
const Content = () => {
    // Clear validation notices when QR payment method becomes active
    useEffect(() => {
        const clearValidationNotices = () => {
            try {
                console.log('Clearing validation notices for QR payment...');
                
                // Clear notices that might be left from other payment methods
                const noticeSelectors = [
                    '.wc-block-components-notice-banner',
                    '.wc-block-components-validation-error',
                    '.wc-block-components-notices',
                    '.woocommerce-error',
                    '.woocommerce-message',
                    '.woocommerce-info'
                ];
                
                noticeSelectors.forEach(selector => {
                    const notices = document.querySelectorAll(selector);
                    notices.forEach((notice) => {
                        const text = notice.textContent || '';
                        // Clear notices related to other payment methods (installment, credit card)
                        if (text.includes('installment') || 
                            text.includes('3,000') || 
                            text.includes('bank') ||
                            text.includes('credit card') ||
                            text.includes('CVV') ||
                            text.includes('card number')) {
                            notice.style.display = 'none';
                            notice.setAttribute('aria-hidden', 'true');
                            notice.classList.add('mns-hidden');
                        }
                    });
                });
                
                console.log('QR payment notices cleared');
            } catch (error) {
                console.log('Error clearing QR payment notices:', error.message);
            }
        };

        // Clear notices when QR payment method is selected
        const currentSelected = document.querySelector('input[name="radio-control-wc-payment-method-options"]:checked');
        if (currentSelected && currentSelected.value === id) {
            setTimeout(clearValidationNotices, 50);
        }

        // Monitor for when this payment method becomes selected
        const handlePaymentMethodChange = (event) => {
            if (event.target?.value === id) {
                setTimeout(clearValidationNotices, 50);
            }
        };

        const paymentRadios = document.querySelectorAll('input[name="radio-control-wc-payment-method-options"]');
        paymentRadios.forEach(radio => {
            radio.addEventListener('change', handlePaymentMethodChange);
        });

        return () => {
            paymentRadios.forEach(radio => {
                radio.removeEventListener('change', handlePaymentMethodChange);
            });
        };
    }, []);

	return decodeEntities( settings.description || '' );
};

const options = {
	name: id,
	label: label,  // Use simple string label
	content: <Content />,
	edit: <Content />,
	ariaLabel: label,
	canMakePayment: () => true,
	supports: {
		features: settings.supports,
	},
};

registerPaymentMethod( options );