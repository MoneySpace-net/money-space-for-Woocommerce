<?php

namespace MoneySpace\Payments;

use MoneySpace\MoneySpacePayment;
use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;
use Automattic\WooCommerce\Blocks\Assets\Api;

/**
 * Class MoneySpace_CreditCard_Installment
 *
 * @package MoneySpace\Payments
 */
class MoneySpace_CreditCard_Installment extends AbstractPaymentMethodType {

	public $name = 'moneyspace_installment'; // Use string instead of constant to prevent undefined constant error

	/**
	 * The gateway instance.
	 *
	 * @var MNS_Payment_Gateway_INSTALLMENT
	 */
	private $gateway;

	private $logger;
	
	public function __construct( ) {
		$this->logger = wc_get_logger();
		// Set name with fallback
		$this->name = defined('MONEYSPACE_ID_INSTALLMENT') ? MONEYSPACE_ID_INSTALLMENT : 'moneyspace_installment';
	}

	/**
	 * Initializes the payment method type.
	 */
	public function initialize() {
		try {
			$this->settings = get_option( 'woocommerce_'.$this->name.'_settings', [] );
			
			// Check if WooCommerce payment gateways are available
			if (WC() && WC()->payment_gateways) {
				$gateways = WC()->payment_gateways->payment_gateways();
				
				// Check if our gateway exists in the gateways array
				if (isset($gateways[$this->name]) && is_object($gateways[$this->name])) {
					$this->gateway = $gateways[$this->name];
				} else {
					// Log error but don't throw exception to prevent critical error
					$this->logger->error( 'MoneySpace: Installment gateway not found during initialization: ' . $this->name, [ 'source' => 'moneyspace' ] );
					$this->gateway = null;
				}
			} else {
				$this->logger->error('MoneySpace: WooCommerce payment gateways not available during installment initialization', [ 'source' => 'moneyspace' ] );
				$this->gateway = null;
			}
		} catch (\Exception $e) {
			$this->logger->error( 'MoneySpace: Error during installment initialization: ' . $e->getMessage(), [ 'source' => 'moneyspace' ] );
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
	}

	/**
	 * Returns an array of scripts/handles to be registered for this payment method.
	 *
	 * @return array
	 */
	public function get_payment_method_script_handles() {
		$script_path       = '/assets/js/frontend/blocks-ms-creditcard-installment.js';
		$script_asset_path = MoneySpacePayment::plugin_abspath() . 'assets/js/frontend/blocks-ms-creditcard-installment.asset.php';
		$script_asset      = file_exists( $script_asset_path )
			? require( $script_asset_path )
			: array(
				'dependencies' => array(),
				'version'      => '1.0.0'
			);
		$script_url        = MoneySpacePayment::plugin_url() . $script_path;
		wp_register_script(
			'wc-moneyspace-creditcard-installment',
			$script_url,
			$script_asset[ 'dependencies' ],
			$script_asset[ 'version' ],
			true
		);

		return [ 'wc-moneyspace-creditcard-installment' ];
	}

	/**
	 * Returns an array of key=>value pairs of data made available to the payment methods script.
	 *
	 * @return array
	 */
	public function get_payment_method_data() {
		$gateways = WC()->payment_gateways->get_available_payment_gateways();
		$id = $this->name;
		
		// Safety checks for gateway settings
		$ktc_enabled = isset($gateways[$id]) && isset($gateways[$id]->settings['ktc_enabled']) 
			? $gateways[$id]->settings['ktc_enabled'] : "yes";
		$bay_enabled = isset($gateways[$id]) && isset($gateways[$id]->settings['bay_enabled']) 
			? $gateways[$id]->settings['bay_enabled'] : "yes";
		$fcy_enabled = isset($gateways[$id]) && isset($gateways[$id]->settings['fcy_enabled']) 
			? $gateways[$id]->settings['fcy_enabled'] : "yes";
		$ktc_max_months_setting = isset($gateways[$id]) && isset($gateways[$id]->settings['ktc_max_months_setting']) 
			? $gateways[$id]->settings['ktc_max_months_setting'] : 10;
		$bay_max_months_setting = isset($gateways[$id]) && isset($gateways[$id]->settings['bay_max_months_setting']) 
			? $gateways[$id]->settings['bay_max_months_setting'] : 10;
		$fcy_max_months_setting = isset($gateways[$id]) && isset($gateways[$id]->settings['fcy_max_months_setting']) 
			? $gateways[$id]->settings['fcy_max_months_setting'] : 10;
		$msfee = isset($gateways[$id]) && isset($gateways[$id]->settings['fee_setting']) 
			? $gateways[$id]->settings['fee_setting'] : "include";
			
		if ($msfee == "include"){
			$KTC = [ 3, 4, 5, 6, 7, 8, 9, 10];
			$BAY = [ 3, 4, 6, 9, 10];
			$FCY = [ 3, 4, 6, 9, 10];
		} else if ($msfee == "exclude") {
			$KTC = [ 3, 4, 5, 6, 7, 8, 9, 10];
			$BAY = [ 3, 4, 6, 9, 10];
			$FCY = [ 3, 4, 6, 9, 10, 12, 18, 24, 36];
		}

		// Define fallback translations in case constants are not loaded
		$fallback_translations = array(
			'MNS_CC_INS_TITLE' => 'Credit Card Installment',
			'MNS_CC_INS_MONTH' => 'Installment Months',
			'MNS_MONTH' => 'Month',
			'MNS_BAHT' => 'THB',
			'MNS_INS' => 'Installment',
			'MNS_CC_INS_KTC' => 'KTC Credit Card',
			'MNS_CC_INS_BAY' => 'BAY Credit Card',
			'MNS_CC_INS_FCY' => 'FCY Credit Card',
			'MNS_PAY_INS' => 'Pay by Installment',
		);
		
		// Use constants if defined, otherwise use fallback
		$cc_ins_i18n = array();
		foreach ($fallback_translations as $constant_name => $fallback_value) {
			$cc_ins_i18n[$constant_name] = defined($constant_name) ? constant($constant_name) : $fallback_value;
		}

		// Safe root URL
		$root_url = defined('MONEYSPACE_ROOT_URL') ? MONEYSPACE_ROOT_URL : plugins_url('/', dirname(__DIR__));

		return [
			'title'       => $this->get_setting( 'title' ),
			'description' => $this->get_setting( 'description' ),
			'icons'		  => [$this->get_payment_method_icons()],
			'i18n' => $cc_ins_i18n,
			'msfee' => $msfee,
			'ccIns' => [
				array(
					"code" => "ktc",
					"isEnabled" => $ktc_enabled,
					"label" => $cc_ins_i18n['MNS_CC_INS_KTC'],
					"maxMonth" => $ktc_max_months_setting,
					"icon" => $root_url . 'includes/images/installment/ktc-logo.png',
					"months" => $KTC,
					"rate" => 0.8

				),
				array(
					"code" => "bay",
					"isEnabled" => $bay_enabled,
					"label" => $cc_ins_i18n['MNS_CC_INS_BAY'],
					"maxMonth" => $bay_max_months_setting,
					"icon" => $root_url . 'includes/images/installment/bay_central_lotus.png',
					"months" => $BAY,
					"rate" => 0.8
				),
				array(
					"code" => "fcy",
					"isEnabled" => $fcy_enabled,
					"label" => $cc_ins_i18n['MNS_CC_INS_FCY'],
					"maxMonth" => $fcy_max_months_setting,
					"icon" => $root_url . 'includes/images/installment/fcy-logo.png',
					"months" => $FCY,
					"rate" => 1
				),
			],
			'supports'    => $this->gateway && $this->gateway->supports 
				? array_filter( $this->gateway->supports, [ $this->gateway, 'supports' ] ) 
				: []
		];
	}

	public function get_payment_method_icons() {
		// Safety check for gateway existence
		if (!$this->gateway || !is_object($this->gateway)) {
			$icon_url = defined('MONEYSPACE_LOGO_INSTALLMENT') ? MONEYSPACE_LOGO_INSTALLMENT : plugins_url('includes/images/moneyspace-installment-logo.png', dirname(__DIR__));
		} else {
			$icon_url = $this->gateway->icon;
		}
		
		return [
			'id'  => 'moneyspace_installment',
			'src' => $icon_url,
			'alt' => 'moneyspace_installment'
		];
	}
}
