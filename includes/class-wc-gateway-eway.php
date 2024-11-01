<?php
/**
 * Main Eway Gateway Class
 *
 * @package WooCommerce Eway Payment Gateway
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WC_Gateway_EWAY' ) ) {
	/**
	 * WC_Gateway_EWAY class.
	 *
	 * @extends WC_Payment_Gateway
	 *
	 * @property-read string $public_api_key    Eway public api key.
	 * @property-read string $customer_api      Eway customer api key.
	 * @property-read string $customer_password Eway customer password.
	 * @property-read string $testmode          Eway sanodbox mode.
	 */
	class WC_Gateway_EWAY extends WC_Payment_Gateway {
		const SECURE_FIELD_CONNECTION = 'card_credit';
		const RESPONSIVE_SHARED_PAGE = 'shared_page';
		const TOKEN_PAYMENT_METHOD = 'TokenPayment';
		const PROCESS_PAYMENT_METHOD = 'ProcessPayment';

		/**
		 * Eway API object.
		 *
		 * @var WC_EWAY_API
		 */
		protected $api;

		/**
		 * Plugin URL.
		 *
		 * @var string
		 */
		protected $plugin_url;

		/**
		 * Success Response Messages.
		 *
		 * @var array
		 */
		protected $success_response_messages;

		/**
		 * Whether or not logging is enabled.
		 *
		 * @var boolean
		 */
		public static $log_enabled = false;

		/**
		 * Logger instance.
		 *
		 * @var WC_Logger
		 */
		public static $log = false;

		/**
		 * Constructor
		 */
		public function __construct() {
			$this->id                 = 'eway';
			$this->method_title       = __( 'Eway', 'wc-eway' );
			$this->method_description = __(
				'Allow customers to securely save their credit card to their account for use with single purchases and subscriptions.',
				'wc-eway'
			);
			$this->supports           = array(
				'subscriptions',
				'products',
				'refunds',
				'subscription_cancellation',
				'subscription_reactivation',
				'subscription_suspension',
				'subscription_amount_changes',
				'subscription_date_changes',
				'subscription_payment_method_change',
				'subscription_payment_method_change_customer',
				'subscription_payment_method_change_admin',
				'multiple_subscriptions',
				'subscription_payment_method_delayed_change',
				'tokenization',
			);

			$this->has_fields = true;

			$this->card_types = '';

			$this->success_response_messages = array( 'A2000', 'A2008', 'A2010', 'A2011', 'A2016' );

			// Load the form fields.
			$this->init_form_fields();

			// Load the settings.
			$this->init_settings();

			// Define user set variables.
			foreach ( $this->settings as $setting_key => $setting ) {
				$this->$setting_key = $setting;
			}

			$this->saved_cards = $this->get_option( 'saved_cards' ) === 'yes' ? true : false;
			$this->connection_method = $this->get_option( 'connection_method' );
			self::$log_enabled = 'on' === $this->get_option( 'debug_mode', 'off' );

			// Pay page fallback.
			if ( $this->connection_method === self::SECURE_FIELD_CONNECTION ) {
				add_action( 'woocommerce_receipt_' . $this->id, array( $this, 'receipt_page' ) );
			} else {
				add_action( 'woocommerce_receipt_' . $this->id, array( $this, 'responsive_shared_page' ) );
				add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'response_listener' ) );
			}

			// Save settings.
			if ( is_admin() ) {
				add_action( 'woocommerce_update_options_payment_gateways', array( $this, 'process_admin_options' ) );
				add_action(
					'woocommerce_update_options_payment_gateways_' . $this->id,
					array( $this, 'process_admin_options' )
				);
			}

			// Enqueue some JS functions and CSS.
			add_filter( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

			// Listen for results from Eway.
			add_action( 'woocommerce_api_wc_gateway_eway', array( $this, 'response_listener' ) );

			// Validate value lengths during checkout.
			add_action( 'woocommerce_after_checkout_validation', array( $this, 'validate_checkout_values' ), 10, 2 );

			// Migrate legacy payment tokens when accessing customer payment tokens.
			if ( $this->saved_cards ) {
				add_filter(
					'woocommerce_get_customer_payment_tokens',
					array( $this, 'get_customer_payment_tokens' ),
					10,
					3
				);
			}
		}

		/**
		 * Logging method.
		 *
		 * @param string $message Message to log.
		 */
		public static function log( $message ) {
			if ( self::$log_enabled ) {
				if ( empty( self::$log ) ) {
					self::$log = new WC_Logger();
				}
				self::$log->add( 'eway', $message );
			}
		}

		/**
		 * Initialize Gateway Settings form fields.
		 *
		 * @since 3.7.0 Add setting for Eway "Public API Key"
		 * @return void
		 */
		public function init_form_fields() {
			$this->form_fields = array(
				'enabled'           => array(
					'title'       => __( 'Enable/Disable', 'wc-eway' ),
					'label'       => __( 'Enable Eway', 'wc-eway' ),
					'type'        => 'checkbox',
					'description' => '',
					'default'     => 'no',
				),
				'title'             => array(
					'title'       => __( 'Title', 'wc-eway' ),
					'type'        => 'text',
					'description' => __( 'This controls the title which the user sees during checkout.', 'wc-eway' ),
					'default'     => __( 'Credit Card', 'wc-eway' ),
				),
				'description'       => array(
					'title'       => __( 'Description', 'wc-eway' ),
					'type'        => 'text',
					'description' => __(
						'This controls the description which the user sees during checkout.',
						'wc-eway'
					),
					'default'     => 'Pay securely using your credit card.',
				),
				'customer_api'      => array(
					'title'       => __( 'Eway Customer API Key', 'wc-eway' ),
					'type'        => 'text',
					'description' => __( 'User API Key can be found in MYeWAY.', 'wc-eway' ),
					'default'     => '',
					'css'         => 'width: 400px',
				),
				'customer_password' => array(
					'title'       => __( 'Eway Customer Password', 'wc-eway' ),
					'type'        => 'password',
					'description' => __( 'Your Eway Password.', 'wc-eway' ),
					'default'     => '',
				),
				'public_api_key'    => array(
					'title'       => __( 'Eway Public API Key', 'wc-eway' ),
					'type'        => 'text',
					'description' => __( 'Public API Key can be found in MYeWAY > My Account > API Key.', 'wc-eway' ),
					'default'     => '',
					'css'         => 'width: 400px',
				),
				'card_types'        => array(
					'title'   => __( 'Allowed Card Types', 'wc-eway' ),
					'type'    => 'multiselect',
					'class'   => 'chosen_select',
					'css'     => 'width: 450px;',
					'default' => array(
						'visa',
						'mastercard',
						'discover',
						'amex',
						'dinersclub',
						'maestro',
						'unionpay',
						'jcb',
					),
					'options' => array(
						'visa'       => 'Visa',
						'mastercard' => 'MasterCard',
						'discover'   => 'Discover',
						'amex'       => 'AmEx',
						'dinersclub' => 'Diners',
						'maestro'    => 'Maestro',
						'unionpay'   => 'UnionPay',
						'jcb'        => 'JCB',
					),
				),
			);

			if ( $this->is_eway_secure_fields_enabled() ) {
				$this->form_fields['3d_secure'] = array(
					'title'       => __( '3-D Secure', 'wc-eway' ),
					'label'       => __( 'Enable 3-D Secure', 'wc-eway' ),
					'type'        => 'checkbox',
					'description' => sprintf(
					/* translators: 1. Eway 3-D Secure documentation link*/
						__(
							'If enabled, plugin will use <a href="%1$s" target="_blank">Eway\'s 3D Secure MPI</a> for credit card validation. Please match this extension\'s setting with the 3D Secure setting on the Eway Dashboard.',
							'wc-eway'
						),
						'https://eway.io/api-v3/?php#3d-secure-2-0'
					),
					'default'     => 'yes',
				);
			}

			$this->form_fields = array_merge(
				$this->form_fields,
				array(
					'connection_method'      => array(
						'title'       => __( 'Connection Method', 'wc-eway' ),
						'label'       => __( 'Connection Method', 'wc-eway' ),
						'type'        => 'select',
						'options'     => array(
							self::SECURE_FIELD_CONNECTION => __( 'Secure Fields', 'wc-eway' ),
							self::RESPONSIVE_SHARED_PAGE => __( 'Responsive Shared Page', 'wc-eway' ),
						),
					),
					'saved_cards' => array(
						'title'       => __( 'Saved cards', 'wc-eway' ),
						'label'       => __( 'Enable saved cards', 'wc-eway' ),
						'type'        => 'checkbox',
						'description' => __(
							'If enabled, users will be able to pay with a saved card during checkout. Card details are saved on Eway servers, not on your store.',
							'wc-eway'
						),
						'default'     => 'no',
					),
					'testmode'    => array(
						'title'       => __( 'Eway Sandbox', 'wc-eway' ),
						'label'       => __( 'Enable Eway sandbox', 'wc-eway' ),
						'type'        => 'checkbox',
						'description' => __( 'Place the payment gateway in development mode.', 'wc-eway' ),
						'default'     => 'no',
					),
					'debug_mode'  => array(
						'title'    => __( 'Debug Mode', 'wc-eway' ),
						'type'     => 'select',
						'desc_tip' => __(
							'Show Detailed Error Messages and API requests in the gateway log.',
							'wc-eway'
						),
						'default'  => 'off',
						'options'  => array(
							'off' => __( 'Off', 'wc-eway' ),
							'on'  => __( 'On', 'wc-eway' ),
						),
					),
				)
			);
		}

		/**
		 * Check if gateway meets all the requirements to be used.
		 *
		 * @return bool
		 */
		public function is_available() {
			// Check if enabled.
			$is_available = parent::is_available();

			// Check for required fields.
			if ( empty( $this->customer_api ) || empty( $this->customer_password ) ) {
				$is_available = false;
			}

			/**
			 * Filter whether Eway is available.
			 *
			 * @since 3.1.6
			 */
			return apply_filters( 'woocommerce_eway_is_available', $is_available );
		}

		/**
		 * Show error in admin options if the store's currency is not an Eway-supported currency.
		 */
		public function admin_options() {
			if ( ! in_array( get_woocommerce_currency(), array( 'AUD', 'NZD', 'SGD', 'HKD', 'MYR' ), true ) ) {
				echo '<div class="notice notice-error"><p>';
				echo esc_html(
					__(
						'To use Eway payment method, currency must be set as AUD, NZD, SGD, HKD or MYR for your store.',
						'wc-eway'
					)
				);
				echo '</p></div>';
			}
			parent::admin_options();
		}

		/**
		 * Make a direct payment through the API for an order and handle the result.
		 *
		 * @param WC_Order   $order                  The order to process.
		 * @param int        $amount_to_charge       The amount to charge for the order.
		 * @param int|string $eway_token_customer_id The customer's TokenCustomerID to include in direct payment request.
		 *
		 * @return stdClass JSON parsed API response on success, or null on failure
		 * @throws Exception If order does not exist or if payment gateway fails.
		 */
		protected function process_payment_request( $order, $amount_to_charge, $eway_token_customer_id ) {
			$order_id = $order->get_id();

			self::log( $order_id . ': Processing payment request' );

			$result = json_decode(
				$this->get_api()->direct_payment(
					$order,
					$eway_token_customer_id,
					$amount_to_charge * 100.00
				)
			);

			// Check if the order exists.
			if ( absint( $result->Payment->InvoiceReference ) !== $order->get_id() ) {
				throw new Exception( __( 'Order does not exist.', 'wc-eway' ) );
			}

			return $this->handle_eway_payment_response( $result, $order );
		}

		/**
		 * Make a direct payment through the API for a new customer order with Eway secure fields and handle the result.
		 *
		 * @since 3.7.0
		 *
		 * @param \WC_Order $order                        The order to process.
		 * @param array     $threeds_verification_results The 3DS verification results.
		 * @param string    $secured_card_data_token      The secured card data token.
		 *
		 * @return stdClass JSON parsed API response on success, or null on failure
		 * @throws Exception If order does not exist or if payment gateway fails.
		 */
		protected function process_payment_with_secure_fields(
			\WC_Order $order,
			array $threeds_verification_results,
			$secured_card_data_token
		): stdClass {
			$order_id = $order->get_id();
			self::log( $order_id . ': Processing payment request' );

			$result = $this->get_api()->direct_payment_with_secured_card_data_token(
				$order,
				$threeds_verification_results,
				$secured_card_data_token
			);

			// Check if the order exists.
			if ( absint( $result->Payment->InvoiceReference ) !== $order->get_id() ) {
				throw new Exception( __( 'Order does not exist.', 'wc-eway' ) );
			}

			return $this->handle_eway_payment_response( $result, $order );
		}

		/**
		 * This function should be called after a payment has been processed on eway's servers.
		 *
		 * @param stdClass  $result The response from eway's servers.
		 * @param \WC_Order $order  The order that was processed.
		 *
		 * @since 3.7.0
		 *
		 * @return object Eway response object
		 * @throws Exception Throw exception If payment failed.
		 */
		private function handle_eway_payment_response( stdClass $result, \WC_Order $order ): object {
			// Log the response and throw error if the payment failed.
			if ( ! $result->TransactionStatus ) {
				self::log( $order->get_id() . ': Processing payment failed' );
				if ( isset( $result->Errors ) ) {
					$error = WC_Gateway_Eway_Error_Codes::get_message( $result->Errors );
				} else {
					$error = WC_Gateway_Eway_Error_Codes::get_message( $result->ResponseMessage );
				}

				// translators: %s Error message.
				$order->update_status( 'failed', sprintf( __( 'Eway token payment failed - %s', 'wc-eway' ), $error ) );

				/**
				 * Triggered when a payment with the gateway fails.
				 *
				 * @since 3.1.6
				 *
				 * @param WC_Order        $order   The order whose payment failed.
				 * @param stdClass        $result  The result from the API call.
				 * @param string          $error   The error message.
				 * @param WC_Gateway_EWAY $gateway The instance of the gateway.
				 */
				do_action( 'woocommerce_api_wc_gateway_eway_payment_failed', $order, $result, $error, $this );

				throw new Exception( $error );
			}

			self::log( $order->get_id() . ': Processing payment completed' );

			$this->preserve_customer_token( $result, $order );

			$order->add_order_note(
				sprintf(
					// translators: %s Response message.
					__( 'Eway token payment completed - %s', 'wc-eway' ),
					WC_Gateway_Eway_Error_Codes::get_message( $result->ResponseMessage, false )
				)
			);

			$order->payment_complete( $result->TransactionID );

			/**
			 * Triggered when a payment with the gateway is completed.
			 *
			 * @since 3.1.6
			 *
			 * @param \WC_Order       $order   The order whose payment failed.
			 * @param stdClass        $result  The result from the API call.
			 * @param WC_Gateway_EWAY $gateway The instance of the gateway.
			 */
			do_action( 'woocommerce_api_wc_gateway_eway_payment_completed', $order, $result, $this );

			return $result;
		}

		/**
		 * This function should preserve new Eway customer token if not exist and attach in order.
		 *
		 * @param stdClass|string $result The response from eway's servers or Eway customer token id.
		 * @param \WC_Order       $order  The order that was processed.
		 *
		 * @since 3.7.0
		 * @throws Exception Throw exception If payment failed.
		 */
		private function preserve_customer_token( $result, \WC_Order $order ): void {
			// Preserve new Eway customer token details.
			if ( $result instanceof stdClass ) {
				$eway_customer = null;

				// Preserve the token customer ID if it exists.
				$has_card_details_eway_customer = isset( $result->Customer->CardDetails )
					&& ! empty( $result->Customer->CardDetails );

				// Check if masked card number was passed otherwise look it up.
				if ( $has_card_details_eway_customer ) {
					$eway_customer = $result->Customer;
				} else {
					$customer_result = json_decode( $this->get_api()->lookup_customer( $result->TokenCustomerID ) );
					if ( isset( $customer_result->Customers[0] ) ) {
						$eway_customer = $customer_result->Customers[0];
					}
				}

				// Check if the token customer ID exists.
				if (
					! $eway_customer
					|| ! property_exists( $eway_customer, 'TokenCustomerID' )
					|| ! $eway_customer->TokenCustomerID
				) {
					return;
				}

				$eway_customer_token = (string) $eway_customer->TokenCustomerID;

				// Check if the customer already has token. If not, add it.
				$eway_cards         = WC_Payment_Tokens::get_customer_tokens( get_current_user_id(), $this->id );
				$has_customer_token = $eway_cards && array_filter(
					$eway_cards,
					static function ( $eway_card ) use ( $eway_customer_token ) {
							return $eway_card->get_token() === $eway_customer_token;
					}
				);

				if ( ! $has_customer_token ) {
					$this->add_new_customer_token( $eway_customer, $order->get_user_id() );

					// translators: %1$s Token customer ID, %2$s Token Masked card number.
					$order->add_order_note(
						sprintf(
						/* translators: 1. Eway customer token id, 2. Eway masked card number*/
							__(
								'Eway Token Customer Created - TokenCustomerID: %1$s Masked Card: %2$s',
								'wc-eway'
							),
							$eway_customer->TokenCustomerID,
							$eway_customer->CardDetails->Number
						)
					);
				}
			} else {
				$eway_customer_token = $result;
			}

			// Store if Eway customer token if is different from the one in the order.
			$order_customer_token = $this->get_token_customer_id( $order );
			if ( $eway_customer_token !== $order_customer_token ) {
				$this->set_token_customer_id( $order, $eway_customer_token );
			}
		}

		/**
		 * Check for token payments and process the payment, otherwise redirect to pay page.
		 *
		 * @since 3.7.0 Implement logic to accept payment with Eway secure fields credit card form.
		 *
		 * @param int $order_id The ID of the order.
		 *
		 * @return void|array
		 *
		 * @throws Exception Throw exception on error.
		 */
		public function process_payment( $order_id ) {
			$order = wc_get_order( $order_id );

			self::log( $order_id . ': Processing payment' );

			// Backward compatibility: process payment with transparent redirect api.
			if ( ! $this->is_eway_secure_fields_enabled() ) {
				return $this->process_payment_with_trasparent_redirect( $order );
			}

			// Process payment with Eway secure fields.
			try {
				// Get tokens.
				$secured_card_data_token = ! empty( $_POST['secure-field-token'] )
					? sanitize_text_field( wp_unslash( $_POST['secure-field-token'] ) )
					: null;
				$saved_token_id          = ! empty( $_POST['eway_card_id'] )
					? sanitize_text_field( wp_unslash( $_POST['eway_card_id'] ) )
					: null;
				$eway_customer_token     = null;

				// Guest customer does not have eway_card_id.
				// Set default token id to 'new' if the token is not saved.
				if ( $secured_card_data_token && ! $saved_token_id ) {
					$saved_token_id = 'new';
				}

				// Validate: saved customer token.
				if ( 'new' !== $saved_token_id ) {
					$saved_token         = new WC_Payment_Token_Eway_CC( $saved_token_id );
					$eway_customer_token = $saved_token->get_token();

					if (
						! $eway_customer_token
						|| ! is_user_logged_in()
						|| ! isset( $_POST['_eway_nonce'] )
						|| ! wp_verify_nonce( sanitize_key( $_POST['_eway_nonce'] ), 'eway_use_saved_card' )
					) {
						throw new Exception(
							esc_html__(
								'Eway Error: Invalid Eway customer token id.',
								'wc-eway'
							)
						);
					}
				} elseif ( empty( $secured_card_data_token ) ) {
					throw new Exception(
						esc_html__(
							'Eway Error: Invalid Eway secured card data token id.',
							'wc-eway'
						)
					);
				}

				// Create and save new token customer.
				if ( $this->can_save_eway_customer_token( $order ) ) {
					if ( ! $eway_customer_token ) {
						$wc_customer = new \WC_Customer( get_current_user_id() );
						$result      = $this->get_api()->add_new_credit_card( $wc_customer, $secured_card_data_token );

						$this->preserve_customer_token( $result, $order );
					} else {
						$this->preserve_customer_token( $eway_customer_token, $order );
					}
				}

				// If order total is zero, this means customer is adding another credit card information.
				// We should process it without 3ds verification because Eway does not enroll zero amount transactions.
				// 3D secure validation should be enabled.
				$threeds_verification_results = array();
				if ( $order->get_total() > 0 && $this->get_option( '3d_secure' ) === 'yes' ) {
					// 3DS is required for transaction.
					if ( empty( $_POST['threeds_enrollment_access_token'] ) ) {
						throw new Exception( esc_html__( 'Eway Error: 3DS verification failed.', 'wc-eway' ) );
					}

					$threeds_enrollment_access_token = sanitize_text_field( wp_unslash( $_POST['threeds_enrollment_access_token'] ) );
					$threeds_verification_results    = $this->get_api()->get_3dsverify_results( $threeds_enrollment_access_token );
				}

				$this->process_payment_with_secure_fields(
					$order,
					$threeds_verification_results,
					$secured_card_data_token
				);

				WC()->cart->empty_cart();

				self::log( $order_id . ': Redirecting to thanks URL' );

				// Return to thankyou page if successful.
				return array(
					'result'   => 'success',
					'redirect' => $this->get_return_url( $order ),
				);
			} catch ( \Exception $e ) {
				self::log( $order_id . ': Processing payment with secure fields failed - ' . $e->getMessage() );

				wc_add_notice( $e->getMessage(), 'error' );

				// Eway secure field token should be unique per transaction rest api.
				// If the token is not unique, the transaction will be declined.
				// So we need to reload the checkout page to generate a new token.
				WC()->session->reload_checkout = true;

				return array(
					'result'   => 'failure',
					'redirect' => $order->get_checkout_payment_url( true ),
					'message'  => $e->getMessage(),
				);
			}
		}

		/**
		 * Request an access code for use in an Eway Transparent Redirect payment.
		 *
		 * @since 3.5.1 Set transaction type to 'Purchase'.
		 *
		 * @param WC_Order $order The order whose token customer id should be used.
		 *
		 * @return mixed JSON response from API.
		 * @throws Exception If error occurs during API request.
		 */
		protected function request_access_code( $order ) {
			$token_payment = $this->get_token_customer_id( $order );

			if ( $token_payment && 'new' === $token_payment ) {
				$result = json_decode( $this->get_api()->request_access_code( $order, 'TokenPayment' ) );
			} elseif ( 0 === $order->get_total() && 'shop_subscription' === $order->get_type() ) {
				$result = json_decode( $this->get_api()->request_access_code( $order, 'CreateTokenCustomer' ) );
			} else {
				$result = json_decode( $this->get_api()->request_access_code( $order ) );
			}

			if ( isset( $result->Errors ) && ! is_null( $result->Errors ) ) {
				throw new Exception( $this->response_message_lookup( $result->Errors ) );
			}

			return $result;
		}

		/**
		 * Load the payment form.
		 *
		 * @param int $order_id The order to load.
		 *
		 * @return void
		 */
		public function receipt_page( $order_id ) {
			wp_enqueue_script( 'eway-credit-card-form' );

			// Get the order.
			$order = wc_get_order( $order_id );

			try {
				$result = $this->request_access_code( $order );
				$this->print_receipt_page_css();

				ob_start();
				$full_name = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
				wc_get_template( 'eway-cc-form.php', array(), 'eway/', plugin_dir_path( __FILE__ ) . '../templates/' );
				echo '<input type="hidden" name="EWAY_ACCESSCODE" value="' . esc_attr( $result->AccessCode ) . '"/>';
				echo '<input type="hidden" name="EWAY_CARDNAME" value="' . esc_attr( $full_name ) . '"/>';
				echo '<input type="hidden" name="EWAY_CARDNUMBER" id="EWAY_CARDNUMBER" value=""/>';
				echo '<input type="hidden" name="EWAY_CARDEXPIRYMONTH" id="EWAY_CARDEXPIRYMONTH" value=""/>';
				echo '<input type="hidden" name="EWAY_CARDEXPIRYYEAR" id="EWAY_CARDEXPIRYYEAR" value=""/>';
				$form  = '<form method="post" id="eway_credit_card_form">';
				$form .= '<input type="hidden" id="EWAY_SUBMIT_URL" value="' . esc_url( $result->FormActionURL ) . '"/>';
				$form .= ob_get_clean();
				$form .= '</form>';
				echo $form; // phpcs:ignore WordPress.Security.EscapeOutput
			} catch ( Exception $e ) {
				self::log( 'CC Form display error: ' . $e->getMessage() );
				wc_add_notice(
					$e->getMessage() . ': ' . __(
						'Please check your Eway API key and password.',
						'wc-eway'
					),
					'error'
				);
				wc_print_notices();

				return;
			}
		}

		/**
		 * Print the css needed to show the card type inside the CC input box.
		 *
		 * @since 3.1.10 introduced
		 */
		public function print_receipt_page_css() {
			$card_types = array(
				'visa',
				'mastercard',
				'dinersclub',
				'jcb',
				'amex',
				'discover',
			);

			$css = '.wc-credit-card-form-card-number{ font-size: 1.5em; padding: 8px; background-repeat: no-repeat; background-position: right;  }' . PHP_EOL;
			foreach ( $card_types as $card_type ) {
				$card_image_filename = 'dinersclub' === $card_type ? 'diners' : $card_type;
				$card_image_url      = WC()->plugin_url() . "/dist/images/icons/credit-cards/{$card_image_filename}.png";
				$css                .= ".woocommerce-checkout.woocommerce-order-pay  .wc-credit-card-form-card-number.$card_type{  background-image: url( $card_image_url ); }" . PHP_EOL;
			}

			echo '<style>', esc_html( $css ), '</style>';
		}

		/**
		 * Handle request to get share payment url
		 *
		 * @param $order_id
		 * @return false|mixed
		 */
		public function responsive_shared_page( $order_id ) {
			// Get the order.
			$order     = wc_get_order( $order_id );
			$orderItem = $this->get_order_item( $order );
			$this->print_responsive_shared_page_css();

			$method = $this->saved_cards ? self::TOKEN_PAYMENT_METHOD : self::PROCESS_PAYMENT_METHOD;

			$return_checkout_html = '<div class="return-checkout">';
			$return_checkout_html .= '<a href="' . wc_get_checkout_url() . '" class="return-checkout">' . __( 'Return to checkout', 'wc-eway' ) . '</a>';
			$return_checkout_html .= '</div>';

			try {
				$result = json_decode( $this->get_api()->get_access_code_share( $order, $orderItem, $method ) );
			} catch (Exception $exception) {
				wc_add_notice( __( $exception->getMessage(), 'wc-eway' ), 'error' );
				wc_print_notices();
				echo $return_checkout_html;
				self::log( $order_id . 'Get error when try to call API get access code share' );
				self::log($exception->getMessage());
				return false;
			}

			if ( intval( $result->Payment->InvoiceReference ) !== $order_id ) {
				wc_add_notice( __( 'Order does not exist.', 'wc-eway' ), 'error' );
				wc_print_notices();
				echo $return_checkout_html;
				self::log($order_id .'Get error in API get access code share response: InvoiceReference not equal order ID' );
				self::log(
					'Response listener result: ' . print_r(
						$result,
						true
					)
				);
				return false;
			}

			if ( is_null( $result->Errors ) ) {
				self::log( $order_id . ': Processing payment Processing' );
				/**
				 * Triggered when a payment with the gateway is completed.
				 *
				 * @param WC_Order $order The order whose payment failed.
				 * @param stdClass $result The result from the API call.
				 * @param WC_Gateway_EWAY $gateway The instance of the gateway.
				 */
				do_action( 'woocommerce_api_wc_gateway_eway_payment_completed', $order, $result, $this );
				wp_redirect( $result->SharedPaymentUrl );
			} else {
				self::log( $order_id . ': Processing payment failed' );
				if ( isset( $result->Errors ) && ! is_null( $result->Errors ) ) {
					$error = $this->response_message_lookup( $result->Errors );
				} else {
					$error = $this->response_message_lookup( $result->ResponseMessage );
				}

				$order->update_status( 'failed', sprintf( __( 'Eway payment failed - %s', 'wc-eway' ), $error ) );

				/**
				 * Triggered when a payment with the gateway fails.
				 *
				 * @param WC_Order $order The order whose payment failed.
				 * @param stdClass $result The result from the API call.
				 * @param string $error The error message.
				 * @param WC_Gateway_EWAY $gateway The instance of the gateway.
				 */
				do_action( 'woocommerce_api_wc_gateway_eway_payment_failed', $order, $result, $error, $this );

				wc_add_notice( $error, 'error' );
				wc_print_notices();
				echo $return_checkout_html;
				return false;
			}

			return $result;
		}

		/**
		 * Get order items data
		 *
		 * @param $order
		 * @return array
		 */
		public function get_order_item( $order ) {
			$items = array();
			foreach ( $order->get_items() as $item_id => $item ) {
				$product = $item->get_product(); // Product object gives you access to all product data

				$items[] = [
					'SKU'         => $product->get_sku(),
					'Description' => $product->get_short_description(),
					'Quantity'    => $item->get_quantity(),
					'UnitCost'    => wc_add_number_precision_deep($product->get_price()),
					'Tax'         => wc_add_number_precision_deep($item->get_subtotal_tax()),
					'Total'       => wc_add_number_precision_deep($item->get_total()),
				];
			}

			return $items;
		}

		/**
		 * Print css to for responsive shared page
		 *
		 * @return void
		 */
		public function print_responsive_shared_page_css() {
			$css = '.return-checkout { display: flex; justify-content: center; margin: 30px 0; }' . PHP_EOL;
			$css .= '.return-checkout a { background-color: #222; border-radius: 4px; border-style: none; box-sizing: border-box; color: #fff; cursor: pointer; display: inline-flex; margin: 0; font-size: 16px; font-weight: 500; line-height: 1.5; min-height: 44px; outline: none; overflow: hidden; padding: 9px 20px 8px; position: relative; text-align: center; text-transform: none; user-select: none; -webkit-user-select: none; touch-action: manipulation; }' . PHP_EOL;
			$css .= '.return-checkout a:hover, .return-checkout a:focus { opacity: .75; }' . PHP_EOL;

			echo '<style>', esc_html( $css ), '</style>';
		}

		/**
		 * Get the Eway API object.
		 *
		 * @return object WC_EWAY_API
		 */
		public function get_api() {
			if ( is_object( $this->api ) ) {
				return $this->api;
			}

			require 'class-wc-eway-api.php';

			$this->api = new WC_EWAY_API(
				$this->customer_api,
				$this->customer_password,
				'yes' === $this->testmode ? 'sandbox' : 'production',
				$this->debug_mode
			);

			return $this->api;
		}

		/**
		 * Return all icons for card types supported by Eway.
		 *
		 * @return array
		 */
		protected function get_all_payment_icons() {
			$plugin_url = $this->plugin_url();

			return array(
				'visa'       => '<img src="' . $plugin_url . 'dist/images/visa.svg" class="eway-icon" alt="Visa" />',
				'mastercard' => '<img src="' . $plugin_url . 'dist/images/mastercard.svg" class="eway-icon" alt="MasterCard" />',
				'discover'   => '<img src="' . $plugin_url . 'dist/images/discover.svg" class="eway-icon" alt="Discover" />',
				'amex'       => '<img src="' . $plugin_url . 'dist/images/amex.svg" class="eway-icon" alt="Amex" />',
				'dinersclub' => '<img src="' . $plugin_url . 'dist/images/diners.svg" class="eway-icon" alt="Diners" />',
				'maestro'    => '<img src="' . $plugin_url . 'dist/images/maestro.svg" class="eway-icon" alt="Maestro" />',
				'unionpay'   => '<img src="' . $plugin_url . 'dist/images/unionpay.svg" class="eway-icon" alt="UnionPay" />',
				'jcb'        => '<img src="' . $plugin_url . 'dist/images/jcb.svg" class="eway-icon" alt="JCB" />',
			);
		}

		/**
		 * Return icons for allowed card types.
		 *
		 * @return string
		 */
		public function get_icon() {
			wp_enqueue_style( 'eway-styles' );

			$icons      = $this->get_all_payment_icons();
			$icons_html = '';

			if ( is_array( $this->card_types ) ) {
				foreach ( $this->card_types as $card_type ) {
					$icons_html .= isset( $icons[ $card_type ] ) ? $icons[ $card_type ] : '';
				}
			}

			/**
			 * Filter the gateway icons to display for Eway.
			 *
			 * @since 3.1.6
			 */
			return apply_filters(
				'woocommerce_gateway_icon',
				/**
				 * Filter the eway icons html.
				 *
				 * @since 3.1.6
				 */
				apply_filters( 'woocommerce_eway_icon', $icons_html ),
				$this->id
			);
		}

		/**
		 * Get the token customer id for an order
		 *
		 * @param WC_Order $order The order to access.
		 *
		 * @return string
		 */
		protected function get_token_customer_id( $order ) {
			return $order->get_meta( '_eway_token_customer_id', true );
		}

		/**
		 * Enqueue scripts.
		 *
		 * @since 3.7.0 Enqueue script for Eway secure fields credit card form.
		 *
		 * @return void
		 */
		public function enqueue_scripts() {
			wp_register_script(
				'eway-credit-card-form',
				$this->plugin_url() . 'dist/js/frontend/eway-credit-card-form.js',
				array( 'jquery', 'jquery-payment' ),
				WOOCOMMERCE_GATEWAY_EWAY_VERSION,
				true
			);

			$form_errors = array(
				'anotherPaymentMethod' => __(
					'Error: Please check details and try again. If this still does not work contact the store admin or use another means of payment.',
					'wc-eway'
				),
			);

			$data = array(
				'card_types' => $this->card_types,
				'formErrors' => $form_errors,
			);

			wp_localize_script( 'eway-credit-card-form', 'eway_settings', $data );

			// Enqueue the script only if Eway public api key is set.
			if ( $this->is_eway_secure_fields_enabled() ) {
				$data['public_api_key']               = $this->get_option( 'public_api_key' );
				$data['wc_eway_3ds_enrollment_nonce'] = wp_create_nonce( 'wc-eway-3ds-enrollment-nonce' );
				$data['is_3d_secure']                 = $this->get_option( '3d_secure' ) === 'yes';

				wp_enqueue_script(
					'eway-checkout-sdk-js',
					'https://secure.ewaypayments.com/scripts/eWAY.min.js',
					array(),
					WOOCOMMERCE_GATEWAY_EWAY_VERSION,
					true
				);

				wp_enqueue_script(
					'eway-cerberus-js',
					'https://static.assets.eway.io/cerberus/6.6.2.54470/assets/sdk/cerberus' . ( 'yes' === $this->testmode ? '-sandbox' : '' ) . '.bundle.js',
					array( 'eway-checkout-sdk-js' ),
					WOOCOMMERCE_GATEWAY_EWAY_VERSION,
					true
				);

				$eway_secure_fields_aseet_info = wc_eway_get_asset_data( 'dist/js/frontend/eway-secure-fields.js' );
				wp_enqueue_script(
					'eway-secure-fields',
					$this->plugin_url() . 'dist/js/frontend/eway-secure-fields.js',
					array_merge(
						array( 'jquery', 'jquery-payment', 'eway-checkout-sdk-js', 'eway-cerberus-js' ),
						$eway_secure_fields_aseet_info['dependencies']
					),
					$eway_secure_fields_aseet_info['version'],
					true
				);

				wp_localize_script( 'eway-secure-fields', 'eway_settings', $data );
			}

			wp_register_style(
				'eway-styles',
				$this->plugin_url() . 'dist/css/eway-styles.css',
				array(),
				WOOCOMMERCE_GATEWAY_EWAY_VERSION
			);
		}

		/**
		 * Get the plugin URL.
		 *
		 * @return string
		 */
		private function plugin_url() {
			if ( isset( $this->plugin_url ) ) {
				return trailingslashit( $this->plugin_url );
			}

			if ( is_ssl() ) {
				$this->plugin_url = str_replace(
					'http://',
					'https://',
					WP_PLUGIN_URL
				) . '/' . plugin_basename( dirname( dirname( __FILE__ ) ) );
			} else {
				$this->plugin_url = WP_PLUGIN_URL . '/' . plugin_basename( dirname( dirname( __FILE__ ) ) );
			}

			return trailingslashit( $this->plugin_url );
		}

		/**
		 * Listen for a response from Eway on API URL and Process request.
		 *
		 * @return void
		 * @throws Exception If there is an error processing the request.
		 */
		public function response_listener() {
			if ( isset( $_GET['AccessCode'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$access_code = sanitize_text_field( wp_unslash( $_GET['AccessCode'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$result      = json_decode( $this->get_api()->get_access_code_result( $access_code ) );

				// phpcs:disable WordPress.PHP.DevelopmentFunctions
				self::log(
					'Response listener result: ' . print_r(
						$result,
						true
					)
				);
				// phpcs:enable WordPress.PHP.DevelopmentFunctions

				// Use InvoiceRef temp, until Eway sorts out empty options echo.
				$order = wc_get_order( intval( $result->InvoiceReference ) );

				if ( ! $order ) {
					self::log( 'Response listener order not found: ' . intval( $result->InvoiceReference ) );

					return;
				}

				try {
					$this->handle_eway_payment_response( $result, $order );
				} catch ( Exception $exception ) {
					$order->add_order_note(
						sprintf(
						// translators: %s Response message.
							__( 'Eway token payment failed - %s', 'wc-eway' ),
							$exception->getMessage()
						)
					);
				}

				WC()->cart->empty_cart();
				wp_safe_redirect( $this->get_return_url( $order ) );
				exit;
			}

			self::log( 'Response listener called without AccessCode' );
		}

		/**
		 * Show description and option to save cards, or pay with new cards on checkout.
		 *
		 * @since 3.7.0 Implement eway secure fields credit card form.
		 *
		 * @return void
		 */
		public function payment_fields() {
			if ( $this->description ) {
				echo '<p>' . wp_kses_post( $this->description ) . '</p>';
			}

			$eway_cards = WC_Payment_Tokens::get_customer_tokens( get_current_user_id(), $this->id );

			if ( is_user_logged_in() && is_checkout() && $this->saved_cards ) {
				wc_get_template(
					'eway-saved-card-list.php',
					array( 'eway_cards' => $eway_cards ),
					'eway/',
					plugin_dir_path( __FILE__ ) . '../templates/'
				);

				wp_nonce_field( 'eway_use_saved_card', '_eway_nonce' );
			}

			if ( $this->is_eway_secure_fields_enabled() ) {
				wc_get_template(
					'eway-secure-fields-cc-form.php',
					array(
						'eway_cards'  => $eway_cards,
						'saved_cards' => $this->saved_cards,
					),
					'eway/',
					plugin_dir_path( __FILE__ ) . '../templates/'
				);
			}
		}

		/**
		 * Return payment tokens for the current user.
		 * If needed, migrate legacy Eway payment tokens stored in user_meta to WooCommerce Payment Token API.
		 *
		 * @param WC_Payment_Token[] $tokens      Array of token objects.
		 * @param int                $customer_id Customer ID.
		 * @param string             $gateway_id  Gateway ID for getting tokens for a specific gateway.
		 *
		 * @return WC_Payment_Token[] Array of token objects.
		 */
		public function get_customer_payment_tokens( $tokens, $customer_id, $gateway_id ) {
			// Limit updates to current user and when the gateway is involved in the query (i.e. not specified or specifically Eway).
			if ( ( $gateway_id && $gateway_id !== $this->id ) || ( ! is_user_logged_in() || get_current_user_id() !== $customer_id ) ) {
				return $tokens;
			}

			// Get legacy payment tokens to migrate.
			$eway_cards = get_user_meta( $customer_id, '_eway_token_cards', true );
			if ( ! is_array( $eway_cards ) || empty( $eway_cards ) ) {
				return $tokens;
			}

			// Unhook early to prevent other code from triggering the migration routine again (can happen when saving tokens).
			remove_filter(
				'woocommerce_get_customer_payment_tokens',
				array( $this, 'get_customer_payment_tokens' ),
				10
			);

			// Get current tokens to avoid migrating duplicates.
			$token_ids = wc_list_pluck(
				array_filter(
					$tokens,
					function ( $token ) {
						return $token->get_gateway_id() === $this->id;
					}
				),
				'get_token'
			);

			foreach ( $eway_cards as $card ) {
				// Skip legacy tokens that already have corresponding WC_Payment_Token objects.
				if ( in_array( strval( $card['id'] ), $token_ids, true ) ) {
					continue;
				}

				$wc_token = new WC_Payment_Token_Eway_CC();
				$wc_token->set_gateway_id( $this->id );
				$wc_token->set_token( $card['id'] );
				$wc_token->set_user_id( $customer_id );
				$wc_token->set_number( $card['number'] );
				$wc_token->set_expiry_year( $card['exp_year'] );
				$wc_token->set_expiry_month( $card['exp_month'] );
				$wc_token->save();
			}

			delete_user_meta( $customer_id, '_eway_token_cards' );

			// Get customer tokens including newly migrated tokens.
			return WC_Payment_Tokens::get_customer_tokens( $customer_id, $gateway_id );
		}

		/**
		 * Process refunds for WC 2.2+
		 *
		 * @param int        $order_id The order ID.
		 * @param float|null $amount   The amount to refund. Default null.
		 * @param string     $reason   The reason for the refund. Default null.
		 *
		 * @return bool|WP_Error
		 */
		public function process_refund( $order_id, $amount = null, $reason = null ) {
			$order = wc_get_order( $order_id );

			if ( ! is_a( $order, 'WC_Order' ) ) {
				return new WP_Error( 'eway_refund_error', __( 'Order not valid', 'wc-eway' ) );
			}

			$transction_id = $order->get_meta( '_transaction_id', true );

			if ( ! $transction_id || empty( $transction_id ) ) {
				return new WP_Error( 'eway_refund_error', __( 'No valid Transaction ID found', 'wc-eway' ) );
			}

			if ( is_null( $amount ) || $amount <= 0 ) {
				return new WP_Error( 'eway_refund_error', __( 'Amount not valid', 'wc-eway' ) );
			}

			if ( is_null( $reason ) || '' === $reason ) {
				// translators: %s Order number.
				$reason = sprintf( __( 'Refund for Order %s', 'wc-eway' ), $order->get_order_number() );
			}

			try {
				$result = json_decode(
					$this->get_api()->direct_refund(
						$order,
						$transction_id,
						$amount * 100,
						$reason
					)
				);
				if ( in_array( $result->ResponseMessage, $this->success_response_messages, true ) ) {
					return true;
				} else {
					if ( isset( $result->Errors ) && ! is_null( $result->Errors ) ) {
						return new WP_Error( 'eway_refund_error', $this->response_message_lookup( $result->Errors ) );
					} else {
						return new WP_Error(
							'eway_refund_error',
							$this->response_message_lookup( $result->ResponseMessage )
						);
					}
				}
			} catch ( Exception $e ) {
				return new WP_Error( 'eway_refund_error', $e->getMessage() );
			}
		}

		/**
		 * Lookup Response / Error messages based on codes.
		 *
		 * @since 3.7.0 Use eway error code class to translate eway error codes.
		 *
		 * @param string $eway_error_codes Response code from API.
		 */
		public function response_message_lookup( string $eway_error_codes ): string {
			return WC_Gateway_Eway_Error_Codes::get_message( $eway_error_codes );
		}

		/**
		 * Save the token customer id on the order being made.
		 *
		 * @param WC_Order $order             The order being made.
		 * @param int      $token_customer_id The token customer id to associate with order.
		 */
		protected function set_token_customer_id( $order, $token_customer_id ) {
			$order->update_meta_data( '_eway_token_customer_id', $token_customer_id );
			$order->save_meta_data();
		}

		/**
		 * Verifies that no customer fields exceed the allowed lengths.
		 *
		 * @param array    $values The values of the submitted fields.
		 * @param WP_Error $errors Validation errors. This is a reference.
		 */
		public function validate_checkout_values( $values, $errors ) {
			if ( $this->id !== $values['payment_method'] ) {
				// No need to validate for other gateways.
				return;
			}

			// Load the fields that are used on the checkout page to make sure they exist and use their labels.
			$checkout_fields = WC()->countries->get_address_fields( $values['billing_country'], 'billing_' );

			$requirements = array(
				'billing_first_name' => 50,
				'billing_last_name'  => 50,
				'billing_company'    => 50,
				'billing_address_1'  => 50,
				'billing_address_2'  => 50,
				'billing_city'       => 50,
				'billing_state'      => 50,
				'billing_postcode'   => 30,
				'billing_country'    => 2,
				'billing_email'      => 50,
				'billing_phone'      => 32,
			);

			foreach ( $requirements as $name => $length ) {
				if ( ! isset( $checkout_fields[ $name ], $values[ $name ] ) ) {
					// The field is not available.
					continue;
				}

				$value = $values[ $name ];
				if ( empty( $value ) || strlen( $value ) <= $length ) {
					continue;
				}

				$errors->add(
					'validation',
					sprintf(
					/* translators: %1$s Checkout field label, %2$d Number of characters. */
						__(
							'The value of "%1$s" must not exceed the length of %2$d characters for payments with Eway.',
							'wc-eway'
						),
						$checkout_fields[ $name ]['label'],
						$length
					)
				);
			}
		}

		/**
		 * Get the settings keys required for setup during onboarding.
		 *
		 * @return array
		 */
		public function get_required_settings_keys() {
			return array(
				'customer_api',
				'customer_password',
			);
		}

		/**
		 * Get help text to display during onboarding setup.
		 *
		 * @return string
		 */
		public function get_setup_help_text() {
			return sprintf(
			/* translators: %s Eway URL. */
				__(
					'Your API details can be obtained from your <a href="%s" target="_blank">Eway account</a>.',
					'wc-eway'
				),
				'https://www.eway.com.au/'
			);
		}

		/**
		 * Determine if the gateway still requires setup.
		 *
		 * @return bool
		 */
		public function needs_setup() {
			return ! $this->get_option( 'customer_api' ) || ! $this->get_option( 'customer_password' );
		}

		/**
		 * This function should validate payment gateway related form fields.
		 *
		 * @since 3.7.0
		 */
		public function validate_fields(): void {
			if ( $this->is_eway_secure_fields_enabled() ) {
				if (
					// Customer should use credit card saved in Eway.
					empty( $_POST['secure-field-token'] ) && // phpcs:ignore WordPress.Security.NonceVerification
					// The card should be new.
					( empty( $_POST['eway_card_id'] ) || 'new' === $_POST['eway_card_id'] ) // phpcs:ignore WordPress.Security.NonceVerification
				) {
					wc_eway_add_notice( __( 'Missing Eway credit card data token ID.', 'wc-eway' ), 'error' );
				}
			}
		}

		/**
		 * This function should save payment method.
		 *
		 * @since 3.7.0
		 * @return array
		 * @throws Exception When there is an error adding the payment method.
		 */
		public function add_payment_method(): array {
			if ( ! $this->is_eway_secure_fields_enabled() ) {
				return array(
					'result'   => 'failure',
					'redirect' => wc_get_endpoint_url( 'payment-methods' ),
				);
			}

			try {
				if ( empty( $_POST['secure-field-token'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
					WC_Gateway_Eway_Error_Codes::add_notice(
						esc_html__(
							'Missing Eway credit card data token ID.',
							'wc-eway'
						)
					);

					return array(
						'result' => 'failure',
						'error'  => wc_get_account_endpoint_url( 'add-payment-method' ),
					);
				}

				$user_id                    = get_current_user_id();
				$wc_customer                = new \WC_Customer( $user_id );
				$secured_card_data_token_id = sanitize_text_field( wp_unslash( $_POST['secure-field-token'] ) ); // phpcs:ignore WordPress.Security.NonceVerification

				$result = $this->get_api()->add_new_credit_card( $wc_customer, $secured_card_data_token_id );

				$wc_eway_customer_token = $this->add_new_customer_token( $result->Customer, $user_id );

				// Eway validates credit card expiry only on frontend.
				// For this reason we need to validate it here.
				// Check if the card is expired and remove eway customer token if expired.
				$expiry_month = (int) $wc_eway_customer_token->get_expiry_month();
				$expiry_year  = (int) $wc_eway_customer_token->get_expiry_year();

				$current_month = (int) date( 'm' ); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
				$current_year  = (int) date( 'y' ); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date

				if ( $expiry_year < $current_year || ( $expiry_year === $current_year && $expiry_month < $current_month ) ) {
					$wc_eway_customer_token->delete();

					wc_eway_add_notice(
						esc_html__( 'The credit card has expired.', 'wc-eway' ),
						'error'
					);

					return array(
						'result' => 'failure',
						'error'  => wc_get_account_endpoint_url( 'add-payment-method' ),
					);
				}

				return array(
					'result'   => 'success',
					'redirect' => wc_get_account_endpoint_url( 'payment-methods' ),
				);
			} catch ( \Exception $exception ) {
				wc_eway_add_notice( $exception->getMessage(), 'error' );

				return array(
					'result' => 'failure',
					'error'  => wc_get_account_endpoint_url( 'add-payment-method' ),
				);
			}
		}

		/**
		 * This function should add new customer token.
		 *
		 * @since 3.7.0
		 *
		 * @param stdClass $eway_customer Eway customer object.
		 * @param int      $user_id       User ID.
		 */
		private function add_new_customer_token( stdClass $eway_customer, int $user_id ): \WC_Payment_Token_Eway_CC {
			$wc_token = new WC_Payment_Token_Eway_CC();

			$wc_token->set_gateway_id( $this->id );
			$wc_token->set_token( $eway_customer->TokenCustomerID );
			$wc_token->set_user_id( $user_id );
			$wc_token->set_number( $eway_customer->CardDetails->Number );
			$wc_token->set_expiry_year( $eway_customer->CardDetails->ExpiryYear );
			$wc_token->set_expiry_month( $eway_customer->CardDetails->ExpiryMonth );
			$wc_token->save();

			return $wc_token;
		}

		/**
		 * This function should return whether to save customer token for automatic future payment.
		 *
		 * Note: This function is used in WC_Payment_Gateway_CC::process_payment() function when process payment with secure fields.
		 *
		 * @param \WC_Order $order Order object.
		 *
		 * @return bool Whether to save customer token for automatic future payment.
		 */
		protected function can_save_eway_customer_token( \WC_Order $order ): bool {
			if ( ! $this->supports( 'tokenization' ) ) {
				return false;
			}

			if ( ! is_user_logged_in() ) {
				return false;
			}

			if ( ! $this->saved_cards ) {
				return false;
			}

			return true;
		}

		/**
		 * This function returns whether Eway secure fields enabled.
		 *
		 * @since 3.7.0
		 *
		 * @return bool
		 */
		private function is_eway_secure_fields_enabled(): bool {
			return ! empty( $this->get_option( 'public_api_key' ) ) && $this->get_option('connection_method') != self::RESPONSIVE_SHARED_PAGE;
		}

		/**
		 * This function should process payment with Eway transparent redirect api.
		 *
		 * Note: Primarily we use Eway secure fields to process payment,
		 * but if Eway secure fields is not enabled then we use transparent redirect api to maintain backward compatibility.
		 *
		 * @since 3.7.1
		 *
		 * @param \WC_Order $order Order object.
		 *
		 * @return array|void
		 * @throws Exception Thor exception, When there is an error processing the request.
		 */
		private function process_payment_with_trasparent_redirect( $order ) {
			if (
				is_user_logged_in()
				&& isset( $_POST['_eway_nonce'] )
				&& isset( $_POST['eway_card_id'] )
			) {
				if ( ! wp_verify_nonce( sanitize_key( $_POST['_eway_nonce'] ), 'eway_use_saved_card' ) ) {
					self::log( $order->get_id() . ': Processing payment with token failed nonce check' );

					throw new Exception( __( 'Unable to process unauthorized action.', 'wc-eway' ) );
				}

				$token_id = sanitize_text_field( wp_unslash( $_POST['eway_card_id'] ) );

				if ( 'new' === $token_id ) {
					self::log( $order->get_id() . ': Processing payment with new token' );
					$this->set_token_customer_id( $order, 'new' );
				} else {
					try {
						$token = new WC_Payment_Token_Eway_CC( $token_id );

						if ( ! $token->get_id() || intval( $token->get_user_id() ) !== intval( $order->get_customer_id() ) ) {
							throw new Exception( __( 'The payment token is invalid', 'wc-eway' ) );
						}

						self::log( $order->get_id() . ': Processing payment with token' );

						$this->process_payment_request( $order, $order->get_total(), $token->get_token() );

						WC()->cart->empty_cart();

						self::log( $order->get_id() . ': Redirecting to thanks url' );

						// Return to thankyou page if successful.
						return array(
							'result'   => 'success',
							'redirect' => $this->get_return_url( $order ),
						);
					} catch ( Exception $e ) {
						self::log( $order->get_id() . ': Processing payment with token failed - ' . $e->getMessage() );

						wc_add_notice( $e->getMessage(), 'error' );

						return array(
							'result'   => 'failure',
							'redirect' => $order->get_checkout_payment_url( true ),
							'message'  => $e->getMessage(),
						);
					}
				}
			} elseif (
				$this->saved_cards &&
				is_user_logged_in() &&
				! empty( $_POST['createaccount'] )
			) {
				// Handle saved cards for newly created user during checkout.
				// If Eway saved cards is enabled and User account is created during checkout and user is logged-in.
				self::log( $order->get_id() . ': Processing payment with new token for newly created account' );
				$this->set_token_customer_id( $order, 'new' );
			}

			self::log( $order->get_id() . ': Redirecting to payment URL' );

			// Redirect to pay/receipt page to follow normal credit card payment.
			return array(
				'result'   => 'success',
				'redirect' => $order->get_checkout_payment_url( true ),
			);
		}
	}
}
