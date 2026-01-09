<?php

namespace MoneySpace\Payments;
if ( !defined( 'ABSPATH')) exit;

use WC_Payment_Gateway;
use WC_Payment_Gateways;
use WC_Order;
use MoneySpace\Mslogs;
use DateTimeZone;
use DateTime;

// date_default_timezone_set("Asia/Bangkok");

class MNS_Payment_Gateway_QR extends WC_Payment_Gateway
{
    public $domain;
    public $instructions;

    public function __construct()
    {
        $this->domain = 'money-space';

        $this->id = MONEYSPACE_ID_QRPROM;
        $this->title = $this->get_option('title');
        $this->icon = apply_filters('moneyspace_gateway_icon', MONEYSPACE_LOGO_QR, '');
        $this->method_title = MONEYSPACE_METHOD_TITLE . "( " . MONEYSPACE_TYPE_PAYMENT_QR . " )";
        $this->method_description = MONEYSPACE_DESCRIPTION_QR;
        $this->has_fields = true;

        $this->init_form_fields();
        $this->init_settings();

        $this->enabled = $this->get_option('enabled');
        $this->description = $this->get_option('description');

        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        add_action('woocommerce_thankyou_' . $this->id, array($this, 'thankyou_page'));
        add_action('woocommerce_receipt_' . $this->id, array($this, 'paymentgateway_form'), 10, 1);
        add_filter('woocommerce_thankyou_order_received_text', array($this, 'avia_thank_you_qr'), 10, 2);

    }

    public function create_payment_transaction($order_id, $ms_body, $ms_template_payment, $gateways, $payment_gateway_qr) {

        $tz = 'Asia/Bangkok';
        $dt = new DateTime("now", new DateTimeZone($tz));

        $response = wp_remote_post(MONEYSPACE_API_URL_CREATE_LINK_PAYMENT, array(
            'method' => 'POST',
            'timeout' => 120,
            'body' => $ms_body
        ));

        if (is_wp_error($response)) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                $log_body = function_exists('moneyspace_filter_sensitive_data') ? moneyspace_filter_sensitive_data($ms_body) : $ms_body;
                (new Mslogs())->insert($response->get_error_message(), 3, 'Create Transaction QR (HTTP error)', $dt->format("Y-m-d H:i:s"), json_encode($log_body));
            }
            wc_add_notice(MONEYSPACE_NOTICE_ERROR_SETUP, 'error');
            return;
        }

        $body = wp_remote_retrieve_body($response);
        if (defined('WP_DEBUG') && WP_DEBUG) {
            $log_body = function_exists('moneyspace_filter_sensitive_data') ? moneyspace_filter_sensitive_data($ms_body) : $ms_body;
            (new Mslogs())->insert($body, 3, 'Create Transaction QR', $dt->format("Y-m-d H:i:s"), json_encode($log_body));
        }

        $data_status = json_decode($body);
        if (empty($data_status) || !isset($data_status[0]->status) || $data_status[0]->status != "success") {
            wc_add_notice("Error ms102 : " . MONEYSPACE_NOTICE_CHECK_TRANSACTION, 'error');
            return;
        }

        // Note: MSKey is supposed to be long for security - it's an encrypted payment token
        // Removed incorrect length validation that was preventing successful payments

        $tranId = $data_status[0]->transaction_ID ?? '';
        $image_qrprom = $data_status[0]->image_qrprom ?? '';
        $response_qr = wp_remote_get($image_qrprom, array('timeout' => 120));
        if (is_wp_error($response_qr)) {
            wc_add_notice(MONEYSPACE_NOTICE_ERROR_LOAD_QR, 'error');
            return;
        }
        
        $code = wp_remote_retrieve_response_code($response_qr);
        if ($code == 200) {
            $image_qrprom = base64_encode(wp_remote_retrieve_body($response_qr));
        }

        update_post_meta($order_id, 'MONEYSPACE_TRANSACTION_ORDERID', $ms_body["order_id"] ?? '');
        update_post_meta($order_id, 'MONEYSPACE_TRANSACTION', $tranId);
        update_post_meta($order_id, 'MONEYSPACE_PAYMENT_IMAGE_QRPROMT', $image_qrprom);
        update_post_meta($order_id, 'MONEYSPACE_QR_TIME', $dt->getTimestamp());
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
                'title' => moneyspace_set_title_html(MONEYSPACE_FORM_FIELD_HEADER_SETTING),
                'type' => 'title'
            ),
            'enabled' => array(
                'title' => MONEYSPACE_FORM_FIELD_ENABLE,
                'type' => 'checkbox',
                'label' => MONEYSPACE_FORM_FIELD_ENABLE_LABEL,
                'default' => 'no'
            ),
            'title' => array(
                'title' => __('Title', 'money-space'),
                'type' => 'text',
                'description' => __('This controls the title which the user sees during checkout.', 'money-space'),
                'default' => __('QR Code PromptPay', 'money-space'),
                'desc_tip' => true,
            ),
            'order_status_if_success' => array(
                'title' => MONEYSPACE_FORM_FIELD_SET_ORDER_STATUS,
                'type' => 'select',
                'class' => 'wc-enhanced-select',
                'default' => 'wc-completed',
                'desc_tip' => true,
                'options' => wc_get_order_statuses()
            ),
            'ms_stock_setting' => array(
                'title' => MONEYSPACE_STOCKSETTING_HEAD,
                'type' => 'select',
                'class' => 'wc-enhanced-select',
                'default' => 'Enable',
                'desc_tip' => true,
                'options' => ["Disable" => MONEYSPACE_STOCKSETTING_DISABLE, "Enable" => MONEYSPACE_STOCKSETTING_ENABLE]
            ),
            'auto_cancel' => array(
                'title' => MONEYSPACE_FORM_FIELD_SET_QRNONE_TIMEOUT,
                'type' => 'select',
                'class' => 'wc-enhanced-select',
                'default' => 1200,
                'desc_tip' => true,
                'options' => [900 => "15 นาที",1200 => "20 นาที",1500 => "25 นาที",1800 => "30 นาที"]
            ),
            'auto_check_result_time' => array(
                'title' => MONEYSPACE_FORM_FIELD_AUTO_CHECK_QR_RESULT_TIME,
                'type' => 'select',
                'class' => 'wc-enhanced-select',
                'default' => 5000,
                'desc_tip' => true,
                'options' => [5000 => "5 ".MONEYSPACE_FORM_FIELD_SECONDS,10000 => "10 ".MONEYSPACE_FORM_FIELD_SECONDS,15000 => "15 ".MONEYSPACE_FORM_FIELD_SECONDS, 30000 => "30 ".MONEYSPACE_FORM_FIELD_SECONDS]
            ),
            'enable_auto_check_result' => array(
                'title' => MONEYSPACE_FORM_FIELD_ENABLE_AUTO_CHECK_QR,
                'type' => 'checkbox',
                'label' => MONEYSPACE_FORM_FIELD_ENABLE_LABEL,
                'default' => 'yes'
            ),
            'template' => array(
                'title' => MONEYSPACE_FORM_FIELD_TEMPLATE,
                'type' => 'select',
                'class' => 'wc-enhanced-select',
                'default' => 'template_1',
                'desc_tip' => true,
                'options' => ['template_1' => 'Template 1', 'template_2' => 'Template 2']
            ),
            'description' => array(
                'title' => MONEYSPACE_FORM_FIELD_DESCRIPTION,
                'type' => 'textarea',
                'default' => '',
                'desc_tip' => true,
            )
        );
    }

    public function thankyou_page()
    {
        if ($this->instructions) {
            echo wp_kses_post(wpautop(wptexturize($this->instructions)));
        }
    }

    public function payment_fields()
    {
        if ($description = $this->get_description()) {
            echo wp_kses_post(wpautop(wptexturize($description)));
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
        $moneyspace_special_instructions_to_merchant = get_post_meta($order_id, 'MONEYSPACE_SPECIAL_INSTRUCTIONS_TO_MERCHANT', true);
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verification handled by WooCommerce checkout process
        $message_qr = sanitize_text_field(wp_unslash($_POST["message_qr"] ?? ''));
        if (strlen($moneyspace_special_instructions_to_merchant) <= 150) {
            if (get_woocommerce_currency() == "THB") {
                update_post_meta($order_id, 'MONEYSPACE_SPECIAL_INSTRUCTIONS_TO_MERCHANT', $message_qr);
                update_post_meta($order_id, 'MONEYSPACE_PAYMENT_TYPE', "Qrnone");
                delete_post_meta($order_id, 'MONEYSPACE_TRANSACTION');
                $order = wc_get_order($order_id);
                return $this->_process_external_payment($order);
            } else {
                wc_add_notice(MONEYSPACE_NOTICE_CURRENCY, 'error');
                return array(
                    'result' => 'failure',
                    'messages' => MONEYSPACE_NOTICE_CURRENCY
                );
            }
        } else {
            wc_add_notice("Error : Enter special instructions to merchant again", 'error');
            return array(
                'result' => 'failure',
                'messages' => "Error : Enter special instructions to merchant again"
            );
        }
    } // End Process


    public function paymentgateway_form($order_id)
    {
        $tz = 'Asia/Bangkok';
        $dt = new DateTime("now", new DateTimeZone($tz));

        $order = wc_get_order($order_id);
        $order_amount = $order->get_total();

        $payment_gateway_id = MONEYSPACE_ID;
        $payment_gateway_qr_id = MONEYSPACE_ID_QRPROM;

        $payment_gateways = WC_Payment_Gateways::instance();

        $payment_gateway = $payment_gateways->payment_gateways()[$payment_gateway_id];
        $payment_gateway_qr = $payment_gateways->payment_gateways()[$payment_gateway_qr_id];

        $gateways = WC()->payment_gateways->get_available_payment_gateways();
        $moneyspace_order_select = $payment_gateway->settings['order_status_if_success'];
        $moneyspace_secret_id = $payment_gateway->settings['secret_id'];
        $moneyspace_secret_key = $payment_gateway->settings['secret_key'];
        $moneyspace_template_payment = $payment_gateway->settings['ms_template_payment'];
        $moneyspace_special_instructions_to_merchant = get_post_meta($order_id, 'MONEYSPACE_SPECIAL_INSTRUCTIONS_TO_MERCHANT', true);

        $moneyspace_time = $dt->format("YmdHis"); // date("YmdHis");

        $items_order = new WC_Order($order_id);
        $items = $order->get_items();
        $items_msg = moneyspace_set_item_message($items);
        $return_url = add_query_arg(
            'key',
            $order->get_order_key(),
            trailingslashit(get_site_url()) . 'process/payment/' . $order_id
        );
        $moneyspace_fee = "include";
        
        $error_list = array("wc-failed", "wc-cancelled", "wc-refunded");
        if (in_array($moneyspace_order_select, $error_list)) {
            wc_add_notice(MONEYSPACE_NOTICE_ERROR_CONTINUE, 'error');
        }

        $body_post = moneyspace_set_body($order_id, $order, $gateways, $order_amount, $items_msg, $moneyspace_special_instructions_to_merchant, $moneyspace_fee, $moneyspace_time);
        $moneyspace_body = moneyspace_set_req_message($moneyspace_secret_id, $moneyspace_secret_key, $body_post, "qrnone", $return_url);
        unset($moneyspace_body["agreement"]);
        return $this->create_payment_transaction($order_id, $moneyspace_body, $moneyspace_template_payment, $gateways, $payment_gateway_qr);
    }

    protected function _process_external_payment($order)
    {
        // Redirect to payment page, where payment form will be printed
        return array(
            'result' => 'success',
            'redirect' => $order->get_checkout_payment_url(true)
        );
    }

    public function avia_thank_you_qr($thank_you_text, $order)
    {
        moneyspace_update_order_status($order);
        return $thank_you_text;
    }
}



