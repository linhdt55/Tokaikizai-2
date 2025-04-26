<?php
/**
 * Order Status Rules for WooCommerce - Compatibility Class
 *
 * @version 1.8.0
 * @since   1.8.0
 *
 * @author  Algoritmika Ltd.
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_Order_Status_Rules_Compatibility' ) ) :

class Alg_WC_Order_Status_Rules_Compatibility {

	/**
	 * Constructor.
	 *
	 * @version 1.8.0
	 * @since   1.8.0
	 */
	function __construct() {
		// "Doctreat" theme
		if ( 'yes' === get_option( 'alg_wc_order_status_rules_compatibility_doctreat', 'no' ) ) {
			add_action( 'init', array( $this, 'doctreat' ) );
		}
	}

	/**
	 * doctreat.
	 *
	 * @version 1.7.0
	 * @since   1.7.0
	 *
	 * @see     doctreat\directory\front-end\woo-hooks.php
	 */
	function doctreat() {
		if ( function_exists( 'doctreat_theme_option' ) ) {
			$offline_package = doctreat_theme_option( 'payment_type' );
			if ( ! empty( $offline_package ) && 'offline' === $offline_package ) {
				if ( wp_doing_cron() ) {
					if ( function_exists( 'doctreat_update_order_data' ) ) {
						if ( false === has_action( 'woocommerce_order_status_completed', 'doctreat_update_order_data' ) ) {
							add_action( 'woocommerce_order_status_completed', 'doctreat_update_order_data', 10 );
						}
						if ( false === has_action( 'woocommerce_order_status_on-hold', 'doctreat_update_order_data' ) ) {
							add_action( 'woocommerce_order_status_on-hold', 'doctreat_update_order_data', 10 );
						}
					}
					if ( function_exists( 'doctreat_on_hold_payment_complete' ) ) {
						if ( false === has_action( 'woocommerce_order_status_completed', 'doctreat_on_hold_payment_complete' ) ) {
							add_action( 'woocommerce_order_status_completed', 'doctreat_on_hold_payment_complete', 10 );
						}
					}
				}
			}
		}
	}

}

endif;

return new Alg_WC_Order_Status_Rules_Compatibility();
