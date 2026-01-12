<?php

namespace MoneySpace\Payments;

use MoneySpace\MoneySpacePayment;
use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;
use WC_Payment_Gateways;

class MoneySpace_CreditCard extends AbstractPaymentMethodType {

	/**
	 * Payment method name/id/slug.
	 *
	 * @var string
	 */
	protected $name = "moneyspace";
	private $logger;

	/**
	 * The gateway instance.
	 *
	 * @var MNS_Payment_Gateway
	 */
	private $gateway;
	
	public function __construct( ) {
		$this->logger = wc_get_logger();
	}

	/**
	 * Initializes the payment method type.
	 */
	public function initialize() {
		try {
			$this->settings = get_option( 'woocommerce_moneyspace_settings', [] ); // '.MNS_ID.'_settings
			
			// Check if WooCommerce payment gateways are available
			if (WC() && WC()->payment_gateways) {
				$gateways = WC()->payment_gateways->payment_gateways();
				
				// Check if our gateway exists in the gateways array
				if (isset($gateways[$this->name]) && is_object($gateways[$this->name])) {
					$this->gateway = $gateways[$this->name];
				} else {
					// Log error but don't throw exception to prevent critical error
					$this->logger->error('MoneySpace: Gateway not found during initialization: ' . $this->name, [ 'source' => 'moneyspace' ] );
					$this->gateway = null;
				}
			} else {
				$this->logger->error('MoneySpace: WooCommerce payment gateways not available during initialization', [ 'source' => 'moneyspace' ] );
				$this->gateway = null;
			}
		} catch (\Exception $e) {
			$this->logger->error('MoneySpace: Error during initialization: ' . $e->getMessage(), [ 'source' => 'moneyspace' ] );
			$this->gateway = null;
		}
	}

	/**
	 * Returns if this payment method should be active. If false, the scripts will not be enqueued.
	 *
	 * @return boolean
	 */
	public function is_active() {
		// If gateway failed to initialize, don't activate
		if (!$this->gateway) {
			return false;
		}
		
		return filter_var( $this->get_setting( 'enabled', false ), FILTER_VALIDATE_BOOLEAN );
		// return true; // $this->gateway->is_available();
	}

	/**
	 * Returns an array of scripts/handles to be registered for this payment method.
	 *
	 * @return array
	 */
	public function get_payment_method_script_handles() {
		$script_path       = '/assets/js/frontend/blocks-ms-creditcard.js';
		$script_asset_path = MoneySpacePayment::plugin_abspath() . 'assets/js/frontend/blocks-ms-creditcard.asset.php';
		
		$script_asset      = file_exists( $script_asset_path )
			? require( $script_asset_path )
			: array(
				'dependencies' => array(),
				'version'      => '1.2.0'
			);
		$script_url        = MoneySpacePayment::plugin_url() . $script_path;
		wp_register_script(
			'wc-moneyspace-payments-blocks',
			$script_url,
			$script_asset[ 'dependencies' ],
			$script_asset[ 'version' ],
			true
		);

		return [ 'wc-moneyspace-payments-blocks' ];
	}

	/**
	 * Returns an array of key=>value pairs of data made available to the payment methods script.
	 *
	 * @return array
	 */
	public function get_payment_method_data() {
		$payment_gateway_id = defined('MONEYSPACE_ID') ? MONEYSPACE_ID : 'moneyspace';
		$gateways = WC()->payment_gateways->get_available_payment_gateways();
		
		// Safety check for gateway existence
		if (!isset($gateways[$payment_gateway_id]) || !is_object($gateways[$payment_gateway_id])) {
			$this->logger->error( 'MoneySpace: Gateway not found for ID: ' . $payment_gateway_id, [ 'source' => 'moneyspace' ] );
			$ms_template_payment = 1; // Default value
		} else {
			$ms_template_payment = isset($gateways[$payment_gateway_id]->settings['ms_template_payment']) 
				? $gateways[$payment_gateway_id]->settings['ms_template_payment'] 
				: 1;
		}

		$fee_opt = isset($gateways[$payment_gateway_id]) && isset($gateways[$payment_gateway_id]->settings['fee_setting']) 
			? $gateways[$payment_gateway_id]->settings['fee_setting'] : "store";
        
		$ms_fees = ($fee_opt === 'customer') ? ('ex'.'clude') : ('inc'.'lude');
		
		// Define fallback translations in case constants are not loaded
		$fallback_translations = array(
			'MNS_CC_NO' => 'Card Number',
			'MNS_CC_NAME' => 'Cardholder Name',
			'MNS_CC_EXP_MONTH' => 'Expiry Month',
			'MNS_CC_EXP_YEAR' => 'Expiry Year',
			'MNS_CC_CVV' => 'CVV',
			'MNS_MONTH' => 'Month',
			'MNS_YEAR' => 'Year',
			'MNS_CC_WARN_CC_NO_1' => 'Card number is required',
			'MNS_CC_WARN_CC_NO_2' => 'Card number must be 16 digits',
			'MNS_CC_WARN_CC_NAME' => 'Cardholder name is required',
			'MNS_CC_WARN_CC_EXP_MONTH' => 'Expiry month is required',
			'MNS_CC_WARN_CC_EXP_YEAR' => 'Expiry year is required',
			'MNS_CC_WARN_CVV_1' => 'CVV is required',
			'MNS_CC_WARN_CVV_2' => 'CVV must be 3 digits',
		);
		
		// Use constants if defined, otherwise use fallback
		$cc_i18n = array();
		foreach ($fallback_translations as $constant_name => $fallback_value) {
			$cc_i18n[$constant_name] = defined($constant_name) ? constant($constant_name) : $fallback_value;
		}
		
		return [
			'title'       => $this->get_setting( 'title' ),
			'description' => $this->get_setting( 'description' ),
			'icons'		  => [$this->get_payment_method_icons()],
			'ms_template_payment' => $ms_template_payment,
			'ms_fees' => $ms_fees,
			'i18n' => $cc_i18n,
			'supports'    => array_filter( $this->gateway->supports, [ $this->gateway, 'supports' ] )
		];
	}

	public function get_payment_method_icons() {
		// Safety check for gateway existence
		if (!$this->gateway || !is_object($this->gateway)) {
			$icon_url = defined('MONEYSPACE_LOGO') ? MONEYSPACE_LOGO : plugins_url('includes/images/moneyspace-logo.png', dirname(__DIR__));
		} else {
			$icon_url = $this->gateway->icon;
		}
		
		return [
			'id'  => 'moneyspace',
			'src' => $icon_url,
			'alt' => 'moneyspace'
		];
	}
}
