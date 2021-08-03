<?php
wc_enqueue_js("
if (document.getElementsByName('checkout')[0] !== undefined) {
    document.getElementsByName('checkout')[0].noValidate = false;
}

if (document.getElementById('order_review') !== null) {
    document.getElementById('order_review').noValidate = false;
}
");

?>

<div class="container" id="credit-card-form">
    <div class="card">
        <div class="card-body">
            <div>
                <input type="hidden" id="mspay" name="mspay" :value="mspay_message">
                <div class="form-group">
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
                </div>
            </div>
        </div>
    </div>
</div>
<?php 
// wp_enqueue_script( "bootstrap-4", MNS_ROOT_URL ."includes/libs/bootstrap-4.0.0-dist/js/bootstrap.bundle.min.js", array(), "4.0.0", true);
// wp_enqueue_script( "vue-2", MNS_ROOT_URL ."includes/libs/vue@2/dist/vue.js", array(), "2.0.0", true);
// wp_enqueue_script( "moneyspace-util", MNS_ROOT_URL ."includes/libs/moneyspace/moneyspace_util.js", array(), "1.0.0", true);
// wp_enqueue_script( "creditcard", MNS_ROOT_URL ."includes/js/creditcard.js", array(), "1.0.0", true);
?>
<script id="bootstrap-4-js" src="<?php echo  MNS_ROOT_URL ."includes/libs/bootstrap-4.0.0-dist/js/bootstrap.bundle.min.js"; ?>"></script>
<script id="vue-2-js" src="<?php echo  MNS_ROOT_URL ."includes/libs/vue@2/dist/vue.js"; ?>"></script>
<script id="moneyspace-util-js" src="<?php echo  MNS_ROOT_URL ."includes/libs/moneyspace/moneyspace_util.js"; ?>"></script>
<script id="creditcard-js" src="<?php echo  MNS_ROOT_URL ."includes/js/creditcard.js"; ?>"></script>
<link id="mns-01-css" rel="stylesheet" href="<?php echo MNS_ROOT_URL ."includes/themes/mns-01/css/mns-01.css"; ?>">
