<?php

global $wpdb;

global $woocommerce;

$payment_gateways = WC_Payment_Gateways::instance();
$payment_gateway = $payment_gateways->payment_gateways()[$payment_gateway_id];
$gateways = WC()->payment_gateways->get_available_payment_gateways();
$ms_secret_id = $gateways['moneyspace']->settings['secret_id'];
$ms_secret_key = $gateways['moneyspace']->settings['secret_key'];

$date = date("Ymdhms");
$hash = hash_hmac("sha256", $date.$ms_secret_id, $ms_secret_key);

$request = wp_remote_get( 'https://www.moneyspace.net/merchantapi/v1/store/obj?timeHash='.$date.'&secreteID='.$ms_secret_id.'&hash='.$hash , array());
$response = wp_remote_retrieve_body( $request );
// $curl = curl_init();

// curl_setopt_array($curl, array(
//   CURLOPT_URL => 'https://www.moneyspace.net/merchantapi/v1/store/obj?timeHash='.$date.'&secreteID='.$ms_secret_id.'&hash='.$hash,
//   CURLOPT_RETURNTRANSFER => true,
//   CURLOPT_ENCODING => '',
//   CURLOPT_MAXREDIRS => 10,
//   CURLOPT_TIMEOUT => 0,
//   CURLOPT_FOLLOWLOCATION => true,
//   CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
//   CURLOPT_CUSTOMREQUEST => 'GET'
// ));

// $response = curl_exec($curl);

// curl_close($curl);

echo "<pre>";
echo $response;
// var_dump($gateways['moneyspace']->settings);
echo "</pre>";