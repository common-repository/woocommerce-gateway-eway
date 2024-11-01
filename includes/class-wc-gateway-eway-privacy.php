<?php
/**
 * WC_Gateway_EWAY_Privacy class.
 *
 * @package WooCommerce Eway Payment Gateway
 */

if ( ! class_exists( 'WC_Abstract_Privacy' ) ) {
	return;
}

/**
 * WC_Gateway_EWAY_Privacy class.
 *
 * @extends WC_Abstract_Privacy
 */
class WC_Gateway_EWAY_Privacy extends WC_Abstract_Privacy {
	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct( __( 'Eway', 'wc-eway' ) );

		$this->add_exporter( 'woocommerce-gateway-eway-order-data', __( 'WooCommerce Eway Order Data', 'wc-eway' ), array( $this, 'order_data_exporter' ) );

		if ( function_exists( 'wcs_get_subscriptions' ) ) {
			$this->add_exporter( 'woocommerce-gateway-eway-subscriptions-data', __( 'WooCommerce Eway Subscriptions Data', 'wc-eway' ), array( $this, 'subscriptions_data_exporter' ) );
		}

		$this->add_eraser( 'woocommerce-gateway-eway-order-data', __( 'WooCommerce Eway Data', 'wc-eway' ), array( $this, 'order_data_eraser' ) );
	}

	/**
	 * Returns a list of orders that are using one of Eway's payment methods.
	 *
	 * @param string $email_address User's email address.
	 * @param int    $page Page of results to retrieve.
	 *
	 * @return array WP_Post
	 */
	protected function get_eway_orders( $email_address, $page ) {
		$user = get_user_by( 'email', $email_address ); // Check if user has an ID in the DB to load stored personal data.

		$order_query = array(
			'payment_method' => 'eway',
			'limit'          => 10,
			'page'           => $page,
		);

		if ( $user instanceof WP_User ) {
			$order_query['customer_id'] = (int) $user->ID;
		} else {
			$order_query['billing_email'] = $email_address;
		}

		return wc_get_orders( $order_query );
	}

	/**
	 * Gets the message of the privacy to display.
	 */
	public function get_privacy_message() {
		// translators: %s WooCommerce privacy documentation URL.
		return wpautop( sprintf( __( 'By using this extension, you may be storing personal data or sharing data with an external service. <a href="%s" target="_blank">Learn more about how this works, including what you may want to include in your privacy policy.</a>', 'wc-eway' ), 'https://docs.woocommerce.com/document/privacy-payments/#woocommerce-gateway-eway' ) );
	}

	/**
	 * Handle exporting data for Orders.
	 *
	 * @param string $email_address E-mail address to export.
	 * @param int    $page          Pagination of data.
	 *
	 * @return array
	 */
	public function order_data_exporter( $email_address, $page = 1 ) {
		$done           = false;
		$data_to_export = array();

		$orders = $this->get_eway_orders( $email_address, (int) $page );

		$done = true;

		if ( 0 < count( $orders ) ) {
			foreach ( $orders as $order ) {
				$data_to_export[] = array(
					'group_id'    => 'woocommerce_orders',
					'group_label' => __( 'Orders', 'wc-eway' ),
					'item_id'     => 'order-' . $order->get_id(),
					'data'        => array(
						array(
							'name'  => __( 'Eway token', 'wc-eway' ),
							'value' => $order->get_meta( '_eway_token_customer_id', true ),
						),
					),
				);
			}

			$done = 10 > count( $orders );
		}

		return array(
			'data' => $data_to_export,
			'done' => $done,
		);
	}

	/**
	 * Handle exporting data for Subscriptions.
	 *
	 * @param string $email_address E-mail address to export.
	 * @param int    $page          Pagination of data.
	 *
	 * @return array
	 */
	public function subscriptions_data_exporter( $email_address, $page = 1 ) {
		$done           = false;
		$page           = (int) $page;
		$data_to_export = array();

		$meta_query = array(
			'relation' => 'AND',
			array(
				'key'     => '_payment_method',
				'value'   => 'eway',
				'compare' => '=',
			),
			array(
				'key'     => '_billing_email',
				'value'   => $email_address,
				'compare' => '=',
			),
		);

		$subscription_query = array(
			'posts_per_page' => 10,
			'page'           => $page,
			'meta_query'     => $meta_query, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
		);

		/**
		 * Array of subscriptions, type of WC_Subscription extends WC_Order
		 *
		 * @var WC_Order[]
		 */
		$subscriptions = wcs_get_subscriptions( $subscription_query );

		$done = true;

		if ( 0 < count( $subscriptions ) ) {
			foreach ( $subscriptions as $subscription ) {
				$data_to_export[] = array(
					'group_id'    => 'woocommerce_subscriptions',
					'group_label' => __( 'Subscriptions', 'wc-eway' ),
					'item_id'     => 'subscription-' . $subscription->get_id(),
					'data'        => array(
						array(
							'name'  => __( 'Eway subscription token', 'wc-eway' ),
							'value' => $subscription->get_meta( '_eway_token_customer_id', true ),
						),
					),
				);
			}

			$done = 10 > count( $subscriptions );
		}

		return array(
			'data' => $data_to_export,
			'done' => $done,
		);
	}

	/**
	 * Finds and erases order data by email address.
	 *
	 * @since 3.4.0
	 * @param string $email_address The user email address.
	 * @param int    $page Page.
	 * @return array An array of personal data in name value pairs
	 */
	public function order_data_eraser( $email_address, $page ) {
		$orders = $this->get_eway_orders( $email_address, (int) $page );

		$items_removed  = false;
		$items_retained = false;
		$messages       = array();

		foreach ( (array) $orders as $order ) {
			$order = wc_get_order( $order->get_id() );

			list( $removed, $retained, $msgs ) = $this->maybe_handle_order( $order );
			$items_removed                    |= $removed;
			$items_retained                   |= $retained;
			$messages                          = array_merge( $messages, $msgs );

			list( $removed, $retained, $msgs ) = $this->maybe_handle_subscription( $order );
			$items_removed                    |= $removed;
			$items_retained                   |= $retained;
			$messages                          = array_merge( $messages, $msgs );
		}

		// Tell core if we have more orders to work on still.
		$done = count( $orders ) < 10;

		return array(
			'items_removed'  => $items_removed,
			'items_retained' => $items_retained,
			'messages'       => $messages,
			'done'           => $done,
		);
	}

	/**
	 * Handle eraser of data tied to Subscriptions.
	 *
	 * @param WC_Order $order The order from which to delete subscription data.
	 * @return array
	 */
	protected function maybe_handle_subscription( $order ) {
		if ( ! class_exists( 'WC_Subscriptions' ) ) {
			return array( false, false, array() );
		}

		if ( ! wcs_order_contains_subscription( $order ) ) {
			return array( false, false, array() );
		}

		/**
		 * Subscription of WC_Subscription type, extends WC_Order
		 *
		 * @var WC_Order
		 */
		$subscription = current( wcs_get_subscriptions_for_order( $order->get_id() ) );

		$eway_token_customer_id = $subscription->get_meta( '_eway_token_customer_id', true );

		if ( empty( $eway_token_customer_id ) ) {
			return array( false, false, array() );
		}

		if ( $subscription->has_status( apply_filters( 'wc_eway_privacy_eraser_subs_statuses', array( 'on-hold', 'active' ) ) ) ) {
			// translators: %d Order ID.
			return array( false, true, array( sprintf( __( 'Order ID %d contains an active Subscription' ), $order->get_id() ) ) );
		}

		/**
		 * Renewal Orders
		 *
		 * @var WC_Order[]
		 */
		$renewal_orders = WC_Subscriptions_Renewal_Order::get_renewal_orders( $order->get_id(), 'WC_Order' );

		foreach ( $renewal_orders as $renewal_order ) {
			$renewal_order->delete_meta_data( '_eway_token_customer_id' );
			$renewal_order->save_meta_data();
		}

		$subscription->delete_meta_data( '_eway_token_customer_id' );
		$subscription->save_meta_data();

		return array( true, false, array( __( 'WC Gateway Eway Subscriptions Data Erased.', 'wc-eway' ) ) );
	}

	/**
	 * Handle eraser of data tied to Orders
	 *
	 * @param WC_Order $order The order from which to delete data.
	 * @return array
	 */
	protected function maybe_handle_order( $order ) {
		$eway_token_customer_id = $order->get_meta( '_eway_token_customer_id', true );

		if ( empty( $eway_token_customer_id ) ) {
			return array( false, false, array() );
		}

		$order->delete_meta_data( '_eway_token_customer_id' );
		$order->save_meta_data();

		return array( true, false, array( __( 'WC Gateway Eway Order Data Erased.', 'wc-eway' ) ) );
	}
}

new WC_Gateway_EWAY_Privacy();
