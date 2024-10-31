<?php
/**
 * Class responsable for implementing the Woocommerce settings.
 *
 * @package SWPH_Order_Signature_Free
 */

/**
 * Class responsable for implementing the Woocommerce settings.
 */
class SWPH_Signature_Woo {

	/**
	 * Setting fields and sections.
	 *
	 * @var array
	 */
	private $settings_fields;

	/**
	 * Class constructor.
	 */
	public function __construct() {
		$this->initialize();
		add_action( 'wp_loaded', array( $this, 'wp_loaded_actions' ) );
	}

	/**
	 * Initialize class variables.
	 *
	 * @return void
	 */
	private function initialize() {
		$this->settings_fields = array(
			'section_title'                => array(
				'name'  => esc_html__( 'Signature Settings', 'order-signature-for-woocommerce' ),
				'type'  => 'title',
				'desc'  => '',
				'id'    => 'wc_settings_tab_signature_section_title',
				'class' => 'swph_setting_field',
			),
			'done_signing'                 => array(
				'name'    => esc_html__( '"Done Signing" Button Text', 'order-signature-for-woocommerce' ),
				'default' => esc_html__( 'Done Signing', 'order-signature-for-woocommerce' ),
				'type'    => 'text',
				'desc'    => '',
				'id'      => 'wc_settings_tab_signature_done_signing',
				'class'   => 'swph_setting_field',
			),
			'clear_sign'                   => array(
				'name'    => esc_html__( '"Clear Signature" Button Text', 'order-signature-for-woocommerce' ),
				'default' => esc_html__( 'Clear Signature', 'order-signature-for-woocommerce' ),
				'type'    => 'text',
				'desc'    => '',
				'id'      => 'wc_settings_tab_signature_clear_sign',
			),
			'sign_color'                   => array(
				'name'    => esc_html__( 'Signature color', 'order-signature-for-woocommerce' ),
				'default' => '#000000',
				'type'    => 'text',
				'desc'    => '',
				'id'      => 'wc_settings_tab_signature_sign_color',
			),
			'sign_stroke'                  => array(
				'name'    => esc_html__( 'Signature stroke thickness', 'order-signature-for-woocommerce' ),
				'default' => '5',
				'type'    => 'number',
				'desc'    => esc_html__( 'in pixels', 'order-signature-for-woocommerce' ),
				'id'      => 'wc_settings_tab_signature_sign_stroke',
			),
			'sign_error_msg'               => array(
				'name'    => esc_html__( 'Signature error message', 'order-signature-for-woocommerce' ),
				'default' => esc_html__( 'Please draw at least a duck :) Sorry for the inconvenience, but we need to be compliant with tax and documents regulations here as well. Thank you for understanding!', 'order-signature-for-woocommerce' ),
				'type'    => 'textarea',
				'desc'    => '',
				'id'      => 'wc_settings_tab_signature_error_msg',
			),
			'pro_banner'                   => array(
				'name' => '',
				'type' => 'title',
				'desc' => '<div class="swph_pro_banner"><a href="https://superwpheroes.io/product/order-signature-for-woocommerce-pro/" target="_blank"><img src="' . SWPH_PLUGIN_URL . 'assets/img/order-signature-for-woocommerce-go-pro-banner.png" /></a></div>',
				'id'   => 'wc_settings_tab_signature_custom_type',
			),
			'custom_field_name'            => array(
				'name'    => esc_html__( 'Custom wavier title', 'order-signature-for-woocommerce' ),
				'default' => esc_html__( 'Default text', 'order-signature-for-woocommerce' ),
				'type'    => 'text',
				'desc'    => esc_html__( 'Text that shows on the Checkout page above wavier text.', 'order-signature-for-woocommerce' ),
				'id'      => 'wc_settings_tab_signature_custom_field_title',
			),
			'custom_field_text'            => array(
				'name'    => esc_html__( 'Custom wavier text', 'order-signature-for-woocommerce' ),
				'default' => esc_html__( 'Default text', 'order-signature-for-woocommerce' ),
				'type'    => 'textarea',
				'desc'    => esc_html__( 'Wavier text that the clients should read on the Checkout page before they sign.', 'order-signature-for-woocommerce' ),
				'id'      => 'wc_settings_tab_signature_custom_field_text',
			),
			'customer_signature_text'      => array(
				'name'    => esc_html__( '"Customer Signature" Text', 'order-signature-for-woocommerce' ),
				'default' => esc_html__( 'Customer Signature', 'order-signature-for-woocommerce' ),
				'type'    => 'text',
				'desc'    => esc_html__( 'The title for the customer signature section on Checkout, Thank you and View Order pages.', 'order-signature-for-woocommerce' ),
				'id'      => 'wc_settings_tab_signature_customer_signature_text',
			),
			'customer_signature_text_size' => array(
				'name'    => esc_html__( '"Customer Signature" Text Type', 'order-signature-for-woocommerce' ),
				'default' => 'h2',
				'type'    => 'select',
				'options' => array(
					'h1' => 'H1',
					'h2' => 'H2',
					'h3' => 'H3',
					'h4' => 'H4',
					'h5' => 'H5',
					'h6' => 'H6',
				),
				'id'      => 'wc_settings_tab_signature_customer_signature_text_size',
				'desc'    => esc_html__( 'The typography tag for the customer signature section on Checkout, Thank you and View Order pages.', 'order-signature-for-woocommerce' ),
			),
			'section_end'                  => array(
				'type' => 'sectionend',
				'id'   => 'wc_settings_tab_signature_section_end',
			),
		);
	}

	/**
	 * Add necessary hooks after WordPress have been loaded.
	 *
	 * @return void
	 */
	public function wp_loaded_actions() {
		add_filter( 'woocommerce_settings_tabs_array', array( $this, 'add_settings_tab' ), 999, 1 );
		add_action( 'woocommerce_settings_tabs_settings_tab_signature', array( $this, 'enqueue_fields' ) );
		add_action( 'woocommerce_update_options_settings_tab_signature', array( $this, 'update_fields' ) );
	}

	/**
	 * Add signature tab to Woocommerce list.
	 *
	 * @param array $tabs Array of existing tabs.
	 * @return array
	 */
	public function add_settings_tab( $tabs ) {
		$tabs['settings_tab_signature'] = esc_html__( 'Order Signature', 'order-signature-for-woocommerce' );
		return $tabs;
	}

	/**
	 * Enqueue woocommerce fields inside the settings tab.
	 *
	 * @return void
	 */
	public function enqueue_fields() {
		woocommerce_admin_fields( $this->settings_fields );
	}

	/**
	 * Enqueue woocommerce fields inside the settings tab.
	 *
	 * @return void
	 */
	public function update_fields() {
		woocommerce_update_options( $this->settings_fields );
	}

}
new SWPH_Signature_Woo();
