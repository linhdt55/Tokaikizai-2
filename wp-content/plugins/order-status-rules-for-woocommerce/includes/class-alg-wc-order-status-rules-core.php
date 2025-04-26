<?php
/**
 * Order Status Rules for WooCommerce - Core Class
 *
 * @version 3.5.5
 * @since   1.0.0
 *
 * @author  Algoritmika Ltd.
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_Order_Status_Rules_Core' ) ) :

class Alg_WC_Order_Status_Rules_Core {

	/**
	 * conditions.
	 *
	 * @version 3.5.0
	 * @since   3.5.0
	 */
	public $conditions;

	/**
	 * crons.
	 *
	 * @version 3.5.0
	 * @since   3.5.0
	 */
	public $crons;

	/**
	 * action_scheduler.
	 *
	 * @version 3.5.0
	 * @since   3.5.0
	 */
	public $action_scheduler;

	/**
	 * options.
	 *
	 * @version 3.5.0
	 * @since   3.5.0
	 */
	public $options;

	/**
	 * do_debug.
	 *
	 * @version 3.5.0
	 * @since   3.5.0
	 */
	public $do_debug;

	/**
	 * do_use_last_record.
	 *
	 * @version 3.5.0
	 * @since   3.5.0
	 */
	public $do_use_last_record;

	/**
	 * on_no_history.
	 *
	 * @version 3.5.0
	 * @since   3.5.0
	 */
	public $on_no_history;

	/**
	 * Constructor.
	 *
	 * @version 3.3.0
	 * @since   1.0.0
	 *
	 * @todo    (dev) remove `alg_wc_order_status_rules_plugin_enabled` (or move `process_rules_manual`, etc. inside the `alg_wc_order_status_rules_plugin_enabled`)
	 * @todo    (dev) remove rules with trigger set to zero from crons?
	 * @todo    (dev) pre-check for possible infinite loops in rules
	 */
	function __construct() {

		if ( 'yes' === get_option( 'alg_wc_order_status_rules_plugin_enabled', 'yes' ) ) {

			// Track order status change
			$this->add_track_status_change_actions();

			// Hooks (e.g., immediately process rules on any order status change)
			$this->add_rules_processing_actions();

			// Process rules via URL
			if ( 'yes' === get_option( 'alg_wc_order_status_rules_allow_url', 'no' ) ) {
				add_action( 'init', array( $this, 'process_rules_url' ), PHP_INT_MAX );
			}

			// Admin order edit page meta box, etc.
			require_once( 'class-alg-wc-order-status-rules-admin.php' );

			// My account > Orders
			require_once( 'class-alg-wc-order-status-rules-my-account.php' );

			// Compatibility
			require_once( 'class-alg-wc-order-status-rules-compatibility.php' );

			// Default order status
			require_once( 'class-alg-wc-order-status-rules-default-status.php' );

			// Process payment order status
			require_once( 'class-alg-wc-order-status-rules-process-payment.php' );

		}

		// Conditions
		$this->conditions = require_once( 'class-alg-wc-order-status-rules-conditions.php' );

		// Crons
		$this->crons = require_once( 'class-alg-wc-order-status-rules-crons.php' );

		// Action scheduler
		$this->action_scheduler = require_once( 'class-alg-wc-order-status-rules-action-scheduler.php' );

		// Process rules manually
		add_action( 'alg_wc_order_status_rules_after_save_settings', array( $this, 'process_rules_manual' ) );

	}

	/**
	 * add_to_log.
	 *
	 * @version 1.4.0
	 * @since   1.4.0
	 */
	function add_to_log( $message ) {
		if ( function_exists( 'wc_get_logger' ) && ( $log = wc_get_logger() ) ) {
			$log->log( 'info', $message, array( 'source' => 'order-status-rules' ) );
		}
	}

	/**
	 * do_debug.
	 *
	 * @version 1.4.0
	 * @since   1.4.0
	 */
	function do_debug() {
		if ( ! isset( $this->do_debug ) ) {
			$this->do_debug = ( 'yes' === get_option( 'alg_wc_order_status_rules_debug', 'no' ) );
		}
		return $this->do_debug;
	}

	/**
	 * get_statuses.
	 *
	 * @version 3.3.0
	 * @since   3.3.0
	 */
	function get_statuses() {
		$statuses         = array();
		$status_functions = get_option( 'alg_wc_order_status_rules_status_functions', array( 'wc_get_order_statuses' ) );
		foreach ( $status_functions as $status_function ) {
			if ( function_exists( $status_function ) ) {
				$statuses = array_merge( $status_function(), $statuses );
			}
		}
		return $statuses;
	}

	/**
	 * get_status_name.
	 *
	 * @version 3.5.0
	 * @since   3.3.0
	 *
	 * @see     `wc_get_order_status_name()`
	 * @see     `wcs_get_subscription_status_name()`
	 */
	function get_status_name( $status ) {
		$statuses = $this->get_statuses();
		$status   = 'wc-' === substr( $status, 0, 3 ) ? substr( $status, 3 ) : $status;
		$status   = $statuses[ 'wc-' . $status ] ?? $status;
		return $status;
	}

	/**
	 * add_track_status_change_actions.
	 *
	 * @version 3.3.0
	 * @since   3.3.0
	 */
	function add_track_status_change_actions() {
		$hooks = get_option( 'alg_wc_order_status_rules_status_change_hooks', array( 'woocommerce_order_status_changed' ) );
		foreach ( $hooks as $hook ) {
			$priority = apply_filters( 'alg_wc_order_status_rules_status_change_hooks_priority', 1, $hook );
			add_action( $hook, array( $this, 'save_status_change' ), $priority, 4 );
		}
	}

	/**
	 * add_rules_processing_actions.
	 *
	 * @version 3.3.0
	 * @since   3.3.0
	 */
	function add_rules_processing_actions() {
		$hooks = get_option( 'alg_wc_order_status_rules_hooks', array( 'woocommerce_order_status_changed' ) );
		foreach ( $hooks as $hook ) {
			$priority = apply_filters( 'alg_wc_order_status_rules_hooks_priority', 10, $hook );
			add_action( $hook, array( $this, 'process_rules_for_order' ), $priority );
			if ( 'alg_wc_order_status_rules_shop_order_screen' === $hook ) {
				add_action( 'admin_head', array( $this, 'shop_order_screen' ) );
			}
			if ( 'alg_wc_order_status_rules_shop_subscription_screen' === $hook ) {
				add_action( 'admin_head', array( $this, 'shop_subscription_screen' ) );
			}
		}
	}

	/**
	 * shop_order_screen.
	 *
	 * @version 3.0.0
	 * @since   3.0.0
	 *
	 * @todo    (dev) maybe there is an easier way, e.g., use some existing action instead?
	 */
	function shop_order_screen() {
		if ( function_exists( 'get_current_screen' ) && ( $current_screen = get_current_screen() ) && 'shop_order' === $current_screen->id ) {
			do_action( 'alg_wc_order_status_rules_shop_order_screen', get_the_ID() );
		}
	}

	/**
	 * shop_subscription_screen.
	 *
	 * @version 3.3.0
	 * @since   3.3.0
	 *
	 * @todo    (dev) maybe there is an easier way, e.g., use some existing action instead?
	 */
	function shop_subscription_screen() {
		if ( function_exists( 'get_current_screen' ) && ( $current_screen = get_current_screen() ) && 'shop_subscription' === $current_screen->id ) {
			do_action( 'alg_wc_order_status_rules_shop_subscription_screen', get_the_ID() );
		}
	}

	/**
	 * on_no_history.
	 *
	 * @version 1.8.0
	 * @since   1.4.0
	 */
	function on_no_history() {
		if ( ! isset( $this->on_no_history ) ) {
			$this->on_no_history = get_option( 'alg_wc_order_status_rules_no_history', 'use_date_modified' );
		}
		return $this->on_no_history;
	}

	/**
	 * process_rules_url.
	 *
	 * @version 1.3.0
	 * @since   1.3.0
	 *
	 * @todo    (dev) optional "key" (for security)
	 * @todo    (dev) optional "rule ID to process"
	 * @todo    (dev) optional "order ID to process" (https://wordpress.org/support/topic/orderid-trigger/)
	 */
	function process_rules_url() {
		if ( isset( $_REQUEST['alg_wc_order_status_rules_process_rules'] ) ) {
			$this->process_rules();
		}
	}

	/**
	 * process_rules_manual.
	 *
	 * @version 1.2.0
	 * @since   1.2.0
	 */
	function process_rules_manual() {
		if ( 'yes' === get_option( 'alg_wc_order_status_rules_run_now', 'no' ) ) {
			update_option( 'alg_wc_order_status_rules_run_now', 'no' );
			$this->process_rules( false );
			if ( method_exists( 'WC_Admin_Settings', 'add_message' ) ) {
				WC_Admin_Settings::add_message( __( '"Run all rules now" tool executed.', 'order-status-rules-for-woocommerce' ) );
			} else {
				add_action( 'admin_notices', array( $this, 'admin_notice_process_rules_manual' ) );
			}
		}
	}

	/**
	 * admin_notice_process_rules_manual.
	 *
	 * @version 1.2.0
	 * @since   1.2.0
	 */
	function admin_notice_process_rules_manual() {
		echo '<div class="notice notice-warning is-dismissible"><p><strong>' .
			__( '"Run all rules now" tool executed.', 'order-status-rules-for-woocommerce' ) . '</strong></p></div>';
	}

	/**
	 * get_trigger_time_skip_days.
	 *
	 * @version 3.5.0
	 * @since   2.4.0
	 */
	function get_trigger_time_skip_days( $last_record_time, $trigger_time, $skip_days = false, $skip_dates = false ) {

		$skip_days  = ( count( $skip_days ) < 7 ? $skip_days : false );
		$skip_dates = array_map( 'trim', explode( ',', $skip_dates ) );

		if ( ! empty( $skip_days ) || ! empty( $skip_dates ) ) {

			$total = $last_record_time;
			$valid = 0;

			while ( $valid <= $trigger_time ) {
				$step = ( strtotime( 'tomorrow', $total ) - $total );
				if (
					( empty( $skip_days )  || ! in_array( date( 'N',   $total ), $skip_days  ) ) &&
					( empty( $skip_dates ) || ! in_array( date( 'm-d', $total ), $skip_dates ) )
				) {
					$valid += $step;
				}
				$total += $step;
			}

			$trigger_time = ( $total - $last_record_time - ( $valid - $trigger_time ) );

		}

		return $trigger_time;

	}

	/**
	 * get_time_remaining.
	 *
	 * @version 3.5.0
	 * @since   1.0.1
	 *
	 * @todo    (dev) rename `get_time_remaining()` to `get_seconds_remaining()`, `$last_record_time` to `$start`, `$trigger_time` to `$offset`, `get_trigger_time_skip_days()` to `get_offset_skip_days()`?
	 */
	function get_time_remaining( $last_record_time, $trigger_time, $skip_days = false, $skip_dates = false, $current_time = false ) {
		$current_time = ( $current_time ? $current_time : current_time( 'timestamp' ) );
		$offset       = $this->get_trigger_time_skip_days( $last_record_time, $trigger_time, $skip_days, $skip_dates );
		return ( $last_record_time + $offset - $current_time );
	}

	/**
	 * get_order_status_change_history.
	 *
	 * @version 3.2.0
	 * @since   1.4.0
	 *
	 * @todo    (dev) use `getTimestamp()`, not `getOffsetTimestamp()`
	 */
	function get_order_status_change_history( $order_id ) {

		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return false;
		}

		$data = $this->get_order_status_change_history_meta( $order );

		if ( empty( $data ) && 'do_nothing' != $this->on_no_history() ) {
			$date = ( 'use_date_created' === $this->on_no_history() ? $order->get_date_created() : $order->get_date_modified() );
			if ( $date ) {
				$data = array(
					array(
						'time' => $date->getOffsetTimestamp(),
						'from' => 'N/A',
						'to'   => $order->get_status(),
					),
				);
			}
		}

		return $data;

	}

	/**
	 * init_options.
	 *
	 * @version 3.5.3
	 * @since   1.6.0
	 *
	 * @todo    (dev) call this only once, e.g., in constructor, or on `init` action
	 * @todo    (dev) `$this->options`: rename?
	 * @todo    (dev) `$this->options['from']`: redo?
	 * @todo    (dev) code refactoring?
	 */
	function init_options() {
		if ( ! isset( $this->options ) ) {

			// Rules options: General
			$this->options = array(
				'enabled'                => get_option( 'alg_wc_order_status_rules_enabled',           array() ),
				'from'                   => get_option( 'alg_wc_order_status_rules_from',              array() ),
				'to'                     => get_option( 'alg_wc_order_status_rules_to',                array() ),
				'time_triggers'          => get_option( 'alg_wc_order_status_rules_time_trigger',      array() ),
				'time_trigger_units'     => get_option( 'alg_wc_order_status_rules_time_trigger_unit', array() ),
				'skip_days'              => get_option( 'alg_wc_order_status_rules_skip_days',         array() ),
				'skip_dates'             => get_option( 'alg_wc_order_status_rules_skip_dates',        array() ),
				'titles'                 => get_option( 'alg_wc_order_status_rules_title',             array() ),
			);

			// Rules options: Conditions
			$disabled_conditions = get_option( 'alg_wc_order_status_rules_disabled_conditions', array() );
			foreach ( $this->conditions->get() as $condition_id => $condition_title ) {
				$is_enabled = ( ! in_array( $condition_id, $disabled_conditions ) );
				switch ( $condition_id ) {
					case 'meta':
						$keys = array( 'meta_key', 'meta_value', 'meta_value_is_multiple', 'meta_compare' );
						break;
					case 'coupons':
						$keys = array( 'coupons', 'specific_coupons' );
						break;
					case 'products':
					case 'product_cats':
					case 'product_tags':
					case 'product_stock_status':
						$keys = array( $condition_id, "{$condition_id}_require_all" ); // mislabeled; should be e.g., `"{$condition_id}_action"`
						break;
					case 'min_amount':
					case 'max_amount':
						$keys = array( $condition_id, "{$condition_id}_type" );
						break;
					default:
						$keys = array( $condition_id );
				}
				foreach ( $keys as $key ) {
					$this->options[ $key ] = ( $is_enabled ? get_option( 'alg_wc_order_status_rules_' . $key, array() ) : array() );
				}
			}
			$this->options['from'] = array_slice( $this->options['from'], 0, apply_filters( 'alg_wc_order_status_rules_rules_total', 1 ), true );

			// General options
			$this->do_use_last_record = ( 'use_last_record' === get_option( 'alg_wc_order_status_rules_non_matching', 'do_nothing' ) );

		}
	}

	/**
	 * do_apply_rule.
	 *
	 * @version 2.8.0
	 * @since   1.6.0
	 *
	 * @todo    (dev) safe-check: `status_from != status_to`
	 */
	function do_apply_rule( $rule_id, $args ) {
		$this->init_options();
		$result = (
			( isset( $this->options['enabled'][ $rule_id ] ) && 'yes' === $this->options['enabled'][ $rule_id ] ) &&
			( isset( $this->options['time_triggers'][ $rule_id ] ) ) &&
			( isset( $this->options['to'][ $rule_id ] ) ) &&
			( 'wc-' . $args['order_status'] == $args['from'] && 'wc-' . $args['last_record']['to'] == $args['from'] ) &&
			$this->conditions->check( $this->options, $rule_id, $args )
		);
		return apply_filters( 'alg_wc_order_status_rules_do_apply_rule', $result, $args['order'], $rule_id, $args );
	}

	/**
	 * process_rules.
	 *
	 * @version 3.5.0
	 * @since   1.0.0
	 *
	 * @see     https://github.com/woocommerce/woocommerce/wiki/wc_get_orders-and-WC_Order_Query
	 *
	 * @todo    (dev) code refactoring: `args`, `step`, `skip`?
	 * @todo    (dev) optimization: stop on `( $last_record['to'] !== $args['order_status'] && ! $this->do_use_last_record )`
	 * @todo    (dev) optimization: `$this->options['from']`: enabled rules only
	 * @todo    (dev) optimization: meta query: `_alg_wc_order_status_change_history` not empty? (only if `'do_nothing' === $this->on_no_history()`)
	 * @todo    (dev) log: add more info
	 * @todo    (desc) use `alg_wc_order_status_rules_process_rules_time_run`
	 */
	function process_rules( $do_die = true ) {

		update_option( 'alg_wc_order_status_rules_process_rules_time_run', time() );

		if ( $this->do_debug() ) {
			$this->add_to_log( __( 'Process rules: Started', 'order-status-rules-for-woocommerce' ) .
				( ! $do_die ? ' (' . __( 'manual', 'order-status-rules-for-woocommerce' ) . ')' : '' ) );
		}

		$this->init_options();

		if ( ! empty( $this->options['from'] ) ) {
			$max_orders = get_option( 'alg_wc_order_status_rules_wc_get_orders_max_orders', -1 );
			$counter    = 0;
			$user_args  = get_option( 'alg_wc_order_status_rules_wc_get_orders_args', array() );
			$orders     = wc_get_orders( apply_filters( 'alg_wc_order_status_rules_wc_get_orders_args', array(
				'limit'    => -1,
				'status'   => $this->options['from'],
				'return'   => 'ids',
				'orderby'  => ( $user_args['orderby'] ?? 'date' ),
				'order'    => ( $user_args['order']   ?? 'DESC' ),
				'type'     => ( $user_args['type']    ?? array( 'shop_order' ) ),
			), $this ) );
			$orders     = apply_filters( 'alg_wc_order_status_rules_wc_get_orders', $orders, $this );
			foreach ( $orders as $order_id ) {
				if ( -1 != $max_orders && $counter >= $max_orders ) {
					if ( $this->do_debug() ) {
						$this->add_to_log( __( 'Process rules: Maximum number of updated orders reached', 'order-status-rules-for-woocommerce' ) );
					}
					break;
				}
				if ( $this->process_rules_for_order( $order_id ) ) {
					$counter++;
				}
			}
		}

		if ( $this->do_debug() ) {
			$this->add_to_log( __( 'Process rules: Finished', 'order-status-rules-for-woocommerce' ) );
		}

		if ( isset( $_REQUEST['alg_wc_order_status_rules_process_rules_redirect'] ) ) {
			wp_redirect( esc_url_raw( $_REQUEST['alg_wc_order_status_rules_process_rules_redirect'] ) );
			exit;
		}

		if ( $do_die ) {
			die();
		}

	}

	/**
	 * process_rules_for_order.
	 *
	 * @version 3.5.5
	 * @since   2.2.0
	 *
	 * @todo    (dev) check if it's a valid order at the beginning (i.e., `( $order = wc_get_order( $order_id ) )`)
	 */
	function process_rules_for_order( $order_id ) {

		$this->init_options();

		if ( $this->do_debug() ) {
			$this->add_to_log( sprintf( __( 'Process rules: Order #%s', 'order-status-rules-for-woocommerce' ), $order_id ) );
		}

		$status_history = $this->get_order_status_change_history( $order_id );

		if ( ! empty( $status_history ) ) {

			if ( ! ( $order = wc_get_order( $order_id ) ) || ! is_callable( array( $order, 'update_status' ) ) ) {
				if ( $this->do_debug() ) {
					$this->add_to_log( sprintf( __( 'Process rules: Skipping order #%s', 'order-status-rules-for-woocommerce' ), $order_id ) );
				}
				return false;
			}

			$status_history      = array_reverse( $status_history, true );
			$last_record         = current( $status_history );
			$args                = array( 'order_status' => $order->get_status(), 'order' => $order );
			$last_record['to']   = ( $last_record['to'] !== $args['order_status'] && $this->do_use_last_record ? $args['order_status'] : $last_record['to'] );
			$args['last_record'] = $last_record;

			// Rules loop (stops after first applied rule)
			foreach ( $this->options['from'] as $rule_id => $from ) {

				$args['from'] = $from;
				$unit         = ( $this->options['time_trigger_units'][ $rule_id ] ?? 'hour' );
				$step         = $this->get_trigger_unit_step( $unit );
				$skip_days    = ( $this->options['skip_days'][ $rule_id ] ?? false );
				$skip_dates   = ( $this->options['skip_dates'][ $rule_id ] ?? false );

				// Apply the rule
				if (
					$this->do_apply_rule( $rule_id, $args ) &&
					( $this->get_time_remaining( $last_record['time'], (int) $this->options['time_triggers'][ $rule_id ] * $step, $skip_days, $skip_dates ) <= 0 )
				) {

					// Custom actions
					do_action( 'alg_wc_order_status_rules_before_rule_applied', $order, $rule_id, $args, $this );

					// Prepare note
					$rule = sprintf( __( 'Rule #%s', 'order-status-rules-for-woocommerce' ), $rule_id ) .
						( ! empty( $this->options['titles'][ $rule_id ] ) ? ': ' . $this->options['titles'][ $rule_id ] : '' );
					$note = sprintf( __( 'Status updated by "Order Status Rules for WooCommerce" plugin (%s).', 'order-status-rules-for-woocommerce' ), $rule );

					// Update status
					$this->update_status( $order, substr( $this->options['to'][ $rule_id ], 3 ), $note );

					// Custom actions
					do_action( 'alg_wc_order_status_rules_after_rule_applied', $order, $rule_id, $args, $this );

					// Debug
					if ( $this->do_debug() ) {
						$this->add_to_log( sprintf( __( 'Process rules: Order #%s: from %s to %s (%s)', 'order-status-rules-for-woocommerce' ),
							$order_id, $from, $this->options['to'][ $rule_id ], $rule ) );
					}

					// Exit
					return true;

				}

			}

		} elseif ( $this->do_debug() ) {
			$this->add_to_log( sprintf( __( 'Process rules: Order #%s: No order status change history found', 'order-status-rules-for-woocommerce' ), $order_id ) );
		}

		return false;

	}

	/**
	 * update_status.
	 *
	 * @version 3.3.0
	 * @since   3.3.0
	 *
	 * @todo    (dev) `remove_action`: check with `has_action()`?
	 */
	function update_status( $order, $new_status, $note = '' ) {

		$had_action = array();
		foreach ( array( 'woocommerce_order_status_changed', 'woocommerce_subscription_status_changed' ) as $_hook ) {
			$had_action[ $_hook ] = remove_action( $_hook, array( $this, 'process_rules_for_order' ) );
		}

		$order->update_status( $new_status, $note );

		foreach ( $had_action as $_hook => $_had_action ) {
			if ( $_had_action ) {
				add_action( $_hook, array( $this, 'process_rules_for_order' ) );
			}
		}

	}

	/**
	 * get_trigger_unit_step.
	 *
	 * @version 1.4.0
	 * @since   1.4.0
	 */
	function get_trigger_unit_step( $unit ) {
		switch ( $unit ) {
			case 'second':
				return 1;
			case 'minute':
				return MINUTE_IN_SECONDS;
			case 'hour':
				return HOUR_IN_SECONDS;
			case 'day':
				return DAY_IN_SECONDS;
			case 'week':
				return WEEK_IN_SECONDS;
			default: // 'hour'
				return HOUR_IN_SECONDS;
		}
	}

	/**
	 * save_status_change.
	 *
	 * @version 3.2.0
	 * @since   1.0.0
	 *
	 * @todo    (dev) when `$from` doesn't exist `woocommerce_order_status_changed` is not called; check `do_action( 'woocommerce_order_status_' . $status_transition['to'], $this->get_id(), $this );`
	 * @todo    (dev) save `time()`, not `current_time()`
	 * @todo    (dev) run this on more actions, e.g., `woocommerce_checkout_order_processed`?
	 * @todo    (dev) mark status change as "changed by plugin" (vs "changed manually/otherwise")
	 */
	function save_status_change( $order_id, $from, $to, $order = false ) {

		$order = ( is_a( $order, 'WC_Order' ) ? $order : wc_get_order( $order_id ) );
		if ( ! $order ) {
			return false;
		}

		$status_history = $this->get_order_status_change_history_meta( $order );

		if ( empty( $status_history ) ) {
			$status_history = array();
		}

		$status_history[] = array(
			'time' => current_time( 'timestamp' ),
			'from' => $from,
			'to'   => $to,
		);

		$this->update_order_status_change_history_meta( $order, $status_history );

	}

	/**
	 * get_date_time_format.
	 *
	 * @version 1.4.0
	 * @since   1.4.0
	 */
	function get_date_time_format() {
		return get_option( 'date_format' ) . ' ' . get_option( 'time_format' );
	}

	/**
	 * get_order_status_change_history_meta.
	 *
	 * @version 3.2.0
	 * @since   3.2.0
	 */
	function get_order_status_change_history_meta( $order ) {

		if ( is_numeric( $order ) ) {
			$order = wc_get_order( $order );
		}

		if ( ! $order ) {
			return false;
		}

		return $order->get_meta( '_alg_wc_order_status_change_history' );

	}

	/**
	 * update_order_status_change_history_meta.
	 *
	 * @version 3.2.0
	 * @since   3.2.0
	 */
	function update_order_status_change_history_meta( $order, $data, $do_save = true ) {

		if ( is_numeric( $order ) ) {
			$order = wc_get_order( $order );
		}

		if ( ! $order ) {
			return false;
		}

		$order->update_meta_data( '_alg_wc_order_status_change_history', $data );

		if ( $do_save ) {
			$order->save_meta_data();
		}

	}

}

endif;

return new Alg_WC_Order_Status_Rules_Core();
