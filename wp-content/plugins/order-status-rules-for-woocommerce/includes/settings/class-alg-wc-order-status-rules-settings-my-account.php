<?php
/**
 * Order Status Rules for WooCommerce - My Account Section Settings
 *
 * @version 3.3.0
 * @since   3.3.0
 *
 * @author  Algoritmika Ltd.
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_Order_Status_Rules_Settings_My_Account' ) ) :

class Alg_WC_Order_Status_Rules_Settings_My_Account extends Alg_WC_Order_Status_Rules_Settings_Section {

	/**
	 * Constructor.
	 *
	 * @version 3.3.0
	 * @since   3.3.0
	 */
	function __construct() {
		$this->id   = 'my_account';
		$this->desc = __( 'My Account', 'order-status-rules-for-woocommerce' );
		parent::__construct();
	}

	/**
	 * get_settings.
	 *
	 * @version 3.3.0
	 * @since   3.3.0
	 *
	 * @todo    (dev) "... next status change is scheduled on..."
	 */
	function get_settings() {
		return array(
			array(
				'title'    => __( '"My Account" Options', 'order-status-rules-for-woocommerce' ),
				'desc'     => __( 'This is a "bonus" feature of the plugin. As plugin tracks order status changes, we can display them on customer\'s "My Account" page.', 'order-status-rules-for-woocommerce' ),
				'type'     => 'title',
				'id'       => 'alg_wc_order_status_rules_my_account_options',
			),
			array(
				'title'    => __( 'My Account > Orders', 'order-status-rules-for-woocommerce' ),
				'desc'     => '<strong>' . __( 'Enable', 'order-status-rules-for-woocommerce' ) . '</strong>',
				'desc_tip' => __( 'Adds order status history to "My Account > Orders".', 'order-status-rules-for-woocommerce' ),
				'id'       => 'alg_wc_osr_my_account_orders_status_history_enabled',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'title'    => __( 'Position', 'order-status-rules-for-woocommerce' ),
				'id'       => 'alg_wc_osr_my_account_orders_status_history_position',
				'default'  => 'order-status',
				'type'     => 'select',
				'class'    => 'chosen_select',
				'options'  => array(
					'order-status'       => __( '"Status" column', 'order-status-rules-for-woocommerce' ),
					'alg-wc-osr-history' => __( 'New column', 'order-status-rules-for-woocommerce' ),
				),
			),
			array(
				'title'    => __( 'Column title', 'order-status-rules-for-woocommerce' ),
				'desc'     => __( 'Ignored unless "New column" is selected for the "Position".', 'order-status-rules-for-woocommerce' ),
				'id'       => 'alg_wc_osr_my_account_orders_status_history_column_title',
				'default'  => __( 'History', 'order-status-rules-for-woocommerce' ),
				'type'     => 'text',
			),
			array(
				'title'    => __( 'Templates', 'order-status-rules-for-woocommerce' ),
				'desc'     => __( 'Before', 'order-status-rules-for-woocommerce' ) . '<br>' .
					sprintf( __( 'Available placeholders: %s', 'order-status-rules-for-woocommerce' ), '<code>' . implode( '</code>, <code>', array(
							'%current_status%',
						) ) . '</code>' ),
				'id'       => 'alg_wc_osr_my_account_orders_status_history_templates[before]',
				'default'  => '%current_status%',
				'type'     => 'textarea',
			),
			array(
				'desc'     => __( 'Each record', 'order-status-rules-for-woocommerce' ) . '<br>' .
					sprintf( __( 'Available placeholders: %s', 'order-status-rules-for-woocommerce' ), '<code>' . implode( '</code>, <code>', array(
							'%record_nr%',
							'%record_date%',
							'%record_time%',
							'%status_from%',
							'%status_to%',
						) ) . '</code>' ),
				'desc_tip' => sprintf( __( '%s is a HTML code for the right arrow symbol.', 'order-status-rules-for-woocommerce' ), htmlentities( '&amp;rarr;' ) ),
				'id'       => 'alg_wc_osr_my_account_orders_status_history_templates[each_record]',
				'default'  => '<br>%status_from% &rarr; %status_to%',
				'type'     => 'textarea',
			),
			array(
				'desc'     => __( 'After', 'order-status-rules-for-woocommerce' ) . '<br>' .
					sprintf( __( 'Available placeholders: %s', 'order-status-rules-for-woocommerce' ), '<code>' . implode( '</code>, <code>', array(
							'%current_status%',
						) ) . '</code>' ),
				'id'       => 'alg_wc_osr_my_account_orders_status_history_templates[after]',
				'default'  => '',
				'type'     => 'textarea',
			),
			array(
				'title'    => __( 'Reverse status history', 'order-status-rules-for-woocommerce' ),
				'desc'     => __( 'Enable', 'order-status-rules-for-woocommerce' ),
				'id'       => 'alg_wc_osr_my_account_orders_status_history_reverse',
				'default'  => 'yes',
				'type'     => 'checkbox',
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_order_status_rules_my_account_options',
			),
		);
	}

}

endif;

return new Alg_WC_Order_Status_Rules_Settings_My_Account();
