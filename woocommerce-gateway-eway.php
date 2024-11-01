<?php
/**
 * Plugin Name: WooCommerce Eway Payment Gateway
 * Description: WooCommerce Eway Rapid 3.1 payment gateway integration supporting AU, NZ, MY, and HK. Support for WooCommerce qSubscriptions.
 * Plugin URI: https://woocommerce.com/products/eway/
 * Author: Eway
 * Author URI: https://eway.com.au
 * Version: 3.8.0
 * Text Domain: wc-eway
 * Domain Path: /languages
 * Requires at least: 6.0
 * Tested up to: 6.2
 * WC tested up to: 7.9
 * WC requires at least: 7.7
 * Requires PHP: 7.3
 * Copyright: © 2023 Web Active Corporation 
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package WooCommerce Eway Payment Gateway
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'WOOCOMMERCE_GATEWAY_EWAY_VERSION', '3.8.0' ); // WRCS: DEFINED_VERSION.
define( 'WOOCOMMERCE_GATEWAY_EWAY_MIN_WC_VERSION', '6.0' );
define( 'WOOCOMMERCE_GATEWAY_EWAY_MIN_WCS_VERSION', '2.0' );
define( 'WOOCOMMERCE_GATEWAY_EWAY_URL', untrailingslashit( plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) ) );
define( 'WOOCOMMERCE_GATEWAY_EWAY_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) );

add_action( 'plugins_loaded', 'woocommerce_eway_init', 0 );

/**
 * Initialize the extension.
 */
function woocommerce_eway_init() {
	if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
		return;
	}

	load_plugin_textdomain( 'wc-eway', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

	define( 'WC_EWAY_TEMPLATE_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) . '/templates/' );

	// Show notice for outdated version of WooCommerce.
	if ( ! class_exists( 'WooCommerce' ) || version_compare( WC()->version, WOOCOMMERCE_GATEWAY_EWAY_MIN_WC_VERSION, '<' ) ) {
		add_action( 'admin_notices', 'woocommerce_eway_outdated_wc_notice' );
		return;
	}

	require_once 'includes/utils.php';
	require_once 'includes/class-wc-gateway-eway-error-codes.php';
	require_once 'includes/class-wc-gateway-eway.php';
	require_once 'includes/class-wc-gateway-eway-ajax-request-controller.php';
	require_once 'includes/class-wc-gateway-eway-privacy.php';
	require_once 'includes/class-wc-payment-token-eway-cc.php';
	require_once 'includes/wc-gateway-eway-payment-token-functions.php';

	// Load subscriptions class if active and meets version requirement.
	if ( class_exists( 'WC_Subscriptions_Order' ) ) {
		if ( version_compare( get_option( 'woocommerce_subscriptions_active_version' ), WOOCOMMERCE_GATEWAY_EWAY_MIN_WCS_VERSION, '<' ) ) {
			add_action( 'admin_notices', 'woocommerce_eway_outdated_wcs_notice' );
		} else {
			require_once 'includes/class-wc-gateway-eway-subscriptions.php';
		}
	}

	add_action( 'woocommerce_blocks_loaded', 'woocommerce_eway_woocommerce_blocks_support' );

	// Add classes to WC Payment Methods.
	add_filter( 'woocommerce_payment_gateways', 'woocommerce_eway_add_gateway' );

	// Add links next to plugin 'Activate' button.
	add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'woocommerce_eway_plugin_action_links' );
}

/**
 * Add Eway gateway classes to WooCommerce.
 *
 * @param array $available_gateways Payment gateways.
 * @return array Payment gateways.
 */
function woocommerce_eway_add_gateway( $available_gateways ) {
	if ( class_exists( 'WC_Subscriptions_Order' ) && class_exists( 'WC_Gateway_EWAY_Subscriptions' ) ) {
		$available_gateways[] = 'WC_Gateway_EWAY_Subscriptions';
	} else {
		$available_gateways[] = 'WC_Gateway_EWAY';
	}
	return $available_gateways;
}

/**
 * Adds plugin action links.
 *
 * @since 3.1.18
 *
 * @param array $links Plugin action links.
 * @return array Plugin action links.
 */
function woocommerce_eway_plugin_action_links( $links ) {
	$setting_link = admin_url( 'admin.php?page=wc-settings&tab=checkout&section=eway' );

	$plugin_links = array(
		'<a href="' . $setting_link . '">' . __( 'Settings', 'wc-eway' ) . '</a>',
	);

	return array_merge( $plugin_links, $links );
}

/**
 * Support Cart and Checkout blocks from WooCommerce Blocks.
 */
function woocommerce_eway_woocommerce_blocks_support() {
	if ( class_exists( 'Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType' ) ) {
		require_once dirname( __FILE__ ) . '/includes/class-wc-gateway-eway-blocks-support.php';
		add_action(
			'woocommerce_blocks_payment_method_type_registration',
			function( Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry $payment_method_registry ) {
				$payment_method_registry->register( new WC_Gateway_EWAY_Blocks_Support() );
			}
		);
	}
}

/**
 * Show notice for outdated version of WooCommerce.
 */
function woocommerce_eway_outdated_wc_notice() {
	echo '<div class="notice notice-error"><p>';
	// translators: %s Minimum WooCommerce version.
	echo esc_html( sprintf( __( 'This version of WooCommerce Eway Payment Gateway requires WooCommerce %s or newer.', 'wc-eway' ), WOOCOMMERCE_GATEWAY_EWAY_MIN_WC_VERSION ) );
	echo '</p></div>';
}

/**
 * Show notice for outdated version of WooCommerce Subscriptions.
 */
function woocommerce_eway_outdated_wcs_notice() {
	echo '<div class="notice notice-error"><p>';
	// translators: %s Minimum WooCommerce Subscriptions version.
	echo esc_html( sprintf( __( 'This version of WooCommerce Eway Payment Gateway requires WooCommerce Subscriptions %s or newer.', 'wc-eway' ), WOOCOMMERCE_GATEWAY_EWAY_MIN_WCS_VERSION ) );
	echo '</p></div>';
}


add_action( 'before_woocommerce_init', 'eway_declare_hpos_compatibility' );

/**
 * Declares HPOS compatibility
 *
 * @since 3.4.6
 */
function eway_declare_hpos_compatibility() {
	if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
}

require_once trailingslashit( __DIR__ ) . 'vendor/autoload.php';
require_once trailingslashit( __DIR__ ) . 'vendor/vendor-prefixed/autoload.php';
