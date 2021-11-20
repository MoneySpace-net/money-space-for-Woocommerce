<?php
/* @wordpress-plugin
 * Plugin Name:       Money Space
 * Plugin URI:        https://github.com/MoneySpace-net/money-space-for-Woocommerce/releases
 * Description:       Would you like to provide a streamlined and secure checkout experience for your customer? Every business does. Payment process is vital to the success eCommerce businesses. While WooCommerce merchants maximize their online products, we can help you take care of  payments and deliver a better overall customer experience for your online customers. By integrating your Magento website to your MoneySpace Payment Gateway account, your customer can pay for their products securely through credit card online. MoneySpace also supports up to 27 foreign currencies and Alipay/ WechatPay payments so you can begin receiving payment and expand your customer base worldwide. MoneySpace is PCI DSS certified and had been approved by Bank of Thailand as a payment method. To get to know more about MoneySpace payment gateway, visit our website at https://www.moneyspace.net
 * Version:           2.6.13
 * Author:            Money Space
 * Author URI:        https://moneyspace.net
 */

define('MNS_ID', 'moneyspace');
define('MNS_ID_QRPROM', 'moneyspace_qrprom');
define('MNS_ID_INSTALLMENT', 'moneyspace_installment');
define('MNS_TITLE', 'Money Space');
define('MNS_API_URL_PREFIX', 'https://a.moneyspace.net'); //'http://127.0.0.1:8000');
define('MNS_STATIC_URL_PREFIX', 'https://a.moneyspace.net/static'); //'http://127.0.0.1:8000/static');
define('MNS_API_URL_CREATE', MNS_API_URL_PREFIX . '/CreateTransactionID');
define('MNS_API_URL_CHECK', MNS_API_URL_PREFIX . '/CheckOrderID');
define('MNS_API_URL_GETPAY', MNS_API_URL_PREFIX . '/Getpay');
define('MNS_API_URL_PAY', MNS_API_URL_PREFIX . '/Pay');
define('MNS_API_URL_V2_CREATE_PAYMENT', 'https://www.moneyspace.net/merchantapi/v2/createpayment/obj');
define('MNS_API_URL_CREATE_INSTALLMENT', 'https://a.moneyspace.net/payment/Createinstallment/');
define('MNS_LOGO', MNS_STATIC_URL_PREFIX . '/img/type/Master_VISA_JCB_UNION_180.png');
define('MNS_LOGO_QR', MNS_STATIC_URL_PREFIX. '/img/type/QRCode_160.png');
define('MNS_LOGO_INSTALLMENT', MNS_STATIC_URL_PREFIX . '/img/type/Installment_220.png');
define('MNS_PAYMENT_JS', plugins_url( "includes/moneyspace_payment.js", __FILE__ )); // MNS_STATIC_URL_PREFIX . '/moneyspace_payment.js'); //
define('MNS_PAYMENT_FORM_JS', plugins_url( "includes/moneyspace_payment_form.js", __FILE__ ) );
define('MNS_PAYMENT_FORM_CSS', plugins_url( "includes/css/moneyspace.css", __FILE__ ) );
define('MNS_METHOD_TITLE', 'Money Space for WooCommerce');
// define('MNS_TIME_ZONE', "Asia/Bangkok");
define('MNS_CANCEL_TRANSACTION', 'https://a.moneyspace.net/merchantapi/cancelpayment');
define("MNS_ROOT", __DIR__."/");
define("MNS_ROOT_URL", plugin_dir_url(__FILE__));
define("MNS_PAYMENT_TYPE_CARD", "card");
define("MNS_PAYMENT_TYPE_QR", "qrnone");
define("MNS_PAYMENT_TYPE_INSTALLMENT", "installment");

if (get_locale() == 'th') {
    include_once "includes/th.php";
} else {
    include_once "includes/eng.php";
}

$active_plugins = apply_filters('active_plugins', get_option('active_plugins'));

if (in_array('woocommerce/woocommerce.php', $active_plugins)) {
    add_action('plugins_loaded', 'load_MS_Payemnt_Gateway');

    function load_MS_Payemnt_Gateway()
    {
        add_filter('woocommerce_payment_gateways', 'add_MS_Payemnt_Gateway');

        function add_MS_Payemnt_Gateway($gateways)
        {
            $gateways[] = 'MNS_Payment_Gateway';
            $gateways[] = 'MNS_Payment_Gateway_QR';
            $gateways[] = 'MNS_Payment_Gateway_INSTALLMENT';
            return $gateways;
        }
        wp_enqueue_style( "moneyspace-style", MNS_PAYMENT_FORM_CSS, array(), "1.0.0", "");
        require_once plugin_dir_path(__FILE__) . 'payment-gateway/class-woocommerce-moneyspace-payment-gateway.php';
        require_once plugin_dir_path(__FILE__) . 'payment-gateway/class-woocommerce-moneyspace-payment-gateway_QrProm.php';
        require_once plugin_dir_path(__FILE__) . 'payment-gateway/class-woocommerce-moneyspace-payment-gateway_installment.php';
    }

    add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'add_action_links');

    function add_action_links($links)
    {
        $mylinks = array(
            '<a href="' . admin_url('admin.php?page=wc-settings&tab=checkout&section=moneyspace') . '">' . MNS_SETTING_LINK . '</a>',
        );
        return array_merge($links, $mylinks);
    }

    if (!function_exists('MNS_Router_load')) {
        function MNS_Router_load()
        {
            // load the base class
            require_once plugin_dir_path(__FILE__) . 'includes/MNS_Router_Utility.class.php';

            if (MNS_Router_Utility::prerequisites_met(phpversion(), get_bloginfo('version'))) {
                // we can continue. Load all supporting files and hook into wordpress
                require_once plugin_dir_path(__FILE__) . 'includes/MNS_Router.class.php';
                require_once plugin_dir_path(__FILE__) . 'includes/MNS_Route.class.php';
                require_once plugin_dir_path(__FILE__) . 'includes/MNS_Router_Page.class.php';
                add_action('init', array('MNS_Router_Utility', 'init'), -100, 0);
                add_action(MNS_Router_Utility::PLUGIN_INIT_HOOK, array('MNS_Router_Page', 'init'), 0, 0);
                add_action(MNS_Router_Utility::PLUGIN_INIT_HOOK, array('MNS_Router', 'init'), 1, 0);

                require_once plugin_dir_path(__FILE__) . 'router/processpayment.php';
                require_once plugin_dir_path(__FILE__) . 'router/webhook.php';
                require_once plugin_dir_path(__FILE__) . 'router/mspaylink.php';
                require_once plugin_dir_path(__FILE__) . 'router/payform.php';
                require_once plugin_dir_path(__FILE__) . 'router/cancel.php';
                add_action(MNS_Router_Utility::PLUGIN_INIT_HOOK, array('MNS_Processpayment', 'init'), 1, 0);
                add_action(MNS_Router_Utility::PLUGIN_INIT_HOOK, array('MNS_Webhook', 'init'), 1, 0);
                add_action(MNS_Router_Utility::PLUGIN_INIT_HOOK, array('MNS_Paylink', 'init'), 1, 0);
                add_action(MNS_Router_Utility::PLUGIN_INIT_HOOK, array('MNS_Payform', 'init'), 1, 0);
                add_action(MNS_Router_Utility::PLUGIN_INIT_HOOK, array('MNS_Cancel', 'init'), 1, 0);

                require_once plugin_dir_path(__FILE__) . 'includes/helper.php';
            }
        }
        // Fire it up!
        MNS_Router_load();
    }

    if( ! class_exists( 'Moneyspace_Updater' ) ){
        include_once( plugin_dir_path( __FILE__ ) . 'includes/updater.php' );
    }
    
    $updater = new Moneyspace_Updater( __FILE__ );
    $updater->set_username( 'MoneySpace-net' );
    $updater->set_repository( 'money-space-for-woocommerce' );
    $updater->initialize();
    
}
