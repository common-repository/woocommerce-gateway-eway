<?php
/**
 * Eway API.
 *
 * @package WooCommerce Eway Payment Gateway
 * @since   3.4.0
 */

use Automattic\WooCommerce\Eway\Vendors\Eway\Rapid as EwaySdk;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WC_EWAY_API' ) ) {
	/**
	 * WC_EWAY_API class.
	 */
	class WC_EWAY_API {

		/**
		 * API endpoint.
		 *
		 * @var string
		 */
		const PRODUCTION_ENDPOINT = 'https://api.ewaypayments.com';

		/**
		 * Test API endpoint.
		 *
		 * @var string
		 */
		const TEST_ENDPOINT = 'https://api.sandbox.ewaypayments.com';

		/**
		 * Device ID.
		 *
		 * @var string
		 */
		private $device_id = '0b38ae7c3c5b466f8b234a8955f62bdd';

		/**
		 * Partner ID.
		 *
		 * @var string
		 */
		private $partner_id = '0b38ae7c3c5b466f8b234a8955f62bdd';

		/**
		 * API endpoint to use (production or test).
		 *
		 * @var string
		 */
		public $endpoint;

		/**
		 * Eway API key.
		 *
		 * @var string
		 */
		public $api_key;

		/**
		 * Eway API password.
		 *
		 * @var string
		 */
		public $api_password;

		/**
		 * Whether debug mode is enabled ('on' or 'off').
		 *
		 * @var string
		 */
		public $debug_mode;

		/**
		 * Eway API client.
		 *
		 * @since 3.7.0
		 * @var EwaySdk\Client
		 */
		private $client;

		/**
		 * Constructor
		 *
		 * @param string $api_key      Eway API key.
		 * @param string $api_password Eway API password.
		 * @param string $environment  Whether API is running in production or test environment.
		 * @param string $debug_mode   Whether debug mode is enabled ('on' or 'off').
		 */
		public function __construct( $api_key, $api_password, $environment, $debug_mode ) {
			$this->api_key      = $api_key;
			$this->api_password = $api_password;
			$this->endpoint     = ( 'production' === $environment ) ?
				EwaySdk\Contract\Client::ENDPOINT_PRODUCTION :
				EwaySdk\Contract\Client::ENDPOINT_SANDBOX;
			$this->debug_mode   = $debug_mode;

			// Setup Client.
			$this->client = EwaySdk::createClient(
				$this->api_key,
				$this->api_password,
				'production' === $environment ?
					EwaySdk\Contract\Client::MODE_PRODUCTION :
					EwaySdk\Contract\Client::MODE_SANDBOX
			);
		}

		/**
		 * Perform a request to Eway API.
		 *
		 * @param string $endpoint API endpoint.
		 * @param string $json     Request body.
		 *
		 * @throws Exception If an error occurs during API request.
		 */
		private function perform_request( $endpoint, $json ) {
			$args = array(
				/**
				 * Filters the arguments used to set request timeout for Eway API request.
				 *
				 * @since 3.4.0
				 */
				'timeout'     => apply_filters( 'wc_eway_api_timeout', 45 ), // Default to 45 seconds.
				'redirection' => 0,
				'httpversion' => '1.0',
				'sslverify'   => false,
				'blocking'    => true,
				'headers'     => array(
					'accept'        => 'application/json',
					'content-type'  => 'application/json',
					// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions
					'authorization' => 'Basic ' . base64_encode( $this->api_key . ':' . $this->api_password ),
				),
				'body'        => $json,
				'cookies'     => array(),
				'user-agent'  => 'PHP ' . PHP_VERSION . '/WooCommerce ' . get_option( 'woocommerce_db_version' ),
			);

			$this->debug_message( json_decode( $json ) );

			$response = wp_remote_post( $this->endpoint . $endpoint, $args );

			$this->debug_message( $response );

			if ( is_wp_error( $response ) ) {
				throw new Exception( $response->get_error_message() );
			}

			if ( 200 !== $response['response']['code'] ) {
				throw new Exception( $response['response']['message'] );
			}

			return $response['body'];
		}

		/**
		 * Perform an HTTP GET request to Eway API.
		 *
		 * @param string $endpoint API endpoint.
		 *
		 * @throws Exception If an error occurs during API request.
		 */
		private function perform_get_request( $endpoint ) {
			$args = array(
				/**
				 * Filters the arguments used to set request timeout for Eway API request.
				 *
				 * @since 3.4.0
				 */
				'timeout'     => apply_filters( 'wc_eway_api_timeout', 45 ), // Default to 45 seconds.
				'redirection' => 0,
				'httpversion' => '1.0',
				'sslverify'   => false,
				'blocking'    => true,
				'headers'     => array(
					// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions
					'authorization' => 'Basic ' . base64_encode( $this->api_key . ':' . $this->api_password ),
				),
				'cookies'     => array(),
				'user-agent'  => 'PHP ' . PHP_VERSION . '/WooCommerce ' . get_option( 'woocommerce_db_version' ),
			);

			$response = wp_remote_get( $this->endpoint . $endpoint, $args );

			$this->debug_message( $response );

			if ( is_wp_error( $response ) ) {
				throw new Exception( $response->get_error_message() );
			}

			if ( 200 !== $response['response']['code'] ) {
				throw new Exception( $response['response']['message'] );
			}

			return $response['body'];
		}

		/**
		 * Request an access code for use in an Eway Transparent Redirect payment
		 * See: https://eway.io/api-v3/#transparent-redirect
		 *
		 * @param WC_Order $order       The order to access.
		 * @param string   $method      The "Method" parameter, see: https://eway.io/api-v3/#payment-methods.
		 * @param string   $trx_type    The "TransactionType" parameter, see: https://eway.io/api-v3/#transaction-types.
		 * @param mixed    $order_total The amount to charge for this transaction.
		 *
		 * @return mixed     JSON response from /CreateAccessCode.json on success
		 * @throws Exception Thrown on failure.
		 */
		public function request_access_code(
			$order,
			$method = 'ProcessPayment',
			$trx_type = 'Purchase',
			$order_total = null
		) {
			$order_id  = $order->get_id();
			$order_key = $order->get_order_key();

			$customer_ip = $order->get_customer_ip_address();

			// If an order total isn't provided (in the case of a subscription), grab it from the Order itself.
			if ( is_null( $order_total ) ) {
				$order_total = $order->get_total() * 100.00;
			}

			// Set up request object.
			$request = array(
				'Method'          => $method,
				'TransactionType' => $trx_type,
				'RedirectUrl'     => add_query_arg(
					array(
						'wc-api'    => 'WC_Gateway_EWAY',
						'order_id'  => $order_id,
						'order_key' => $order_key,
						'sig_key'   => md5( $order_key . 'WOO' . $order_id ),
					),
					home_url( '/' )
				),
				'CustomerIP'      => $customer_ip,
				'DeviceID'        => $this->device_id,
				'PartnerID'       => $this->partner_id,
				'Payment'         => array(
					'TotalAmount'        => $order_total,
					'CurrencyCode'       => $order->get_currency(),
					/**
					 * Filters the arguments used to set invoice description.
					 *
					 * @since 3.1.6
					 */
					'InvoiceDescription' => apply_filters( 'woocommerce_eway_description', '', $order ),
					'InvoiceNumber'      => ltrim(
						$order->get_order_number(),
						_x( '#', 'hash before order number', 'woocommerce' )
					),
					'InvoiceReference'   => $this->get_invoice_reference( $order ),
				),
				'Customer'        => array(
					'FirstName'   => $order->get_billing_first_name(),
					'LastName'    => $order->get_billing_last_name(),
					'CompanyName' => $order->get_billing_company(),
					'Street1'     => $order->get_billing_address_1(),
					'Street2'     => $order->get_billing_address_2(),
					'City'        => $order->get_billing_city(),
					'State'       => $order->get_billing_state(),
					'PostalCode'  => $order->get_billing_postcode(),
					'Country'     => strtolower( $order->get_billing_country() ),
					'Email'       => $order->get_billing_email(),
					'Phone'       => $order->get_billing_phone(),
				),
			);

			// Add customer ID if logged in.
			if ( is_user_logged_in() ) {
				$request['Options'][] = array( 'customerID' => get_current_user_id() );
			}

			return $this->perform_request( '/CreateAccessCode.json', wp_json_encode( $request ) );
		}

		/**
		 * Perform API call to get access code result.
		 *
		 * @param string $access_code Access code.
		 *
		 * @return string
		 */
		public function get_access_code_result( $access_code ) {
			$request = array(
				'AccessCode' => $access_code,
			);

			return $this->perform_request( '/GetAccessCodeResult.json', wp_json_encode( $request ) );
		}

		/**
		 * Perform API call to make a direct payment for an order.
		 *
		 * @param WC_Order   $order             The order associated with the payment.
		 * @param int|string $token_customer_id The customer's token customer id to include in direct payment request.
		 * @param int        $amount            The amount to charge for the order.
		 *
		 * @throws Exception If an error occurs during API request.
		 */
		public function direct_payment( $order, $token_customer_id, $amount = 0 ) {
			$order_id    = $order->get_id();
			$order_key   = $order->get_order_key();
			$amount      = intval( $amount );
			$customer_ip = $order->get_customer_ip_address();

			// Check for 0 value order.
			if ( 0 === $amount ) {
				$return_object = array(
					'Payment'           => array(
						'InvoiceReference' => $this->get_invoice_reference( $order ),
					),
					'ResponseMessage'   => 'A2000',
					'TransactionID'     => '',
					'TokenCustomerID'   => $token_customer_id,
					'TransactionStatus' => true,
				);

				return wp_json_encode( $return_object );
			}
			$request = array(
				'DeviceID'        => $this->device_id,
				'PartnerID'       => $this->partner_id,
				'TransactionType' => 'Recurring',
				'Method'          => 'TokenPayment',
				'CustomerIP'      => $customer_ip,
				'Customer'        => array(
					'TokenCustomerID' => $token_customer_id,
				),
				'Payment'         => array(
					'TotalAmount'        => $amount,
					'CurrencyCode'       => $order->get_currency(),
					/**
					 * Filters the arguments used to set invoice description.
					 *
					 * @since 3.1.6
					 */
					'InvoiceDescription' => apply_filters( 'woocommerce_eway_description', '', $order ),
					'InvoiceNumber'      => ltrim(
						$order->get_order_number(),
						_x( '#', 'hash before order number', 'woocommerce' )
					),
					'InvoiceReference'   => $this->get_invoice_reference( $order ),
				),
				'Options'         => array(
					array( 'OrderID' => $order_id ),
					array( 'OrderKey' => $order_key ),
					array( 'SigKey' => md5( $order_key . 'WOO' . $order_id ) ),
				),
			);

			return $this->perform_request( '/DirectPayment.json', wp_json_encode( $request ) );
		}

		/**
		 * Perform API call to refund an order for some amount.
		 *
		 * @param WC_Order   $order          The order to access.
		 * @param string     $transaction_id The transaction to refund.
		 * @param float|null $amount         The refund amount.
		 * @param string     $reason         The reason for refund.
		 */
		public function direct_refund( $order, $transaction_id, $amount = 0, $reason = '' ) {
			$request = array(
				'DeviceID'  => $this->device_id,
				'PartnerID' => $this->partner_id,
				'Refund'    => array(
					'TotalAmount'        => $amount,
					'TransactionID'      => $transaction_id,
					'InvoiceNumber'      => ltrim(
						$order->get_order_number(),
						_x( '#', 'hash before order number', 'woocommerce' )
					),
					'InvoiceReference'   => $this->get_invoice_reference( $order ),
					'InvoiceDescription' => $reason,
				),
			);

			return $this->perform_request( '/DirectRefund.json', wp_json_encode( $request ) );
		}

		/**
		 * Log a debugging message.
		 *
		 * @param array|object|string $message The message to log.
		 */
		public function debug_message( $message ) {
			if ( 'on' === $this->debug_mode ) {
				// phpcs:disable WordPress.PHP.DevelopmentFunctions
				WC_Gateway_EWAY::log(
					is_array( $message ) || is_object( $message ) ? print_r(
						$message,
						true
					) : $message
				);
				// phpcs:enable WordPress.PHP.DevelopmentFunctions
			}
		}

		/**
		 * Perform API call to get a customer by their token customer id.
		 *
		 * @param string $token_customer_id The token customer id to look up.
		 *
		 * @throws Exception If the request fails.
		 */
		public function lookup_customer( $token_customer_id ) {
			return $this->perform_get_request( '/Customer/' . $token_customer_id );
		}

		/**
		 * Return Eway customer token details.
		 *
		 * @since 3.7.0
		 *
		 * @param string $token Customer token.
		 *
		 * @return stdClass Eway customer token details.
		 * @throws \InvalidArgumentException Throw exception if error.
		 */
		public function get_customer_token_detail( $token ) {
			$response = json_decode( $this->perform_get_request( "/Customer/$token", '' ) );

			// Check for errors.
			if ( property_exists( $response, 'Errors' ) && $response->Errors ) {
				/* @var \WC_Gateway_EWAY|\WC_Gateway_EWAY_Subscriptions $eway_gateway */ // phpcs:ignore
				$eway_gateway = wc()->payment_gateways()->payment_gateways()['eway'];

				$error_codes          = array_map( 'trim', explode( ',', $response->Errors ) );
				$decode_error_message = '';

				foreach ( $error_codes as $error_code ) {
					$decode_error_message .= $eway_gateway->response_message_lookup( $error_code ) . ' ';
				}

				throw new \InvalidArgumentException( $decode_error_message );
			}

			return $response->Customers[0];
		}

		/**
		 * Get an order's possibly modified reference number.
		 *
		 * @param \WC_Order $order The full order object.
		 */
		public function get_invoice_reference( $order ) {
			$order_id = $order->get_id();

			/**
			 * Allows the invoice reference to be modified.
			 *
			 * @since 3.1.25
			 *
			 * @param int      $order_id The ID of the order.
			 * @param WC_Order $order    The full order object.
			 *
			 * @return string
			 */
			return apply_filters( 'woocommerce_eway_payment_reference', $order_id, $order );
		}

		/**
		 * Perform API call to make a direct payment with secured card data token.
		 *
		 * @since 3.7.0
		 *
		 * @param \WC_Order   $order                        The order associated with the payment.
		 * @param array       $threeds_verification_results The 3DS verification results.
		 * @param string|null $secured_card_data_token The secured card data token. If null, the Eway customer token id will be retrieve from the order for transaction.
		 *
		 * @throws \RuntimeException Throws an exception if the request fails.
		 */
		public function direct_payment_with_secured_card_data_token(
			\WC_Order $order,
			array $threeds_verification_results,
			$secured_card_data_token
		): object {
			$order_id            = $order->get_id();
			$order_key           = $order->get_order_key();
			$customer_ip         = $order->get_customer_ip_address();
			$amount              = $order->get_total() * 100; // convert to cents.
			$eway_customer_token = $order->get_meta( '_eway_token_customer_id', true );

			// Check for 0 value order.
			if ( 0 === $amount ) {
				if ( ! $eway_customer_token ) {
					throw new RuntimeException( __( 'Eway error: Invalid Eway customer token id', 'wc-eway' ) );
				}

				$return_object = array(
					'Payment'           => array( 'InvoiceReference' => $this->get_invoice_reference( $order ) ),
					'ResponseMessage'   => 'A2000',
					'TransactionID'     => '',
					'TokenCustomerID'   => $eway_customer_token,
					'TransactionStatus' => true,
				);

				return json_decode( wp_json_encode( $return_object ) );
			}

			$transaction                    = new EwaySdk\Model\Transaction( array() );
			$transaction->DeviceID          = $this->device_id;
			$transaction->PartnerID         = $this->partner_id;
			$transaction->TransactionType   = EwaySdk\Enum\TransactionType::RECURRING;
			$transaction->Method            = EwaySdk\Enum\PaymentMethod::PROCESS_PAYMENT;
			$transaction->CustomerIP        = $customer_ip;
			$transaction->PaymentInstrument = array( 'PaymentType' => 'CreditCard' );
			$transaction->Options           = array(
				array( 'OrderID' => $order_id ),
				array( 'OrderKey' => $order_key ),
				array( 'SigKey' => md5( $order_key . 'WOO' . $order_id ) ),
			);

			if ( array_key_exists( 'ThreeDSecureAuth', $threeds_verification_results ) ) {
				$transaction->PaymentInstrument = array_merge(
					$transaction->PaymentInstrument,
					array( 'ThreeDSecureAuth' => $threeds_verification_results['ThreeDSecureAuth'] )
				);
			}

			// Payment.
			$transaction->Payment               = new EwaySdk\Model\Payment( array() );
			$transaction->Payment->CurrencyCode = $order->get_currency();
			$transaction->Payment->TotalAmount  = $amount;
			/**
			 * Filters the arguments used to set invoice description.
			 *
			 * @since 3.7.0
			 */
			$transaction->Payment->InvoiceDescription = apply_filters( 'woocommerce_eway_description', '', $order );
			$transaction->Payment->InvoiceReference   = $this->get_invoice_reference( $order );
			$transaction->Payment->InvoiceNumber      = ltrim(
				$order->get_order_number(),
				_x( '#', 'hash before order number', 'woocommerce' )
			);

			// Customer.
			$transaction->Customer = new EwaySdk\Model\Customer( array() );

			if ( $eway_customer_token ) {
				$transaction->Customer->TokenCustomerID = $eway_customer_token;
			} else {
				$transaction->Customer->FirstName  = $order->get_billing_first_name();
				$transaction->Customer->LastName   = $order->get_billing_last_name();
				$transaction->Customer->Email      = $order->get_billing_email();
				$transaction->Customer->Phone      = $order->get_billing_phone();
				$transaction->Customer->Street1    = $order->get_billing_address_1();
				$transaction->Customer->Street2    = $order->get_billing_address_2();
				$transaction->Customer->City       = $order->get_billing_city();
				$transaction->Customer->State      = $order->get_billing_state();
				$transaction->Customer->Country    = $order->get_billing_country();
				$transaction->Customer->PostalCode = $order->get_billing_postcode();

				// Secured Card Data if required if customer token is not present.
				$transaction->SecuredCardData = $secured_card_data_token;
			}

			$result = $this->client->createTransaction(
				EwaySdk\Enum\ApiMethod::DIRECT,
				$transaction
			);

			// TODO: We are not validating the response here. This should be done in the future, to maintain function response standard, when remove backward compatibility for "Transparent Redirect".
			return json_decode( $result->toJson() );
		}

		/**
		 * Should enroll secured card data token to 3D Secure.
		 *
		 * @since 3.7.1 Add support for Eway customer token
		 * @since 3.7.0
		 *
		 * @param int          $amount                  Amount.
		 * @param \WC_Customer $customer                Customer.
		 * @param string       $token                   Secured card data token or Eway customer token.
		 * @param bool         $use_eway_customer_token Falg to decide whether to use Secured card data token or Eway customer token.
		 *
		 * @return stdClass
		 * @throws Exception If an error occurs during API request.
		 */
		public function three_ds_enroll_secured_card_data_token(
			$amount,
			\WC_Customer $customer,
			$token,
			$use_eway_customer_token = false
		): stdClass {
			$transaction           = new EwaySdk\Model\Transaction( array() );
			$transaction->Payment  = new EwaySdk\Model\Payment( array() );
			$transaction->Customer = new EwaySdk\Model\Customer( array() );

			if ( $use_eway_customer_token ) {
				$transaction->Customer->TokenCustomerID = $token;
			} else {
				$transaction->SecuredCardData = $token;
			}

			$transaction->DeviceID  = $this->device_id;
			$transaction->PartnerID = $this->partner_id;

			$transaction->Payment->TotalAmount = $amount;

			$transaction->Customer->FirstName  = $customer->get_billing_first_name();
			$transaction->Customer->LastName   = $customer->get_billing_last_name();
			$transaction->Customer->Email      = $customer->get_billing_email();
			$transaction->Customer->Phone      = $customer->get_billing_phone();
			$transaction->Customer->Street1    = $customer->get_billing_address_1();
			$transaction->Customer->Street2    = $customer->get_billing_address_2();
			$transaction->Customer->City       = $customer->get_billing_city();
			$transaction->Customer->State      = $customer->get_billing_state();
			$transaction->Customer->Country    = $customer->get_billing_country();
			$transaction->Customer->PostalCode = $customer->get_billing_postcode();

			$result = $this->client->create3dsEnrolment( $transaction->toArray() );

			$this->validate_result( $result );

			return json_decode( $result->toJson() );
		}

		/**
		 * Should handle Eway 3D secure enrollment ajax request.
		 *
		 * @since 3.7.0
		 *
		 * @param string $access_code Access Code.
		 *
		 * @throws Exception If an error occurs during API request.
		 */
		public function get_3dsverify_results( string $access_code ): array {
			$transaction             = new EwaySdk\Model\Transaction( array() );
			$transaction->DeviceID   = $this->device_id;
			$transaction->PartnerID  = $this->partner_id;
			$transaction->AccessCode = $access_code;

			$result = $this->client->verify3dsEnrolment( $transaction->toArray() );

			$this->validate_result( $result );

			return json_decode( $result->toJson(), true );
		}

		/**
		 * This function should add new credit card to Eway and return transaction details.
		 *
		 * @since 3.7.0
		 *
		 * @param \WC_Customer $wc_customer                WC_Customer object.
		 * @param string       $secured_card_data_token_id Secured Card Data ID.
		 *
		 * @throws Exception If an error occurs during API request.
		 */
		public function add_new_credit_card( \WC_Customer $wc_customer, string $secured_card_data_token_id ): object {
			$eway_customer                  = new EwaySdk\Model\Customer( array() );
			$eway_customer->FirstName       = $wc_customer->get_billing_first_name();
			$eway_customer->LastName        = $wc_customer->get_billing_last_name();
			$eway_customer->Email           = $wc_customer->get_billing_email();
			$eway_customer->Street1         = $wc_customer->get_billing_address_1();
			$eway_customer->Street2         = $wc_customer->get_billing_address_2();
			$eway_customer->City            = $wc_customer->get_billing_city();
			$eway_customer->State           = $wc_customer->get_billing_state();
			$eway_customer->Country         = $wc_customer->get_billing_country();
			$eway_customer->PostalCode      = $wc_customer->get_billing_postcode();
			$eway_customer->Phone           = $wc_customer->get_billing_phone();
			$eway_customer->SecuredCardData = $secured_card_data_token_id;

			$result = $this->client->createCustomer( EwaySdk\Enum\ApiMethod::DIRECT, $eway_customer->toArray() );

			$this->validate_result( $result );

			return json_decode( $result->toJson() );
		}

		/**
		 * This function should validate Eway API response.
		 *
		 * @since 3.7.0
		 *
		 * @param EwaySdk\Model\Response\AbstractResponse $result Eway API response.
		 *
		 * @throws \Exception If an error occurs during API request.
		 */
		private function validate_result( EwaySdk\Model\Response\AbstractResponse $result ): void {
			if ( $result->Errors ) {
				$errors = WC_Gateway_Eway_Error_Codes::get_message( $result->Errors );

				throw new \Exception( $errors );
			}
		}

		public function get_access_code_share( $order, $orderItem, $method ) {
			$request = array(
				'Customer'        => [
					'FirstName'      => $order->get_billing_first_name(),
					'LastName'       => $order->get_billing_last_name(),
					'CompanyName'    => $order->get_billing_company(),
					'JobDescription' => 'Woocommerce',
					'Street1'        => $order->get_billing_address_1(),
					'Street2'        => $order->get_billing_address_2(),
					'City'           => $order->get_billing_city(),
					'State'          => $order->get_billing_state(),
					'PostalCode'     => $order->get_billing_postcode(),
					'Country'        => strtolower( $order->get_billing_country() ),
					'Phone'          => $order->get_billing_phone(),
					'Email'          => $order->get_billing_email(),
				],
				'ShippingAddress' => [
					'FirstName'      => $order->get_shipping_first_name(),
					'LastName'       => $order->get_shipping_last_name(),
					'Street1'        => $order->get_shipping_address_1(),
					'Street2'        => $order->get_shipping_address_2(),
					'City'           => $order->get_shipping_city(),
					'State'          => $order->get_shipping_state(),
					'Country'        => strtolower( $order->get_shipping_country() ),
					'PostalCode'     => $order->get_shipping_postcode(),
					'Phone'          => $order->get_shipping_phone(),
					'Email'          => $order->get_billing_email(),
				],
				'Items'           => $orderItem,
				'Payment'         => array(
					'TotalAmount'        => $order->get_total() * 100.00,
					'InvoiceNumber'      => ltrim( $order->get_order_number(), _x( '#', 'hash before order number', 'woocommerce' ) ),
					'InvoiceDescription' => apply_filters( 'woocommerce_eway_description', '', $order ),
					'InvoiceReference'   => apply_filters( 'woocommerce_eway_payment_reference', $order->get_id(), $order ),
					'CurrencyCode'       => $order->get_currency(),
				),
				'RedirectUrl'     => $order->get_checkout_order_received_url(),
				'CancelUrl'       => $order->get_checkout_payment_url(),
				'DeviceID'        => '0b38ae7c3c5b466f8b234a8955f62bdd',
				'PartnerID'       => '0012000000xzXlpAAE',
				'CustomerIP'      => $order->get_customer_ip_address(),
				'TransactionType' => 'Purchase',
				'Method'          => $method,
			);

			return $this->perform_request( '/CreateAccessCodeShared.json', wp_json_encode( $request ) );
		}
	}
}
