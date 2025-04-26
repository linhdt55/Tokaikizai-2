<?php
/**
 * Order Status Rules for WooCommerce - Main Class
 *
 * @version 3.7.1
 * @since   1.0.0
 *
 * @author  Algoritmika Ltd.
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_Order_Status_Rules' ) ) :

final class Alg_WC_Order_Status_Rules {

	/**
	 * Plugin version.
	 *
	 * @var   string
	 * @since 1.0.0
	 */
	public $version = ALG_WC_ORDER_STATUS_RULES_VERSION;

	/**
	 * core.
	 *
	 * @version 3.5.0
	 * @since   3.5.0
	 */
	public $core;

	/**
	 * @var   Alg_WC_Order_Status_Rules The single instance of the class
	 * @since 1.0.0
	 */
	protected static $_instance = null;

	/**
	 * Main Alg_WC_Order_Status_Rules Instance
	 *
	 * Ensures only one instance of Alg_WC_Order_Status_Rules is loaded or can be loaded.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 *
	 * @static
	 * @return  Alg_WC_Order_Status_Rules - Main instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Alg_WC_Order_Status_Rules Constructor.
	 *
	 * @version 3.7.0
	 * @since   1.0.0
	 *
	 * @access  public
	 */
	function __construct() {

		// Check for active WooCommerce plugin
		if ( ! function_exists( 'WC' ) ) {
			return;
		}

		// Load libs
		if ( is_admin() ) {
			require_once plugin_dir_path( ALG_WC_ORDER_STATUS_RULES_FILE ) . 'vendor/autoload.php';
		}

		// Set up localisation
		add_action( 'init', array( $this, 'localize' ) );

		// Declare compatibility with custom order tables for WooCommerce
		add_action( 'before_woocommerce_init', array( $this, 'wc_declare_compatibility' ) );

		// Pro
		if ( 'order-status-rules-for-woocommerce-pro.php' === basename( ALG_WC_ORDER_STATUS_RULES_FILE ) ) {
			require_once plugin_dir_path( __FILE__ ) . 'pro/class-alg-wc-order-status-rules-pro.php';
		}

		// Include required files
		$this->includes();

		// Admin
		if ( is_admin() ) {
			$this->admin();
		}

	}

	/**
	 * localize.
	 *
	 * @version 1.7.0
	 * @since   1.4.0
	 */
	function localize() {
		load_plugin_textdomain(
			'order-status-rules-for-woocommerce',
			false,
			dirname( plugin_basename( ALG_WC_ORDER_STATUS_RULES_FILE ) ) . '/langs/'
		);
	}

	/**
	 * wc_declare_compatibility.
	 *
	 * @version 3.4.0
	 * @since   3.2.0
	 *
	 * @see     https://github.com/woocommerce/woocommerce/wiki/High-Performance-Order-Storage-Upgrade-Recipe-Book#declaring-extension-incompatibility
	 */
	function wc_declare_compatibility() {
		if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
			$files = ( defined( 'ALG_WC_ORDER_STATUS_RULES_FILE_FREE' ) ?
				array( ALG_WC_ORDER_STATUS_RULES_FILE, ALG_WC_ORDER_STATUS_RULES_FILE_FREE ) :
				array( ALG_WC_ORDER_STATUS_RULES_FILE )
			);
			foreach ( $files as $file ) {
				\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', $file, true );
			}
		}
	}

	/**
	 * Include required core files used in admin and on the frontend.
	 *
	 * @version 3.7.0
	 * @since   1.0.0
	 */
	function includes() {
		$this->core = require_once plugin_dir_path( __FILE__ ) . 'class-alg-wc-order-status-rules-core.php';
	}

	/**
	 * admin.
	 *
	 * @version 3.7.0
	 * @since   1.1.0
	 */
	function admin() {

		// Action links
		add_filter( 'plugin_action_links_' . plugin_basename( ALG_WC_ORDER_STATUS_RULES_FILE ), array( $this, 'action_links' ) );

		// "Recommendations" page
		$this->add_cross_selling_library();

		// WC Settings tab as WPFactory submenu item
		$this->move_wc_settings_tab_to_wpfactory_menu();

		// Settings
		add_filter( 'woocommerce_get_settings_pages', array( $this, 'add_woocommerce_settings_tab' ) );

		// Version update
		if ( get_option( 'alg_wc_order_status_rules_version', '' ) !== $this->version ) {
			add_action( 'admin_init', array( $this, 'version_updated' ) );
		}

	}

	/**
	 * Show action links on the plugin screen.
	 *
	 * @version 3.7.1
	 * @since   1.0.0
	 *
	 * @param   mixed $links
	 * @return  array
	 */
	function action_links( $links ) {
		$custom_links = array();

		$custom_links[] = '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=alg_wc_order_status_rules' ) . '">' .
			__( 'Settings', 'order-status-rules-for-woocommerce' ) .
		'</a>';

		if ( 'order-status-rules-for-woocommerce.php' === basename( ALG_WC_ORDER_STATUS_RULES_FILE ) ) {
			$custom_links[] = '<a target="_blank" style="font-weight: bold; color: green;" href="https://wpfactory.com/item/order-status-rules-for-woocommerce/">' .
				__( 'Go Pro', 'order-status-rules-for-woocommerce' ) .
			'</a>';
		}

		return array_merge( $custom_links, $links );
	}

	/**
	 * add_cross_selling_library.
	 *
	 * @version 3.7.0
	 * @since   3.7.0
	 */
	function add_cross_selling_library() {

		if ( ! class_exists( '\WPFactory\WPFactory_Cross_Selling\WPFactory_Cross_Selling' ) ) {
			return;
		}

		$cross_selling = new \WPFactory\WPFactory_Cross_Selling\WPFactory_Cross_Selling();
		$cross_selling->setup( array( 'plugin_file_path' => ALG_WC_ORDER_STATUS_RULES_FILE ) );
		$cross_selling->init();

	}

	/**
	 * move_wc_settings_tab_to_wpfactory_menu.
	 *
	 * @version 3.7.0
	 * @since   3.7.0
	 */
	function move_wc_settings_tab_to_wpfactory_menu() {

		if ( ! class_exists( '\WPFactory\WPFactory_Admin_Menu\WPFactory_Admin_Menu' ) ) {
			return;
		}

		$wpfactory_admin_menu = \WPFactory\WPFactory_Admin_Menu\WPFactory_Admin_Menu::get_instance();

		if ( ! method_exists( $wpfactory_admin_menu, 'move_wc_settings_tab_to_wpfactory_menu' ) ) {
			return;
		}

		$wpfactory_admin_menu->move_wc_settings_tab_to_wpfactory_menu( array(
			'wc_settings_tab_id' => 'alg_wc_order_status_rules',
			'menu_title'         => __( 'Order Status Rules', 'order-status-rules-for-woocommerce' ),
			'page_title'         => __( 'Order Status Rules', 'order-status-rules-for-woocommerce' ),
		) );

	}

	/**
	 * Add Order Status Rules settings tab to WooCommerce settings.
	 *
	 * @version 3.7.0
	 * @since   1.0.0
	 */
	function add_woocommerce_settings_tab( $settings ) {
		$settings[] = require_once plugin_dir_path( __FILE__ ) . 'settings/class-alg-wc-settings-order-status-rules.php';
		return $settings;
	}

	/**
	 * version_updated.
	 *
	 * @version 1.1.0
	 * @since   1.1.0
	 */
	function version_updated() {
		update_option( 'alg_wc_order_status_rules_version', $this->version );
	}

	/**
	 * Get the plugin url.
	 *
	 * @version 1.7.0
	 * @since   1.0.0
	 *
	 * @return  string
	 */
	function plugin_url() {
		return untrailingslashit( plugin_dir_url( ALG_WC_ORDER_STATUS_RULES_FILE ) );
	}

	/**
	 * Get the plugin path.
	 *
	 * @version 1.7.0
	 * @since   1.0.0
	 *
	 * @return  string
	 */
	function plugin_path() {
		return untrailingslashit( plugin_dir_path( ALG_WC_ORDER_STATUS_RULES_FILE ) );
	}

}

endif;
