<?php
/**
 * Order Status Rules for WooCommerce - Admin Class
 *
 * @version 3.7.1
 * @since   1.4.0
 *
 * @author  Algoritmika Ltd.
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_Order_Status_Rules_Admin' ) ) :

class Alg_WC_Order_Status_Rules_Admin {

	/**
	 * core.
	 *
	 * @version 3.5.0
	 * @since   3.5.0
	 */
	public $core;

	/**
	 * Constructor.
	 *
	 * @version 2.7.2
	 * @since   1.4.0
	 */
	function __construct() {
		// Meta box
		add_action( 'add_meta_boxes', array( $this, 'add_status_change_meta_box' ) );
		// "Guest" in `wc-customer-search`
		add_filter( 'woocommerce_json_search_found_customers', array( $this, 'wc_customer_search_guest' ), PHP_INT_MAX );
	}

	/**
	 * get_core.
	 *
	 * @version 1.6.0
	 * @since   1.6.0
	 */
	function get_core() {
		if ( ! isset( $this->core ) ) {
			$this->core = alg_wc_order_status_rules()->core;
		}
		return $this->core;
	}

	/**
	 * wc_customer_search_guest.
	 *
	 * @version 2.7.2
	 * @since   2.7.2
	 *
	 * @todo    (dev) replace `guest` key with `0` key?
	 */
	function wc_customer_search_guest( $found_customers ) {
		if ( isset( $_GET['term'], $_GET['exclude'] ) && 'alg_wc_order_status_rules' === wc_clean( $_GET['exclude'] ) ) {
			$term  = (string) wc_clean( wp_unslash( $_GET['term'] ) );
			$guest = esc_html__( 'Guest', 'order-status-rules-for-woocommerce' );
			if ( false !== stripos( $guest, $term ) ) {
				$found_customers['guest'] = $guest;
			}
		}
		return $found_customers;
	}

	/**
	 * add_status_change_meta_box.
	 *
	 * @version 3.3.0
	 * @since   1.0.0
	 */
	function add_status_change_meta_box() {
		$screen = get_option( 'alg_wc_order_status_rules_meta_box_screen', array( 'shop_order' ) );
		if ( ! empty( $screen ) ) {
			add_meta_box(
				'alg-wc-order-status-change-history',
				__( 'Order Status History', 'order-status-rules-for-woocommerce' ),
				array( $this, 'create_status_change_meta_box' ),
				$screen,
				'normal',
				'default'
			);
		}
	}

	/**
	 * create_status_change_meta_box.
	 *
	 * @version 3.7.1
	 * @since   1.0.0
	 *
	 * @todo    (dev) use `$this->get_core()->get_status_name()`
	 * @todo    (desc) `( ! $is_status_match )`: better desc
	 */
	function create_status_change_meta_box() {

		$order_id       = get_the_ID();
		$status_history = $this->get_core()->get_order_status_change_history( $order_id );

		if ( empty( $status_history ) ) {

			echo '<p><em>' . __( 'No data.', 'order-status-rules-for-woocommerce' ) . '</p></em>';

		} else {

			// Status history
			$date_format    = $this->get_core()->get_date_time_format();
			$status_history = array_reverse( $status_history, true );
			$last_record    = current( $status_history );
			$status         = $this->get_core()->get_statuses();
			echo '<table class="widefat stripped">' .
				'<tr>' .
					'<th>' . __( 'Nr.', 'order-status-rules-for-woocommerce' )  . '</th>' .
					'<th>' . __( 'Time', 'order-status-rules-for-woocommerce' ) . '</th>' .
					'<th>' . __( 'From', 'order-status-rules-for-woocommerce' ) . '</th>' .
					'<th>' . __( 'To', 'order-status-rules-for-woocommerce' )   . '</th>' .
				'</tr>';
			foreach ( $status_history as $index => $record ) {
				$i    = ( $index + 1 );
				$time = date_i18n( $date_format, $record['time'] );
				$from = '<code>' . ( $status[ 'wc-' . $record['from'] ] ?? $record['from'] ) . '</code>';
				$to   = '<code>' . ( $status[ 'wc-' . $record['to'] ]   ?? $record['to'] )   . '</code>';
				echo "<tr><td>{$i}</td><td>{$time}</td><td>{$from}</td><td>{$to}</td></tr>";
			}
			echo '</table>';

			// Scheduled status update
			$this->get_core()->init_options();
			$order               = wc_get_order( $order_id );
			$args                = array( 'order_status' => $order->get_status(), 'order' => $order );
			$is_status_match     = ( $last_record['to'] === $args['order_status'] );
			$last_record['to']   = ( ! $is_status_match && $this->get_core()->do_use_last_record ? $args['order_status'] : $last_record['to'] );
			$args['last_record'] = $last_record;
			$is_rule_applied     = false;
			foreach ( $this->get_core()->options['from'] as $i => $from ) {
				$args['from'] = $from;
				if ( $this->get_core()->do_apply_rule( $i, $args ) ) {
					$unit           = ( $this->get_core()->options['time_trigger_units'][ $i ] ?? 'hour' );
					$step           = $this->get_core()->get_trigger_unit_step( $unit );
					$skip_days      = ( $this->get_core()->options['skip_days'][ $i ] ?? false );
					$skip_dates     = ( $this->get_core()->options['skip_dates'][ $i ] ?? false );
					$current_time   = current_time( 'timestamp' );
					$time_remaining = $this->get_core()->get_time_remaining( $last_record['time'], $this->get_core()->options['time_triggers'][ $i ] * $step, $skip_days, $skip_dates, $current_time );
					$to             = $this->get_core()->options['to'][ $i ];
					$rule           = (
						sprintf(
							/* Translators: %s: Rule ID. */
							__( 'Rule #%s', 'order-status-rules-for-woocommerce' ),
							$i
						) .
						(
							! empty( $this->get_core()->options['titles'][ $i ] ) ?
							': ' . $this->get_core()->options['titles'][ $i ] :
							''
						)
					);
					echo '<p><em>' .
						sprintf(
							/* Translators: %1$s: Status name, %2$s: Status name, %3$s: Rule name, %4$s: Date and time, %5$s: Time difference text. */
							__( 'Status scheduled to be updated from %1$s to %2$s (%3$s) on %4$s (i.e., %5$s).', 'order-status-rules-for-woocommerce' ),
							'<code>' . ( $status[ $from ] ?? $from ) . '</code>',
							'<code>' . ( $status[ $to ]   ?? $to )   . '</code>',
							$rule,
							date_i18n( $date_format, $current_time + $time_remaining ),
							( $time_remaining > 0 ?
								sprintf(
									/* Translators: %s: Time difference. */
									__( 'in %s', 'order-status-rules-for-woocommerce' ),
									human_time_diff( $current_time - $time_remaining, $current_time )
								) :
								__( 'now', 'order-status-rules-for-woocommerce' ) )
						) . ' ' .
						sprintf(
							/* Translators: %s: Date and time. */
							__( 'Current time is %s.', 'order-status-rules-for-woocommerce' ),
							date_i18n( $date_format, $current_time )
						) .
					'</p></em>';
					$is_rule_applied = true;
					break;
				}
			}
			if ( ! $is_rule_applied ) {
				echo '<p><em>' .
					__( 'No order status rules are scheduled to be applied for the current order.', 'order-status-rules-for-woocommerce' ) .
				'</em></p>';
			}

			// Check matching order status
			if ( ! $is_status_match ) {
				echo '<p>';
				if ( $this->get_core()->do_use_last_record ) {
					echo '<span class="dashicons dashicons-info"></span> ' .
						__( 'Although the current order status does not match the last record in the order history, it will be used anyway.', 'order-status-rules-for-woocommerce' );
				} else {
					echo '<span class="dashicons dashicons-warning" style="color:red;"></span> ' .
						__( 'The current order status does not match the last record in the order history! Order status rules will not be applied!', 'order-status-rules-for-woocommerce' );
				}
				echo ' ' . sprintf(
					/* Translators: %1$s: Option name, %2$s: Section link. */
					__( 'To change this behaviour, please check the "%1$s" option in the %2$s section.', 'order-status-rules-for-woocommerce' ),
					__( 'On non-matching order status', 'order-status-rules-for-woocommerce' ),
					'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=alg_wc_order_status_rules&section=advanced' ) . '" target="_blank">' .
						__( 'Advanced', 'order-status-rules-for-woocommerce' ) .
					'</a>'
				);
				echo '</p>';
			}

		}

	}

}

endif;

return new Alg_WC_Order_Status_Rules_Admin();
