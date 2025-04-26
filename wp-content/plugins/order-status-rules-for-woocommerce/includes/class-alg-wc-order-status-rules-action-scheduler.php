<?php
/**
 * Order Status Rules for WooCommerce - Action Scheduler Class
 *
 * @version 3.5.0
 * @since   2.3.0
 *
 * @author  Algoritmika Ltd.
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_Order_Status_Rules_Action_Scheduler' ) ) :

class Alg_WC_Order_Status_Rules_Action_Scheduler {

	/**
	 * action.
	 *
	 * @version 3.5.0
	 * @since   3.5.0
	 */
	public $action;

	/**
	 * Constructor.
	 *
	 * @version 2.3.0
	 * @since   2.3.0
	 *
	 * @see     https://actionscheduler.org/
	 */
	function __construct() {
		$this->action = 'alg_wc_order_status_rules_process_rules_as';
		if ( 'yes' === get_option( 'alg_wc_order_status_rules_plugin_enabled', 'yes' ) ) {
			if ( 'yes' === get_option( 'alg_wc_order_status_rules_use_action_scheduler', 'no' ) ) {
				add_action( 'init', array( $this, 'schedule_action' ) );
				add_action( $this->action, array( $this, 'process_rules_action_scheduler' ) );
			} else {
				add_action( 'init', array( $this, 'unschedule_action' ) );
			}
		}
	}

	/**
	 * process_rules_action_scheduler.
	 *
	 * @version 2.3.0
	 * @since   2.3.0
	 */
	function process_rules_action_scheduler( $args ) {
		alg_wc_order_status_rules()->core->process_rules( false );
	}

	/**
	 * unschedule_action.
	 *
	 * @version 2.3.0
	 * @since   2.3.0
	 */
	function unschedule_action() {
		as_unschedule_all_actions( $this->action );
	}

	/**
	 * schedule_action.
	 *
	 * @version 2.3.0
	 * @since   2.3.0
	 */
	function schedule_action() {
		if (
			function_exists( 'as_has_scheduled_action' ) &&
			( $interval_in_seconds = get_option( 'alg_wc_order_status_rules_action_scheduler_interval', 3600 ) ) &&
			false === as_has_scheduled_action( $this->action, array( $interval_in_seconds ) )
		) {
			as_unschedule_all_actions( $this->action );
			as_schedule_recurring_action( time(), $interval_in_seconds, $this->action, array( $interval_in_seconds ) );
		}
	}

}

endif;

return new Alg_WC_Order_Status_Rules_Action_Scheduler();
