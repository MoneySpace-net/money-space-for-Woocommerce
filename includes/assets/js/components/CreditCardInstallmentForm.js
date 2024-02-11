import { useState, useEffect, useRef, useCallback } from '@wordpress/element';
import '../payment-method/styles.scss';
import { __ } from '@wordpress/i18n';

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
    console.log('ccInsIcons', ccIns, ccIns.filter(x => x.code == "ktc")[0].icon);

    const handleChange = (field) => (event) => {
        setFormData({ ...formData, [field]: event.target.value, ["dirty"]: true });
    };

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
                    <label class="wc-block-components-radio-control__option" for="radio-control-wc-payment-method-options-moneyspace-ins-ktc">
                        <input id="radio-control-wc-payment-method-options-moneyspace-ins-ktc" class="wc-block-components-radio-control__input" type="radio" name="radio-control-wc-payment-method-options-ins-ktc" aria-describedby="radio-control-wc-payment-method-options-moneyspace__label" value="moneyspace-ins-ktc"  checked="true" />
                        <div class="wc-block-components-radio-control__option-layout">
                            <div class="wc-block-components-radio-control__label-group">
                                <span id="radio-control-wc-payment-method-options-moneyspace__label" class="wc-block-components-radio-control__label">
                                    <div class="wc-moneyspace-blocks-payment-method__label moneyspace-ins-ktc">
                                        <span class="wc-block-components-payment-method-label">{ccIns.filter(x => x.code == "ktc")[0].label}</span>
                                        <div class="wc-block-components-payment-method-icons">
                                            <img class="wc-block-components-payment-method-icon wc-block-components-payment-method-icon--moneyspace" src={ccIns.filter(x => x.code == "ktc")[0].icon} alt="moneyspace-ins-ktc" />
                                        </div>
                                    </div>
                                </span>
                            </div>
                        </div>
                    </label>
                    <div className='wc-block-components-radio-control-accordion-content'>
                        <h1>test</h1>
                    </div>
                </div>
                <div class="wc-block-components-radio-control-accordion-option">
                    <label class="wc-block-components-radio-control__option" for="radio-control-wc-payment-method-options-moneyspace-ins-bay">
                        <input id="radio-control-wc-payment-method-options-moneyspace-ins-bay" class="wc-block-components-radio-control__input" type="radio" name="radio-control-wc-payment-method-options-ins-bay" aria-describedby="radio-control-wc-payment-method-options-moneyspace__label" value="moneyspace-ins-bay" checked="" />
                        <div class="wc-block-components-radio-control__option-layout">
                            <div class="wc-block-components-radio-control__label-group">
                                <span id="radio-control-wc-payment-method-options-moneyspace__label" class="wc-block-components-radio-control__label">
                                    <div class="wc-moneyspace-blocks-payment-method__label moneyspace-ins-bay">
                                        <span class="wc-block-components-payment-method-label">{ccIns.filter(x => x.code == "bay")[0].label}</span>
                                        <div class="wc-block-components-payment-method-icons">
                                            <img class="wc-block-components-payment-method-icon wc-block-components-payment-method-icon--moneyspace" src={ccIns.filter(x => x.code == "bay")[0].icon} alt="moneyspace-ins-bay" />
                                        </div>
                                    </div>
                                </span>
                            </div>
                        </div>
                    </label>
                </div>
                <div class="wc-block-components-radio-control-accordion-option">
                    <label class="wc-block-components-radio-control__option" for="radio-control-wc-payment-method-options-moneyspace-ins-fcy">
                        <input id="radio-control-wc-payment-method-options-moneyspace-ins-fcy" class="wc-block-components-radio-control__input" type="radio" name="radio-control-wc-payment-method-options-ins-fcy" aria-describedby="radio-control-wc-payment-method-options-moneyspace__label" value="moneyspace-ins-fcy" checked="" />
                        <div class="wc-block-components-radio-control__option-layout">
                            <div class="wc-block-components-radio-control__label-group">
                                <span id="radio-control-wc-payment-method-options-moneyspace__label" class="wc-block-components-radio-control__label">
                                    <div class="wc-moneyspace-blocks-payment-method__label moneyspace-ins-fcy">
                                        <span class="wc-block-components-payment-method-label">{ccIns.filter(x => x.code == "fcy")[0].label}</span>
                                        <div class="wc-block-components-payment-method-icons">
                                            <img class="wc-block-components-payment-method-icon wc-block-components-payment-method-icon--moneyspace" src={ccIns.filter(x => x.code == "fcy")[0].icon} alt="moneyspace-ins-fcy" />
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