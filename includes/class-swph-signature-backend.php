<?php
/**
 * Class for backend functions.
 *
 * @package SWPH_Order_Signature_Free
 */

/**
 * Backend WordPress plugin class.
 */
class SWPH_Signature_Backend {

	/**
	 * Class constructor. Will find hooks here.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'check_for_woo' ) );
		add_action( 'admin_init', array( $this, 'load_textdomain' ) );
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		register_activation_hook( SWPH_REGISTER_PATH, array( $this, 'check_for_pro' ) );
	}

	/**
	 * Check if PRO version of plugin is active. If it is, deactivate it to avoid conflicts.
	 */
	public function check_for_pro() {
		if ( SWPH_Signature_Helper::is_plugin_active( 'order-signature-for-woocommerce-pro/swph-woo-order-signature-pro.php' ) && current_user_can( 'activate_plugins' ) ) {
			die( esc_html__( 'PRO version is already activated. Activation stopped.', 'order-signature-for-woocommerce' ) );
		}
	}

	/**
	 * Check if Woocommerce plugin is active. If its not, disable this plugin to prevent errors.
	 *
	 * @return void
	 */
	public function check_for_woo() {
		if ( ! SWPH_Signature_Helper::is_plugin_active( 'woocommerce/woocommerce.php' ) && current_user_can( 'activate_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
			deactivate_plugins( SWPH_PLUGIN_FILE );
		}
	}

	/**
	 * Initialization of the plugin and enqueueing backend hooks.
	 *
	 * @return void
	 */
	public function admin_init() {
		register_activation_hook( __FILE__, __NAMESPACE__, array( $this, 'activate_plugin' ) );

		add_filter( 'plugin_action_links_' . SWPH_PLUGIN_FILE, array( $this, 'add_plugin_links' ) );

		// phpcs:ignore
		$current_page = isset( $_GET['page'] ) ? filter_input( INPUT_GET, 'page', FILTER_SANITIZE_STRING ) : '';
		// phpcs:ignore
		$current_tab  = isset( $_GET['tab'] ) ? filter_input( INPUT_GET, 'tab', FILTER_SANITIZE_STRING ) : '';

		if ( 'wc-settings' === $current_page && 'settings_tab_signature' && $current_tab ) {
			add_action( 'admin_print_styles', array( $this, 'admin_styles' ) );
			add_action( 'admin_print_scripts', array( $this, 'admin_scripts' ) );
		}

		add_action( 'add_meta_boxes', array( $this, 'signature_order_metabox' ) );
	}

	/**
	 * Load plugin textdomain.
	 *
	 * @return void
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'order-signature-for-woocommerce', false, dirname( SWPH_PLUGIN_FILE ) ) . '/languages';
	}

	/**
	 * Checks that should run once the plugin is activated.
	 *
	 * @return void
	 */
	public function activate_plugin() {
		$woocommerce_error = 'Sorry, but this plugin requires the WooCommerce Plugin to be installed and active. <br><a href="%s">&laquo; Return to Plugins</a>';

		if ( ! SWPH_Signature_Helper::is_plugin_active( 'woocommerce/woocommerce.php' ) && current_user_can( 'activate_plugins' ) ) {
			// phpcs:ignore
			wp_die( printf( $woocommerce_error, admin_url( 'plugins.php' ) ) );
		}
	}

	/**
	 * Enqueue backend scripts.
	 *
	 * @return void
	 */
	public function admin_styles() {
		wp_enqueue_style(
			'swph-woo-sign-backend-styles',
			SWPH_PLUGIN_URL . 'assets/css/swph-woo-sign-back-end-styles-custom.css',
			array( 'wp-color-picker' ),
			filemtime( SWPH_PLUGIN_DIR . 'assets/css/swph-woo-sign-back-end-styles-custom.css' ),
		);
	}

	/**
	 * Enqueue backend scripts.
	 *
	 * @return void
	 */
	public function admin_scripts() {
		wp_register_script(
			'swph-woo-sign-back-end-scripts',
			SWPH_PLUGIN_URL . 'assets/js/swph-woo-sign-back-end-scripts-custom.js',
			array(
				'jquery',
				'wp-color-picker',
			),
			filemtime( SWPH_PLUGIN_DIR . 'assets/js/swph-woo-sign-back-end-scripts-custom.js' ),
			false
		);
		wp_enqueue_script( 'swph-woo-sign-back-end-scripts' );
	}

	/**
	 * Add custom links to the plugin inside the Plugin List page.
	 *
	 * @param array $links Default WordPress links for the plugin.
	 * @return array
	 */
	public function add_plugin_links( $links ) {
		$custom_links = array(
			'<a target="_blank" href="https://wordpress.org/support/plugin/order-signature-for-woocommerce/reviews/#new-post">' . esc_html__( 'Rate This Plugin', 'order-signature-for-woocommerce' ) . '</a>',
			'<a href="mailto:support@superwpheroes.io?subject=Order Signature for WooCommerce not working on ' . get_bloginfo( 'url' ) . '">' . esc_html__( 'SUPPORT', 'order-signature-for-woocommerce' ) . '</a>',
			'<a href="admin.php?page=wc-settings&tab=settings_tab_signature">' . esc_html__( 'Settings', 'order-signature-for-woocommerce' ) . '</a>',
		);

		return array_merge( $custom_links, $links );
	}

	/**
	 * Add order metabox to display signature.
	 *
	 * @return void
	 */
	public function signature_order_metabox() {
		add_meta_box(
			'swph_customer_signature',
			esc_html__( 'Customer Signature', 'order-signature-for-woocommerce' ),
			array( $this, 'display_signature' ),
			'shop_order',
			'side',
			'default'
		);
	}

	/**
	 * Display order signature in edit signature screen.
	 *
	 * @param WP_Post $order Order object.
	 * @return void
	 */
	public function display_signature( $order ) {
		SWPH_Signature_Helper::display_order_signature( $order, '100%' );
	}

}
new SWPH_Signature_Backend();
