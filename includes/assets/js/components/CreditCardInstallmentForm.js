import { useState, useEffect, useRef, useCallback } from '@wordpress/element';
import '../payment-method/styles.scss';
import { __ } from '@wordpress/i18n';
import _, { map } from 'underscore';

const CreditCardInstallmentForm = (props) => {
    const model = {
        selectbank: "",
        KTC_permonths: "",
        BAY_permonths: "",
        FCY_permonths: "",
        dirty: false
    };
    const [paymentData, setPaymentData] = useState(model);
    const { ccIns, msfee } = props;
    const { cartTotal, currency } = props.billing;
    const { onPaymentSetup, onPaymentProcessing, onCheckoutValidationBeforeProcessing } = props.eventRegistration;
    const { i18n } = props;
    
    const handleChange = (field) => (event) => {
        if (field == "selectbank" && event.target.value == "KTC")
            setPaymentData({ ...paymentData, [field]: event.target.value, ["dirty"]: true, ["KTC_permonths"]: "3", ["BAY_permonths"]: "", ["FCY_permonths"]: "" });
        else if (field == "selectbank" && event.target.value == "BAY")
            setPaymentData({ ...paymentData, [field]: event.target.value, ["dirty"]: true, ["KTC_permonths"]: "", ["BAY_permonths"]: "3", ["FCY_permonths"]: "" });
        else if (field == "selectbank" && event.target.value == "FCY")
            setPaymentData({ ...paymentData, [field]: event.target.value, ["dirty"]: true, ["KTC_permonths"]: "", ["BAY_permonths"]: "0", ["FCY_permonths"]: "3" });
        else
            setPaymentData({ ...paymentData, [field]: event.target.value, ["dirty"]: true });
    };

    const findObj = (key) => {
        return _.find(ccIns, (x) => { return x.code == key; });
    }
    const ktcObj = findObj("ktc");
    const bayObj = findObj("bay");
    const fcyObj = findObj("fcy");
    const amount_total = cartTotal.value / Math.pow(10, currency.minorUnit);

    const checkPrice = () => {
        return amount_total > 3000;
    }

    const formatNum = (val) => {
        var number = parseFloat(val).toFixed(2);
        return number.toString().replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,");
    }

    const warningPriceLessThanMinimum = () => {
        return (<div>
            <span style={{ color: "red" }} >The amount of balance must be 3,000.01 baht or more in order to make the installment payment.</span>
        </div>);
    }

    const useValidateCheckout = ({ paymentData, onCheckoutValidationBeforeProcessing }) => {
        useEffect(() => {
            if (paymentData.selectbank == "KTC")
                handleChange("KTC_permonths");

            if (paymentData.selectbank == "BAY")
                handleChange("BAY_permonths");
            
            if (paymentData.selectbank == "FCY")
                handleChange("FCY_permonths");
            
            const unsubscribe = onCheckoutValidationBeforeProcessing(() => {
                if (!checkPrice()) {
                    return {
                        errorMessage: "The amount of balance must be 3,000.01 baht or more in order to make the installment payment."
                    }
                }

                if (paymentData.selectbank == "") {
                    return {
                        errorMessage: "Please choose bank type for installment."
                    }
                }

                return true;
            });

            return unsubscribe;
        }, [paymentData]);
    }

    useValidateCheckout({ paymentData, onCheckoutValidationBeforeProcessing });

    const useProcessPayment = ({paymentData, onPaymentProcessing}) => {
        useEffect(() => {
            const unsubscribe = onPaymentProcessing(() => {
                const response = {
                    meta: {
                        paymentMethodData: {
                            selectbank: paymentData.selectbank,
                            KTC_permonths: paymentData.KTC_permonths,
                            BAY_permonths: paymentData.BAY_permonths,
                            FCY_permonths: paymentData.FCY_permonths
                        }
                    }
                }
                return {type: "success", ...response};
            });

            return unsubscribe;
        }, [paymentData]);
    }
    useProcessPayment({paymentData, onPaymentProcessing});

    const renderView = () => {
        return (<div className='wc-block-components-credit-card-installment-form'>
            <h2>{i18n.MNS_CC_INS_TITLE}</h2>
            <div className={`wc-block-components-radio-control`}>
                <div class="wc-block-components-radio-control-accordion-option">
                    <label class="wc-block-components-radio-control__option" for="radio-control-wc-payment-method-options-moneyspace-ins-ktc">
                        <input id="radio-control-wc-payment-method-options-moneyspace-ins-ktc" class="wc-block-components-radio-control__input" type="radio" name="mns_ins_payment" aria-describedby="radio-control-wc-payment-method-options-moneyspace__label" value="KTC" onChange={handleChange('selectbank')} checked={ paymentData.selectbank == "KTC" } />
                        <div class="wc-block-components-radio-control__option-layout">
                            <div class="wc-block-components-radio-control__label-group">
                                <span id="radio-control-wc-payment-method-options-moneyspace__label" class="wc-block-components-radio-control__label">
                                    <div class="wc-moneyspace-blocks-payment-method__label moneyspace-ins-ktc">
                                        <span class="wc-block-components-payment-method-label">{ktcObj.label}</span>
                                        <div class="wc-block-components-payment-method-icons">
                                            <img class="wc-block-components-payment-method-icon wc-block-components-payment-method-icon--moneyspace" src={ktcObj.icon} alt="moneyspace-ins-ktc" />
                                        </div>
                                    </div>
                                </span>
                            </div>
                        </div>
                    </label>
                    <div className={ `wc-block-components-radio-control-accordion-content ${ paymentData.selectbank == "KTC" ? "": "hide" }`}>
                        <div id="KTC" class="installment wc-block-components-text-input is-active">
                            <label>{i18n.MNS_CC_INS_MONTH}</label>
                            <select name="KTC_permonths" id="permonths" value={paymentData.KTC_permonths} onChange={handleChange('KTC_permonths')}>
                                {
                                    _.map(ktcObj.months, function(month, index) {
                                        if (msfee == 'include') {
                                            return Math.round(amount_total/month) >= 300 && month <=  ktcObj.maxMonth ? 
                                                (<option value={month} selected={ index==0 }>{i18n.MNS_INS} {month} {i18n.MNS_MONTH} ( { formatNum(amount_total/month) } {i18n.MNS_BAHT} / {i18n.MNS_MONTH} )</option>) : (<></>)
                                        } else if (msfee == 'exclude') {
                                            var ex_ktc = amount_total / 100 * ktcObj.rate * month + amount_total;
                                            return Math.round(amount_total/month) >= 300 && month <=  ktcObj.maxMonth ? 
                                                (<option value={month} selected={ index==0 }>{i18n.MNS_INS} {month} {i18n.MNS_MONTH} ( { formatNum(ex_ktc/month) } {i18n.MNS_BAHT} / {i18n.MNS_MONTH} )</option>) : (<></>)
                                        }
                                    })
                                }
                            </select>
                        </div>
                    </div>
                </div>
                <div class="wc-block-components-radio-control-accordion-option">
                    <label class="wc-block-components-radio-control__option" for="radio-control-wc-payment-method-options-moneyspace-ins-bay">
                        <input id="radio-control-wc-payment-method-options-moneyspace-ins-bay" class="wc-block-components-radio-control__input" type="radio" name="mns_ins_payment" aria-describedby="radio-control-wc-payment-method-options-moneyspace__label" value="BAY" onChange={handleChange('selectbank')} checked={ paymentData.selectbank == "BAY" } />
                        <div class="wc-block-components-radio-control__option-layout">
                            <div class="wc-block-components-radio-control__label-group">
                                <span id="radio-control-wc-payment-method-options-moneyspace__label" class="wc-block-components-radio-control__label">
                                    <div class="wc-moneyspace-blocks-payment-method__label moneyspace-ins-bay">
                                        <span class="wc-block-components-payment-method-label">{bayObj.label}</span>
                                        <div class="wc-block-components-payment-method-icons">
                                            <img class="wc-block-components-payment-method-icon wc-block-components-payment-method-icon--moneyspace" src={bayObj.icon} alt="moneyspace-ins-bay" />
                                        </div>
                                    </div>
                                </span>
                            </div>
                        </div>
                    </label>
                    <div className={ `wc-block-components-radio-control-accordion-content ${ paymentData.selectbank == "BAY" ? "": "hide" }`}>
                        <div id="BAY" class="installment wc-block-components-text-input is-active">
                            <label>{i18n.MNS_CC_INS_MONTH}</label>
                            <select name="BAY_permonths" id="permonths" value={paymentData.BAY_permonths} onChange={handleChange('BAY_permonths')} >
                                {
                                    _.map(bayObj.months, function(month, index) {
                                        if (msfee == 'include') {
                                            return Math.round(amount_total/month) >= 500 && month <=  bayObj.maxMonth ? 
                                                (<option value={month} selected={ index==0 }>{i18n.MNS_INS} {month} {i18n.MNS_MONTH} ( { formatNum(amount_total/month) } {i18n.MNS_BAHT} / {i18n.MNS_MONTH} )</option>) : (<></>)
                                        } else if (msfee == 'exclude') {
                                            var ex_bay = amount_total / 100 * bayObj.rate * month + amount_total;
                                            return Math.round(amount_total/month) >= 500 && month <=  bayObj.maxMonth ? 
                                                (<option value={month} selected={ index==0 }>{i18n.MNS_INS} {month} {i18n.MNS_MONTH} ( { formatNum(ex_bay/month) } {i18n.MNS_BAHT} / {i18n.MNS_MONTH} )</option>) : (<></>)
                                        }
                                    })
                                }
                            </select>
                        </div>
                    </div>
                </div>
                <div class="wc-block-components-radio-control-accordion-option">
                    <label class="wc-block-components-radio-control__option" for="radio-control-wc-payment-method-options-moneyspace-ins-fcy">
                        <input id="radio-control-wc-payment-method-options-moneyspace-ins-fcy" class="wc-block-components-radio-control__input" type="radio" name="mns_ins_payment" aria-describedby="radio-control-wc-payment-method-options-moneyspace__label" value="FCY" onChange={handleChange('selectbank')} checked={ paymentData.selectbank == "FCY" } />
                        <div class="wc-block-components-radio-control__option-layout">
                            <div class="wc-block-components-radio-control__label-group">
                                <span id="radio-control-wc-payment-method-options-moneyspace__label" class="wc-block-components-radio-control__label">
                                    <div class="wc-moneyspace-blocks-payment-method__label moneyspace-ins-fcy">
                                        <span class="wc-block-components-payment-method-label">{fcyObj.label}</span>
                                        <div class="wc-block-components-payment-method-icons">
                                            <img class="wc-block-components-payment-method-icon wc-block-components-payment-method-icon--moneyspace" src={fcyObj.icon} alt="moneyspace-ins-fcy" />
                                        </div>
                                    </div>
                                </span>
                            </div>
                        </div>
                    </label>
                    <div className={ `wc-block-components-radio-control-accordion-content ${ paymentData.selectbank == "FCY" ? "": "hide" }`}>
                        <div id="FCY" class="installment wc-block-components-text-input is-active">
                            <label>{i18n.MNS_CC_INS_MONTH}</label>
                            <select name="FCY_permonths" id="permonths" value={paymentData.FCY_permonths} onChange={handleChange('FCY_permonths')} >
                                {
                                    _.map(fcyObj.months, function(month, index) {
                                        if (msfee == 'include') {
                                            return Math.round(amount_total/month) >= 300 && month <=  fcyObj.maxMonth ? 
                                                (<option value={month} selected={ index==0 }>{i18n.MNS_INS} {month} {i18n.MNS_MONTH} ( { formatNum(amount_total/month) } {i18n.MNS_BAHT} / {i18n.MNS_MONTH} )</option>) : (<></>)
                                        } else if (msfee == 'exclude') {
                                            var ex_fcy = amount_total / 100 * fcyObj.rate * month + amount_total;
                                            return Math.round(amount_total/month) >= 300 && month <=  fcyObj.maxMonth ? 
                                                (<option value={month} selected={ index==0 }>{i18n.MNS_INS} {month} {i18n.MNS_MONTH} ( { formatNum(ex_fcy/month) } {i18n.MNS_BAHT} / {i18n.MNS_MONTH} )</option>) : (<></>)
                                        }
                                    })
                                }
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>);
    }

    return checkPrice() ? renderView() : warningPriceLessThanMinimum();
}

export default CreditCardInstallmentForm;