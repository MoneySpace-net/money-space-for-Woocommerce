<?php
// wp_enqueue_script("bootstrap-4", MNS_ROOT_URL ."/includes/libs/bootstrap-4.0.0-dist/js/bootstrap.bundle.min.js", array(), "4.0.0", true);
// wp_enqueue_style("bootstrap-4", MNS_ROOT_URL ."/includes/libs/bootstrap-4.0.0-dist/css/bootstrap.min.css", array(), "4.0.0", "" );
// wp_enqueue_style("mns-01", MNS_ROOT_URL ."/includes/themes/mns-01/css/mns-01.css", array(), "1.0.0", "" );

?>
<div class="container" id="installment-form">
    <div class="card">
        <div class="card-body">
            <div>
                <input type="hidden" id="mspay" name="mspay">
                <div class="form-group">
                    <input type="radio" name="bankType" id="bankTypeKTC" value="KTC">
                    <label for="bankTypeKTC">บัตรเคทีซี (KTC)</label>
                    <img alt="" src="<?php echo MNS_ROOT_URL .'/includes/images/installment/KTC0.png'; ?>">
                </div>
                <div class="form-group">
                    <input type="radio" name="bankType" id="bankTypeBAY" value="BAY">
                    <label for="bankTypeBAY">บัตรกรุงศรีฯ วีซ่า , บัตรเซ็นทรัล , บัตรเทสโก้โลตัส</label>
                    <img alt=""  src="<?php echo MNS_ROOT_URL .'includes/images/installment/BAY33050.png'; ?>">
                </div>
                <div class="form-group">
                    <input type="radio" name="bankType" id="bankTypeFCY" value="FCY">
                    <label for="bankTypeFCY">บัตรกรุงศรีเฟิร์สช้อยส์ , บัตรโฮมโปร , บัตรเมกาโฮม</label>
                    <img alt="" src="<?php echo MNS_ROOT_URL .'includes/images/installment/FCY.png'; ?>">
                    
                </div>
                
                <!-- <div class="form-group">
                    <label for="txtCardNumber">Card Number <abbr class="required" title="required">*</abbr></label>
                    <input type="text" class="form-control" v-model="cardNumber" id="txtCardNumber" name="cardNumber" :required="validateCardNumber()"  @keypress="checkCardNumber" placeholder="0000 0000 0000 0000">
                </div>
                <div class="form-group">
                    <label for="txtHolder">Card Holder <abbr class="required" title="required">*</abbr></label>
                    <input type="text" class="form-control" v-model="cardHolder" id="txtHolder" name="cardHolder" :required="validateCardHolder()" @keypress="checkCardName" placeholder="TONY ELSDEN">
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="txtExpDate">Exp Date <abbr class="required" title="required">*</abbr></label>
                            <div class="input-group">
                                <select v-model="expDate" id="txtExpDate" name="cardExpDate" class="form-control" :required="validateCardExpDate()">
                                    <option value="" disabled selected>Month</option>
                                    <option :value="n < 10 ? '0' + n : n" v-for="n in 12" :disabled="n < minCardMonth" :key="n">
                                        {{n < 10 ? '0' + n : n}}
                                    </option>
                                </select>
                                <select v-model="expDateYear" id="txtExpDateYear" name="cardExpDateYear" class="form-control" :required="validateCardExpYear()">
                                    <option value="" disabled selected>Year</option>
                                    <option :value="$index + minCardYear" v-for="(n, $index) in 12" v-bind:key="n">
                                        {{$index + minCardYear}}
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="txtCVV">CVV <abbr class="required" title="required">*</abbr></label>
                            <input type="text" class="form-control" v-model="cardCVV" id="txtCVV" name="cardCVV" @keypress="checkCVV" placeholder="000" :required="validateCardCVV()">
                        </div>
                    </div>
                </div> -->
            </div>
        </div>
    </div>
</div>
<script id="bootstrap-4-js" src="<?php echo  MNS_ROOT_URL ."includes/libs/bootstrap-4.0.0-dist/js/bootstrap.bundle.min.js"; ?>"></script>
<script id="vue-2-js" src="<?php echo  MNS_ROOT_URL ."includes/libs/vue@2/dist/vue.js"; ?>"></script>
<script id="moneyspace-util-js" src="<?php echo  MNS_ROOT_URL ."includes/libs/moneyspace/moneyspace_util.js"; ?>"></script>
<script id="installment-js" src="<?php echo  MNS_ROOT_URL ."includes/js/installment.js"; ?>"></script>