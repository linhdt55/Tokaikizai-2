<?php
/**
 * Order Status Rules for WooCommerce - Section Settings
 *
 * @version 3.5.1
 * @since   1.0.0
 *
 * @author  Algoritmika Ltd.
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_Order_Status_Rules_Settings_Section' ) ) :

class Alg_WC_Order_Status_Rules_Settings_Section {

	/**
	 * id.
	 *
	 * @version 3.5.0
	 * @since   3.5.0
	 */
	public $id;

	/**
	 * desc.
	 *
	 * @version 3.5.0
	 * @since   3.5.0
	 */
	public $desc;

	/**
	 * num.
	 *
	 * @version 3.5.0
	 * @since   3.5.0
	 */
	public $num;

	/**
	 * Constructor.
	 *
	 * @version 1.1.0
	 * @since   1.0.0
	 */
	function __construct() {
		add_filter( 'woocommerce_get_sections_alg_wc_order_status_rules', array( $this, 'settings_section' ) );
		add_filter( 'woocommerce_get_settings_alg_wc_order_status_rules_' . $this->id, array( $this, 'get_settings' ), PHP_INT_MAX );
	}

	/**
	 * settings_section.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function settings_section( $sections ) {
		$sections[ $this->id ] = $this->desc;
		return $sections;
	}

	/**
	 * get_select_all_buttons.
	 *
	 * @version 1.8.1
	 * @since   1.8.1
	 */
	function get_select_all_buttons() {
		return
			'<a href="#" class="button alg-wc-osr-select-all">'   . __( 'Select all', 'order-status-rules-for-woocommerce' )   . '</a>' . ' ' .
			'<a href="#" class="button alg-wc-osr-deselect-all">' . __( 'Deselect all', 'order-status-rules-for-woocommerce' ) . '</a>';
	}

	/**
	 * add_admin_script.
	 *
	 * @version 1.8.1
	 * @since   1.8.1
	 *
	 * @todo    (dev) move this to a separate js file
	 * @todo    (dev) load on needed pages only
	 */
	function add_admin_script() {
		?><script>
			jQuery( document ).ready( function() {
				jQuery( '.alg-wc-osr-select-all' ).click( function( event ) {
					event.preventDefault();
					jQuery( this ).closest( 'td' ).find( 'select.chosen_select' ).select2( 'destroy' ).find( 'option' ).prop( 'selected', 'selected' ).end().select2();
					return false;
				} );
				jQuery( '.alg-wc-osr-deselect-all' ).click( function( event ) {
					event.preventDefault();
					jQuery( this ).closest( 'td' ).find( 'select.chosen_select' ).val( '' ).change();
					return false;
				} );
			} );
		</script><?php
	}

	/**
	 * get_next_scheduled_desc.
	 *
	 * @version 3.5.1
	 * @since   1.2.0
	 *
	 * @todo    (desc) `alg_wc_order_status_rules_no_history`: better title and desc
	 * @todo    (desc) `crons`: desc ("... only when someone visits your site...")
	 * @todo    (desc) Advanced Options: better description?
	 */
	function get_next_scheduled_desc() {
		if (
			'yes' === get_option( 'alg_wc_order_status_rules_use_wp_cron', 'yes' ) &&
			false != ( $next_scheduled = wp_next_scheduled( 'alg_wc_order_status_rules_process_rules', array( get_option( 'alg_wc_order_status_rules_wp_cron_schedule', 'hourly' ) ) ) )
		) {
			$date_format    = alg_wc_order_status_rules()->core->get_date_time_format();
			$current_time   = current_time( 'timestamp' );
			$server_time    = time();
			$next_scheduled = $next_scheduled + ( $current_time - $server_time );
			return sprintf( __( 'Next cron event is scheduled on %s (%s).', 'order-status-rules-for-woocommerce' ),
					'<code>' . date_i18n( $date_format, $next_scheduled ) . '</code>',
					( ( $next_scheduled - $current_time ) <= 0 ?
						__( 'i.e., now', 'order-status-rules-for-woocommerce' ) :
						sprintf( __( 'i.e., in %s', 'order-status-rules-for-woocommerce' ), human_time_diff( $next_scheduled, $current_time ) ) )
				) . ' ' .
				sprintf( __( 'Current time is %s.', 'order-status-rules-for-woocommerce' ),
					'<code>' . date_i18n( $date_format, $current_time ) . '</code>' );
		} else {
			return '';
		}
	}

	/**
	 * get_enabled_rules_desc.
	 *
	 * @version 2.0.0
	 * @since   2.0.0
	 */
	function get_enabled_rules_desc() {
		$enabled       = array_slice( get_option( 'alg_wc_order_status_rules_enabled', array() ), 0, apply_filters( 'alg_wc_order_status_rules_rules_total', 1 ), true );
		$enabled_rules = array();
		foreach ( $enabled as $rule_num => $is_enabled ) {
			if ( 'yes' === $is_enabled ) {
				$enabled_rules[] = $rule_num;
			}
		}
		return ( ! empty( $enabled_rules ) ?
			sprintf( __( 'Enabled rule(s): %s.', 'order-status-rules-for-woocommerce' ),
				__( 'Rule', 'order-status-rules-for-woocommerce' ) . ' #' . implode( ', ' . __( 'Rule', 'order-status-rules-for-woocommerce' ) . ' #', $enabled_rules ) ) :
			'' );
	}

	/**
	 * get_shipping_zones.
	 *
	 * @version 2.1.0
	 * @since   2.1.0
	 */
	function get_shipping_zones( $include_empty_zone = true ) {
		$zones = WC_Shipping_Zones::get_zones();
		if ( $include_empty_zone ) {
			$zone                                                = new WC_Shipping_Zone( 0 );
			$zones[ $zone->get_id() ]                            = $zone->get_data();
			$zones[ $zone->get_id() ]['zone_id']                 = $zone->get_id();
			$zones[ $zone->get_id() ]['formatted_zone_location'] = $zone->get_formatted_location();
			$zones[ $zone->get_id() ]['shipping_methods']        = $zone->get_shipping_methods();
		}
		return $zones;
	}

	/**
	 * get_shipping_methods_instances.
	 *
	 * @version 2.1.0
	 * @since   2.1.0
	 */
	function get_shipping_methods_instances() {
		$shipping_methods = array();
		foreach ( $this->get_shipping_zones() as $zone_id => $zone_data ) {
			foreach ( $zone_data['shipping_methods'] as $shipping_method ) {
				$shipping_methods[ $shipping_method->instance_id ] = $zone_data['zone_name'] . ': ' . $shipping_method->title;
			}
		}
		return $shipping_methods;
	}

	/**
	 * get_gateways.
	 *
	 * @version 2.5.0
	 * @since   2.5.0
	 *
	 * @todo    (dev) add "Other" and/or "N/A" options?
	 */
	function get_gateways() {
		$gateways = WC()->payment_gateways()->payment_gateways;
		return array_combine( wp_list_pluck( $gateways, 'id' ), wp_list_pluck( $gateways, 'method_title' ) );
	}

	/**
	 * get_missing_product_cat_title.
	 *
	 * @version 1.6.0
	 * @since   1.6.0
	 */
	function get_missing_product_cat_title( $term_id ) {
		return sprintf( __( 'Product category #%s', 'order-status-rules-for-woocommerce' ), $term_id );
	}

	/**
	 * get_missing_product_tag_title.
	 *
	 * @version 1.6.0
	 * @since   1.6.0
	 */
	function get_missing_product_tag_title( $term_id ) {
		return sprintf( __( 'Product tag #%s', 'order-status-rules-for-woocommerce' ), $term_id );
	}

	/**
	 * get_terms.
	 *
	 * @version 2.5.0
	 * @since   2.5.0
	 *
	 * @todo    (desc) add term ID?
	 */
	function get_terms( $taxonomy ) {
		$terms  = get_option( "alg_wc_order_status_rules_{$taxonomy}s", array() );
		$terms  = ( ! empty( $terms[ $this->num ] ) ? array_combine( $terms[ $this->num ], array_map( array( $this, "get_missing_{$taxonomy}_title" ), $terms[ $this->num ] ) ) : array() );
		$_terms = get_terms( array( 'taxonomy' => $taxonomy, 'hide_empty' => false ) );
		$_terms = ( ! empty( $_terms ) && ! is_wp_error( $_terms ) ? array_combine( wp_list_pluck( $_terms, 'term_id' ), wp_list_pluck( $_terms, 'name' ) ) : array() );
		return array_replace( $terms, $_terms );
	}

	/**
	 * get_user_roles.
	 *
	 * @version 2.5.0
	 * @since   2.5.0
	 */
	function get_user_roles() {
		global $wp_roles;
		return array_merge( array( 'guest' => __( 'Guest', 'order-status-rules-for-woocommerce' ) ), wp_list_pluck( apply_filters( 'editable_roles', $wp_roles->roles ), 'name' ) );
	}

	/**
	 * get_admin_title.
	 *
	 * @version 2.5.0
	 * @since   2.5.0
	 */
	function get_admin_title() {
		$titles = get_option( 'alg_wc_order_status_rules_title', array() );
		return ( ! empty( $titles[ $this->num ] ) ? ': ' . $titles[ $this->num ] : '' );
	}

	/**
	 * get_ajax_options.
	 *
	 * @version 3.5.0
	 * @since   2.6.1
	 *
	 * @see     https://github.com/woocommerce/woocommerce/blob/6.3.1/plugins/woocommerce/includes/class-wc-ajax.php#L1569
	 * @see     https://github.com/woocommerce/woocommerce/blob/6.3.1/plugins/woocommerce/includes/class-wc-ajax.php#L1681
	 */
	function get_ajax_options( $type, $option, $key = false ) {
		$options = array();
		$current = get_option( $option, array() );
		if ( false !== $key ) {
			$current = ( $current[ $key ] ?? array() );
		}
		foreach ( $current as $id ) {
			switch ( $type ) {
				case 'product':
					$obj      = wc_get_product( $id );
					$is_valid = ( $obj && is_object( $obj ) );
					break;
				case 'customer':
					if ( 'guest' === $id ) {
						$is_valid = false;
					} else {
						$obj      = new WC_Customer( $id );
						$is_valid = ( $obj && is_object( $obj ) && 0 != $obj->get_id() );
					}
					break;
			}
			if ( ! $is_valid ) {
				switch ( $type ) {
					case 'product':
						$res = sprintf( esc_html__( 'Product #%d', 'order-status-rules-for-woocommerce' ), $id );
						break;
					case 'customer':
						$res = ( 'guest' === $id ? esc_html__( 'Guest', 'order-status-rules-for-woocommerce' ) :
							sprintf( esc_html__( 'User #%d', 'order-status-rules-for-woocommerce' ), $id ) );
						break;
				}
			} else {
				switch ( $type ) {
					case 'product':
						$res = esc_html( wp_strip_all_tags( $obj->get_formatted_name() ) );
						break;
					case 'customer':
						$res = sprintf(
							/* translators: $1: customer name, $2 customer id, $3: customer email */
							esc_html__( '%1$s (#%2$s &ndash; %3$s)', 'woocommerce' ),
							$obj->get_first_name() . ' ' . $obj->get_last_name(),
							$obj->get_id(),
							$obj->get_email()
						);
						break;
				}
			}
			$options[ esc_attr( $id ) ] = $res;
		}
		return $options;
	}

}

endif;
