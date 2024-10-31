<?php
/**
 * Plugin Name: Order Signature for WooCommerce - Light
 * Plugin URI: http://superwpheroes.io/woocommerce-order-signiture-plugin-wordpress-heroes/
 * Description: Add a nice responsive signature pad to your website's WooCommerce checkout page. If you find it usefull, kindly take a minute of your time to <a href="https://wordpress.org/support/plugin/order-signature-for-woocommerce/reviews/#new-post" target="_blank">rate it</a>.
 * Version: 2.0.1
 * Author: SUPER WP HEROES
 * Author URI: http://superwpheroes.io
 * License:     GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: order-signature-for-woocommerce
 * Domain Path: /languages
 * Requires at least: 4.0
 * Tested up to: 6.5.4
 * WC requires at least: 3.0.0
 * WC tested up to: 8.9.3
 *
 * @package swph-woo-order-signature
 */

defined( 'ABSPATH' ) || die( 'No script stuff please!' );
define( 'SWPH_REGISTER_PATH', __FILE__ );
define( 'SWPH_PLUGIN_FILE', plugin_basename( __FILE__ ) );
define( 'SWPH_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'SWPH_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once SWPH_PLUGIN_DIR . 'includes/class-swph-signature-helper.php';
require_once SWPH_PLUGIN_DIR . 'includes/class-swph-signature-backend.php';
require_once SWPH_PLUGIN_DIR . 'includes/class-swph-signature-woo.php';
require_once SWPH_PLUGIN_DIR . 'includes/class-swph-signature-frontend.php';
