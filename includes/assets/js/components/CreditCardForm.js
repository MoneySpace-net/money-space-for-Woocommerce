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
    var checkPaymentMethodCC = false;
    var errorFields = [];
    const listNumber = [1,2,3,4,5,6,7,8,9,10,11,12];
    console.log('props', props);
    const [formData, setFormData] = useState(model);
    const { onPaymentSetup, onPaymentProcessing, onCheckoutValidationBeforeProcessing } = props.eventRegistration;
    
    if (document.getElementById('radio-control-wc-payment-method-options-moneyspace') !== null) {
        checkPaymentMethodCC = document.getElementById('radio-control-wc-payment-method-options-moneyspace').checked;
    }

    const useValidateCheckout = (
        {
            formData,
            onCheckoutValidationBeforeProcessing
        }, errorFields) => {
        useEffect(() => {
            const unsubscribe = onCheckoutValidationBeforeProcessing(() => {
                if (formData.dirty == false) {
                    setFormData({ ...formData, ["dirty"]: true });
                    return {
                        errorMessage: "Please fill in Pay by Card 3D secured section before placing your order."
                    }
                }

                if (Object.keys(errorFields).length > 0){
                    return {
                        errorMessage: "Please check Pay by Card 3D secured section before placing your order."
                    }
                }
                return true;
            });
            return unsubscribe;
        }, [formData]); //formData, errorFields
    }

    const usePaymentSetup = ({formData, onPaymentSetup}) => {
        useEffect(() => {
            const unsubscribe = onPaymentSetup(() => {
                return formData;
            });

            return unsubscribe;
        }, [formData]);
    }
    usePaymentSetup({formData, onPaymentSetup});

    const useProcessPayment = ({formData, onPaymentProcessing}) => {
        useEffect(() => {
            const unsubscribe = onPaymentProcessing(() => {
                const response = {
                    meta: {
                        paymentMethodData: {
                            cardNumber: formData.ccNo.replaceAll(" ", ""),
                            cardHolder: formData.ccName,
                            cardExpDate: formData.ccExpMonth,
                            cardExpDateYear: formData.ccExpYear,
                            cardCVV: formData.ccCVV
                        }
                    }
                }
                return {type: "success", ...response};
            });

            return unsubscribe;
        }, [formData]);
    }
    useProcessPayment({formData, onPaymentProcessing});

    useValidateCheckout({
        formData,
        onCheckoutValidationBeforeProcessing
    }, errorFields);

    const FieldValidatorClass = (fieldName) => {
        if (fieldName == "ccNo" && formData[fieldName].replaceAll(" ", "").length < 16 && formData.dirty == true) {
            return 'has-error';
        } else if (fieldName == "ccCVV" && formData[fieldName].length < 3 && formData.dirty == true) {
            return 'has-error';
        } else {
            return formData[fieldName] == "" && formData.dirty == true ? 'has-error' : '';
        }
    }

    const FieldCreditCardError = (fieldName, errorMsg) => {
        if (formData[fieldName].replaceAll(" ", "").length < 16 && formData[fieldName].replaceAll(" ", "").length > 0 && formData.dirty == true) {
            errorFields[fieldName] = errorMsg;
            return ErrorTemplate(errorMsg);
        } else {
            return "";
        }
    }

    const FieldCVVError = (fieldName, errorMsg) => {
        if (formData[fieldName].length < 3 && formData[fieldName].length > 0 && formData.dirty == true) {
            errorFields[fieldName] = errorMsg;
            return ErrorTemplate(errorMsg);
        } else {
            return "";
        }
    }

    const FieldError = (fieldName, errorMsg) => {
        if (formData[fieldName] == "" && formData.dirty == true) {
            errorFields[fieldName] = errorMsg;
            return ErrorTemplate(errorMsg);
        } else {
            return "";
        }
    }

    const ErrorTemplate = (errorMsg) => {
        return  (
            <div class="wc-block-components-validation-error" role="alert">
                <p>{errorMsg}</p></div>
            );
    }
    
    const minCardMonth = () => {
        if (model.cardYear === model.minCardYear) 
            return new Date().getMonth() + 1;

        return 1;
    };
    
    const cc_format = (value) => {
        // var value = formData.ccNo;
        var v = value.replace(/\s+/g, '').replace(/[^0-9]/gi, '')
        var matches = v.match(/\d{4,16}/g);
        var match = matches && matches[0] || ''
        var parts = []
    
        for (var i=0, len=match.length; i<len; i+=4) {
            parts.push(match.substring(i, i+4))
        }
    
        if (parts.length) {
            return parts.join(' ')
        } else {
            return value
        }
    }

    const checkCVV = (event) => {
        if (!/^[0-9]*$/.test(event.key) && ![8,67,86,88].includes(event.keyCode)) {
            return event.preventDefault();
        }
    }

    const checkCardNumber = (event) => {
        if (!/^[0-9]*$/.test(event.key) && ![8,67,86,88].includes(event.keyCode)) {
            return event.preventDefault();
        }

        if (formData.ccNo.replaceAll(" ", "").length >= 16 && ![8,67,86,88].includes(event.keyCode)) {
            return event.preventDefault();
        }
    }

    const handleChange = (field) => (event) => {
        if (field == "ccNo") {
            if (/^[0-9]*$/.test(event.target.value)) {
                setFormData({ ...formData, [field]: cc_format(event.target.value), ["dirty"]: true  });
            }
        } else if (field == "ccName") {
            setFormData({ ...formData, [field]: event.target.value.toUpperCase(), ["dirty"]: true });
        } else {
            setFormData({ ...formData, [field]: event.target.value, ["dirty"]: true });
        }
    };

    const validateCardCVV = () => {
        return formData.ccCVV.length == 0 && checkPaymentMethodCC ? true: false;
    }

    return (<div className='wc-block-components-credit-card-form'>
        <div className={ `wc-block-components-text-input wc-block-components-credit-form is-active ${ FieldValidatorClass('ccNo') }` }>
            <input type="text" value={formData.ccNo} onChange={handleChange('ccNo')} id="txtCardNumber" name="cardNumber" required="validateCardNumber()" onKeyDown={checkCardNumber} placeholder="0000 0000 0000 0000" />
            <label for="creditCard">Card Number *</label>
            {FieldError('ccNo', 'Please fill in Card Number')}
            {FieldCreditCardError('ccNo', 'Please check your Card Number')}
        </div>
        <div className={ `wc-block-components-text-input wc-block-components-credit-form is-active ${ FieldValidatorClass('ccName') }` }>
            <input type="text" value={formData.ccName} onChange={handleChange('ccName')} id="txtHolder" name="cardHolder" required="validateCardHolder()" keypress="checkCardName" placeholder="TONY ELSDEN"/>
            <label for="cardHolder">Card Holder *</label>
            {FieldError('ccName', 'Please fill in Card Holder')}
        </div>
        <div className={ `wc-block-components-text-input is-active ${ FieldValidatorClass('ccExpMonth') }` }>
            <select value={formData.ccExpMonth} onChange={handleChange('ccExpMonth')} id="txtExpDate" name="cardExpDate" required="validateCardExpDate()">
                <option value="" disabled selected>Month</option>
                {
                    listNumber.map((x)=>(
                        <option value={x} disabled={x < minCardMonth()}>
                            {x < 10 ? '0'+x : x}
                        </option>
                    ))
                }
            </select>
            <label for="ccExpMonth">Exp Month *</label>
            {FieldError('ccExpMonth', 'Please fill in Exp Month')}
        </div>
        <div className={ `wc-block-components-text-input is-active ${ FieldValidatorClass('ccExpYear') }` }>
            <select value={formData.ccExpYear} onChange={handleChange('ccExpYear')} id="ccExpYear" name="cardExpDateYear" required="validateCardExpYear()">
                <option value="" disabled selected>Month</option>
                {
                    listNumber.map((x, index)=>(
                        <option value={index + formData.minCardYear}>
                            {index + formData.minCardYear}
                        </option>
                    ))
                }
            </select>
            <label for="ccExpYear">Exp Year *</label>
            {FieldError('ccExpYear', 'Please fill in Card Exp Year')}
        </div>
        <div className={ `wc-block-components-text-input wc-block-components-credit-form is-active ${ FieldValidatorClass('ccCVV') }` }>
            <input type="password" value={formData.ccCVV} onChange={handleChange('ccCVV')} id="txtCVV" name="cardCVV" maxLength={3} onKeyDown={checkCVV} placeholder="000" required={validateCardCVV()} />
            <label for="cardCVV">CVV *</label>
            {FieldError('ccCVV', 'Please fill in CVV')}
            {FieldCVVError('ccCVV', 'Please check CVV')}
        </div>
    </div>);
}
 
export default CreditCardForm;