=== Plugin Name ===
Contributors: moneyspace
Tags: credit card, moneyspace, payment request, thaibank, woocommerce, automattic, bay, ktc, fcy, installment
Requires at least: 4.9
Tested up to: 6.5.2
Stable tag: 2.11.1
Requires PHP: 7.0
Requires WordPress: 4.9
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Payment Gateway รับชำระด้วยบัตรเครดิตออนไลน์ได้ สามารถรองรับสกุลเงินต่างประเทศได้ถึง 27 สกุลเงิน ขยายฐานลูกค้าของคุณไปทั่วโลก

== Description ==

Accept Visa, MasterCard, more directly on your store with the MoneySpace payment gateway for WooCommerce.

= Take Credit card payments easily and directly on your store =

The MoneySpace plugin extends WooCommerce allowing you to take payments directly on your store via MoneySpace’s API.

MoneySpace is available for Store Owners and Merchants in:

* Thailand

MoneySpace is a simple way to accept payments online. With MoneySpace you can accept Visa, MasterCard, JCB, Union Pay, Promtpay and Instalment payment plans, directly on your store.

= Why choose MoneySpace? =

MoneySpace has no setup fees, no monthly fees, no hidden costs: you only get charged when you earn money.

== Installation ==
You can download an [older version of this gateway for older versions of MoneySpace from here](https://bitbucket.org/MoneySpace/money-space-for-woocommerce/downloads/).

Please note, v1.0.0 of this gateway requires WooCommerce 4.9 and above.

= Automatic installation =

Automatic installation is the easiest option as WordPress handles the file transfers itself and you don’t need to leave your web browser. To do an automatic install of the WooCommerce MoneySpace plugin, log in to your WordPress dashboard, navigate to the Plugins menu and click Add New.

In the search field type “WooCommerce MoneySpace Payment Gateway” and click Search Plugins. Once you’ve found our plugin you can view details about it such as the point release, rating, and description. Most importantly, of course, you can install it by simply clicking "Install Now", then "Activate".

= Manual installation =

The manual installation method involves downloading our plugin and uploading it to your web server via your favorite FTP application. The WordPress codex contains [instructions on how to do this here](http://codex.wordpress.org/Managing_Plugins#Manual_Plugin_Installation).

= Updating =

Automatic updates should work like a charm; as always though, ensure you backup your site just in case.

== Frequently Asked Questions ==

= Does this require an SSL certificate? =

Yes! In Live Mode, an SSL certificate must be installed on your site to use MoneySpace. In addition to SSL encryption.

== Screenshots ==

1. The MoneySpace payment gateway settings screen used to configure the main MoneySpace gateway.
2. Offer a range of payment methods such as local and alternative payment methods.
3. Pay with a saved payment method, a new card, and allow customers to save the payment card for future transactions.

== Changelog ==
= 2.11.1 - 2024-04-29 =

* 2024-04-29 : tested up woocommerce 8.8.2 and wordpress 6.5.2
* 2024-04-21 : enhance cc template 2 to call payment link and fix bug installment option
* 2024-04-10 : fix bug filter cc template
* 2024-04-08 : fix bug router not found template, fix bug credit card not validate and remove force timezone when payment creation
* 2024-02-19 : release credit card installment for blocks-based feature and bug fixed credit card form not render exp month and year
* 2024-02-06 : enhance support woocommerce 8.x blocks-based feature
* 2024-01-11 : qr promptpay new option for auto check result
* 2023-10-12 : tested up with 6.3.1
* 2023-06-19 : add option to disable auto check qrnone
* 2023-04-23 : fix bug qrcode can't update payment status
* 2022-10-20 : fix bug qrcode not show on firefox
* 2022-07-10 : custom payment status after payment is completed
* 2022-07-08 : fix bug callback not invalid parameter
* 2022-06-30 : fix bug checking qrnone status from api and set default enable stock reduce and force wc-completed after pay.
* 2022-06-28 : fix bug checking qrnone status from api
* 2022-05-30 : fix bug qrnone not rendering
* 2022-05-04 : fix bug qrnone not redirect after payment success and add option stock reduce in admin config
* 2022-04-18 : add log data for investigated and update payment api
* 2022-02-20 : fix bug wordpress 5.9 not auto enqueue style
* 2022-01-13 : fix bug action hook not triggered
* 2021-12-19 : fix bug create card payment with buyer pay fee and add description of status after pay 
* 2021-11-21 : fix bug payment form not compatible themes
* 2021-10-17 : fix bug render html text
* 2021-10-09 : fix bug installment channel
* 2021-08-09 : fix bug remove some data on post meta
* 2021-08-04 : fix bug some Wordpress version can't working
* 2021-07-02 : fix bug credit card and installment with option buyer pay fees
* 2021-06-28 : verify credit card payment form
* 2021-06-23 : add shortcut credit card on place order page
* 2021-05-12 : Fixed QR auto kill transaction id on server and add Time countdown on qr pay page.
* 2021-03-11 : Fixed bugs the order status update system.
* 2021-03-09 : Added the buyer system, responsible for the fee in the installment payment system.
* 2021-02-23 : Fixed payment bugs and product details.
* 2021-02-10 : Fixed stock reduction bugs and updated QR code expiration time settings.
* 2021-01-18 : Added a setting to be able to select the expiration time of the QR Code.
* 2021-01-15 : Added buyer fee system and plugin update system.
* 2020-12-28 : Updated security system UI type 1.
* 2020-12-19 : Updated settings to set order status when payment is complete ( QR Code and installment )
* 2020-11-18 : Updated redirect to the order received page upon successful payment.
* 2020-11-15 : Updated redirect to the order detail page upon successful payment and bugs fix.
* 2020-09-07 : Updated setting (Enter the buyer information in the transaction id)
* 2020-07-06 : Updated payment methods to light box and fixed bugs.
* 2020-04-09 : Updated QR promptpay (Redirect when payment completed)
* 2020-03-04 : Updated QR promptpay and added installment payments
* 2019-12-24 : Fixed bugs
* 2019-11-30 : Added stock setting
* 2019-09-13 : Added special instructions to merchant
* 2019-09-02 : Updated settings
* 2019-08-20 : Updated QR promptpay
* 2019-04-22 : Fixed payment errors
* 2019-02-11 : Added your domain and webhook
* 2019-01-26 : Added

