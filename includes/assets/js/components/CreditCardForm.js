import {useState, useEffect, useRef, useCallback} from '@wordpress/element';
import '../payment-method/styles.scss';///styles.scss';

const CreditCardForm = (props) => {
    const model = {
        ccNo: '',
        ccName: '',
        ccExpMonth: '',
        ccExpYear: '',
        ccCVV: '',
        cardYear: '',
        minCardYear: new Date().getFullYear()
    };

    var checkPaymentMethodCC = false;
    if (document.getElementById('radio-control-wc-payment-method-options-moneyspace') !== null) {
        checkPaymentMethodCC = document.getElementById('radio-control-wc-payment-method-options-moneyspace').checked;
    }

    const [formData, setFormData] = useState(model);

    const minCardMonth = () => {
        if (model.cardYear === model.minCardYear) 
            return new Date().getMonth() + 1;

        return 1;
    };

    const listNumber = [1,2,3,4,5,6,7,8,9,10,11,12];

    useEffect(() => {
        // var ccFormat = cc_format();
        // console.log('cc_format', ccFormat);
        
        // formData.ccNo = ccFormat;
        // setFormData(formData);
        // setFormData(formData => ({ ...formData, ccNo: ccFormat }));
        // setFormData({ ...formData, ccNo: cc_format() });
        // setFormData({formData});
        console.log('formData', formData);
        console.log('props', props);
    },[formData]);

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
            setFormData({ ...formData, [field]: cc_format(event.target.value) });
        } else if (field == "ccName") {
            setFormData({ ...formData, [field]: event.target.value.toUpperCase() });
        } else {
            setFormData({ ...formData, [field]: event.target.value });
        }
    };

    const validateCardNumber = () => {
        return this.formData.cardNumber.trim().length == 0 && checkPaymentMethodCC ? true: false;
    }

    const validateCardHolder = () => {
        return this.formData.cardHolder.trim().length == 0 && checkPaymentMethodCC ? true: false;
    }

    const validateCardExpDate = () => {
        return this.formData.expDate.length == 0 && checkPaymentMethodCC ? true: false;
    }

    const validateCardExpYear = () => {
        return this.formData.expDateYear.length == 0 && checkPaymentMethodCC ? true: false;
    }

    const validateCardCVV = () => {
        return formData.ccCVV.length == 0 && checkPaymentMethodCC ? true: false;
    }

    return (
    <div class="container ms-box" id="credit-card-form">
        <div class="card">
            <div class="card-body">
                <div>
                    <input type="hidden" id="mspay" name="mspay" />
                    <div class="form-group">
                        <label for="txtCardNumber">MNS_CC_NO <abbr class="required" title="required">*</abbr></label>
                        <input type="text" class="form-control" value={formData.ccNo} onChange={handleChange('ccNo')} id="txtCardNumber" name="cardNumber" required="validateCardNumber()" onKeyDown={checkCardNumber} placeholder="0000 0000 0000 0000" />
                    </div>
                    <div class="form-group">
                        <label for="txtHolder">MNS_CC_NAME <abbr class="required" title="required">*</abbr></label>
                        <input type="text" class="form-control"  value={formData.ccName} onChange={handleChange('ccName')} id="txtHolder" name="cardHolder" required="validateCardHolder()" keypress="checkCardName" placeholder="TONY ELSDEN" />
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="txtExpDate">MNS_CC_EXP <abbr class="required" title="required">*</abbr></label>
                                <div class="input-group">
                                    <select value={formData.ccExpMonth} onChange={handleChange('ccExpMonth')} id="txtExpDate" name="cardExpDate" class="form-control" required="validateCardExpDate()">
                                        <option value="" disabled selected>Month</option>
                                        {
                                            listNumber.map((x)=>(
                                                <option value={x} disabled={x < minCardMonth()}>
                                                    {x < 10 ? '0'+x : x}
                                                </option>
                                            ))
                                        }
                                        
                                    </select>
                                    <select value={formData.ccExpYear} onChange={handleChange('ccExpYear')} id="txtExpDateYear" name="cardExpDateYear" class="form-control" required="validateCardExpYear()">
                                        <option value="" disabled selected>Year</option>
                                        {
                                            listNumber.map((x, index)=>(
                                                <option value={index + formData.minCardYear}>
                                                    {index + formData.minCardYear}
                                                </option>
                                            ))
                                        }
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="txtCVV">MNS_CC_CVV <abbr class="required" title="required">*</abbr></label>
                                <input type="password" class="form-control" value={formData.ccCVV} onChange={handleChange('ccCVV')} id="txtCVV" name="cardCVV" maxLength={3} onKeyDown={checkCVV} placeholder="000" required={validateCardCVV()} />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>);
}
 
export default CreditCardForm;