<?php
/**
 * Order Status Rules for WooCommerce - Process Payment Order Status Class
 *
 * @version 3.1.0
 * @since   3.1.0
 *
 * @author  Algoritmika Ltd.
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_Order_Status_Rules_Process_Payment' ) ) :

class Alg_WC_Order_Status_Rules_Process_Payment {

	/**
	 * Constructor.
	 *
	 * @version 3.1.0
	 * @since   3.1.0
	 *
	 * @todo    (feature) conditional, e.g., `$order->has_downloadable_item() ? 'on-hold' : 'processing'`
	 */
	function __construct() {

		// Direct bank transfer
		add_filter( 'woocommerce_bacs_process_payment_order_status', array( $this, 'bacs_process_payment_order_status' ), PHP_INT_MAX, 2 );

		// Check payments
		add_filter( 'woocommerce_cheque_process_payment_order_status', array( $this, 'cheque_process_payment_order_status' ), PHP_INT_MAX, 2 );

		// Cash on delivery (COD)
		add_filter( 'woocommerce_cod_process_payment_order_status', array( $this, 'cod_process_payment_order_status' ), PHP_INT_MAX, 2 );

	}

	/**
	 * bacs_process_payment_order_status.
	 *
	 * @version 3.1.0
	 * @since   3.1.0
	 */
	function bacs_process_payment_order_status( $status, $order ) {
		return ( '' !== ( $_status = get_option( 'alg_wc_order_status_rules_bacs_process_payment_order_status', '' ) ) ? $_status : $status );
	}

	/**
	 * cheque_process_payment_order_status.
	 *
	 * @version 3.1.0
	 * @since   3.1.0
	 */
	function cheque_process_payment_order_status( $status, $order ) {
		return ( '' !== ( $_status = get_option( 'alg_wc_order_status_rules_cheque_process_payment_order_status', '' ) ) ? $_status : $status );
	}

	/**
	 * cod_process_payment_order_status.
	 *
	 * @version 3.1.0
	 * @since   3.1.0
	 */
	function cod_process_payment_order_status( $status, $order ) {
		return ( '' !== ( $_status = get_option( 'alg_wc_order_status_rules_cod_process_payment_order_status', '' ) ) ? $_status : $status );
	}

}

endif;

return new Alg_WC_Order_Status_Rules_Process_Payment();
