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
        $this->title = $this->get_option('title');
        $this->icon = apply_filters('woocommerce_custom_gateway_icon', MNS_LOGO, '');
        $this->method_title = MNS_METHOD_TITLE;
        $this->method_description = MNS_DESCRIPTION;
        $this->has_fields = true;

        $this->init_form_fields();
        $this->init_settings();

        $this->enabled = $this->get_option('enabled');
        $this->description = $this->get_option('description');

        add_action('woocommerce_receipt_' . $this->id, array($this, 'paymentgateway_form'), 10, 1);
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        add_action('woocommerce_thankyou_custom', array($this, 'thankyou_page'));
        add_filter('woocommerce_thankyou_order_received_text', array($this, 'avia_thank_you'), 10, 2 );
    }


    public function create_payment_transaction_v3($order_id, $ms_body) {

        $response = wp_remote_post(MNS_API_URL_CREATE_LINK_PAYMENT, array(
                'method' => 'POST',
                'timeout' => 120,
                'body' => $ms_body
            ));

        if (is_wp_error($response)) {
            $log_body = function_exists('moneyspace_filter_sensitive_data') ? moneyspace_filter_sensitive_data($ms_body) : $ms_body;
            (new Mslogs())->insert($response->get_error_message(), 1, 'Create Link Payment (HTTP error)', date("Y-m-d H:i:s"), json_encode($log_body));
            wc_add_notice('Error : ' . MNS_NOTICE_ERROR_SETUP, 'error');
            return;
        }
        
        $body = wp_remote_retrieve_body($response);

        $data_status = json_decode($body);
        if (empty($data_status) || !isset($data_status[0]->status) || $data_status[0]->status != "success") {
            wc_add_notice('Error ms102 : ' . MNS_NOTICE_CHECK_TRANSACTION, 'error');
            return;
        }
        
        // Note: MSKey is supposed to be long for security - it's an encrypted payment token
        // Removed incorrect length validation that was preventing successful payments

        $tranId = $data_status[0]->transaction_ID ?? '';
        $linkPayment = $data_status[0]->link_payment ?? '';

        update_post_meta($order_id, 'MNS_transaction_orderid', $ms_body['order_id'] ?? '');
        update_post_meta($order_id, 'MNS_transaction', $tranId);

        $allowed_host = wp_parse_url($linkPayment, PHP_URL_HOST);
        if (!empty($allowed_host)) {
            $allowed_redirect_filter = static function ($hosts) use ($allowed_host) {
                $hosts[] = $allowed_host;
                return array_unique($hosts);
            };
            add_filter('allowed_redirect_hosts', $allowed_redirect_filter);
            wp_safe_redirect(esc_url_raw($linkPayment));
            remove_filter('allowed_redirect_hosts', $allowed_redirect_filter);
            exit;
        }

        wp_safe_redirect(esc_url_raw($linkPayment));
        exit;
    }

    public function init_form_fields()
    {
        $this->form_fields = array(
            'header_setting' => array(
                'title' => set_title_html(MNS_FORM_FIELD_HEADER_SETTING),
                'type' => 'title'
            ),
            'enabled' => array(
                'title' => MNS_FORM_FIELD_ENABLE,
                'type' => 'checkbox',
                'label' => MNS_FORM_FIELD_ENABLE_LABEL,
                'default' => 'no'
            ),
            'title' => array(
                'title' => __('Title', 'ms_payment'),
                'type' => 'text',
                'description' => __('This controls the title which the user sees during checkout.', 'ms_payment'),
                'default' => __('Pay by Card 3D secured', 'ms_payment'),
                'desc_tip' => true,
            ),
            'description' => array(
                'title' => MNS_FORM_FIELD_DESCRIPTION,
                'type' => 'textarea',
                'default' => '',
                'desc_tip' => true,
                'description' => MNS_ADMIN_SETTING_CC_DESC
            ),
            'desc_domain_webhook1' => array(
                'title' => set_title_html(MNS_HEAD_DOMAIN_WEBHOOK),
                'type' => 'title',
                'desc_tip' => true,
                'description' => MNS_DOMAIN_WEBHOOK
            ),
            'ms_domain' => array(
                'title' => MNS_YOUR_DOMAIN . " : <code>" . esc_html(get_site_url()) . "</code>",
                'type' => 'title'
            ),
            'ms_webhook' => array(
                'title' => MNS_YOUR_WEBHOOK . " : <code>" . esc_html(get_site_url()) . "/ms/webhook" . "</code>",
                'type' => 'title'
            ),
            'header_setting_ms' => array(
                'title' => set_title_html(MNS_FORM_FIELD_HEADER_SETTING_MS),
                'type' => 'title'
            ),
            'secret_id' => array(
                'title' => __('secret_id', 'ms_payment'),
                'type' => 'input',
                'default' => '',
                'desc_tip' => true,
            ),
            'secret_key' => array(
                'title' => __('secret_key', 'ms_payment'),
                'type' => 'input',
                'default' => '',
                'desc_tip' => true,
            ),
            'fee_setting' => array(
                'title' => MNS_FEE_HEADER,
                'type' => 'select',
                'class' => 'wc-enhanced-select',
                'default' => 'include',
                'desc_tip' => true,
                'options' => ["include" => MNS_FEE_INCLUDE,"exclude" => MNS_FEE_EXCLUDE],
                'description' => MNS_FEE_HEADER
            ),
            'order_status_if_success' => array(
                'title' => MNS_FORM_FIELD_SET_ORDER_STATUS,
                'type' => 'select',
                'class' => 'wc-enhanced-select',
                'default' => 'wc-completed',
                'desc_tip' => true,
                'options' => wc_get_order_statuses(),
                'description' => MNS_ADMIN_SETTING_STATUS_AFTER_PAY
            ),
            'ms_stock_setting' => array(
                'title' => MNS_STOCKSETTING_HEAD,
                'type' => 'select',
                'class' => 'wc-enhanced-select',
                'default' => 'Enable',
                'desc_tip' => true,
                'options' => ["Disable" => MNS_STOCKSETTING_DISABLE, "Enable" => MNS_STOCKSETTING_ENABLE]
            ),
            'header_setting_QRPROM' => array(
                'title' => set_title_html(MNS_FORM_FIELD_HEADER_SETTING_MS . " ( " . MNS_TYPE_PAYMENT_QR . " )"),
                'type' => 'title'
            ),
            'header_setting_QRPROM_Link' => array(
                'title' => '<a href="admin.php?page=wc-settings&tab=checkout&section=moneyspace_qrprom">' . esc_html(MNS_SETTING_LINK) . '</a>',
                'type' => 'title'
            ),
            'header_setting_INSTALLMENT' => array(
                'title' => set_title_html(MNS_FORM_FIELD_HEADER_SETTING_MS . " ( ผ่อนชำระรายเดือน )"),
                'type' => 'title'
            ),
            'header_setting_installment_Link' => array(
                'title' => '<a href="admin.php?page=wc-settings&tab=checkout&section=moneyspace_installment">' . esc_html(MNS_SETTING_LINK) . '</a>',
                'type' => 'title'
            ),
            'header_setting_ui' => array(
                'title' => set_title_html(MNS_FORM_FIELD_HEADER_SETTING_UI . ' ( Moneyspace )'),
                'type' => 'title'
            ),
            'ms_firstname' => array(
                'title' => MNS_FORM_FIELD_MS_FIRSTNAME,
                'type' => 'checkbox',
                'label' => MNS_FORM_FIELD_MS_LABEL,
                'default' => 'yes'
            ),
            'ms_lastname' => array(
                'title' => MNS_FORM_FIELD_MS_LASTNAME,
                'type' => 'checkbox',
                'label' => MNS_FORM_FIELD_MS_LABEL,
                'default' => 'yes'
            ),
            'ms_email' => array(
                'title' => MNS_FORM_FIELD_MS_EMAIL,
                'type' => 'checkbox',
                'label' => MNS_FORM_FIELD_MS_LABEL,
                'default' => 'yes'
            ),
            'ms_phone' => array(
                'title' => MNS_FORM_FIELD_MS_PHONE,
                'type' => 'checkbox',
                'label' => MNS_FORM_FIELD_MS_LABEL,
                'default' => 'yes'
            ),
            'ms_address' => array(
                'title' => MNS_FORM_FIELD_MS_ADDRESS,
                'type' => 'checkbox',
                'label' => MNS_FORM_FIELD_MS_LABEL,
                'default' => 'yes'
            ),
            'header_setting_check_connection' => array(
                'title' => set_title_html(MNS_FORM_FIELD_HEADER_SETTING_CHECK_CONNECTION),
                'type' => 'title'
            ),
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
        if (get_woocommerce_currency() == "THB") {
            update_post_meta($order_id, 'MNS_PAYMENT_TYPE', "Card");
            delete_post_meta($order_id, 'MNS_transaction');

            $order = wc_get_order($order_id);
            return $this->_process_external_payment($order);
        } else {
            wc_add_notice(MNS_NOTICE_CURRENCY, 'error');
            return array(
                'result' => 'failure',
                'messages' => MNS_NOTICE_CURRENCY
            );
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

        $ms_time = date("YmdHis");
        $items = $order->get_items();
        $items_msg = set_item_message($items);
        $return_url = add_query_arg(
            'key',
            $order->get_order_key(),
            trailingslashit(get_site_url()) . 'process/payment/' . $order_id
        );
        
        $error_list = array("wc-failed", "wc-cancelled", "wc-refunded");
        if (in_array($ms_order_select, $error_list)) {
            echo esc_html("Error : " . MNS_NOTICE_ERROR_CONTINUE);
        }

        $body_post = set_body($order_id, $order, $gateways, $order_amount, $items_msg, "", $ms_fee, $ms_time);
        
        $ms_body = set_req_message($ms_secret_id, $ms_secret_key, $body_post, "card", $return_url);
        return $this->create_payment_transaction_v3($order_id, $ms_body);
    }

    protected function _process_external_payment($order)
    {
        // Redirect to payment page, where payment form will be printed
        return array(
            'result' => 'success',
            'redirect' => $order->get_checkout_payment_url(true)
        );
    }
    
    public function avia_thank_you($thank_you_text, $order)
    {
        $added_text = '';
        return $added_text;
    }

}
