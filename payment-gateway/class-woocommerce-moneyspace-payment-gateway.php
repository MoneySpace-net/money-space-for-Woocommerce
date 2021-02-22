<?php

class MS_Payment_Gateway extends WC_Payment_Gateway
{
    public function __construct()
    {
        $this->domain = 'ms_payment';

        $this->id = MS_ID;
        $this->title = __($this->get_option('title'), $this->domain);
        $this->icon = apply_filters('woocommerce_custom_gateway_icon', MS_LOGO, '');
        $this->method_title = __(MS_METHOD_TITLE, $this->domain);
        $this->method_description = __(MS_DESCRIPTION, $this->domain);
        $this->has_fields = true;

        $this->init_form_fields();
        $this->init_settings();

        $this->enabled = $this->get_option('enabled');
        $this->description = $this->get_option('description');

        add_action('woocommerce_receipt_' . $this->id, array($this, 'paymentgateway_form'), 10, 1);
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        add_action('woocommerce_thankyou_custom', array($this, 'thankyou_page'));
    }

    public function init_form_fields()
    {
        $this->form_fields = array(
            'header_setting' => array(
                'title' => __('<h1><b> ' . MS_FORM_FIELD_HEADER_SETTING . ' </b></h1>', $this->domain),
                'type' => 'title'
            ),
            'enabled' => array(
                'title' => __(MS_FORM_FIELD_ENABLE, $this->domain),
                'type' => 'checkbox',
                'label' => __(MS_FORM_FIELD_ENABLE_LABEL, $this->domain),
                'default' => 'no'
            ),
            'title' => array(
                'title' => __('Title', $this->domain),
                'type' => 'text',
                'description' => __('This controls the title which the user sees during checkout.', $this->domain),
                'default' => __('Pay by Card 3D secured', $this->domain),
                'desc_tip' => true,
            ),
            'description' => array(
                'title' => __(MS_FORM_FIELD_DESCRIPTION, $this->domain),
                'type' => 'textarea',
                'default' => __("", $this->domain),
                'desc_tip' => true,
            ),
            'desc_domain_webhook1' => array(
                'title' => __('<h1><b>' . HEAD_DOMAIN_WEBHOOK . '</b></h1>', $this->domain),
                'type' => 'title',
                'description' => DOMAIN_WEBHOOK
            ),
            'ms_domain' => array(
                'title' => __(YOUR_DOMAIN . " : <code>" . get_site_url() . "</code>", $this->domain),
                'type' => 'title'
            ),
            'ms_webhook' => array(
                'title' => __(YOUR_WEBHOOK . " : <code>" . get_site_url() . "/ms/webhook" . "</code>", $this->domain),
                'type' => 'title'
            ),
            'header_setting_ms' => array(
                'title' => __('<h1><b>' . MS_FORM_FIELD_HEADER_SETTING_MS . '</b></h1>', $this->domain),
                'type' => 'title'
            ),
            'secret_id' => array(
                'title' => __('secret_id', $this->domain),
                'type' => 'input',
                'default' => '',
                'desc_tip' => true,
            ),
            'secret_key' => array(
                'title' => __('secret_key', $this->domain),
                'type' => 'input',
                'default' => '',
                'desc_tip' => true,
            ),
            'fee_setting' => array(
                'title' => __(FEE_HEADER, $this->domain),
                'type' => 'select',
                'class' => 'wc-enhanced-select',
                'default' => 'include',
                'desc_tip' => true,
                'options' => ["include" => FEE_INCLUDE,"exclude" => FEE_EXCLUDE]
            ),
            'message2store_setting' => array(
                'title' => __(MESSAGE2STORE_HEADER, $this->domain),
                'type' => 'select',
                'class' => 'wc-enhanced-select',
                'default' => false,
                'desc_tip' => true,
                'options' => [true => "Enable", false => "Disable"]
            ),
            'order_status_if_success' => array(
                'title' => __(MS_FORM_FIELD_SET_ORDER_STATUS, $this->domain),
                'type' => 'select',
                'class' => 'wc-enhanced-select',
                'default' => 'wc-processing',
                'desc_tip' => true,
                'options' => wc_get_order_statuses()
            ),
            'ms_stock_setting' => array(
                'title' => __(STOCKSETTING_HEAD, $this->domain),
                'type' => 'select',
                'class' => 'wc-enhanced-select',
                'default' => 'Disable',
                'desc_tip' => true,
                'options' => ["Disable" => STOCKSETTING_DISABLE, "Enable" => STOCKSETTING_ENABLE]
            ),
            'ms_template_payment' => array(
                'title' => __("เลือกรูปแบบ UI", $this->domain),
                'description' => __('', $this->domain),
                'type' => 'select',
                'class' => 'wc-enhanced-select',
                'default' => "1",
                'desc_tip' => true,
                'options' => ["1" => "รูปแบบที่ 1", "2" => "รูปแบบที่ 2"]
            ),
            'header_setting_QRPROM' => array(
                'title' => __('<h1><b>' . MS_FORM_FIELD_HEADER_SETTING_MS . " ( " . TYPE_PAYMENT_QR . " )" . '</b></h1>', $this->domain),
                'type' => 'title'
            ),
            'header_setting_QRPROM_Link' => array(
                'title' => __('<a href="admin.php?page=wc-settings&tab=checkout&section=moneyspace_qrprom">' . SETTING_LINK . '</a>', $this->domain),
                'type' => 'title'
            ),
            'header_setting_INSTALLMENT' => array(
                'title' => __('<h1><b>' . MS_FORM_FIELD_HEADER_SETTING_MS . " ( ผ่อนชำระรายเดือน )" . '</b></h1>', $this->domain),
                'type' => 'title'
            ),
            'header_setting_installment_Link' => array(
                'title' => __('<a href="admin.php?page=wc-settings&tab=checkout&section=moneyspace_installment">' . SETTING_LINK . '</a>', $this->domain),
                'type' => 'title'
            ),
            'header_setting_ui' => array(
                'title' => __('<h1><b>' . MS_FORM_FIELD_HEADER_SETTING_UI . ' ( Moneyspace ) </b></h1>', $this->domain),
                'type' => 'title'
            ),
            'ms_firstname' => array(
                'title' => __('' . MS_FORM_FIELD_MS_FIRSTNAME, $this->domain),
                'type' => 'checkbox',
                'label' => __(MS_FORM_FIELD_MS_LABEL, $this->domain),
                'default' => 'yes'
            ),
            'ms_lastname' => array(
                'title' => __('' . MS_FORM_FIELD_MS_LASTNAME, $this->domain),
                'type' => 'checkbox',
                'label' => __(MS_FORM_FIELD_MS_LABEL, $this->domain),
                'default' => 'yes'
            ),
            'ms_email' => array(
                'title' => __('' . MS_FORM_FIELD_MS_EMAIL, $this->domain),
                'type' => 'checkbox',
                'label' => __(MS_FORM_FIELD_MS_LABEL, $this->domain),
                'default' => 'yes'
            ),
            'ms_phone' => array(
                'title' => __('' . MS_FORM_FIELD_MS_PHONE, $this->domain),
                'type' => 'checkbox',
                'label' => __(MS_FORM_FIELD_MS_LABEL, $this->domain),
                'default' => 'yes'
            ),
            'ms_address' => array(
                'title' => __('' . MS_FORM_FIELD_MS_ADDRESS, $this->domain),
                'type' => 'checkbox',
                'label' => __(MS_FORM_FIELD_MS_LABEL, $this->domain),
                'default' => 'yes'
            )
        );
    }

    public function thankyou_page()
    {
        if ($this->instructions) echo wpautop(wptexturize($this->instructions));
    }

    public function payment_fields()
    {
        $order_amount = round(WC()->cart->cart_contents_total, 2);

        $payment_gateway_id = MS_ID;
        $payment_gateways = WC_Payment_Gateways::instance();
        $payment_gateway = $payment_gateways->payment_gateways()[$payment_gateway_id];
        $gateways = WC()->payment_gateways->get_available_payment_gateways();
        $ms_message2store = $gateways['moneyspace']->settings['message2store_setting'];

        if ($description = $this->get_description()) {
            echo wpautop(wptexturize($description));
        }

        ?>
        <?php if ($ms_message2store != 0) { ?>
        <div id="custom_input">
            <p class="form-row form-row-wide">
                <label for="message_card" class=""><?php _e("", $this->domain); ?></label>
                <input type="text" class="" name="message_card" id="message_card" placeholder="<?= MESSAGE2STORE ?>">
            </p>
        </div>
    <?php } ?>
        <?php
    }

    /**
     * Process the payment and return the result.
     *
     * @param int $order_id
     * @return array
     */
    public function process_payment($order_id)
    {
        $MS_special_instructions_to_merchant = get_post_meta($order_id, 'MS_special_instructions_to_merchant', true);

        if (strlen($MS_special_instructions_to_merchant) <= 150) {
            if (get_woocommerce_currency() == "THB") {
                update_post_meta($order_id, 'MS_special_instructions_to_merchant', $_POST["message_card"]);
                update_post_meta($order_id, 'MS_PAYMENT_TYPE', "Card");
                delete_post_meta($order_id, 'MS_transaction');
                $order = wc_get_order($order_id);
                return $this->_process_external_payment($order);
            } else {
                wc_add_notice(__(MS_NOTICE_CURRENCY, $this->domain), 'error');
            }
        } else {
            wc_add_notice(__("Error : Enter special instructions to merchant again", $this->domain), 'error');
        }
    } // End Process

    public function paymentgateway_form($order_id)
    {
        $order = wc_get_order($order_id);
        $order_amount = $order->get_total();
        $payment_gateway_id = MS_ID;
        $payment_gateways = WC_Payment_Gateways::instance();
        $payment_gateway = $payment_gateways->payment_gateways()[$payment_gateway_id];
        $gateways = WC()->payment_gateways->get_available_payment_gateways();
        $ms_order_select = $payment_gateway->settings['order_status_if_success'];
        $ms_secret_id = $payment_gateway->settings['secret_id'];
        $ms_secret_key = $payment_gateway->settings['secret_key'];
        $ms_fee = $payment_gateway->settings['fee_setting'];
        $MS_PAYMENT_TYPE = get_post_meta($order->id, 'MS_PAYMENT_TYPE', true);
        $ms_template_payment = $gateways['moneyspace']->settings['ms_template_payment'];

        $MS_special_instructions_to_merchant = get_post_meta($order_id, 'MS_special_instructions_to_merchant', true);
        $ms_time = date("YmdHis");

        $items_order = new WC_Order($order_id);
        $items = $order->get_items();
        $items_msg = "";
        $i = 1;

        foreach ($items as $item) {
            $product = wc_get_product($item['product_id']);
            if (count($items) > 1) {
                $items_msg .= $i . ". " . $product->get_name() . "  " . $product->get_price() . "฿" . " ( " . $item['quantity'] . " qty ) ";
                $i++;
            } else {
                $items_msg .= $product->get_name() . "  " . $product->get_price() . "฿" . " ( " . $item['quantity'] . " qty ) ";
            }
        }

        if ($ms_order_select == "wc-failed" || $ms_order_select == "wc-cancelled" || $ms_order_select == "wc-refunded") {
            echo "Error : " . MS_NOTICE_ERROR_CONTINUE;
        } else {


            if($ms_fee == "include"){
                
            


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

            $body_post = array("firstname" => $order_firstname, "lastname" => $order_lastname, "email" => $order_email, "phone" => $order_phone, "address" => $order_address, "amount" => $order_amount, "description" => $items_msg, "message" => $MS_special_instructions_to_merchant, "feeType" => "include", "customer_order_id" => $order_id . "MS" . $ms_time);

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
                'feeType' => "include",
                'order_id' => $body_post["customer_order_id"],
                "payment_type" => "card",
                'success_Url' => get_site_url() . "/process/payment/" . $order_id,
                'fail_Url' => get_site_url() . "/process/payment/" . $order_id,
                'cancel_Url' => get_site_url() . "/process/payment/" . $order_id,
                "agreement" => 1
            );

            $response = wp_remote_post(MS_API_URL_CREATE, array(
                    'method' => 'POST',
                    'timeout' => 120,
                    'body' => $ms_body
                )
            );

            if (is_wp_error($response)) {
                echo "Error : " . MS_NOTICE_ERROR_SETUP;
            } else {
                $data_status = json_decode($response["body"]);
                if ($data_status[0]->status == "success") {
                    if (strlen($data_status[0]->mskey) > 9999) {
                        wc_add_notice(__("Error ms100 : " . MS_NOTICE_CHECK_TRANSACTION . $data_status[0]->status, $this->domain), 'error');
                    } else {
                        $tranId = $data_status[0]->transaction_ID;
                        $mskey = $data_status[0]->mskey;

                        update_post_meta($order_id, 'MS_transaction_orderid', $body_post["customer_order_id"]);
                        update_post_meta($order_id, 'MS_transaction', $tranId);
                        update_post_meta($order_id, 'MS_PAYMENT_KEY', $mskey);

                        if ($ms_template_payment == "1") {
                            wp_redirect(get_site_url() . "/mspaylink/" . $order_id);
                            exit;
                        } else if ($ms_template_payment == "2") {
                            echo '<div align="center">
                            <div id="moneyspace-payment" 
                                    template="2"
                                    lang="eng"
                                    ms-title="' . $gateways['moneyspace']->settings['title'] . '" 
                                    ms-key="' . $mskey . '" 
                                    description="false">
                            </div>
                        </div>
                        <script type="text/javascript" src="' . MS_PAYMENT_JS . '"></script>';
                        } else {
                            wp_redirect(get_site_url() . "/mspaylink/" . $order_id);
                            exit;
                        }
                    }
                } else {
                    wc_add_notice(__("Error ms102 : " . MS_NOTICE_CHECK_TRANSACTION, $this->domain), 'error');
                }
            }

        }else if($ms_fee == "exclude"){

                        $url = "https://www.moneyspace.net/merchantapi/v2/createpayment/obj";

                        $body_post = array("firstname" => "", "lastname" => "", "email" => "", "phone" => "", "address" => "", "amount" => $order_amount, "currency" => "THB", "description" => $items_msg, "message" => "", "feeType" => "exclude", "timeHash" => $ms_time, "customer_order_id" => $order_id . "MS" . $ms_time, "gatewayType" => "card", "successUrl" => get_site_url() . "/process/payment/" . $order_id, "failUrl" => get_site_url() . "/process/payment/" . $order_id, "cancelUrl" => get_site_url() . "/process/payment/" . $order_id
                        );


                        if ($ms_message2store == "Enable") {
                            $body_post["message"] = $MS_special_instructions_to_merchant;
                        }


                        if ($gateways['moneyspace']->settings['ms_firstname'] == "yes") {
                            $body_post["firstname"] = $order->get_billing_first_name();
                        }

                        if ($gateways['moneyspace']->settings['ms_lastname'] == "yes") {
                            $body_post["lastname"] = $order->get_billing_last_name();
                        }

                        if ($gateways['moneyspace']->settings['ms_email'] == "yes") {
                            $body_post["email"] = $order->get_billing_email();
                        }

                        if ($gateways['moneyspace']->settings['ms_phone'] == "yes") {
                            $body_post["phone"] = $order->get_billing_phone();
                        }

                        if ($gateways['moneyspace']->settings['ms_address'] == "yes") {
                            $body_post["address"] = $order->get_billing_address_1() . " " . $order->get_billing_address_2() . " " . $order->get_billing_city() . " " . $order->get_billing_postcode();
                        }


                        $hash_data = $body_post["firstname"] . $body_post["lastname"] . $body_post["email"] . $body_post["phone"] . $body_post["amount"] . $body_post["currency"] . preg_replace( "/<br>|\n/", "", $body_post["description"] ) . $body_post["address"] . $body_post["message"] . $body_post["feeType"] . $body_post["timeHash"] . $body_post["customer_order_id"] . $body_post["gatewayType"] . $body_post["successUrl"] . $body_post["failUrl"] . $body_post["cancelUrl"];


                        $hash_body = hash_hmac('sha256', $hash_data, $ms_secret_key);

                        $ms_body = array('secreteID' => $ms_secret_id, 'firstname' => $body_post["firstname"], 'lastname' => $body_post["lastname"], 'email' => $body_post["email"], 'phone' => $body_post["phone"], 'amount' => $body_post["amount"], 'currency' => $body_post["currency"], 'description' => preg_replace( "/<br>|\n/", "", $body_post["description"] ), 'address' => $body_post["address"], 'message' => $body_post["message"], 'feeType' => $body_post["feeType"], 'customer_order_id' => $body_post["customer_order_id"], 'gatewayType' => $body_post["gatewayType"], 'timeHash' => $body_post["timeHash"], 'hash' => $hash_body, 'successUrl' => $body_post["successUrl"], 'failUrl' => $body_post["failUrl"], 'cancelUrl' => $body_post["cancelUrl"]
                        );

                        $response = wp_remote_post($url, array(
                                'method' => 'POST',
                                'timeout' => 120,
                                'body' => $ms_body
                            )
                        );

                        if (is_wp_error($response)) {

                            wc_add_notice(__(MS_NOTICE_ERROR_SETUP, $this->domain), 'error');

                        } else {

                            $json_tranId_status = json_decode($response["body"]);


                            if ($json_tranId_status[0]->status == "NotFound") {

                                wc_add_notice(__(MS_NOTICE_ERROR_SETUP, $this->domain), 'error');

                            } else {

                                $urlpayment = "Transaction ID";
                                $json_tranId = json_decode($response["body"]);
                                $tranId = $json_tranId[0]->$urlpayment;

                                $hash_link = hash_hmac('sha256', $tranId . $ms_time, $ms_secret_key);

                                $link = "https://www.moneyspace.net/merchantapi/makepayment/linkpaymentcard?transactionID=" . $tranId . "&timehash=" . $ms_time . "&secreteID=" . $ms_secret_id . "&hash=" . $hash_link;

                                update_post_meta($order_id, 'MS_transaction', $tranId);
                                update_post_meta($order_id, 'MS_transaction_orderid', $body_post["customer_order_id"]);
                                update_post_meta($order_id, 'MS_LINK', $link);


                                WC()->cart->empty_cart();

                                wp_redirect($link);

                            }

                        } // check POST
                        

        }


        }



        
    }

    protected function _process_external_payment($order)
    {
        // Redirect to payment page, where payment form will be printed
        return array(
            'result' => 'success',
            'redirect' => $order->get_checkout_payment_url(true)
        );
    }
}

add_filter('woocommerce_thankyou_order_received_text', 'avia_thank_you');

function avia_thank_you()
{
    $gateways = WC()->payment_gateways->get_available_payment_gateways();
    $added_text = '';
    return $added_text;
}

add_filter( 'woocommerce_payment_complete_reduce_order_stock', 'filter_woocommerce_payment_complete_reduce_order_stock', 10, 2 ); 

function filter_woocommerce_payment_complete_reduce_order_stock( $order_order_get_data_store_get_stock_reduced_order_id, $order_id ) { 

    $order = new WC_Order( $order_id );


    if ($order->get_payment_method() == MS_ID || $order->get_payment_method() == MS_ID_QRPROM || $order->get_payment_method() == MS_ID_INSTALLMENT){

        $order_order_get_data_store_get_stock_reduced_order_id = false;

    }else if ($order->get_payment_method() != MS_ID || $order->get_payment_method() != MS_ID_QRPROM || $order->get_payment_method() != MS_ID_INSTALLMENT){
       
        $order_order_get_data_store_get_stock_reduced_order_id = true;
 
    }else if ( $order->has_status( 'on-hold' ) && $order->get_payment_method() == 'bacs' ) {

        $order_order_get_data_store_get_stock_reduced_order_id = true;

    }

    return $order_order_get_data_store_get_stock_reduced_order_id; 

}; 

add_action('woocommerce_order_details_before_order_table', 'custom_order_details_after_order_table', 10, 1);

function custom_order_details_after_order_table($order)
{
}

add_action('woocommerce_order_details_after_order_table', 'ms_order_detail_display', 10, 1);

function ms_order_detail_display($order)
{
    $gateways = WC()->payment_gateways->get_available_payment_gateways();
    $ms_secret_id = $gateways['moneyspace']->settings['secret_id'];
    $ms_secret_key = $gateways['moneyspace']->settings['secret_key'];
    $ms_order_select = $gateways['moneyspace']->settings['order_status_if_success'];

    $MS_PAYMENT_TYPE = get_post_meta($order->id, 'MS_PAYMENT_TYPE', true);
    $MS_transaction = get_post_meta($order->id, 'MS_transaction', true);
    $MS_transaction_orderid = get_post_meta($order->id, 'MS_transaction_orderid', true);
    $MS_PAYMENT_PAID = get_post_meta($order->id, 'MS_PAYMENT_PAID', true);
    $MS_PAYMENT_STATUS = get_post_meta($order->id, 'MS_PAYMENT_STATUS', true);

    $order_amount = $order->get_total();
    $ms_time = date("YmdHis");

    if ($MS_PAYMENT_STATUS == "Pay Success") {
        if ($MS_PAYMENT_TYPE == "Qrnone" || $MS_PAYMENT_TYPE == "Card") {
            echo '<h6 style="margin:0"><strong>' . MS_THANK_PAYMENT_ORDER_1 . '</strong></h6>';
            echo '<h6 style="margin:0"><strong>' . MS_THANK_PAYMENT_ORDER_2 . '</strong></h6><br>';
            echo "<p style='color:#a7a6a6;margin:0'>" . wc_price($MS_PAYMENT_PAID) . " ( Transaction ID : " . $MS_transaction . " )</p>";
        } else if ($MS_PAYMENT_TYPE == "Installment") {
            // $MS_INSTALLMENT_BANK = get_post_meta($order->id, 'MS_INSTALLMENT_BANK', true);
            // $MS_INSTALLMENT_MONTHS = get_post_meta($order->id, 'MS_INSTALLMENT_MONTHS', true);
            // $MS_fee_installment = get_post_meta($order->id, 'MS_fee_installment', true);

            // echo '<h6 style="margin:0"><strong>' . MS_THANK_PAYMENT_ORDER_1 . '</strong></h6>';
            // echo '<h6 style="margin:0"><strong>' . MS_THANK_PAYMENT_ORDER_2 . '</strong></h6><br>';

            // if($MS_fee_installment == "include"){
            //     $ex_ktc_bay = $json_status[0]->$amount_payment;
            //     $ex_fcy = $json_status[0]->$amount_payment;
            // }else if($MS_fee_installment == "exclude"){
            //     $ex_ktc_bay = $order_amount / 100 * 0.8 * $MS_INSTALLMENT_MONTHS + $order_amount;
            //     $ex_fcy = $order_amount / 100 * 1 * $MS_INSTALLMENT_MONTHS + $order_amount;
            // }

            // if ($MS_INSTALLMENT_BANK == "KTC" || $MS_INSTALLMENT_BANK == "BAY"){
            //     echo "<p style='color:#a7a6a6;margin:0'>จำนวนเดือน : " . $MS_INSTALLMENT_MONTHS . " เดือน</p>";
            //     echo "<p style='color:#a7a6a6;margin:0'>จำนวนเงินชำระต่อเดือน : " . wc_price($ex_ktc_bay / $MS_INSTALLMENT_MONTHS) . " / เดือน</p>";
            //     echo "<p style='color:#a7a6a6;margin:0'>" . wc_price($ex_ktc_bay) . " ( Transaction ID: " . $MS_transaction . " )</p>";
            // } else if ($MS_INSTALLMENT_BANK == "FCY"){
            //     echo "<p style='color:#a7a6a6;margin:0'>จำนวนเดือน : " . $MS_INSTALLMENT_MONTHS . " เดือน</p>";
            //     echo "<p style='color:#a7a6a6;margin:0'>จำนวนเงินชำระต่อเดือน : " . wc_price($ex_fcy / $MS_INSTALLMENT_MONTHS) . " / เดือน</p>";
            //     echo "<p style='color:#a7a6a6;margin:0'>" . wc_price($ex_fcy) . " ( Transaction ID: " . $MS_transaction . " )</p>";
            // }

            echo '<h6 style="margin:0"><strong>' . MS_THANK_PAYMENT_ORDER_1 . '</strong></h6>';
            echo '<h6 style="margin:0"><strong>' . MS_THANK_PAYMENT_ORDER_2 . '</strong></h6><br>';
            echo "<p style='color:#a7a6a6;margin:0'>" . wc_price($MS_PAYMENT_PAID) . " ( Transaction ID : " . $MS_transaction . " [" . $MS_transaction_orderid . "] )</p>";
        }
    }
}
