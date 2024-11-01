<?php
/**
 * This class should be used to handle ajax requests.
 *
 * @package WooCommerce Eway Payment Gateway
 * @since   x.x.x
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class WC_Gateway_EWAY_Ajax_Request_Controller.
 *
 * @since 3.7.0
 */
class WC_Gateway_EWAY_Ajax_Request_Controller {
	/**
	 * This variable should contain the gateway class.
	 *
	 * @since 3.7.0
	 * @var WC_Gateway_EWAY
	 */
	private $gateway;

	/**
	 * This function should return the gateway class.
	 *
	 * @since 3.7.0
	 */
	private function get_gateway_class(): WC_Gateway_EWAY {
		if ( $this->gateway ) {
			return $this->gateway;
		}

		$this->gateway = wc_eway_get_gateway_class();

		return $this->gateway;
	}

	/**
	 * This function should return the eway api class.
	 *
	 * @since 3.7.0
	 */
	private function get_eway_api(): WC_EWAY_API {
		return $this->get_gateway_class()->get_api();
	}

	/**
	 * This function should be used to setup hooks for the ajax request.
	 *
	 * @since 3.7.0
	 * @return void
	 */
	public function setup_hooks() {
		// Add ajax request controller for eway 3D secure enrollment for checkout page.
		add_action( 'wp_ajax_wc_eway_3ds_enrollment', array( $this, 'eway_3ds_enrollment' ) );
		add_action( 'wp_ajax_nopriv_wc_eway_3ds_enrollment', array( $this, 'eway_3ds_enrollment' ) );

		// Add ajax request controller for eway 3D secure enrollment for order re-payment.
		add_action( 'wp_ajax_wc_eway_3ds_enrollment_order_pay', array( $this, 'eway_3ds_enrollment_order_pay' ) );
		add_action(
			'wp_ajax_nopriv_wc_eway_3ds_enrollment_order_pay',
			array( $this, 'eway_3ds_enrollment_order_pay' )
		);
	}

	/**
	 * Should handle Eway 3D secure enrollment ajax request.
	 *
	 * @since 3.7.1 Add support for saved token id.
	 * @since 3.7.0
	 * @return void
	 * @throws Exception If an error occurs during API request.
	 */
	public function eway_3ds_enrollment(): void {
		check_ajax_referer( 'wc-eway-3ds-enrollment-nonce' );

		try {
			$cart                       = WC()->cart;
			[ $token, $is_saved_token ] = $this->get_token_from_ajax_request(); //phpcs:ignore

			$payment_amount = $cart->total * 100;
			$customer       = $cart->get_customer();

			// Enroll customer credit card for 3D secure.
			$result = $this->get_eway_api()->three_ds_enroll_secured_card_data_token(
				$payment_amount,
				$customer,
				$token,
				$is_saved_token
			);

			wp_send_json_success( array( 'three_ds_enrollment_access_code' => $result->AccessCode ) );
		} catch ( Exception $exception ) {
			wp_send_json_error( array( 'errors' => $exception->getMessage() ) );
		}
	}

	/**
	 * Should handle Eway 3D secure enrollment ajax request.
	 *
	 * @since 3.7.1 Add support for saved token id.
	 * @since 3.7.0
	 * @return void
	 * @throws Exception If an error occurs during API request.
	 */
	public function eway_3ds_enrollment_order_pay(): void {
		check_ajax_referer( 'wc-eway-3ds-enrollment-nonce' );

		try {
			$order_key = isset( $_POST['order_key'] ) ?
				sanitize_text_field( wp_unslash( $_POST['order_key'] ) ) :
				null;

			$order_id = wc_get_order_id_by_order_key( $order_key );
			$order    = wc_get_order( $order_id );

			if ( ! $order || ! $order->needs_payment() ) {
				wp_send_json_error( array( 'errors' => __( 'Eway error: Un-authorize action', 'wc-eway' ) ) );
			}

			if (
				! current_user_can( 'pay_for_order', $order_id )
				|| $order->get_order_key() !== $order_key
			) {
				wp_send_json_error( array( 'errors' => __( 'Eway error: Invalid order key.', 'wc-eway' ) ) );
			}

			$payment_amount             = $order->get_total() * 100;
			$customer                   = new WC_Customer( $order->get_customer_id() );
			[ $token, $is_saved_token ] = $this->get_token_from_ajax_request(); //phpcs:ignore

			// Enroll customer credit card for 3D secure.
			$result = $this->get_eway_api()->three_ds_enroll_secured_card_data_token(
				$payment_amount,
				$customer,
				$token,
				$is_saved_token
			);

			wp_send_json_success( array( 'three_ds_enrollment_access_code' => $result->AccessCode ) );
		} catch ( Exception $exception ) {
			wp_send_json_error( array( 'errors' => $exception->getMessage() ) );
		}
	}

	/**
	 * This function should be used to access token from ajax request.
	 *
	 * @throws Exception If an error throw exception.
	 */
	private function get_token_from_ajax_request(): array {
		$secure_fields_token = ! empty( $_POST['secure_fields_token'] ) ?
			sanitize_text_field( wp_unslash( $_POST['secure_fields_token'] ) ) :
			null;
		$saved_token_id      = ! empty( $_POST['saved_token_id'] ) ?
			sanitize_text_field( wp_unslash( $_POST['saved_token_id'] ) ) :
			null;
		$saved_token         = new WC_Payment_Token_Eway_CC( $saved_token_id );
		$eway_customer_token = $saved_token->get_token();

		if ( ! $eway_customer_token && ! $secure_fields_token ) {
			throw new Exception( esc_html__( 'Eway error: Invalid data.', 'wc-eway' ) );
		}

		if ( $eway_customer_token ) {
			if (
				! is_user_logged_in()
				|| ! isset( $_POST['_eway_nonce'] )
				|| ! wp_verify_nonce( sanitize_key( $_POST['_eway_nonce'] ), 'eway_use_saved_card' )
			) {
				throw new Exception(
					esc_html__(
						'Eway Error: Unauthorized user can process payment with saved card.',
						'wc-eway'
					)
				);
			}
		} elseif ( empty( $secure_fields_token ) ) {
			throw new Exception(
				esc_html__(
					'Eway Error: Unauthorized user can process payment with saved card.',
					'wc-eway'
				)
			);
		}

		return array(
			$secure_fields_token ?? $eway_customer_token,
			! empty( $eway_customer_token ),
		);
	}
}

// Set up the ajax controller.
( new WC_Gateway_EWAY_Ajax_Request_Controller() )->setup_hooks();
