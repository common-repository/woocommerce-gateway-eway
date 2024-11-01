<?php
/**
 * Eway payment token functions.
 *
 * @package WooCommerce Eway Payment Gateway
 */

add_filter( 'woocommerce_payment_methods_list_item', 'wc_eway_get_saved_payment_methods_list', 10, 2 );

/**
 * On the My Account page, show number and expiration month for Eway cards.
 *
 * @param array            $item Individual list item from woocommerce_saved_payment_methods_list.
 * @param WC_Payment_Token $payment_token The payment token associated with this method entry.
 * @return array Filtered item.
 */
function wc_eway_get_saved_payment_methods_list( $item, $payment_token ) {
	if ( 'eway_cc' === strtolower( $payment_token->get_type() ) ) {
		$item['method']['last4'] = substr( $payment_token->get_number(), -4 );
		$item['method']['brand'] = __( 'card', 'wc-eway' );
		$item['expires']         = $payment_token->get_expiry_month() . '/' . $payment_token->get_expiry_year();
	}
	return $item;
}
