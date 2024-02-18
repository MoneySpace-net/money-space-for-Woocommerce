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

	/**
	 * The gateway instance.
	 *
	 * @var MNS_Payment_Gateway
	 */
	private $gateway;
	
	public function __construct( ) {
	}

	/**
	 * Initializes the payment method type.
	 */
	public function initialize() {
		$this->settings = get_option( 'woocommerce_moneyspace_settings', [] ); // '.MNS_ID.'_settings
		$gateways       = WC()->payment_gateways->payment_gateways();
		$this->gateway  = $gateways[ $this->name ];
	}

	/**
	 * Returns if this payment method should be active. If false, the scripts will not be enqueued.
	 *
	 * @return boolean
	 */
	public function is_active() {
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
		$payment_gateway_id = MNS_ID;
		$gateways = WC()->payment_gateways->get_available_payment_gateways();
		$ms_template_payment = $gateways[$payment_gateway_id]->settings['ms_template_payment'];
		$cc_i18n = array(
			'MNS_CC_NO' => MNS_CC_NO,
			'MNS_CC_NAME' => MNS_CC_NAME,
			'MNS_CC_EXP_MONTH' => MNS_CC_EXP_MONTH,
			'MNS_CC_EXP_YEAR' => MNS_CC_EXP_YEAR,
			'MNS_CC_CVV' => MNS_CC_CVV,
			'MNS_MONTH' => MNS_MONTH,
			'MNS_YEAR' => MNS_YEAR,
			'MNS_CC_WARN_CC_NO_1' => MNS_CC_WARN_CC_NO_1,
			'MNS_CC_WARN_CC_NO_2' => MNS_CC_WARN_CC_NO_2,
			'MNS_CC_WARN_CC_NAME' => MNS_CC_WARN_CC_NAME,
			'MNS_CC_WARN_CC_EXP_MONTH' => MNS_CC_WARN_CC_EXP_MONTH,
			'MNS_CC_WARN_CC_EXP_YEAR' => MNS_CC_WARN_CC_EXP_YEAR,
			'MNS_CC_WARN_CVV_1' => MNS_CC_WARN_CVV_1,
			'MNS_CC_WARN_CVV_2' => MNS_CC_WARN_CVV_2,
		);
		return [
			'title'       => $this->get_setting( 'title' ),
			'description' => $this->get_setting( 'description' ),
			'icons'		  => [$this->get_payment_method_icons()],
			'ms_template_payment' => $ms_template_payment,
			'i18n' => $cc_i18n,
			'supports'    => array_filter( $this->gateway->supports, [ $this->gateway, 'supports' ] )
		];
	}

	public function get_payment_method_icons() {
		return [
			'id'  => 'moneyspace',
			'src' => $this->gateway->icon,
			'alt' => 'moneyspace'
		];
	}
}
