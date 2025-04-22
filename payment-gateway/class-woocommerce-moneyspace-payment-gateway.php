<?php

namespace MoneySpace\Payments;

use WC_Payment_Gateway;
use WC_Payment_Gateways;
use WC_Order;
use MoneySpace\Mslogs;
use Exception;

class MNS_Payment_Gateway extends WC_Payment_Gateway
{
    public $domain;
    public $instructions;

    public function __construct()
    {
        $this->domain = 'ms_payment';

        $this->id = MNS_ID;
        $this->title = __($this->get_option('title'), $this->domain);
        $this->icon = apply_filters('woocommerce_custom_gateway_icon', MNS_LOGO, '');
        $this->method_title = _x(MNS_METHOD_TITLE, $this->domain);
        $this->method_description = __(MNS_DESCRIPTION, $this->domain);
        $this->has_fields = true;

        $this->init_form_fields();
        $this->init_settings();

        $this->enabled = $this->get_option('enabled');
        $this->description = $this->get_option('description');

        add_action('woocommerce_receipt_' . $this->id, array($this, 'paymentgateway_form'), 10, 1);
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        add_action('woocommerce_thankyou_custom', array($this, 'thankyou_page'));
        add_filter('woocommerce_thankyou_order_received_text', array($this, 'avia_thank_you'), 10, 2 );
        add_action('woocommerce_after_checkout_validation', array($this, 'after_checkout_validation'), 10, 3 );
    }


    public function create_payment_transaction_v3($order_id, $ms_body, $ms_template_payment, $gateways) {
        if ($ms_template_payment == 1) {
            $response = wp_remote_post(MNS_API_URL_CREATE, array(
                'method' => 'POST',
                'timeout' => 120,
                'body' => $ms_body
            ));
            (new Mslogs())->insert($response["body"], 1, 'Create Transaction Card', date("Y-m-d H:i:s"), json_encode($ms_body));

            if (is_wp_error($response)) {
                wc_add_notice(__("Error : " . MNS_NOTICE_ERROR_SETUP, $this->domain), 'error');
                return;
            }
    
            $data_status = json_decode($response["body"]);
            if (empty($data_status) || $data_status[0]->status != "success") {
                wc_add_notice(__("Error ms102 : " . MNS_NOTICE_CHECK_TRANSACTION, $this->domain), 'error');
                return;
            }
    
            if ($data_status[0]->status == "success" && strlen($data_status[0]->mskey) > 9999) {
                wc_add_notice(__("Error ms100 : " . MNS_NOTICE_CHECK_TRANSACTION . $data_status[0]->status, $this->domain), 'error');
                return;
            }
    
            $tranId = $data_status[0]->transaction_ID;
            $mskey = $data_status[0]->mskey;
            
            update_post_meta($order_id, 'MNS_transaction_orderid', $ms_body['order_id']);
            update_post_meta($order_id, 'MNS_transaction', $tranId);
            update_post_meta($order_id, 'MNS_PAYMENT_KEY', $mskey);

            wp_redirect(get_site_url() . "/mspaylink/" . $order_id);

        } else {
            $response = wp_remote_post(MNS_API_URL_CREATE_LINK_PAYMENT, array(
                'method' => 'POST',
                'timeout' => 120,
                'body' => $ms_body
            ));

            if (is_wp_error($response)) {
                wc_add_notice(__("Error : " . MNS_NOTICE_ERROR_SETUP, $this->domain), 'error');
                return;
            }
    
            $data_status = json_decode($response["body"]);
            if (empty($data_status) || $data_status[0]->status != "success") {
                wc_add_notice(__("Error ms102 : " . MNS_NOTICE_CHECK_TRANSACTION, $this->domain), 'error');
                return;
            }
    
            if ($data_status[0]->status == "success" && strlen($data_status[0]->mskey) > 9999) {
                wc_add_notice(__("Error ms100 : " . MNS_NOTICE_CHECK_TRANSACTION . $data_status[0]->status, $this->domain), 'error');
                return;
            }

            $tranId = $data_status[0]->transaction_ID;
            $linkPayment = $data_status[0]->link_payment;

            update_post_meta($order_id, 'MNS_transaction_orderid', $ms_body['order_id']);
            update_post_meta($order_id, 'MNS_transaction', $tranId);
            wp_redirect($linkPayment); 
        }
    }

    public function create_payment_transaction($order_id, $ms_body, $ms_template_payment, $gateways) {
        
        $response = wp_remote_post(MNS_API_URL_CREATE, array(
            'method' => 'POST',
            'timeout' => 120,
            'body' => $ms_body
        ));
        
        if (is_wp_error($response)) {
            wc_add_notice(__("Error : " . MNS_NOTICE_ERROR_SETUP, $this->domain), 'error');
            return;
        }

        $data_status = json_decode($response["body"]);
        if (empty($data_status) || $data_status[0]->status != "success") {
            wc_add_notice(__("Error ms102 : " . MNS_NOTICE_CHECK_TRANSACTION, $this->domain), 'error');
            return;
        }

        if ($data_status[0]->status == "success" && strlen($data_status[0]->mskey) > 9999) {
            wc_add_notice(__("Error ms100 : " . MNS_NOTICE_CHECK_TRANSACTION . $data_status[0]->status, $this->domain), 'error');
            return;
        }

        $tranId = $data_status[0]->transaction_ID;
        $mskey = $data_status[0]->mskey;
        
        update_post_meta($order_id, 'MNS_transaction_orderid', $ms_body['order_id']);
        update_post_meta($order_id, 'MNS_transaction', $tranId);
        update_post_meta($order_id, 'MNS_PAYMENT_KEY', $mskey);
        
        if ($ms_template_payment == "1") {
            wp_redirect(get_site_url() . "/mspaylink/" . $order_id);
        }
        
        if ($ms_template_payment == "2") {
            
            _e('<div align="center">
            <div id="moneyspace-payment" 
                    template="2"
                    lang="eng"
                    ms-title="' . $gateways['moneyspace']->settings['title'] . '" 
                    ms-key="' . $mskey . '" 
                    description="false">
            </div>
            </div>');
            wp_register_script('cc_mspayment', MNS_PAYMENT_FORM_JS, array(), false, true);
            wp_enqueue_script('cc_mspayment', MNS_PAYMENT_FORM_JS, array(), false, true);
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
            }

            .MuiContainer-maxWidthXs {
                max-width: 100%;
            }");    
            
            wp_register_style( 'custom-css-handle', false );
            wp_enqueue_style( 'custom-css-handle' );
            wp_add_inline_style( 'custom-css-handle', $customStyle );
        } else {
            wp_redirect(get_site_url() . "/mspaylink/" . $order_id);
        }
    }

    public function create_payment_transaction_v2($order_id, $ms_secret_key, $ms_body, $ms_template_payment, $gateways) {
        $response = wp_remote_post(MNS_API_URL_V2_CREATE_PAYMENT, array(
            'method' => 'POST',
            'timeout' => 120,
            'body' => $ms_body
        ));
        (new Mslogs())->insert($response["body"], 1, 'Create Transaction Card', date("Y-m-d H:i:s"), json_encode($ms_body));

        if (is_wp_error($response)) {
            wc_add_notice(__(MNS_NOTICE_ERROR_SETUP, $this->domain), 'error');
            return;
        }

        $json_tranId_status = json_decode($response["body"]);
        if ($json_tranId_status[0]->status == "NotFound") {
            wc_add_notice(__(MNS_NOTICE_ERROR_SETUP, $this->domain), 'error');
            return;
        } 
        $trans_ID = "Transaction ID";
        $json_tranId = json_decode($response["body"]);
        $tranId = $json_tranId[0]->$trans_ID;
        $ms_time = $ms_body['timeHash'];
        $ms_secret_id = $ms_body['secreteID'];
        $hash_link = hash_hmac('sha256', $tranId . $ms_time, $ms_secret_key);
        $link = "https://www.moneyspace.net/merchantapi/makepayment/linkpaymentcard?transactionID=" . $tranId . "&timehash=" . $ms_time . "&secreteID=" . $ms_secret_id . "&hash=" . $hash_link;
        update_post_meta($order_id, 'MNS_transaction', $tranId);
        update_post_meta($order_id, 'MNS_transaction_orderid', $ms_body["customer_order_id"]);
        update_post_meta($order_id, 'MNS_LINK', $link);
        WC()->cart->empty_cart();
        wp_redirect($link);
    }

    public function call_payment_getpay($order_id, $mskey) {
        $ms_body["mskey"] = $mskey;
        $response = wp_remote_post(MNS_API_URL_GETPAY, array(
            'method' => 'POST',
            'timeout' => 120,
            'body' => $ms_body
        ));
        (new Mslogs())->insert($response["body"], 2, 'Create Transaction Card 2', date("Y-m-d H:i:s"), json_encode($ms_body));

        if (is_wp_error($response)) {
            wc_add_notice(__("Error : " . MNS_NOTICE_ERROR_SETUP, $this->domain), 'error');
            return;
        }

        $data_status = json_decode($response["body"]);
        $mspay = json_encode($data_status->data);
        update_post_meta($order_id, 'MNS_PAYMENT_PAY', $mspay);
        return $mspay;
    }

    public function init_form_fields()
    {
        $this->form_fields = array(
            'header_setting' => array(
                'title' => __(set_title_html(MNS_FORM_FIELD_HEADER_SETTING), $this->domain),
                'type' => 'title'
            ),
            'enabled' => array(
                'title' => __(MNS_FORM_FIELD_ENABLE, $this->domain),
                'type' => 'checkbox',
                'label' => __(MNS_FORM_FIELD_ENABLE_LABEL, $this->domain),
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
                'title' => __(MNS_FORM_FIELD_DESCRIPTION, $this->domain),
                'type' => 'textarea',
                'default' => __("", $this->domain),
                'desc_tip' => true,
                'description' => __(MNS_ADMIN_SETTING_CC_DESC, $this->domain)
            ),
            'desc_domain_webhook1' => array(
                'title' => __(set_title_html(MNS_HEAD_DOMAIN_WEBHOOK), $this->domain),
                'type' => 'title',
                'desc_tip' => true,
                'description' => MNS_DOMAIN_WEBHOOK
            ),
            'ms_domain' => array(
                'title' => __(MNS_YOUR_DOMAIN . " : <code>" . get_site_url() . "</code>", $this->domain),
                'type' => 'title'
            ),
            'ms_webhook' => array(
                'title' => __(MNS_YOUR_WEBHOOK . " : <code>" . get_site_url() . "/ms/webhook" . "</code>", $this->domain),
                'type' => 'title'
            ),
            'header_setting_ms' => array(
                'title' => __(set_title_html(MNS_FORM_FIELD_HEADER_SETTING_MS), $this->domain),
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
                'title' => __(MNS_FEE_HEADER, $this->domain),
                'type' => 'select',
                'class' => 'wc-enhanced-select',
                'default' => 'include',
                'desc_tip' => true,
                'options' => ["include" => MNS_FEE_INCLUDE,"exclude" => MNS_FEE_EXCLUDE],
                'description' => __(MNS_FEE_HEADER, $this->domain)
            ),
            'order_status_if_success' => array(
                'title' => __(MNS_FORM_FIELD_SET_ORDER_STATUS, $this->domain),
                'type' => 'select',
                'class' => 'wc-enhanced-select',
                'default' => 'wc-completed',
                'desc_tip' => true,
                'options' => wc_get_order_statuses(),
                'description' => __(MNS_ADMIN_SETTING_STATUS_AFTER_PAY, $this->domain)
            ),
            'ms_stock_setting' => array(
                'title' => __(MNS_STOCKSETTING_HEAD, $this->domain),
                'type' => 'select',
                'class' => 'wc-enhanced-select',
                'default' => 'Enable',
                'desc_tip' => true,
                'options' => ["Disable" => MNS_STOCKSETTING_DISABLE, "Enable" => MNS_STOCKSETTING_ENABLE]
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
                'title' => __(set_title_html(MNS_FORM_FIELD_HEADER_SETTING_MS . " ( " . MNS_TYPE_PAYMENT_QR . " )"), $this->domain),
                'type' => 'title'
            ),
            'header_setting_QRPROM_Link' => array(
                'title' => __('<a href="admin.php?page=wc-settings&tab=checkout&section=moneyspace_qrprom">' . MNS_SETTING_LINK . '</a>', $this->domain),
                'type' => 'title'
            ),
            'header_setting_INSTALLMENT' => array(
                'title' => __(set_title_html(MNS_FORM_FIELD_HEADER_SETTING_MS . " ( ผ่อนชำระรายเดือน )"), $this->domain),
                'type' => 'title'
            ),
            'header_setting_installment_Link' => array(
                'title' => __('<a href="admin.php?page=wc-settings&tab=checkout&section=moneyspace_installment">' . MNS_SETTING_LINK . '</a>', $this->domain),
                'type' => 'title'
            ),
            'header_setting_ui' => array(
                'title' => __(set_title_html(MNS_FORM_FIELD_HEADER_SETTING_UI . ' ( Moneyspace )'), $this->domain),
                'type' => 'title'
            ),
            'ms_firstname' => array(
                'title' => __('' . MNS_FORM_FIELD_MS_FIRSTNAME, $this->domain),
                'type' => 'checkbox',
                'label' => __(MNS_FORM_FIELD_MS_LABEL, $this->domain),
                'default' => 'yes'
            ),
            'ms_lastname' => array(
                'title' => __('' . MNS_FORM_FIELD_MS_LASTNAME, $this->domain),
                'type' => 'checkbox',
                'label' => __(MNS_FORM_FIELD_MS_LABEL, $this->domain),
                'default' => 'yes'
            ),
            'ms_email' => array(
                'title' => __('' . MNS_FORM_FIELD_MS_EMAIL, $this->domain),
                'type' => 'checkbox',
                'label' => __(MNS_FORM_FIELD_MS_LABEL, $this->domain),
                'default' => 'yes'
            ),
            'ms_phone' => array(
                'title' => __('' . MNS_FORM_FIELD_MS_PHONE, $this->domain),
                'type' => 'checkbox',
                'label' => __(MNS_FORM_FIELD_MS_LABEL, $this->domain),
                'default' => 'yes'
            ),
            'ms_address' => array(
                'title' => __('' . MNS_FORM_FIELD_MS_ADDRESS, $this->domain),
                'type' => 'checkbox',
                'label' => __(MNS_FORM_FIELD_MS_LABEL, $this->domain),
                'default' => 'yes'
            ),
            'header_setting_check_connection' => array(
                'title' => __(set_title_html(MNS_FORM_FIELD_HEADER_SETTING_CHECK_CONNECTION), $this->domain),
                'type' => 'title'
            ),
        );
    }

    public function thankyou_page()
    {
        if ($this->instructions) {
            _e(wpautop(wptexturize($this->instructions)));
        }
    }

    public function payment_fields()
    {
        $gateways = WC()->payment_gateways->get_available_payment_gateways();
        $ms_template_payment = $gateways['moneyspace']->settings['ms_template_payment'];
        $ms_fees = $gateways['moneyspace']->settings['fee_setting'];

        if ($description = $this->get_description()) {
            _e(wpautop(wptexturize($description)));
        }
        if ($ms_template_payment == "1" && $ms_fees == "include") {
            wp_register_style( "moneyspace-style", MNS_PAYMENT_FORM_CSS, array(), "1.0.0", "");
            wp_enqueue_style( "moneyspace-style", MNS_PAYMENT_FORM_CSS, array(), "1.0.0", "");

            require_once MNS_ROOT . '/templates/credit-cards/mns-cc-tpl-1.php';
        }
    }
    

    /**
     * Process the payment and return the result.
     *
     * @param int $order_id
     * @return array
     */
    public function process_payment($order_id)
    {
        $MNS_special_instructions_to_merchant = get_post_meta($order_id, 'MNS_special_instructions_to_merchant', true);
        $message_card = sanitize_text_field($_POST["message_card"]);
        
        if (strlen($MNS_special_instructions_to_merchant) <= 150) {
            if (get_woocommerce_currency() == "THB") {
                update_post_meta($order_id, 'MNS_special_instructions_to_merchant', $message_card);
                update_post_meta($order_id, 'MNS_PAYMENT_TYPE', "Card");
                delete_post_meta($order_id, 'MNS_transaction');

                $cardNumber = sanitize_text_field($_POST["cardNumber"] ?? $_POST["cardnumber"]);
                $cardHolder = sanitize_text_field($_POST["cardHolder"]?? $_POST["cardholder"]);
                $cardExpDate = sanitize_text_field($_POST["cardExpDate"]?? $_POST["cardexpdate"]);
                $cardExpDateYear = sanitize_text_field($_POST["cardExpDateYear"]?? $_POST["cardexpdateyear"]);
                $cardCVV = sanitize_text_field($_POST["cardCVV"]?? $_POST["cardcvv"]);
                $MNS_CARD = $cardNumber."|".$cardHolder."|".$cardExpDate."|".$cardExpDateYear."|".$cardCVV;
                update_post_meta($order_id, 'MNS_CARD', base64_encode($MNS_CARD));

                $mspay = sanitize_text_field($_POST["mspay"]);
                update_post_meta($order_id, 'MNS_PAYMENT_PAY', $mspay);
                $order = wc_get_order($order_id);
                return $this->_process_external_payment($order);
            } else {
                wc_add_notice(__(MNS_NOTICE_CURRENCY, $this->domain), 'error');
                throw new Exception( __(MNS_NOTICE_CURRENCY, $this->domain) );
            }
        } else {
            wc_add_notice(__("Error : Message to the store (150 characters maximum)", $this->domain), 'error');
            throw new Exception( __("Error : Message to the store (150 characters maximum)", $this->domain) );
        }
    } // End Process

    public function paymentgateway_form($order_id)
    {
        $order = wc_get_order($order_id);
        $order_amount = $order->get_total();
        $payment_gateway_id = MNS_ID;
        $payment_gateways = WC_Payment_Gateways::instance();
        $payment_gateway = $payment_gateways->payment_gateways()[$payment_gateway_id];
        $gateways = WC()->payment_gateways->get_available_payment_gateways();
        $ms_order_select = $payment_gateway->settings['order_status_if_success'];
        $ms_secret_id = $payment_gateway->settings['secret_id'];
        $ms_secret_key = $payment_gateway->settings['secret_key'];
        $ms_fee = $payment_gateway->settings['fee_setting'];
        $ms_template_payment = $gateways['moneyspace']->settings['ms_template_payment'];

        $MNS_special_instructions_to_merchant = get_post_meta($order_id, 'MNS_special_instructions_to_merchant', true);
        $ms_time = date("YmdHis");
        $items = $order->get_items();
        $items_msg = set_item_message($items);
        $return_url = get_site_url() . "/process/payment/" . $order_id;
        
        $error_list = array("wc-failed", "wc-cancelled", "wc-refunded");
        if (in_array($ms_order_select, $error_list)) {
            _e("Error : " . MNS_NOTICE_ERROR_CONTINUE);
        }

        $body_post = set_body($order_id, $order, $gateways, $order_amount, $items_msg, $MNS_special_instructions_to_merchant, $ms_fee, $ms_time);
            
        if ($ms_fee == "include") {
            $ms_body = set_req_message($ms_secret_id, $ms_secret_key, $body_post, "card", $return_url);
            return $this->create_payment_transaction_v3($order_id, $ms_body, $ms_template_payment, $gateways);
        }

        if ($ms_fee == "exclude") {
            $body_post["message"] = "Order ID#".$order_id;
            $body_post["successUrl"] = $return_url;
            $body_post["failUrl"] = $return_url;
            $body_post["cancelUrl"] = $return_url;
            $hash_data = $body_post["firstname"] . $body_post["lastname"] . $body_post["email"] . $body_post["phone"] 
            . $body_post["amount"] . $body_post["currency"] . preg_replace( "/<br>|\n/", "", $body_post["description"] ) 
            . $body_post["address"] . $body_post["message"] . $body_post["feeType"] . $body_post["timeHash"] . $body_post["customer_order_id"] 
            . $body_post["gatewayType"] . $body_post["successUrl"] . $body_post["failUrl"] . $body_post["cancelUrl"];
            $hash_body = hash_hmac('sha256', $hash_data, $ms_secret_key);
            $ms_body = array('secreteID' => $ms_secret_id, 'firstname' => $body_post["firstname"], 'lastname' => $body_post["lastname"], 'email' => $body_post["email"], 'phone' => $body_post["phone"], 'amount' => $body_post["amount"], 'currency' => $body_post["currency"], 'description' => preg_replace( "/<br>|\n/", "", $body_post["description"] ), 'address' => $body_post["address"], 'message' => $body_post["message"], 'feeType' => $body_post["feeType"], 'customer_order_id' => $body_post["customer_order_id"], 'gatewayType' => $body_post["gatewayType"], 'timeHash' => $body_post["timeHash"], 'hash' => $hash_body, 'successUrl' => $body_post["successUrl"], 'failUrl' => $body_post["failUrl"], 'cancelUrl' => $body_post["cancelUrl"]);
            return $this->create_payment_transaction_v2($order_id, $ms_secret_key, $ms_body, $ms_template_payment, $gateways);
        }
    }

    public function custom_order_pay($order_id)
    {
        // TODO: Show payment form in here
        $order = new WC_Order($order_id);
        if (strtolower($order->get_status()) != "cancelled")
        {
            wp_register_style( "bootstrap-style", MNS_ROOT_URL ."includes/libs/bootstrap-4.6.0-dist/css/bootstrap.css", array(), "4.6.0", "all" );
            wp_enqueue_style( "bootstrap-style", MNS_ROOT_URL ."includes/libs/bootstrap-4.6.0-dist/css/bootstrap.css", array(), "4.6.0", "all");
            require_once MNS_ROOT . '/templates/credit-cards/mns-cc-tpl-1.php';
            
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

    public function after_checkout_validation($data, $errors)
    {
        if($_POST["cardNumber"] == "" && $data["payment_method"] == "moneyspace" && $data["ms_template_payment"] == "1")
            $errors->add( 'validation', __( 'Please input Card Number.' ));

        if($_POST["cardHolder"] == "" && $data["payment_method"] == "moneyspace" && $data["ms_template_payment"] == "1")
            $errors->add( 'validation', __( 'Please input Card Holder.' ));

        if($_POST["cardExpDate"] == "" && $data["payment_method"] == "moneyspace" && $data["ms_template_payment"] == "1")
            $errors->add( 'validation', __( 'Please input Card Exp Date.' ));

        if($_POST["cardExpDateYear"] == "" && $data["payment_method"] == "moneyspace" && $data["ms_template_payment"] == "1")
            $errors->add( 'validation', __( 'Please input Card Exp Year.' ));

        if($_POST["cardCVV"] == "" && $data["payment_method"] == "moneyspace" && $data["ms_template_payment"] == "1")
            $errors->add( 'validation', __( 'Please input Card CVV.' ));
    }
    
    public function avia_thank_you($thank_you_text, $order)
    {
        $added_text = '';
        return $added_text;
    }

}
