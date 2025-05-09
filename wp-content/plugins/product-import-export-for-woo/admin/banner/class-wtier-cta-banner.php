<?php

namespace wtierproduct\Banners;

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Class Wtier_Cta_Banner
 *
 * @since    2.5.1  This class is responsible for displaying the CTA banner on the product edit page.
 */
if (!class_exists('\\Wtierproduct\\Banners\\Wtier_Cta_Banner')) {
    class Wtier_Cta_Banner {
        /**
         * Constructor.
         */
        public function __construct() {  
            // Check if premium plugin is active
            if (!in_array('wt-import-export-for-woo-product/wt-import-export-for-woo-product.php', apply_filters('active_plugins', get_option('active_plugins')))) {
                add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
                add_action('add_meta_boxes', array($this, 'add_meta_box'));
            }
        }
        /**
         * Enqueue required scripts and styles.
         */
        public function enqueue_scripts($hook) {
            if (!in_array($hook, array('post.php', 'post-new.php')) || get_post_type() !== 'product') {
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
         * Add the meta box to the product edit screen
         */
        public function add_meta_box() {
            add_meta_box(
                'wtier_product_import_export_pro',
                '—',
                array($this, 'render_banner'),
                'product',
                'side',
                'low'
            );
        }

        /**
         * Render the banner HTML.
         */
        public function render_banner() {
            $plugin_url = 'https://www.webtoffee.com/product/product-import-export-woocommerce/?utm_source=free_plugin_cross_promotion&utm_medium=add_new_product_tab&utm_campaign=Product_import_export';
            $wt_admin_img_path = WT_P_IEW_PLUGIN_URL . 'assets/images/other_solutions';
            ?>
            <div class="wtier-cta-banner">
                <div class="wtier-cta-content">
                    <div class="wtier-cta-header">
                        <img src="<?php echo esc_url(WT_P_IEW_PLUGIN_URL . 'assets/images/gopro/product-ie.svg'); ?>" alt="<?php _e('Product Import Export', 'product-import-export-for-woo'); ?>" class="wtier-cta-icon">
                        <h3><?php _e('Product Import Export for WooCommerce', 'product-import-export-for-woo'); ?></h3>
                    </div>

                    <ul class="wtier-cta-features">
                        <li><?php _e('Import, export, or update WooCommerce products', 'product-import-export-for-woo'); ?></li>
                        <li><?php _e('Supports all types of products (Simple, variable, subscription grouped, and external)', 'product-import-export-for-woo'); ?></li>
                        <li><?php _e('Multiple file formats - CSV, XML, Excel, and TSV', 'product-import-export-for-woo'); ?></li>
                        <li><?php _e('Advanced filters and customizations for better control', 'product-import-export-for-woo'); ?></li>
                        <li class="hidden-feature"><?php _e('Bulk update WooCommerce product data', 'product-import-export-for-woo'); ?></li>
                        <li class="hidden-feature"><?php _e('Import via FTP/SFTP and URL', 'product-import-export-for-woo'); ?></li>
                        <li class="hidden-feature"><?php _e('Schedule automated import & export', 'product-import-export-for-woo'); ?></li>
                        <li class="hidden-feature"><?php _e('Export and Import custom fields and third-party plugin fields', 'product-import-export-for-woo'); ?></li>
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

    new \wtierproduct\Banners\Wtier_Cta_Banner();
}
