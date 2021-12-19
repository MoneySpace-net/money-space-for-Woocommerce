<?php

define('MNS_DESCRIPTION', 'Integrates WooCommerce with MoneySpace Payment Gateway module. ');
define('MNS_DESCRIPTION_QR', '
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
define('MNS_TOTAL_PAYMENT_ORDER', 'Total');
define('MNS_VAT_PAYMENT_ORDER', 'Vat');
define('MNS_THANK_PAYMENT_ORDER_1', 'Thanks for choosing Money Space !');
define('MNS_THANK_PAYMENT_ORDER_2', 'Moneysp* will show up on Bank statement as payment receiver.');
define('MNS_TRANSACTION_PAYMENT_ORDER', 'Transaction');
define('MNS_NOTICE_CURRENCY', 'Pay only in THB currency');
define('MNS_NOTICE_CHECK_TRANSACTION', 'An error occurred Please press the payment again.');
define('MNS_NOTICE_ERROR_SETUP', 'Cannot pay Please setup the system again.');
define('MNS_NOTICE_ERROR_PAYMENT', 'An error occurred, unable to pay.');
define('MNS_NOTICE_ERROR_CONTINUE', 'Your settings cannot continue Please setup the system again.');
define('MNS_ORDER_PENDING', 'Pending');
define('MNS_FORM_FIELD_HEADER_SETTING', 'Setting');
define('MNS_FORM_FIELD_ENABLE', 'Enable / Disable');
define('MNS_FORM_FIELD_ENABLE_LABEL', 'Enable');
define('MNS_FORM_FIELD_DESCRIPTION', 'Description');
define('MNS_FORM_FIELD_HEADER_SETTING_MS', 'Moneyspace');
define('MNS_FORM_FIELD_MS_VAT', 'Vat');
define('MNS_FORM_FIELD_SET_ORDER_STATUS', 'Set order status when payment is completed');
define('MNS_FORM_FIELD_HEADER_SETTING_UI', 'Enter the buyer information in the transaction id');
define('MNS_FORM_FIELD_MS_FIRSTNAME', 'First name');
define('MNS_FORM_FIELD_MS_LASTNAME', 'Last name');
define('MNS_FORM_FIELD_MS_EMAIL', 'Email for receipt');
define('MNS_FORM_FIELD_MS_PHONE', 'Phone Number');
define('MNS_FORM_FIELD_MS_ADDRESS', 'Address');
define('MNS_FORM_FIELD_MS_CUSTOMER', 'Customer');
define('MNS_FORM_FIELD_MS_LABEL', 'Select');
define('MNS_FORM_FIELD_KTC_ENABLE', 'KTC Enable / Disable');
define('MNS_FORM_FIELD_BAY_ENABLE', 'BAY Enable / Disable');
define('MNS_FORM_FIELD_FCY_ENABLE', 'FCY Enable / Disable');
define('MNS_FORM_FIELD_HEADER_SETTING_CHECK_CONNECTION', 'Test connect moneyspace gateway');

define('MNS_YOUR_DOMAIN', 'Domain');
define('MNS_YOUR_WEBHOOK', 'Webhook');
define('MNS_HEAD_DOMAIN_WEBHOOK', 'Domain and Webhook');
define('MNS_DOMAIN_WEBHOOK', "Please Copy this below Domain and Webhook , to Paste in Web hook 's Menu Of Money Space after logged in. Then you will get Secret Id and Secret Key.");

define('MNS_PAYMENT_TYPE', 'Payment type');
define('MNS_TYPE_PAYMENT_CARD', 'Pay by Card 3D secured');
define('MNS_TYPE_PAYMENT_QR', 'QR Code PromptPay');
define('MNS_TYPE_PAYMENT_INSTALLMENT', 'Monthly Installments');

define('MNS_SETTING_LINK', 'Settings');
define('MNS_FEE_HEADER', 'Responsible fee person');
define('MNS_FEE_INCLUDE', 'Seller pay fees');
define('MNS_FEE_EXCLUDE', 'Buyer pay fees');

define('MNS_MESSAGE2STORE_HEADER', 'Add special instructions to merchant');
define('MNS_MESSAGE2STORE', '(Optional) Leave a message to seller');
define('MNS_MESSAGE', 'Message');

define('MNS_STOCKSETTING_HEAD', 'Stock reduce');
define('MNS_STOCKSETTING_ENABLE', 'Enable');
define('MNS_STOCKSETTING_DISABLE', 'Disable');

define('MNS_QR_SCAN', 'Please scan below QR Code through your Mobile Banking. After payment completed ,please wait a sec. Or Click completed here.');
define('MNS_QR_SCANSUCCESS', 'Pay success please wait a sec.');
define('MNS_QR_CONFIRM', ' Click here after payment completed');
define('MNS_QR_NEWCREATE', 'Click here for new QR Code');

define('MNS_PAY_INS', 'Monthly installments');
define('MNS_PAY_QR', 'Pay bills with QR Code');

define('MNS_INS_MESSAGE_WARNING', 'The amount of balance must be 3,000.01 baht or more in order to make the installment payment.');

define('MNS_ADMIN_SETTING_CC_DESC', 'Description Credit Card Pay');
define('MNS_ADMIN_SETTING_STATUS_AFTER_PAY', "Status After Pay <br> 'Pending payment', <br>'Processing', <br>'On hold', <br>'Completed', <br>'Cancelled', <br>'Refunded', <br>'Failed'");