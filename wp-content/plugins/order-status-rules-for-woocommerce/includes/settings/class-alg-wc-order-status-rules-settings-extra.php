<?php
/**
 * Order Status Rules for WooCommerce - Extra Section Settings
 *
 * @version 3.3.0
 * @since   3.3.0
 *
 * @author  Algoritmika Ltd.
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_Order_Status_Rules_Settings_Extra' ) ) :

class Alg_WC_Order_Status_Rules_Settings_Extra extends Alg_WC_Order_Status_Rules_Settings_Section {

	/**
	 * Constructor.
	 *
	 * @version 3.3.0
	 * @since   3.3.0
	 */
	function __construct() {
		$this->id   = 'extra';
		$this->desc = __( 'Extra', 'order-status-rules-for-woocommerce' );
		parent::__construct();
	}

	/**
	 * get_settings.
	 *
	 * @version 3.3.0
	 * @since   3.3.0
	 */
	function get_settings() {

		$order_status_options = array_merge( array( '' => __( 'No changes...', 'order-status-rules-for-woocommerce' ) ), wc_get_order_statuses() );

		$default_status_settings = array(
			array(
				'title'    => __( 'Default Order Status', 'order-status-rules-for-woocommerce' ),
				'type'     => 'title',
				'id'       => 'alg_wc_order_status_rules_default_order_status_options',
			),
			array(
				'title'    => __( 'Default order status', 'order-status-rules-for-woocommerce' ),
				'desc_tip' => sprintf( __( 'WooCommerce default: %s.', 'order-status-rules-for-woocommerce' ),
					_x( 'Pending payment', 'Order status', 'woocommerce' ) ),
				'id'       => 'alg_wc_order_status_rules_default_order_status',
				'default'  => '',
				'type'     => 'select',
				'class'    => 'wc-enhanced-select',
				'options'  => $order_status_options,
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_order_status_rules_default_order_status_options',
			),
		);

		$process_payment_settings = array(
			array(
				'title'    => __( 'Process Payment Order Status', 'order-status-rules-for-woocommerce' ),
				'desc'     => __( 'Order status updated in gateway\'s "process payment" function.', 'order-status-rules-for-woocommerce' ),
				'type'     => 'title',
				'id'       => 'alg_wc_order_status_rules_process_payment_order_status_options',
			),
			array(
				'title'    => __( 'Direct bank transfer', 'order-status-rules-for-woocommerce' ),
				'desc_tip' => sprintf( __( 'WooCommerce default: %s.', 'order-status-rules-for-woocommerce' ),
					_x( 'On hold', 'Order status', 'woocommerce' ) ),
				'id'       => 'alg_wc_order_status_rules_bacs_process_payment_order_status',
				'default'  => '',
				'type'     => 'select',
				'class'    => 'wc-enhanced-select',
				'options'  => $order_status_options,
			),
			array(
				'title'    => __( 'Check payments', 'order-status-rules-for-woocommerce' ),
				'desc_tip' => sprintf( __( 'WooCommerce default: %s.', 'order-status-rules-for-woocommerce' ),
					_x( 'On hold', 'Order status', 'woocommerce' ) ),
				'id'       => 'alg_wc_order_status_rules_cheque_process_payment_order_status',
				'default'  => '',
				'type'     => 'select',
				'class'    => 'wc-enhanced-select',
				'options'  => $order_status_options,
			),
			array(
				'title'    => __( 'Cash on delivery (COD)', 'order-status-rules-for-woocommerce' ),
				'desc_tip' => sprintf( __( 'WooCommerce default: %s or %s (if the order contains a downloadable product).', 'order-status-rules-for-woocommerce' ),
					_x( 'Processing', 'Order status', 'woocommerce' ), _x( 'On hold', 'Order status', 'woocommerce' ) ),
				'id'       => 'alg_wc_order_status_rules_cod_process_payment_order_status',
				'default'  => '',
				'type'     => 'select',
				'class'    => 'wc-enhanced-select',
				'options'  => $order_status_options,
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_order_status_rules_process_payment_order_status_options',
			),
		);

		return array_merge( $default_status_settings, $process_payment_settings );

	}

}

endif;

return new Alg_WC_Order_Status_Rules_Settings_Extra();
