<?php

/**
 * The template for displaying product category thumbnails within loops
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/content-product-cat.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 4.7.0
 */

if (!defined('ABSPATH')) {
	exit;
}
$term = get_queried_object();
$category_type = get_field('category_type', $term);
if ($category_type == 'oem_parts') :
?>
	<li <?php //wc_product_cat_class('', $category); 
		?>>
		<?php
		$args = array(
			'post_type' => 'product',
			'posts_per_page' => -1,
			'tax_query'         => array(
				array(
					'taxonomy'  => 'product_cat',
					'field'     => 'slug',
					'terms'     => $category->slug
				)
			)
		);
		if (isset($_GET['pa_year-model']) && $_GET['pa_year-model'] != '') {
			$args['tax_query'][1]         = 	array(
				'taxonomy'  => 'pa_year-model',
				'field'     => 'slug',
				'terms'     => $_GET['pa_year-model'],
			);
		}
		if (isset($_GET['pa_model']) && $_GET['pa_model'] != '') {
			$args['tax_query'][2]  =  array(
				'taxonomy'  => 'pa_model',
				'field'     => 'slug',
				'terms'     => $_GET['pa_model'],
			);
		}
		if (isset($_GET['brands_oem']) && $_GET['brands_oem'] != '') {
			$args['tax_query'][3]  =  array(
				'taxonomy'  => 'pa_brands-oem',
				'field'     => 'slug',
				'terms'     => $_GET['brands_oem'],
			);
		}
		$the_query = new WP_Query($args);

		// The Loop
		if ($the_query->have_posts()) {

			/**
			 * The woocommerce_before_subcategory hook.
			 *
			 * @hooked woocommerce_template_loop_category_link_open - 10
			 */
			//	do_action('woocommerce_before_subcategory', $category);

			/**
			 * The woocommerce_before_subcategory_title hook.
			 *
			 * @hooked woocommerce_subcategory_thumbnail - 10
			 */
			//do_action('woocommerce_before_subcategory_title', $category);

			/**
			 * The woocommerce_shop_loop_subcategory_title hook.
			 *
			 * @hooked woocommerce_template_loop_category_title - 10
			 */
			do_action('woocommerce_shop_loop_subcategory_title', $category);

			/**
			 * The woocommerce_after_subcategory_title hook.
			 */
			do_action('woocommerce_after_subcategory_title', $category);

			echo '<div class="queryproduct 12">';
			echo '<ul class="products columns-7">';
			while ($the_query->have_posts()) :
				$the_query->the_post(); ?>
	<li <?php echo wc_product_class(); ?>>
		<?php
				/**
				 * Hook: woocommerce_before_shop_loop_item.
				 *
				 * @hooked woocommerce_template_loop_product_link_open - 10
				 */
				do_action('woocommerce_before_shop_loop_item');
				/**
				 * Hook: woocommerce_before_shop_loop_item_title.
				 *
				 * @hooked woocommerce_show_product_loop_sale_flash - 10
				 * @hooked woocommerce_template_loop_product_thumbnail - 10
				 * woocommerce_template_loop_product_link_close - 15
				 */
				do_action('woocommerce_before_shop_loop_item_title');

		?>
		<a href="<?php the_permalink(); ?>">
			<?php
				/**
				 * Hook: woocommerce_shop_loop_item_title.
				 *
				 * @hooked woocommerce_template_loop_product_title - 10
				 */
				do_action('woocommerce_shop_loop_item_title');
			?>
		</a>
	</li>
<?php
			endwhile;
			echo '</ul>';
			echo '</div>';
		}
		/* Restore original Post Data */
		wp_reset_postdata();
?>

<?php

	/**
	 * The woocommerce_after_subcategory hook.
	 *
	 * @hooked woocommerce_template_loop_category_link_close - 10
	 */
	//	do_action('woocommerce_after_subcategory', $category);
?>
</li>
<?php endif; ?>