import {useState, useEffect, useRef, useCallback} from '@wordpress/element';

const CreditCardForm = (props) => {
    return (
    <div class="container ms-box" id="credit-card-form">
        <div class="card">
            <div class="card-body">
                <div>
                    <input type="hidden" id="mspay" name="mspay" />
                    <div class="form-group">
                        <label for="txtCardNumber">MNS_CC_NO <abbr class="required" title="required">*</abbr></label>
                        <input type="text" class="form-control" v-model="cardNumber" id="txtCardNumber" name="cardNumber" required="validateCardNumber()"  keypress="checkCardNumber" placeholder="0000 0000 0000 0000" />
                    </div>
                    <div class="form-group">
                        <label for="txtHolder">MNS_CC_NAME <abbr class="required" title="required">*</abbr></label>
                        <input type="text" class="form-control" v-model="cardHolder" id="txtHolder" name="cardHolder" required="validateCardHolder()" keypress="checkCardName" placeholder="TONY ELSDEN" />
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="txtExpDate">MNS_CC_EXP <abbr class="required" title="required">*</abbr></label>
                                <div class="input-group">
                                    <select v-model="expDate" id="txtExpDate" name="cardExpDate" class="form-control" required="validateCardExpDate()">
                                        <option value="" disabled selected>Month</option>
                                        <option value="n < 10 ? '0' + n : n" v-for="n in 12" disabled="n < minCardMonth" key="n">
                                            {/* // {{n < 10 ? '0' + n : n}} */}
                                        </option>
                                    </select>
                                    <select v-model="expDateYear" id="txtExpDateYear" name="cardExpDateYear" class="form-control" required="validateCardExpYear()">
                                        <option value="" disabled selected>Year</option>
                                        <option value="$index + minCardYear" v-for="(n, $index) in 12" key="n">
                                            {/* {{$index + minCardYear}} */}
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="txtCVV">MNS_CC_CVV <abbr class="required" title="required">*</abbr></label>
                                <input type="text" class="form-control" v-model="cardCVV" id="txtCVV" name="cardCVV" keypress="checkCVV" placeholder="000" required="validateCardCVV()" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>);
}
 
export default CreditCardForm;