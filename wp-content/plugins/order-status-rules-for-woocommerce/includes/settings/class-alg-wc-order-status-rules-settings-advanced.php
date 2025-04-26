<?php
/**
 * Order Status Rules for WooCommerce - Advanced Section Settings
 *
 * @version 3.5.1
 * @since   1.5.0
 *
 * @author  Algoritmika Ltd.
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_Order_Status_Rules_Settings_Advanced' ) ) :

class Alg_WC_Order_Status_Rules_Settings_Advanced extends Alg_WC_Order_Status_Rules_Settings_Section {

	/**
	 * Constructor.
	 *
	 * @version 2.0.0
	 * @since   1.5.0
	 */
	function __construct() {
		$this->id   = 'advanced';
		$this->desc = __( 'Advanced', 'order-status-rules-for-woocommerce' );
		parent::__construct();
	}

	/**
	 * get_settings.
	 *
	 * @version 3.5.1
	 * @since   1.5.0
	 *
	 * @todo    (dev) split into sections, e.g., "Compatibility"
	 * @todo    (dev) `woocommerce_can_subscription_be_updated_to_` (for the "Unable to change subscription status to ..." error)
	 * @todo    (dev) `alg_wc_order_status_rules_hooks`: update default, e.g., add `woocommerce_checkout_order_processed`
	 * @todo    (dev) `alg_wc_order_status_rules_hooks`: `woocommerce_payment_complete`
	 * @todo    (dev) `alg_wc_order_status_rules_hooks`: `woocommerce_order_status_pending`, etc. (`woocommerce_order_status_ . $status_to`)
	 * @todo    (dev) `alg_wc_order_status_rules_hooks`: order updated action, etc.?
	 * @todo    (dev) `alg_wc_order_status_rules_non_matching`: default to `use_last_record`?
	 * @todo    (dev) `alg_wc_order_status_rules_non_matching`: add `use_date_created` and/or `use_date_modified` options
	 * @todo    (desc) `alg_wc_order_status_rules_non_matching`: better desc?
	 * @todo    (dev) Orders sorting: Order by: add `none`, `name`, `type`?
	 * @todo    (desc) Orders sorting: better desc?
	 * @todo    (desc) `alg_wc_order_status_rules_compatibility_doctreat`: better desc, e.g., add link to the theme?
	 * @todo    (desc) `alg_wc_order_status_rules_disabled_conditions`: better desc?
	 */
	function get_settings() {

		add_action( 'admin_footer', array( $this, 'add_admin_script' ) );

		$advanced_settings = array(
			array(
				'title'    => __( 'Advanced Options', 'order-status-rules-for-woocommerce' ),
				'type'     => 'title',
				'id'       => 'alg_wc_order_status_rules_advanced_options',
			),
			array(
				'title'    => __( 'Save status change on', 'order-status-rules-for-woocommerce' ),
				'id'       => 'alg_wc_order_status_rules_status_change_hooks',
				'default'  => array( 'woocommerce_order_status_changed' ),
				'type'     => 'multiselect',
				'class'    => 'chosen_select',
				'options'  => apply_filters( 'alg_wc_order_status_rules_status_change_hooks', array(
					'woocommerce_order_status_changed'        => __( 'Order status changed', 'order-status-rules-for-woocommerce' ),
					'woocommerce_subscription_status_changed' => __( 'Subscription status changed', 'order-status-rules-for-woocommerce' ),
				) ),
			),
			array(
				'title'    => __( 'Process rules on', 'order-status-rules-for-woocommerce' ),
				'id'       => 'alg_wc_order_status_rules_hooks',
				'default'  => array( 'woocommerce_order_status_changed' ),
				'type'     => 'multiselect',
				'class'    => 'chosen_select',
				'options'  => apply_filters( 'alg_wc_order_status_rules_hooks', array(
					'woocommerce_order_status_changed'                   => __( 'Order status changed', 'order-status-rules-for-woocommerce' ),
					'woocommerce_checkout_order_processed'               => __( 'Checkout order processed', 'order-status-rules-for-woocommerce' ),
					'woocommerce_thankyou'                               => __( '"Thank you" (i.e., "Order received") page', 'order-status-rules-for-woocommerce' ),
					'alg_wc_order_status_rules_shop_order_screen'        => __( 'Admin "Edit order" page', 'order-status-rules-for-woocommerce' ),
					'alg_wc_order_status_rules_shop_subscription_screen' => __( 'Admin "Edit subscription" page', 'order-status-rules-for-woocommerce' ),
					'woocommerce_subscription_status_changed'            => __( 'Subscription status changed', 'order-status-rules-for-woocommerce' ),
				) ),
			),
			array(
				'title'    => __( 'Statuses', 'order-status-rules-for-woocommerce' ),
				'id'       => 'alg_wc_order_status_rules_status_functions',
				'default'  => array( 'wc_get_order_statuses' ),
				'type'     => 'multiselect',
				'class'    => 'chosen_select',
				'options'  => apply_filters( 'alg_wc_order_status_rules_status_functions', array(
					'wc_get_order_statuses'         => __( 'WooCommerce Order Statuses', 'order-status-rules-for-woocommerce' ),
					'wcs_get_subscription_statuses' => __( 'WooCommerce Subscription Statuses', 'order-status-rules-for-woocommerce' ),
				) ),
			),
			array(
				'title'    => __( 'Meta box', 'order-status-rules-for-woocommerce' ),
				'desc_tip' => __( 'Adds admin "Order Status History" meta box.', 'order-status-rules-for-woocommerce' ),
				'id'       => 'alg_wc_order_status_rules_meta_box_screen',
				'default'  => array( 'shop_order' ),
				'type'     => 'multiselect',
				'class'    => 'chosen_select',
				'options'  => array(
					'shop_order'        => __( 'Orders', 'order-status-rules-for-woocommerce' ),
					'shop_subscription' => __( 'Subscriptions', 'order-status-rules-for-woocommerce' ),
				),
			),
			array(
				'title'    => __( 'Allow rules processing via URL', 'order-status-rules-for-woocommerce' ),
				'desc'     => __( 'Enable', 'order-status-rules-for-woocommerce' ),
				'desc_tip' => sprintf( __( 'This will allow to initiate all rules processing via URL: %s.', 'order-status-rules-for-woocommerce' ),
					'<code>' . add_query_arg( 'alg_wc_order_status_rules_process_rules', '', get_site_url() ) . '</code>' ) . '<br>' .
					sprintf( __( 'For example, this could be useful if you are going to disable %s and use "real" (i.e., server) cron jobs instead.', 'order-status-rules-for-woocommerce' ),
						'<span style="text-decoration:underline;">' . __( 'Periodical Processing Options', 'order-status-rules-for-woocommerce' ) . '</span>' ),
				'id'       => 'alg_wc_order_status_rules_allow_url',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'title'    => __( 'Disabled conditions', 'order-status-rules-for-woocommerce' ),
				'desc_tip' => __( 'Removes the selected conditions from each rule\'s settings.', 'order-status-rules-for-woocommerce' ),
				'desc'     => $this->get_select_all_buttons(),
				'id'       => 'alg_wc_order_status_rules_disabled_conditions',
				'default'  => array(),
				'type'     => 'multiselect',
				'class'    => 'chosen_select',
				'options'  => alg_wc_order_status_rules()->core->conditions->get(),
			),
			array(
				'title'    => __( 'Debug', 'order-status-rules-for-woocommerce' ),
				'desc'     => __( 'Enable', 'order-status-rules-for-woocommerce' ),
				'desc_tip' => sprintf( __( 'Will add a log to %s.', 'order-status-rules-for-woocommerce' ),
					'<a target="_blank" href="' . admin_url( 'admin.php?page=wc-status&tab=logs' ) . '">' .
						__( 'WooCommerce > Status > Logs', 'order-status-rules-for-woocommerce' ) . '</a>' ),
				'id'       => 'alg_wc_order_status_rules_debug',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_order_status_rules_advanced_options',
			),
		);

		$orders_query_settings = array(
			array(
				'title'             => __( 'Orders Query Options', 'order-status-rules-for-woocommerce' ),
				'desc'              => __( 'Affects "Periodical Processing" options, "Rules processing via URL" option and "Run all rules now" tool.', 'order-status-rules-for-woocommerce' ),
				'type'              => 'title',
				'id'                => 'alg_wc_order_status_rules_orders_query_options',
			),
			array(
				'title'             => __( 'Order types', 'order-status-rules-for-woocommerce' ),
				'id'                => 'alg_wc_order_status_rules_wc_get_orders_args[type]',
				'default'           => array( 'shop_order' ),
				'type'              => 'multiselect',
				'class'             => 'chosen_select',
				'options'           => array(
					'shop_order'        => __( 'Orders', 'order-status-rules-for-woocommerce' ),
					'shop_subscription' => __( 'Subscriptions', 'order-status-rules-for-woocommerce' ),
				),
			),
			array(
				'title'             => __( 'Orders sorting', 'order-status-rules-for-woocommerce' ),
				'desc'              => __( 'Order by', 'order-status-rules-for-woocommerce' ),
				'id'                => 'alg_wc_order_status_rules_wc_get_orders_args[orderby]',
				'default'           => 'date',
				'type'              => 'select',
				'class'             => 'chosen_select',
				'options'           => array(
					'ID'       => __( 'ID', 'order-status-rules-for-woocommerce' ),
					'rand'     => __( 'Random', 'order-status-rules-for-woocommerce' ),
					'date'     => __( 'Date', 'order-status-rules-for-woocommerce' ),
					'modified' => __( 'Modified', 'order-status-rules-for-woocommerce' ),
				),
			),
			array(
				'desc'              => __( 'Order', 'order-status-rules-for-woocommerce' ),
				'id'                => 'alg_wc_order_status_rules_wc_get_orders_args[order]',
				'default'           => 'DESC',
				'type'              => 'select',
				'class'             => 'chosen_select',
				'options'           => array(
					'DESC' => __( 'Descending', 'order-status-rules-for-woocommerce' ),
					'ASC'  => __( 'Ascending', 'order-status-rules-for-woocommerce' ),
				),
			),
			array(
				'title'             => __( 'Max orders', 'order-status-rules-for-woocommerce' ),
				'desc_tip'          => __( 'Maximum number of orders to update in a single run.', 'order-status-rules-for-woocommerce' ) . ' ' .
					__( 'Set to -1 to update all eligible orders at once.', 'order-status-rules-for-woocommerce' ),
				'id'                => 'alg_wc_order_status_rules_wc_get_orders_max_orders',
				'default'           => -1,
				'type'              => 'number',
				'custom_attributes' => array( 'min' => -1 ),
			),
			array(
				'type'              => 'sectionend',
				'id'                => 'alg_wc_order_status_rules_orders_query_options',
			),
		);

		$status_history_settings = array(
			array(
				'title'    => __( 'Order Status History Options', 'order-status-rules-for-woocommerce' ),
				'type'     => 'title',
				'id'       => 'alg_wc_order_status_rules_status_history_options',
			),
			array(
				'title'    => __( 'Empty order status history', 'order-status-rules-for-woocommerce' ),
				'desc_tip' => __( 'Plugin must be enabled at the time order status change occurs, so there is no order status change history on initial plugin install. This can be solved by using order creation (or modification) date instead (i.e., instead of real status change date). This is ignored for orders with available real status change history.', 'order-status-rules-for-woocommerce' ),
				'id'       => 'alg_wc_order_status_rules_no_history',
				'default'  => 'use_date_modified',
				'type'     => 'select',
				'class'    => 'chosen_select',
				'options'  => array(
					'do_nothing'        => __( 'Do nothing', 'order-status-rules-for-woocommerce' ),
					'use_date_created'  => __( 'Use order date created', 'order-status-rules-for-woocommerce' ),
					'use_date_modified' => __( 'Use order date modified', 'order-status-rules-for-woocommerce' ),
				),
			),
			array(
				'title'    => __( 'Non-matching order status', 'order-status-rules-for-woocommerce' ),
				'desc_tip' => __( 'If order status is not properly changed (e.g., by some plugin), it may happen that current order status does not match the last record in order status change history.', 'order-status-rules-for-woocommerce' ) . ' ' .
					__( 'In this case order status rules will not be applied.', 'order-status-rules-for-woocommerce' ) . ' ' .
					__( 'You can change this behaviour here.', 'order-status-rules-for-woocommerce' ),
				'id'       => 'alg_wc_order_status_rules_non_matching',
				'default'  => 'do_nothing',
				'type'     => 'select',
				'class'    => 'chosen_select',
				'options'  => array(
					'do_nothing'        => __( 'Do nothing', 'order-status-rules-for-woocommerce' ),
					'use_last_record'   => __( 'Use latest record anyway', 'order-status-rules-for-woocommerce' ),
				),
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_order_status_rules_status_history_options',
			),
		);

		$periodical_settings = array(
			array(
				'title'    => __( 'Periodical Processing Options', 'order-status-rules-for-woocommerce' ),
				'desc'     => __( 'Although it\'s possible to enable both periodical processing options, we recommend enabling only one of them.', 'order-status-rules-for-woocommerce' ) . '<br>' .
					sprintf( __( 'If you are going to disable both periodical processing options, you may want to enable the %s option and set up "real" (i.e., server) cron job.', 'order-status-rules-for-woocommerce' ),
						'<span style="text-decoration:underline;">' . __( 'Allow rules processing via URL', 'order-status-rules-for-woocommerce' ) . '</span>' ) . ' ' .
					sprintf( __( 'Also you can use our %s tool manually.', 'order-status-rules-for-woocommerce' ),
						'<a target="_blank" href="' . admin_url( 'admin.php?page=wc-settings&tab=alg_wc_order_status_rules&section' ) . '">' . __( 'Run all rules now', 'order-status-rules-for-woocommerce' ) . '</a>' ) .
					( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ?
						'<br>' . '<strong>' . sprintf( __( 'Crons (%s) are disabled on your site!', 'order-status-rules-for-woocommerce' ), '<code>DISABLE_WP_CRON</code>' ) . '</strong>' : '' ),
				'type'     => 'title',
				'id'       => 'alg_wc_order_status_rules_periodical_options',
			),
			array(
				'title'    => __( 'Use WP cron', 'order-status-rules-for-woocommerce' ),
				'desc'     => __( 'Enable', 'order-status-rules-for-woocommerce' ),
				'desc_tip' => sprintf( __( 'This will use %s to process the rules periodically.', 'order-status-rules-for-woocommerce' ),
						'<a href="https://developer.wordpress.org/plugins/cron/" target="_blank">' .
							__( 'WordPress crons', 'order-status-rules-for-woocommerce' ) . '</a>' ),
				'id'       => 'alg_wc_order_status_rules_use_wp_cron',
				'default'  => 'yes',
				'type'     => 'checkbox',
			),
			array(
				'desc'     => __( 'Interval', 'order-status-rules-for-woocommerce' ),
				'id'       => 'alg_wc_order_status_rules_wp_cron_schedule',
				'default'  => 'hourly',
				'type'     => 'select',
				'class'    => 'chosen_select',
				'options'  => alg_wc_order_status_rules()->core->crons->get_schedules(),
			),
			array(
				'title'    => __( 'Use Action Scheduler', 'order-status-rules-for-woocommerce' ),
				'desc'     => __( 'Enable', 'order-status-rules-for-woocommerce' ),
				'desc_tip' => sprintf( __( 'This will use %s to process the rules periodically.', 'order-status-rules-for-woocommerce' ),
						'<a href="https://actionscheduler.org/" target="_blank">' .
							__( 'Action Scheduler', 'order-status-rules-for-woocommerce' ) . '</a>' ) .
					'<br>* ' . sprintf( __( 'Action Scheduler has a built in <a href="%s" target="_blank">administration screen</a> for monitoring, debugging and manually triggering scheduled actions. Search for the %s hook there.', 'order-status-rules-for-woocommerce' ),
						admin_url( 'admin.php?page=wc-status&tab=action-scheduler' ), '<code>' . alg_wc_order_status_rules()->core->action_scheduler->action . '</code>' ),
				'id'       => 'alg_wc_order_status_rules_use_action_scheduler',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'desc'     => __( 'Interval (in seconds)', 'order-status-rules-for-woocommerce' ),
				'id'       => 'alg_wc_order_status_rules_action_scheduler_interval',
				'default'  => 3600,
				'type'     => 'number',
				'custom_attributes'  => array( 'min' => 60 ),
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_order_status_rules_periodical_options',
			),
		);

		$compatibility_settings = array(
			array(
				'title'    => __( 'Compatibility Options', 'order-status-rules-for-woocommerce' ),
				'type'     => 'title',
				'id'       => 'alg_wc_order_status_rules_compatibility_options',
			),
			array(
				'title'    => __( '"Doctreat" theme', 'order-status-rules-for-woocommerce' ),
				'desc'     => __( 'Enable', 'order-status-rules-for-woocommerce' ),
				'desc_tip' => __( 'For the "Doctreat - Doctors Directory WordPress Theme" by AmentoTech.', 'order-status-rules-for-woocommerce' ),
				'id'       => 'alg_wc_order_status_rules_compatibility_doctreat',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_order_status_rules_compatibility_options',
			),
		);

		return array_merge(
			$advanced_settings,
			$orders_query_settings,
			$status_history_settings,
			$periodical_settings,
			$compatibility_settings
		);
	}

}

endif;

return new Alg_WC_Order_Status_Rules_Settings_Advanced();
