<?php
/**
 * @wordpress-plugin
 * Plugin Name:       Money Space
 * Plugin URI:        https://github.com/MoneySpace-net/money-space-for-Woocommerce/releases
 * Description:       Would you like to provide a streamlined and secure checkout experience for your customer? Every business does. Payment process is vital to the success eCommerce businesses. While WooCommerce merchants maximize their online products, we can help you take care of  payments and deliver a better overall customer experience for your online customers. By integrating your Magento website to your MoneySpace Payment Gateway account, your customer can pay for their products securely through credit card online. MoneySpace also supports up to 27 foreign currencies and Alipay/ WechatPay payments so you can begin receiving payment and expand your customer base worldwide. MoneySpace is PCI DSS certified and had been approved by Bank of Thailand as a payment method. To get to know more about MoneySpace payment gateway, visit our website at https://www.moneyspace.net
 * Version:           2.10.2
 * Author:            Money Space
 * Author URI:        https://moneyspace.net
 * 
 * @package MoneySpace
 */

namespace MoneySpace;

use MoneySpace\MNS_Router_Utility;
use MoneySpace\MNS_Router_Page;
use MoneySpace\MNS_Router;
use MoneySpace\MNS_Processpayment;
use MoneySpace\MNS_Webhook;
use MoneySpace\MNS_Paylink;
use MoneySpace\MNS_Payform;
use MoneySpace\MNS_Cancel;
use MoneySpace\MNS_Info;
use MoneySpace\MNS_CheckPayment;
use MoneySpace\Moneyspace_Updater;
use MoneySpace\Payments\MoneySpace_CreditCard;
use MoneySpace\Payments\MoneySpace_QRCode;
use MoneySpace\Payments\MoneySpace_CreditCard_Installment;
use MoneySpace\Payments\MNS_Payment_Gateway_Test_Block;
use WP_Scripts;

use Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry;
use Automattic\WooCommerce\Blocks\Package;
use Automattic\WooCommerce\Blocks\Assets\Api as AssetApi;

MoneySpacePayment::Import('MNS_Bootstrapper.php');

class MoneySpacePayment {

    public $active_plugins;
    public $updater;

    public function __construct() {

    }

    public static function Factory() {
        return new MoneySpacePayment();
    }

    public function Initialize() {
        
        $this->active_plugins = apply_filters('active_plugins', get_option('active_plugins'));
        if (in_array('woocommerce/woocommerce.php', $this->active_plugins)) {
            add_action('plugins_loaded', array($this, 'load_MS_Payment_Gateway'));
            add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'add_action_links'));
        }
    }

    public function load_MS_Payment_Gateway() {
        add_filter('woocommerce_payment_gateways', array($this, 'add_MS_Payemnt_Gateway'));
        MoneySpacePayment::Import('payment-gateway/class-woocommerce-moneyspace-payment-gateway.php');
        MoneySpacePayment::Import('payment-gateway/class-woocommerce-moneyspace-payment-gateway_QrProm.php');
        MoneySpacePayment::Import('payment-gateway/class-woocommerce-moneyspace-payment-gateway_installment.php');
        
        // load the base class
        MoneySpacePayment::Import('includes/MNS_Router_Utility.class.php');
        MoneySpacePayment::Import('includes/updater.php');

        add_action('MoneySpaceInit', array($this, 'MNS_Router_load'));
        add_action('MoneySpaceInit', array($this, 'MoneySpaceUpdater'));
        do_action('MoneySpaceInit');

        
        add_action( 'woocommerce_blocks_loaded', array( __CLASS__, 'woocommerce_gateway_moneyspace_woocommerce_block_support' ) );
    }

    public function add_MS_Payemnt_Gateway($gateways) {
        $gateways[] = 'MoneySpace\Payments\MNS_Payment_Gateway';
        $gateways[] = 'MoneySpace\Payments\MNS_Payment_Gateway_QR';
        $gateways[] = 'MoneySpace\Payments\MNS_Payment_Gateway_INSTALLMENT';
        $gateways[] = 'MoneySpace\Payments\MNS_Payment_Gateway_Test';
        return $gateways;
    }

    public function add_action_links($links)
    {
        $mylinks = array(
            '<a href="' . admin_url('admin.php?page=wc-settings&tab=checkout&section=moneyspace') . '">' . MNS_SETTING_LINK . '</a>',
        );
        return array_merge($links, $mylinks);
    }

    public function MNS_Router_load()
    {
        // load the base class
        MoneySpacePayment::Import('includes/MNS_Router_Utility.class.php');

        if (MNS_Router_Utility::prerequisites_met(phpversion(), get_bloginfo('version'))) {
            // we can continue. Load all supporting files and hook into wordpress
            MoneySpacePayment::Import('includes/MNS_Router.class.php');
            MoneySpacePayment::Import('includes/MNS_Route.class.php');
            MoneySpacePayment::Import('includes/MNS_Router_Page.class.php');
            MoneySpacePayment::Import('router/processpayment.php');
            MoneySpacePayment::Import('router/webhook.php');
            MoneySpacePayment::Import('router/mspaylink.php');
            MoneySpacePayment::Import('router/payform.php');
            MoneySpacePayment::Import('router/cancel.php');
            MoneySpacePayment::Import('router/info.php');
            MoneySpacePayment::Import('router/check-payment.php');
            MoneySpacePayment::Import('includes/helper.php');
            MoneySpacePayment::Import('includes/ms_log.php');

            add_action('init', array(MNS_Router_Utility::class, 'init'), -100, 0);
            add_action(MNS_Router_Utility::PLUGIN_INIT_HOOK, array(MNS_Router_Page::class, 'init'), 0, 0);
            add_action(MNS_Router_Utility::PLUGIN_INIT_HOOK, array(MNS_Router::class, 'init'), 1, 0);
            add_action(MNS_Router_Utility::PLUGIN_INIT_HOOK, array(MNS_Processpayment::class, 'init'), 1, 0);
            add_action(MNS_Router_Utility::PLUGIN_INIT_HOOK, array(MNS_Webhook::class, 'init'), 1, 0);
            add_action(MNS_Router_Utility::PLUGIN_INIT_HOOK, array(MNS_Paylink::class, 'init'), 1, 0);
            add_action(MNS_Router_Utility::PLUGIN_INIT_HOOK, array(MNS_Payform::class, 'init'), 1, 0);
            add_action(MNS_Router_Utility::PLUGIN_INIT_HOOK, array(MNS_Cancel::class, 'init'), 1, 0);
            add_action(MNS_Router_Utility::PLUGIN_INIT_HOOK, array(MNS_Info::class, 'init'), 1, 0);
            add_action(MNS_Router_Utility::PLUGIN_INIT_HOOK, array(MNS_CheckPayment::class, 'init'), 1, 0);

            add_action('admin_enqueue_scripts', array($this, 'load_custom_wp_admin_style'));
            add_filter('wc_order_statuses', 'wc_renaming_order_status');
        }
    }

    public function MoneySpaceUpdater() {
        $updater = new Moneyspace_Updater( __FILE__ );
        $updater->set_username( 'MoneySpace-net' );
        $updater->set_repository( 'money-space-for-woocommerce' );
        $updater->initialize();
    }

    public function load_custom_wp_admin_style(){
        wp_register_style( "moneyspace-style", MNS_PAYMENT_FORM_CSS, array(), "1.0.0", "");
        wp_enqueue_style( "moneyspace-style", MNS_PAYMENT_FORM_CSS, array(), "1.0.0", "");
        wp_register_style( 'custom_wp_admin_css', plugin_dir_url( __FILE__ )."includes/css/admin-style.css", false, '1.0.0' );
        wp_enqueue_style( 'custom_wp_admin_css' );
    }

    /**
	 * Plugin url.
	 *
	 * @return string
	 */
	public static function plugin_url() {
		return untrailingslashit( plugins_url( '/', __FILE__ ) );
	}

	/**
	 * Plugin url.
	 *
	 * @return string
	 */
	public static function plugin_abspath() {
		return trailingslashit( plugin_dir_path( __FILE__ ) );
	}

    public static function Import($file) {
        return require_once MoneySpacePayment::plugin_abspath().$file;
    }

    /**
	 * Registers WooCommerce Blocks integration.
	 *
	 */
	public static function woocommerce_gateway_moneyspace_woocommerce_block_support() {
		if ( class_exists( 'Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType' ) ) {
            
            wp_register_style( 'moneyspace-block-style', plugin_dir_url( __FILE__ )."assets/js/frontend/blocks-ms-creditcard.css", array(), "1.0.0", "");
            wp_enqueue_style('moneyspace-block-style');

			MoneySpacePayment::Import('includes/blocks/moneyspace-creditcard.php');
            MoneySpacePayment::Import('includes/blocks/moneyspace-qrcode.php');
            MoneySpacePayment::Import('includes/blocks/moneyspace-creditcard-installment.php');
            
			add_action(
				'woocommerce_blocks_payment_method_type_registration',
				function(PaymentMethodRegistry $payment_method_registry ) {
					$payment_method_registry->register( new MoneySpace_CreditCard() );
                    $payment_method_registry->register( new MoneySpace_QRCode() );
                    $payment_method_registry->register( new MoneySpace_CreditCard_Installment() );
				}
			);
		}
	}
}

MoneySpacePayment::Factory()->Initialize();