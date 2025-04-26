<?php
/**
 * Order Status Rules for WooCommerce - Tools Section Settings
 *
 * @version 3.3.0
 * @since   3.3.0
 *
 * @author  Algoritmika Ltd.
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_Order_Status_Rules_Settings_Tools' ) ) :

class Alg_WC_Order_Status_Rules_Settings_Tools extends Alg_WC_Order_Status_Rules_Settings_Section {

	/**
	 * Constructor.
	 *
	 * @version 3.3.0
	 * @since   3.3.0
	 */
	function __construct() {
		$this->id   = 'tools';
		$this->desc = __( 'Tools', 'order-status-rules-for-woocommerce' );
		parent::__construct();
	}

	/**
	 * get_settings.
	 *
	 * @version 3.3.0
	 * @since   3.3.0
	 */
	function get_settings() {
		return array(
			array(
				'title'    => __( 'Tools', 'order-status-rules-for-woocommerce' ),
				'type'     => 'title',
				'id'       => 'alg_wc_order_status_rules_tools_options',
				'desc'     => implode( '<br>', array( $this->get_next_scheduled_desc(), $this->get_enabled_rules_desc() ) ),
			),
			array(
				'title'    => __( 'Run all rules now', 'order-status-rules-for-woocommerce' ),
				'desc'     => '<strong>' . __( 'Run', 'order-status-rules-for-woocommerce' ) . '</strong>',
				'desc_tip' => __( 'Check the box and save changes to run all rules now.', 'order-status-rules-for-woocommerce' ),
				'id'       => 'alg_wc_order_status_rules_run_now',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_order_status_rules_tools_options',
			),
		);
	}

}

endif;

return new Alg_WC_Order_Status_Rules_Settings_Tools();
