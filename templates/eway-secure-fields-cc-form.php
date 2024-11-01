<?php
/**
 * This template use to render credit card form on checkout page when select eway payment gateway.
 *
 * @package WooCommerce Eway Payment Gateway
 * @since x.x.x
 */

/* @var bool $saved_cards Flag to check whether preserve customer eway tokens for future payment. */

/* @var WC_Payment_Token_Eway_CC[] $eway_cards Saved customer eway card data token. */
$has_default_card = $eway_cards && array_filter(
	$eway_cards,
	static function ( $eway_card ) {
			return $eway_card->is_default();
	}
);

// For add payment method page, we do not display saved cards.
if ( is_add_payment_method_page() ) {
	$saved_cards = false;
}
?>
<fieldset id="wc-eway-credit-card-fields" style="<?php echo $has_default_card && $saved_cards ? 'display:none' : ''; ?>">
	<input type="text" id="wc-eway-credit-card-field-placeholder" style="position:absolute;left:-9999px">
	<div class="form-row form-row-wide">
		<label for="eway-secure-field-card">
			<?php esc_html_e( 'Card Number', 'wc-eway' ); ?>&nbsp;<span class="required">*</span>
		</label>
		<div id="eway-secure-field-card" style="height: 50px"></div>
	</div>
	<div class="form-row form-row-wide">
		<label for="eway-secure-field-name">
			<?php esc_html_e( 'Name on card', 'wc-eway' ); ?>&nbsp;<span class="required">*</span>
		</label>
		<div id="eway-secure-field-name" style="height: 50px"></div>
	</div>
	<div class="form-row form-row-first">
		<label for="eway-secure-field-expiry">
			<?php esc_html_e( 'Card Expiry', 'wc-eway' ); ?>&nbsp;<span class="required">*</span>
		</label>
		<div id="eway-secure-field-expiry" style="height: 50px"></div>
	</div>
	<div class="form-row form-row-last">
		<label for="eway-secure-field-cvn">
			<?php esc_html_e( 'Card CVN', 'wc-eway' ); ?>&nbsp;<span class="required">*</span>
		</label>
		<div id="eway-secure-field-cvn" style="height: 50px"></div>
	</div>
	<input type="hidden" id="securefieldcode" name="secure-field-token" value=""/>
</fieldset>
