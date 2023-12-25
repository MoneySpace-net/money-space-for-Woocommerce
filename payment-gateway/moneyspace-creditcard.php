<?php

namespace MoneySpace\Payments;

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
	 * An instance of the Asset Api
	 *
	 * @var Api
	 */
	private $asset_api;
	

	/**
	 * Constructor
	 *
	 * @param Api $asset_api An instance of Api.
	 */
	public function __construct( Api $asset_api ) {
		$this->asset_api = $asset_api;
	}

	/**
	 * Initializes the payment method type.
	 */
	public function initialize() {
		$this->settings = get_option( 'woocommerce_'.MNS_ID.'_settings', [] ); // '.MNS_ID.'_settings
	}

	/**
	 * Returns if this payment method should be active. If false, the scripts will not be enqueued.
	 *
	 * @return boolean
	 */
	public function is_active() {
		return filter_var( $this->get_setting( 'enabled', false ), FILTER_VALIDATE_BOOLEAN );
	}

	/**
	 * Returns an array of scripts/handles to be registered for this payment method.
	 *
	 * @return array
	 */
	public function get_payment_method_script_handles() {
		$this->asset_api->register_script(
			'wc-moneyspace-creditcard',
			MNS_ROOT . 'assets/js/payment-method-extensions/creditcard.js'
			//'build/wc-payment-method-cod.js'
		);
		// wp_register_script( 'moneyspace_payment_block_creditcard.js', MNS_ROOT_URL .'assets/js/payment-method-extensions/creditcard.js', array(), filemtime( MNS_ROOT . 'assets/js/payment-method-extensions/creditcard.js' ), true );
		return [ 'wc-moneyspace-creditcard' ];
	}

	/**
	 * Returns an array of key=>value pairs of data made available to the payment methods script.
	 *
	 * @return array
	 */
	public function get_payment_method_data() {
		// echo "<pre>";
		// echo json_encode([
		// 	'title'                    => $this->get_setting( 'title' ),
		// 	'description'              => $this->get_setting( 'description' ),
		// 	// 'enableForVirtual'         => $this->get_enable_for_virtual(),
		// 	// 'enableForShippingMethods' => $this->get_enable_for_methods(),
		// 	'supports'                 => $this->get_supported_features(),
		// ]);
		// echo "</pre>";
		// exit();
		return [
			'title'                    => $this->get_setting( 'title' ),
			'description'              => $this->get_setting( 'description' ),
			// 'enableForVirtual'         => $this->get_enable_for_virtual(),
			// 'enableForShippingMethods' => $this->get_enable_for_methods(),
			'supports'                 => $this->get_supported_features(),
		];
	}
}
