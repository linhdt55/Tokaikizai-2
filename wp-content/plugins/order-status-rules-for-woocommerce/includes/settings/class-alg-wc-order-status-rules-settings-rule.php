<?php
/**
 * Order Status Rules for WooCommerce - Rule Section Settings
 *
 * @version 3.5.4
 * @since   2.0.0
 *
 * @author  Algoritmika Ltd.
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_Order_Status_Rules_Settings_Rule' ) ) :

class Alg_WC_Order_Status_Rules_Settings_Rule extends Alg_WC_Order_Status_Rules_Settings_Section {

	/**
	 * Constructor.
	 *
	 * @version 3.3.0
	 * @since   2.0.0
	 */
	function __construct( $rule_id ) {
		$this->id   = 'rule_' . $rule_id;
		$this->desc = strtoupper( sprintf( __( 'Rule #%d', 'order-status-rules-for-woocommerce' ), $rule_id ) );
		$this->num  = $rule_id;
		parent::__construct();
	}

	/**
	 * get_settings.
	 *
	 * @version 3.5.4
	 * @since   2.0.0
	 *
	 * @todo    (desc) add description to each subsection
	 * @todo    (dev) AJAX: Product categories, Product tags, Payment gateways, Shipping methods, User roles
	 * @todo    (dev) `alg_wc_order_status_rules()->core->init_options()`?
	 * @todo    (desc) `alg_wc_order_status_rules_skip_days`: better desc
	 * @todo    (dev) group into more subsections?
	 * @todo    (desc) `alg_wc_order_status_rules_gateways`: better desc?
	 * @todo    (feature) `alg_wc_order_status_rules_gateways`, `alg_wc_order_status_rules_products`, etc.: add "exclude" options?
	 */
	function get_settings() {
		$settings = array();

		add_action( 'admin_footer', array( $this, 'add_admin_script' ) );

		$disabled_conditions = get_option( 'alg_wc_order_status_rules_disabled_conditions', array() );

		$i = $this->num;

		// General
		$settings = array_merge( $settings, array(
			array(
				'title'    => sprintf( __( 'Rule #%d', 'order-status-rules-for-woocommerce' ), $i ) . $this->get_admin_title(),
				'type'     => 'title',
				'id'       => "alg_wc_order_status_rules_options_{$i}",
			),
			array(
				'title'    => __( 'Enable/Disable', 'order-status-rules-for-woocommerce' ),
				'desc'     => '<strong>' . sprintf( __( 'Enable rule #%s', 'order-status-rules-for-woocommerce' ), $i ) . '</strong>',
				'id'       => "alg_wc_order_status_rules_enabled[{$i}]",
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'title'    => __( 'Status from', 'order-status-rules-for-woocommerce' ),
				'id'       => "alg_wc_order_status_rules_from[{$i}]",
				'default'  => 'wc-pending',
				'type'     => 'select',
				'class'    => 'wc-enhanced-select',
				'options'  => alg_wc_order_status_rules()->core->get_statuses(),
			),
			array(
				'title'    => __( 'Status to', 'order-status-rules-for-woocommerce' ),
				'id'       => "alg_wc_order_status_rules_to[{$i}]",
				'default'  => 'wc-cancelled',
				'type'     => 'select',
				'class'    => 'wc-enhanced-select',
				'options'  => alg_wc_order_status_rules()->core->get_statuses(),
			),
			array(
				'title'    => __( 'Admin title', 'order-status-rules-for-woocommerce' ) . ' (' . __( 'optional', 'order-status-rules-for-woocommerce' ) . ')',
				'id'       => "alg_wc_order_status_rules_title[{$i}]",
				'default'  => '',
				'type'     => 'text',
			),
			array(
				'type'     => 'sectionend',
				'id'       => "alg_wc_order_status_rules_options_{$i}",
			),
		) );

		// Time
		$settings = array_merge( $settings, array(
			array(
				'title'    => __( 'Time', 'order-status-rules-for-woocommerce' ),
				'type'     => 'title',
				'id'       => "alg_wc_order_status_rules_time_options_{$i}",
			),
			array(
				'title'             => __( 'Time trigger', 'order-status-rules-for-woocommerce' ),
				'desc_tip'          => __( 'Set it to zero for an immediate status update.', 'order-status-rules-for-woocommerce' ),
				'id'                => "alg_wc_order_status_rules_time_trigger[{$i}]",
				'default'           => 1,
				'type'              => 'number',
				'custom_attributes' => array( 'min' => 0 ),
			),
			array(
				'id'       => "alg_wc_order_status_rules_time_trigger_unit[{$i}]",
				'default'  => 'hour',
				'type'     => 'select',
				'class'    => 'chosen_select',
				'options'  => array(
					'second' => __( 'second(s)', 'order-status-rules-for-woocommerce' ),
					'minute' => __( 'minute(s)', 'order-status-rules-for-woocommerce' ),
					'hour'   => __( 'hour(s)', 'order-status-rules-for-woocommerce' ),
					'day'    => __( 'day(s)', 'order-status-rules-for-woocommerce' ),
					'week'   => __( 'week(s)', 'order-status-rules-for-woocommerce' ),
				),
			),
			array(
				'title'    => __( 'Skip days', 'order-status-rules-for-woocommerce' ),
				'desc_tip' => __( 'Ignored if empty, or if all seven days are selected.', 'order-status-rules-for-woocommerce' ),
				'desc'     => $this->get_select_all_buttons(),
				'id'       => "alg_wc_order_status_rules_skip_days[{$i}]",
				'default'  => array(),
				'type'     => 'multiselect',
				'class'    => 'chosen_select',
				'options'  => array(
					1 => __( 'Monday', 'order-status-rules-for-woocommerce' ),
					2 => __( 'Tuesday', 'order-status-rules-for-woocommerce' ),
					3 => __( 'Wednesday', 'order-status-rules-for-woocommerce' ),
					4 => __( 'Thursday', 'order-status-rules-for-woocommerce' ),
					5 => __( 'Friday', 'order-status-rules-for-woocommerce' ),
					6 => __( 'Saturday', 'order-status-rules-for-woocommerce' ),
					7 => __( 'Sunday', 'order-status-rules-for-woocommerce' ),
				),
			),
			array(
				'title'    => __( 'Skip dates', 'order-status-rules-for-woocommerce' ),
				'desc_tip' => sprintf( __( 'Comma-separated list of dates in %s format (two digits with leading zeros, month - %s to %s, day - %s to %s).', 'order-status-rules-for-woocommerce' ),
						'<code>MM-DD</code>', '<code>01</code>', '<code>12</code>', '<code>01</code>', '<code>31</code>' ) . ' ' .
					__( 'Ignored if empty.', 'order-status-rules-for-woocommerce' ),
				'desc'     => __( 'Dates (e.g., holidays).', 'order-status-rules-for-woocommerce' ) . ' ' .
					sprintf( __( 'E.g.: %s', 'order-status-rules-for-woocommerce' ), '<code>01-01,12-25,12-26</code>' ),
				'id'       => "alg_wc_order_status_rules_skip_dates[{$i}]",
				'default'  => '',
				'type'     => 'text',
			),
			array(
				'type'     => 'sectionend',
				'id'       => "alg_wc_order_status_rules_time_options_{$i}",
			),
		) );

		// Minimum/Maximum Amounts
		if (
			! in_array( 'min_amount', $disabled_conditions ) ||
			! in_array( 'max_amount', $disabled_conditions ) ||
			! in_array( 'min_qty', $disabled_conditions ) ||
			! in_array( 'max_qty', $disabled_conditions )
		) {
			$settings = array_merge( $settings, array(
				array(
					'title'    => __( 'Order Minimum/Maximum Amounts', 'order-status-rules-for-woocommerce' ),
					'type'     => 'title',
					'id'       => "alg_wc_order_status_rules_min_max_amount_options_{$i}",
				),
			) );
		}
		if ( ! in_array( 'min_amount', $disabled_conditions ) ) {
			$settings = array_merge( $settings, array(
				array(
					'title'             => __( 'Minimum amount', 'order-status-rules-for-woocommerce' ),
					'desc_tip'          => __( 'Minimum order amount (subtotal).', 'order-status-rules-for-woocommerce' ) . ' ' .
						__( 'If you want the rule to be applied only for orders with subtotal equal or greater than some value, you can set it here.', 'order-status-rules-for-woocommerce' ) . ' ' .
						__( 'Ignored if empty.', 'order-status-rules-for-woocommerce' ),
					'id'                => "alg_wc_order_status_rules_min_amount[{$i}]",
					'default'           => '',
					'type'              => 'number',
					'custom_attributes' => array( 'step' => '0.000001' ),
				),
				array(
					'desc'     => __( 'Order amount type', 'order-status-rules-for-woocommerce' ),
					'desc_tip' => sprintf( __( 'Used in the "%s" option.', 'order-status-rules-for-woocommerce' ), __( 'Minimum amount', 'order-status-rules-for-woocommerce' ) ),
					'id'       => "alg_wc_order_status_rules_min_amount_type[{$i}]",
					'default'  => 'subtotal',
					'type'     => 'select',
					'class'    => 'chosen_select',
					'options'  => array(
						'subtotal' => __( 'Order subtotal', 'order-status-rules-for-woocommerce' ),
						'total'    => __( 'Order total', 'order-status-rules-for-woocommerce' ),
					),
				),
			) );
		}
		if ( ! in_array( 'max_amount', $disabled_conditions ) ) {
			$settings = array_merge( $settings, array(
				array(
					'title'             => __( 'Maximum amount', 'order-status-rules-for-woocommerce' ),
					'desc_tip'          => __( 'Maximum order amount (subtotal).', 'order-status-rules-for-woocommerce' ) . ' ' .
						__( 'If you want the rule to be applied only for orders with subtotal equal or less than some value, you can set it here.', 'order-status-rules-for-woocommerce' ) . ' ' .
						__( 'Ignored if empty.', 'order-status-rules-for-woocommerce' ),
					'id'                => "alg_wc_order_status_rules_max_amount[{$i}]",
					'default'           => '',
					'type'              => 'number',
					'custom_attributes' => array( 'step' => '0.000001' ),
				),
				array(
					'desc'     => __( 'Order amount type', 'order-status-rules-for-woocommerce' ),
					'desc_tip' => sprintf( __( 'Used in the "%s" option.', 'order-status-rules-for-woocommerce' ), __( 'Maximum amount', 'order-status-rules-for-woocommerce' ) ),
					'id'       => "alg_wc_order_status_rules_max_amount_type[{$i}]",
					'default'  => 'subtotal',
					'type'     => 'select',
					'class'    => 'chosen_select',
					'options'  => array(
						'subtotal' => __( 'Order subtotal', 'order-status-rules-for-woocommerce' ),
						'total'    => __( 'Order total', 'order-status-rules-for-woocommerce' ),
					),
				),
			) );
		}
		if ( ! in_array( 'min_qty', $disabled_conditions ) ) {
			$settings = array_merge( $settings, array(
				array(
					'title'             => __( 'Minimum quantity', 'order-status-rules-for-woocommerce' ),
					'desc_tip'          => __( 'Minimum number of items in the order.', 'order-status-rules-for-woocommerce' ) . ' ' .
						__( 'If you want the rule to be applied only for orders with number of items equal or greater than some value, you can set it here.', 'order-status-rules-for-woocommerce' ) . ' ' .
						__( 'Ignored if empty.', 'order-status-rules-for-woocommerce' ),
					'id'                => "alg_wc_order_status_rules_min_qty[{$i}]",
					'default'           => '',
					'type'              => 'number',
					'custom_attributes' => array( 'min' => 0 ),
				),
			) );
		}
		if ( ! in_array( 'max_qty', $disabled_conditions ) ) {
			$settings = array_merge( $settings, array(
				array(
					'title'             => __( 'Maximum quantity', 'order-status-rules-for-woocommerce' ),
					'desc_tip'          => __( 'Maximum number of items in the order.', 'order-status-rules-for-woocommerce' ) . ' ' .
						__( 'If you want the rule to be applied only for orders with number of items equal or less than some value, you can set it here.', 'order-status-rules-for-woocommerce' ) . ' ' .
						__( 'Ignored if empty.', 'order-status-rules-for-woocommerce' ),
					'id'                => "alg_wc_order_status_rules_max_qty[{$i}]",
					'default'           => '',
					'type'              => 'number',
					'custom_attributes' => array( 'min' => 0 ),
				),
			) );
		}
		if (
			! in_array( 'min_amount', $disabled_conditions ) ||
			! in_array( 'max_amount', $disabled_conditions ) ||
			! in_array( 'min_qty', $disabled_conditions ) ||
			! in_array( 'max_qty', $disabled_conditions )
		) {
			$settings = array_merge( $settings, array(
				array(
					'type'     => 'sectionend',
					'id'       => "alg_wc_order_status_rules_min_max_amount_options_{$i}",
				),
			) );
		}

		// Payment & Shipping
		if (
			! in_array( 'gateways', $disabled_conditions ) ||
			! in_array( 'shipping_instances', $disabled_conditions )
		) {
			$settings = array_merge( $settings, array(
				array(
					'title'    => __( 'Order Payment & Shipping', 'order-status-rules-for-woocommerce' ),
					'type'     => 'title',
					'id'       => "alg_wc_order_status_rules_payment_shipping_options_{$i}",
				),
			) );
		}
		if ( ! in_array( 'gateways', $disabled_conditions ) ) {
			$settings = array_merge( $settings, array(
				array(
					'title'    => __( 'Payment gateways', 'order-status-rules-for-woocommerce' ),
					'desc_tip' => __( 'Required payment gateways.', 'order-status-rules-for-woocommerce' ) . ' ' .
						__( 'If you want the rule to be applied only for orders with selected payment gateways, you can set them here.', 'order-status-rules-for-woocommerce' ) . ' ' .
						__( 'Ignored if empty.', 'order-status-rules-for-woocommerce' ),
					'desc'     => $this->get_select_all_buttons(),
					'id'       => "alg_wc_order_status_rules_gateways[{$i}]",
					'default'  => array(),
					'type'     => 'multiselect',
					'class'    => 'chosen_select',
					'options'  => $this->get_gateways(),
				),
			) );
		}
		if ( ! in_array( 'shipping_instances', $disabled_conditions ) ) {
			$settings = array_merge( $settings, array(
				array(
					'title'    => __( 'Shipping methods', 'order-status-rules-for-woocommerce' ),
					'desc_tip' => __( 'Required shipping methods.', 'order-status-rules-for-woocommerce' ) . ' ' .
						__( 'If you want the rule to be applied only for orders with selected shipping methods, you can set them here.', 'order-status-rules-for-woocommerce' ) . ' ' .
						__( 'Ignored if empty.', 'order-status-rules-for-woocommerce' ),
					'desc'     => $this->get_select_all_buttons(),
					'id'       => "alg_wc_order_status_rules_shipping_instances[{$i}]",
					'default'  => array(),
					'type'     => 'multiselect',
					'class'    => 'chosen_select',
					'options'  => $this->get_shipping_methods_instances(),
				),
			) );
		}
		if (
			! in_array( 'gateways', $disabled_conditions ) ||
			! in_array( 'shipping_instances', $disabled_conditions )
		) {
			$settings = array_merge( $settings, array(
				array(
					'type'     => 'sectionend',
					'id'       => "alg_wc_order_status_rules_payment_shipping_options_{$i}",
				),
			) );
		}

		// Countries
		if (
			! in_array( 'billing_countries', $disabled_conditions ) ||
			! in_array( 'shipping_countries', $disabled_conditions )
		) {
			$settings = array_merge( $settings, array(
				array(
					'title'    => __( 'Order Countries', 'order-status-rules-for-woocommerce' ),
					'type'     => 'title',
					'id'       => "alg_wc_order_status_rules_country_options_{$i}",
				),
			) );
		}
		if ( ! in_array( 'billing_countries', $disabled_conditions ) ) {
			$settings = array_merge( $settings, array(
				array(
					'title'    => __( 'Billing countries', 'order-status-rules-for-woocommerce' ),
					'desc_tip' => __( 'Required billing countries', 'order-status-rules-for-woocommerce' ) . ' ' .
						__( 'If you want the rule to be applied only for orders with selected billing countries, you can set them here.', 'order-status-rules-for-woocommerce' ) . ' ' .
						__( 'Ignored if empty.', 'order-status-rules-for-woocommerce' ),
					'desc'     => $this->get_select_all_buttons(),
					'id'       => "alg_wc_order_status_rules_billing_countries[{$i}]",
					'default'  => array(),
					'type'     => 'multiselect',
					'class'    => 'chosen_select',
					'options'  => WC()->countries->get_countries(),
				),
			) );
		}
		if ( ! in_array( 'shipping_countries', $disabled_conditions ) ) {
			$settings = array_merge( $settings, array(
				array(
					'title'    => __( 'Shipping countries', 'order-status-rules-for-woocommerce' ),
					'desc_tip' => __( 'Required shipping countries', 'order-status-rules-for-woocommerce' ) . ' ' .
						__( 'If you want the rule to be applied only for orders with selected shipping countries, you can set them here.', 'order-status-rules-for-woocommerce' ) . ' ' .
						__( 'Ignored if empty.', 'order-status-rules-for-woocommerce' ),
					'desc'     => $this->get_select_all_buttons(),
					'id'       => "alg_wc_order_status_rules_shipping_countries[{$i}]",
					'default'  => array(),
					'type'     => 'multiselect',
					'class'    => 'chosen_select',
					'options'  => WC()->countries->get_countries(),
				),
			) );
		}
		if (
			! in_array( 'billing_countries', $disabled_conditions ) ||
			! in_array( 'shipping_countries', $disabled_conditions )
		) {
			$settings = array_merge( $settings, array(
				array(
					'type'     => 'sectionend',
					'id'       => "alg_wc_order_status_rules_country_options_{$i}",
				),
			) );
		}

		// Products
		if (
			! in_array( 'products', $disabled_conditions ) ||
			! in_array( 'product_cats', $disabled_conditions ) ||
			! in_array( 'product_tags', $disabled_conditions ) ||
			! in_array( 'product_stock_status', $disabled_conditions )
		) {
			$settings = array_merge( $settings, array(
				array(
					'title'    => __( 'Order Products', 'order-status-rules-for-woocommerce' ),
					'type'     => 'title',
					'id'       => "alg_wc_order_status_rules_product_options_{$i}",
				),
			) );
		}
		if ( ! in_array( 'products', $disabled_conditions ) ) {
			$settings = array_merge( $settings, array(
				array(
					'title'             => __( 'Products', 'order-status-rules-for-woocommerce' ),
					'desc_tip'          => __( 'Required products.', 'order-status-rules-for-woocommerce' ) . ' ' .
						__( 'If you want the rule to be applied only for orders with selected products, you can set them here.', 'order-status-rules-for-woocommerce' ) . ' ' .
						__( 'Ignored if empty.', 'order-status-rules-for-woocommerce' ),
					'id'                => "alg_wc_order_status_rules_products[{$i}]",
					'default'           => array(),
					'type'              => 'multiselect',
					'class'             => 'wc-product-search',
					'options'           => $this->get_ajax_options( 'product', 'alg_wc_order_status_rules_products', $this->num ),
					'custom_attributes' => array(
						'data-placeholder' => esc_attr__( 'Search for a product&hellip;', 'woocommerce' ),
						'data-action'      => 'woocommerce_json_search_products_and_variations',
						'data-allow_clear' => true,
					),
				),
				array(
					'desc_tip' => __( '"Require all / Exclude all" means that all products in the order must match the selection (vs at least one product).', 'order-status-rules-for-woocommerce' ),
					'id'       => "alg_wc_order_status_rules_products_require_all[{$i}]", // mislabeled; should be e.g., `alg_wc_order_status_rules_products_action`
					'default'  => 'no',
					'type'     => 'select',
					'class'    => 'chosen_select',
					'options'  => array(
						'no'          => __( 'Require', 'order-status-rules-for-woocommerce' ),
						'yes'         => __( 'Require all', 'order-status-rules-for-woocommerce' ),
						'exclude'     => __( 'Exclude', 'order-status-rules-for-woocommerce' ),
						'exclude_all' => __( 'Exclude all', 'order-status-rules-for-woocommerce' ),
					),
				),
			) );
		}
		if ( ! in_array( 'product_cats', $disabled_conditions ) ) {
			$settings = array_merge( $settings, array(
				array(
					'title'    => __( 'Product categories', 'order-status-rules-for-woocommerce' ),
					'desc_tip' => __( 'Required product categories.', 'order-status-rules-for-woocommerce' ) . ' ' .
						__( 'If you want the rule to be applied only for orders with selected product categories, you can set them here.', 'order-status-rules-for-woocommerce' ) . ' ' .
						__( 'Ignored if empty.', 'order-status-rules-for-woocommerce' ),
					'desc'     => $this->get_select_all_buttons(),
					'id'       => "alg_wc_order_status_rules_product_cats[{$i}]",
					'default'  => array(),
					'type'     => 'multiselect',
					'class'    => 'chosen_select',
					'options'  => $this->get_terms( 'product_cat' ),
				),
				array(
					'desc_tip' => __( '"Require all / Exclude all" means that all products in the order must match the selection (vs at least one product).', 'order-status-rules-for-woocommerce' ),
					'id'       => "alg_wc_order_status_rules_product_cats_require_all[{$i}]", // mislabeled; should be e.g., `alg_wc_order_status_rules_product_cats_action`
					'default'  => 'no',
					'type'     => 'select',
					'class'    => 'chosen_select',
					'options'  => array(
						'no'          => __( 'Require', 'order-status-rules-for-woocommerce' ),
						'yes'         => __( 'Require all', 'order-status-rules-for-woocommerce' ),
						'exclude'     => __( 'Exclude', 'order-status-rules-for-woocommerce' ),
						'exclude_all' => __( 'Exclude all', 'order-status-rules-for-woocommerce' ),
					),
				),
			) );
		}
		if ( ! in_array( 'product_tags', $disabled_conditions ) ) {
			$settings = array_merge( $settings, array(
				array(
					'title'    => __( 'Product tags', 'order-status-rules-for-woocommerce' ),
					'desc_tip' => __( 'Required product tags.', 'order-status-rules-for-woocommerce' ) . ' ' .
						__( 'If you want the rule to be applied only for orders with selected product tags, you can set them here.', 'order-status-rules-for-woocommerce' ) . ' ' .
						__( 'Ignored if empty.', 'order-status-rules-for-woocommerce' ),
					'desc'     => $this->get_select_all_buttons(),
					'id'       => "alg_wc_order_status_rules_product_tags[{$i}]",
					'default'  => array(),
					'type'     => 'multiselect',
					'class'    => 'chosen_select',
					'options'  => $this->get_terms( 'product_tag' ),
				),
				array(
					'desc_tip' => __( '"Require all / Exclude all" means that all products in the order must match the selection (vs at least one product).', 'order-status-rules-for-woocommerce' ),
					'id'       => "alg_wc_order_status_rules_product_tags_require_all[{$i}]", // mislabeled; should be e.g., `alg_wc_order_status_rules_product_tags_action`
					'default'  => 'no',
					'type'     => 'select',
					'class'    => 'chosen_select',
					'options'  => array(
						'no'          => __( 'Require', 'order-status-rules-for-woocommerce' ),
						'yes'         => __( 'Require all', 'order-status-rules-for-woocommerce' ),
						'exclude'     => __( 'Exclude', 'order-status-rules-for-woocommerce' ),
						'exclude_all' => __( 'Exclude all', 'order-status-rules-for-woocommerce' ),
					),
				),
			) );
		}
		if ( ! in_array( 'product_stock_status', $disabled_conditions ) ) {
			$settings = array_merge( $settings, array(
				array(
					'title'    => __( 'Product stock status', 'order-status-rules-for-woocommerce' ),
					'desc_tip' => __( 'Required product stock status.', 'order-status-rules-for-woocommerce' ) . ' ' .
						__( 'If you want the rule to be applied only for orders with selected product stock status, you can set them here.', 'order-status-rules-for-woocommerce' ) . ' ' .
						__( 'Ignored if empty.', 'order-status-rules-for-woocommerce' ),
					'desc'     => $this->get_select_all_buttons(),
					'id'       => "alg_wc_order_status_rules_product_stock_status[{$i}]",
					'default'  => array(),
					'type'     => 'multiselect',
					'class'    => 'chosen_select',
					'options'  => wc_get_product_stock_status_options(),
				),
				array(
					'desc_tip' => __( '"Require all / Exclude all" means that all products in the order must match the selection (vs at least one product).', 'order-status-rules-for-woocommerce' ),
					'id'       => "alg_wc_order_status_rules_product_stock_status_require_all[{$i}]", // mislabeled; should be e.g., `alg_wc_order_status_rules_product_stock_status_action`
					'default'  => 'no',
					'type'     => 'select',
					'class'    => 'chosen_select',
					'options'  => array(
						'no'          => __( 'Require', 'order-status-rules-for-woocommerce' ),
						'yes'         => __( 'Require all', 'order-status-rules-for-woocommerce' ),
						'exclude'     => __( 'Exclude', 'order-status-rules-for-woocommerce' ),
						'exclude_all' => __( 'Exclude all', 'order-status-rules-for-woocommerce' ),
					),
				),
			) );
		}
		if (
			! in_array( 'products', $disabled_conditions ) ||
			! in_array( 'product_cats', $disabled_conditions ) ||
			! in_array( 'product_tags', $disabled_conditions ) ||
			! in_array( 'product_stock_status', $disabled_conditions )
		) {
			$settings = array_merge( $settings, array(
				array(
					'type'     => 'sectionend',
					'id'       => "alg_wc_order_status_rules_product_options_{$i}",
				),
			) );
		}

		// Coupons
		if ( ! in_array( 'coupons', $disabled_conditions ) ) {
			$settings = array_merge( $settings, array(
				array(
					'title'    => __( 'Order Coupons', 'order-status-rules-for-woocommerce' ),
					'type'     => 'title',
					'id'       => "alg_wc_order_status_rules_coupon_options_{$i}",
				),
				array(
					'title'    => __( 'Coupons', 'order-status-rules-for-woocommerce' ),
					'desc_tip' => __( 'Required coupons.', 'order-status-rules-for-woocommerce' ) . ' ' .
						__( 'If you want the rule to be applied only for orders with any or specific coupon(s), you can set it here.', 'order-status-rules-for-woocommerce' ),
					'id'       => "alg_wc_order_status_rules_coupons[{$i}]",
					'default'  => '',
					'type'     => 'select',
					'class'    => 'chosen_select',
					'options'  => array(
						''         => __( 'Do not check', 'order-status-rules-for-woocommerce' ),
						'any'      => __( 'Any coupon', 'order-status-rules-for-woocommerce' ),
						'specific' => __( 'Specific coupon(s)', 'order-status-rules-for-woocommerce' ),
						'none'     => __( 'No coupons', 'order-status-rules-for-woocommerce' ),
					),
				),
				array(
					'desc'     => __( 'Specific coupons', 'order-status-rules-for-woocommerce' ),
					'desc_tip' => __( 'Coupon codes.', 'order-status-rules-for-woocommerce' ) . ' ' .
						__( 'Can be a comma-separated list.', 'order-status-rules-for-woocommerce' ) . ' ' .
						__( 'Ignored unless the "Coupons" option is set to "Specific coupon(s)".', 'order-status-rules-for-woocommerce' ),
					'id'       => "alg_wc_order_status_rules_specific_coupons[{$i}]",
					'default'  => '',
					'type'     => 'text',
				),
				array(
					'type'     => 'sectionend',
					'id'       => "alg_wc_order_status_rules_coupon_options_{$i}",
				),
			) );
		}

		// Users
		if (
			! in_array( 'billing_emails', $disabled_conditions ) ||
			! in_array( 'user_roles', $disabled_conditions ) ||
			! in_array( 'users', $disabled_conditions ) ||
			! in_array( 'paying_customer', $disabled_conditions )
		) {
			$settings = array_merge( $settings, array(
				array(
					'title'    => __( 'Order Users', 'order-status-rules-for-woocommerce' ),
					'type'     => 'title',
					'id'       => "alg_wc_order_status_rules_user_options_{$i}",
				),
			) );
		}
		if ( ! in_array( 'billing_emails', $disabled_conditions ) ) {
			$settings = array_merge( $settings, array(
				array(
					'title'    => __( 'Billing emails', 'order-status-rules-for-woocommerce' ),
					'desc_tip' => __( 'Required billing emails.', 'order-status-rules-for-woocommerce' ) . ' ' .
						__( 'If you want the rule to be applied only for orders with selected billing emails, you can set them here.', 'order-status-rules-for-woocommerce' ) . ' ' .
						__( 'Can be a comma-separated list.', 'order-status-rules-for-woocommerce' ) . ' ' .
						__( 'Ignored if empty.', 'order-status-rules-for-woocommerce' ),
					'id'       => "alg_wc_order_status_rules_billing_emails[{$i}]",
					'default'  => '',
					'type'     => 'textarea',
					'css'      => 'min-height: 100px;',
				),
			) );
		}
		if ( ! in_array( 'user_roles', $disabled_conditions ) ) {
			$settings = array_merge( $settings, array(
				array(
					'title'    => __( 'User roles', 'order-status-rules-for-woocommerce' ),
					'desc_tip' => __( 'Required user roles.', 'order-status-rules-for-woocommerce' ) . ' ' .
						__( 'If you want the rule to be applied only for orders from selected user roles, you can set them here.', 'order-status-rules-for-woocommerce' ) . ' ' .
						__( 'Ignored if empty.', 'order-status-rules-for-woocommerce' ),
					'desc'     => $this->get_select_all_buttons(),
					'id'       => "alg_wc_order_status_rules_user_roles[{$i}]",
					'default'  => array(),
					'type'     => 'multiselect',
					'class'    => 'chosen_select',
					'options'  => $this->get_user_roles(),
				),
			) );
		}
		if ( ! in_array( 'users', $disabled_conditions ) ) {
			$settings = array_merge( $settings, array(
				array(
					'title'             => __( 'Users', 'order-status-rules-for-woocommerce' ),
					'desc_tip'          => __( 'Required users.', 'order-status-rules-for-woocommerce' ) . ' ' .
						__( 'If you want the rule to be applied only for orders from selected users, you can set them here.', 'order-status-rules-for-woocommerce' ) . ' ' .
						__( 'Ignored if empty.', 'order-status-rules-for-woocommerce' ),
					'id'                => "alg_wc_order_status_rules_users[{$i}]",
					'default'           => array(),
					'type'              => 'multiselect',
					'class'             => 'wc-customer-search',
					'options'           => $this->get_ajax_options( 'customer', 'alg_wc_order_status_rules_users', $this->num ),
					'custom_attributes' => array(
						'data-placeholder' => esc_attr__( 'Search for a user&hellip;', 'woocommerce' ),
						'data-allow_clear' => true,
						'data-exclude'     => 'alg_wc_order_status_rules', // workaround for the `wc_customer_search_guest()` function
					),
				),
			) );
		}
		if ( ! in_array( 'paying_customer', $disabled_conditions ) ) {
			$settings = array_merge( $settings, array(
				array(
					'title'    => __( 'Paying customer', 'order-status-rules-for-woocommerce' ),
					'desc_tip' => __( 'Is order user a paying customer?', 'order-status-rules-for-woocommerce' ) . ' ' .
						__( 'If you want the rule to be applied only for orders from paying or not paying customers, you can set it here.', 'order-status-rules-for-woocommerce' ),
					'id'       => "alg_wc_order_status_rules_paying_customer[{$i}]",
					'default'  => '',
					'type'     => 'select',
					'class'    => 'chosen_select',
					'options'  => array(
						''    => __( 'Do not check', 'order-status-rules-for-woocommerce' ),
						'yes' => __( 'Paying customer', 'order-status-rules-for-woocommerce' ),
						'no'  => __( 'Not paying customer', 'order-status-rules-for-woocommerce' ),
					),
				),
			) );
		}
		if (
			! in_array( 'billing_emails', $disabled_conditions ) ||
			! in_array( 'user_roles', $disabled_conditions ) ||
			! in_array( 'users', $disabled_conditions ) ||
			! in_array( 'paying_customer', $disabled_conditions )
		) {
			$settings = array_merge( $settings, array(
				array(
					'type'     => 'sectionend',
					'id'       => "alg_wc_order_status_rules_user_options_{$i}",
				),
			) );
		}

		// Dates
		if (
			! in_array( 'date_created_before', $disabled_conditions ) ||
			! in_array( 'date_created_after', $disabled_conditions )
		) {
			$settings = array_merge( $settings, array(
				array(
					'title'    => __( 'Order Dates', 'order-status-rules-for-woocommerce' ),
					'type'     => 'title',
					'id'       => "alg_wc_order_status_rules_date_options_{$i}",
				),
			) );
		}
		if ( ! in_array( 'date_created_before', $disabled_conditions ) ) {
			$settings = array_merge( $settings, array(
				array(
					'title'    => __( 'Date created before', 'order-status-rules-for-woocommerce' ),
					'desc_tip' => __( 'Date (UTC).', 'order-status-rules-for-woocommerce' ) . ' ' .
						__( 'If you want the rule to be applied only for orders created before some date, you can set it here.', 'order-status-rules-for-woocommerce' ) . ' ' .
						__( 'Ignored if empty.', 'order-status-rules-for-woocommerce' ),
					'id'       => "alg_wc_order_status_rules_date_created_before[{$i}]",
					'default'  => '',
					'type'     => 'date',
				),
			) );
		}
		if ( ! in_array( 'date_created_after', $disabled_conditions ) ) {
			$settings = array_merge( $settings, array(
				array(
					'title'    => __( 'Date created after', 'order-status-rules-for-woocommerce' ),
					'desc_tip' => __( 'Date (UTC).', 'order-status-rules-for-woocommerce' ) . ' ' .
						__( 'If you want the rule to be applied only for orders created after some date, you can set it here.', 'order-status-rules-for-woocommerce' ) . ' ' .
						__( 'Ignored if empty.', 'order-status-rules-for-woocommerce' ),
					'id'       => "alg_wc_order_status_rules_date_created_after[{$i}]",
					'default'  => '',
					'type'     => 'date',
				),
			) );
		}
		if (
			! in_array( 'date_created_before', $disabled_conditions ) ||
			! in_array( 'date_created_after', $disabled_conditions )
		) {
			$settings = array_merge( $settings, array(
				array(
					'type'     => 'sectionend',
					'id'       => "alg_wc_order_status_rules_date_options_{$i}",
				),
			) );
		}

		// Meta
		if ( ! in_array( 'meta', $disabled_conditions ) ) {
			$settings = array_merge( $settings, array(
				array(
					'title'    => __( 'Order Meta', 'order-status-rules-for-woocommerce' ),
					'type'     => 'title',
					'id'       => "alg_wc_order_status_rules_meta_options_{$i}",
				),
				array(
					'title'    => __( 'Meta', 'order-status-rules-for-woocommerce' ),
					'desc'     => __( 'Meta key', 'order-status-rules-for-woocommerce' ),
					'desc_tip' => __( 'Required order meta.', 'order-status-rules-for-woocommerce' ) . ' ' .
						__( 'If you want the rule to be applied only for orders with specific order meta value, you can set it here.', 'order-status-rules-for-woocommerce' ) . ' ' .
						__( 'Ignored if empty.', 'order-status-rules-for-woocommerce' ),
					'id'       => "alg_wc_order_status_rules_meta_key[{$i}]",
					'default'  => '',
					'type'     => 'text',
				),
				array(
					'desc'     => __( 'Meta value', 'order-status-rules-for-woocommerce' ),
					'id'       => "alg_wc_order_status_rules_meta_value[{$i}]",
					'default'  => '',
					'type'     => 'text',
				),
				array(
					'desc'     => __( 'Multiple meta values', 'order-status-rules-for-woocommerce' ),
					'desc_tip' => __( 'Allows setting multiple meta values as a comma-separated list.', 'order-status-rules-for-woocommerce' ),
					'id'       => "alg_wc_order_status_rules_meta_value_is_multiple[{$i}]",
					'default'  => 'no',
					'type'     => 'checkbox',
				),
				array(
					'desc'     => __( 'Meta compare', 'order-status-rules-for-woocommerce' ),
					'id'       => "alg_wc_order_status_rules_meta_compare[{$i}]",
					'default'  => 'equals',
					'type'     => 'select',
					'class'    => 'chosen_select',
					'options'  => array(
						'equals'     => __( 'Equals', 'order-status-rules-for-woocommerce' ),
						'not_equals' => __( 'Not equals', 'order-status-rules-for-woocommerce' ),
					),
				),
				array(
					'type'     => 'sectionend',
					'id'       => "alg_wc_order_status_rules_meta_options_{$i}",
				),
			) );
		}

		return $settings;
	}

}

endif;
