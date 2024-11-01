<?php
/**
 * WC Payment Token for Eway credit cards.
 *
 * @package WooCommerce Eway Payment Gateway
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WC_Payment_Token_Eway_CC' ) ) {

	/**
	 * WC_Payment_Token_Eway_CC class.
	 */
	class WC_Payment_Token_Eway_CC extends WC_Payment_Token {

		/**
		 * Token Type String.
		 *
		 * @var string
		 */
		protected $type = 'Eway_CC';

		/**
		 * Stores Credit Card payment token data.
		 *
		 * @var array
		 */
		protected $extra_data = array(
			'number'       => '',
			'expiry_year'  => '',
			'expiry_month' => '',
		);

		/**
		 * Get type to display to user.
		 *
		 * @param  string $deprecated Deprecated since WooCommerce 3.0.
		 * @return string
		 */
		public function get_display_name( $deprecated = '' ) {
			$display = sprintf(
				/* translators: %1$s Card number, %2$s Expiry month, %3$s Expiry year.  */
				__( '%1$s (expires %2$s/%3$s)', 'wc-eway' ),
				$this->get_number(),
				$this->get_expiry_month(),
				$this->get_expiry_year()
			);
			return $display;
		}

		/**
		 * Hook prefix.
		 */
		protected function get_hook_prefix() {
			return 'woocommerce_wc_gateway_eway_payment_token_cc_get_';
		}

		/**
		 * Validate credit card payment tokens.
		 * number        - string Masked credit card number for the card
		 * expiry_year   - string Expiration date (YY) for the card
		 * expiry_month  - string Expiration date (MM) for the card
		 *
		 * @return boolean True if the passed data is valid.
		 */
		public function validate() {
			if ( false === parent::validate() ) {
				return false;
			}

			if ( ! $this->get_number( 'edit' ) ) {
				return false;
			}

			if ( ! $this->get_expiry_year( 'edit' ) ) {
				return false;
			}

			if ( ! $this->get_expiry_month( 'edit' ) ) {
				return false;
			}

			if ( 2 !== strlen( $this->get_expiry_year( 'edit' ) ) ) {
				return false;
			}

			if ( 2 !== strlen( $this->get_expiry_month( 'edit' ) ) ) {
				return false;
			}

			return true;
		}

		/**
		 * Return the card expiration year (YY).
		 *
		 * @param  string $context What the value is for. Valid values are view and edit.
		 * @return string Expiration year.
		 */
		public function get_expiry_year( $context = 'view' ) {
			return $this->get_prop( 'expiry_year', $context );
		}

		/**
		 * Set the expiration year for the card (YY format).
		 *
		 * @param string $year Credit card expiration year.
		 */
		public function set_expiry_year( $year ) {
			$this->set_prop( 'expiry_year', $year );
		}

		/**
		 * Return the card expiration month (MM).
		 *
		 * @param  string $context What the value is for. Valid values are view and edit.
		 * @return string Expiration month.
		 */
		public function get_expiry_month( $context = 'view' ) {
			return $this->get_prop( 'expiry_month', $context );
		}

		/**
		 * Set the expiration month for the card (formats into MM format).
		 *
		 * @param string $month Credit card expiration month.
		 */
		public function set_expiry_month( $month ) {
			$this->set_prop( 'expiry_month', str_pad( $month, 2, '0', STR_PAD_LEFT ) );
		}

		/**
		 * Return the masked credit card number.
		 *
		 * @param  string $context What the value is for. Valid values are view and edit.
		 * @return string Masked credit card number.
		 */
		public function get_number( $context = 'view' ) {
			return $this->get_prop( 'number', $context );
		}

		/**
		 * Set the masked credit card number.
		 *
		 * @param string $number Masked credit card number.
		 */
		public function set_number( $number ) {
			$this->set_prop( 'number', $number );
		}
	}
}
