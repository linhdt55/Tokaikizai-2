<?php

/**
 * tokaikiza functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package tokaikiza
 */

if (!defined('_S_VERSION')) {
	// Replace the version number of the theme on each release.
	//define('_S_VERSION', '1.0.2');
	define('_S_VERSION', round(time(), 3));
}

/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which
 * runs before the init hook. The init hook is too late for some features, such
 * as indicating support for post thumbnails.
 */
function tokaikiza_setup()
{
	/*
		* Make theme available for translation.
		* Translations can be filed in the /languages/ directory.
		* If you're building a theme based on tokaikiza, use a find and replace
		* to change 'tokaikiza' to the name of your theme in all the template files.
		*/
	load_theme_textdomain('tokaikiza', get_template_directory() . '/languages');

	// Add default posts and comments RSS feed links to head.
	add_theme_support('automatic-feed-links');

	/*
		* Let WordPress manage the document title.
		* By adding theme support, we declare that this theme does not use a
		* hard-coded <title> tag in the document head, and expect WordPress to
		* provide it for us.
		*/
	add_theme_support('title-tag');

	/*
		* Enable support for Post Thumbnails on posts and pages.
		*
		* @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
		*/
	add_theme_support('post-thumbnails');

	// This theme uses wp_nav_menu() in one location.
	register_nav_menus(
		array(
			'menu-1' => esc_html__('Primary', 'tokaikiza'),
		)
	);

	/*
		* Switch default core markup for search form, comment form, and comments
		* to output valid HTML5.
		*/
	add_theme_support(
		'html5',
		array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
			'style',
			'script',
		)
	);

	// Set up the WordPress core custom background feature.
	add_theme_support(
		'custom-background',
		apply_filters(
			'tokaikiza_custom_background_args',
			array(
				'default-color' => 'ffffff',
				'default-image' => '',
			)
		)
	);

	// Add theme support for selective refresh for widgets.
	add_theme_support('customize-selective-refresh-widgets');

	/**
	 * Add support for core custom logo.
	 *
	 * @link https://codex.wordpress.org/Theme_Logo
	 */
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 250,
			'width'       => 250,
			'flex-width'  => true,
			'flex-height' => true,
		)
	);
	/**
	 * Add support for woocommerce	 *
	 */
	add_theme_support('woocommerce');
	add_theme_support('wc-product-gallery-zoom');
	add_theme_support('wc-product-gallery-lightbox');
	add_theme_support('wc-product-gallery-slider');
}
add_action('after_setup_theme', 'tokaikiza_setup');

/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 */
function tokaikiza_content_width()
{
	$GLOBALS['content_width'] = apply_filters('tokaikiza_content_width', 640);
}
add_action('after_setup_theme', 'tokaikiza_content_width', 0);

/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
function tokaikiza_widgets_init()
{
	register_sidebar(
		array(
			'name'          => esc_html__('Sidebar', 'tokaikiza'),
			'id'            => 'sidebar-1',
			'description'   => esc_html__('Add widgets here.', 'tokaikiza'),
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h5 class="widget-title wpf_item_name">',
			'after_title'   => '</h5>',
		)
	);
	register_sidebar(
		array(
			'name'          => esc_html__('Recently viewed products widget', 'tokaikiza'),
			'id'            => 'sidebar-product',
			'description'   => esc_html__('Add widgets here.', 'tokaikiza'),
			'before_widget' => '<section id="%1$s" class="woocommerce widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h4 class="widget-title">',
			'after_title'   => '</h4>',
		)
	);
	register_sidebar(
		array(
			'name'          => esc_html__('Popup send mail', 'tokaikiza'),
			'id'            => 'popup-product',
			'description'   => esc_html__('Add widgets here.', 'tokaikiza'),
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h5 class="widget-title modal-title">',
			'after_title'   => '</h5>',
		)
	);
}
add_action('widgets_init', 'tokaikiza_widgets_init');

/**
 * Enqueue scripts and styles.
 */
function tokaikiza_scripts()
{
	wp_enqueue_style('owlcarousel', get_template_directory_uri() . '/css/owl.carousel.min.css', array(), '2.3.4');
	wp_enqueue_style('tokaikiza-style', get_stylesheet_uri(), array(), _S_VERSION);
	wp_enqueue_style('faq-style', get_template_directory_uri() . '/css/cms.css', array(), _S_VERSION);
	wp_enqueue_style('tokaikiza-home', get_template_directory_uri() . '/css/home.css', array(), _S_VERSION);
	wp_enqueue_style('tokaikiza-wishlist', get_template_directory_uri() . '/css/wishlist.css', array(), _S_VERSION);
	wp_enqueue_style('tokaikiza-company-profile', get_template_directory_uri() . '/css/company-profile.css', array(), _S_VERSION);
	wp_enqueue_style('tokaikiza-account-address', get_template_directory_uri() . '/css/account_address.css', array(), _S_VERSION);
	wp_enqueue_style('tokaikiza-checkout', get_template_directory_uri() . '/css/checkout.css', array(), _S_VERSION);
	wp_enqueue_style('shipping-woo', get_template_directory_uri() . '/css/shipping-woo.css', array(), _S_VERSION);
	wp_style_add_data('tokaikiza-style', 'rtl', 'replace');

	wp_enqueue_script('owl-carousel', get_template_directory_uri() . '/js/owl.carousel.min.js', array(), '2.3.4', true);
	wp_enqueue_script('wishlist', get_template_directory_uri() . '/js/wishlist.js', array(), _S_VERSION, true);
	wp_enqueue_script('tokaikiza-theme', get_template_directory_uri() . '/js/theme.js', array(), _S_VERSION, true);

	if (is_singular() && comments_open() && get_option('thread_comments')) {
		wp_enqueue_script('comment-reply');
	}
}
add_action('wp_enqueue_scripts', 'tokaikiza_scripts', 99);

/**
 * Implement the Custom Header feature.
 */
require get_template_directory() . '/inc/custom-header.php';

/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/template-tags.php';

/**
 * Functions which enhance the theme by hooking into WordPress.
 */
require get_template_directory() . '/inc/template-functions.php';

/**
 * Customizer additions.
 */
require get_template_directory() . '/inc/customizer.php';

/**
 * Customizer widget wordpress.
 */
require get_template_directory() . '/inc/widget.php';

/**
 * Customizer woocommerce
 */
require get_template_directory() . '/inc/custom_woo.php';

/**
 * Customizer woocommerce shipping
 */
require get_template_directory() . '/inc/functions-shipping-woo.php';

/**
 * custom elementor widget
 */
if (is_plugin_active('elementor/elementor.php')) {
	require get_template_directory() . '/inc/elementor-widgets.php';
}

/**
 * Load Jetpack compatibility file.
 */
if (defined('JETPACK__VERSION')) {
	require get_template_directory() . '/inc/jetpack.php';
}
/**
 * disable widget editor
 */
add_filter('gutenberg_use_widgets_block_editor', '__return_false');
add_filter('use_widgets_block_editor', '__return_false');

add_action('get_header', 'custom_calendar_Date', 10, 2);

/**
 * Fires before the header template file is loaded.
 *
 * @param string|null $name Name of the specific header file to use. Null for the default header.
 * @param array       $args Additional arguments passed to the header template.
 */
function custom_calendar_Date()
{
	$months = date('m', strtotime('first day of +1 month'));
	$year =  date('Y', strtotime('first day of +1 month'));
	echo '<input type="hidden" value="' . mktime(0, 0, 0, $months, 15, $year) . '" id="next_month_calendar"/>';
	echo '<input type="hidden" value="' . get_woocommerce_currency_symbol() . '" id="curency_symboy"/>';
	echo '<input type="hidden" value="' . WC()->cart->get_cart_contents_count() . '" id="cartcount"/>';
	echo '<input type="hidden" value="' . esc_attr__('Search', 'tokaikiza') . '" id="textsearch"/>';
	$oem_parts = get_terms(array(
		'taxonomy' => 'product_cat',
		'hide_empty' => false,
		'meta_query' => array(
			array(
				'key'       => 'category_type',
				'value'     => 'oem_parts',
				'compare'   => 'LIKE'
			)
		),
	));
	foreach ($oem_parts as $item) {
		$arrayoem_parts[$item->term_id] = $item->slug;
	};
	if (!empty($arrayoem_parts)) {
		//	echo '<input type="hidden" value="' . implode(",", array_keys($arrayoem_parts)) . '" id="exclude_cat"/>';
	}
}

function get_company_of_user(){
	$address_type = 'billing';
	$getter  = "get_{$address_type}";
	$address = array();

	$customer_id = get_current_user_id();

	$customer = new WC_Customer( $customer_id );

	if ( is_callable( array( $customer, $getter ) ) ) {
		$address = $customer->$getter();
		if($address['company'] !=''){
			return $address['company'];
		}
		else{
			$address_type = 'shipping';
			$getter  = "get_{$address_type}";
			if ( is_callable( array( $customer, $getter ) ) ) {
				$address = $customer->$getter();
				if($address['company'] !=''){
					return $address['company'];
				}
			}
		}
	}
	return;
}

// 「Read more」を非表示
 function new_excerpt_more($more) {
     return '';
     }
     add_filter('excerpt_more', 'new_excerpt_more');

// 商品CSVにスラッグを含める
add_filter( 'woocommerce_product_export_column_names', 'add_slug_export_column' );
add_filter( 'woocommerce_product_export_product_default_columns', 'add_slug_export_column' );

function add_slug_export_column( $columns ) {
$columns['slug'] = 'Slug';

return $columns;
}

add_filter( 'woocommerce_product_export_product_column_slug' , 'add_export_data_slug', 10, 2 );
function add_export_data_slug( $value, $product ) {
$value = $product->get_slug();

return $value;
}

add_filter( 'woocommerce_csv_product_import_mapping_options', 'add_slug_import_option' );
function add_slug_import_option( $options ) {
$options['slug'] = 'Slug';

return $options;
}

add_filter( 'woocommerce_csv_product_import_mapping_default_columns', 'add_default_slug_column_mapping' );
function add_default_slug_column_mapping( $columns ) {
$columns['Slug'] = 'slug';

return $columns;
}

add_filter( 'woocommerce_product_import_pre_insert_product_object', 'process_import_product_slug_column', 10, 2 );
function process_import_product_slug_column( $object, $data ) {
if ( !empty( $data['slug'] ) ) {
$object->set_slug( $data['slug'] );
}

return $object;
}

// 商品一覧ページにSKU（品番）を表示
add_action('woocommerce_after_shop_loop_item_title', 'display_sku_on_product_list', 20);

function display_sku_on_product_list() {
    global $product;
    
    // SKUがある場合に表示
    if ($sku = $product->get_sku()) {
        echo '<p class="product-sku">' . $sku . '</p>';
    }
}

// checkoutページの利用規約の部分
add_filter( 'woocommerce_get_terms_and_conditions_checkbox_text', 'custom_terms_and_conditions_text' );

function custom_terms_and_conditions_text( $text ) {
    $terms_page_url = get_permalink( wc_get_page_id( 'terms' ) ); // 利用規約ページのURLを取得
    $text = '<a href="' . esc_url( $terms_page_url ) . '" target="_blank">利用規約</a>に同意しました。';
    return $text;
}

// デフォルトのクーポン表示を削除
remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10 );

// 請求書先住所の下にクーポンフォームを追加
add_action( 'woocommerce_checkout_after_customer_details', 'woocommerce_checkout_coupon_form', 10 );

// バリエーションのある商品 JavaScriptによる在庫状況の更新
function add_custom_js() {
    ?>
    <script type="text/javascript">
    jQuery(document).ready(function($) {
        // バリエーションが変更されたとき
        $('.variations_form').on('show_variation', function(event, variation) {
            var availability = variation.availability_html;

            // 在庫状況を更新
            $('.single_variation_wrap .stock').html(availability);
        });
    });
    </script>
    <?php
}
add_action('wp_footer', 'add_custom_js');


// 在庫状況の翻訳をカスタマイズする
add_filter( 'woocommerce_get_availability_text', 'custom_stock_status_text', 10, 2 );

function custom_stock_status_text( $availability, $product ) {
    if ( ! $product->is_in_stock() ) {
        if ( $product->is_on_backorder() ) {
            return '在庫状況：お取り寄せ';
        } else {
            return '在庫状況：完売';
        }
    } elseif ( $product->is_on_backorder() ) {
        return '在庫状況：お取り寄せ'; // 在庫があっても、取り寄せの場合は「お取り寄せ」
    } else {
        return '在庫状況：在庫あり';
    }
}


// 並び替えのオプションの文言
function custom_woocommerce_catalog_orderby( $orderby ) {
    $orderby['rating'] = '評価順'; // 「平均評価順」を追加
    $orderby['date'] = '新着順'; // 「新しい順」を追加
    $orderby['price'] = '価格の安い順'; // 「安い順」を日本語に変更
    $orderby['price-desc'] = '価格の高い順'; // 「高い順」を日本語に変更
    return $orderby;
}
add_filter( 'woocommerce_catalog_orderby', 'custom_woocommerce_catalog_orderby' );




// 商品一覧ページで定価と特別価格を表示する（商品一覧ページ価格の強制上書き）
add_action('woocommerce_after_shop_loop_item_title', 'custom_loop_product_price', 5);
function custom_loop_product_price() {
    global $product;

    // バリエーション商品のみ適用
    if ($product->is_type('variable')) {
        $prices = $product->get_variation_prices(true);
        $min_regular_price = current($prices['regular_price']);

        // 定価のみ表示
        echo '<span class="price"><del>' . wc_price($min_regular_price) . '</del></span>';
    }
}




