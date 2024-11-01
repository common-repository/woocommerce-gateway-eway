<?php
/**
 * This file contains utility functions.
 *
 * @since x.x.x
 * @package WooCommerce Eway Payment Gateway
 */

/**
 * This function should return assets data.
 *
 * @since x.x.x
 *
 * @param string $asset_path Path to asset file.
 */
function wc_eway_get_asset_data( string $asset_path ): array {
	$asset_path   = trailingslashit( WOOCOMMERCE_GATEWAY_EWAY_PATH ) . str_replace( '.js', '.asset.php', $asset_path );
	$version      = WOOCOMMERCE_GATEWAY_EWAY_VERSION;
	$dependencies = array();
	$data         = array(
		'version'      => $version,
		'dependencies' => $dependencies,
	);

	if ( file_exists( $asset_path ) ) {
		$asset                = require $asset_path;
		$data['version']      = is_array( $asset ) && isset( $asset['version'] )
			? $asset['version']
			: $version;
		$data['dependencies'] = is_array( $asset ) && isset( $asset['dependencies'] )
			? $asset['dependencies']
			: $dependencies;
	}

	return $data;
}

/**
 * This function should register an error to WooCommerce.
 *
 * @since x.x.x
 *
 * @param string $notice Error message.
 * @param string $notice_type Error type.
 */
function wc_eway_add_notice( string $notice, string $notice_type = 'success' ): void {
	wc_add_notice(
		sprintf(
			/* translators: %1$s: Eway error message */
			esc_html__( 'Eway Error: %1$s', 'wc-eway' ),
			$notice
		),
		$notice_type
	);
}

/**
 * This function should return the gateway class.
 *
 * @since x.x.x
 */
function wc_eway_get_gateway_class(): WC_Gateway_EWAY {
	$registered_gateways = WC()->payment_gateways()->payment_gateways();

	return $registered_gateways['eway'];
}
