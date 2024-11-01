<?php
/**
 * Eway Support for Cart and Checkout blocks.
 *
 * @package WooCommerce Eway Payment Gateway
 */

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

/**
 * Eway payment method integration
 *
 * @since 3.2.0
 */
final class WC_Gateway_EWAY_Blocks_Support extends AbstractPaymentMethodType {
	/**
	 * Name of the payment method.
	 *
	 * @var string
	 */
	protected $name = 'eway';

	/**
	 * Initializes the payment method type.
	 */
	public function initialize() {
		$this->settings = get_option( 'woocommerce_eway_settings', array() );

		add_action(
			'woocommerce_blocks_enqueue_checkout_block_scripts_before',
			function() {
				add_filter( 'woocommerce_saved_payment_methods_list', array( $this, 'add_eway_saved_payment_methods' ), 10, 1 );
			}
		);
		add_action(
			'woocommerce_blocks_enqueue_checkout_block_scripts_after',
			function () {
				remove_filter( 'woocommerce_saved_payment_methods_list', array( $this, 'add_eway_saved_payment_methods' ) );
			}
		);
	}

	/**
	 * Manually adds the customers Eway cards to the list of cards returned
	 * by the `woocommerce_saved_payment_methods_list` filter.
	 *
	 * Eway tokens use the `eway_cc` token type instead of the `cc` token type.
	 * WooCommerce Blocks doesn't know how to display the `eway_cc` token type,
	 * so this converts all Eway saved cards to the standard `cc` token type
	 * and unsets the custom `eway_cc` token type.
	 *
	 * @param array $saved_methods Saved payment methods list.
	 * @return array
	 */
	public function add_eway_saved_payment_methods( $saved_methods ) {
		$saved_cards = $this->get_saved_cards();

		if ( ! $saved_cards ) {
			$saved_cards = array();
		}

		foreach ( $saved_cards as $saved_card ) {
			$saved_method = array(
				'method'     => array(
					'gateway' => 'eway',
					'last4'   => esc_html( substr( $saved_card->get_number(), -4 ) ),
					'brand'   => esc_html__( 'Card', 'wc-eway' ),
				),
				'expires'    => sprintf(
					/* translators: %1$s Expiration month, %2$s Expiration year. */
					esc_html__( '%1$s/%2$s', 'wc-eway' ),
					$saved_card->get_expiry_month(),
					$saved_card->get_expiry_year()
				),
				'is_default' => $saved_card->is_default(),
				'actions'    => array(),
				'tokenId'    => $saved_card->get_id(),
			);

			$saved_methods['cc'][] = $saved_method;
		}

		unset( $saved_methods['eway_cc'] );
		return $saved_methods;
	}

	/**
	 * Returns if this payment method should be active. If false, the scripts will not be enqueued.
	 *
	 * @return boolean
	 */
	public function is_active() {
		return wc_eway_get_gateway_class()->is_available();
	}

	/**
	 * Returns an array of scripts/handles to be registered for this payment method.
	 *
	 * @return array
	 */
	public function get_payment_method_script_handles() {
		$file_path  = '/dist/js/gutenberg-blocks.js';
		$asset_data = wc_eway_get_asset_data( $file_path );

		wp_register_script(
			'wc-eway-blocks-integration',
			WOOCOMMERCE_GATEWAY_EWAY_URL . $file_path,
			$asset_data['dependencies'],
			$asset_data['version'],
			true
		);

		wp_set_script_translations(
			'wc-eway-blocks-integration',
			'wc-eway'
		);

		return array( 'wc-eway-blocks-integration' );
	}

	/**
	 * Returns an array of key=>value pairs of data made available to the payment methods script.
	 *
	 * @return array
	 */
	public function get_payment_method_data() {
		$show_saved_cards    = $this->get_show_saved_cards();
		$payment_method_data = array(
			'title'                  => $this->get_setting( 'title' ),
			'description'            => $this->get_setting( 'description' ),
			'supports'               => $this->get_supported_features(),
			'showSavedCards'         => $show_saved_cards,
			'ajaxUrl'                => admin_url( 'admin-ajax.php' ),
			'threeDsEnrollmentNonce' => wp_create_nonce( 'wc-eway-3ds-enrollment-nonce' ),
			'ewayPublicApiKey'       => $this->get_setting( 'public_api_key' ),
			'is3dSecure'             => $this->get_setting( '3d_secure' ) === 'yes',
		);

		if ( $show_saved_cards ) {
			$payment_method_data = array_merge(
				$payment_method_data,
				array(
					'_eway_nonce' => wp_create_nonce( 'eway_use_saved_card' ),
				)
			);
		}

		return $payment_method_data;
	}

	/**
	 * Determine if saved cards should be shown as an option.
	 *
	 * @return bool True if customers should be able to select a saved card during checkout.
	 */
	private function get_show_saved_cards() {
		return isset( $this->settings['saved_cards'] ) ? 'yes' === $this->settings['saved_cards'] : false;
	}

	/**
	 * Get saved cards data from the database.
	 *
	 * @return object[] Array of saved cards data.
	 */
	private function get_saved_cards() {
		return WC_Payment_Tokens::get_customer_tokens( get_current_user_id(), $this->name );
	}

	/**
	 * Returns an array of supported features.
	 *
	 * @return string[]
	 */
	public function get_supported_features() {
		return wc_eway_get_gateway_class()->supports;
	}
}
