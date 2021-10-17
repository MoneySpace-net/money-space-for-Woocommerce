<?php

// date_default_timezone_set(MNS_TIME_ZONE);


global $wpdb;

global $woocommerce;

$order = wc_get_order($pid);
$redirect_url = get_site_url() . "/ms/cancel/" . $order->id;

if ($order && $pid) {

    $payment_gateway_id = MNS_ID;
    $payment_gateway_qr_id = MNS_ID_QRPROM;
    $payment_gateway_installment_id = MNS_ID_INSTALLMENT;

    
    $payment_gateways = WC_Payment_Gateways::instance();

    $payment_gateway = $payment_gateways->payment_gateways()[$payment_gateway_id];
    $payment_gateway_qr = $payment_gateways->payment_gateways()[$payment_gateway_qr_id];
    $payment_gateway_installment = $payment_gateways->payment_gateways()[$payment_gateway_installment_id];
    $gateways = WC()->payment_gateways->get_available_payment_gateways();
    $ms_secret_id = $gateways['moneyspace']->settings['secret_id'];
    $ms_secret_key = $gateways['moneyspace']->settings['secret_key'];
    $ms_order_select = $gateways['moneyspace']->settings['order_status_if_success'];
    $ms_stock_setting = $gateways['moneyspace']->settings['ms_stock_setting'];
    $ms_template_payment = $gateways['moneyspace']->settings['ms_template_payment'];

    $ms_time = date("YmdHis");
    $MNS_transaction_orderid = get_post_meta($order->id, 'MNS_transaction_orderid', true);
    $MNS_transaction = get_post_meta($order->id, 'MNS_transaction', true);
    $order_amount = $order->get_total();
    $MNS_PAYMENT_TYPE = get_post_meta($order->id, 'MNS_PAYMENT_TYPE', true);
    $MNS_PAYMENT_KEY = get_post_meta($order->id, 'MNS_PAYMENT_KEY', true);

    if ((strlen($MNS_PAYMENT_KEY) > 9999 && isset($MNS_PAYMENT_KEY) && $MNS_PAYMENT_KEY != "")
    || (!isset($MNS_PAYMENT_KEY) && $MNS_PAYMENT_KEY == "")) {
        wp_redirect(wc_get_account_endpoint_url('orders'));
    }

    if ($MNS_PAYMENT_TYPE == "Card") {
        $ms_title = $gateways['moneyspace']->settings['title'];
    } else if ($MNS_PAYMENT_TYPE == "Qrnone") {

        $ms_title = $gateways['moneyspace_qrprom']->settings['title'];
        $MNS_QR_TIME = get_post_meta($order->id, 'MNS_QR_TIME', true);
        $auto_cancel = $payment_gateway_qr->settings['auto_cancel'];

        if(empty($auto_cancel)){
            $limit_time = 1200;
        }else{
            $limit_time = $auto_cancel;
        }
        

        if ((time() - $MNS_QR_TIME) > $limit_time){
            wp_redirect($redirect_url);
        }


    } else if ($MNS_PAYMENT_TYPE == "Installment") {
        $ms_title = $gateways['moneyspace_installment']->settings['title'];
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?php _e($ms_title); ?></title>
    
    <script id="moneyspace-util-js" src="<?php _e(MNS_ROOT_URL ."includes/libs/moneyspace/moneyspace_util.js"); ?>"></script>

    <style>
        .MuiPaper-root.MuiCard-root {
            width: 100%;
        }

        .MuiGrid-root.MuiGrid-item.MuiGrid-grid-xs-12 > div > p > img {
            display: inline-flex;
        }
        
        .MuiGrid-root.MuiGrid-item.MuiGrid-grid-xs-12 > div > p {
            margin-left: 10%;
            margin-right: 10%;
        }
    </style>
</head>
<body>
<?php wp_head(); ?>
<?php if ($ms_template_payment == "1") { ?>
    <div align="center">
    

        
        <?php if ($MNS_PAYMENT_TYPE == "Qrnone") { 
            
            ?>
            <div id="moneyspace-payment" lang="eng" ms-title="<?php _e($ms_title); ?> " ms-key="<?php _e($MNS_PAYMENT_KEY); ?>"></div>
            <br>

            <h3 style="text-align: center;">
                QR Code จะหมดอายุวันที่ : <?php _e(date('d/m/Y H:i', $MNS_QR_TIME + $limit_time)); ?>
            </h3>
            <h3 id="time" style="text-align: center;"></h3>
            <script>
                function startTimer(duration, display) {
                    var timer = duration, minutes, seconds;
                    var countDownDate = new Date();
                    countDownDate.setMinutes(countDownDate.getMinutes()+ (duration/60) );
                    setInterval(function () {
                        var now = new Date().getTime();
                        var distance = countDownDate - now;

                        minutes = parseInt(timer / 60, 10);
                        seconds = parseInt(timer % 60, 10);
                
                        minutes = minutes < 10 ? "0" + minutes : minutes;
                        seconds = seconds < 10 ? "0" + seconds : seconds;
                        
                        timer -= 1;
                        if (timer === 0) {
                            window.location="<?php _e($redirect_url); ?>";
                        } else if (timer > 0) {
                            // Time calculations for days, hours, minutes and seconds
                            var days = Math.floor(distance / (1000 * 60 * 60 * 24));
                            var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                            var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                            var seconds = Math.floor((distance % (1000 * 60)) / 1000);
                            display.innerHTML = ("QR Code จะหมดอายุในอีก "+minutes + " นาที " + seconds + " วินาที ");
                        }
                    }, 1000);
                }
                
                var duration = <?php _e($limit_time); ?>,
                    display = document.querySelector("#time");
                startTimer(duration, display);
            </script>

        <?php 
        
    } else { 
        $mscard = get_post_meta($order->id, 'MNS_CARD', true);
        $mscard_ext = explode("|", base64_decode($mscard));
        $mskey = get_post_meta( $order->id, 'MNS_PAYMENT_KEY', true);
        $mspay = get_post_meta( $order->id, 'MNS_PAYMENT_PAY', true);

        $cardNumber = $mscard_ext[0];
        $cardHolder = $mscard_ext[1];
        $cardExpDate = $mscard_ext[2];
        $cardExpDateYear = $mscard_ext[3];
        $cardCVV = $mscard_ext[4];
        delete_post_meta($order->id, 'MNS_PAYMENT_KEY', $mskey);
        delete_post_meta($order->id, 'MNS_PAYMENT_PAY', $mspay);
        delete_post_meta($order->id, 'MNS_CARD', $mscard);
            
        $customStyle = ("
            input[type=text]{
                box-sizing: content-box !important;
                background-color: transparent !important;
            }
        
            .MuiOutlinedInput-notchedOutline {
                background: transparent !important;
            }
        
            .MuiInputLabel-outlined.MuiInputLabel-shrink {
                left: -0.6em !important;
            }
        
            .MuiInput-underline:before, .MuiInput-underline:after {
                border-bottom: 0px !important;
            }
        
            .MuiSelect-select:focus {
                background-color: transparent !important;
            }
        
            .MuiGrid-root.MuiGrid-container.MuiGrid-justify-xs-center > div > img {
                width: 10em !important;
                height: auto !important;
            }
        
            .MuiGrid-root.MuiGrid-container.MuiGrid-justify-xs-center {
                padding-bottom: 10px !important;
            }");

            wp_register_style( 'custom-css-handle', false );
            wp_enqueue_style( 'custom-css-handle' );
            wp_add_inline_style( 'custom-css-handle', $customStyle );
        
            wc_enqueue_js( "
                bindMSPay();
                function bindMSPay() {
                    Moneyspace_util.getPay('".$mskey."').then(function(res) {
                        var mspay = Moneyspace_util.addEncryptedData('".$cardHolder."', '".$cardNumber."', '".$cardExpDate."', '".$cardExpDateYear."', '".$cardCVV."', res.t);
                        document.getElementById('mspay').value = mspay;
                    }).then(function(res) {
                        document.getElementById('pay_form').submit();
                    });
                }
            ");
            ?>
            <form action="<?php _e(MNS_API_URL_PAY); ?>" id="pay_form" method="post" target="_top">
                <input type="hidden" id="mskey" name="mskey" value="<?php _e($mskey); ?>">
                <input type="hidden" id="mspay" name="mspay" >
                <div class="btn-submit-payment" style="display: none;">
                    <button type="submit" onclick="submit()" id="submit-form"></button>
                </div>
            </form>

        <?php } ?>
    </div>
<?php wp_enqueue_script( 'payment_form_pay', MNS_PAYMENT_FORM_JS, array(), false, true ); ?>
<?php 
}
else if ($ms_template_payment == "2"){ 
    
    ?>
    
    <div align="center">
        <div id="moneyspace-payment"
             template="2"
             lang="eng"
             ms-title="<?php _e($ms_title); ?>"
             ms-key="<?php _e($MNS_PAYMENT_KEY); ?>"
             description="false">
        </div>
    </div>
<?php wp_enqueue_script( 'payment_pay', MNS_PAYMENT_JS, array(), false, true ); ?>
<?php 
} else { ?>
    <div align="left">
        <div id="moneyspace-payment" lang="eng" ms-title="<?php _e($ms_title); ?>" ms-key="<?php _e($MNS_PAYMENT_KEY); ?>"></div>
        <br>
        <?php if ($MNS_PAYMENT_TYPE == "Qrnone") { ?>


            <h3 style="text-align: center;">
                QR Code จะหมดอายุวันที่ : <?php _e(date('d/m/Y H:i', $MNS_QR_TIME + $limit_time)); ?>
            </h3>
            <h3 id="time" style="text-align: center;"></h3>
            <script>
                function startTimer(duration, display) {
                    var timer = duration, minutes, seconds;
                    var countDownDate = new Date();
                    countDownDate.setMinutes(countDownDate.getMinutes()+ (duration/60) );
                    setInterval(function () {
                        var now = new Date().getTime();
                        var distance = countDownDate - now;

                        minutes = parseInt(timer / 60, 10);
                        seconds = parseInt(timer % 60, 10);
                
                        minutes = minutes < 10 ? "0" + minutes : minutes;
                        seconds = seconds < 10 ? "0" + seconds : seconds;

                        timer -= 1;
                        if (timer == 0) {
                            window.location="<?php _e($redirect_url); ?>";
                        } else if (timer > 0) { 
                            var days = Math.floor(distance / (1000 * 60 * 60 * 24));
                            var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                            var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                            var seconds = Math.floor((distance % (1000 * 60)) / 1000);
                            display.innerHTML = ("QR Code จะหมดอายุในอีก "+minutes + " นาที " + seconds + " วินาที ");
                        }
                    }, 1000);
                }
                
                window.onload = function () {
                    var duration = <?php _e($limit_time); ?>,
                        display = document.querySelector("#time");
                    startTimer(duration, display);
                };
            </script>

        <?php } ?>
    </div>
<?php wp_enqueue_script( 'payment_form_pay', MNS_PAYMENT_FORM_JS, array(), false, true ); ?>
<?php 
} ?>
</body>
<?php wp_footer(); ?>
</html>

