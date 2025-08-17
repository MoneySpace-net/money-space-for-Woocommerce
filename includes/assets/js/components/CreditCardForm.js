import {useState, useEffect, useRef, useCallback} from '@wordpress/element';
import '../payment-method/styles.scss';
import {__} from '@wordpress/i18n';

const CreditCardForm = (props) => {
    const model = {
        ccNo: '',
        ccName: '',
        ccExpMonth: '',
        ccExpYear: '',
        ccCVV: '',
        cardYear: '',
        minCardYear: new Date().getFullYear(),
        dirty: false,
    };
    
    const [formData, setFormData] = useState(model);
    const [validationErrors, setValidationErrors] = useState({});
    
    // Safely destructure eventRegistration with default values
    const { eventRegistration = {}, i18n = {} } = props;
    const { onPaymentSetup, onCheckoutValidation } = eventRegistration;

    // Check if this payment method is selected
    const isPaymentMethodSelected = () => {
        // Try multiple selectors to ensure we catch the payment method selection
        const radioElement = document.querySelector('input[name="radio-control-wc-payment-method-options"]:checked');
        if (radioElement) {
            const isSelected = radioElement.value === 'moneyspace_creditcard' || radioElement.value === 'moneyspace';
            console.log('Payment method selection check via radio:', radioElement.value, '-> selected:', isSelected);
            return isSelected;
        }
        
        // Fallback to direct ID check
        const directRadio = document.getElementById('radio-control-wc-payment-method-options-moneyspace_creditcard');
        if (directRadio && directRadio.checked) {
            console.log('Payment method selection check via direct ID -> selected: true');
            return true;
        }
        
        // Another fallback
        const legacyRadio = document.getElementById('radio-control-wc-payment-method-options-moneyspace');
        if (legacyRadio && legacyRadio.checked) {
            console.log('Payment method selection check via legacy ID -> selected: true');
            return true;
        }
        
        console.log('⚠️ Warning: Could not determine if credit card payment method is selected');
        return false;
    };

    // Comprehensive validation function
    const validateFormData = useCallback(() => {
        const errors = {};
        
        // Only validate if this payment method is selected
        if (!isPaymentMethodSelected()) {
            return {};
        }

        // Card number validation
        const cardNumber = formData.ccNo.replaceAll(" ", "");
        if (!cardNumber) {
            errors.ccNo = i18n.MNS_CC_WARN_CC_NO_1 || 'Card number is required';
        } else if (cardNumber.length < 16) {
            errors.ccNo = i18n.MNS_CC_WARN_CC_NO_2 || 'Card number must be 16 digits';
        } else if (!/^\d{16}$/.test(cardNumber)) {
            errors.ccNo = 'Card number must contain only digits';
        }

        // Card holder name validation
        if (!formData.ccName.trim()) {
            errors.ccName = i18n.MNS_CC_WARN_CC_NAME || 'Cardholder name is required';
        } else if (formData.ccName.trim().length < 2) {
            errors.ccName = 'Cardholder name must be at least 2 characters';
        }

        // Expiry month validation
        if (!formData.ccExpMonth) {
            errors.ccExpMonth = i18n.MNS_CC_WARN_CC_EXP_MONTH || 'Expiry month is required';
        } else {
            const month = parseInt(formData.ccExpMonth);
            if (month < 1 || month > 12) {
                errors.ccExpMonth = 'Please select a valid month';
            }
        }

        // Expiry year validation
        if (!formData.ccExpYear) {
            errors.ccExpYear = i18n.MNS_CC_WARN_CC_EXP_YEAR || 'Expiry year is required';
        } else {
            const currentYear = new Date().getFullYear();
            const selectedYear = parseInt(formData.ccExpYear);
            if (selectedYear < currentYear) {
                errors.ccExpYear = 'Card has expired';
            }
        }

        // Card expiry date validation (month/year combination)
        if (formData.ccExpMonth && formData.ccExpYear) {
            const currentDate = new Date();
            const currentYear = currentDate.getFullYear();
            const currentMonth = currentDate.getMonth() + 1;
            const selectedYear = parseInt(formData.ccExpYear);
            const selectedMonth = parseInt(formData.ccExpMonth);
            
            if (selectedYear === currentYear && selectedMonth < currentMonth) {
                errors.ccExpMonth = 'Card has expired';
            }
        }

        // CVV validation
        if (!formData.ccCVV) {
            errors.ccCVV = i18n.MNS_CC_WARN_CVV_1 || 'CVV is required';
        } else if (formData.ccCVV.length < 3) {
            errors.ccCVV = i18n.MNS_CC_WARN_CVV_2 || 'CVV must be 3 digits';
        } else if (!/^\d{3,4}$/.test(formData.ccCVV)) {
            errors.ccCVV = 'CVV must contain only digits';
        }

        return errors;
    }, [formData, i18n]);

    // Update validation errors when form data changes
    useEffect(() => {
        if (formData.dirty) {
            const errors = validateFormData();
            setValidationErrors(errors);
            
            // If this payment method is selected and there are errors, show them immediately
            if (isPaymentMethodSelected() && Object.keys(errors).length > 0) {
                console.log('Credit Card Form Validation Errors:', errors);
            }
        }
    }, [formData, validateFormData]);
    
    const useValidateCheckout = (
        {
            formData,
            onCheckoutValidation
        }) => {
        useEffect(() => {
            if (!onCheckoutValidation) return;
            
            const unsubscribe = onCheckoutValidation(() => {
                // Only validate if this payment method is selected
                if (!isPaymentMethodSelected()) {
                    console.log('Credit Card validation skipped - payment method not selected');
                    return true;
                }

                console.log('Credit Card validation running - payment method IS selected');

                // Mark form as dirty to show validation errors
                if (!formData.dirty) {
                    setFormData(prev => ({ ...prev, dirty: true }));
                }

                // Perform comprehensive validation
                const errors = validateFormData();
                
                console.log('Credit Card validation errors:', errors);
                
                if (Object.keys(errors).length > 0) {
                    // Update validation errors state
                    setValidationErrors(errors);
                    
                    // Get the first error message
                    const firstError = Object.values(errors)[0];
                    
                    console.error('🚨 Credit Card Validation Failed - BLOCKING CHECKOUT:', firstError);
                    console.error('🚨 Full validation errors:', errors);
                    
                    // Return validation error object that WooCommerce Blocks expects
                    return {
                        type: 'error',
                        message: firstError,
                        hidden: false
                    };
                }

                console.log('✅ Credit Card Validation Passed - allowing checkout');
                
                // All validation passed
                return true;
            });
            return unsubscribe;
        }, [formData, onCheckoutValidation, validateFormData]);
    }

    const usePaymentSetup = ({formData, onPaymentSetup}) => {
        useEffect(() => {
            if (!onPaymentSetup) return;
            
            const unsubscribe = onPaymentSetup(() => {
                return formData;
            });

            return unsubscribe;
        }, [formData, onPaymentSetup]);
    }
    usePaymentSetup({formData, onPaymentSetup});

    const useProcessPayment = ({formData, onPaymentSetup}) => {
        useEffect(() => {
            if (!onPaymentSetup) return;
            
            const unsubscribe = onPaymentSetup(() => {
                // WooCommerce Blocks expects payment data in a specific format
                return {
                    type: "success",
                    meta: {
                        paymentMethodData: {
                            cardNumber: formData.ccNo.replaceAll(" ", ""),
                            cardHolder: formData.ccName,
                            cardExpDate: formData.ccExpMonth,
                            cardExpDateYear: formData.ccExpYear,
                            cardCVV: formData.ccCVV
                        }
                    },
                    // Also provide the data in the format the gateway expects
                    paymentMethodData: {
                        cardNumber: formData.ccNo.replaceAll(" ", ""),
                        cardHolder: formData.ccName,
                        cardExpDate: formData.ccExpMonth,
                        cardExpDateYear: formData.ccExpYear,
                        cardCVV: formData.ccCVV
                    }
                };
            });

            return unsubscribe;
        }, [formData, onPaymentSetup]);
    }
    useProcessPayment({formData, onPaymentSetup});

    useValidateCheckout({
        formData,
        onCheckoutValidation
    });

    // Enhanced notice clearing system for WooCommerce Blocks
    useEffect(() => {
        const clearValidationNotices = () => {
            try {
                console.log('Clearing credit card validation notices...');
                
                // Method 1: Hide notices via CSS
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
                        // Clear notices related to other payment methods (installment, QR)
                        if (text.includes('installment') || 
                            text.includes('3,000') || 
                            text.includes('bank') ||
                            text.includes('QR')) {
                            notice.style.display = 'none';
                            notice.setAttribute('aria-hidden', 'true');
                            notice.classList.add('mns-hidden');
                        }
                    });
                });
                
                // Method 2: Try to clear WooCommerce notices container
                const noticesContainer = document.querySelector('.wc-block-components-notices');
                if (noticesContainer) {
                    // Clear inner content if safe to do so
                    const noticeItems = noticesContainer.querySelectorAll('.wc-block-components-notice-banner');
                    noticeItems.forEach(item => {
                        const text = item.textContent || '';
                        if (text.includes('installment') || text.includes('3,000') || text.includes('bank')) {
                            item.style.display = 'none';
                            item.setAttribute('aria-hidden', 'true');
                        }
                    });
                }
                
                console.log('Credit card notices cleared successfully');
            } catch (error) {
                console.log('Error clearing credit card notices:', error.message);
            }
        };

        const handlePaymentMethodChange = (event) => {
            console.log('Payment method changed (credit card):', event.target?.value);
            
            // Only process if this is actually a payment method radio button change
            if (!event.target?.name?.includes('radio-control-wc-payment-method-options')) {
                return;
            }
            
            // Clear notices immediately when payment method changes
            clearValidationNotices();
            
            // Also clear after a short delay to catch any late-rendered notices
            setTimeout(() => {
                clearValidationNotices();
                
                // Reset form state when switching away from credit card
                if (event.target?.value && event.target.value !== 'moneyspace') {
                    setFormData(prev => ({ ...prev, dirty: false }));
                    setValidationErrors({});
                }
            }, 100);
        };

        // Enhanced payment method change detection - but more specific to avoid conflicts
        const addPaymentMethodListeners = () => {
            // Only listen for payment method radio button changes - be very specific
            const paymentRadios = document.querySelectorAll('input[name="radio-control-wc-payment-method-options"]');
            
            paymentRadios.forEach(radio => {
                radio.addEventListener('change', handlePaymentMethodChange);
                // Remove click listener to avoid conflicts with dropdowns
            });
            
            // DO NOT add listeners to the payment container as it interferes with dropdowns
            
            return paymentRadios;
        };

        const paymentRadios = addPaymentMethodListeners();
        
        // Clear notices when component mounts if another payment method is selected
        const currentSelected = document.querySelector('input[name="radio-control-wc-payment-method-options"]:checked');
        if (currentSelected && currentSelected.value !== 'moneyspace') {
            setTimeout(clearValidationNotices, 100);
        }

        return () => {
            paymentRadios.forEach(radio => {
                radio.removeEventListener('change', handlePaymentMethodChange);
                // No click listener to remove
            });
            
            // No payment container listeners to remove
        };
    }, []);

    // Helper functions for UI validation states
    const getFieldValidationClass = (fieldName) => {
        if (!formData.dirty) return '';
        return validationErrors[fieldName] ? 'has-error' : '';
    };

    const renderFieldError = (fieldName) => {
        if (!formData.dirty || !validationErrors[fieldName]) return null;
        
        return (
            <div className="wc-block-components-validation-error" role="alert">
                <p>{validationErrors[fieldName]}</p>
            </div>
        );
    };
    
    const listNumber = [1,2,3,4,5,6,7,8,9,10,11,12];
    
    const minCardMonth = () => {
        if (formData.ccExpYear && parseInt(formData.ccExpYear) === formData.minCardYear) {
            return new Date().getMonth() + 1;
        }
        return 1;
    };
    
    const cc_format = (value) => {
        const v = value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
        const matches = v.match(/\d{4,16}/g);
        const match = matches && matches[0] || '';
        const parts = [];
    
        for (let i = 0, len = match.length; i < len; i += 4) {
            parts.push(match.substring(i, i + 4));
        }
    
        if (parts.length) {
            return parts.join(' ');
        } else {
            return value;
        }
    };

    const checkCVV = (event) => {
        if (!/^[0-9]*$/.test(event.key) && ![8, 67, 86, 88].includes(event.keyCode)) {
            return event.preventDefault();
        }
        
        // Limit CVV to 4 digits max
        if (formData.ccCVV.length >= 4 && ![8, 67, 86, 88].includes(event.keyCode)) {
            return event.preventDefault();
        }
    };

    const checkCardNumber = (event) => {
        if (!/^[0-9]*$/.test(event.key) && ![8, 67, 86, 88].includes(event.keyCode)) {
            return event.preventDefault();
        }

        if (formData.ccNo.replaceAll(" ", "").length >= 16 && ![8, 67, 86, 88].includes(event.keyCode)) {
            return event.preventDefault();
        }
    };

    const handleChange = (field) => (event) => {
        let value = event.target.value;
        
        if (field === "ccNo") {
            // Only allow numeric input and format with spaces
            if (/^[0-9\s]*$/.test(value)) {
                value = cc_format(value);
                setFormData(prev => ({ ...prev, [field]: value, dirty: true }));
            }
        } else if (field === "ccName") {
            // Convert to uppercase and allow only letters and spaces
            value = value.toUpperCase().replace(/[^A-Z\s]/g, '');
            setFormData(prev => ({ ...prev, [field]: value, dirty: true }));
        } else if (field === "ccCVV") {
            // Only allow numeric input for CVV
            if (/^[0-9]*$/.test(value) && value.length <= 4) {
                setFormData(prev => ({ ...prev, [field]: value, dirty: true }));
            }
        } else {
            setFormData(prev => ({ ...prev, [field]: value, dirty: true }));
        }
    };

    const validateCardCVV = () => {
        return formData.ccCVV.length === 0 && isPaymentMethodSelected();
    };

    return (
        <div className='wc-block-components-credit-card-form'>
            <div className={`wc-block-components-text-input wc-block-components-credit-form is-active ${getFieldValidationClass('ccNo')}`}>
                <input 
                    type="text" 
                    value={formData.ccNo} 
                    onChange={handleChange('ccNo')} 
                    id="txtCardNumber" 
                    name="cardNumber"  
                    onKeyDown={checkCardNumber} 
                    placeholder="0000 0000 0000 0000"
                    maxLength="19"
                />
                <label htmlFor="txtCardNumber">{i18n.MNS_CC_NO} *</label>
                {renderFieldError('ccNo')}
            </div>
            
            <div className={`wc-block-components-text-input wc-block-components-credit-form is-active ${getFieldValidationClass('ccName')}`}>
                <input 
                    type="text" 
                    value={formData.ccName} 
                    onChange={handleChange('ccName')} 
                    id="txtHolder" 
                    name="cardHolder" 
                    placeholder="TONY ELSDEN"
                    maxLength="50"
                />
                <label htmlFor="txtHolder">{i18n.MNS_CC_NAME} *</label>
                {renderFieldError('ccName')}
            </div>
            
            <div className={`wc-block-components-text-input is-active ${getFieldValidationClass('ccExpMonth')}`}>
                <select 
                    value={formData.ccExpMonth} 
                    onChange={handleChange('ccExpMonth')} 
                    id="txtExpDate" 
                    name="cardExpDate"
                >
                    <option value="" disabled>{i18n.MNS_MONTH}</option>
                    {listNumber.map((x) => (
                        <option 
                            key={x}
                            value={x} 
                            disabled={x < minCardMonth()}
                        >
                            {x < 10 ? '0' + x : x}
                        </option>
                    ))}
                </select>
                <label htmlFor="txtExpDate">{i18n.MNS_CC_EXP_MONTH} *</label>
                {renderFieldError('ccExpMonth')}
            </div>
            
            <div className={`wc-block-components-text-input is-active ${getFieldValidationClass('ccExpYear')}`}>
                <select 
                    value={formData.ccExpYear} 
                    onChange={handleChange('ccExpYear')} 
                    id="ccExpYear" 
                    name="cardExpDateYear"
                >
                    <option value="" disabled>{i18n.MNS_YEAR}</option>
                    {listNumber.map((x, index) => (
                        <option 
                            key={index}
                            value={index + formData.minCardYear}
                        >
                            {index + formData.minCardYear}
                        </option>
                    ))}
                </select>
                <label htmlFor="ccExpYear">{i18n.MNS_CC_EXP_YEAR} *</label>
                {renderFieldError('ccExpYear')}
            </div>
            
            <div className={`wc-block-components-text-input wc-block-components-credit-form is-active ${getFieldValidationClass('ccCVV')}`}>
                <input 
                    type="password" 
                    value={formData.ccCVV} 
                    onChange={handleChange('ccCVV')} 
                    id="txtCVV" 
                    name="cardCVV" 
                    maxLength="4" 
                    onKeyDown={checkCVV} 
                    placeholder="000" 
                    required={validateCardCVV()}
                />
                <label htmlFor="txtCVV">{i18n.MNS_CC_CVV} *</label>
                {renderFieldError('ccCVV')}
            </div>
        </div>
    );
}
 
export default CreditCardForm;