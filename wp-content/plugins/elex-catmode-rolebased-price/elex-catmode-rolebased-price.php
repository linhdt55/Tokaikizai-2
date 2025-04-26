<?php
/*
Plugin Name: ELEX WooCommerce Role-based Pricing Plugin & WooCommerce Catalog Mode
Plugin URI: https://elextensions.com/plugin/woocommerce-catalog-mode-wholesale-role-based-pricing/
Description:  Hide Price or Add to Cart option for guest and specific registered users for simple, variable and grouped products. Create user role specific product price. Enforce markup/discount on price for selected user roles. Also, turn your shop into catalog mode.
Version: 2.7.0
WC requires at least: 2.6.0
WC tested up to: 6.5
Author: ELEXtensions
Author URI: https://elextensions.com 
Developer: ELEXtensions
Developer URI: https://elextensions.com
Text Domain: elex-catmode-rolebased-price
*/

// to check wether accessed directly
if (!defined('ABSPATH')) {
	exit;
}

// for Required functions
if (!function_exists('elex_rp_is_woocommerce_active')) {
	require_once ('elex-includes/elex-functions.php');
}

// to check woocommerce is active
if (!(elex_rp_is_woocommerce_active())) {
    add_action( 'admin_notices', 'elex_rp_premium_prices_woocommerce_inactive_notice' );
    return;
}
function elex_rp_premium_prices_woocommerce_inactive_notice() {
    ?>
<div id="message" class="error">
    <p>
	<?php	print_r(__( '<b>WooCommerce</b> plugin must be active for <b>WooCommerce Catalog Mode, Wholesale & Role Based Pricing</b> to work. ', 'elex-catmode-rolebased-price' ) ); ?>
    </p>
</div>
<?php
}

if (!defined('ELEX_PRICING_DISCOUNT_MAIN_URL_PATH')) {
    define('ELEX_PRICING_DISCOUNT_MAIN_URL_PATH', plugin_dir_url(__FILE__));
}

if (!defined('ELEX_USER_MULTIPLE_ROLE_STATUS')) {  //user_multile_role_plugin.
	if (in_array('user-role-editor/user-role-editor.php', get_option('active_plugins'))) {
		define('ELEX_USER_MULTIPLE_ROLE_STATUS', TRUE);
	} else {
		define('ELEX_USER_MULTIPLE_ROLE_STATUS', FALSE);
	}
}
if (!defined('ELEX_CUSTOM_REGISTRATION_STATUS')) {  //custom-registeration-role-plugin.
	if (in_array('user-registration-plugin-for-woocommerce/addify-registration-addon-main.php', get_option('active_plugins'))) {
		define('ELEX_CUSTOM_REGISTRATION_STATUS', TRUE);
	} else {
		define('ELEX_CUSTOM_REGISTRATION_STATUS', FALSE);
	}
}

//to check if basic version is active
function elex_rp_pricing_pre_activation_check(){
	//check if basic version is there
	if ( is_plugin_active('elex-woocommerce-catalog-mode/elex-catalog-mode.php') || is_plugin_active('elex-woocommerce-role-based-pricing-plugin-basic/elex-rolebased-price-basic.php')) {
        deactivate_plugins( plugin_basename( __FILE__ ) );
		wp_die( __("Oops! You tried installing the premium version without deactivating and deleting the basic version. Kindly deactivate and delete WooCommerce Catalog Mode, Wholesale & Role Based Pricing (BASIC) and then try again. For any issues, kindly contact our <a target='_blank' href='https://elextensions.com/support/'>support</a>.", "elex-catmode-rolebased-price" ), "", array('back_link' => 1 ));
	}
        
}

register_activation_hook( __FILE__, 'elex_rp_pricing_pre_activation_check' );

//show message for the first installation
add_action('admin_notices', 'elex_rp_plugin_admin_notices');
function elex_rp_plugin_admin_notices() {
    if (!get_option("elex_first_installation_msg")) {
        if ( in_array( 'elex-catmode-rolebased-price/elex-catmode-rolebased-price.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
            echo "<div class='updated'><strong>ELEX WooCommerce Catalog Mode, Wholesale & Role Based Pricing</strong> is activated. Go to <a href=".admin_url( 'admin.php?page=wc-settings&tab=eh_pricing_discount' ).">Settings</a> to configure.</div>";
        }
        update_option("elex_first_installation_msg",'true');
    }
}

if(!class_exists('Elex_Pricing_discounts_By_User_Role_WooCommerce')){
	class Elex_Pricing_discounts_By_User_Role_WooCommerce {
		
		// initializing the class
		public function __construct() {
			add_filter('plugin_action_links_' . plugin_basename(__FILE__) , array( $this,'elex_rp_pricing_discount_action_links')); //to add settings, doc, etc options to plugins base
			add_action('init', array( $this,'elex_rp_pricing_discount_admin_menu')); //to add pricing discount settings options on woocommerce shop
			add_action('admin_menu', array(	$this,'elex_rp_pricing_discount_admin_menu_option')); //to add pricing discount settings menu to main menu of woocommerce
		}
		
		// function to add settings link to plugin view
		public function elex_rp_pricing_discount_action_links($links) {
			$plugin_links = array(
				'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=eh_pricing_discount' ) . '">' . __( 'Settings', 'elex-catmode-rolebased-price' ) . '</a>',
				'<a href="https://elextensions.com/documentation/#elex-woocommerce-catalog-mode" target="_blank">' . __('Documentation', 'elex-catmode-rolebased-price') . '</a>',
                '<a href="https://elextensions.com/support/" target="_blank">' . __('Support', 'elex-catmode-rolebased-price') . '</a>'
			);
			return array_merge($plugin_links, $links);
		}
		
		// function to add menu in woocommerce
		public function elex_rp_pricing_discount_admin_menu() 
		{
                        //Comment the below line to remove Licencing part.
			include_once ( 'includes/wf_api_manager/wf-api-manager-config.php' );
			require_once('includes/elex-price-discount-admin.php');
			require_once('includes/elex-price-discount-settings.php');
		}
		
		public function elex_rp_pricing_discount_admin_menu_option() {
			global $pricing_discount_settings_page;
			$pricing_discount_settings_page = add_submenu_page('woocommerce', __('Role-based Pricing', 'elex-catmode-rolebased-price') , __('Role-based Pricing', 'elex-catmode-rolebased-price') , 'manage_woocommerce', 'admin.php?page=wc-settings&tab=eh_pricing_discount');
		}
	}
	new Elex_Pricing_discounts_By_User_Role_WooCommerce();
}
function elex_rp_catalog_load_plugin_textdomain() {
    load_plugin_textdomain( 'elex-catmode-rolebased-price', FALSE, basename( dirname( __FILE__ ) ) . '/lang/' );
}
add_action( 'plugins_loaded', 'elex_rp_catalog_load_plugin_textdomain' );