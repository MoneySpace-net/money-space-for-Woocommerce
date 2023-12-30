<?php

namespace MoneySpace\Payments;

use MoneySpace\MoneySpacePayment;
use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;
use Automattic\WooCommerce\Blocks\Assets\Api;

/**
 * Class MoneySpace_CreditCard
 *
 * @package MoneySpace\Payments
 */
class MoneySpace_CreditCard extends AbstractPaymentMethodType {

	public $name = MNS_ID;

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
		$this->settings = get_option( 'woocommerce_'.MNS_ID.'_settings', [] ); // '.MNS_ID.'_settings
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
	}

	// /**
	//  * Returns if this payment method should be active. If false, the scripts will not be enqueued.
	//  *
	//  * @return boolean
	//  */
	// public function is_active() {
	// 	return $this->gateway->is_available();
	// }


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
			'wc-moneyspace-creditcard',
			$script_url,
			$script_asset[ 'dependencies' ],
			$script_asset[ 'version' ],
			true
		);

		return [ 'wc-moneyspace-creditcard' ];
	}

	/**
	 * Returns an array of key=>value pairs of data made available to the payment methods script.
	 *
	 * @return array
	 */
	public function get_payment_method_data() {
		return [
			'title'       => $this->get_setting( 'title' ),
			'description' => $this->get_setting( 'description' ),
			'supports'    => array_filter( $this->gateway->supports, [ $this->gateway, 'supports' ] )
		];
	}
}
