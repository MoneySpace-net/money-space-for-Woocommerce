import { useState, useEffect, useRef, useCallback } from '@wordpress/element';
import '../payment-method/styles.scss';
import { __ } from '@wordpress/i18n';
import _, { map } from 'underscore';

const CreditCardInstallmentForm = (props) => {
    console.log('props', props);
    const model = {
        selectbank: "",
        KTC_permonths: "",
        BAY_permonths: "",
        FCY_permonths: "",
        dirty: false
    };
    const [paymentData, setPaymentData] = useState(model);
    const { ccIns } = props;
    const { cartTotal, currency } = props.billing;
    const { onPaymentSetup, onPaymentProcessing, onCheckoutValidationBeforeProcessing } = props.eventRegistration;
    
    const handleChange = (field) => (event) => {
        setPaymentData({ ...paymentData, [field]: event.target.value, ["dirty"]: true });
        console.log('handleChange paymentData', paymentData);
    };

    const findObj = (key) => {
        return _.find(ccIns, (x) => { return x.code == key; });
    }
    const ktcObj = findObj("ktc");
    const bayObj = findObj("bay");
    const fcyObj = findObj("fcy");
    const amount_total = cartTotal.value / Math.pow(10, currency.minorUnit);
    // console.log('amount_total', amount_total);

    const checkPrice = () => {
        return amount_total > 3000;
    }

    const warningPriceLessThanMinimum = () => {
        return (<div>
            <span style={{ color: "red" }} >The amount of balance must be 3,000.01 baht or more in order to make the installment payment.</span>
        </div>);
    }

    const useValidateCheckout = ({ onCheckoutValidationBeforeProcessing }) => {
        useEffect(() => {
            const unsubscribe = onCheckoutValidationBeforeProcessing(() => {
                if (!checkPrice()) {
                    return {
                        errorMessage: "The amount of balance must be 3,000.01 baht or more in order to make the installment payment."
                    }
                }

                return true;
            });

            return unsubscribe;
        }, []);
    }

    useValidateCheckout({ onCheckoutValidationBeforeProcessing });

    const renderView = () => {
        return (<div className='wc-block-components-credit-card-installment-form'>
            <h2>เลือกการผ่อนชำระ</h2>
            <div className={`wc-block-components-radio-control`}>
                <div class="wc-block-components-radio-control-accordion-option">
                    <label class="wc-block-components-radio-control__option" for="radio-control-wc-payment-method-options-moneyspace-ins-ktc">
                        <input id="radio-control-wc-payment-method-options-moneyspace-ins-ktc" class="wc-block-components-radio-control__input" type="radio" name="mns_ins_payment" aria-describedby="radio-control-wc-payment-method-options-moneyspace__label" value="moneyspace-ins-ktc" onChange={handleChange('selectbank')} checked={ paymentData.selectbank == "moneyspace-ins-ktc" } />
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
                    <div className='wc-block-components-radio-control-accordion-content'>
                        <div id="KTC" class="installment wc-block-components-text-input is-active">
                            <label>จำนวนเดือนผ่อนชำระ</label>
                            <select name="KTC_permonths" id="permonths">
                                {
                                    _.map(ktcObj.months, function(month) {
                                        if ( Math.round(amount_total/month) >= 300 && month <=  ktcObj.maxMonth) {
                                            return (<option value={month}>ผ่อน {month} เดือน ( {amount_total/month} บาท / เดือน )</option>)
                                        }
                                    })
                                }
                            </select>
                        </div>
                    </div>
                </div>
                <div class="wc-block-components-radio-control-accordion-option">
                    <label class="wc-block-components-radio-control__option" for="radio-control-wc-payment-method-options-moneyspace-ins-bay">
                        <input id="radio-control-wc-payment-method-options-moneyspace-ins-bay" class="wc-block-components-radio-control__input" type="radio" name="mns_ins_payment" aria-describedby="radio-control-wc-payment-method-options-moneyspace__label" value="moneyspace-ins-bay" onChange={handleChange('selectbank')} checked={ paymentData.selectbank == "moneyspace-ins-bay" } />
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
                    <div className='wc-block-components-radio-control-accordion-content'>
                        <div id="BAY" class="installment wc-block-components-text-input is-active">
                            <label>จำนวนเดือนผ่อนชำระ</label>
                            <select name="BAY_permonths" id="permonths" >
                                {
                                    _.map(bayObj.months, function(month) {
                                        if ( Math.round(amount_total/month) >= 300 && month <=  bayObj.maxMonth) {
                                            return (<option value={month}>ผ่อน {month} เดือน ( {amount_total/month} บาท / เดือน )</option>)
                                        }
                                    })
                                }
                            </select>
                        </div>
                    </div>
                </div>
                <div class="wc-block-components-radio-control-accordion-option">
                    <label class="wc-block-components-radio-control__option" for="radio-control-wc-payment-method-options-moneyspace-ins-fcy">
                        <input id="radio-control-wc-payment-method-options-moneyspace-ins-fcy" class="wc-block-components-radio-control__input" type="radio" name="mns_ins_payment" aria-describedby="radio-control-wc-payment-method-options-moneyspace__label" value="moneyspace-ins-fcy" onChange={handleChange('selectbank')} checked={ paymentData.selectbank == "moneyspace-ins-fcy" } />
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
                    <div className='wc-block-components-radio-control-accordion-content'>
                        <div id="FCY" class="installment wc-block-components-text-input is-active">
                            <label>จำนวนเดือนผ่อนชำระ</label>
                            <select name="FCY_permonths" id="permonths">
                                {
                                    _.map(fcyObj.months, function(month) {
                                        if ( Math.round(amount_total/month) >= 300 && month <=  fcyObj.maxMonth) {
                                            return (<option value={month}>ผ่อน {month} เดือน ( {amount_total/month} บาท / เดือน )</option>)
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