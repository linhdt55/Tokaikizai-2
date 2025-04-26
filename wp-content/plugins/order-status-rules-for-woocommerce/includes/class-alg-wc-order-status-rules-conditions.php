<?php
/**
 * Order Status Rules for WooCommerce - Conditions Class
 *
 * @version 3.5.3
 * @since   2.8.0
 *
 * @author  Algoritmika Ltd.
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_Order_Status_Rules_Conditions' ) ) :

class Alg_WC_Order_Status_Rules_Conditions {

	/**
	 * Constructor.
	 *
	 * @version 2.8.0
	 * @since   2.8.0
	 */
	function __construct() {
		return true;
	}

	/**
	 * get.
	 *
	 * @version 2.9.0
	 * @since   2.6.0
	 *
	 * @todo    (feature) "Order type", e.g., "Any", "Order", "Subscription"
	 */
	function get() {
		return array(
			'min_amount'           => __( 'Minimum amount', 'order-status-rules-for-woocommerce' ),
			'max_amount'           => __( 'Maximum amount', 'order-status-rules-for-woocommerce' ),
			'min_qty'              => __( 'Minimum quantity', 'order-status-rules-for-woocommerce' ),
			'max_qty'              => __( 'Maximum quantity', 'order-status-rules-for-woocommerce' ),
			'gateways'             => __( 'Payment gateways', 'order-status-rules-for-woocommerce' ),
			'shipping_instances'   => __( 'Shipping methods', 'order-status-rules-for-woocommerce' ),
			'billing_countries'    => __( 'Billing country', 'order-status-rules-for-woocommerce' ),
			'shipping_countries'   => __( 'Shipping country', 'order-status-rules-for-woocommerce' ),
			'products'             => __( 'Products', 'order-status-rules-for-woocommerce' ),
			'product_cats'         => __( 'Product categories', 'order-status-rules-for-woocommerce' ),
			'product_tags'         => __( 'Product tags', 'order-status-rules-for-woocommerce' ),
			'product_stock_status' => __( 'Product stock status', 'order-status-rules-for-woocommerce' ),
			'coupons'              => __( 'Coupons', 'order-status-rules-for-woocommerce' ),
			'billing_emails'       => __( 'Billing emails', 'order-status-rules-for-woocommerce' ),
			'user_roles'           => __( 'User roles', 'order-status-rules-for-woocommerce' ),
			'users'                => __( 'Users', 'order-status-rules-for-woocommerce' ),
			'paying_customer'      => __( 'Paying customer', 'order-status-rules-for-woocommerce' ),
			'meta'                 => __( 'Meta', 'order-status-rules-for-woocommerce' ),
			'date_created_before'  => __( 'Date created before', 'order-status-rules-for-woocommerce' ),
			'date_created_after'   => __( 'Date created after', 'order-status-rules-for-woocommerce' ),
		);
	}

	/**
	 * check_min_max_amounts.
	 *
	 * @version 3.5.0
	 * @since   2.8.0
	 *
	 * @todo    (dev) calculate only if needed, i.e., if the condition(s) is enabled, etc.
	 */
	function check_min_max_amounts( $options, $rule_id, $order ) {

		// Get order amount, e.g., subtotal
		foreach ( array( 'min_amount', 'max_amount' ) as $condition_id ) {
			$amount_type = ( $options[ "{$condition_id}_type" ][ $rule_id ] ?? 'subtotal' );
			switch ( $amount_type ) {
				case 'total':
					$amount[ $condition_id ] = apply_filters( 'alg_wc_order_status_rules_order_amount', ( float ) $order->get_total(),
						$options, $rule_id, $order, $condition_id );
					break;
				default: // 'subtotal':
					$amount[ $condition_id ] = apply_filters( 'alg_wc_order_status_rules_order_amount', $order->get_subtotal(),
						$options, $rule_id, $order, $condition_id );
			}
		}

		// Get order quantity
		$quantity = apply_filters( 'alg_wc_order_status_rules_order_quantity', $order->get_item_count(), $options, $rule_id, $order );

		// Check order amount and quantity
		return (

			// Amount
			( ! isset( $options['min_amount'][ $rule_id ] ) || '' === $options['min_amount'][ $rule_id ] || $amount['min_amount'] >= $options['min_amount'][ $rule_id ] ) &&
			( ! isset( $options['max_amount'][ $rule_id ] ) || '' === $options['max_amount'][ $rule_id ] || $amount['max_amount'] <= $options['max_amount'][ $rule_id ] ) &&

			// Quantity
			( ! isset( $options['min_qty'][ $rule_id ] )    || '' === $options['min_qty'][ $rule_id ]    || $quantity >= $options['min_qty'][ $rule_id ] ) &&
			( ! isset( $options['max_qty'][ $rule_id ] )    || '' === $options['max_qty'][ $rule_id ]    || $quantity <= $options['max_qty'][ $rule_id ] )

		);
	}

	/**
	 * check_gateways.
	 *
	 * @version 2.8.0
	 * @since   2.8.0
	 */
	function check_gateways( $options, $rule_id, $order ) {
		return ( empty( $options['gateways'][ $rule_id ] ) ||
			in_array( ( is_callable( array( $order, 'get_payment_method' ) ) ? $order->get_payment_method() : '' ), $options['gateways'][ $rule_id ] ) );
	}

	/**
	 * get_shipping_method_instance_id.
	 *
	 * @version 2.1.0
	 * @since   2.1.0
	 */
	function get_shipping_method_instance_id( $shipping_method ) {
		return $shipping_method->get_instance_id();
	}

	/**
	 * check_shipping_methods.
	 *
	 * @version 2.8.0
	 * @since   2.8.0
	 */
	function check_shipping_methods( $options, $rule_id, $order ) {
		if ( ! empty( $options['shipping_instances'][ $rule_id ] ) ) {
			$shipping_methods   = ( is_callable( array( $order, 'get_shipping_methods' ) ) ? $order->get_shipping_methods() : array() );
			$shipping_instances = ( ! empty( $shipping_methods ) ? array_map( array( $this, 'get_shipping_method_instance_id' ), $shipping_methods ) : array() );
			if ( ! $this->is_array_intersect( $shipping_instances, $options['shipping_instances'][ $rule_id ] ) ) {
				return false;
			}
		}
		return true;
	}

	/**
	 * check_data.
	 *
	 * @version 2.9.0
	 * @since   2.8.0
	 *
	 * @todo    (feature) states?
	 */
	function check_data( $options, $rule_id, $order ) {
		return (

			( empty( $options['billing_countries'][ $rule_id ] )  ||
				in_array( ( is_callable( array( $order, 'get_billing_country' ) )  ? $order->get_billing_country()  : '' ),
					$options['billing_countries'][ $rule_id ] ) ) &&

			( empty( $options['shipping_countries'][ $rule_id ] ) ||
				in_array( ( is_callable( array( $order, 'get_shipping_country' ) ) ? $order->get_shipping_country() : '' ),
					$options['shipping_countries'][ $rule_id ] ) ) &&

			( empty( $options['billing_emails'][ $rule_id ] ) ||
				in_array( ( is_callable( array( $order, 'get_billing_email' ) )    ? $order->get_billing_email()    : '' ),
					array_map( 'trim', explode( ',', $options['billing_emails'][ $rule_id ] ) ) ) )

		);
	}

	/**
	 * check_order_products.
	 *
	 * @version 2.9.0
	 * @since   1.6.0
	 */
	function check_order_products( $order, $values, $type, $do_check_all, $do_exclude ) {
		$is_empty_order = true;

		// Go through order's products
		foreach ( $order->get_items() as $item ) {
			$is_empty_order = false;

			// Type
			switch ( $type ) {
				case 'product_stock_status':
					$product = wc_get_product( ( ! empty( $item['variation_id'] ) ? $item['variation_id'] : $item['product_id'] ) );
					$res = ( $product && in_array( $product->get_stock_status(), $values ) );
					break;
				case 'product_cats':
					$res = ( has_term( $values, 'product_cat', $item['product_id'] ) );
					break;
				case 'product_tags':
					$res = ( has_term( $values, 'product_tag', $item['product_id'] ) );
					break;
				default: // 'products'
					$res = ( in_array( $item['product_id'], $values ) || in_array( $item['variation_id'], $values ) );
					break;
			}

			if ( $res ) {
				// Match
				if ( ! $do_check_all ) {
					return ( $do_exclude ?
						false : // Exclude (match, do NOT check all)
						true    // Require (match, do NOT check all)
					);
				}
			} else {
				// Non-match
				if ( $do_check_all ) {
					return ( $do_exclude ?
						true :  // Exclude (non-match, do check all)
						false   // Require (non-match, do check all)
					);
				}
			}

		}

		// Final result
		return ( $is_empty_order ?
			( $do_exclude ?
				true :          // Exclude (empty order)
				false           // Require (empty order)
			) :
			( $do_exclude ?
				( $do_check_all ?
					false :     // Exclude (do check all)
					true        // Exclude (do NOT check all)
				) :
				( $do_check_all ?
					true :      // Require (do check all)
					false       // Require (do NOT check all)
				)
			)
		);

	}

	/**
	 * check_products.
	 *
	 * @version 3.5.0
	 * @since   2.8.0
	 */
	function check_products( $options, $rule_id, $order ) {
		foreach ( array( 'products', 'product_cats', 'product_tags', 'product_stock_status' ) as $type ) {
			if ( ! empty( $options[ $type ][ $rule_id ] ) ) {
				$action       = ( $options[ $type . '_require_all' ][ $rule_id ] ?? 'no' ); // mislabeled; should be e.g., `$type . '_action'`
				$do_check_all = ( in_array( $action, array( 'yes',     'exclude_all' ) ) );
				$do_exclude   = ( in_array( $action, array( 'exclude', 'exclude_all' ) ) );
				if ( ! $this->check_order_products( $order, $options[ $type ][ $rule_id ], $type, $do_check_all, $do_exclude ) ) {
					return false;
				}
			}
		}
		return true;
	}

	/**
	 * check_order_coupons.
	 *
	 * @version 2.8.1
	 * @since   2.8.0
	 */
	function check_order_coupons( $order, $coupons, $specific_coupons ) {
		$order_coupons = $order->get_coupon_codes();
		switch ( $coupons ) {
			case 'any':
				return ( ! empty( $order_coupons ) );
			case 'none':
				return ( empty( $order_coupons ) );
			case 'specific':
				$specific_coupons = array_map( 'trim', explode( ',', $specific_coupons ) );
				return $this->is_array_intersect( $order_coupons, $specific_coupons );
		}
		return true;
	}

	/**
	 * check_coupons.
	 *
	 * @version 3.5.0
	 * @since   2.8.0
	 */
	function check_coupons( $options, $rule_id, $order ) {
		return ( empty( $options['coupons'][ $rule_id ] ) ||
			$this->check_order_coupons(
				$order,
				$options['coupons'][ $rule_id ],
				( $options['specific_coupons'][ $rule_id ] ?? '' )
			)
		);
	}

	/**
	 * handle_guest_user_role.
	 *
	 * @version 1.9.0
	 * @since   1.9.0
	 */
	function handle_guest_user_role( $value ) {
		return ( '' === $value ? 'guest' : $value );
	}

	/**
	 * check_user.
	 *
	 * @version 3.5.2
	 * @since   1.9.0
	 *
	 * @todo    (dev) `paying_customer`: check if has any previous orders (vs `get_is_paying_customer()`)?
	 */
	function check_user( $user, $values, $type ) {
		switch ( $type ) {

			case 'user_id':
				$user_id = ( $user && 0 != $user->ID ? $user->ID : 'guest' );
				return in_array( $user_id, $values );

			case 'user_roles':
				$user_roles = ( $user && ! empty( $user->roles ) ?
					array_map( array( $this, 'handle_guest_user_role' ), $user->roles ) :
					array( 'guest' )
				);
				return $this->is_array_intersect( $user_roles, $values );

			case 'paying_customer':
				$customer = ( $user ? new WC_Customer( $user->ID ) : false );
				$is_paying_customer = ( $customer ? $customer->get_is_paying_customer() : false );
				return (
					( 'yes' === $values &&   $is_paying_customer ) ||
					( 'no'  === $values && ! $is_paying_customer )
				);

		}
	}

	/**
	 * check_users.
	 *
	 * @version 2.8.0
	 * @since   2.8.0
	 */
	function check_users( $options, $rule_id, $order ) {
		$user = $order->get_user();
		return (
			( empty( $options['user_roles'][ $rule_id ] )      || $this->check_user( $user, $options['user_roles'][ $rule_id ],      'user_roles' ) ) &&
			( empty( $options['users'][ $rule_id ] )           || $this->check_user( $user, $options['users'][ $rule_id ],           'user_id' ) ) &&
			( empty( $options['paying_customer'][ $rule_id ] ) || $this->check_user( $user, $options['paying_customer'][ $rule_id ], 'paying_customer' ) )
		);
	}

	/**
	 * check_order_meta.
	 *
	 * @version 3.5.3
	 * @since   2.4.0
	 *
	 * @todo    (dev) `alg_wc_order_status_rules_allow_multiple_order_meta`: make it always `yes`, i.e., remove the option?
	 */
	function check_order_meta( $order, $meta_key, $meta_value, $meta_value_is_multiple, $meta_compare ) {

		// Get order meta value
		$_meta_value = $order->get_meta( $meta_key );

		// Compare
		$res = ( 'yes' === $meta_value_is_multiple ?
			in_array( $_meta_value, explode( ',', $meta_value ) ) :
			$_meta_value === $meta_value
		);

		// Result
		switch ( $meta_compare ) {

			case 'not_equals':
				return ! $res;

			default: // 'equals'
				return $res;

		}

	}

	/**
	 * check_meta.
	 *
	 * @version 3.5.3
	 * @since   2.8.0
	 */
	function check_meta( $options, $rule_id, $order ) {
		return ( empty( $options['meta_key'][ $rule_id ] ) ||
			$this->check_order_meta(
				$order,
				$options['meta_key'][ $rule_id ],
				( $options['meta_value'][ $rule_id ]             ?? '' ),
				( $options['meta_value_is_multiple'][ $rule_id ] ?? 'no' ),
				( $options['meta_compare'][ $rule_id ]           ?? 'equals' )
			)
		);
	}

	/**
	 * check_dates.
	 *
	 * @version 2.9.3
	 * @since   2.8.0
	 *
	 * @todo    (feature) `relative_date_selector`
	 * @todo    (feature) `time`
	 */
	function check_dates( $options, $rule_id, $order ) {
		$date_created = ( ( $date_created = $order->get_date_created() ) ? $date_created->getTimestamp() : 0 );
		$date_created = apply_filters( 'alg_wc_order_status_rules_check_dates_order_date', $date_created, $options, $rule_id, $order );
		return (
			( empty( $options['date_created_before'][ $rule_id ] ) || $date_created < strtotime( $options['date_created_before'][ $rule_id ] ) ) &&
			( empty( $options['date_created_after'][ $rule_id ] )  || $date_created > strtotime( $options['date_created_after'][ $rule_id ] ) )
		);
	}

	/**
	 * is_array_intersect.
	 *
	 * @version 2.1.0
	 * @since   2.1.0
	 */
	function is_array_intersect( $array1, $array2 ) {
		$intersect = array_intersect( $array1, $array2 );
		return ( ! empty( $intersect ) );
	}

	/**
	 * check.
	 *
	 * @version 2.9.0
	 * @since   2.8.0
	 *
	 * @todo    (feature) `order_function`?
	 */
	function check( $options, $rule_id, $args ) {
		foreach ( array( 'min_max_amounts', 'gateways', 'shipping_methods', 'data', 'products', 'coupons', 'users', 'meta', 'dates' ) as $group ) {
			$func = 'check_' . $group;
			if ( ! $this->{$func}( $options, $rule_id, $args['order'] ) ) {
				return false;
			}
		}
		return true;
	}

}

endif;

return new Alg_WC_Order_Status_Rules_Conditions();
