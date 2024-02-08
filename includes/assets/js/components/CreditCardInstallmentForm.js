import {useState, useEffect, useRef, useCallback} from '@wordpress/element';
import '../payment-method/styles.scss';
import {__} from '@wordpress/i18n';

const CreditCardInstallmentForm = (props) => {
    console.log('props', props);
    const {cartTotal, currency} = props.billing;
    const { onPaymentSetup, onPaymentProcessing, onCheckoutValidationBeforeProcessing } = props.eventRegistration;

    const checkPrice = () => {
        cartTotal.value
        var total = cartTotal.value/Math.pow(10, currency.minorUnit);
        return total > 3000;
    }

    const warningPriceLessThanMinimum = () => {
        return (<div>
            <span style={{ color: "red" }} >The amount of balance must be 3,000.01 baht or more in order to make the installment payment.</span>
        </div>);
    }

    const useValidateCheckout = ({onCheckoutValidationBeforeProcessing}) => {
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

    useValidateCheckout({onCheckoutValidationBeforeProcessing});

    const renderView = () => {
        return (<div>

        </div>);
    }

    return checkPrice() ? renderView() : warningPriceLessThanMinimum();
}

export default CreditCardInstallmentForm;