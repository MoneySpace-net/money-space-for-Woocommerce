<?php

namespace MoneySpace\Payments;

use MoneySpace\MoneySpacePayment;
use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;
use Automattic\WooCommerce\Blocks\Assets\Api;

/**
 * Class MoneySpace_QRCode
 *
 * @package MoneySpace\Payments
 */
class MoneySpace_QRCode extends AbstractPaymentMethodType {

	public $name = 'moneyspace_qrprom'; // Use string instead of constant to prevent undefined constant error

	/**
	 * The gateway instance.
	 *
	 * @var MNS_Payment_Gateway_QR
	 */
	private $gateway;
	private $logger;
	
	public function __construct( ) {
		$this->logger = wc_get_logger();
		// Set name with fallback
		$this->name = defined('MONEYSPACE_ID_QRPROM') ? MONEYSPACE_ID_QRPROM : 'moneyspace_qrprom';
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
					$this->logger->error( 'MoneySpace: QR gateway not found during initialization: ' . $this->name, [ 'source' => 'moneyspace' ] );
					$this->gateway = null;
				}
			} else {
				$this->logger->error('MoneySpace: WooCommerce payment gateways not available during QR initialization', [ 'source' => 'moneyspace' ] );
				$this->gateway = null;
			}
		} catch (\Exception $e) {
			$this->logger->error( 'MoneySpace: Error during QR initialization: ' . $e->getMessage(), [ 'source' => 'moneyspace' ] );
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
		$script_path       = '/assets/js/frontend/blocks-ms-qr.js';
		$script_asset_path = MoneySpacePayment::plugin_abspath() . 'assets/js/frontend/blocks-ms-qr.asset.php';
		$script_asset      = file_exists( $script_asset_path )
			? require( $script_asset_path )
			: array(
				'dependencies' => array(),
				'version'      => '1.2.0'
			);
		$script_url        = MoneySpacePayment::plugin_url() . $script_path;

		wp_register_script(
			'wc-moneyspace-qr',
			$script_url,
			$script_asset[ 'dependencies' ],
			$script_asset[ 'version' ],
			true
		);

		return [ 'wc-moneyspace-qr' ];
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
			'icons'		  => [$this->get_payment_method_icons()],
			'supports'    => $this->gateway && $this->gateway->supports 
				? array_filter( $this->gateway->supports, [ $this->gateway, 'supports' ] ) 
				: []
		];
	}

	public function get_payment_method_icons() {
		// Safety check for gateway existence
		if (!$this->gateway || !is_object($this->gateway)) {
			$icon_url = defined('MONEYSPACE_LOGO_QR') ? MONEYSPACE_LOGO_QR : plugins_url('includes/images/moneyspace-qr-logo.png', dirname(__DIR__));
		} else {
			$icon_url = $this->gateway->icon;
		}
		
		return [
			'id'  => 'moneyspace_qr',
			'src' => $icon_url,
			'alt' => 'moneyspace_qr'
		];
	}
}
