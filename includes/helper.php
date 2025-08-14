<?php

function set_title_html($title) {
    return $title; //  '<h1><b>'.$title.'</b></h1>';
}

function set_item_message($items) {
    $items_msg = "";
    $prefix = "";

    foreach ($items as $key => $item) {
        $product = wc_get_product($item['product_id']);
        if (count($items) > 1) {
            $prefix = ($key+1) . ". ";
        }

        $items_msg .= $prefix . $product->get_name() . "  " . $product->get_price() . "฿" . " ( " . $item['quantity'] . " qty ) ";
    }
    return $items_msg;
}

function set_body($order_id, $order, $gateways, $amount, $items_msg, $message, $feeType, $ms_time, $gatewayType = "card") {
    $order_firstname = "";
    $order_lastname = "";
    $order_email = "";
    $order_phone = "";
    $order_address = "";

    $order_firstname = $gateways['moneyspace']->settings['ms_firstname'] == "yes" ? $order->get_billing_first_name() : "สมชาย";
    $order_lastname = $gateways['moneyspace']->settings['ms_lastname'] == "yes" ? $order->get_billing_last_name() : "ค้าขายดี";
    $order_email = $gateways['moneyspace']->settings['ms_email'] == "yes" ? $order->get_billing_email() : "test@test.com";
    $order_phone = $gateways['moneyspace']->settings['ms_phone'] == "yes" ? $order->get_billing_phone() : "0111111111";
    $order_address = $gateways['moneyspace']->settings['ms_address'] == "yes" ? $order->get_billing_address_1() . " " . $order->get_billing_address_2() . " " . $order->get_billing_city() . " " . $order->get_billing_postcode() : "P23 สุขุมวิท 23";
    

    return array("firstname" => $order_firstname
            , "lastname" => $order_lastname
            , "email" => $order_email
            , "phone" => $order_phone
            , "address" => $order_address
            , "amount" => $amount
            , "currency" => "THB"
            , "timeHash" => $ms_time
            , "description" => $items_msg
            , "message" => "Order ID#".$order_id
            , "feeType" => $feeType // "include"
            , "customer_order_id" => $order_id . "MS" . $ms_time
            , "gatewayType" => $gatewayType);
}

function set_req_message($ms_secret_id, $ms_secret_key, $body_post, $payment_type, $return_url, $hash_body = "") {
    $ms_body = array(
        "secret_id" => $ms_secret_id,
        "secret_key" => $ms_secret_key,
        'firstname' => $body_post["firstname"],
        'lastname' => $body_post["lastname"],
        'email' => $body_post["email"],
        'phone' => $body_post["phone"],
        'amount' => round($body_post["amount"], 2),
        'description' => preg_replace( "/<br>|\n/", "", $body_post["description"] ),
        'address' => $body_post["address"],
        'message' => $body_post["message"],
        'feeType' => $body_post["feeType"],
        'order_id' => $body_post["customer_order_id"],
        "payment_type" => $payment_type, //"card",
        'success_Url' => $return_url,
        'fail_Url' => $return_url,
        'cancel_Url' => $return_url,
        "agreement" => 1,
    );

    if (strtolower($body_post["feeType"]) == "exclude") {
        unset($ms_body['payment_type']);
        $ms_body['currency'] = $body_post["currency"];
        $ms_body['gatewayType'] = $body_post["gatewayType"];
        $ms_body['timeHash'] = $body_post["timeHash"];
        $ms_body['hash'] = $hash_body;
    }

    return $ms_body;
}

function array_select_keys(array $select_keys, array $array_source) {
    $result = array();
    foreach ($array_source as $key => $value) {
        if (in_array($key, $select_keys)) {
            $result[$key] = $value;
        }
    }

    return $result;
}

function wc_renaming_order_status( $order_statuses ) {
    foreach ( $order_statuses as $key => $status ) {
        if ( 'wc-completed' === $key ) 
            $order_statuses['wc-completed'] = _x( MNS_ORDER_STATUS_COMPLETED, 'Order status', 'woocommerce' );
    }
    return $order_statuses;
}

function update_order_status($order) {

    if ($order) {
        $payment_gateway_id = MNS_ID;
        $payment_gateway_qr_id = MNS_ID_QRPROM;
        $payment_gateway_installment_id = MNS_ID_INSTALLMENT;
    
        $payment_gateways = WC_Payment_Gateways::instance();
        $payment_gateway = $payment_gateways->payment_gateways()[$payment_gateway_id];
        $payment_gateway_qr = $payment_gateways->payment_gateways()[$payment_gateway_qr_id];
        $payment_gateway_installment = $payment_gateways->payment_gateways()[$payment_gateway_installment_id];
    
        $ms_secret_id = $payment_gateway->settings['secret_id'];
        $ms_secret_key = $payment_gateway->settings['secret_key'];
        $ms_stock_setting = $payment_gateway->settings['ms_stock_setting'];
    
        $ms_order_select = $payment_gateway->settings['order_status_if_success'];
        $ms_order_select_qr = $payment_gateway_qr->settings['order_status_if_success'];
        $ms_order_select_installment = $payment_gateway_installment->settings['order_status_if_success'];

        $MNS_transaction_orderid = get_post_meta($order->get_id(), 'MNS_transaction_orderid', true);
        $MNS_PAYMENT_TYPE = get_post_meta($order->get_id(), 'MNS_PAYMENT_TYPE', true);
        $check_orderid = wp_remote_post(MNS_API_URL_CHECK, array(
            'method' => 'POST',
            'timeout' => 120,
            'body' => array(
                'secret_id' => $ms_secret_id,
                'secret_key' => $ms_secret_key,
                'order_id' => $MNS_transaction_orderid,
            )
        ));
    
        if (!is_wp_error($check_orderid)) {
            $json_status = json_decode($check_orderid["body"]);
            
            // Add safety check for API response
            if (empty($json_status) || !is_array($json_status) || !isset($json_status[0])) {
                error_log('MoneySpace API: Invalid response format in helper.php');
                return false;
            }
            
            // Access the order data using proper property notation
            $response_data = $json_status[0];
            $ms_status = isset($response_data->{'order id'}) ? $response_data->{'order id'} : null;
            
            // Additional safety check for order status
            if (empty($ms_status)) {
                error_log('MoneySpace API: No order status found in response in helper.php');
                return false;
            }
    
            cancel_payment($order->get_id(), $payment_gateway);
    
            if (isset($ms_status->status) && $ms_status->status == "Pay Success") {
    
                if($MNS_PAYMENT_TYPE == "Card"){
    
                    if(empty($ms_order_select)){
                        $order->update_status("wc-processing");
                    }else{
                        $order->update_status($ms_order_select);
                    }
                }else if($MNS_PAYMENT_TYPE == "Qrnone"){
    
                    if(empty($ms_order_select_qr)){
                        $order->update_status("wc-processing");
                    }else{
                        $order->update_status($ms_order_select_qr);
                    }
                } else if($MNS_PAYMENT_TYPE == "Installment"){
    
                    if(empty($ms_order_select_installment)){
                        $order->update_status("wc-processing");
                    }else{
                        $order->update_status($ms_order_select_installment);
                    }
                }
    
                if ($ms_stock_setting != "Disable") {
                    wc_reduce_stock_levels($order->get_id());
                }
                update_post_meta($order->get_id(), 'MNS_PAYMENT_PAID', $ms_status->amount);
                update_post_meta($order->get_id(), 'MNS_PAYMENT_STATUS', $ms_status->status);
            } else if ($ms_status->status == "Cancel") {
                $order->update_status("wc-cancelled");
            }
        } else {
            
        }
    } else {

    }
}

function cancel_payment($order_id, $payment_gateway)
{
    $MNS_transaction = get_post_meta($order_id, 'MNS_transaction', true);

    $ms_secret_id = $payment_gateway->settings['secret_id'];
    $ms_secret_key = $payment_gateway->settings['secret_key'];
    // trigger kill transaction id
    $call_cancel = wp_remote_post(MNS_CANCEL_TRANSACTION, array(
        'method' => 'POST',
        'timeout' => 120,
        'body' => array(
            'secret_id' => $ms_secret_id,
            'secret_key' => $ms_secret_key,
            'transaction_ID' => $MNS_transaction,
        )
    ));

    $json_status = json_decode($call_cancel["body"]);
    if($json_status[0]->status == "success")
    {
        // TODO
    }
}

?>