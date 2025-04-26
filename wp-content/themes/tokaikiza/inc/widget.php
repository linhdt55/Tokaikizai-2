<?php

/**
 * Track product views.
 */
function wc_track_product_view_by_toki()
{
    if (!is_singular('product') || !is_active_widget(false, false, 'woocommerce_recently_viewed_products_by_toki', true)) {
        return;
    }

    global $post;

    if (empty($_COOKIE['woocommerce_recently_viewed_bytoki'])) { // @codingStandardsIgnoreLine.
        $viewed_products = array();
    } else {
        $viewed_products = wp_parse_id_list((array) explode('|', wp_unslash($_COOKIE['woocommerce_recently_viewed_bytoki']))); // @codingStandardsIgnoreLine.
    }

    // Unset if already in viewed products list.
    $keys = array_flip($viewed_products);

    if (isset($keys[$post->ID])) {
        unset($viewed_products[$keys[$post->ID]]);
    }

    $viewed_products[] = $post->ID;

    if (count($viewed_products) > 15) {
        array_shift($viewed_products);
    }

    // Store for session only.
    wc_setcookie('woocommerce_recently_viewed_bytoki', implode('|', $viewed_products));
}

add_action('template_redirect', 'wc_track_product_view_by_toki', 20);

/**
 * Register Widgets.
 *
 * @since 2.3.0
 */
function wc_register_widgets_by_toki()
{
    register_widget('WC_Widget_Recently_Viewed_By_Tokaikiza');
}
add_action('widgets_init', 'wc_register_widgets_by_toki');

/**
 * Widget recently viewed.
 */
class WC_Widget_Recently_Viewed_By_Tokaikiza extends WC_Widget
{

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->widget_cssclass = 'woocommerce widget_recently_viewed_products_by_toki';
        $this->widget_description = __("Display a list of a customer's recently viewed products custom by Tokaikiza.", 'woocommerce');
        $this->widget_id = 'woocommerce_recently_viewed_products_by_toki';
        $this->widget_name = __('Recently Viewed Products list custom by Tokaikiza', 'woocommerce');
        $this->settings = array(
            'title' => array(
                'type' => 'text',
                'std' => __('Recently Viewed Products', 'woocommerce'),
                'label' => __('Title', 'woocommerce'),
            ),
            'number' => array(
                'type' => 'number',
                'step' => 1,
                'min' => 1,
                'max' => 15,
                'std' => 10,
                'label' => __('Number of products to show', 'woocommerce'),
            ),
        );

        parent::__construct();
    }

    /**
     * Output widget.
     *
     * @see WP_Widget
     * @param array $args Arguments.
     * @param array $instance Widget instance.
     */
    public function widget($args, $instance)
    {
        $viewed_products = !empty($_COOKIE['woocommerce_recently_viewed_bytoki']) ? (array) explode('|', wp_unslash($_COOKIE['woocommerce_recently_viewed_bytoki'])) : array(); // @codingStandardsIgnoreLine
        $viewed_products = array_reverse(array_filter(array_map('absint', $viewed_products)));

        if (empty($viewed_products)) {
            return;
        }

        ob_start();

        $number = !empty($instance['number']) ? absint($instance['number']) : $this->settings['number']['std'];

        $query_args = array(
            'posts_per_page' => $number,
            'posts_per_page' => 10,
            'no_found_rows' => 1,
            'post_status' => 'publish',
            'post_type' => 'product',
            'post__in' => $viewed_products,
            'orderby' => 'post__in',
        );

        if ('yes' === get_option('woocommerce_hide_out_of_stock_items')) {
            $query_args['tax_query'] = array(
                array(
                    'taxonomy' => 'product_visibility',
                    'field' => 'name',
                    'terms' => 'outofstock',
                    'operator' => 'NOT IN',
                ),
            ); // WPCS: slow query ok.
        }
        $r = new WP_Query(apply_filters('woocommerce_recently_viewed_products_widget_query_args', $query_args));

        if (
            $r->have_posts()
        ) {

            $this->widget_start($args, $instance);

            echo wp_kses_post(apply_filters('woocommerce_before_widget_product_list', '<ul class="product_list_widget productsct columns-4">'));

            while ($r->have_posts()) {
                $r->the_post();
                //  wc_get_template('content-widget-product.php', $template_args);
                wc_get_template_part('content', 'product');
            }

            echo wp_kses_post(apply_filters('woocommerce_after_widget_product_list', '</ul>'));

            $this->widget_end($args);
        }

        wp_reset_postdata();

        $content = ob_get_clean();

        echo $content; // WPCS: XSS ok.
    }
}
