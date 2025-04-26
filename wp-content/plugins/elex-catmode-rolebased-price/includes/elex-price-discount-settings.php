<?php
// to check whether accessed directly
if (!defined('ABSPATH')) {
    exit;
}

require_once( WP_PLUGIN_DIR . '/woocommerce/includes/admin/settings/class-wc-settings-page.php' );

class Elex_Pricing_Discount_Settings extends WC_Settings_Page {

    public function __construct() {
        global $user_adjustment_price;
        $this->init();
        $this->id = 'eh_pricing_discount';
    }

    public function init() {
        include( 'elex-admin-notice.php' );

        $this->user_adjustment_price = get_option('eh_pricing_discount_price_adjustment_options', array());
        add_filter('woocommerce_settings_tabs_array', array($this, 'elex_rp_add_settings_tab'), 50);
        add_filter('eh_pricing_discount_manage_user_role_settings', array($this, 'elex_rp_add_manage_role_settings'), 30);
        add_action('woocommerce_admin_field_productdiscountonusers', array($this, 'elex_rp_pricing_admin_field_productdiscountonusers'));

        add_action('woocommerce_admin_field_priceadjustmenttable', array($this, 'elex_rp_pricing_admin_field_priceadjustmenttable')); //to add price adjustment table to settings
        add_action('woocommerce_admin_field_taxoptiontable', array($this, 'elex_rp_pricing_admin_field_taxoptiontable')); //to add tax options table to settings
        add_action('woocommerce_admin_field_rolepricesuffix', array($this, 'elex_rp_pricing_admin_field_rolepricesuffix')); //to add price suffix option to settings
        add_action('woocommerce_admin_field_pricing_discount_manage_user_role', array($this, 'elex_rp_pricing_admin_field_pricing_discount_manage_user_role'));
        //ajax call to save the role
            add_action('wp_ajax_elex_rp_pricing_discount_add_user_role',array($this,'elex_rp_pricing_discount_add_user_role'));
            //ajax call to view the role
            add_action('wp_ajax_elex_rp_ajax_pricing_discount_show_user_role',array($this,'elex_rp_ajax_pricing_discount_show_user_role'));
            //ajax call to delete the role
            add_action('wp_ajax_elex_rp_ajax_pricing_discount_delete_user_role', array($this,'elex_rp_ajax_pricing_discount_delete_user_role'));
            //ajax call to update the role
            add_action('wp_ajax_elex_rp_ajax_pricing_discount_update_user_role',array($this,'elex_rp_ajax_pricing_discount_update_user_role'));

        add_action('woocommerce_update_options_eh_pricing_discount', array($this, 'elex_rp_update_settings'));
        add_action('woocommerce_update_options_elex_catalog_mode', array($this, 'elex_rp_update_catalog_settings'));
        add_filter('woocommerce_product_data_tabs', array($this, 'elex_rp_add_product_tab'));
        add_action('woocommerce_product_data_panels', array($this, 'elex_rp_add_price_adjustment_data_fields'));
        add_action('woocommerce_product_after_variable_attributes', array($this, 'elex_rp_variation_settings_fields'), 10, 3);
        add_action('woocommerce_product_after_variable_attributes_js', array($this, 'elex_rp_variation_settings_fields'), 10, 3);
        add_action('woocommerce_process_product_meta', array($this, 'elex_rp_add_custom_general_fields_save'));
        add_action('woocommerce_product_options_general_product_data', array($this, 'elex_rp_add_price_extra_fields'));
        add_action('woocommerce_save_product_variation', array($this, 'elex_rp_save_variable_fields'), 10, 1);
        add_action('event-category_add_form_fields', array($this, 'elex_rp_pricing_category_adjustment_fields'), 10);
        add_filter('woocommerce_sections_eh_pricing_discount', array($this, 'output_sections'));
        add_filter('woocommerce_settings_eh_pricing_discount', array($this, 'elex_rp_output_settings'));
        add_filter('woocommerce_settings_elex_catalog_mode', array($this, 'elex_rp_output_catalog_settings'));
        add_action('admin_init', array($this, 'elex_rp_pricing_discount_remove_notices'));
        add_action('admin_enqueue_scripts', array($this, 'elex_rp_include_js'));
		add_action('wp_enqueue_scripts', array($this, 'elex_rp_include_js_custom_registration'));
    }
    function elex_rp_include_js_custom_registration() {
		if(ELEX_CUSTOM_REGISTRATION_STATUS && is_checkout() && !(is_user_logged_in())){
			
			wp_enqueue_script('jquery');
            wp_enqueue_script('eh-pricing-discount', ELEX_PRICING_DISCOUNT_MAIN_URL_PATH . 'assets/update_user.js');
			wp_localize_script(
				'eh-pricing-discount',
				'elex_rp_add_user_role_checkout',
				array(
					'ajax_url' => admin_url( 'admin-ajax.php' ),
					'nonce'    => wp_create_nonce( 'eraq_nonce' ),
				)
			);
		}
	}
    function elex_rp_include_js() {
             
        $page = isset($_GET['page']) ? $_GET['page'] : '';
        $tab = isset($_GET['tab']) ? $_GET['tab'] : '';
        $section = isset($_GET['section']) ? $_GET['section'] : '';
        if ($page == 'wc-settings' && (($tab == "eh_pricing_discount" && ($section == "" || $section == "xa-unregistered-role" || $section == "xa-tax-option")) || $tab = 'elex_catalog_mode')) {
            wp_enqueue_script('jquery');
            wp_enqueue_script('eh-pricing-discount', ELEX_PRICING_DISCOUNT_MAIN_URL_PATH . 'includes/elex-common.js');
        }
		
    }

    public function get_sections() {
        $sections = array(
            '' => __('Role-based Settings', 'elex-catmode-rolebased-price'),
            'xa-unregistered-role' => __('Unregistered User', 'elex-catmode-rolebased-price'),
            'xa-tax-option' => __('Tax Option', 'elex-catmode-rolebased-price'),
            'manage-user-role' => __('Manage User Role', 'elex-catmode-rolebased-price'),
            'license'          =>__('License','elex-catmode-rolebased-price'),
        );
        return apply_filters('woocommerce_get_sections_eh_pricing_discount', $sections);
    }

    public function elex_rp_pricing_discount_remove_notices() {
        global $current_section;

        if ($current_section == 'manage-user-role') {
            wc_enqueue_js('jQuery(document).ready(function(){
                jQuery(".woocommerce-save-button").hide();
            })');
            remove_all_actions('admin_notices');
            Elex_admin_notice::throw_notices();
        }
    }

    //function to create User Role
    public function elex_rp_pricing_discount_add_user_role() {
        global $wp_roles;
        $user_role_name = $_POST['user_role'];
        $user_role_desc = $_POST['user_desc'];
        $user_roles = $wp_roles->role_names;
        $key_user_role = str_replace(' ', '_', $user_role_name);

            if (($key_user_role != '' && $user_role_name != '' ) && !( array_key_exists($key_user_role, $user_roles) )) {
                $this->eh_query_to_create_save_role($key_user_role,$user_role_name, $user_role_desc);
                add_role($key_user_role, $user_role_name, array('read' => true));
                wp_die(json_encode(array("status"=> 200)));
                
            } else {
               // $status = __('User Role creation failed', 'elex-catmode-rolebased-price');
                wp_die(json_encode(array("status"=> 400)));
            }
        
        
    }

    //function to save a custom role field

    public function eh_query_to_create_save_role($key_user_role,$user_role_name, $user_role_desc){
            global $wpdb;

            $table_name = $wpdb->prefix.'eh_custom_keeper_role';
             $sql = "CREATE TABLE $table_name(
                id varchar(255),
                user_roles varchar(255),
                user_roles_desc varchar(255)
                );";

                if($wpdb->get_var("SHOW TABLES LIKE '$table_name'" ) != $table_name){
                    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
                    dbDelta($sql);
                }

                $wpdb->insert($table_name,
                    array(
                        'id' => $key_user_role,
                        'user_roles' => $user_role_name,
                        'user_roles_desc' => $user_role_desc
                    ),
                    array(
                        '%s',
                        '%s',
                        '%s',
                    )
                );       
            }


    //Ajax call funtion to show all available roles
    public function elex_rp_ajax_pricing_discount_show_user_role(){
        global $wp_roles,$wpdb;
        $custom_roles = array();
        $user_roles = $wp_roles->role_names;
            //retrive the elex custom roles
            $result = $wpdb->get_results("
            SELECT  *
            FROM ".$wpdb->prefix."eh_custom_keeper_role
            ");
        $count = 0;
            foreach ($user_roles as $keys => $name) {
                foreach ($result as $key => $value) {
                    if($value->id == $keys){
                        $user_roles_name = $value->user_roles;
                        $user_roles      = $value->id;
                        $user_roles_descs = $value->user_roles_desc;
                        array_push($custom_roles, array( $user_roles => array('user_role' => $user_roles,'user_role_name'=> $user_roles_name,'user_role_desc' => $user_roles_descs)
                            )
                        );
                        $count++;
                    }
                }
            }
           $edit_url = untrailingslashit(plugins_url()).'/elex-catmode-rolebased-price/resources/barretr_Pencil.png';
           $modify_url = untrailingslashit(plugins_url()).'/elex-catmode-rolebased-price/resources/edit.png';
           wp_die(json_encode(array('status'=>200 , 'available_roles'=> $custom_roles, 'edit_url'=>$edit_url,'modify_url'=>$modify_url)));
    }
    
    public function elex_rp_ajax_pricing_discount_delete_user_role(){
        global $wp_roles,$wpdb;
        if(isset($_POST['del_user_role'])){
            $delete_user_roles = $_POST['del_user_role'];    
        }else{
             wp_die(json_encode(array('status'=>400)));
        }
       if(isset($delete_user_roles)){
            foreach ($delete_user_roles as $key => $id) {
                remove_role($id);
                $wpdb->delete(
                    'wp_eh_custom_keeper_role',
                    array(
                        "id"=>$id
                    )
                );
            }
             wp_die(json_encode(array('status'=>200)));
       }else{
             wp_die(json_encode(array('status'=>400)));
       }
    }
    public function elex_rp_ajax_pricing_discount_update_user_role(){
        global $wpdb; 
        $edit_user_roles   = $_POST['updated_user_role'];
        if(isset($edit_user_roles)){
            foreach ($edit_user_roles as $keys => $value) {
                
                    $wpdb->update(
                        "wp_eh_custom_keeper_role",
                        array('user_roles_desc' =>$value),
                        array("id"=>$keys),
                            array('%s'),
                            array('%s')
                    );
            }
            wp_die(array('status'=>200));
        }else{
            wp_die(array('status'=>400));
        }
    }
    public static function elex_rp_add_settings_tab($settings_tabs) {
        $settings_tabs['eh_pricing_discount'] = __('Role-based Pricing', 'elex-catmode-rolebased-price');
        $settings_tabs['elex_catalog_mode'] = __('Catalog Mode', 'elex-catmode-rolebased-price');
        return $settings_tabs;
    }

    public function elex_rp_output_settings() {
        global $current_section;
        if ($current_section == '') {
            $settings = $this->elex_rp_get_role_settings($current_section);
            WC_Admin_Settings::output_fields($settings);
        } else if ($current_section == 'xa-unregistered-role') {
            $settings = $this->elex_rp_get_unregistered_settings($current_section);
            WC_Admin_Settings::output_fields($settings);
        } else if ($current_section == 'xa-tax-option') {
            $settings = $this->elex_rp_get_tax_settings($current_section);
            WC_Admin_Settings::output_fields($settings);
        } else if ($current_section == 'manage-user-role') {
            $settings = $this->elex_rp_get_user_role_settings($current_section);
            WC_Admin_Settings::output_fields($settings);
        }elseif($current_section == 'license'){
            $settings = $this->elex_rp_license_activation($current_section);
        }

    }
    public function elex_rp_output_catalog_settings () {
        $settings = $this->elex_rp_get_catalog_settings();
        WC_Admin_Settings::output_fields($settings);
    }

    public function elex_rp_get_user_role_settings($current_section) {
        $settings = array(
            'section_title' => array(
                'name' => __('', 'elex-catmode-rolebased-price'),
                'type' => 'title',
                'desc' => '',
                'id' => 'eh_pricing_discount_add_user_role_section_title',
            ),
            'section_end' => array(
                'type' => 'sectionend',
                'id' => 'eh_pricing_discount_add_user_role_section_end'
            ),
        );
        return apply_filters('eh_pricing_discount_manage_user_role_settings', $settings);
    }

    //function to add 
    public function elex_rp_add_manage_role_settings($settings) {
        $settings['price_adjustment_options'] = array(
            'type' => 'pricing_discount_manage_user_role',
            'id' => 'eh_pricing_discount_manage_user_role',
        );
        return $settings;
    }
    //function to generate license settings
    public function elex_rp_license_activation($settings){
        $plugin_name = 'pricesbyuserrole';
        //Comment the below line to remove Licencing part.
        include( 'wf_api_manager/html/html-wf-activation-window.php' );
    }
    //function to generate manage user role setting page
    public function elex_rp_pricing_admin_field_pricing_discount_manage_user_role($settings) {
        include( 'elex-html-price-adjustment-manage-user-role.php' );
    }

    public function elex_rp_update_settings($current_section) {
        global $current_section;
        if ($current_section == '') {
            if(!isset($_REQUEST['eh_pricing_discount_product_on_users'])) {
                delete_option('eh_pricing_discount_product_on_users');
            }
            $options = $this->elex_rp_get_role_settings();
			$price_adjustment_table = isset( $_POST['eh_pricing_discount_price_adjustment_options'] ) ? map_deep( wp_unslash( $_POST['eh_pricing_discount_price_adjustment_options'] ), 'sanitize_text_field' ) : '';
			$price_adjustment_table_array = !empty( $price_adjustment_table ) ? array_values( $price_adjustment_table ) : '';
            woocommerce_update_options($options);
			update_option( 'eh_pricing_discount_price_adjustment_options', $price_adjustment_table_array );
            $this->user_adjustment_price = get_option('eh_pricing_discount_price_adjustment_options', array());  
        }
        if ($current_section == 'xa-unregistered-role') {
            $options = $this->elex_rp_get_unregistered_settings();
            woocommerce_update_options($options);
        }
        if ($current_section == 'xa-tax-option') {
            $options = $this->elex_rp_get_tax_settings();
            woocommerce_update_options($options);
        }
    }
    public function elex_rp_update_catalog_settings () {
		$options = $this->elex_rp_get_catalog_settings();
        woocommerce_update_options($options);
    }
    
    public function elex_rp_get_tax_settings() {
        global $wp_roles;

        $price_suffix_options = array(
            'none' => 'None',
            'general' => 'General',
            'role_specific' => 'Role Specific'
        );
        $user_roles = $wp_roles->role_names;
        $settings = array(
            'general_settings_section_title' => array(
                'name' => __('General and Tax Options:', 'elex-catmode-rolebased-price'),
                'type' => 'title',
                'desc' => '',
                'id' => 'eh_pricing_discount_section_title',
            ),
            'enable_tax_options' => array(
                'title' => __('Enable Tax Options', 'elex-catmode-rolebased-price'),
                'type' => 'checkbox',
                'desc' => __('Enable', 'elex-catmode-rolebased-price'),
                'css' => 'width:100%',
                'id' => 'eh_pricing_discount_enable_tax_options',
                'desc_tip' => __('Check to enable Role specific tax options.', 'elex-catmode-rolebased-price'),
            ),
            'price_tax_options' => array(
                'type' => 'taxoptiontable',
                'id' => 'eh_pricing_discount_price_tax_options',
                'value' => ''
            ),
            'price_suffix' => array(
                'title' => __('Price Suffix', 'elex-catmode-rolebased-price'),
                'type' => 'select',
                'css'  => 'padding: 0px;',
                'desc' => __('Select the price suffix option you want to have. Choose General to apply globally. Choose Role specific to set different suffixes to different Roles.', 'elex-catmode-rolebased-price'),
                'id' => 'eh_pricing_discount_enable_price_suffix',
                'default' => 'none',
                'options' => $price_suffix_options,
                'desc_tip' => true
            ),
            'general_price_suffix' => array(
                'title' => __('Suffix Text', 'elex-catmode-rolebased-price'),
                'type' => 'text',
                'desc' => __("Enter the text you want to add as suffix to the price.", 'elex-catmode-rolebased-price'),
                'css' => 'width:350px',
                'id' => 'eh_pricing_discount_price_general_price_suffix',
                'desc_tip' => true
            ),
            'role_price_suffix' => array(
                'type' => 'rolepricesuffix',
                'id' => 'eh_pricing_discount_role_price_suffix',
                'value' => ''
            ),
            'general_settings_section_title_end' => array(
                'type' => 'sectionend',
                'id' => 'eh_pricing_discount_section_title'
            ),
        );
        return apply_filters('eh_pricing_discount_tax_settings', $settings);
    }

    public function elex_rp_get_catalog_settings() {
        global $wp_roles;

        $user_roles = $wp_roles->role_names;
        $settings = array(
            'catalog_settings_section_title' => array(
                'name' => __('Catalog Mode Option:', 'elex-catmode-rolebased-price'),
                'type' => 'title',
                'desc' => __('The changes you make here will be applicable across the site. You can exclude Administrator role from these changes.<br>Catalog Mode Settings will override Role Based, Unregistered User & Individual Product Settings.', 'elex-catmode-rolebased-price'),
                'id' => 'eh_pricing_discount_catalog_section_title',
            ),
            'cart_catalog_mode' => array(
                'title' => __('Remove Add to Cart', 'elex-catmode-rolebased-price'),
                'type' => 'checkbox',
                'desc' => __('Enable', 'elex-catmode-rolebased-price'),
                'css' => 'width:100%',
                'id' => 'eh_pricing_discount_cart_catalog_mode',
                'desc_tip' => __('Check to remove Add to Cart option.', 'elex-catmode-rolebased-price'),
            ),
            'cart_catalog_mode_shop' => array(
                'desc' => __('Shop Page', 'elex-catmode-rolebased-price'),
                'id' => 'elex_catalog_remove_addtocart_shop',
                'default' => 'yes',
                'type' => 'checkbox',
                'checkboxgroup' => 'start',
                'autoload' => false,
            ),
            'cart_catalog_mode_product' => array(
                'desc' => __('Product Page', 'elex-catmode-rolebased-price'),
                'id' => 'elex_catalog_remove_addtocart_product',
                'default' => 'yes',
                'type' => 'checkbox',
                'checkboxgroup' => 'end',
                'autoload' => false,
            ),
            'cart_catalog_mode_text' => array(
                'title' => __('Placeholder Text', 'elex-catmode-rolebased-price'),
                'type' => 'textarea',
                'desc' => __("Enter a text or html content to display when Add to Cart button is removed. Leave it empty if you don't want to show any content.", 'elex-catmode-rolebased-price'),
                'css' => 'width:350px',
                'id' => 'eh_pricing_discount_cart_catalog_mode_text',
                'desc_tip' => true
            ),
            
            'replace_cart_catalog_mode' => array(
                'title' => __('Customize Add to Cart', 'elex-catmode-rolebased-price'),
                'type' => 'checkbox',
                'desc' => __('Enable', 'elex-catmode-rolebased-price'),
                'css' => 'width:100%',
                'id' => 'eh_pricing_discount_replace_cart_catalog_mode',
                'desc_tip' => __('Check to customize Add to Cart.', 'elex-catmode-rolebased-price'),
            ),
            'replace_cart_catalog_mode_text_product' => array(
                'title' => __('Change Button Text (Product Page)', 'elex-catmode-rolebased-price'),
                'type' => 'text',
                'desc' => __("Enter a text to replace the existing Add to Cart button text on the product page.", 'elex-catmode-rolebased-price'),
                'css' => 'width:350px',
                'id' => 'eh_pricing_discount_replace_cart_catalog_mode_text_product',
                'desc_tip' => true
            ),
            'replace_cart_catalog_mode_text_shop' => array(
                'title' => __('Change Button Text (Shop Page)', 'elex-catmode-rolebased-price'),
                'type' => 'text',
                'desc' => __("Enter a text to replace the existing Add to Cart button text on the shop page.", 'elex-catmode-rolebased-price'),
                'css' => 'width:350px',
                'id' => 'eh_pricing_discount_replace_cart_catalog_mode_text_shop',
                'desc_tip' => true
            ),
            'replace_cart_catalog_mode_url_shop' => array(
                'title' => __('Change Button URL', 'elex-catmode-rolebased-price'),
                'type' => 'text',
                'desc' => __("Enter a url to redirect customers from Add to Cart button. Leave this field empty to not change the button functionality. Make sure to enter a text in the above fields to apply these changes.", 'elex-catmode-rolebased-price'),
                'css' => 'width:350px',
                'id' => 'eh_pricing_discount_replace_cart_catalog_mode_url_shop',
                'desc_tip' => true
            ),
            
            'price_catalog_mode' => array(
                'title' => __('Hide Price', 'elex-catmode-rolebased-price'),
                'type' => 'checkbox',
                'desc' => __('Enable', 'elex-catmode-rolebased-price'),
                'css' => 'width:100%',
                'id' => 'eh_pricing_discount_price_catalog_mode',
                'desc_tip' => __('Check to hide product price. This will also remove Add to Cart button.', 'elex-catmode-rolebased-price'),
            ),
            'price_catalog_mode_text' => array(
                'title' => __('Placeholder Text', 'elex-catmode-rolebased-price'),
                'type' => 'text',
                'desc' => __("Enter the text you want to display when price is removed. Leave it empty if you don't want to show any placeholder text.", 'elex-catmode-rolebased-price'),
                'css' => 'width:350px',
                'id' => 'eh_pricing_discount_price_catalog_mode_text',
                'desc_tip' => true
            ),
            'cart_catalog_mode_remove_cart_checkout' => array(
                'title' => __('Hide Cart and Checkout Page', 'elex-catmode-rolebased-price'),
                'type' => 'checkbox',
                'desc' => __('Enable', 'elex-catmode-rolebased-price'),
                'css' => 'width:100%',
                'id' => 'eh_pricing_discount_cart_catalog_mode_remove_cart_checkout',
                'desc_tip' => __('Check to disable access to Cart and Checkout pages.', 'elex-catmode-rolebased-price'),
            ),
            'price_catalog_exclude_admin' => array(
                'title' => __('Exclude Administrator', 'elex-catmode-rolebased-price'),
                'type' => 'checkbox',
                'desc' => __('Enable', 'elex-catmode-rolebased-price'),
                'css' => 'width:100%',
                'id' => 'eh_pricing_discount_price_catalog_mode_exclude_admin',
                'desc_tip' => __('Check to exclude Administrator role from the above catalog mode settings', 'elex-catmode-rolebased-price'),
            ),
            'catalog_settings_section_title_end' => array(
                'type' => 'sectionend',
                'id' => 'eh_pricing_discount_catalog_section_title'
            ),
        );
        return apply_filters('eh_pricing_discount_catalog_settings', $settings);
    }

    public function elex_rp_get_unregistered_settings() {
        $settings = array(
            'eh_pricing_discount_unregistered_title' => array(
                'title' => __('Unregistered User Options:', 'elex-catmode-rolebased-price'),
                'type' => 'title',
                'description' => '',
                'id' => 'eh_pricing_discount_unregistered'
            ),
            'cart_unregistered_user' => array(
                'title' => __('Remove Add to Cart', 'elex-catmode-rolebased-price'),
                'type' => 'checkbox',
                'desc' => __('Enable', 'elex-catmode-rolebased-price'),
                'css' => 'width:100%',
                'id' => 'eh_pricing_discount_cart_unregistered_user',
                'desc_tip' => __('Check to remove Add to Cart option.', 'elex-catmode-rolebased-price'),
            ),
            'cart_unregistered_shop' => array(
                'desc' => __('Shop Page', 'elex-catmode-rolebased-price'),
                'id' => 'elex_unregistered_remove_addtocart_shop',
                'default' => 'yes',
                'type' => 'checkbox',
                'checkboxgroup' => 'start',
                'autoload' => false,
            ),
            'cart_unregistered_product' => array(
                'desc' => __('Product Page', 'elex-catmode-rolebased-price'),
                'id' => 'elex_unregistered_remove_addtocart_product',
                'default' => 'yes',
                'type' => 'checkbox',
                'checkboxgroup' => 'end',
                'autoload' => false,
            ),
            'cart_unregistered_user_text' => array(
                'title' => __('Placeholder Text', 'elex-catmode-rolebased-price'),
                'type' => 'textarea',
                'desc' => __("Enter a text or html content to display when Add to Cart button is removed. Leave it empty if you don't want to show any content.", 'elex-catmode-rolebased-price'),
                'css' => 'width:350px',
                'id' => 'eh_pricing_discount_cart_unregistered_user_text',
                'desc_tip' => true
            ),
            'replace_cart_unregistered_user' => array(
                'title' => __('Customize Add to Cart', 'elex-catmode-rolebased-price'),
                'type' => 'checkbox',
                'desc' => __('Enable', 'elex-catmode-rolebased-price'),
                'css' => 'width:100%',
                'id' => 'eh_pricing_discount_replace_cart_unregistered_user',
                'desc_tip' => __('Check to customize Add to Cart option.', 'elex-catmode-rolebased-price'),
            ),
            'replace_cart_unregistered_user_text_product' => array(
                'title' => __('Change Button Text (Product Page)', 'elex-catmode-rolebased-price'),
                'type' => 'text',
                'desc' => __("Enter a text to replace the existing Add to Cart button text on the product page.", 'elex-catmode-rolebased-price'),
                'css' => 'width:350px',
                'id' => 'eh_pricing_discount_replace_cart_unregistered_user_text_product',
                'desc_tip' => true
            ),
            'replace_cart_unregistered_user_text_shop' => array(
                'title' => __('Change Button Text (Shop Page)', 'elex-catmode-rolebased-price'),
                'type' => 'text',
                'desc' => __("Enter a text to replace the existing Add to Cart button text on the shop page.", 'elex-catmode-rolebased-price'),
                'css' => 'width:350px',
                'id' => 'eh_pricing_discount_replace_cart_unregistered_user_text_shop',
                'desc_tip' => true
            ),
            'replace_cart_unregistered_user_url_shop' => array(
                'title' => __('Change Button URL', 'elex-catmode-rolebased-price'),
                'type' => 'text',
                'desc' => __("Enter a url to redirect customers from Add to Cart button. Leave this field empty to not change the button functionality. Make sure to enter a text in the above fields to apply these changes.", 'elex-catmode-rolebased-price'),
                'css' => 'width:350px',
                'id' => 'eh_pricing_discount_replace_cart_unregistered_user_url_shop',
                'desc_tip' => true
            ),
            'hide_regular_price' => array(
                'title' => __('Hide Regular Price', 'elex-catmode-rolebased-price'),
                'type' => 'checkbox',
                'desc' => __('Enable', 'elex-catmode-rolebased-price'),
                'css' => 'width:100%',
                'id' => 'eh_pricing_discount_hide_regular_price_unregistered',
                'desc_tip' => __('Check to hide regular price when sale price is provided.', 'elex-catmode-rolebased-price'),
            ),
            'price_unregistered_user' => array(
                'title' => __('Hide Price', 'elex-catmode-rolebased-price'),
                'type' => 'checkbox',
                'desc' => __('Enable', 'elex-catmode-rolebased-price'),
                'css' => 'width:100%',
                'id' => 'eh_pricing_discount_price_unregistered_user',
                'desc_tip' => __('Check to hide product price. This will also remove Add to Cart option.', 'elex-catmode-rolebased-price'),
            ),
            'price_unregistered_user_text' => array(
                'title' => __('Placeholder Text', 'elex-catmode-rolebased-price'),
                'type' => 'text',
                'desc' => __("Enter the text you want to display when price is removed. Leave it empty if you don't want to show any placeholder text.", 'elex-catmode-rolebased-price'),
                'css' => 'width:350px',
                'id' => 'eh_pricing_discount_price_unregistered_user_text',
                'desc_tip' => true
            ),
            'cart_unregistered_user_remove_cart_checkout' => array(
                'title' => __('Hide Cart and Checkout Page', 'elex-catmode-rolebased-price'),
                'type' => 'checkbox',
                'desc' => __('Enable', 'elex-catmode-rolebased-price'),
                'css' => 'width:100%',
                'id' => 'eh_pricing_discount_cart_unregistered_user_remove_cart_checkout',
                'desc_tip' => __('Check to disable access to Cart and Checkout pages.', 'elex-catmode-rolebased-price'),
            ),
            'eh_pricing_discount_unregistered_title_end' => array(
                'type' => 'sectionend',
                'id' => 'eh_pricing_discount_unregistered'
            ),
        );
        return apply_filters('eh_pricing_discount_unregistered_settings', $settings);
    }

    public function elex_rp_get_role_settings() {
        global $wp_roles;

        $price_sale_regular = array('regular' => __('Regular Price', 'elex-catmode-rolebased-price'),
            'sale' => __('Sale Price', 'elex-catmode-rolebased-price'),
            'regular_sale' => __('Regular & Sale Price', 'elex-catmode-rolebased-price')
            );

        $user_roles = $wp_roles->role_names;
		$user_roles['unregistered_user'] = 'Unregistered User';
        $settings = array(
            'eh_pricing_discount_user_role_title' => array(
                'title' => __('User Role Specific Options:', 'elex-catmode-rolebased-price'),
                'type' => 'title',
                'description' => '',
                'id' => 'eh_pricing_discount_user_role'
            ),
            'cart_user_role' => array(
                'title' => __('Remove Add to Cart', 'elex-catmode-rolebased-price'),
                'type' => 'multiselect',
                'desc' => __('Select the user role(s) for which you want to hide Add to Cart option.', 'elex-catmode-rolebased-price'),
                'css' => 'width:350px',
				'class' => 'chosen_select',
                'id' => 'eh_pricing_discount_cart_user_role',
                'options' => $user_roles,
                'desc_tip' => true
            ),
            'cart_user_role_shop' => array(
                'desc' => __('Shop Page', 'elex-catmode-rolebased-price'),
                'id' => 'elex_user_role_remove_addtocart_shop',
                'default' => 'yes',
                'type' => 'checkbox',
                'checkboxgroup' => 'start',
                'autoload' => false,
            ),
            'cart_user_role_product' => array(
                'desc' => __('Product Page', 'elex-catmode-rolebased-price'),
                'id' => 'elex_user_role_remove_addtocart_product',
                'default' => 'yes',
                'type' => 'checkbox',
                'checkboxgroup' => 'end',
                'autoload' => false,
            ),
            'cart_user_role_text' => array(
                'title' => __('Placeholder Content', 'elex-catmode-rolebased-price'),
                'type' => 'textarea',
                'desc' => __("Enter a text or html content to display when Add to Cart button is removed. Leave it empty if you don't want to show any content.", 'elex-catmode-rolebased-price'),
                'css' => 'width:350px',
                'id' => 'eh_pricing_discount_cart_user_role_text',
                'desc_tip' => true
            ),
            'replace_cart_user_role' => array(
                'title' => __('Customize Add to Cart', 'elex-catmode-rolebased-price'),
                'type' => 'multiselect',
                'desc' => __('Select the user role(s) for which you want to customize Add to Cart option.', 'elex-catmode-rolebased-price'),
				'css' => 'width:350px',
				'class' => 'chosen_select',
                'id' => 'eh_pricing_discount_replace_cart_user_role',
                'options' => $user_roles,
                'desc_tip' => true
            ),
            'replace_cart_user_role_text_product' => array(
                'title' => __('Change Button Text (Product Page)', 'elex-catmode-rolebased-price'),
                'type' => 'text',
                'desc' => __("Enter a text to replace the existing Add to Cart button text on the product page.", 'elex-catmode-rolebased-price'),
                'css' => 'width:350px',
                'id' => 'eh_pricing_discount_replace_cart_user_role_text_product',
                'desc_tip' => true
            ),
            'replace_cart_user_role_text_shop' => array(
                'title' => __('Change Button Text (Shop Page)', 'elex-catmode-rolebased-price'),
                'type' => 'text',
                'desc' => __("Enter a text to replace the existing Add to Cart button text on the shop page.", 'elex-catmode-rolebased-price'),
                'css' => 'width:350px',
                'id' => 'eh_pricing_discount_replace_cart_user_role_text_shop',
                'desc_tip' => true
            ),
            'replace_cart_user_role_url_shop' => array(
                'title' => __('Change Button URL', 'elex-catmode-rolebased-price'),
                'type' => 'text',
                'desc' => __("Enter a url to redirect customers from Add to Cart button. Leave this field empty to not change the button functionality. Make sure to enter a text in the above fields to apply these changes.", 'elex-catmode-rolebased-price'),
                'css' => 'width:350px',
                'id' => 'eh_pricing_discount_replace_cart_user_role_url_shop',
                'desc_tip' => true
            ),
            'regular_price_user_role' => array(
                'title' => __('Hide Regular Price', 'elex-catmode-rolebased-price'),
                'type' => 'multiselect',
                'desc' => __('Select the user role(s) for which you want to hide the Regular Price. This will be applicable for all products that have either discounted or sales price.', 'elex-catmode-rolebased-price'),
				'css' => 'width:350px',
				'class' => 'chosen_select',
                'id' => 'eh_pricing_discount_regular_price_user_role',
                'options' => $user_roles,
                'desc_tip' => true
            ),
            'price_user_role' => array(
                'title' => __('Hide Price', 'elex-catmode-rolebased-price'),
                'type' => 'multiselect',
                'desc' => __('Select the user role(s) for which you want to hide product price. This will also remove Add to Cart option.', 'elex-catmode-rolebased-price'),
                'css' => 'width:350px',
				'class' => 'chosen_select',
                'id' => 'eh_pricing_discount_price_user_role',
                'options' => $user_roles,
                'desc_tip' => true
            ),
            'price_user_role_text' => array(
                'title' => __('Placeholder Text', 'elex-catmode-rolebased-price'),
                'type' => 'textarea',
                'desc' => __("Enter a text you want to display when price is removed. Leave it empty if you don't want to show any text.", 'elex-catmode-rolebased-price'),
                'css' => 'width:350px',
                'id' => 'eh_pricing_discount_price_user_role_text',
                'desc_tip' => true
            ),
            'cart_user_role_remove_cart_checkout' => array(
                'title' => __('Hide Cart and Checkout Page', 'elex-catmode-rolebased-price'),
                'type' => 'multiselect',
                'desc' => __('Select the user role(s) for which you do not want to provide access to Cart and Checkout page', 'elex-catmode-rolebased-price'),
                'css' => 'width:350px',
				'class' => 'chosen_select',
                'id' => 'eh_pricing_discount_cart_user_role_remove_cart_checkout',
                'options' => $user_roles,
                'desc_tip' => true
            ),
            'eh_pricing_discount_user_role_title_end' => array(
                'type' => 'sectionend',
                'id' => 'eh_pricing_discount_user_role'
            ),
            'eh_pricing_adjustment_specific_user_role_title' => array(
                'title' => __('Individual Product Pricing Specific Options:', 'elex-catmode-rolebased-price'),
                'type' => 'title',
                'description' => '',
                'id' => 'eh_pricing_adjustment_specific_user_role'
            ),
            'product_price_user_role' => array(
                'title' => __('Select User Roles', 'elex-catmode-rolebased-price'),
                'type' => 'multiselect',
                'desc' => __('Choose the specific user roles for which you want to enable custom price option in the individual product settings. Go to each individual products admin page (edit product) and navigate to the General and Role-based Settings tab to update the price and price adjustments.', 'elex-catmode-rolebased-price'),
				'class' => 'chosen_select',
                'id' => 'eh_pricing_discount_product_price_user_role',
                'options' => $user_roles,
                'desc_tip' => true
            ),

			
            'product_adjustment_on_users' => array(
                'type' => 'productdiscountonusers',
                'id' => 'eh_pricing_discount_product_on_users',
                'value' => ''
            ),

			'eh_multiple_role_title' => array(
                'type' => 'title',
                'id' => 'eh_pricing_discount_multiple_role_price'
            ),

			'multiple_user_role_price' => array(
                'title' => __('Users With Multiple Roles Assigned', 'elex-catmode-rolebased-price'),
                'type' => 'radio',
				'required'        => true,
                'desc' => __('Select how you want to apply the price adjustment when multiple user roles are enabled for the same user. ', 'elex-catmode-rolebased-price'),
                'class' => 'form-row-wide',
                'id' => 'eh_pricing_discount_multiple_role_price',
                'options' => array(
					'max_role_price'    => 'Take the highest price adjustment value from available roles.',
					'min_role_price'    => 'Take the lowest price adjustment value from available roles.',
					'consolidate_price'    => 'Take a consolidated value by adding all available price adjustment values.'
				),
                'desc_tip' => true
            ),
			
			'eh_multiple_role_title_end' => array(
                'type' => 'sectionend',
                'id' => 'eh_pricing_discount_multiple_role_price'
            ),
            
            'eh_pricing_discount_adjustment_title' => array(
                'title' => __('Price Adjustment: (Discount/Markup)', 'elex-catmode-rolebased-price'),
                'type' => 'title',
                'desc' => __("Drag and drop User Roles to set priority. If a single User has multiple User Roles assigned, the User Role with the highest priority will be chosen. Select a category to apply price adjustment to the products which belong to that category. If no particular category is selected, the price adjustment will be applied to all the products.<br><p><strong>Price Adjustment - Choose 'D' for DISCOUNT and 'M' for MARKUP.</strong></p>","elex-catmode-rolebased-price"),
                'id' => 'eh_pricing_discount_adjustment'
            ),
            'product_choose_sale_regular' => array(
                'title' => __(' Price Adjustment applied to ', 'elex-catmode-rolebased-price'),
                'type' => 'select',
                'css'  => 'padding: 0px;',
                'desc' => __('Select where you want to apply the discount/markup. This is applicable to individual product level price adjustment also. If a product does not have sale price, adjustment will be applied only to the regular price.', 'elex-catmode-rolebased-price'),
                'default' => 'sale',
                'id' => 'eh_product_choose_sale_regular',
                'options' => $price_sale_regular,
                
                'desc_tip' => true
            ),
            'price_adjustment_options' => array(
                'type' => 'priceadjustmenttable',
                'id' => 'eh_pricing_discount_price_adjustment_options',
                'value' => ''
            ),
            'eh_pricing_discount_adjustment_title_end' => array(
                'type' => 'sectionend',
                'id' => 'eh_pricing_discount_adjustment'
            ),
        );
        return apply_filters('eh_pricing_discount_general_settings', $settings);
    }

    //function to generate price adjustment table
    public function elex_rp_pricing_admin_field_priceadjustmenttable($settings) {
        include( 'elex-html-price-adjustment-table.php' );
    }
    public function elex_rp_pricing_admin_field_productdiscountonusers ($settings) {
        $saved_users = get_option('eh_pricing_discount_product_on_users');
        ?>
            <table id="eh_pricing_discount_product_on_users">
                <tr>
                    <td style="width: 15.5%; font-size: 14px;"><b><?php _e('Select Users', 'elex-catmode-rolebased-price'); ?></b></td>
                    <td style="width: 2%; padding-left:30px;"><span class="woocommerce-help-tip" data-tip="<?php _e('Choose the specific users for which you want to enable custom price in the  individual product settings. Go to each individual products admin page (edit product) and navigate to the General and Role-based Settings tabs to update the price and price adjustments.','elex-catmode-rolebased-price')?>"></span></td>
                    <td><select style="width: 31.75em;"  data-placeholder="N/A" class="wc-customer-search" name="eh_pricing_discount_product_on_users[users][]" multiple="multiple" style="width: 25%;float: left;">
                        <?php
                            $user_ids = (is_array($saved_users ) && !empty($saved_users ))? $saved_users['users']  : array();  // selected user ids
                            foreach ($user_ids as $user_id) {
                                $user = get_user_by('id', $user_id);
                                if (is_object($user)) {
                                    echo '<option value="' . esc_attr($user_id) . '"' . selected(true, true, false) . '>'.$user->display_name.'(#'.$user->ID.') - '.$user->user_email.'</option>';
                                }
                            }
                            ?>
                    </select></td>
                </tr>
            </table>
        <?php
    }

    //function to generate tax options table
    public function elex_rp_pricing_admin_field_taxoptiontable($settings) {
        include( 'elex-html-tax-options.php' );
    }

    //function to generate tax price suffix table
    public function elex_rp_pricing_admin_field_rolepricesuffix($settings) {
        include( 'elex-html-price-suffix.php' );
    }

    //function to add a prodcut tab in product page
    public function elex_rp_add_product_tab($product_data_tabs) {
        $product_data_tabs['product_price_adjustment'] = array(
            'label' => __('Role-based Settings', 'elex-catmode-rolebased-price'),
            'target' => 'product_price_adjustment_data',
            'class' => Array(),
        );
        $product_data_tabs['product_price_adjustment_catalog'] = array(
            'label' => __('Catalog Mode', 'elex-catmode-rolebased-price'),
            'target' => 'product_price_adjustment_data_catalog',
            'class' => Array(),
        );
        return $product_data_tabs;
    }

    public function elex_rp_add_price_adjustment_data_fields() {
        global $woocommerce, $post;
        $settings = array('hide_regular_price' => array(
                'title' => __('Hide Regular Price', 'elex-catmode-rolebased-price'),
                'type' => 'check',
                'desc' => __('Check to hide product regular price', 'elex-catmode-rolebased-price'),
                'css' => 'width:100%',
                'id' => 'eh_pricing_discount_hide_regular_price',
            )
        );
        ?>
        <!-- id below must match target registered in above add_my_custom_product_data_tab function -->
        <div id="product_price_adjustment_data" class="panel woocommerce_options_panel hidden">
            <?php include( 'elex-html-product-price-adjustment.php' ); ?>
        </div>
        <div id="product_price_adjustment_data_catalog" class="panel woocommerce_options_panel hidden">
            <?php include( 'elex-html-product-price-adjustment_catalog.php' ); ?>
        </div>
        <?php
    }

    function elex_rp_add_price_extra_fields() {
            echo '<div id="general_role_based_price" style="padding: 3%; >';
            include( 'elex-html-product-role-based-price.php' );
            echo '</div>';
    }

    public function elex_rp_add_custom_general_fields_save($post_id) {
        //catalog mode individual products
        $catalog_mode_addtocart = (isset($_POST['product_adjustment_hide_addtocart_catalog']) && ($_POST['product_adjustment_hide_addtocart_catalog'] == 'on')) ? 'yes' : 'no';
        if (!empty($catalog_mode_addtocart)) {
            update_post_meta($post_id, 'product_adjustment_hide_addtocart_catalog', $catalog_mode_addtocart);
        }
        $hide_addtocart_shop_catalog = (isset($_POST['product_adjustment_hide_addtocart_catalog_shop']) && ($_POST['product_adjustment_hide_addtocart_catalog_shop'] == 'on')) ? 'yes' : 'no';
        if (!empty($hide_addtocart_shop_catalog)) {
            update_post_meta($post_id, 'product_adjustment_hide_addtocart_catalog_shop', $hide_addtocart_shop_catalog);
        }
        $hide_addtocart_product_catalog = (isset($_POST['product_adjustment_hide_addtocart_catalog_product']) && ($_POST['product_adjustment_hide_addtocart_catalog_product'] == 'on')) ? 'yes' : 'no';
        if (!empty($hide_addtocart_product_catalog)) {
            update_post_meta($post_id, 'product_adjustment_hide_addtocart_catalog_product', $hide_addtocart_product_catalog);
        }
        $this->elex_rp_catalog_default_check_for_hide_addtocart($post_id, 'product_adjustment_hide_addtocart_catalog_shop', 'product_adjustment_hide_addtocart_catalog_product');
        
        if (isset($_POST['product_adjustment_hide_addtocart_placeholder_catalog'])) {
            update_post_meta($post_id, 'product_adjustment_hide_addtocart_placeholder_catalog', wp_kses_post($_POST['product_adjustment_hide_addtocart_placeholder_catalog']));
        }
        
        $customize_checkbox_catalog = (isset($_POST['product_adjustment_customize_addtocart_catalog']) && ($_POST['product_adjustment_customize_addtocart_catalog'] == 'on')) ? 'yes' : 'no';
        if (!empty($customize_checkbox_catalog)) {
            update_post_meta($post_id, 'product_adjustment_customize_addtocart_catalog', $customize_checkbox_catalog);
        }
        if (isset($_POST['product_adjustment_customize_addtocart_prod_btn_text_catalog'])) {
            update_post_meta($post_id, 'product_adjustment_customize_addtocart_prod_btn_text_catalog', sanitize_text_field($_POST['product_adjustment_customize_addtocart_prod_btn_text_catalog']));
        }
        if (isset($_POST['product_adjustment_customize_addtocart_shop_btn_text_unregistered'])) {
            update_post_meta($post_id, 'product_adjustment_customize_addtocart_shop_btn_text_catalog', sanitize_text_field($_POST['product_adjustment_customize_addtocart_shop_btn_text_catalog']));
        }
        if (isset($_POST['product_adjustment_customize_addtocart_btn_url_unregistered'])) {
            update_post_meta($post_id, 'product_adjustment_customize_addtocart_btn_url_catalog', sanitize_text_field($_POST['product_adjustment_customize_addtocart_btn_url_catalog']));
        }
        
        //to update product hide price for catalog
        $catalog_price = (isset($_POST['product_adjustment_hide_price_catalog']) && ($_POST['product_adjustment_hide_price_catalog'] == 'on')) ? 'yes' : 'no';
        if (!empty($catalog_price)) {
            update_post_meta($post_id, 'product_adjustment_hide_price_catalog', $catalog_price);
        }
        //to update hide price placeholder for catalog
        if (isset($_POST['product_adjustment_hide_price_placeholder_catalog'])) {
            update_post_meta($post_id, 'product_adjustment_hide_price_placeholder_catalog', wp_kses_post($_POST['product_adjustment_hide_price_placeholder_catalog']));
        }
        
        $exlude_admin_catalog = (isset($_POST['product_adjustment_exclude_admin_catalog']) && ($_POST['product_adjustment_exclude_admin_catalog'] == 'on')) ? 'yes' : 'no';
        if (!empty($exlude_admin_catalog)) {
            update_post_meta($post_id, 'product_adjustment_exclude_admin_catalog', $exlude_admin_catalog);
        }
        
        //--------------------------------------------------------
        //to update product hide Add to Cart for unregistered users
        $woocommerce_adjustment_field = (isset($_POST['product_adjustment_hide_addtocart_unregistered']) && ($_POST['product_adjustment_hide_addtocart_unregistered'] == 'on')) ? 'yes' : 'no';
        if (!empty($woocommerce_adjustment_field)) {
            update_post_meta($post_id, 'product_adjustment_hide_addtocart_unregistered', $woocommerce_adjustment_field);
        }
        $hide_addtocart_shop = (isset($_POST['product_adjustment_hide_addtocart_unregistered_shop']) && ($_POST['product_adjustment_hide_addtocart_unregistered_shop'] == 'on')) ? 'yes' : 'no';
        if (!empty($hide_addtocart_shop)) {
            update_post_meta($post_id, 'product_adjustment_hide_addtocart_unregistered_shop', $hide_addtocart_shop);
        }
        $hide_addtocart_product = (isset($_POST['product_adjustment_hide_addtocart_unregistered_product']) && ($_POST['product_adjustment_hide_addtocart_unregistered_product'] == 'on')) ? 'yes' : 'no';
        if (!empty($hide_addtocart_product)) {
            update_post_meta($post_id, 'product_adjustment_hide_addtocart_unregistered_product', $hide_addtocart_product);
        }
        $this->elex_rp_catalog_default_check_for_hide_addtocart($post_id, 'product_adjustment_hide_addtocart_unregistered_shop', 'product_adjustment_hide_addtocart_unregistered_product');

        //to update add to cart placeholder for unregistered users
        if (isset($_POST['product_adjustment_hide_addtocart_placeholder_unregistered'])) {
            update_post_meta($post_id, 'product_adjustment_hide_addtocart_placeholder_unregistered', wp_kses_post($_POST['product_adjustment_hide_addtocart_placeholder_unregistered']));
        }
        
        //to update product customize Add to Cart for unregistered users
        $customize_checkbox_unregistered = (isset($_POST['product_adjustment_customize_addtocart_unregistered']) && ($_POST['product_adjustment_customize_addtocart_unregistered'] == 'on')) ? 'yes' : 'no';
        if (!empty($woocommerce_adjustment_field)) {
            update_post_meta($post_id, 'product_adjustment_customize_addtocart_unregistered', $customize_checkbox_unregistered);
        }
        if (isset($_POST['product_adjustment_customize_addtocart_prod_btn_text_unregistered'])) {
            update_post_meta($post_id, 'product_adjustment_customize_addtocart_prod_btn_text_unregistered', sanitize_text_field($_POST['product_adjustment_customize_addtocart_prod_btn_text_unregistered']));
        }
        if (isset($_POST['product_adjustment_customize_addtocart_shop_btn_text_unregistered'])) {
            update_post_meta($post_id, 'product_adjustment_customize_addtocart_shop_btn_text_unregistered', sanitize_text_field($_POST['product_adjustment_customize_addtocart_shop_btn_text_unregistered']));
        }
        if (isset($_POST['product_adjustment_customize_addtocart_btn_url_unregistered'])) {
            update_post_meta($post_id, 'product_adjustment_customize_addtocart_btn_url_unregistered', sanitize_text_field($_POST['product_adjustment_customize_addtocart_btn_url_unregistered']));
        }
        
        //to update product role based hide Add to Cart for user role
        $woocommerce_product_price_hide_field = (isset($_POST['eh_pricing_adjustment_product_addtocart_user_role'])) ? $_POST['eh_pricing_adjustment_product_addtocart_user_role'] : '';
        update_post_meta($post_id, 'eh_pricing_adjustment_product_addtocart_user_role', $woocommerce_product_price_hide_field);
        
        $hide_addtocart_shop_role = (isset($_POST['product_adjustment_hide_addtocart_user_role_shop']) && ($_POST['product_adjustment_hide_addtocart_user_role_shop'] == 'on')) ? 'yes' : 'no';
        if (!empty($hide_addtocart_shop_role)) {
            update_post_meta($post_id, 'product_adjustment_hide_addtocart_user_role_shop', $hide_addtocart_shop_role);
        }
        $hide_addtocart_product_role = (isset($_POST['product_adjustment_hide_addtocart_user_role_product']) && ($_POST['product_adjustment_hide_addtocart_user_role_product'] == 'on')) ? 'yes' : 'no';
        if (!empty($hide_addtocart_product_role)) {
            update_post_meta($post_id, 'product_adjustment_hide_addtocart_user_role_product', $hide_addtocart_product_role);
        }
        $this->elex_rp_catalog_default_check_for_hide_addtocart($post_id, 'product_adjustment_hide_addtocart_user_role_shop', 'product_adjustment_hide_addtocart_user_role_product');
        
        
        //to update hide add  to cart placeholder for user role
        if (isset($_POST['product_adjustment_hide_addtocart_placeholder_role'])) {
            update_post_meta($post_id, 'product_adjustment_hide_addtocart_placeholder_role', wp_kses_post($_POST['product_adjustment_hide_addtocart_placeholder_role']));
        }
        
        //to update product role based customizee Add to Cart for user role
        $customize_addtocart_role = (isset($_POST['eh_pricing_adjustment_product_customize_addtocart_user_role'])) ? $_POST['eh_pricing_adjustment_product_customize_addtocart_user_role'] : '';
        update_post_meta($post_id, 'eh_pricing_adjustment_product_customize_addtocart_user_role', $customize_addtocart_role);
        
        if (isset($_POST['product_adjustment_customize_addtocart_prod_btn_text_role'])) {
            update_post_meta($post_id, 'product_adjustment_customize_addtocart_prod_btn_text_role', sanitize_text_field($_POST['product_adjustment_customize_addtocart_prod_btn_text_role']));
        }
        if (isset($_POST['product_adjustment_customize_addtocart_shop_btn_text_role'])) {
            update_post_meta($post_id, 'product_adjustment_customize_addtocart_shop_btn_text_role', sanitize_text_field($_POST['product_adjustment_customize_addtocart_shop_btn_text_role']));
        }
        if (isset($_POST['product_adjustment_customize_addtocart_btn_url_role'])) {
            update_post_meta($post_id, 'product_adjustment_customize_addtocart_btn_url_role', sanitize_text_field($_POST['product_adjustment_customize_addtocart_btn_url_role']));
        }
        
        //to update hide price placeholder for user role
        if (isset($_POST['product_adjustment_hide_price_placeholder_role'])) {
            update_post_meta($post_id, 'product_adjustment_hide_price_placeholder_role', wp_kses_post($_POST['product_adjustment_hide_price_placeholder_role']));
        }
		//to update product hide regular price for unregistered users
		$woocommerce_adjustment_field = (isset($_POST['product_adjustment_hide_regular_price_unregistered']) && ($_POST['product_adjustment_hide_regular_price_unregistered'] == 'on')) ? 'yes' : 'no';
        if (!empty($woocommerce_adjustment_field)) {
            update_post_meta($post_id, 'product_adjustment_hide_regular_price_unregistered', $woocommerce_adjustment_field);
        }
        //to update product hide price for unregistered users
        $woocommerce_adjustment_field = (isset($_POST['product_adjustment_hide_price_unregistered']) && ($_POST['product_adjustment_hide_price_unregistered'] == 'on')) ? 'yes' : 'no';
        if (!empty($woocommerce_adjustment_field)) {
            update_post_meta($post_id, 'product_adjustment_hide_price_unregistered', $woocommerce_adjustment_field);
        }
        //to update hide price placeholder for unregistered users
        if (isset($_POST['product_adjustment_hide_price_placeholder_unregistered'])) {
            update_post_meta($post_id, 'product_adjustment_hide_price_placeholder_unregistered', wp_kses_post($_POST['product_adjustment_hide_price_placeholder_unregistered']));
        }
	    //to update product hide regular price for user role
		$woocommerce_product_price_field = (isset($_POST['eh_pricing_adjustment_product_hide_regular_price_user_role'])) ? $_POST['eh_pricing_adjustment_product_hide_regular_price_user_role'] : '';
        update_post_meta($post_id, 'eh_pricing_adjustment_product_hide_regular_price_user_role', $woocommerce_product_price_field);

        //to update product hide price for user role
        $woocommerce_product_price_field = (isset($_POST['eh_pricing_adjustment_product_price_user_role'])) ? $_POST['eh_pricing_adjustment_product_price_user_role'] : '';
        update_post_meta($post_id, 'eh_pricing_adjustment_product_price_user_role', $woocommerce_product_price_field);

        //to update product visibility for unregistered users
        $woocommerce_adjustment_field = (isset($_POST['product_adjustment_product_visibility_unregistered']) && ($_POST['product_adjustment_product_visibility_unregistered'] == 'on')) ? 'yes' : 'no';
        if (!empty($woocommerce_adjustment_field)) {
            update_post_meta($post_id, 'product_adjustment_product_visibility_unregistered', $woocommerce_adjustment_field);
        }

        //to update product visibility for user role
        $woocommerce_product_visibility_field = (isset($_POST['eh_pricing_adjustment_product_visibility_user_role'])) ? $_POST['eh_pricing_adjustment_product_visibility_user_role'] : '';
        update_post_meta($post_id, 'eh_pricing_adjustment_product_visibility_user_role', $woocommerce_product_visibility_field);

        //to update product based price adjustment
        $woocommerce_adjustment_field = (isset($_POST['product_based_price_adjustment']) && ($_POST['product_based_price_adjustment'] == 'on')) ? 'yes' : 'no';
        if (!empty($woocommerce_adjustment_field)) {
            update_post_meta($post_id, 'product_based_price_adjustment', $woocommerce_adjustment_field);
        }

        //to update the product role based adjustment
        $woocommerce_adjustment_field = (isset($_POST['product_price_adjustment'])) ? $_POST['product_price_adjustment'] : '';
        update_post_meta($post_id, 'product_price_adjustment', $woocommerce_adjustment_field);

        //to update the product role based adjustment individual user
        $woocommerce_adjustment_field = (isset($_POST['product_price_adjustment_for_users'])) ? $_POST['product_price_adjustment_for_users'] : '';
        update_post_meta($post_id, 'product_price_adjustment_for_users', $woocommerce_adjustment_field);
        

        //to update the product role based price
        $woocommerce_price_field = (isset($_POST['product_role_based_price'])) ? $_POST['product_role_based_price'] : '';
        
        update_post_meta($post_id, 'product_role_based_price', $woocommerce_price_field);
        if($woocommerce_price_field) {
            foreach ($woocommerce_price_field as $key=>$val) {
                    if(array_key_exists('role_price',$val)){
                     update_post_meta($post_id, 'product_role_based_price_'.$key, $woocommerce_price_field[$key]['role_price']);
                 }
            }
        }
        //to update the product role based price for individual user
        $woocommerce_price_field_user = (!empty($_POST['product_role_based_price_user'])) ? $_POST['product_role_based_price_user'] : '';
       
        update_post_meta($post_id, 'product_role_based_price_user', $woocommerce_price_field_user);
        if($woocommerce_price_field_user) {
            
            foreach ($woocommerce_price_field_user as $key=>$val) {
                    if(array_key_exists('role_price',$val)){
                     update_post_meta($post_id, 'product_role_based_price_user_'.$key, $woocommerce_price_field_user[$key]['role_price']);
                 }
            }
        }
    }
    
    function elex_rp_catalog_default_check_for_hide_addtocart ($post_id, $meta_key_shop, $meta_key_product) {
        $default_check = FALSE;
        if (isset($_POST['meta'])) {
            foreach ($_POST['meta'] as $key => $val) {
                if ($val['key'] == $meta_key_shop) {
                    $default_check = TRUE;
                    break;
                }
            }
        }
        if (!$default_check) {
            update_post_meta($post_id, $meta_key_shop, 'yes');
            update_post_meta($post_id, $meta_key_product, 'yes');
        }
    }

    //function to generate price adjustment table
    public function elex_rp_pricing_category_adjustment_fields($tag) {
        $t_id = $tag->term_id;
        $cat_meta = get_option("category_$t_id");
        print_r($cat_meta);
        print_r($t_id);
        print_r($tag);
        ?>
        <tr class="form-field">
            <th scope="row" valign="top"><label for="meta-color"><?php _e('Category Name Background Color'); ?></label></th>
            <td>
                <div id="colorpicker">
                    <input type="text" name="cat_meta[catBG]" class="colorpicker" size="3" style="width:20%;" value="<?php echo (isset($cat_meta['catBG'])) ? $cat_meta['catBG'] : '#fff'; ?>" />
                </div>
                <br />
                <span class="description"><?php _e(''); ?></span>
                <br />
            </td>
        </tr>
        <?php
    }

    public function elex_rp_variation_settings_fields($loop, $variation_data, $variation) {
            include( 'elex-html-variation-product-role-based-price.php' );
    }

    public function elex_rp_save_variable_fields() {
        if (isset($_POST['variable_sku'])) {
            $variable_sku = $_POST['variable_sku'];
            $variable_post_id = $_POST['variable_post_id'];
            $role_based_price = $_POST['product_role_based_price'];
            $role_based_price_user = $_POST['product_role_based_price_user'];
            $counter = max(array_keys($variable_post_id));
                      
            for ($i = 0; $i <= $counter; $i++) {
                if(!isset($variable_post_id[$i])){
                    continue;
                }
                $variation_id = (int) $variable_post_id[$i];
                if (isset($role_based_price[$i])) {
                        update_post_meta($variation_id, 'product_role_based_price', $role_based_price[$i]);
                        // for loop is used only for CSV Import and Export based on user role 
                         foreach ($role_based_price[$i] as $key => $value) {
                        update_post_meta($variation_id, 'product_role_based_price_'.$key, $value['role_price']);   
                    }
                } 
                if (isset($role_based_price_user[$i])) {
                    update_post_meta($variation_id, 'product_role_based_price_user', $role_based_price_user[$i]);
                    // for loop is used only for CSV Import and Export based on user role 
                     foreach ($role_based_price_user[$i] as $key => $value) {
                    update_post_meta($variation_id, 'product_role_based_price_user_'.$key, $value['role_price']);   
                }
            }
            }
        }
    }
  
}

new Elex_Pricing_Discount_Settings();
