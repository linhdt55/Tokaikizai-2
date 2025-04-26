<?php
/**
 * Order Status Rules for WooCommerce - Default Status Class
 *
 * @version 3.1.0
 * @since   3.1.0
 *
 * @author  Algoritmika Ltd.
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_Order_Status_Rules_Default_Status' ) ) :

class Alg_WC_Order_Status_Rules_Default_Status {

	/**
	 * Constructor.
	 *
	 * @version 3.1.0
	 * @since   3.1.0
	 *
	 * @todo    (feature) conditional, e.g., per order payment gateway
	 */
	function __construct() {
		add_filter( 'woocommerce_default_order_status', array( $this, 'default_order_status' ), PHP_INT_MAX );
	}

	/**
	 * default_order_status.
	 *
	 * @version 3.1.0
	 * @since   3.1.0
	 */
	function default_order_status( $status ) {
		return ( '' !== ( $_status = get_option( 'alg_wc_order_status_rules_default_order_status', '' ) ) ? $_status : $status );
	}

}

endif;

return new Alg_WC_Order_Status_Rules_Default_Status();
