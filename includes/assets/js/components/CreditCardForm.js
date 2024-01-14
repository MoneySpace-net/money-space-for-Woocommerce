import { forEach } from '@woocommerce/dependency-extraction-webpack-plugin/assets/packages';
import {useState, useEffect, useRef, useCallback} from '@wordpress/element';

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

    const [formData, setFormData] = useState(model);

    const minCardMonth = () => {
        if (model.cardYear === model.minCardYear) 
            return new Date().getMonth() + 1;

        return 1;
    };
    const listNumber = [1,2,3,4,5,6,7,8,9,10,11,12];

    const handleChange = (field) => (event) => {
        setFormData({ ...formData, [field]: event.target.value });
    };

    return (
    <div class="container ms-box" id="credit-card-form">
        <div class="card">
            <div class="card-body">
                <div>
                    <input type="hidden" id="mspay" name="mspay" />
                    <div class="form-group">
                        <label for="txtCardNumber">MNS_CC_NO <abbr class="required" title="required">*</abbr></label>
                        <input type="text" class="form-control" value={formData.ccNo} onChange={handleChange('ccNo')} id="txtCardNumber" name="cardNumber" required="validateCardNumber()"  keypress="checkCardNumber" placeholder="0000 0000 0000 0000" />
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
                                <input type="text" class="form-control" value={formData.ccCVV} onChange={handleChange('ccCVV')} id="txtCVV" name="cardCVV" keypress="checkCVV" placeholder="000" required="validateCardCVV()" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>);
}
 
export default CreditCardForm;