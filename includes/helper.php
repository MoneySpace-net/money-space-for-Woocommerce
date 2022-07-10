<?php

function set_title_html($title) {
    return '<h1><b>'.$title.'</b></h1>';
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

    if ($gateways['moneyspace']->settings['ms_firstname'] == "yes") {
        $order_firstname = $order->get_billing_first_name();
    }

    if ($gateways['moneyspace']->settings['ms_lastname'] == "yes") {
        $order_lastname = $order->get_billing_last_name();
    }

    if ($gateways['moneyspace']->settings['ms_email'] == "yes") {
        $order_email = $order->get_billing_email();
    }

    if ($gateways['moneyspace']->settings['ms_phone'] == "yes") {
        $order_phone = $order->get_billing_phone();
    }

    if ($gateways['moneyspace']->settings['ms_address'] == "yes") {
        $order_address = $order->get_billing_address_1() . " " . $order->get_billing_address_2() . " " . $order->get_billing_city() . " " . $order->get_billing_postcode();
    }

    return array("firstname" => $order_firstname
            , "lastname" => $order_lastname
            , "email" => $order_email
            , "phone" => $order_phone
            , "address" => $order_address
            , "amount" => $amount
            , "currency" => "THB"
            , "timeHash" => $ms_time
            , "description" => $items_msg
            , "message" => "Order ID#".$order_id // $message // $MNS_special_instructions_to_merchant
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

?>