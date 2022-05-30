<?php
date_default_timezone_set("Asia/Bangkok");

class MNS_Payment_Gateway_QR extends WC_Payment_Gateway
{

    public function __construct()
    {
        $this->domain = 'ms_payment_qrprom';

        $this->id = MNS_ID_QRPROM;
        $this->title = __($this->get_option('title'), $this->domain);
        $this->icon = apply_filters('woocommerce_custom_gateway_icon', MNS_LOGO_QR, '');
        $this->method_title = __(MNS_METHOD_TITLE . "( " . MNS_TYPE_PAYMENT_QR . " )", $this->domain);
        $this->method_description = __(MNS_DESCRIPTION_QR, $this->domain);
        $this->has_fields = true;

        $this->init_form_fields();
        $this->init_settings();

        $this->enabled = $this->get_option('enabled');
        $this->description = $this->get_option('description');

        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        add_action('woocommerce_thankyou_custom', array($this, 'thankyou_page'));
        add_action('woocommerce_receipt_' . $this->id, array($this, 'paymentgateway_form'), 10, 1);
    }

    public function create_payment_transaction($order_id, $ms_body, $ms_template_payment, $gateways, $payment_gateway_qr) {
        $response = wp_remote_post(MNS_API_URL_CREATE_LINK_PAYMENT, array(
            'method' => 'POST',
            'timeout' => 120,
            'body' => $ms_body
        ));
        (new Mslogs())->insert($response["body"], 3, 'Create Transaction QR', date("Y-m-d H:i:s"), json_encode($ms_body));
        if (is_wp_error($response)) {
            wc_add_notice(__(MNS_NOTICE_ERROR_SETUP, $this->domain), 'error');
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
        $image_qrprom = $data_status[0]->image_qrprom;

        update_post_meta($order_id, 'MNS_transaction_orderid', $ms_body["order_id"]);
        update_post_meta($order_id, 'MNS_transaction', $tranId);
        update_post_meta($order_id, 'MNS_PAYMENT_IMAGE_QRPROMT', $image_qrprom);
        update_post_meta($order_id, 'MNS_QR_TIME', time());
        $order = wc_get_order($order_id);
        $items = $order->get_items();

        $template = $payment_gateway_qr->settings['template'] ?? 'template_1';
        $chooseTemplate = $template == 'template_1' ? 'mns_tpl_qrnone_1': 'mns_tpl_qrnone_2';
        load_template( plugin_dir_path( __DIR__ ).'templates/qrnone/'.$chooseTemplate.'.php', false, array(
            'order_id' => $order_id,
            'payment_gateway_qr' => $payment_gateway_qr,
            'image_qrprom' => $image_qrprom
        ));
    }

    public function init_form_fields()
    {
        $this->form_fields = array(
            'header_setting' => array(
                'title' => __('<h1><b> ' . MNS_FORM_FIELD_HEADER_SETTING . ' </b></h1>', $this->domain),
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
                'default' => __('QR Code PromptPay', $this->domain),
                'desc_tip' => true,
            ),
            'order_status_if_success' => array(
                'title' => __(MNS_FORM_FIELD_SET_ORDER_STATUS, $this->domain),
                'type' => 'select',
                'class' => 'wc-enhanced-select',
                'default' => 'wc-processing',
                'desc_tip' => true,
                'options' => wc_get_order_statuses()
            ),
            'ms_stock_setting' => array(
                'title' => __(MNS_STOCKSETTING_HEAD, $this->domain),
                'type' => 'select',
                'class' => 'wc-enhanced-select',
                'default' => 'Disable',
                'desc_tip' => true,
                'options' => ["Disable" => MNS_STOCKSETTING_DISABLE, "Enable" => MNS_STOCKSETTING_ENABLE]
            ),
            'auto_cancel' => array(
                'title' => __(MNS_FORM_FIELD_SET_QRNONE_TIMEOUT, $this->domain),
                'type' => 'select',
                'class' => 'wc-enhanced-select',
                'default' => 1200,
                'desc_tip' => true,
                'options' => [900 => "15 นาที",1200 => "20 นาที",1500 => "25 นาที",1800 => "30 นาที"]
            ),
            'template' => array(
                'title' => __(MNS_FORM_FIELD_TEMPLATE, $this->domain),
                'type' => 'select',
                'class' => 'wc-enhanced-select',
                'default' => 'template_1',
                'desc_tip' => true,
                'options' => ['template_1' => 'Template 1', 'template_2' => 'Template 2']
            ),
            'description' => array(
                'title' => __(MNS_FORM_FIELD_DESCRIPTION, $this->domain),
                'type' => 'textarea',
                'default' => __("", $this->domain),
                'desc_tip' => true,
            )
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
        $payment_gateway_id = MNS_ID;
        $payment_gateways = WC_Payment_Gateways::instance();
        $payment_gateway = $payment_gateways->payment_gateways()[$payment_gateway_id];
        $gateways = WC()->payment_gateways->get_available_payment_gateways();
        $ms_message2store = $gateways['moneyspace']->settings['message2store_setting'];

        if ($description = $this->get_description()) {
            _e(wpautop(wptexturize($description)));
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
        $message_qr = sanitize_text_field($_POST["message_qr"]);
        if (strlen($MNS_special_instructions_to_merchant) <= 150) {
            if (get_woocommerce_currency() == "THB") {
                update_post_meta($order_id, 'MNS_special_instructions_to_merchant', $message_qr);
                update_post_meta($order_id, 'MNS_PAYMENT_TYPE', "Qrnone");
                delete_post_meta($order_id, 'MNS_transaction');
                $order = wc_get_order($order_id);
                return $this->_process_external_payment($order);
            } else {
                wc_add_notice(__(MNS_NOTICE_CURRENCY, $this->domain), 'error');
                return;
            }
        } else {
            wc_add_notice(__("Error : Enter special instructions to merchant again", $this->domain), 'error');
            return;
        }
    } // End Process


    public function paymentgateway_form($order_id)
    {
        $order = wc_get_order($order_id);
        $order_amount = $order->get_total();

        $payment_gateway_id = MNS_ID;
        $payment_gateway_qr_id = MNS_ID_QRPROM;

        $payment_gateways = WC_Payment_Gateways::instance();

        $payment_gateway = $payment_gateways->payment_gateways()[$payment_gateway_id];
        $payment_gateway_qr = $payment_gateways->payment_gateways()[$payment_gateway_qr_id];

        $gateways = WC()->payment_gateways->get_available_payment_gateways();
        $ms_order_select = $payment_gateway->settings['order_status_if_success'];
        $ms_secret_id = $payment_gateway->settings['secret_id'];
        $ms_secret_key = $payment_gateway->settings['secret_key'];

        $MNS_PAYMENT_TYPE = get_post_meta($order->id, 'MNS_PAYMENT_TYPE', true);

        $ms_template_payment = $payment_gateway->settings['ms_template_payment'];

        $MNS_special_instructions_to_merchant = get_post_meta($order_id, 'MNS_special_instructions_to_merchant', true);

        $ms_time = date("YmdHis");

        $items_order = new WC_Order($order_id);
        $items = $order->get_items();
        $items_msg = set_item_message($items);
        $return_url = get_site_url() . "/process/payment/" . $order_id;
        $ms_fee = "include";
        
        $error_list = array("wc-failed", "wc-cancelled", "wc-refunded");
        if (in_array($ms_order_select, $error_list)) {
            wc_add_notice(__(MNS_NOTICE_ERROR_CONTINUE, $this->domain), 'error');
        }

        if (strlen($message_ins) > 150) {
            wc_add_notice(__("Message to the store (150 characters maximum)", $this->domain), 'error');
            return;
        }

        $body_post = set_body($order_id, $order, $gateways, $order_amount, $items_msg, $MNS_special_instructions_to_merchant, $ms_fee, $ms_time);
        $ms_body = set_req_message($ms_secret_id, $ms_secret_key, $body_post, "qrnone", $return_url);
        unset($ms_body["agreement"]);
        return $this->create_payment_transaction($order_id, $ms_body, $ms_template_payment, $gateways, $payment_gateway_qr);
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
