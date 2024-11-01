<?php
/**
 * This template use to render saved eway credit cards on checkout page.
 *
 * @package WooCommerce Eway Payment Gateway
 * @since x.x.x
 */

/* @var WC_Payment_Token[] $eway_cards Array of saved cards. */
?>
<p class="form-row form-row-wide">
	<?php
	// Find the default card if any.
	$has_default_card = $eway_cards ?
		array_filter(
			$eway_cards,
			static function ( $eway_card ) {
				return $eway_card->is_default();
			}
		) :
		false;
	?>
	<?php if ( $eway_cards ) : ?>
		<?php foreach ( $eway_cards as $index => $card ) : ?>
			<label for="eway_card_<?php echo esc_attr( $index ); ?>">
				<input type="radio"
					id="eway_card_<?php echo esc_attr( $index ); ?>"
					name="eway_card_id"
					value="<?php echo esc_attr( $card->get_id() ); ?>" <?php checked( $card->is_default() ); ?> />
				<?php printf( esc_html( $card->get_display_name() ) ); ?>
			</label>
		<?php endforeach; ?>
	<?php endif; ?>
	<label for="new">
		<input type="radio"
			id="new"
			name="eway_card_id"
			<?php checked( ! $has_default_card ); ?>
			value="new"/>
		<?php esc_html_e( 'Use a new credit card', 'wc-eway' ); ?>
	</label>
</p>
