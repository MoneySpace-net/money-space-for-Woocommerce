// import { usePaymentEventsContext, useCheckoutContext  } from '@woocommerce/base-contexts';
import {useState, useEffect, useRef, useCallback} from '@wordpress/element';
import {useValidateCheckout} from "./../payment-method/hooks";
import '../payment-method/styles.scss';///styles.scss';

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
    var errorFields = [];

    console.log('props', props);
    const { isComplete } = props.checkoutStatus;
    const { ValidationInputError } = props.components;
    const { onCheckoutValidationBeforeProcessing } = props.eventRegistration;

    var checkPaymentMethodCC = false;
    if (document.getElementById('radio-control-wc-payment-method-options-moneyspace') !== null) {
        checkPaymentMethodCC = document.getElementById('radio-control-wc-payment-method-options-moneyspace').checked;
    }

    const FieldValidatorClass = (fieldName) => {
        return formData[fieldName] == "" && formData.dirty == true ? 'has-error' : '';
    }

    const FieldError = (fieldName, errorMsg) => {
        if (formData[fieldName] == "" && formData.dirty == true) {
            errorFields[fieldName] = errorMsg;
            console.log('errorFields', errorFields);
            return (
                <div class="wc-block-components-validation-error" role="alert">
                    <p>{errorMsg}</p></div>
                );
        } else {
            return "";
        }
    }
    
    const minCardMonth = () => {
        if (model.cardYear === model.minCardYear) 
            return new Date().getMonth() + 1;

        return 1;
    };

    const listNumber = [1,2,3,4,5,6,7,8,9,10,11,12];

    useValidateCheckout({
        formData, 
        onCheckoutValidationBeforeProcessing
    });
    
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
        
        if (!/^[0-9]*$/.test(event.key) && event.keyCode != 8) {
            return event.preventDefault();
        }
    }

    const checkCardNumber = (event) => {
        if (!/^[0-9]*$/.test(event.key) && event.keyCode != 8) {
            return event.preventDefault();
        }

        if (formData.ccNo.replaceAll(" ", "").length >= 16 && event.keyCode != 8) {
            return event.preventDefault();
        }
    }

    const handleChange = (field) => (event) => {
        if (field == "ccNo") {
            setFormData({ ...formData, [field]: cc_format(event.target.value), ["dirty"]: true  });
        } else if (field == "ccName") {
            setFormData({ ...formData, [field]: event.target.value.toUpperCase(), ["dirty"]: true });
        } else {
            setFormData({ ...formData, [field]: event.target.value, ["dirty"]: true });
        }
    };

    // const validateCardNumber = () => {
    //     return this.formData.cardNumber.trim().length == 0 && checkPaymentMethodCC ? true: false;
    // }

    // const validateCardHolder = () => {
    //     return this.formData.cardHolder.trim().length == 0 && checkPaymentMethodCC ? true: false;
    // }

    // const validateCardExpDate = () => {
    //     return this.formData.expDate.length == 0 && checkPaymentMethodCC ? true: false;
    // }

    // const validateCardExpYear = () => {
    //     return this.formData.expDateYear.length == 0 && checkPaymentMethodCC ? true: false;
    // }

    const validateCardCVV = () => {
        return formData.ccCVV.length == 0 && checkPaymentMethodCC ? true: false;
    }

    return (<div className='wc-block-components-credit-card-form'>
        <div className={ `wc-block-components-text-input wc-block-components-credit-form is-active ${ FieldValidatorClass('ccNo') }` }>
            <input type="text" value={formData.ccNo} onChange={handleChange('ccNo')} id="txtCardNumber" name="cardNumber" required="validateCardNumber()" onKeyDown={checkCardNumber} placeholder="0000 0000 0000 0000" />
            <label for="creditCard">Card Number *</label>
            {FieldError('ccNo', 'Please fill in Card Number')}
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
            <select value={formData.ccExpYear} onChange={handleChange('ccExpYear')} id="ccExpYear" name="ccExpYear" required="validateCardExpYear()">
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
        </div>
    </div>);

    // return !isComplete ? (
    // <div class="container ms-box" id="credit-card-form">
    //     <div class="card">
    //         <div class="card-body">
    //             <div>
    //                 <input type="hidden" id="mspay" name="mspay" />
    //                 <div class="form-group">
    //                     <label for="txtCardNumber">Card Number <abbr class="required" title="required">*</abbr></label>
    //                     <input type="text" class="form-control" value={formData.ccNo} onChange={handleChange('ccNo')} id="txtCardNumber" name="cardNumber" required="validateCardNumber()" onKeyDown={checkCardNumber} placeholder="0000 0000 0000 0000" />
    //                 </div>
    //                 <div class="form-group">
    //                     <label for="txtHolder">Card Holder <abbr class="required" title="required">*</abbr></label>
    //                     <input type="text" class="form-control"  value={formData.ccName} onChange={handleChange('ccName')} id="txtHolder" name="cardHolder" required="validateCardHolder()" keypress="checkCardName" placeholder="TONY ELSDEN" />
    //                 </div>
    //                 <div class="row">
    //                     <div class="col-md-6">
    //                         <div class="form-group">
    //                             <label for="txtExpDate">Exp Date <abbr class="required" title="required">*</abbr></label>
    //                             <div class="input-group">
    //                                 <select value={formData.ccExpMonth} onChange={handleChange('ccExpMonth')} id="txtExpDate" name="cardExpDate" class="form-control" required="validateCardExpDate()">
    //                                     <option value="" disabled selected>Month</option>
    //                                     {
    //                                         listNumber.map((x)=>(
    //                                             <option value={x} disabled={x < minCardMonth()}>
    //                                                 {x < 10 ? '0'+x : x}
    //                                             </option>
    //                                         ))
    //                                     }
                                        
    //                                 </select>
    //                                 <select value={formData.ccExpYear} onChange={handleChange('ccExpYear')} id="txtExpDateYear" name="cardExpDateYear" class="form-control" required="validateCardExpYear()">
    //                                     <option value="" disabled selected>Year</option>
    //                                     {
    //                                         listNumber.map((x, index)=>(
    //                                             <option value={index + formData.minCardYear}>
    //                                                 {index + formData.minCardYear}
    //                                             </option>
    //                                         ))
    //                                     }
    //                                 </select>
    //                             </div>
    //                         </div>
    //                     </div>
    //                     <div class="col-md-6">
    //                         <div class="form-group">
    //                             <label for="txtCVV">CVV <abbr class="required" title="required">*</abbr></label>
    //                             <input type="password" class="form-control" value={formData.ccCVV} onChange={handleChange('ccCVV')} id="txtCVV" name="cardCVV" maxLength={3} onKeyDown={checkCVV} placeholder="000" required={validateCardCVV()} />
    //                         </div>
    //                     </div>
    //                 </div>
    //             </div>
    //         </div>
    //     </div>
    // </div>) : null;
}
 
export default CreditCardForm;