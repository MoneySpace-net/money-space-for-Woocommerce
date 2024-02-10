import { useState, useEffect, useRef, useCallback } from '@wordpress/element';
import '../payment-method/styles.scss';
import { __ } from '@wordpress/i18n';

const CreditCardInstallmentForm = (props) => {
    console.log('props', props);
    const paymentData = {
        selectbank: "",
        KTC_permonths: "",
        BAY_permonths: "",
        FCY_permonths: ""
    };
    const { cartTotal, currency } = props.billing;
    const { onPaymentSetup, onPaymentProcessing, onCheckoutValidationBeforeProcessing } = props.eventRegistration;

    const checkPrice = () => {
        cartTotal.value
        var total = cartTotal.value / Math.pow(10, currency.minorUnit);
        return total > 3000;
    }

    const warningPriceLessThanMinimum = () => {
        return (<div>
            <span style={{ color: "red" }} >The amount of balance must be 3,000.01 baht or more in order to make the installment payment.</span>
        </div>);
    }

    const useValidateCheckout = ({ onCheckoutValidationBeforeProcessing }) => {
        useEffect(() => {
            console.log('useValidateCheckout', checkPrice());
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
                    <label class="wc-block-components-radio-control__option" for="radio-control-wc-payment-method-options-moneyspace">
                        <input id="radio-control-wc-payment-method-options-moneyspace" class="wc-block-components-radio-control__input" type="radio" name="radio-control-wc-payment-method-options" aria-describedby="radio-control-wc-payment-method-options-moneyspace__label" value="moneyspace" checked="" />
                        <div class="wc-block-components-radio-control__option-layout">
                            <div class="wc-block-components-radio-control__label-group">
                                <span id="radio-control-wc-payment-method-options-moneyspace__label" class="wc-block-components-radio-control__label">
                                    <div class="wc-moneyspace-blocks-payment-method__label moneyspace">
                                        <span class="wc-block-components-payment-method-label">เคทีซี (KTC)</span>
                                        <div class="wc-block-components-payment-method-icons">
                                            <img class="wc-block-components-payment-method-icon wc-block-components-payment-method-icon--moneyspace" src="https://a.moneyspace.net/static/img/type/Master_VISA_JCB_UNION_180.png" alt="moneyspace" />
                                        </div>
                                    </div>
                                </span>
                            </div>
                        </div>
                    </label>
                </div>
                <div class="wc-block-components-radio-control-accordion-option">
                    <label class="wc-block-components-radio-control__option" for="radio-control-wc-payment-method-options-moneyspace">
                        <input id="radio-control-wc-payment-method-options-moneyspace" class="wc-block-components-radio-control__input" type="radio" name="radio-control-wc-payment-method-options" aria-describedby="radio-control-wc-payment-method-options-moneyspace__label" value="moneyspace" checked="" />
                        <div class="wc-block-components-radio-control__option-layout">
                            <div class="wc-block-components-radio-control__label-group">
                                <span id="radio-control-wc-payment-method-options-moneyspace__label" class="wc-block-components-radio-control__label">
                                    <div class="wc-moneyspace-blocks-payment-method__label moneyspace">
                                        <span class="wc-block-components-payment-method-label">กรุงศรีฯ วีซ่า , เซ็นทรัล , เทสโก้โลตัส</span>
                                        <div class="wc-block-components-payment-method-icons">
                                            <img class="wc-block-components-payment-method-icon wc-block-components-payment-method-icon--moneyspace" src="https://a.moneyspace.net/static/img/type/Master_VISA_JCB_UNION_180.png" alt="moneyspace" />
                                        </div>
                                    </div>
                                </span>
                            </div>
                        </div>
                    </label>
                </div>
                <div class="wc-block-components-radio-control-accordion-option">
                    <label class="wc-block-components-radio-control__option" for="radio-control-wc-payment-method-options-moneyspace">
                        <input id="radio-control-wc-payment-method-options-moneyspace" class="wc-block-components-radio-control__input" type="radio" name="radio-control-wc-payment-method-options" aria-describedby="radio-control-wc-payment-method-options-moneyspace__label" value="moneyspace" checked="" />
                        <div class="wc-block-components-radio-control__option-layout">
                            <div class="wc-block-components-radio-control__label-group">
                                <span id="radio-control-wc-payment-method-options-moneyspace__label" class="wc-block-components-radio-control__label">
                                    <div class="wc-moneyspace-blocks-payment-method__label moneyspace">
                                        <span class="wc-block-components-payment-method-label">กรุงศรีเฟิร์สช้อยส์ , โฮมโปร , เมกาโฮม</span>
                                        <div class="wc-block-components-payment-method-icons">
                                            <img class="wc-block-components-payment-method-icon wc-block-components-payment-method-icon--moneyspace" src="https://a.moneyspace.net/static/img/type/Master_VISA_JCB_UNION_180.png" alt="moneyspace" />
                                        </div>
                                    </div>
                                </span>
                            </div>
                        </div>
                    </label>
                </div>
            </div>
        </div>);
    }

    return checkPrice() ? renderView() : warningPriceLessThanMinimum();
}

export default CreditCardInstallmentForm;