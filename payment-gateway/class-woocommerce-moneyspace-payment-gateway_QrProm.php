<?php

class MS_Payment_Gateway_QR extends WC_Payment_Gateway
{

    public function __construct()
    {
        $this->domain = 'ms_payment_qrprom';

        $this->id = MS_ID_QRPROM;
        $this->title = __($this->get_option('title'), $this->domain);
        $this->icon = apply_filters('woocommerce_custom_gateway_icon', MS_LOGO_QR, '');
        $this->method_title = __(MS_METHOD_TITLE . "( " . TYPE_PAYMENT_QR . " )", $this->domain);
        $this->method_description = __(MS_DESCRIPTION_QR, $this->domain);
        $this->has_fields = true;

        $this->init_form_fields();
        $this->init_settings();

        $this->enabled = $this->get_option('enabled');
        $this->description = $this->get_option('description');

        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        add_action('woocommerce_thankyou_custom', array($this, 'thankyou_page'));
        add_action('woocommerce_receipt_' . $this->id, array($this, 'paymentgateway_form'), 10, 1);
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
                'default' => __('QR Code PromptPay', $this->domain),
                'desc_tip' => true,
            ),
            'order_status_if_success' => array(
                'title' => __(MS_FORM_FIELD_SET_ORDER_STATUS, $this->domain),
                'type' => 'select',
                'class' => 'wc-enhanced-select',
                'default' => 'wc-processing',
                'desc_tip' => true,
                'options' => wc_get_order_statuses()
            ),
            'auto_cancel' => array(
                'title' => __("ตั้งเวลาหมดอายุ", $this->domain),
                'type' => 'select',
                'class' => 'wc-enhanced-select',
                'default' => 1200,
                'desc_tip' => true,
                'options' => [900 => "15 นาที",1200 => "20 นาที",1500 => "25 นาที",1800 => "30 นาที"]
            ),
            'description' => array(
                'title' => __(MS_FORM_FIELD_DESCRIPTION, $this->domain),
                'type' => 'textarea',
                'default' => __("", $this->domain),
                'desc_tip' => true,
            )
        );
    }

    public function thankyou_page()
    {
        if ($this->instructions)
            echo wpautop(wptexturize($this->instructions));
    }

    public function payment_fields()
    {
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
                <label for="message" class=""><?php _e("", $this->domain); ?></label>
                <input type="text" class="" name="message_qr" id="message" placeholder="<?= MESSAGE2STORE ?>">
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
                update_post_meta($order_id, 'MS_special_instructions_to_merchant', $_POST["message_qr"]);
                update_post_meta($order_id, 'MS_PAYMENT_TYPE', "Qrnone");
                delete_post_meta($order_id, 'MS_transaction');
                $order = wc_get_order($order_id);
                return $this->_process_external_payment($order);
            } else {
                wc_add_notice(__(MS_NOTICE_CURRENCY, $this->domain), 'error');
            }
        } else {
            echo "Error : Enter special instructions to merchant again";
        }
    } // End Process


    public function paymentgateway_form($order_id)
    {
        $order = wc_get_order($order_id);
        $order_amount = $order->get_total();

        $payment_gateway_id = MS_ID;
        $payment_gateway_qr_id = MS_ID_QRPROM;

        $payment_gateways = WC_Payment_Gateways::instance();

        $payment_gateway = $payment_gateways->payment_gateways()[$payment_gateway_id];
        $payment_gateway_qr = $payment_gateways->payment_gateways()[$payment_gateway_qr_id];

        $gateways = WC()->payment_gateways->get_available_payment_gateways();
        $ms_order_select = $payment_gateway->settings['order_status_if_success'];
        $ms_secret_id = $payment_gateway->settings['secret_id'];
        $ms_secret_key = $payment_gateway->settings['secret_key'];

        $MS_PAYMENT_TYPE = get_post_meta($order->id, 'MS_PAYMENT_TYPE', true);

        $ms_template_payment = $payment_gateway->settings['ms_template_payment'];

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
            wc_add_notice(__(MS_NOTICE_ERROR_CONTINUE, $this->domain), 'error');
        } else {
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
                'description' => $body_post["description"],
                'address' => $body_post["address"],
                'message' => $body_post["message"],
                'feeType' => "include",
                'order_id' => $body_post["customer_order_id"],
                "payment_type" => "qrnone",
                'success_Url' => get_site_url() . "/process/payment/" . $order_id,
                'fail_Url' => get_site_url() . "/process/payment/" . $order_id,
                'cancel_Url' => get_site_url() . "/process/payment/" . $order_id,
            );

            $response = wp_remote_post(MS_API_URL_CREATE, array(
                    'method' => 'POST',
                    'timeout' => 120,
                    'body' => $ms_body
                )
            );

            if (is_wp_error($response)) {
                wc_add_notice(__(MS_NOTICE_ERROR_SETUP, $this->domain), 'error');
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
                        update_post_meta($order_id, 'MS_QR_TIME', time());

                        if ($ms_template_payment == "1") {
                            wp_redirect(get_site_url() . "/mspaylink/" . $order_id);
                            exit;
                        } else if ($ms_template_payment == "2") {

                            date_default_timezone_set("Asia/Bangkok");
                            $MS_QR_TIME = get_post_meta($order->id, 'MS_QR_TIME', true);
                            $auto_cancel = $payment_gateway_qr->settings['auto_cancel'];

                            if(empty($auto_cancel)){
                                $limit_time = 1200;
                            }else{
                                $limit_time = $auto_cancel;
                            }


                            echo '<div align="center">
                                        <div id="moneyspace-payment" 
                                                template="2"
                                                lang="eng"
                                                ms-title="' . $gateways['moneyspace_qrprom']->settings['title'] . '" 
                                                ms-key="' . $mskey . '" 
                                                description="false">
                                        </div>
                                        <br>
                                        <h3> QR Code จะหมดอายุวันที่ : '.date('d/m/Y H:i', $MS_QR_TIME + $limit_time).'</h3>
                                    </div>
                                    <script type="text/javascript" src="' . MS_PAYMENT_JS . '"></script>
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
                                                window.location="'.get_site_url() . "/ms/cancel/" . $order_id.'";
                                            }
                                        }, 1000);
                                    }
                                    
                                    window.onload = function () {
                                        var fiveMinutes = '.$limit_time.',
                                            display = document.querySelector("#time");
                                        startTimer(fiveMinutes, display);
                                    };
            </script>
                                    ';
                        } else {
                            wp_redirect(get_site_url() . "/mspaylink/" . $order_id);
                            exit;
                        }
                    }
                } else {
                    wc_add_notice(__("Error ms102 : " . MS_NOTICE_CHECK_TRANSACTION, $this->domain), 'error');
                }
            } // check POST
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

add_filter('woocommerce_thankyou_order_received_text', 'avia_thank_you_qr');

function avia_thank_you_qr()
{
    $gateways = WC()->payment_gateways->get_available_payment_gateways();
    $added_text = '';
    return $added_text;
}
