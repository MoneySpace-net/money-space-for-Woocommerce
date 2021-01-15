<?php

define('MS_DESCRIPTION', 'Integrates WooCommerce with MoneySpace Payment Gateway module. ');
define('MS_DESCRIPTION_QR', '
QR Payment, where you can easily pay via your preferred mobile banking application.

Mobile banking with QR payment functionality (as of March 2018):
- Siam Commercial Bank (SCB)
- Kasikorn Bank (KBANK)
- Krungthai Bank (KTB)
- Bangkok Bank (BBL)
- Krungsri Bank (BAY)
- TMB Bank (TMB)
- Thanachart Bank (TBANK)
- Government Savings Bank (GSB)


Terms and Conditions of QR Payment

- Once QR Code was generated, there is 15-45 minute time limit to complete the payment.
- Change or buy more transaction cannot be paid via QR Payment
- Registration to mobile banking service is required before using QR Payment
- For payment in Thailand with THB currency only');
define('MS_TOTAL_PAYMENT_ORDER', 'Total');
define('MS_VAT_PAYMENT_ORDER', 'Vat');
define('MS_THANK_PAYMENT_ORDER_1', 'Thanks for choosing Money Space !');
define('MS_THANK_PAYMENT_ORDER_2', 'Moneysp* will show up on Bank statement as payment receiver.');
define('MS_TRANSACTION_PAYMENT_ORDER', 'Transaction');
define('MS_NOTICE_CURRENCY', 'Pay only in THB currency');
define('MS_NOTICE_CHECK_TRANSACTION', 'An error occurred Please press the payment again.');
define('MS_NOTICE_ERROR_SETUP', 'Cannot pay Please setup the system again.');
define('MS_NOTICE_ERROR_PAYMENT', 'An error occurred, unable to pay.');
define('MS_NOTICE_ERROR_CONTINUE', 'Your settings cannot continue Please setup the system again.');
define('MS_ORDER_PENDING', 'Pending');
define('MS_FORM_FIELD_HEADER_SETTING', 'Setting');
define('MS_FORM_FIELD_ENABLE', 'Enable / Disable');
define('MS_FORM_FIELD_ENABLE_LABEL', 'Enable');
define('MS_FORM_FIELD_DESCRIPTION', 'Description');
define('MS_FORM_FIELD_HEADER_SETTING_MS', 'Moneyspace');
define('MS_FORM_FIELD_MS_VAT', 'Vat');
define('MS_FORM_FIELD_SET_ORDER_STATUS', 'Set order status when payment is completed');
define('MS_FORM_FIELD_HEADER_SETTING_UI', 'Enter the buyer information in the transaction id');
define('MS_FORM_FIELD_MS_FIRSTNAME', 'First name');
define('MS_FORM_FIELD_MS_LASTNAME', 'Last name');
define('MS_FORM_FIELD_MS_EMAIL', 'Email for receipt');
define('MS_FORM_FIELD_MS_PHONE', 'Phone Number');
define('MS_FORM_FIELD_MS_ADDRESS', 'Address');
define('MS_FORM_FIELD_MS_CUSTOMER', 'Customer');
define('MS_FORM_FIELD_MS_LABEL', 'Select');

define('YOUR_DOMAIN', 'Domain');
define('YOUR_WEBHOOK', 'Webhook');
define('HEAD_DOMAIN_WEBHOOK', 'Domain and Webhook');
define('DOMAIN_WEBHOOK', "Please Copy this below Domain and Webhook , to Paste in Web hook 's Menu Of Money Space after logged in. Then you will get Secret Id and Secret Key.");

define('PAYMENT_TYPE', 'Payment type');
define('TYPE_PAYMENT_CARD', 'Pay by Card 3D secured');
define('TYPE_PAYMENT_QR', 'QR Code PromptPay');

define('SETTING_LINK', 'Settings');
define('FEE_HEADER', 'Responsible fee person');
define('FEE_INCLUDE', 'Seller pay fees');
define('FEE_EXCLUDE', 'Buyer pay fees');

define('MESSAGE2STORE_HEADER', 'Add special instructions to merchant');
define('MESSAGE2STORE', 'Instructions to Merchant ( Special request )');

define('STOCKSETTING_HEAD', 'Stock reduce');
define('STOCKSETTING_ENABLE', 'Enable');
define('STOCKSETTING_DISABLE', 'Disable');

define('QR_SCAN', 'Please scan below QR Code through your Mobile Banking. After payment completed ,please wait a sec. Or Click completed here.');
define('QR_SCANSUCCESS', 'Pay success please wait a sec.');
define('QR_CONFIRM', ' Click here after payment completed');
define('QR_NEWCREATE', 'Click here for new QR Code');
