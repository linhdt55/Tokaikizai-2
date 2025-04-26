<?php

/**
 * Review request
 *  
 *
 * @package  Cookie_Law_Info  
 */
if (!defined('ABSPATH')) {
    exit;
}
class Order_Import_Export_Review_Request
{
    /**
     * config options 
     */
    private $plugin_title               =   "Order Export & Order Import for WooCommerce";
    private $review_url                 =   "https://wordpress.org/support/plugin/order-import-export-for-woocommerce/reviews/#new-post";
    private $plugin_prefix              =   "wt_o_iew_basic"; /* must be unique name */
    private $activation_hook            =   "wt_o_iew_basic_activate"; /* hook for activation, to store activated date */
    private $deactivation_hook          =   "wt_o_iew_basic_deactivate"; /* hook for deactivation, to delete activated date */
    private $days_to_show_banner        =   7; /* when did the banner to show */
    private $remind_days                =   5; /* remind interval in days */
    private $webtoffee_logo_url         =   WT_O_IEW_PLUGIN_URL . 'assets/images/webtoffee-logo_small.png';



    private $start_date                 =   0; /* banner to show count start date. plugin installed date, remind me later added date */
    private $current_banner_state       =   2; /* 1: active, 2: waiting to show(first after installation), 3: closed by user/not interested to review, 4: user done the review, 5:remind me later */
    private $banner_state_option_name   =   ''; /* WP option name to save banner state */
    private $start_date_option_name     =   ''; /* WP option name to save start date */
    private $banner_css_class           =   ''; /* CSS class name for Banner HTML element. */
    private $banner_message             =   ''; /* Banner message. */
    private $later_btn_text             =   ''; /* Remind me later button text */
    private $never_btn_text             =   ''; /* Never review button text. */
    private $review_btn_text            =   ''; /* Review now button text. */
    private $ajax_action_name           =   ''; /* Name of ajax action to save banner state. */
    private $allowed_action_type_arr    = array(
        'later', /* remind me later */
        'never', /* never */
        'review', /* review now */
        'closed', /* not interested */
    );

    public function __construct()
    {
        //Set config vars
        $this->set_vars();

        add_action($this->activation_hook, array($this, 'on_activate'));
        add_action($this->deactivation_hook, array($this, 'on_deactivate'));
        add_action('admin_notices', array($this, 'show_banner_cta'));


        if ($this->check_condition()) /* checks the banner is active now */ {
            $this->banner_message = sprintf(__("Hey, we at %sWebToffee%s would like to thank you for using our plugin. We would really appreciate if you could take a moment to drop a quick review that will inspire us to keep going."), '<b>', '</b>');

            /* button texts */
            $this->later_btn_text   = __("Remind me later");
            $this->never_btn_text   = __("Not interested");
            $this->review_btn_text  = __("Review now");

            add_action('admin_notices', array($this, 'show_banner')); /* show banner */
            add_action('admin_print_footer_scripts', array($this, 'add_banner_scripts')); /* add banner scripts */
            add_action('wp_ajax_' . $this->ajax_action_name, array($this, 'process_user_action')); /* process banner user action */
        }
    }

    /**
     *	Set config vars
     */
    public function set_vars()
    {
        $this->ajax_action_name             =   $this->plugin_prefix . '_process_user_review_action';
        $this->banner_state_option_name     =   $this->plugin_prefix . "_review_request";
        $this->start_date_option_name       =   $this->plugin_prefix . "_start_date";
        $this->banner_css_class             =   $this->plugin_prefix . "_review_request";

        $this->start_date                   =   absint(get_option($this->start_date_option_name));
        $banner_state                       =   absint(get_option($this->banner_state_option_name));
        $this->current_banner_state         =   ($banner_state == 0 ? $this->current_banner_state : $banner_state);
    }

    /**
     *	Actions on plugin activation
     *	Saves activation date
     */
    public function on_activate()
    {
        $this->reset_start_date();
    }

    /**
     *	Actions on plugin deactivation
     *	Removes activation date
     */
    public function on_deactivate()
    {
        delete_option($this->start_date_option_name);
    }

    /**
     *	Reset the start date. 
     */
    private function reset_start_date()
    {
        update_option($this->start_date_option_name, time());
    }

    /**
     *	Update the banner state 
     */
    private function update_banner_state($val)
    {
        update_option($this->banner_state_option_name, $val);
    }

    /**
     *	Prints the banner 
     */
    public function show_banner()
    {
        $this->update_banner_state(1); /* update banner active state */
?>
        <div class="<?php echo $this->banner_css_class; ?> notice-info notice is-dismissible">
            <?php
            if ($this->webtoffee_logo_url != "") {
            ?>
                <h3 style="margin: 10px 0;"><?php echo $this->plugin_title; ?></h3>
            <?php
            }
            ?>
            <p>
                <?php echo $this->banner_message; ?>
            </p>
            <p>
                <a class="button button-secondary" style="color:#333; border-color:#ccc; background:#efefef;" data-type="later"><?php echo $this->later_btn_text; ?></a>
                <a class="button button-primary" data-type="review"><?php echo $this->review_btn_text; ?></a>
            </p>
            <div class="wt-cli-review-footer" style="position: relative;">
                <span class="wt-cli-footer-icon" style="position: absolute;right: 0;bottom: 10px;"><img src="<?php echo $this->webtoffee_logo_url; ?>" style="max-width:100px;"></span>
            </div>
        </div>
    <?php
    }

    /**
     *	Ajax hook to process user action on the banner
     */
    public function process_user_action()
    {
        check_ajax_referer($this->plugin_prefix);
        if (isset($_POST['wt_review_action_type'])) {
            $action_type = sanitize_text_field($_POST['wt_review_action_type']);

            /* current action is in allowed action list */
            if (in_array($action_type, $this->allowed_action_type_arr)) {
                if ($action_type == 'never' || $action_type == 'closed') {
                    $new_banner_state = 3;
                } elseif ($action_type == 'review') {
                    $new_banner_state = 4;
                } else {
                    /* reset start date to current date */
                    $this->reset_start_date();
                    $new_banner_state = 5; /* remind me later */
                }
                $this->update_banner_state($new_banner_state);
            }
        }
        exit();
    }

    /**
     *	Add banner JS to admin footer
     */
    public function add_banner_scripts()
    {
        $ajax_url = admin_url('admin-ajax.php');
        $nonce = wp_create_nonce($this->plugin_prefix);
    ?>
        <script type="text/javascript">
            (function($) {
                "use strict";

                /* prepare data object */
                var data_obj = {
                    _wpnonce: '<?php echo $nonce; ?>',
                    action: '<?php echo $this->ajax_action_name; ?>',
                    wt_review_action_type: ''
                };

                $(document).on('click', '.<?php echo $this->banner_css_class; ?> a.button', function(e) {
                    e.preventDefault();
                    var elm = $(this);
                    var btn_type = elm.attr('data-type');
                    if (btn_type == 'review') {
                        window.open('<?php echo $this->review_url; ?>');
                    }
                    elm.parents('.<?php echo $this->banner_css_class; ?>').hide();

                    data_obj['wt_review_action_type'] = btn_type;
                    $.ajax({
                        url: '<?php echo $ajax_url; ?>',
                        data: data_obj,
                        type: 'POST'
                    });

                }).on('click', '.<?php echo $this->banner_css_class; ?> .notice-dismiss', function(e) {
                    e.preventDefault();
                    data_obj['wt_review_action_type'] = 'closed';
                    $.ajax({
                        url: '<?php echo $ajax_url; ?>',
                        data: data_obj,
                        type: 'POST',
                    });

                });

            })(jQuery)
        </script>
        <?php
    }

    /**
     *	Checks the condition to show the banner
     */
    private function check_condition()
    {

        if ($this->current_banner_state == 1) /* currently showing then return true */ {
            return true;
        }

        if ($this->current_banner_state == 2 || $this->current_banner_state == 5) /* only waiting/remind later state */ {
            if ($this->start_date == 0) /* unable to get activated date */ {
                /* set current date as activation date*/
                $this->reset_start_date();
                return false;
            }

            $days = ($this->current_banner_state == 2 ? $this->days_to_show_banner : $this->remind_days);

            $date_to_check = $this->start_date + (86400 * $days);
            if ($date_to_check <= time()) /* time reached to show the banner */ {
                return true;
            } else {
                return false;
            }
        }

        return false;
    }
    public function show_banner_cta()
    {
        // Check if the WooCommerce Order Import Export plugin is active
        if (is_plugin_active('order-import-export-for-woocommerce/order-import-export-for-woocommerce.php')) {
    
            // Get the current screen object
            $screen = get_current_screen();
    
            // Check if we're on the WooCommerce Reports page
            if ($screen->id == 'woocommerce_page_wc-reports') {
                // Set 'orders' as default tab if no 'tab' is set
                $current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'orders';
    
                // Define content and plugin URL based on the current tab
                $content = '';
                $plugin_url = '';
                $title = esc_html__('Did You Know?', 'order-import-export-for-woocommerce');
                $cookie_name = ''; // We'll set this based on the current tab
    
                switch ($current_tab) {
                    case 'orders':
                        // Check if the 'orders' banner has been hidden
                        $cookie_name = 'hide_cta_orders';
                        if (isset($_COOKIE[$cookie_name]) && sanitize_text_field($_COOKIE[$cookie_name]) == 'true') {
                            return; // Don't show the banner if the cookie is set
                        }
    
                        $content = '<span style="color: #212121;">' . esc_html__('You can now export WooCommerce order', 'order-import-export-for-woocommerce') . '</span> <span style="color: #5454A5; font-weight: bold;">' . esc_html__('data with custom filters, custom metadata, FTP export, and scheduling options.', 'order-import-export-for-woocommerce') . '</span> <span style="color: #212121;">' . esc_html__('Bulk edit or update orders using CSV, XML, Excel, or TSV files in one go.', 'order-import-export-for-woocommerce') . '</span>';
                        $plugin_url = 'https://www.webtoffee.com/product/order-import-export-plugin-for-woocommerce/?utm_source=free_plugin_report&utm_medium=basic_revamp&utm_campaign=Order_Import_Export';
                        break;
    
                    case 'customers':
                        // Check if the 'customers' banner has been hidden
                        $cookie_name = 'hide_cta_customers';
                        if (isset($_COOKIE[$cookie_name]) && sanitize_text_field($_COOKIE[$cookie_name]) == 'true') {
                            return; // Don't show the banner if the cookie is set
                        }
    
                        $content = '<span style="color: #212121;">' . esc_html__('You can easily bulk export your customers’', 'order-import-export-for-woocommerce') . '</span> <span style="color: #5454A5; font-weight: bold;">' . esc_html__('data to CSV, XML, Excel, or TSV files in just a few clicks.', 'order-import-export-for-woocommerce') . '</span> <span style="color: #212121;">' . esc_html__('Export custom user metadata of third-party plugins seamlessly.', 'order-import-export-for-woocommerce') . '</span>';
                        $plugin_url = 'https://www.webtoffee.com/product/wordpress-users-woocommerce-customers-import-export/?utm_source=free_plugin_report&utm_medium=basic_revamp&utm_campaign=User_Import_Export';
                        break;
    
                    case 'stock':
                        // Check if the 'stock' banner has been hidden
                        $cookie_name = 'hide_cta_stock';
                        if (isset($_COOKIE[$cookie_name]) && sanitize_text_field($_COOKIE[$cookie_name]) == 'true') {
                            return; // Don't show the banner if the cookie is set
                        }
    
                        $content = '<span style="color: #212121;">' . esc_html__('Get your store products', 'order-import-export-for-woocommerce') . '</span> <span style="color: #5454A5; font-weight: bold;">' . esc_html__('bulk exported for hassle-free migration, inventory management, and bookkeeping.', 'order-import-export-for-woocommerce') . '</span> <span style="color: #212121;">' . esc_html__('Import/export WooCommerce products with reviews, images, and custom metadata.', 'order-import-export-for-woocommerce') . '</span>';
                        $plugin_url = 'https://www.webtoffee.com/product/product-import-export-woocommerce/?utm_source=free_plugin_report&utm_medium=basic_revamp&utm_campaign=Product_Import_Export';
                        break;
    
                    case 'subscriptions':
                        // Check if the 'subscriptions' banner has been hidden
                        $cookie_name = 'hide_cta_subscriptions';
                        if (isset($_COOKIE[$cookie_name]) && sanitize_text_field($_COOKIE[$cookie_name]) == 'true') {
                            return; // Don't show the banner if the cookie is set
                        }
    
                        $content = '<span style="color: #212121;">' . esc_html__('Get your subscription orders exported to a', 'order-import-export-for-woocommerce') . '</span> <span style="color: #5454A5; font-weight: bold;">' . esc_html__('CSV, XML, Excel, or TSV file.', 'order-import-export-for-woocommerce') . '</span> <span style="color: #212121;">' . esc_html__('Featuring scheduled exports, advanced filters, custom metadata, and more.', 'order-import-export-for-woocommerce') . '</span>';
                        $plugin_url = 'https://www.webtoffee.com/product/order-import-export-plugin-for-woocommerce/?utm_source=free_plugin_report&utm_medium=basic_revamp&utm_campaign=Order_Import_Export';
                        break;
    
                    default:
                        return; // Exit if not on a recognized tab
                }
    
                // HTML for the banner remains unchanged
                ?>
                <div id="cta-banner" class="notice notice-info" style="position: relative; padding: 15px; background-color: #f3f0ff; border-left: 4px solid #5454A5; display: flex; justify-content: space-between; align-items: center; border-radius: 1px;">
                    <div style="flex: 1; margin-right: 10px;">
                        <div style="display: flex; align-items: center; margin-bottom: 5px;">
                            <img src="<?php echo esc_url(WT_O_IEW_PLUGIN_URL . 'assets/images/idea_bulb_purple.svg'); ?>" style="width: 25px; margin-right: 10px;">
                            <h2 style="margin: 0; font-size: 16px; color: #2d2d77; font-weight: 600;"><?php echo esc_html($title); ?></h2>
                        </div>
                        <p style="margin: 0; font-size: 14px; color: #6f6f6f; line-height: 1.4;"><?php echo wp_kses_post($content); ?></p>
                    </div>
    
                    <div style="display: flex; gap: 10px;">
                        <a href="<?php echo esc_url($plugin_url); ?>" target="_blank" class="button-primary" style="background: #5454A5; color: white; border: none; padding: 8px 15px; border-radius: 4px; text-decoration: none; display: flex; align-items: center; justify-content: center; font-size: 14px;"><?php esc_html_e('Check out plugin ➔', 'order-import-export-for-woocommerce'); ?></a>
                        <button id="maybe-later" class="button-secondary" style="background-color: #f3f0ff; color: #4a42a3; padding: 8px 15px; border: 1px solid #5454A5; border-radius: 4px; font-size: 14px;"><?php esc_html_e('Maybe later', 'order-import-export-for-woocommerce'); ?></button>
                    </div>
                </div>
    
                <script type="text/javascript">
                    (function($) {
                        $('#maybe-later').on('click', function(e) {
                            e.preventDefault();
                            // Set a cookie to hide the banner for 30 days for this specific tab
                            document.cookie = "<?php echo esc_js($cookie_name); ?>=true; path=/; max-age=" + (30*24*60*60) + ";";
                            $('#cta-banner').remove();
                        });
                    })(jQuery);
                </script>
                <?php
            }
        }
    }
    
}
new Order_Import_Export_Review_Request();
