<?php
/*
Plugin Name: Scheduled & Automatic Order Status Controller for WooCommerce
Plugin URI: https://wpfactory.com/item/order-status-rules-for-woocommerce/
Description: Automate WooCommerce order statuses. Beautifully.
Version: 3.7.1
Author: WPFactory
Author URI: https://wpfactory.com
Text Domain: order-status-rules-for-woocommerce
Domain Path: /langs
WC tested up to: 9.5
Requires Plugins: woocommerce
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

defined( 'ABSPATH' ) || exit;

if ( 'order-status-rules-for-woocommerce.php' === basename( __FILE__ ) ) {
	/**
	 * Check if Pro plugin version is activated.
	 *
	 * @version 3.4.0
	 * @since   1.7.0
	 */
	$plugin = 'order-status-rules-for-woocommerce-pro/order-status-rules-for-woocommerce-pro.php';
	if (
		in_array( $plugin, (array) get_option( 'active_plugins', array() ), true ) ||
		( is_multisite() && array_key_exists( $plugin, (array) get_site_option( 'active_sitewide_plugins', array() ) ) )
	) {
		defined( 'ALG_WC_ORDER_STATUS_RULES_FILE_FREE' ) || define( 'ALG_WC_ORDER_STATUS_RULES_FILE_FREE', __FILE__ );
		return;
	}
}

defined( 'ALG_WC_ORDER_STATUS_RULES_VERSION' ) || define( 'ALG_WC_ORDER_STATUS_RULES_VERSION', '3.7.1' );

defined( 'ALG_WC_ORDER_STATUS_RULES_FILE' ) || define( 'ALG_WC_ORDER_STATUS_RULES_FILE', __FILE__ );

require_once( 'includes/class-alg-wc-order-status-rules.php' );

if ( ! function_exists( 'alg_wc_order_status_rules' ) ) {
	/**
	 * Returns the main instance of Alg_WC_Order_Status_Rules to prevent the need to use globals.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function alg_wc_order_status_rules() {
		return Alg_WC_Order_Status_Rules::instance();
	}
}

add_action( 'plugins_loaded', 'alg_wc_order_status_rules' );
