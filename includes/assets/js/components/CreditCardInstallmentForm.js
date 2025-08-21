import React, { useState, useEffect, useRef, useCallback, useMemo, Component } from '@wordpress/element';
import '../payment-method/styles.scss';
import { __ } from '@wordpress/i18n';
import _, { map } from 'underscore';
import { useSelect } from '@wordpress/data';
import { debugLog, debugWarn, debugError } from '../utils/debug';

// Error Boundary Component
class ErrorBoundary extends Component {
    constructor(props) {
        super(props);
        this.state = { hasError: false, error: null };
    }

    static getDerivedStateFromError(error) {
        return { hasError: true, error };
    }

    componentDidCatch(error, errorInfo) {
        debugError('CreditCardInstallmentForm Error Boundary Caught:', {
            error: error,
            errorMessage: error.message,
            errorStack: error.stack,
            errorInfo: errorInfo,
            componentStack: errorInfo.componentStack
        });
    }

    render() {
        if (this.state.hasError) {
            return <div>Something went wrong with the installment form. Please refresh the page.</div>;
        }
        return this.props.children;
    }
}

const CreditCardInstallmentForm = (props) => {
    try {
        // Add safety checks for props
        if (!props) {
            return <div>Loading payment options...</div>;
        }

        const { ccIns, msfee, i18n, billing: propsBilling, eventRegistration } = props;

        // Use billing data from props if available, otherwise try useSelect as fallback
        let billing = propsBilling;
        
        // Only use useSelect fallback if no billing data in props
        const fallbackBilling = useSelect((select) => {
            // Skip if we already have billing data from props
            if (propsBilling && propsBilling.cartTotal && propsBilling.currency) {
                return null;
            }
            
            try {
                // Try different store selectors that might be available
                const stores = ['wc/store/cart', 'wc/store/checkout', 'core/data'];
                
                for (const storeName of stores) {
                    try {
                        const store = select(storeName);
                        if (store && typeof store.getCartTotals === 'function') {
                            const cartTotals = store.getCartTotals();
                            
                            if (cartTotals && cartTotals.total_price !== undefined) {
                                return {
                                    cartTotal: {
                                        value: cartTotals.total_price
                                    },
                                    currency: {
                                        minorUnit: cartTotals.currency_minor_unit || 2
                                    }
                                };
                            }
                        }
                    } catch (storeError) {
                        // Store not available, continue to next
                    }
                }
                
                // Fallback: create default billing data for testing
                return {
                    cartTotal: {
                        value: 500000 // 5000 THB (in minor units, assuming 2 decimal places)
                    },
                    currency: {
                        minorUnit: 2
                    }
                };
                
            } catch (error) {
                // Return fallback data
                return {
                    cartTotal: {
                        value: 500000 // 5000 THB (in minor units)
                    },
                    currency: {
                        minorUnit: 2
                    }
                };
            }
        }, [propsBilling]);

        // Use props billing data if available, otherwise use fallback
        if (!billing && fallbackBilling) {
            billing = fallbackBilling;
        }

        // Add safety checks for required props
        if (!i18n) {
            return <div>Loading payment form...</div>;
        }

        // Use props billing data if available, otherwise use fallback
        if (!billing && fallbackBilling) {
            billing = fallbackBilling;
        }

        const { cartTotal, currency } = billing;
        
        // Enhanced safety checks for billing data
        if (!cartTotal) {
            return <div>Loading cart total...</div>;
        }
        
        if (!currency) {
            return <div>Loading currency information...</div>;
        }
        
        if (typeof cartTotal.value !== 'number') {
            return <div>Invalid cart total value...</div>;
        }
        
        if (typeof currency.minorUnit !== 'number') {
            return <div>Invalid currency data...</div>;
        }
        
        // Safe destructuring of eventRegistration with fallbacks
        let onPaymentSetup, onCheckoutValidation;
        try {
            ({
                onPaymentSetup = () => {},
                onCheckoutValidation = () => {}
            } = eventRegistration || {});
        } catch (destructuringError) {
            onPaymentSetup = () => {};
            onCheckoutValidation = () => {};
        }

        // Add safety checks for billing data
        if (!cartTotal || !currency) {
            return <div>Loading cart information...</div>;
        }

        const model = {
            selectbank: "",
            KTC_permonths: "",
            BAY_permonths: "",
            FCY_permonths: "",
            dirty: false
        };
        
        let paymentData, setPaymentData;
        try {
            [paymentData, setPaymentData] = useState(model);
        } catch (stateError) {
            return <div>Error initializing payment form...</div>;
        }
    
    const handleChange = (field) => (event) => {
        try {
            const value = event.target.value;
            
            if (field === "selectbank") {
                // Reset all month selections when bank changes
                const newData = {
                    ...paymentData,
                    selectbank: value,
                    dirty: true,
                    KTC_permonths: "",
                    BAY_permonths: "",
                    FCY_permonths: ""
                };
                
                // Set default month for selected bank
                if (value === "KTC") {
                    newData.KTC_permonths = "3";
                } else if (value === "BAY") {
                    newData.BAY_permonths = "3";
                } else if (value === "FCY") {
                    newData.FCY_permonths = "3";
                }
                
                setPaymentData(newData);
            } else {
                // Handle month selection changes
                setPaymentData(prev => ({
                    ...prev,
                    [field]: value,
                    dirty: true
                }));
            }
        } catch (changeError) {
            debugError('MoneySpace installment handleChange error:', changeError);
        }
    };        // Enhanced dropdown interaction handling - simplified approach
        useEffect(() => {
            const ensureDropdownsWork = () => {
                const selects = document.querySelectorAll('.wc-block-components-credit-card-installment-form select');
                
                selects.forEach((select) => {
                    // Only set essential styles without aggressive event handling
                    select.style.pointerEvents = 'auto';
                    select.style.position = 'relative';
                    select.style.zIndex = '10'; // Reduced z-index to avoid conflicts
                });
            };
            
            // Simple timeout to ensure DOM is ready
            const timeoutId = setTimeout(ensureDropdownsWork, 50);
            
            return () => {
                clearTimeout(timeoutId);
            };
        }, [paymentData.selectbank]);

    const findObj = useCallback((key) => {
        try {
            if (!ccIns || !Array.isArray(ccIns)) {
                return null;
            }
            return _.find(ccIns, (x) => { return x && x.code == key; });
        } catch (findError) {
            return null;
        }
    }, [ccIns]);
    
    // Helper function to convert maxMonth strings to numbers
    const parseMaxMonth = useCallback((maxMonth) => {
        try {
            if (typeof maxMonth === 'number') return maxMonth;
            if (typeof maxMonth === 'string') {
                const monthMap = {
                    'three': 3, 'four': 4, 'five': 5, 'six': 6, 'seven': 7, 
                    'eight': 8, 'nine': 9, 'ten': 10, 'eleven': 11, 'twelve': 12
                };
                return monthMap[maxMonth.toLowerCase()] || parseInt(maxMonth) || 10;
            }
            return 10; // default fallback
        } catch (parseError) {
            return 10;
        }
    }, []);
    
    // Memoize bank objects to prevent unnecessary re-renders
    const ktcObj = useMemo(() => findObj("ktc"), [findObj]);
    const bayObj = useMemo(() => findObj("bay"), [findObj]);
    const fcyObj = useMemo(() => findObj("fcy"), [findObj]);

    // Early return if critical data is missing
    if (!ccIns || !Array.isArray(ccIns) || ccIns.length === 0) {
        return <div>Loading installment options...</div>;
    }

    if (!ktcObj && !bayObj && !fcyObj) {
        return <div>No installment options available.</div>;
    }
    
    // Memoize amount calculation with safety checks
    const amount_total = useMemo(() => {
        if (!cartTotal || typeof cartTotal.value !== 'number' || !currency || typeof currency.minorUnit !== 'number') {
            return 0;
        }
        return cartTotal.value / Math.pow(10, currency.minorUnit);
    }, [cartTotal, currency]);

    // Define checkPrice function before using it
    const checkPrice = useCallback(() => {
        return amount_total > 3000;
    }, [amount_total]);

    const formatNum = useCallback((val) => {
        var number = parseFloat(val).toFixed(2);
        return number.toString().replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,");
    }, []);

    const warningPriceLessThanMinimum = useMemo(() => {
        return (<div>
            <span style={{ color: "red" }} >The amount of balance must be 3,000.01 baht or more in order to make the installment payment.</span>
        </div>);
    }, []);

    const useValidateCheckout = ({ paymentData, onCheckoutValidation }) => {
        useEffect(() => {
            try {
                // Skip if validation function is not available
                if (!onCheckoutValidation || typeof onCheckoutValidation !== 'function') {
                    return;
                }

                const unsubscribe = onCheckoutValidation(() => {
                    // Check if this payment method is selected
                    const radioElement = document.getElementById('radio-control-wc-payment-method-options-moneyspace_installment');
                    const isInstallmentSelected = radioElement ? radioElement.checked : false;
                    
                    // Only validate if installment payment method is selected
                    if (!isInstallmentSelected) {
                        return true;
                    }

                    // Validate minimum amount for installment
                    if (!checkPrice()) {
                        return false;
                    }

                    // Validate bank selection
                    if (!paymentData.selectbank || paymentData.selectbank === "") {
                        return false;
                    }

                    // Validate installment months based on selected bank
                    if (paymentData.selectbank === "KTC") {
                        if (!paymentData.KTC_permonths || paymentData.KTC_permonths === "") {
                            return false;
                        }
                        
                        // Validate KTC minimum amount per month - ensure we parse the string
                        const monthlyAmount = amount_total / parseInt(String(paymentData.KTC_permonths));
                        if (monthlyAmount < 300) {
                            return false;
                        }
                    } else if (paymentData.selectbank === "BAY") {
                        if (!paymentData.BAY_permonths || paymentData.BAY_permonths === "") {
                            return false;
                        }
                        
                        // Validate BAY minimum amount per month - ensure we parse the string
                        const monthlyAmount = amount_total / parseInt(String(paymentData.BAY_permonths));
                        if (monthlyAmount < 500) {
                            return false;
                        }
                    } else if (paymentData.selectbank === "FCY") {
                        if (!paymentData.FCY_permonths || paymentData.FCY_permonths === "") {
                            return false;
                        }
                        
                        // Validate FCY minimum amount per month - ensure we parse the string
                        const monthlyAmount = amount_total / parseInt(String(paymentData.FCY_permonths));
                        if (monthlyAmount < 300) {
                            return false;
                        }
                    }

                    // Validate that selected months are within allowed range
                    const selectedBank = paymentData.selectbank;
                    const bankObj = selectedBank === "KTC" ? ktcObj : 
                                   selectedBank === "BAY" ? bayObj : 
                                   selectedBank === "FCY" ? fcyObj : null;
                    
                    if (bankObj) {
                        const maxMonth = parseMaxMonth(bankObj.maxMonth);
                        const selectedMonths = parseInt(String(
                            selectedBank === "KTC" ? paymentData.KTC_permonths :
                            selectedBank === "BAY" ? paymentData.BAY_permonths :
                            selectedBank === "FCY" ? paymentData.FCY_permonths : "0"
                        ));
                        
                        if (selectedMonths > maxMonth) {
                            return false;
                        }
                        
                        if (bankObj.months && !bankObj.months.includes(selectedMonths)) {
                            return false;
                        }
                    }
                    
                    // All validation passed
                    return true;
                });

                return unsubscribe;
            } catch (validateError) {
                debugError('Error in useValidateCheckout:', validateError);
                return () => {}; // Return empty cleanup function
            }
        }, [paymentData, onCheckoutValidation, checkPrice, amount_total, ktcObj, bayObj, fcyObj, parseMaxMonth]);
    };

    useValidateCheckout({ paymentData, onCheckoutValidation });

    // Enhanced notice clearing system for WooCommerce Blocks
    useEffect(() => {
        const clearValidationNotices = () => {
            try {
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
                        if (notice && notice.style) {
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
                        item.style.display = 'none';
                        item.setAttribute('aria-hidden', 'true');
                    });
                }
                
                // Method 3: Clear specific checkout validation notices
                const checkoutNotices = document.querySelectorAll('[data-block-name*="checkout"] .wc-block-components-notice-banner');
                checkoutNotices.forEach(notice => {
                    notice.style.display = 'none';
                    notice.setAttribute('aria-hidden', 'true');
                });
            } catch (error) {
                debugError('MoneySpace notice clearing error:', error.message);
            }
        };

        const handlePaymentMethodChange = (event) => {
            // Only process if this is actually a payment method radio button change
            if (!event.target?.name?.includes('radio-control-wc-payment-method-options')) {
                return;
            }
            
            // Clear notices immediately when payment method changes
            clearValidationNotices();
            
            // Also clear after a short delay to catch any late-rendered notices
            setTimeout(() => {
                clearValidationNotices();
                
                // Reset installment form when switching away
                if (event.target?.value && event.target.value !== 'moneyspace_installment') {
                    setPaymentData(prev => ({ 
                        ...prev, 
                        dirty: false,
                        selectbank: "",
                        KTC_permonths: "",
                        BAY_permonths: "",
                        FCY_permonths: ""
                    }));
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
        if (currentSelected && currentSelected.value !== 'moneyspace_installment') {
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

    // Additional effect to monitor for notice changes and clear them when installment is not selected
    useEffect(() => {
        const observer = new MutationObserver(() => {
            const currentSelected = document.querySelector('input[name="radio-control-wc-payment-method-options"]:checked');
            if (currentSelected && currentSelected.value !== 'moneyspace_installment') {
                // Clear any installment-related notices that might have appeared
                setTimeout(() => {
                    const notices = document.querySelectorAll('.wc-block-components-notice-banner');
                    notices.forEach(notice => {
                        const text = notice.textContent || '';
                        if (text.includes('installment') || text.includes('3,000') || text.includes('bank')) {
                            notice.style.display = 'none';
                            notice.setAttribute('aria-hidden', 'true');
                        }
                    });
                }, 50);
            }
        });

        const noticesContainer = document.querySelector('.wc-block-components-notices');
        if (noticesContainer) {
            observer.observe(noticesContainer, { 
                childList: true, 
                subtree: true,
                attributes: true 
            });
        }

        return () => observer.disconnect();
    }, []);

    const useProcessPayment = ({paymentData, onPaymentSetup}) => {
        useEffect(() => {
            try {
                // Skip if processing function is not available
                if (!onPaymentSetup || typeof onPaymentSetup !== 'function') {
                    return;
                }

                const unsubscribe = onPaymentSetup(() => {
                    // Prepare payment method data based on selected bank
                    const selectedBank = paymentData.selectbank;
                    const selectedMonths = selectedBank === "KTC" ? paymentData.KTC_permonths :
                                         selectedBank === "BAY" ? paymentData.BAY_permonths :
                                         selectedBank === "FCY" ? paymentData.FCY_permonths : "";
                    
                    // Ensure all values are strings for WooCommerce compatibility
                    const paymentMethodData = {
                        selectbank: String(selectedBank || ""),
                        installment_months: String(selectedMonths || ""),
                        // Include individual bank fields for backward compatibility - all as strings
                        KTC_permonths: String(paymentData.KTC_permonths || ""),
                        BAY_permonths: String(paymentData.BAY_permonths || ""),
                        FCY_permonths: String(paymentData.FCY_permonths || ""),
                        // Add calculated amounts for reference - as strings
                        total_amount: String(amount_total || "0"),
                        monthly_amount: selectedMonths ? String((amount_total / parseInt(selectedMonths)).toFixed(2)) : "0"
                    };

                    // Debug log only when debug mode is enabled
                    debugLog('Payment data prepared for submission', paymentMethodData);

                    const response = {
                        type: "success",
                        meta: {
                            paymentMethodData: paymentMethodData
                        },
                        // Also provide the data in the format the gateway expects
                        paymentMethodData: paymentMethodData
                    };
                    
                    return response;
                });

                return unsubscribe;
            } catch (processError) {
                debugError('Error in useProcessPayment:', processError);
                return () => {}; // Return empty cleanup function
            }
        }, [paymentData, onPaymentSetup, amount_total]);
    }
    useProcessPayment({paymentData, onPaymentSetup});

    const renderView = useMemo(() => {
        try {
            // Test with minimal JSX first
            if (window.location.search.includes('minimal=1')) {
                const minimalJsx = (<div className='wc-block-components-credit-card-installment-form'>
                    <h2>Test Installment Form</h2>
                    <p>Amount: {amount_total} THB</p>
                    <p>Banks available: {ccIns ? ccIns.length : 0}</p>
                </div>);
                return minimalJsx;
            }

            const jsxResult = (<div className='wc-block-components-credit-card-installment-form'>
                <h2>{i18n.MNS_CC_INS_TITLE}</h2>
            <div className={`wc-block-components-radio-control`}>
                <div className="wc-block-components-radio-control-accordion-option">
                    <label className="wc-block-components-radio-control__option" htmlFor="radio-control-wc-payment-method-options-moneyspace-ins-ktc">
                        <input id="radio-control-wc-payment-method-options-moneyspace-ins-ktc" className="wc-block-components-radio-control__input" type="radio" name="mns_ins_payment" aria-describedby="radio-control-wc-payment-method-options-moneyspace__label" value="KTC" onChange={handleChange('selectbank')} checked={ paymentData.selectbank == "KTC" } />
                        <div className="wc-block-components-radio-control__option-layout">
                            <div className="wc-block-components-radio-control__label-group">
                                <span id="radio-control-wc-payment-method-options-moneyspace__label" className="wc-block-components-radio-control__label">
                                    <div className="wc-moneyspace-blocks-payment-method__label moneyspace-ins-ktc">
                                        <span className="wc-block-components-payment-method-label">{ktcObj?.label || 'KTC Credit Card'}</span>
                                        <div className="wc-block-components-payment-method-icons">
                                            {ktcObj?.icon && <img className="wc-block-components-payment-method-icon wc-block-components-payment-method-icon--moneyspace" src={ktcObj.icon} alt="moneyspace-ins-ktc" />}
                                        </div>
                                    </div>
                                </span>
                            </div>
                        </div>
                    </label>
                    <div className={ `wc-block-components-radio-control-accordion-content ${ paymentData.selectbank == "KTC" ? "": "hide" }`}>
                        <div id="KTC" className="installment wc-block-components-text-input is-active">
                            <label htmlFor="ktc_permonths">{i18n.MNS_CC_INS_MONTH}</label>
                            <select 
                                name="KTC_permonths" 
                                id="ktc_permonths" 
                                value={paymentData.KTC_permonths} 
                                onChange={handleChange('KTC_permonths')}
                            >
                                {ktcObj && ktcObj.months && ktcObj.months.length > 0 ? (
                                    _.map(ktcObj.months, function(month, index) {
                                        let shouldShow = false;
                                        let optionText = '';
                                        const maxMonth = parseMaxMonth(ktcObj.maxMonth);
                                        
                                        if (msfee == 'include') {
                                            shouldShow = Math.round(amount_total/month) >= 300 && month <= maxMonth;
                                            optionText = `${i18n.MNS_INS || 'Installment'} ${month} ${i18n.MNS_MONTH || 'months'} ( ${formatNum(amount_total/month)} ${i18n.MNS_BAHT || 'THB'} / ${i18n.MNS_MONTH || 'month'} )`;
                                        } else if (msfee == 'exclude') {
                                            var ex_ktc = amount_total / 100 * (ktcObj.rate || 0.8) * month + amount_total;
                                            shouldShow = Math.round(amount_total/month) >= 300 && month <= maxMonth;
                                            optionText = `${i18n.MNS_INS || 'Installment'} ${month} ${i18n.MNS_MONTH || 'months'} ( ${formatNum(ex_ktc/month)} ${i18n.MNS_BAHT || 'THB'} / ${i18n.MNS_MONTH || 'month'} )`;
                                        }
                                        
                                        if (shouldShow) {
                                            return (<option key={`ktc-${month}`} value={month}>{optionText}</option>);
                                        }
                                        return null;
                                    })
                                ) : (
                                    <>
                                        <option value="3">3 months (Test)</option>
                                        <option value="6">6 months (Test)</option>
                                        <option value="12">12 months (Test)</option>
                                    </>
                                )}
                            </select>
                        </div>
                    </div>
                </div>
                <div className="wc-block-components-radio-control-accordion-option">
                    <label className="wc-block-components-radio-control__option" htmlFor="radio-control-wc-payment-method-options-moneyspace-ins-bay">
                        <input id="radio-control-wc-payment-method-options-moneyspace-ins-bay" className="wc-block-components-radio-control__input" type="radio" name="mns_ins_payment" aria-describedby="radio-control-wc-payment-method-options-moneyspace__label" value="BAY" onChange={handleChange('selectbank')} checked={ paymentData.selectbank == "BAY" } />
                        <div className="wc-block-components-radio-control__option-layout">
                            <div className="wc-block-components-radio-control__label-group">
                                <span id="radio-control-wc-payment-method-options-moneyspace__label" className="wc-block-components-radio-control__label">
                                    <div className="wc-moneyspace-blocks-payment-method__label moneyspace-ins-bay">
                                        <span className="wc-block-components-payment-method-label">{bayObj?.label || 'BAY Credit Card'}</span>
                                        <div className="wc-block-components-payment-method-icons">
                                            {bayObj?.icon && <img className="wc-block-components-payment-method-icon wc-block-components-payment-method-icon--moneyspace" src={bayObj.icon} alt="moneyspace-ins-bay" />}
                                        </div>
                                    </div>
                                </span>
                            </div>
                        </div>
                    </label>
                    <div className={ `wc-block-components-radio-control-accordion-content ${ paymentData.selectbank == "BAY" ? "": "hide" }`}>
                        <div id="BAY" className="installment wc-block-components-text-input is-active">
                            <label htmlFor="bay_permonths">{i18n.MNS_CC_INS_MONTH}</label>
                            <select 
                                name="BAY_permonths" 
                                id="bay_permonths" 
                                value={paymentData.BAY_permonths} 
                                onChange={handleChange('BAY_permonths')}
                            >
                                {bayObj && bayObj.months ? (
                                    _.map(bayObj.months, function(month, index) {
                                        let shouldShow = false;
                                        let optionText = '';
                                        const maxMonth = parseMaxMonth(bayObj.maxMonth);
                                        
                                        if (msfee == 'include') {
                                            shouldShow = Math.round(amount_total/month) >= 500 && month <= maxMonth;
                                            optionText = `${i18n.MNS_INS || 'Installment'} ${month} ${i18n.MNS_MONTH || 'months'} ( ${formatNum(amount_total/month)} ${i18n.MNS_BAHT || 'THB'} / ${i18n.MNS_MONTH || 'month'} )`;
                                        } else if (msfee == 'exclude') {
                                            var ex_bay = amount_total / 100 * (bayObj.rate || 0.8) * month + amount_total;
                                            shouldShow = Math.round(amount_total/month) >= 500 && month <= maxMonth;
                                            optionText = `${i18n.MNS_INS || 'Installment'} ${month} ${i18n.MNS_MONTH || 'months'} ( ${formatNum(ex_bay/month)} ${i18n.MNS_BAHT || 'THB'} / ${i18n.MNS_MONTH || 'month'} )`;
                                        }
                                        
                                        if (shouldShow) {
                                            return (<option key={`bay-${month}`} value={month}>{optionText}</option>);
                                        }
                                        return null;
                                    })
                                ) : (
                                    <option value="">No options available</option>
                                )}
                            </select>
                        </div>
                    </div>
                </div>
                <div className="wc-block-components-radio-control-accordion-option">
                    <label className="wc-block-components-radio-control__option" htmlFor="radio-control-wc-payment-method-options-moneyspace-ins-fcy">
                        <input id="radio-control-wc-payment-method-options-moneyspace-ins-fcy" className="wc-block-components-radio-control__input" type="radio" name="mns_ins_payment" aria-describedby="radio-control-wc-payment-method-options-moneyspace__label" value="FCY" onChange={handleChange('selectbank')} checked={ paymentData.selectbank == "FCY" } />
                        <div className="wc-block-components-radio-control__option-layout">
                            <div className="wc-block-components-radio-control__label-group">
                                <span id="radio-control-wc-payment-method-options-moneyspace__label" className="wc-block-components-radio-control__label">
                                    <div className="wc-moneyspace-blocks-payment-method__label moneyspace-ins-fcy">
                                        <span className="wc-block-components-payment-method-label">{fcyObj?.label || 'FCY Credit Card'}</span>
                                        <div className="wc-block-components-payment-method-icons">
                                            {fcyObj?.icon && <img className="wc-block-components-payment-method-icon wc-block-components-payment-method-icon--moneyspace" src={fcyObj.icon} alt="moneyspace-ins-fcy" />}
                                        </div>
                                    </div>
                                </span>
                            </div>
                        </div>
                    </label>
                    <div className={ `wc-block-components-radio-control-accordion-content ${ paymentData.selectbank == "FCY" ? "": "hide" }`}>
                        <div id="FCY" className="installment wc-block-components-text-input is-active">
                            <label htmlFor="fcy_permonths">{i18n.MNS_CC_INS_MONTH}</label>
                            <select 
                                name="FCY_permonths" 
                                id="fcy_permonths" 
                                value={paymentData.FCY_permonths} 
                                onChange={handleChange('FCY_permonths')}
                            >
                                {fcyObj && fcyObj.months ? (
                                    _.map(fcyObj.months, function(month, index) {
                                        let shouldShow = false;
                                        let optionText = '';
                                        const maxMonth = parseMaxMonth(fcyObj.maxMonth);
                                        
                                        if (msfee == 'include') {
                                            shouldShow = Math.round(amount_total/month) >= 300 && month <= maxMonth;
                                            optionText = `${i18n.MNS_INS || 'Installment'} ${month} ${i18n.MNS_MONTH || 'months'} ( ${formatNum(amount_total/month)} ${i18n.MNS_BAHT || 'THB'} / ${i18n.MNS_MONTH || 'month'} )`;
                                        } else if (msfee == 'exclude') {
                                            var ex_fcy = amount_total / 100 * (fcyObj.rate || 1.0) * month + amount_total;
                                            shouldShow = Math.round(amount_total/month) >= 300 && month <= maxMonth;
                                            optionText = `${i18n.MNS_INS || 'Installment'} ${month} ${i18n.MNS_MONTH || 'months'} ( ${formatNum(ex_fcy/month)} ${i18n.MNS_BAHT || 'THB'} / ${i18n.MNS_MONTH || 'month'} )`;
                                        }
                                        
                                        if (shouldShow) {
                                            return (<option key={`fcy-${month}`} value={month}>{optionText}</option>);
                                        }
                                        return null;
                                    })
                                ) : (
                                    <option value="">No options available</option>
                                )}
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>);
        
        return jsxResult;
        
        } catch (renderError) {
            debugError('Error in renderView:', renderError);
            return <div>Error rendering installment options. Please try again.</div>;
        }
    }, [ktcObj, bayObj, fcyObj, i18n, paymentData, amount_total, msfee, parseMaxMonth, formatNum]);

    // Temporarily force render in debug mode for testing
    const shouldRender = checkPrice() || window.location.search.includes('debug=1');
    
    if (shouldRender) {
        return renderView;
    } else {
        return warningPriceLessThanMinimum;
    }

    } catch (error) {
        debugError('CreditCardInstallmentForm Runtime Error:', {
            error: error,
            errorMessage: error.message,
            errorStack: error.stack,
            errorName: error.name,
            props: props
        });
        return <div>Error loading installment form. Please refresh the page. Error: {error.message}</div>;
    }
}

// Wrap with Error Boundary
const CreditCardInstallmentFormWithErrorBoundary = (props) => (
    <ErrorBoundary>
        <CreditCardInstallmentForm {...props} />
    </ErrorBoundary>
);

export default CreditCardInstallmentFormWithErrorBoundary;