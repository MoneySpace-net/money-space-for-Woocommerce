<?php

define('MONEYSPACE_DESCRIPTION', 'Integrates WooCommerce with MoneySpace Payment Gateway module. ');
define('MONEYSPACE_DESCRIPTION_QR', '
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
define('MONEYSPACE_TOTAL_PAYMENT_ORDER', 'Total');
define('MONEYSPACE_VAT_PAYMENT_ORDER', 'Vat');
define('MONEYSPACE_THANK_PAYMENT_ORDER_1', 'Thanks for choosing Money Space !');
define('MONEYSPACE_THANK_PAYMENT_ORDER_2', 'Moneysp* will show up on Bank statement as payment receiver.');
define('MONEYSPACE_TRANSACTION_PAYMENT_ORDER', 'Transaction');
define('MONEYSPACE_NOTICE_CURRENCY', 'Pay only in THB currency');
define('MONEYSPACE_NOTICE_CHECK_TRANSACTION', 'An error occurred Please press the payment again.');
define('MONEYSPACE_NOTICE_ERROR_SETUP', 'Cannot pay Please setup the system again.');
define('MONEYSPACE_NOTICE_ERROR_PAYMENT', 'An error occurred, unable to pay.');
define('MONEYSPACE_NOTICE_ERROR_CONTINUE', 'Your settings cannot continue Please setup the system again.');
define('MONEYSPACE_ORDER_PENDING', 'Pending');
define('MONEYSPACE_FORM_FIELD_HEADER_SETTING', 'Setting');
define('MONEYSPACE_FORM_FIELD_ENABLE', 'Enable / Disable');
define('MONEYSPACE_FORM_FIELD_ENABLE_LABEL', 'Enable');
define('MONEYSPACE_FORM_FIELD_DESCRIPTION', 'Description');
define('MONEYSPACE_FORM_FIELD_HEADER_SETTING_MS', 'Moneyspace');
define('MONEYSPACE_FORM_FIELD_MS_VAT', 'Vat');
define('MONEYSPACE_FORM_FIELD_SET_ORDER_STATUS', 'Set order status when payment is completed');
define('MONEYSPACE_FORM_FIELD_TEMPLATE', 'Choose Template');
define('MONEYSPACE_FORM_FIELD_SET_QRNONE_TIMEOUT', 'Set QR Code Timeout');
define('MONEYSPACE_FORM_FIELD_ENABLE_AUTO_CHECK_QR', 'Enable auto check QR Code Result');
define('MONEYSPACE_FORM_FIELD_HEADER_SETTING_UI', 'Enter the buyer information in the transaction id');
define('MONEYSPACE_FORM_FIELD_MS_FIRSTNAME', 'First name');
define('MONEYSPACE_FORM_FIELD_MS_LASTNAME', 'Last name');
define('MONEYSPACE_FORM_FIELD_MS_EMAIL', 'Email for receipt');
define('MONEYSPACE_FORM_FIELD_MS_PHONE', 'Phone Number');
define('MONEYSPACE_FORM_FIELD_MS_ADDRESS', 'Address');
define('MONEYSPACE_FORM_FIELD_MS_CUSTOMER', 'Customer');
define('MONEYSPACE_FORM_FIELD_MS_LABEL', 'Select');
define('MONEYSPACE_FORM_FIELD_KTC_ENABLE', 'KTC Enable / Disable');
define('MONEYSPACE_FORM_FIELD_BAY_ENABLE', 'BAY Enable / Disable');
define('MONEYSPACE_FORM_FIELD_FCY_ENABLE', 'FCY Enable / Disable');
define('MONEYSPACE_FORM_FIELD_HEADER_SETTING_CHECK_CONNECTION', 'Test connect moneyspace gateway');

define('MONEYSPACE_YOUR_DOMAIN', 'Domain');
define('MONEYSPACE_YOUR_WEBHOOK', 'Webhook');
define('MONEYSPACE_HEAD_DOMAIN_WEBHOOK', 'Domain and Webhook');
define('MONEYSPACE_DOMAIN_WEBHOOK', "Please Copy this below Domain and Webhook , to Paste in Web hook 's Menu Of Money Space after logged in. Then you will get Secret Id and Secret Key.");

define('MONEYSPACE_PAYMENT_TYPE', 'Payment type');
define('MONEYSPACE_TYPE_PAYMENT_CARD', 'Pay by Card 3D secured');
define('MONEYSPACE_TYPE_PAYMENT_QR', 'QR Code PromptPay');
define('MONEYSPACE_TYPE_PAYMENT_INSTALLMENT', 'Monthly Installments');

define('MONEYSPACE_SETTING_LINK', 'Settings');
define('MONEYSPACE_FEE_HEADER', 'Responsible fee person');
define('MONEYSPACE_FEE_INCLUDE', 'Seller pay fees');
define('MONEYSPACE_FEE_EXCLUDE', 'Buyer pay fees');

define('MONEYSPACE_MESSAGE2STORE_HEADER', 'Add special instructions to merchant');
define('MONEYSPACE_MESSAGE2STORE', '(Optional) Leave a message to seller');
define('MONEYSPACE_MESSAGE', 'Message');

define('MONEYSPACE_STOCKSETTING_HEAD', 'Stock reduce');
define('MONEYSPACE_STOCKSETTING_ENABLE', 'Enable');
define('MONEYSPACE_STOCKSETTING_DISABLE', 'Disable');

define('MONEYSPACE_QR_SCAN', 'Please scan below QR Code through your Mobile Banking. After payment completed ,please wait a sec. Or Click completed here.');
define('MONEYSPACE_QR_SCANSUCCESS', 'Pay success please wait a sec.');
define('MONEYSPACE_QR_CONFIRM', ' Click here after payment completed');
define('MONEYSPACE_QR_NEWCREATE', 'Click here for new QR Code');
define('MONEYSPACE_NOTICE_ERROR_LOAD_QR', 'Cannot download QR CODE');

define('MONEYSPACE_PAY_INS', 'MoneySpace Monthly installments');
define('MONEYSPACE_PAY_QR', 'Pay bills with QR Code');

define('MONEYSPACE_INS_MESSAGE_WARNING', 'The amount of balance must be 3,000.01 baht or more in order to make the installment payment.');

define('MONEYSPACE_ADMIN_SETTING_CC_DESC', 'Description Credit Card Pay');
define('MONEYSPACE_ADMIN_SETTING_STATUS_AFTER_PAY', "Status After Pay <br> 'Pending payment', <br>'Processing', <br>'On hold', <br>'Completed', <br>'Cancelled', <br>'Refunded', <br>'Failed'");

define('MONEYSPACE_CC_NAME', 'Card Holder');
define('MONEYSPACE_CC_NO', 'Card Number');
define('MONEYSPACE_CC_EXP', 'Exp Date');
define('MONEYSPACE_CC_EXP_MONTH', 'Exp Month');
define('MONEYSPACE_CC_EXP_YEAR', 'Exp Year');
define('MONEYSPACE_CC_CVV', 'CVV');
define('MONEYSPACE_CC_REMEMBER', 'Save my card details securely for future payment');

define('MONEYSPACE_MONTH', 'Month');
define('MONEYSPACE_YEAR', 'Year');
define('MONEYSPACE_BAHT', 'Baht');
define('MONEYSPACE_INS', 'Installments');


define('MONEYSPACE_CC_WARN_CC_NO_1', 'Please fill in Card Number');
define('MONEYSPACE_CC_WARN_CC_NO_2', 'Please check your Card Number');
define('MONEYSPACE_CC_WARN_CC_NAME', 'Please fill in Card Holder');
define('MONEYSPACE_CC_WARN_CC_EXP_MONTH', 'Please fill in Exp Month');
define('MONEYSPACE_CC_WARN_CC_EXP_YEAR', 'Please fill in Card Exp Year');
define('MONEYSPACE_CC_WARN_CVV_1', 'Please fill in CVV');
define('MONEYSPACE_CC_WARN_CVV_2', 'Please check CVV');

define('MONEYSPACE_CC_INS_TITLE', 'Credit Card Bank for installment');
define('MONEYSPACE_CC_INS_MONTH', 'Select month for installment');

define('MONEYSPACE_CC_INS_KTC', 'KTC Card');
define('MONEYSPACE_CC_INS_BAY', 'Krungsri Visa Card , Central Credit Card , Lotus Credit Card');
define('MONEYSPACE_CC_INS_FCY', 'First Choice Card , HomePro Credit Card , MegaHome Credit Card');

define('MONEYSPACE_ORDER_STATUS_COMPLETED', 'Completed');

define('MONEYSPACE_FORM_FIELD_AUTO_CHECK_QR_RESULT_TIME', 'Set auto check QR Code Result time');
define('MONEYSPACE_FORM_FIELD_SECONDS', 'Seconds');