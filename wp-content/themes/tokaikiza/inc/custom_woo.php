<?php

add_filter('woocommerce_product_tabs', 'my_remove_description_tab', 11);

function my_remove_description_tab($tabs)
{
    unset($tabs['additional_information']);
    unset($tabs['reviews']);
    return $tabs;
}
remove_action('woocommerce_before_main_content', 'woocommerce_breadcrumb', 20);
remove_action('woocommerce_before_shop_loop', 'woocommerce_result_count', 20);
remove_action('woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30);
remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40);
remove_action('woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20);

add_action('woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 14);

add_action('woocommerce_result_count_toki', 'woocommerce_result_count');
add_action('woocommerce_catalog_ordering_toki', 'woocommerce_catalog_ordering');
add_action('woocommerce_breadcrumb_toki', 'woocommerce_breadcrumb');
add_action('woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_link_close', 15);

add_filter('woocommerce_breadcrumb_defaults', 'jk_woocommerce_breadcrumbs');
add_filter('body_class', function ($classes) {
	$class_lg = '';
	if( ! is_user_logged_in() ){
		$class_lg = 'not-login';
	}
    if (is_shop()) {
        return array_merge($classes, array('tax-product_cat'));
    }
    if (is_product()) {
        global $post;
        $product = wc_get_product($post->ID);
        $tipo    = 'body-type-' . $product->get_type();
        return array_merge($classes, array($tipo, $class_lg));
    } else {
        return $classes;
    }
});
function jk_woocommerce_breadcrumbs()
{
    return array(
        'delimiter'   => '<span class="delimiter"> / </span>',
        'wrap_before' => '<nav class="woocommerce-breadcrumb" itemprop="breadcrumb">',
        'wrap_after'  => '</nav>',
        'before'      => '<span class="item">',
        'after'       => '</span>',
        'home'        => esc_html__('Home', 'woocommerce'),
    );
}

add_action('woocommerce_before_shop_loop', 'layout_gird_list', 50);
function layout_gird_list()
{
?>
    <div class="group-layout d-flex space-between">
        <div class="d-flex wrap-layout">
            <a href="#" class="opensidebar" data-toggle=".col-sidebar">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M22 3H2L10 12.46V19L14 21V12.46L22 3Z" stroke="#2F2F39" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <span class="text"><?= esc_html__('Filter', 'tokaikiza'); ?></span>
            </a>
            <div class="view-layout">
                <span class="text"><?= esc_html__('View as', 'tokaikiza'); ?></span>
                <a href="#gird" data-layout="grid" class="active">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M10 3H3V10H10V3Z" stroke="#C6CBD9" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M21 3H14V10H21V3Z" stroke="#C6CBD9" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M21 14H14V21H21V14Z" stroke="#C6CBD9" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M10 14H3V21H10V14Z" stroke="#C6CBD9" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>

                </a>
                <a href="#list" data-layout="list">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M8 6H21M8 12H21M8 18H21M3 6H3.01M3 12H3.01M3 18H3.01" stroke="#C6CBD9" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </a>
            </div>
        </div>
        <?php do_action('woocommerce_catalog_ordering_toki'); ?>
    </div>
<?php
}
add_action('woocommerce_shop_loop_item_title', 'woo_show_excerpt_shop_page', 11);
function woo_show_excerpt_shop_page()
{
    global $post;
    $short_description = apply_filters('woocommerce_short_description', $post->post_excerpt);
?>
    <div class="short-description">
        <?php echo $short_description; // WPCS: XSS ok. 
        ?>
    </div>
<?php
}
add_action('woocommerce_sidebar', 'title_istock');
function title_istock()
{
    echo '<input type="hidden" value="' . esc_attr__('In stock') . '" id="title_istock">';
}

add_action('woocommerce_single_product_summary', 'woocommerce_before_shop_brand', 1);
add_action('woocommerce_shop_loop_item_title', 'woocommerce_before_shop_brand', 9);
function woocommerce_before_shop_brand()
{
    echo '<div class="pa_brand">';
    echo join(', ', wp_list_pluck(get_the_terms(get_the_id(), 'pa_brand', 'string'), 'name'));
    echo '</div>';
}
add_action('woocommerce_after_quantity_input_field', 'ts_quantity_plus_sign');

function ts_quantity_plus_sign()
{
    echo '
    <button type="button" class="plus">
        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M8 1V15M1 8H15" stroke="#7E7E8F" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
    </button>';
}

add_action('woocommerce_before_quantity_input_field', 'ts_quantity_minus_sign');
function ts_quantity_minus_sign()
{
    echo '
    <button type="button" class="minus">
        <svg width="16" height="2" viewBox="0 0 16 2" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M1 1H15" stroke="#E2E2EA" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
    </button>';
}

add_action('woocommerce_share', 'woocommerce_share_custom');
function woocommerce_share_custom()
{
    global $product;
    if ($product->get_type() == 'grouped') return;
?>
    <div class="bottom-summary">
        <div class="social-share">
            <div class="label-share"><?php echo esc_html__('Share via: ', 'tokaikiza') ?></div>
            <ul class="menu d-flex">
                <li>
                    <a href="https://social-plugins.line.me/lineit/share?url=<?php echo get_the_permalink(); ?>" title="Line">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M9.57276 9.05518H10.1858C10.2798 9.05518 10.356 9.13138 10.356 9.22518V13.0326C10.356 13.1264 10.2798 13.2024 10.1858 13.2024H9.57276C9.47896 13.2024 9.40256 13.1264 9.40256 13.0326V9.22518C9.40256 9.13138 9.47896 9.05518 9.57276 9.05518Z" fill="#00B900" />
                            <path d="M14.4046 9.05518H13.7918C13.6976 9.05518 13.6216 9.13138 13.6216 9.22518V11.4872L11.8766 9.13078C11.8726 9.12478 11.868 9.11898 11.8634 9.11358L11.8624 9.11238C11.859 9.10878 11.8554 9.10518 11.852 9.10198L11.849 9.09898L11.8459 9.09641C11.8439 9.09473 11.8419 9.09302 11.8398 9.09158C11.8386 9.09033 11.8373 9.08941 11.8359 9.08838L11.8266 9.08198C11.8255 9.08112 11.8242 9.08047 11.8229 9.0798L11.8214 9.07898L11.8124 9.07378L11.807 9.07118L11.8048 9.07021C11.8023 9.06909 11.7998 9.06795 11.7972 9.06718L11.7916 9.06518C11.7884 9.06378 11.785 9.06278 11.7816 9.06178L11.7756 9.06018C11.7724 9.05938 11.7692 9.05878 11.7658 9.05818L11.7588 9.05718L11.7528 9.05633L11.7498 9.05598L11.741 9.05538L11.735 9.05518H11.122C11.0282 9.05518 10.9518 9.13138 10.9518 9.22518V13.0326C10.9518 13.1264 11.0282 13.2024 11.122 13.2024H11.735C11.829 13.2024 11.9052 13.1264 11.9052 13.0326V10.7714L13.6522 13.1306C13.6644 13.1476 13.6792 13.1616 13.6954 13.1726L13.6972 13.174C13.7006 13.1762 13.7042 13.1784 13.7078 13.1802L13.7114 13.1823L13.7126 13.183C13.7152 13.1844 13.7178 13.1858 13.7206 13.187L13.729 13.1904L13.734 13.1924L13.7456 13.1962L13.7482 13.1966C13.7618 13.2004 13.7764 13.2024 13.7918 13.2024H14.4046C14.4986 13.2024 14.5748 13.1264 14.5748 13.0326V9.22518C14.5748 9.13138 14.4986 9.05518 14.4046 9.05518Z" fill="#00B900" />
                            <path d="M17.7894 10.0086C17.8834 10.0086 17.9594 9.9324 17.9594 9.8384V9.2254C17.9594 9.1314 17.8834 9.0552 17.7894 9.0552H15.341C15.295 9.0552 15.2532 9.0736 15.2226 9.1032L15.2213 9.10435C15.2206 9.10507 15.2191 9.10644 15.2185 9.10716C15.2183 9.10744 15.2187 9.10688 15.2185 9.10716C15.1893 9.13756 15.1706 9.1796 15.1706 9.2252V13.0322C15.1706 13.0778 15.1888 13.1196 15.2184 13.15C15.2186 13.1505 15.2189 13.151 15.2193 13.1515C15.2197 13.152 15.2202 13.1524 15.2206 13.1528C15.2212 13.1534 15.2222 13.1544 15.2232 13.1548C15.2536 13.1842 15.2948 13.2026 15.3406 13.2026H17.7894C17.8834 13.2026 17.9594 13.1262 17.9594 13.0322V12.4194C17.9594 12.3254 17.8834 12.249 17.7894 12.249H16.1242V11.6054H17.7894C17.8834 11.6054 17.9594 11.5294 17.9594 11.4352V10.8224C17.9594 10.7284 17.8834 10.652 17.7894 10.652H16.1242V10.0086H17.7894Z" fill="#00B900" />
                            <path d="M8.70832 12.249H7.04292V9.22522C7.04292 9.13122 6.96672 9.05522 6.87272 9.05522H6.25972C6.16592 9.05522 6.08952 9.13122 6.08952 9.22522V13.0322C6.08952 13.0778 6.10792 13.1196 6.13732 13.15L6.13952 13.1528C6.13997 13.1531 6.14087 13.1538 6.14129 13.1542C6.14152 13.1545 6.14111 13.1539 6.14129 13.1542C6.17209 13.1836 6.21392 13.2026 6.25952 13.2026H8.70832C8.80232 13.2026 8.87832 13.1262 8.87832 13.0322V12.4194C8.87832 12.3254 8.80232 12.249 8.70832 12.249Z" fill="#00B900" />
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M18.8 24H5.2C2.3282 24 0 21.6718 0 18.8V5.2C0 2.3282 2.3282 0 5.2 0H18.8C21.6718 0 24 2.3282 24 5.2V18.8C24 21.6718 21.6718 24 18.8 24ZM20.6992 10.9446C20.6992 7.03461 16.7794 3.85339 11.961 3.85339C7.14318 3.85339 3.22298 7.03461 3.22298 10.9446C3.22298 14.45 6.33158 17.3858 10.5308 17.9408C10.8154 18.0022 11.2028 18.1282 11.3006 18.3716C11.3888 18.5926 11.3582 18.9386 11.3288 19.1616C11.3288 19.1616 11.2264 19.7782 11.2042 19.9098L11.2009 19.9288C11.1593 20.1649 11.054 20.7631 11.961 20.3808C12.8938 19.9878 16.993 17.4178 18.8264 15.3076C20.0926 13.9188 20.6992 12.5096 20.6992 10.9446Z" fill="#00B900" />
                        </svg>
                    </a>
                </li>
                <li>
                    <a href="https://twitter.com/intent/tweet?text=<?php echo get_the_permalink(); ?>" title="Twitter">
                        <svg width="24" height="21" viewBox="0 0 24 21" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M21.543 4.99458C21.5576 5.21156 21.5576 5.42855 21.5576 5.64754C21.5576 12.3201 16.6045 20.0156 7.54759 20.0156V20.0116C4.87215 20.0156 2.25229 19.2297 0 17.7478C0.389031 17.7958 0.780012 17.8198 1.17197 17.8208C3.38915 17.8228 5.54296 17.0598 7.28726 15.6549C5.18026 15.6139 3.3326 14.205 2.68714 12.1481C3.42523 12.2941 4.18574 12.2641 4.91018 12.0611C2.61304 11.5852 0.96039 9.5153 0.96039 7.11144C0.96039 7.08945 0.96039 7.06845 0.96039 7.04745C1.64485 7.43842 2.41121 7.65541 3.19512 7.67941C1.03157 6.1965 0.364656 3.24469 1.67118 0.93683C4.17111 4.09163 7.8596 6.00951 11.8191 6.2125C11.4223 4.45861 11.9644 2.62073 13.2436 1.3878C15.2268 -0.524079 18.3459 -0.426085 20.2101 1.60679C21.3129 1.3838 22.3698 0.968828 23.337 0.380865C22.9694 1.54979 22.2001 2.54273 21.1725 3.17369C22.1484 3.0557 23.102 2.78771 24 2.37874C23.3389 3.39468 22.5063 4.27962 21.543 4.99458Z" fill="#1D9BF0" />
                        </svg>
                    </a>
                </li>
                <li>
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo get_the_permalink(); ?>" title="Facebook">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 0.09375C18.6274 0.09375 24 5.46633 24 12.0938C24 18.0833 19.6118 23.0477 13.875 23.948V15.5625H16.6711L17.2031 12.0938H13.875V9.84274C13.875 8.89376 14.3399 7.96875 15.8306 7.96875H17.3438V5.01563C17.3438 5.01563 15.9705 4.78125 14.6576 4.78125C11.9165 4.78125 10.125 6.4425 10.125 9.45V12.0938H7.07812V15.5625H10.125V23.948C4.38823 23.0477 0 18.0833 0 12.0938C0 5.46633 5.37258 0.09375 12 0.09375Z" fill="#1877F2" />
                        </svg>
                    </a>
                </li>
            </ul>
        </div>
        <div class="wishlist">
            <?php echo do_shortcode('[ti_wishlists_addtowishlist]'); ?>
        </div>
    </div>
<?php
}
add_action('woocommerce_single_product_summary', 'product_number', 9);
function product_number()
{
    global $product;
?>
    <div class="product-number">
        <?php
        echo esc_html__('Product Number: ', 'tokaikiza');
        echo $product->get_sku();
        ?>
    </div>
    <?php
    echo '<div class="opendiv-price">'; ?>

<?php
}

add_action('woocommerce_single_product_summary', 'custom_price_percent', 10);
function custom_price_percent()
{
    global $product;
    if ($product->is_on_sale() && $product->get_type() == 'simple') {
        // $regular_price = $product->get_regular_price();
        // $sale_price =  $product->get_sale_price();
        // $pricetk = $regular_price -  $sale_price;
        // $percent = ($pricetk / $regular_price) * 100;
        echo '<span class="percent"> <span class="onlycounter">00</span>% OFF</span>';
    } elseif ($product->get_type() == 'variable') {
        $prices = $product->get_variation_prices(true);
        $childid =  ($product->get_children()[0]);
        $variation = wc_get_product($childid);
        if (!empty($variation)) {
            if ($variation->is_on_sale()) {
                $regular_price =  $prices['regular_price'][$childid];
                $sale_price = $prices['sale_price'][$childid];
                $pricetk = $regular_price -  $sale_price;
                $percent = ($pricetk / $regular_price) * 100;
                echo '<span class="percent"> <span class="onlycounter">' . round($percent, 0) . '</span>% OFF</span>';
            }
        }
    }
    echo '</div>';
}

add_action('woocommerce_before_add_to_cart_quantity', 'opendiv_qty_stock');
function opendiv_qty_stock()
{
    echo '<div class="qty_stock d-flex">';
    echo '<div class="label-qty"><div class="label-item">' . esc_html__('Quantity:', 'tokaikiza') . '<span class="countqty">1</span></div>';
}
add_action('woocommerce_after_add_to_cart_quantity', 'template_sendmail');
function template_sendmail()
{
    echo '</div><!-- close div label-qty -->';
    global $product;
    $product->get_type();

    if ($product->get_manage_stock()){
        $StockQty = $product->get_stock_quantity();
        $text = '';
        if($product->backorders_allowed()) {
            if($StockQty <= 0){
                $text = 'お取り寄せ'; //Back order
            }
            else {
                $text = '在庫あり'; //In stock
            }
        }
        else{
            if($StockQty <= 0){
                $text = '在庫切れ'; //Out stock
            }
            else {
                $text = '在庫あり'; //In stock
            }
        }
        echo  '<div class="stock in-stock"><span class="label-item">' . esc_html__('Availability: ', 'tokaikiza') . '</span>' . esc_html__($text, 'tokaikiza') . '</div>';
    }
    else {
        $stock_status = $product->get_stock_status();
        if ('instock' == $stock_status) {
            echo  '<div class="stock in-stock"><span class="label-item">' . esc_html__('Availability: ', 'tokaikiza') . '</span>' . esc_html__('In stock', 'tokaikiza') . '</div>';
        } else {
            echo  '<div class="stock out-of-stock"><span class="label-item">' . esc_html__('Availability: ', 'tokaikiza') . '</span>' . esc_html__('Out stock', 'tokaikiza') . '</div>';
        }
    }
?>

    </div> <!-- close div qty_stock -->
    <div class="sendmail"> <a href="#" data-toggle="modal" data-target="#form_sendmail" class="clickpopup"><?php echo esc_html__('Send An Email For Details', 'tokaikiza'); ?></a></div>
    <div class="open-addcart-div">
    <?php
}
add_action('woocommerce_single_product_summary', 'modalpoup_sendmail');
function modalpoup_sendmail()
{ ?>
        <!-- Modal -->
        <div class=" modal fade" id="form_sendmail" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M13 1L1 13M1 1L13 13" stroke="#2F2F39" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </button>
                    </div>
                    <div class="modal-body">
                        <?php dynamic_sidebar('popup-product') ?>
                    </div>
                </div>
            </div>
        </div>
    <?php
}

/* buy now button */
add_action('woocommerce_after_add_to_cart_button', 'toki_quickbuy_after_addtocart_button');
function toki_quickbuy_after_addtocart_button()
{
    ?>
        <button type="button" class="button elementor-button elementor-size-lg buy_now_button">
            <?php _e('Buy It Now', 'tokaikiza'); ?>
        </button>
    </div> <!--  close addcart-div -->
    <input type="hidden" name="is_buy_now" class="is_buy_now" value="0" autocomplete="off" />
    <input type="hidden" name="chekout_url" class="chekout_url" value="<?php echo wc_get_checkout_url(); ?>" autocomplete="off" />
<?php
}
add_filter('woocommerce_add_to_cart_redirect', 'redirect_to_checkout');
function redirect_to_checkout($redirect_url)
{
    if (isset($_REQUEST['is_buy_now']) && $_REQUEST['is_buy_now']) {
        $redirect_url = wc_get_checkout_url(); //or wc_get_cart_url()
    }
    return $redirect_url;
}

add_filter('woocommerce_order_button_text', 'toki_custom_button_text');
function toki_custom_button_text($button_text)
{
    $button_text = esc_html__('Complete Order', 'tokaikiza');
    return $button_text;
}

//add_action('init', 'taxomony_register');
function taxomony_register()
{
    $labels = array(
        'name' => _x('Brands OEM', 'taxonomy general name'),
        'singular_name' => _x('Brands OEM', 'taxonomy singular name'),
        'search_items' =>  __('Search Brands OEM '),
        'popular_items' => __('Popular Brands OEM'),
        'all_items' => __('All Brands OEM'),
        'parent_item' => null,
        'parent_item_colon' => null,
        'edit_item' => __('Edit Brands OEM'),
        'update_item' => __('Update Brands OEM'),
        'add_new_item' => __('Add New Brands OEM '),
        'new_item_name' => __('New Brands OEM Name'),
        'separate_items_with_commas' => __('Separate Members with commas'),
        'add_or_remove_items' => __('Add or remove Members'),
        'choose_from_most_used' => __('Choose from the most used Members'),
        'menu_name' => __('Brands OEM'),
    );
    register_taxonomy('brands_oem', 'product', array(
        'hierarchical' => true,
        'labels' => $labels,
        'show_ui' => true,
        'show_admin_column' => true,
        'update_count_callback' => '_update_post_term_count',
        'query_var' => true,
        //	'rewrite' => array( 'slug' => 'Members' ),
    ));
}
add_filter('woocommerce_subcategory_count_html', '__return_null');
/**
 * Override Movie Archive Query
 * https://codex.wordpress.org/Plugin_API/Action_Reference/pre_get_posts
 */
function movie_archive($query)
{
    if (!is_admin() && $query->is_main_query()) {

        // if (is_post_type_archive() && is_search() && isset($_GET['exclude_type']) && isset($_GET['s']) && isset($_GET['post_type']) || is_post_type_archive() && isset($_GET['exclude_type'])) {
        if (isset($_GET['exclude_type']) && is_search()) {
            $taxquery = array(
                array(
                    'taxonomy' => 'product_cat',
                    'terms' => array($_GET['exclude_type']),
                    'field' => 'id',
                    'include_children' => true,
                    'operator' => 'NOT IN'
                ),
            );
            $query->set('tax_query', $taxquery);
        }
    }
}
//add_action('pre_get_posts', 'movie_archive');
/**
 * Change number of upsells output
 */
add_filter('woocommerce_output_related_products_args', 'wc_change_number_related_products', 20);

function wc_change_number_related_products($args)
{

    $args['posts_per_page'] = 20;
    $args['columns'] = 7; //change number of upsells here
    return $args;
}
add_action('woocommerce_register_form_start', 'toki_add_name_woo_account_registration');

function toki_add_name_woo_account_registration()
{
?>

    <p class="form-row form-row-wide">
        <label for="reg_billing_first_name"><?php _e('First name', 'woocommerce'); ?> <span class="required">*</span></label>
        <input type="text" class="input-text" name="billing_first_name" id="reg_billing_first_name" value="<?php if (!empty($_POST['billing_first_name'])) esc_attr_e($_POST['billing_first_name']); ?>" placeholder="<?php echo esc_attr__('Enter your first name', 'woocommerce') ?>" />
    </p>

    <p class="form-row form-row-wide">
        <label for="reg_billing_last_name"><?php _e('Last name', 'woocommerce'); ?> <span class="required">*</span></label>
        <input type="text" class="input-text" name="billing_last_name" id="reg_billing_last_name" value="<?php if (!empty($_POST['billing_last_name'])) esc_attr_e($_POST['billing_last_name']); ?>" placeholder="<?php echo esc_attr__('Enter your last name', 'woocommerce') ?>" />
    </p>

    <div class="clear"></div>

<?php

}

///////////////////////////////
// 2. VALIDATE FIELDS

add_filter('woocommerce_registration_errors', 'toki_validate_name_fields', 10, 3);

function toki_validate_name_fields($errors, $username, $email)
{
    if (isset($_POST['billing_first_name']) && empty($_POST['billing_first_name'])) {
        $errors->add('billing_first_name_error', __('First name is required!', 'woocommerce'));
    }
    if (isset($_POST['billing_last_name']) && empty($_POST['billing_last_name'])) {
        $errors->add('billing_last_name_error', __('Last name is required!.', 'woocommerce'));
    }
    return $errors;
}

///////////////////////////////
// 3. SAVE FIELDS

add_action('woocommerce_created_customer', 'toki_save_name_fields');

function toki_save_name_fields($customer_id)
{
    if (isset($_POST['billing_first_name'])) {
        update_user_meta($customer_id, 'billing_first_name', sanitize_text_field($_POST['billing_first_name']));
        update_user_meta($customer_id, 'first_name', sanitize_text_field($_POST['billing_first_name']));
    }
    if (isset($_POST['billing_last_name'])) {
        update_user_meta($customer_id, 'billing_last_name', sanitize_text_field($_POST['billing_last_name']));
        update_user_meta($customer_id, 'last_name', sanitize_text_field($_POST['billing_last_name']));
    }
}

add_filter('woocommerce_add_to_cart_fragments', 'iconic_cart_count_fragments', 10, 1);

function iconic_cart_count_fragments($fragments)
{
    $fragments['#cartcount'] = '<input type="hidden" value="' . WC()->cart->get_cart_contents_count() . '" id="cartcount"/>';

    return $fragments;
}
add_filter('woocommerce_redirect_single_search_result', '__return_false');

// function change_product_price($price)
// {
//     $price .= '(税抜)';
//     return $price;
// }
// add_filter('woocommerce_get_price_html', 'change_product_price');
// add_filter('woocommerce_cart_item_price', 'change_product_price');
