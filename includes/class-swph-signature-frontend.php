<?php
/**
 * Class responsable for implementing the frontend actions.
 *
 * @package SWPH_Order_Signature
 */

/**
 * Class responsable for implementing the frontend actions.
 */
class SWPH_Signature_Frontend {

	/**
	 * Class constructor.
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'wp_ajax_swph_sign_order_view', array( $this, 'sign_existing_order' ) );
		add_action( 'wp', array( $this, 'enqueue_signature' ) );
	}

	/**
	 * Enqueue frontend necessary scripts if on specific templates.
	 *
	 * @return void
	 */
	public function enqueue_styles() {
		if ( $this->should_enqueue_scripts() ) {
			wp_enqueue_style(
				'swph-woo-sign-front-end-styles-custom',
				SWPH_PLUGIN_URL . 'assets/css/swph-woo-sign-front-end-styles-custom.css',
				array(),
				filemtime( SWPH_PLUGIN_DIR . 'assets/css/swph-woo-sign-front-end-styles-custom.css' ),
				'all'
			);
		}
	}

	/**
	 * Enqueue frontend javascript scripts.
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		if ( $this->should_enqueue_scripts() ) {

			wp_enqueue_script(
				'swph-jsignature',
				SWPH_PLUGIN_URL . 'assets/js/jSignature.min.noconflict.js',
				array( 'jquery' ),
				filemtime( SWPH_PLUGIN_DIR . 'assets/js/jSignature.min.noconflict.js' ),
				true
			);

			wp_register_script(
				'swph-woo-sign-front-end-scripts-custom',
				SWPH_PLUGIN_URL . 'assets/js/swph-woo-sign-front-end-scripts-custom.js',
				array( 'jquery' ),
				filemtime( SWPH_PLUGIN_DIR . 'assets/js/swph-woo-sign-front-end-scripts-custom.js' ),
				true
			);

			$script_variables = array(
				'done_signing'   => get_option( 'wc_settings_tab_signature_done_signing', esc_html__( 'Done Signing', 'order-signature-for-woocommerce' ) ),
				'clear_sign'     => get_option( 'wc_settings_tab_signature_clear_sign', esc_html__( 'Clear Signature', 'order-signature-for-woocommerce' ) ),
				'sign_color'     => get_option( 'wc_settings_tab_signature_sign_color' ),
				'sign_stroke'    => get_option( 'wc_settings_tab_signature_sign_stroke', '5' ),
				'sign_error_msg' => get_option( 'wc_settings_tab_signature_error_msg', esc_html__( 'Please draw at least a duck :) Sorry for the inconvenience, but we need to be compliant with tax and documents regulations here as well. Thank you for understanding!', 'order-signature-for-woocommerce' ) ),
			);

			wp_localize_script(
				'swph-woo-sign-front-end-scripts-custom',
				'swph_woo_sign_texts',
				$script_variables
			);

			wp_localize_script(
				'swph-woo-sign-front-end-scripts-custom',
				'swph_ajax_object',
				array(
					'ajax_url' => admin_url( 'admin-ajax.php' ),
				)
			);

			wp_enqueue_script( 'swph-woo-sign-front-end-scripts-custom' );
		}
	}

	/**
	 * Enqueue signature function to different hooks.
	 *
	 * @return void
	 */
	public function enqueue_signature() {
		$this->enqueue_signature_pad_hooks();
	}

	/**
	 * Enqueue signature pad with the necessary information.
	 *
	 * @return void
	 */
	public function enqueue_signature_pad_hooks() {

		$signature_data = $this->get_signature_data_for_endpoint();

		add_filter( 'woocommerce_form_field_swph_hidden', array( $this, 'render_hidden_input' ), 10, 4 );

		add_action(
			'woocommerce_checkout_before_customer_details',
			function() {
				$this->render_signature_inputs();
			}
		);

		add_action(
			'woocommerce_pay_order_before_submit',
			function() {
				$this->render_signature_inputs();
			}
		);

		add_action( 'woocommerce_after_checkout_validation', array( $this, 'validate_signature_on_checkout' ), 10, 2 );

		add_action( 'woocommerce_before_pay_action', array( $this, 'validate_signature_on_order_pay' ), 10, 1 );

		add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'update_order_and_user_meta' ) );

		add_action(
			'woocommerce_thankyou',
			function( $order ) {
				SWPH_Signature_Helper::display_order_signature( $order );
			}
		);

		add_action(
			'woocommerce_view_order',
			function( $order ) {
				SWPH_Signature_Helper::display_order_signature( $order );
			}
		);

		add_action(
			'woocommerce_pay_order_before_submit',
			function() {
				include SWPH_PLUGIN_DIR . 'templates/signature-pad-template.php';
			}
		);

		add_action(
			'woocommerce_after_checkout_billing_form',
			function() {
				include SWPH_PLUGIN_DIR . 'templates/signature-pad-template.php';
			}
		);
	}

	/**
	 * Print custom hidden input.
	 *
	 * @param string $field Field.
	 * @param string $key Key of field.
	 * @param array  $args Arguments passed.
	 * @param string $value Value passed.
	 * @return string
	 */
	public function render_hidden_input( $field, $key, $args, $value = null ) {
		$field = sprintf(
			'<input type="hidden" id="%s" name="%s" value="%s"/>',
			esc_html( $key ),
			esc_html( $key ),
			esc_html( $value ),
		);

		return $field;
	}

	/**
	 * Render hidden inputs required for the signature.
	 *
	 * @return void
	 */
	public function render_signature_inputs() {

		woocommerce_form_field(
			'swph_woo_custom_field_title',
			array(
				'type'        => 'swph_hidden',
				'class'       => array(),
				'label'       => '',
				'placeholder' => '',
				'required'    => false,
			),
			get_option( 'wc_settings_tab_signature_custom_field_title', esc_html__( 'Terms and Conditions', 'order-signature-for-woocommerce' ) )
		);

		woocommerce_form_field(
			'swph_woo_custom_field_text',
			array(
				'type'        => 'swph_hidden',
				'class'       => array(),
				'label'       => '',
				'placeholder' => '',
				'required'    => false,
			),
			esc_html( get_option( 'wc_settings_tab_signature_custom_field_text', esc_html__( 'By signing below you agree with the terms and conditions.', 'order-signature-for-woocommerce' ) ) )
		);

		woocommerce_form_field(
			'swph_woo_sign_customer_signature',
			array(
				'type'        => 'swph_hidden',
				'class'       => array( 'swph-woo-sign-customer-signature form-row-wide' ),
				'label'       => '',
				'placeholder' => '',
				'required'    => true,
			)
		);

	}

	/**
	 * Validate signature and information.
	 *
	 * @param array  $fields Submitted fields.
	 * @param object $errors Error object.
	 * @return void
	 */
	public function validate_signature_on_checkout( $fields, $errors ) {
		$required_error = get_option( 'wc_settings_tab_signature_error_msg', esc_html__( 'Please draw at least a duck :) Sorry for the inconvenience, but we need to be compliant with tax and documents regulations here as well. Thank you for understanding!', 'order-signature-for-woocommerce' ) );
		// phpcs:ignore
		if ( empty( $_POST['swph_woo_sign_customer_signature'] ) ) {
			$errors->add( 'validation', $required_error );
		}
	}

	/**
	 * Validate signature on generated order pay page.
	 *
	 * @param WC_Order $order Woocommerce order that is payed for.
	 * @return void
	 */
	public function validate_signature_on_order_pay( $order ) {
		$required_error = get_option( 'wc_settings_tab_signature_error_msg', esc_html__( 'Please draw at least a duck :) Sorry for the inconvenience, but we need to be compliant with tax and documents regulations here as well. Thank you for understanding!', 'order-signature-for-woocommerce' ) );
		// Woocommerce already validates nonce.
		// phpcs:ignore
		$signature = esc_html( $_POST['swph_woo_sign_customer_signature'] );

		// phpcs:ignore
		if ( empty( $signature ) ) {
			wc_add_notice( $required_error, 'error' );
			return;
		}

		$this->update_order_and_user_meta( $order->get_id() );
	}

	/**
	 * Update order and current user meta after checkout submit.
	 *
	 * @param int $order_id ID of submitted order.
	 * @return void
	 */
	public function update_order_and_user_meta( $order_id ) {
		// Ignore coding standards as we can't verify Woocommerce nonce.
		// phpcs:disable
		if ( ! empty( $_POST['swph_woo_sign_customer_signature'] ) ) {
			update_post_meta( $order_id, '_customer-signature', wp_kses_post( $_POST['swph_woo_sign_customer_signature'] ) );
		}

		if ( ! empty( $_POST['swph_woo_custom_field_title'] ) ) {
			update_post_meta( $order_id, '_customer-signature-custom-field-title', wp_kses_post( $_POST['swph_woo_custom_field_title'] ) );
		}

		if ( ! empty( $_POST['swph_woo_custom_field_text'] ) ) {
			update_post_meta( $order_id, '_customer-signature-custom-field-text', wp_kses_post( $_POST['swph_woo_custom_field_text'] ) );
		}
		// phpcs:enable
	}

	/**
	 * Determine if template requires frontend scripts and styles.
	 *
	 * @return boolean
	 */
	private function should_enqueue_scripts() {
		return is_checkout();
	}

	/**
	 * Grab signature data from current order.
	 *
	 * @return string
	 */
	private function get_signature_data_for_endpoint() {
		global $wp, $post;
		$data = '';

		$order_id = 0;

		if ( is_wc_endpoint_url( 'order-pay' ) || is_wc_endpoint_url( 'view-order' ) ) {
			$order_id = empty( $wp->query_vars['order-pay'] ) ? $post->ID : $wp->query_vars['order-pay'];
			$data     = $this->get_saved_signature( $order_id );
		}

		return $data;
	}

	/**
	 * Grab signature data from Woocommerce order or user meta.
	 *
	 * @param int $id ID of user or order.
	 * @return string
	 */
	private function get_saved_signature( $id ) {
		// Older versions support.
		$meta_keys = array(
			'_customer-signature',
			'customer-signature',
			'Customer Signature,',
		);

		$data = array();

		foreach ( $meta_keys as $meta_key ) {
			$data = get_post_meta( $id, $meta_key, true );

			if ( ! empty( $data ) ) {
				break;
			}
		}

		return $data;
	}
}
new SWPH_Signature_Frontend();
