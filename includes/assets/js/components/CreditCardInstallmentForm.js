import React, { useState, useEffect, useRef, useCallback, useMemo, Component } from '@wordpress/element';
import '../payment-method/styles.scss';
import { __ } from '@wordpress/i18n';
import _, { map } from 'underscore';
import { useSelect } from '@wordpress/data';

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
        console.error('CreditCardInstallmentForm Error Boundary Caught:', {
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
                console.error('Error in handleChange:', changeError);
            }
        };

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
                        return {
                            errorMessage: "The amount of balance must be 3,000.01 baht or more in order to make the installment payment."
                        };
                    }

                    // Validate bank selection
                    if (!paymentData.selectbank || paymentData.selectbank === "") {
                        return {
                            errorMessage: "Please choose a bank for installment payment."
                        };
                    }

                    // Validate installment months based on selected bank
                    if (paymentData.selectbank === "KTC") {
                        if (!paymentData.KTC_permonths || paymentData.KTC_permonths === "") {
                            return {
                                errorMessage: "Please select the number of installment months for KTC."
                            };
                        }
                        
                        // Validate KTC minimum amount per month
                        const monthlyAmount = amount_total / parseInt(paymentData.KTC_permonths);
                        if (monthlyAmount < 300) {
                            return {
                                errorMessage: "KTC installment requires a minimum of 300 THB per month."
                            };
                        }
                    } else if (paymentData.selectbank === "BAY") {
                        if (!paymentData.BAY_permonths || paymentData.BAY_permonths === "") {
                            return {
                                errorMessage: "Please select the number of installment months for BAY."
                            };
                        }
                        
                        // Validate BAY minimum amount per month
                        const monthlyAmount = amount_total / parseInt(paymentData.BAY_permonths);
                        if (monthlyAmount < 500) {
                            return {
                                errorMessage: "BAY installment requires a minimum of 500 THB per month."
                            };
                        }
                    } else if (paymentData.selectbank === "FCY") {
                        if (!paymentData.FCY_permonths || paymentData.FCY_permonths === "") {
                            return {
                                errorMessage: "Please select the number of installment months for FCY."
                            };
                        }
                        
                        // Validate FCY minimum amount per month
                        const monthlyAmount = amount_total / parseInt(paymentData.FCY_permonths);
                        if (monthlyAmount < 300) {
                            return {
                                errorMessage: "FCY installment requires a minimum of 300 THB per month."
                            };
                        }
                    }

                    // Validate that selected months are within allowed range
                    const selectedBank = paymentData.selectbank;
                    const bankObj = selectedBank === "KTC" ? ktcObj : 
                                   selectedBank === "BAY" ? bayObj : 
                                   selectedBank === "FCY" ? fcyObj : null;
                    
                    if (bankObj) {
                        const maxMonth = parseMaxMonth(bankObj.maxMonth);
                        const selectedMonths = parseInt(
                            selectedBank === "KTC" ? paymentData.KTC_permonths :
                            selectedBank === "BAY" ? paymentData.BAY_permonths :
                            selectedBank === "FCY" ? paymentData.FCY_permonths : "0"
                        );
                        
                        if (selectedMonths > maxMonth) {
                            return {
                                errorMessage: `Maximum installment period for ${selectedBank} is ${maxMonth} months.`
                            };
                        }
                        
                        if (bankObj.months && !bankObj.months.includes(selectedMonths)) {
                            return {
                                errorMessage: `${selectedMonths} months installment is not available for ${selectedBank}.`
                            };
                        }
                    }

                    // All validation passed
                    return true;
                });

                return unsubscribe;
            } catch (validateError) {
                console.error('Error in useValidateCheckout:', validateError);
                return () => {}; // Return empty cleanup function
            }
        }, [paymentData, onCheckoutValidation, checkPrice, amount_total, ktcObj, bayObj, fcyObj, parseMaxMonth]);
    };

    useValidateCheckout({ paymentData, onCheckoutValidation });

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
                    
                    const paymentMethodData = {
                        selectbank: selectedBank,
                        installment_months: selectedMonths,
                        // Include individual bank fields for backward compatibility
                        KTC_permonths: paymentData.KTC_permonths,
                        BAY_permonths: paymentData.BAY_permonths,
                        FCY_permonths: paymentData.FCY_permonths,
                        // Add calculated amounts for reference
                        total_amount: amount_total,
                        monthly_amount: selectedMonths ? (amount_total / parseInt(selectedMonths)).toFixed(2) : "0"
                    };

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
                console.error('Error in useProcessPayment:', processError);
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
                            <select name="KTC_permonths" id="ktc_permonths" value={paymentData.KTC_permonths} onChange={handleChange('KTC_permonths')}>
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
                            <select name="BAY_permonths" id="bay_permonths" value={paymentData.BAY_permonths} onChange={handleChange('BAY_permonths')} >
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
                            <select name="FCY_permonths" id="fcy_permonths" value={paymentData.FCY_permonths} onChange={handleChange('FCY_permonths')} >
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
            console.error('Error in renderView:', renderError);
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
        console.error('CreditCardInstallmentForm Runtime Error:', {
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