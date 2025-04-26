<?php
/**
 * Order Status Rules for WooCommerce - My Account Class
 *
 * @version 3.3.0
 * @since   1.8.0
 *
 * @author  Algoritmika Ltd.
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_Order_Status_Rules_My_Account' ) ) :

class Alg_WC_Order_Status_Rules_My_Account {

	/**
	 * Constructor.
	 *
	 * @version 1.8.0
	 * @since   1.8.0
	 *
	 * @todo    (feature) additional "Position": `do_action( 'woocommerce_before_account_orders' || 'woocommerce_after_account_orders', $has_orders );`
	 */
	function __construct() {
		if ( 'yes' === get_option( 'alg_wc_osr_my_account_orders_status_history_enabled', 'no' ) ) {
			$column_id = get_option( 'alg_wc_osr_my_account_orders_status_history_position', 'order-status' );
			if ( 'alg-wc-osr-history' === $column_id ) {
				add_filter( 'woocommerce_account_orders_columns', array( $this, 'add_status_history_column' ) );
			}
			add_action( 'woocommerce_my_account_my_orders_column_' . $column_id, array( $this, 'status_history_column' ) );
		}
	}

	/**
	 * add_status_history_column.
	 *
	 * @version 1.8.0
	 * @since   1.8.0
	 *
	 * @see     https://github.com/woocommerce/woocommerce/blob/5.5.2/includes/wc-account-functions.php#L192
	 *
	 * @todo    (feature) customizable column position
	 */
	function add_status_history_column( $columns ) {
		$_title   = get_option( 'alg_wc_osr_my_account_orders_status_history_column_title', __( 'History', 'order-status-rules-for-woocommerce' ) );
		$_columns = array();
		$is_added = false;
		foreach ( $columns as $id => $title ) {
			$_columns[ $id ] = $title;
			if ( 'order-status' === $id ) {
				$_columns['alg-wc-osr-history'] = $_title;
				$is_added = true;
			}
		}
		if ( ! $is_added ) {
			$_columns['alg-wc-osr-history'] = $_title; // fallback
		}
		return $_columns;
	}

	/**
	 * status_history_column.
	 *
	 * @version 3.3.0
	 * @since   1.8.0
	 *
	 * @see     https://github.com/woocommerce/woocommerce/blob/5.5.2/templates/myaccount/orders.php#L45
	 */
	function status_history_column( $order ) {
		$templates      = get_option( 'alg_wc_osr_my_account_orders_status_history_templates', array() );
		$defaults       = array( 'before' => '%current_status%', 'each_record' => '<br>%status_from% &rarr; %status_to%', 'after' => '' );
		$templates      = wp_parse_args( $templates, $defaults );
		$is_reverse     = ( 'yes' === get_option( 'alg_wc_osr_my_account_orders_status_history_reverse', 'yes' ) );
		$current_status = esc_html( alg_wc_order_status_rules()->core->get_status_name( $order->get_status() ) );
		$records        = '';
		$status_history = alg_wc_order_status_rules()->core->get_order_status_change_history( $order->get_id() );
		if ( ! empty( $status_history ) ) {
			if ( $is_reverse ) {
				$status_history = array_reverse( $status_history, true );
			}
			foreach ( $status_history as $index => $record ) {
				$placeholders = array(
					'%record_nr%'   => ( $index + 1 ),
					'%record_date%' => date_i18n( get_option( 'date_format' ), $record['time'] ),
					'%record_time%' => date_i18n( get_option( 'time_format' ), $record['time'] ),
					'%status_from%' => esc_html( alg_wc_order_status_rules()->core->get_status_name( $record['from'] ) ),
					'%status_to%'   => esc_html( alg_wc_order_status_rules()->core->get_status_name( $record['to'] ) ),
				);
				$records .= str_replace( array_keys( $placeholders ), $placeholders, $templates['each_record'] );
			}
		}
		echo str_replace( '%current_status%', $current_status, $templates['before'] ) . $records . str_replace( '%current_status%', $current_status, $templates['after'] );
	}

}

endif;

return new Alg_WC_Order_Status_Rules_My_Account();
