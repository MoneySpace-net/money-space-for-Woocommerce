<?php

if ( ! defined( 'MNS_PLUGIN_FILE' ) ) {
	define( 'MNS_PLUGIN_FILE', __FILE__ );
}

define('MNS_ID', 'moneyspace');
define('MNS_ID_QRPROM', 'moneyspace_qrprom');
define('MNS_ID_INSTALLMENT', 'moneyspace_installment');
define('MNS_TITLE', 'Money Space');
define('MNS_API_ENDPOINT', 'https://a.moneyspace.net');
define('MNS_STATIC_URL_ENDPOINT', 'https://a.moneyspace.net/static'); //'http://127.0.0.1:8000/static');
define('MNS_API_URL_CREATE_LINK_PAYMENT', MNS_API_ENDPOINT . '/payment/CreateTransaction');
define('MNS_API_URL_CHECK_PAYMENT', MNS_API_ENDPOINT . '/CheckPayment');
define('MNS_API_URL_CREATE', MNS_API_ENDPOINT . '/CreateTransactionID');
define('MNS_API_URL_CHECK', MNS_API_ENDPOINT . '/CheckOrderID');
define('MNS_API_URL_GETPAY', MNS_API_ENDPOINT . '/Getpay');
define('MNS_API_URL_PAY', MNS_API_ENDPOINT . '/Pay');
define('MNS_API_URL_V2_CREATE_PAYMENT', 'https://www.moneyspace.net/merchantapi/v2/createpayment/obj');
define('MNS_API_URL_CREATE_INSTALLMENT', 'https://a.moneyspace.net/payment/Createinstallment/');
define('MNS_LOGO', MNS_STATIC_URL_ENDPOINT . '/img/type/Master_VISA_JCB_UNION_180.png');
define('MNS_LOGO_QR', MNS_STATIC_URL_ENDPOINT. '/img/type/QRCode_160.png');
define('MNS_LOGO_INSTALLMENT', MNS_STATIC_URL_ENDPOINT . '/img/type/Installment_220.png');
define('MNS_PAYMENT_JS', plugins_url( "includes/moneyspace_payment.js", __FILE__ )); // MNS_STATIC_URL_ENDPOINT . '/moneyspace_payment.js'); //
define('MNS_PAYMENT_FORM_JS', plugins_url( "includes/moneyspace_payment_form.js", __FILE__ ) );
define('MNS_PAYMENT_FORM_CSS', plugins_url( "includes/css/moneyspace.css", __FILE__ ) );
define('MNS_METHOD_TITLE', 'Money Space for WooCommerce');
// define('MNS_TIME_ZONE', "Asia/Bangkok");
define('MNS_CANCEL_TRANSACTION', 'https://a.moneyspace.net/merchantapi/cancelpayment');
define('MNS_CHECK_PAYMENT', 'https://a.moneyspace.net/CheckPayment');
define('MNS_CHECK_PAYMENT_STATUS', '/ms/check-payment/');

// Load debug security configuration (only if file exists in development)
$debug_config_file = plugin_dir_path(__FILE__) . 'includes/debug-config.php';
if (file_exists($debug_config_file)) {
    require_once $debug_config_file;
}
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