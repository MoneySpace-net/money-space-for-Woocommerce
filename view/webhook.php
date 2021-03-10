<?php

global $wpdb;

global $woocommerce;

echo "Webhook !!!!!!!!!!";


if (isset($_POST["transectionID"])) {

    $getorderid = $_POST["orderid"];
    preg_match_all('!\d+!', $getorderid, $arroid);
    $order = wc_get_order($arroid[0][0]);


    $payment_gateway_id = MS_ID;
    $payment_gateway_qr_id = MS_ID_QRPROM;
    $payment_gateway_installment_id = MS_ID_INSTALLMENT;

    $payment_gateways = WC_Payment_Gateways::instance();
    $payment_gateway = $payment_gateways->payment_gateways()[$payment_gateway_id];
    $payment_gateway_qr = $payment_gateways->payment_gateways()[$payment_gateway_qr_id];
    $payment_gateway_installment = $payment_gateways->payment_gateways()[$payment_gateway_installment_id];

    $gateways = WC()->payment_gateways->get_available_payment_gateways();


    $ms_secret_id = $payment_gateway->settings['secret_id'];
    $ms_secret_key = $payment_gateway->settings['secret_key'];
    $ms_stock_setting = $payment_gateway->settings['ms_stock_setting'];
    $ms_time = date("YmdHis");

    $MS_transaction_orderid = get_post_meta($order->id, 'MS_transaction_orderid', true);
    $MS_PAYMENT_TYPE = get_post_meta($order->id, 'MS_PAYMENT_TYPE', true);
    $order_amount = $order->get_total();


    $ms_order_select = $payment_gateway->settings['order_status_if_success'];
    $ms_order_select_qr = $payment_gateway_qr->settings['order_status_if_success'];
    $ms_order_select_installment = $payment_gateway_installment->settings['order_status_if_success'];




    $process_transactionID = $_POST["transectionID"]; 
    $amount = $_POST["amount"];
    $status = $_POST["status"];
    $hash = $_POST["hash"];
    $process_payment_hash = hash_hmac('sha256', $process_transactionID.$amount.$status.$getorderid, $ms_secret_key);

    if ($hash == $process_payment_hash){


        if($MS_PAYMENT_TYPE == "Card"){

            if(empty($ms_order_select)){
                $order->update_status("wc-processing");
            }else{
                $order->update_status($ms_order_select);
            }



        }else if($MS_PAYMENT_TYPE == "Qrnone"){

            if(empty($ms_order_select_qr)){
                $order->update_status("wc-processing");
            }else{
                $order->update_status($ms_order_select_qr);
            }


        }else if($MS_PAYMENT_TYPE == "Installment"){

            if(empty($ms_order_select_installment)){
                $order->update_status("wc-processing");
            }else{
                $order->update_status($ms_order_select_installment);
            }


        }

        if ($ms_stock_setting != "Disable") {
            $order->reduce_order_stock();
        }



    }else{


        $order->update_status("wc-failed");


    }


}

