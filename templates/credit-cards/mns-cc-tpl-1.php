<?php
wc_enqueue_js("
if (document.getElementsByName('checkout')[0] !== undefined) {
    document.getElementsByName('checkout')[0].noValidate = false;
}

if (document.getElementById('order_review') !== null) {
    document.getElementById('order_review').noValidate = false;
}
");

function render_creditcard() {
?>

<div class="container ms-box" id="credit-card-form">
    <div class="card">
        <div class="card-body">
            <div>
                <input type="hidden" id="mspay" name="mspay" :value="mspay_message">
                <div class="form-group">
                    <label for="txtCardNumber">Card Number <abbr class="required" title="required">*</abbr></label>
                    <input type="text" class="form-control" v-model="cardNumber" id="txtCardNumber" name="cardNumber" :required="validateCardNumber()"  v-on:keypress="checkCardNumber" placeholder="0000 0000 0000 0000">
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
<script>
    if (typeof createCreditCard === 'function') 
        createCreditCard();
</script>
<?php 
}
render_creditcard();
add_action('mns_credit_card_add_script', 'add_script', 10, 1);
function add_script() {
    
    wp_enqueue_script( "bootstrap-4", MNS_ROOT_URL ."includes/libs/bootstrap-4.6.0-dist/js/bootstrap.bundle.min.js", array(), "4.0.0", true);
    wp_enqueue_script( "vue-2", MNS_ROOT_URL ."includes/libs/vue@2/dist/vue.min.js", array(), "2.0.0", true);
    wp_enqueue_script( "moneyspace-util", MNS_ROOT_URL ."includes/libs/moneyspace/moneyspace_util.js", array(), "1.0.0", true);
    wp_enqueue_script( "creditcard", MNS_ROOT_URL ."includes/js/creditcard.min.js", array("bootstrap-4", "vue-2", "moneyspace-util"), "1.0.0", true);
    wp_enqueue_style( "mns-01", MNS_ROOT_URL ."includes/themes/mns-01/css/mns-01.css", array(), "1.0.0", "all" );

    return;
}
do_action( 'mns_credit_card_add_script' );

?>
