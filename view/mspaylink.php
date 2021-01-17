<?php

date_default_timezone_set("Asia/Bangkok");

global $wpdb;

global $woocommerce;

$order = wc_get_order($pid);

if ($order && $pid) {

    $payment_gateway_id = MS_ID;
    $payment_gateway_qr_id = MS_ID_QRPROM;
    $payment_gateway_installment_id = MS_ID_INSTALLMENT;

    
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
    $MS_transaction_orderid = get_post_meta($order->id, 'MS_transaction_orderid', true);
    $MS_transaction = get_post_meta($order->id, 'MS_transaction', true);
    $order_amount = $order->get_total();
    $MS_PAYMENT_TYPE = get_post_meta($order->id, 'MS_PAYMENT_TYPE', true);
    $MS_PAYMENT_KEY = get_post_meta($order->id, 'MS_PAYMENT_KEY', true);

    if (strlen($MS_PAYMENT_KEY) > 9999 && isset($MS_PAYMENT_KEY) && $MS_PAYMENT_KEY != "") {
        wp_redirect(wc_get_account_endpoint_url('orders'));
    } else if (!isset($MS_PAYMENT_KEY) && $MS_PAYMENT_KEY == "") {
        wp_redirect(wc_get_account_endpoint_url('orders'));
    }

    if ($MS_PAYMENT_TYPE == "Card") {
        $ms_title = $gateways['moneyspace']->settings['title'];
    } else if ($MS_PAYMENT_TYPE == "Qrnone") {

        $ms_title = $gateways['moneyspace_qrprom']->settings['title'];
        $MS_QR_TIME = get_post_meta($order->id, 'MS_QR_TIME', true);
        $auto_cancel = $payment_gateway_qr->settings['auto_cancel'];

        if(empty($auto_cancel)){
            $limit_time = 1200;
        }else{
            $limit_time = $auto_cancel;
        }
        

        if ((time() - $MS_QR_TIME) > $limit_time){
            wp_redirect(get_site_url() . "/ms/cancel/" . $order->id);
        }


    } else if ($MS_PAYMENT_TYPE == "Installment") {
        $ms_title = $gateways['moneyspace_installment']->settings['title'];
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?= $ms_title ?></title>
</head>
<body>
<?php if ($ms_template_payment == "1") { ?>
    <div align="center">
        <div id="moneyspace-payment" lang="eng" ms-title="<?= $ms_title ?> " ms-key="<?= $MS_PAYMENT_KEY ?>"></div>
        <br>
        <?php if ($MS_PAYMENT_TYPE == "Qrnone") { ?>


            <h3>
                QR Code จะหมดอายุวันที่ : <?=date('d/m/Y H:i', $MS_QR_TIME + $limit_time);?>
            </h3>

            <script>
                                    function startTimer(duration, display) {
                                        var timer = duration, minutes, seconds;
                                        setInterval(function () {
                                            minutes = parseInt(timer / 60, 10);
                                            seconds = parseInt(timer % 60, 10);
                                    
                                            minutes = minutes < 10 ? "0" + minutes : minutes;
                                            seconds = seconds < 10 ? "0" + seconds : seconds;
                                    
                                          
                                    
                                            if (--timer < 0) {
                                                timer = duration;
                                                window.location="<?=get_site_url() . "/ms/cancel/" . $order->id?>";
                                            }
                                        }, 1000);
                                    }
                                    
                                    window.onload = function () {
                                        var fiveMinutes = <?=$limit_time?>,
                                            display = document.querySelector("#time");
                                        startTimer(fiveMinutes, display);
                                    };
            </script>

        <?php } ?>
    </div>
    <script type="text/javascript" src="<?= MS_PAYMENT_FORM_JS ?>"></script>
<?php }else if ($ms_template_payment == "2"){ ?>
    <div align="center">
        <div id="moneyspace-payment"
             template="2"
             lang="eng"
             ms-title="<?= $ms_title ?>"
             ms-key="<?= $MS_PAYMENT_KEY ?>"
             description="false">
        </div>
    </div>
    <script type="text/javascript" src="<?= MS_PAYMENT_JS ?>"></script>
<?php } else { ?>
    <div align="left">
        <div id="moneyspace-payment" lang="eng" ms-title="<?= $ms_title ?>" ms-key="<?= $MS_PAYMENT_KEY ?>"></div>
        <br>
        <?php if ($MS_PAYMENT_TYPE == "Qrnone") { ?>


            <h3>
                QR Code จะหมดอายุวันที่ : <?=date('d/m/Y H:i', $MS_QR_TIME + $limit_time);?>
            </h3>

            <script>
                                    function startTimer(duration, display) {
                                        var timer = duration, minutes, seconds;
                                        setInterval(function () {
                                            minutes = parseInt(timer / 60, 10);
                                            seconds = parseInt(timer % 60, 10);
                                    
                                            minutes = minutes < 10 ? "0" + minutes : minutes;
                                            seconds = seconds < 10 ? "0" + seconds : seconds;
                                    
                                          
                                    
                                            if (--timer < 0) {
                                                timer = duration;
                                                window.location="<?=get_site_url() . "/ms/cancel/" . $order->id?>";
                                            }
                                        }, 1000);
                                    }
                                    
                                    window.onload = function () {
                                        var fiveMinutes = <?=$limit_time?>,
                                            display = document.querySelector("#time");
                                        startTimer(fiveMinutes, display);
                                    };
            </script>

        <?php } ?>
    </div>
    <script type="text/javascript" src="<?= MS_PAYMENT_FORM_JS ?>"></script>
<?php } ?>
</body>
</html>

