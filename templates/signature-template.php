<?php
/**
 * Template to display signature of order.
 *
 * @package SWPH_Order_Signature
 */

?>
<section class="woocommerce-customer-signature">
	<div id="swph-woo-sign-admin-signature-pad">
		<?php if ( ! is_admin() ) : ?>
		<<?php echo esc_html( $title_tag ); ?> class="woocommerce-column__title"> 
			<?php echo esc_html( get_option( 'wc_settings_tab_signature_customer_signature_text', esc_html__( 'Customer Signature', 'order-signature-for-woocommerce' ) ) ); ?>
		</<?php echo esc_html( $title_tag ); ?>>
		<?php endif; ?>
		<p>
			<?php // phpcs:ignore ?>
			<img src="data:image/png;base64,<?php echo esc_html( $signature_image ); ?>" alt="image 1" width="<?php echo esc_html( $signature_width ); ?>" />
		</p>
	</div>
</section>
