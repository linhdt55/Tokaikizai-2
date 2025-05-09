<?php

namespace wtierproduct\Banners;

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Class Wtier_Coupon_Cta_Banner
 *
 * @since    2.5.1  This class is responsible for displaying the CTA banner on the coupon edit page.
 */
if (!class_exists('\\Wtierproduct\\Banners\\Wtier_Coupon_Cta_Banner')) {
    class Wtier_Coupon_Cta_Banner {
        /**
         * Constructor.
         */
        public function __construct() { 
            // Check if premium plugin is active
            if (!in_array('wt-smart-coupon-pro/wt-smart-coupon-pro.php', apply_filters('active_plugins', get_option('active_plugins')))) {
                add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
                add_action('add_meta_boxes', array($this, 'add_meta_box'));
            }
        }

        /**
         * Enqueue required scripts and styles.
         */
        public function enqueue_scripts($hook) { 
           if (!in_array($hook, array('post.php', 'post-new.php')) || get_post_type() !== 'shop_coupon') {
                return;
            }

            wp_enqueue_style(
                'wtier-cta-banner',
                plugin_dir_url(__FILE__) . 'assets/css/wtier-cta-banner.css',
                array(),
                WT_P_IEW_VERSION
            );

            wp_enqueue_script(
                'wtier-cta-banner',
                plugin_dir_url(__FILE__) . 'assets/js/wtier-cta-banner.js',
                array('jquery'),
                WT_P_IEW_VERSION,
                true
            );
        }

        /**
         * Add the meta box to the coupon edit screen
         */
        public function add_meta_box() {
            add_meta_box(
                'wtier_coupon_import_export_pro',
                '—',
                array($this, 'render_banner'),
                'shop_coupon',
                'side',
                'low'
            );
        }

        /**
         * Render the banner HTML.
         */
        public function render_banner() {
            $plugin_url = 'https://www.webtoffee.com/product/smart-coupons-for-woocommerce/?utm_source=free_plugin_cross_promotion&utm_medium=marketing_coupons_tab&utm_campaign=Smart_coupons';
            $wt_admin_img_path = WT_P_IEW_PLUGIN_URL . 'assets/images/other_solutions';
            ?>
            <div class="wtier-cta-banner">
                <div class="wtier-cta-content">
                    <div class="wtier-cta-header">
                        <img src="<?php echo esc_url(plugin_dir_url(__FILE__) . 'assets/images/smart-coupon.svg'); ?>" alt="<?php _e('Smart Coupons for WooCommerce Pro', 'product-import-export-for-woo'); ?>" class="wt-smart-coupon-cta-icon">
                        <h3><?php _e('Create better coupon campaigns with advanced WooCommerce coupon features', 'product-import-export-for-woo'); ?></h3>
                    </div>

                    <div class="wtier-cta-features-header">
                        <h2 style="font-size: 13px; font-weight: 700; color: #4750CB;"><?php _e('Smart Coupons for WooCommerce Pro', 'product-import-export-for-woo'); ?></h2>
                    </div>

                    <ul class="wtier-cta-features">
                        <li><?php _e('Auto-apply coupons', 'product-import-export-for-woo'); ?></li>
                        <li><?php _e('Create attractive Buy X Get Y (BOGO) offers', 'product-import-export-for-woo'); ?></li>
                        <li><?php _e('Create product quantity/subtotal based discounts', 'product-import-export-for-woo'); ?></li>
                        <li><?php _e('Offer store credits and gift cards', 'product-import-export-for-woo'); ?></li>
                        <li><?php _e('Set up smart giveaway campaigns', 'product-import-export-for-woo'); ?></li>
                        <li><?php _e('Set advanced coupon rules and conditions', 'product-import-export-for-woo'); ?></li>
                        <li class="hidden-feature"><?php _e('Bulk generate coupons', 'product-import-export-for-woo'); ?></li>
                        <li class="hidden-feature"><?php _e('Shipping, purchase history, and payment method-based coupons', 'product-import-export-for-woo'); ?></li>
                        <li class="hidden-feature"><?php _e('Sign up coupons', 'product-import-export-for-woo'); ?></li>
                        <li class="hidden-feature"><?php _e('Cart abandonment coupons', 'product-import-export-for-woo'); ?></li>
                        <li class="hidden-feature"><?php _e('Create day-specific deals', 'product-import-export-for-woo'); ?></li>
                        <li class="hidden-feature"><?php _e('Display coupon banners and widgets', 'product-import-export-for-woo'); ?></li>
                        <li class="hidden-feature"><?php _e('Import coupons', 'product-import-export-for-woo'); ?></li>
                    </ul>

                    <div class="wtier-cta-footer">
                        <div class="wtier-cta-footer-links">
                            <a href="#" class="wtier-cta-toggle" data-show-text="<?php esc_attr_e('View all premium features', 'product-import-export-for-woo'); ?>" data-hide-text="<?php esc_attr_e('Show less', 'product-import-export-for-woo'); ?>"><?php _e('View all premium features', 'product-import-export-for-woo'); ?></a>
                            <a href="<?php echo esc_url($plugin_url); ?>" class="wtier-cta-button" target="_blank"><img src="<?php echo esc_url($wt_admin_img_path . '/promote_crown.png');?>" style="width: 15.01px; height: 10.08px; margin-right: 8px;"><?php _e('Get the plugin', 'product-import-export-for-woo'); ?></a>
                        </div>
                        <a href="#" class="wtier-cta-dismiss" style="display: block; text-align: center; margin-top: 15px; color: #666; text-decoration: none;"><?php _e('Dismiss', 'product-import-export-for-woo'); ?></a>
                    </div>
                </div>
            </div>
            <?php
        }
    }

    new \wtierproduct\Banners\Wtier_Coupon_Cta_Banner();
} 