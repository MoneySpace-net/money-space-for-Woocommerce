<?php

namespace MoneySpace\Payments;

use WC_Payment_Gateway;
use WC_Payment_Gateways;
use WC_Order;
use MoneySpace\Mslogs;
use Exception;

/**
 * WC_Gateway_Dummy class
 *
 * @author   SomewhereWarm <info@somewherewarm.gr>
 * @package  WooCommerce Dummy Payments Gateway
 * @since    1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * MNS Payment Gateway Test
 *
 * @class    MNS_Payment_Gateway_Test
 * @version  1.0.5
 */
class MNS_Payment_Gateway_Test extends WC_Payment_Gateway {

	public $domain;

	/**
	 * Constructor for the gateway.
	 */
	public function __construct() {
		$this->domain = "ms_payment";
		$this->id                 = 'moneyspace_test';
		$this->icon               = apply_filters( 'woocommerce_dummy_gateway_icon', '' );
		$this->has_fields         = false;
		$this->supports           = array(
			'products'
			// ,
			// 'subscriptions',
			// 'subscription_cancellation',
			// 'subscription_suspension',
			// 'subscription_reactivation',
			// 'subscription_amount_changes',
			// 'subscription_date_changes',
			// 'multiple_subscriptions'
		);

		$this->method_title       = _x( 'MoneySpace Dummy Payment', 'MoneySpace Dummy payment method', $this->domain );
		$this->method_description = __( 'Allows dummy payments.', $this->domain );

		// Load the settings.
		$this->init_form_fields();
		$this->init_settings();

		// Define user set variables.
		$this->title                    = $this->get_option( 'title' );
		$this->description              = $this->get_option( 'description' );
		$this->instructions             = $this->get_option( 'instructions', $this->description );
		$this->hide_for_non_admin_users = $this->get_option( 'hide_for_non_admin_users' );

		// Actions.
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		add_action( 'woocommerce_scheduled_subscription_payment_dummy', array( $this, 'process_subscription_payment' ), 10, 2 );
	}

	/**
	 * Initialise Gateway Settings Form Fields.
	 */
	public function init_form_fields() {

		$this->form_fields = array(
			'enabled' => array(
				'title'   => __( 'Enable/Disable', $this->domain ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable Dummy Payments', $this->domain ),
				'default' => 'yes',
			),
			'hide_for_non_admin_users' => array(
				'type'    => 'checkbox',
				'label'   => __( 'Hide at checkout for non-admin users', $this->domain ),
				'default' => 'no',
			),
			'title' => array(
				'title'       => __( 'Title', $this->domain ),
				'type'        => 'text',
				'description' => __( 'This controls the title which the user sees during checkout.', $this->domain ),
				'default'     => _x( 'Dummy Payment', 'Dummy payment method', $this->domain ),
				'desc_tip'    => true,
			),
			'description' => array(
				'title'       => __( 'Description', $this->domain ),
				'type'        => 'textarea',
				'description' => __( 'Payment method description that the customer will see on your checkout.', $this->domain ),
				'default'     => __( 'The goods are yours. No money needed.', $this->domain ),
				'desc_tip'    => true,
			),
			'result' => array(
				'title'    => __( 'Payment result', $this->domain ),
				'desc'     => __( 'Determine if order payments are successful when using this gateway.', $this->domain ),
				'id'       => 'woo_dummy_payment_result',
				'type'     => 'select',
				'options'  => array(
					'success'  => __( 'Success', $this->domain ),
					'failure'  => __( 'Failure', $this->domain ),
				),
				'default' => 'success',
				'desc_tip' => true,
			)
		);
	}

	/**
	 * Process the payment and return the result.
	 *
	 * @param  int  $order_id
	 * @return array
	 */
	public function process_payment( $order_id ) {

		$payment_result = $this->get_option( 'result' );

		if ( 'success' === $payment_result ) {
			$order = wc_get_order( $order_id );

			$order->payment_complete();

			// Remove cart
			WC()->cart->empty_cart();

			// Return thankyou redirect
			return array(
				'result' 	=> 'success',
				'redirect'	=> $this->get_return_url( $order )
			);
		} else {
			$message = __( 'Order payment failed. To make a successful payment using Dummy Payments, please review the gateway settings.', $this->domain );
			throw new Exception( $message );
		}
	}

	/**
	 * Process subscription payment.
	 *
	 * @param  float     $amount
	 * @param  WC_Order  $order
	 * @return void
	 */
	public function process_subscription_payment( $amount, $order ) {
		$payment_result = $this->get_option( 'result' );

		if ( 'success' === $payment_result ) {
			$order->payment_complete();
		} else {
			$message = __( 'Order payment failed. To make a successful payment using Dummy Payments, please review the gateway settings.', $this->domain );
			throw new Exception( $message );
		}
	}
}
