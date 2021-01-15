<?php

global $wpdb;

global $woocommerce;

$order = wc_get_order($pid);

if ($order && $pid) {
    $payment_gateway_id = MS_ID;
    $payment_gateways = WC_Payment_Gateways::instance();
    $payment_gateway = $payment_gateways->payment_gateways()[$payment_gateway_id];
    $gateways = WC()->payment_gateways->get_available_payment_gateways();
    $ms_secret_id = $gateways['moneyspace']->settings['secret_id'];
    $ms_secret_key = $gateways['moneyspace']->settings['secret_key'];
    $ms_order_select = $gateways['moneyspace']->settings['order_status_if_success'];
    $ms_stock_setting = $gateways['moneyspace']->settings['ms_stock_setting'];
    $ms_template_payment = $gateways['moneyspace']->settings['ms_template_payment'];

    $ms_time = date("YmdHis");
    $MS_transaction_orderid = get_post_meta($order->id, 'MS_transaction_orderid', true);
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
    <div align="left">
        <div id="moneyspace-payment" lang="eng" ms-title="<?= $ms_title ?> " ms-key="<?= $MS_PAYMENT_KEY ?>"></div>
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
    </div>
    <script type="text/javascript" src="<?= MS_PAYMENT_FORM_JS ?>"></script>
<?php } ?>
</body>
</html>

