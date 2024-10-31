<?php
/**
 * Template to display signature pad in the checkout.
 *
 * @package SWPH_Order_Signature
 */

// Disable PHPCS for unfiltered output errors which are not based.
// phpcs:disable
?>
<div id="swph_order_custom_fields">
	<h3><?php echo get_option( 'wc_settings_tab_signature_custom_field_title', esc_html__( 'Terms and Conditions', 'order-signature-for-woocommerce' ) ); ?></h3>
	<p><?php echo wp_specialchars_decode( get_option( 'wc_settings_tab_signature_custom_field_text', esc_html__( 'By signing below you agree with the terms and conditions.', 'order-signature-for-woocommerce' ) ) ); ?></p>
</div>

<div id="swph-woo-sign-signature-pad-wrapper">
	<h3><?php echo esc_html__( 'Your Signature', 'order-signature-for-woocommerce' ); ?></h3>
	<div id="swph-woo-sign-signature-pad"></div>
		<a href="javascript: void(0)" id="swph-woo-sign-svgButton" class="swph_woo_sign_button button btn"><?php echo esc_html__( get_option( 'wc_settings_tab_signature_done_signing', __( 'Done Signing', 'order-signature-for-woocommerce' ) ) ); ?></a>
		<a href="javascript: void(0)" id="swph-woo-sign-clearButton" class="swph_woo_sign_button button btn"><?php echo esc_html__( get_option( 'wc_settings_tab_signature_clear_sign', __( 'Clear Signature', 'order-signature-for-woocommerce' ) ) ); ?></a>
	<?php if ( ! empty( $signature_data['data'] ) ) : ?>
		<a href="javascript: void(0)" id="swph-woo-sign-redrawButton" onclick="redrawImg();" class="swph_woo_sign_button button btn"><?php echo esc_html__( 'Redraw Signature', 'order-signature-for-woocommerce' ); ?></a>
	<?php endif; ?>
</div>
