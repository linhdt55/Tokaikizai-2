<?php
/**
 * Order Status Rules for WooCommerce - Settings
 *
 * @version 3.5.4
 * @since   1.0.0
 *
 * @author  Algoritmika Ltd.
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_Settings_Order_Status_Rules' ) ) :

class Alg_WC_Settings_Order_Status_Rules extends WC_Settings_Page {

	/**
	 * Constructor.
	 *
	 * @version 3.3.0
	 * @since   1.0.0
	 */
	function __construct() {

		$this->id    = 'alg_wc_order_status_rules';
		$this->label = __( 'Order Status Rules', 'order-status-rules-for-woocommerce' );
		parent::__construct();

		// Sections
		require_once( 'class-alg-wc-order-status-rules-settings-section.php' );
		require_once( 'class-alg-wc-order-status-rules-settings-general.php' );
		require_once( 'class-alg-wc-order-status-rules-settings-rule.php' );
		for ( $rule_id = 1; $rule_id <= apply_filters( 'alg_wc_order_status_rules_rules_total', 1 ); $rule_id++ ) {
			new Alg_WC_Order_Status_Rules_Settings_Rule( $rule_id );
		}
		require_once( 'class-alg-wc-order-status-rules-settings-advanced.php' );
		require_once( 'class-alg-wc-order-status-rules-settings-tools.php' );
		require_once( 'class-alg-wc-order-status-rules-settings-my-account.php' );
		require_once( 'class-alg-wc-order-status-rules-settings-extra.php' );

	}

	/**
	 * get_settings.
	 *
	 * @version 3.5.4
	 * @since   1.0.0
	 */
	function get_settings() {
		global $current_section;

		// Settings
		$settings = apply_filters( 'woocommerce_get_settings_' . $this->id . '_' . $current_section, array() );

		// Settings Tools: Start
		$settings_tools = array();
		$settings_tools = array_merge( $settings_tools, array(
			array(
				'title'     => __( 'Settings Tools', 'order-status-rules-for-woocommerce' ),
				'type'      => 'title',
				'id'        => $this->id . '_' . $current_section . '_settings_tools',
			),
		) );

		// Settings tools: Copy settings
		if ( ( $copy_rules = $this->get_copy_rules_options() ) ) {
			$settings_tools = array_merge( $settings_tools, array(
				array(
					'title'     => __( 'Copy settings', 'order-status-rules-for-woocommerce' ),
					'desc'      => __( 'Select a rule to copy settings from and save changes.', 'order-status-rules-for-woocommerce' ),
					'desc_tip'  => __( 'Please note that there is no undo for this action. Your current rule settings will be overwritten.', 'order-status-rules-for-woocommerce' ),
					'id'        => $this->id . '_' . $current_section . '_copy_settings',
					'default'   => '',
					'type'      => 'select',
					'options'   => $copy_rules,
					'class'     => 'chosen_select',
				),
			) );
		}

		// Settings Tools: Reset section settings
		$settings_tools = array_merge( $settings_tools, array(
			array(
				'title'     => __( 'Reset section settings', 'order-status-rules-for-woocommerce' ),
				'desc'      => '<strong>' . __( 'Reset', 'order-status-rules-for-woocommerce' ) . '</strong>',
				'desc_tip'  => __( 'Check the box and save changes to reset.', 'order-status-rules-for-woocommerce' ),
				'id'        => $this->id . '_' . $current_section . '_reset',
				'default'   => 'no',
				'type'      => 'checkbox',
			),
		) );

		// Settings Tools: End
		$settings_tools = array_merge( $settings_tools, array(
			array(
				'type'      => 'sectionend',
				'id'        => $this->id . '_' . $current_section . '_settings_tools',
			),
		) );

		// Result
		return array_merge( $settings, $settings_tools );
	}

	/**
	 * get_copy_rules_options.
	 *
	 * @version 3.5.4
	 * @since   3.5.4
	 */
	function get_copy_rules_options() {
		global $current_section;

		if ( 'rule_' !== substr( $current_section, 0, 5 ) ) {
			return false;
		}

		$copy_rules = array( '' => __( 'Select a rule&hellip;', 'order-status-rules-for-woocommerce' ) );
		for ( $rule_id = 1; $rule_id <= apply_filters( 'alg_wc_order_status_rules_rules_total', 1 ); $rule_id++ ) {
			$copy_rules[ 'rule_' . $rule_id ] = strtoupper( sprintf( __( 'Rule #%d', 'order-status-rules-for-woocommerce' ), $rule_id ) );
		}
		unset( $copy_rules[ $current_section ] );

		return ( count( $copy_rules ) > 1 ? $copy_rules : false );
	}

	/**
	 * maybe_copy_settings.
	 *
	 * @version 3.5.4
	 * @since   3.5.4
	 */
	function maybe_copy_settings() {
		global $current_section;

		$copy_settings_id = $this->id . '_' . $current_section . '_copy_settings';
		if ( '' != ( $rule_id_from = get_option( $copy_settings_id, '' ) ) ) {
			update_option( $copy_settings_id, '' );

			$rule_id_from = substr( $rule_id_from, 5 );
			$rule_id_to   = substr( $current_section, 5 );

			foreach ( $this->get_settings() as $option ) {
				if ( isset( $option['id'], $option['default'] ) ) {
					$id = explode( '[', $option['id'] );
					if ( count( $id ) > 1 ) {
						$value = get_option( $id[0], array() );
						$value[ $rule_id_to ] = ( $value[ $rule_id_from ] ?? $option['default'] );
						update_option( $id[0], $value );
					}
				}
			}

			if ( method_exists( 'WC_Admin_Settings', 'add_message' ) ) {
				WC_Admin_Settings::add_message( __( 'Your settings have been copied.', 'order-status-rules-for-woocommerce' ) );
			}

		}

	}

	/**
	 * maybe_reset_settings.
	 *
	 * @version 3.5.4
	 * @since   1.0.0
	 */
	function maybe_reset_settings() {
		global $current_section;

		if ( 'yes' === get_option( $this->id . '_' . $current_section . '_reset', 'no' ) ) {

			foreach ( $this->get_settings() as $option ) {
				if ( isset( $option['id'] ) ) {
					$id = explode( '[', $option['id'] );
					if (
						count( $id ) > 1 &&
						'rule_' === substr( $current_section, 0, 5 )
					) {
						$rule_id = substr( $current_section, 5 );
						$value   = get_option( $id[0], array() );
						unset( $value[ $rule_id ] );
						update_option( $id[0], $value );
					} else {
						delete_option( $id[0] );
					}
				}
			}

			if ( method_exists( 'WC_Admin_Settings', 'add_message' ) ) {
				WC_Admin_Settings::add_message( __( 'Your settings have been reset.', 'order-status-rules-for-woocommerce' ) );
			} else {
				add_action( 'admin_notices', array( $this, 'admin_notice_settings_reset' ) );
			}

		}

	}

	/**
	 * admin_notice_settings_reset.
	 *
	 * @version 1.1.0
	 * @since   1.1.0
	 */
	function admin_notice_settings_reset() {
		echo '<div class="notice notice-warning is-dismissible"><p><strong>' .
			__( 'Your settings have been reset.', 'order-status-rules-for-woocommerce' ) . '</strong></p></div>';
	}

	/**
	 * Save settings.
	 *
	 * @version 3.5.4
	 * @since   1.0.0
	 */
	function save() {
		parent::save();
		$this->maybe_copy_settings();
		$this->maybe_reset_settings();
		do_action( 'alg_wc_order_status_rules_after_save_settings' );
	}

}

endif;

return new Alg_WC_Settings_Order_Status_Rules();
