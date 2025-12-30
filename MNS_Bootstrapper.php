<?php

if ( ! defined( 'MONEYSPACE_PLUGIN_FILE' ) ) {
    define( 'MONEYSPACE_PLUGIN_FILE', __FILE__ );
}

define('MONEYSPACE_ID', 'moneyspace');
define('MONEYSPACE_ID_QRPROM', 'moneyspace_qrprom');
define('MONEYSPACE_ID_INSTALLMENT', 'moneyspace_installment');
define('MONEYSPACE_TITLE', 'Money Space');
define('MONEYSPACE_API_ENDPOINT', 'https://a.moneyspace.net');
define('MONEYSPACE_STATIC_URL_ENDPOINT', 'https://a.moneyspace.net/static'); //'http://127.0.0.1:8000/static');
define('MONEYSPACE_API_URL_CREATE_LINK_PAYMENT', MONEYSPACE_API_ENDPOINT . '/payment/CreateTransaction');
define('MONEYSPACE_API_URL_CHECK_PAYMENT', MONEYSPACE_API_ENDPOINT . '/CheckPayment');
define('MONEYSPACE_API_URL_CREATE', MONEYSPACE_API_ENDPOINT . '/CreateTransactionID');
define('MONEYSPACE_API_URL_CHECK', MONEYSPACE_API_ENDPOINT . '/CheckOrderID');
define('MONEYSPACE_API_URL_GETPAY', MONEYSPACE_API_ENDPOINT . '/Getpay');
define('MONEYSPACE_API_URL_PAY', MONEYSPACE_API_ENDPOINT . '/Pay');
define('MONEYSPACE_API_URL_V2_CREATE_PAYMENT', 'https://www.moneyspace.net/merchantapi/v2/createpayment/obj');
define('MONEYSPACE_API_URL_CREATE_INSTALLMENT', 'https://a.moneyspace.net/payment/Createinstallment/');
define('MONEYSPACE_LOGO', MONEYSPACE_STATIC_URL_ENDPOINT . '/img/type/Master_VISA_JCB_UNION_180.png');
define('MONEYSPACE_LOGO_QR', MONEYSPACE_STATIC_URL_ENDPOINT. '/img/type/QRCode_160.png');
define('MONEYSPACE_LOGO_INSTALLMENT', MONEYSPACE_STATIC_URL_ENDPOINT . '/img/type/Installment_220.png');
define('MONEYSPACE_PAYMENT_FORM_CSS', plugins_url( "includes/css/moneyspace.css", __FILE__ ) );
define('MONEYSPACE_METHOD_TITLE', 'Money Space for WooCommerce');
// define('MONEYSPACE_TIME_ZONE', "Asia/Bangkok");
define('MONEYSPACE_CANCEL_TRANSACTION', 'https://a.moneyspace.net/merchantapi/cancelpayment');
define('MONEYSPACE_CHECK_PAYMENT', 'https://a.moneyspace.net/CheckPayment');
define('MONEYSPACE_CHECK_PAYMENT_STATUS', '/ms/check-payment/');

define("MONEYSPACE_ROOT", __DIR__."/");
define("MONEYSPACE_ROOT_URL", plugin_dir_url(__FILE__));
define("MONEYSPACE_PAYMENT_TYPE_CARD", "card");
define("MONEYSPACE_PAYMENT_TYPE_QR", "qrnone");
define("MONEYSPACE_PAYMENT_TYPE_INSTALLMENT", "installment");

if (get_locale() == 'th') {
    include_once "includes/th.php";
} else {
    include_once "includes/eng.php";
}