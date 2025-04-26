<?php

/**
 * The Template for displaying product archives, including the main shop page which is a post type archive
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/archive-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.4.0
 */

defined('ABSPATH') || exit;

get_header('shop'); ?>
<?php if (is_shop()) : ?>
	<div class="oem-title"></div>
<?php else : ?>
	<header class="oem-entry-header container">
		<div class="oem-title ">
			<?php

			if (isset($_GET['pa_years']) || isset($_GET['pa_model']) || isset($_GET['brands_oem'])) {
			?><h1 class="entry-title"><?php echo $_GET['pa_years'] . " " . str_replace('-', ' ', $_GET['brands_oem']) . " " . str_replace('-', ' ', $_GET['pa_model']); ?></h1>
				<p class="woocommerce-result-count"><?php echo __('(<span class="counterproduct"></span> Products)') ?></p>
				<input type="hidden" value="<?php echo $_GET['pa_years'] . " " . str_replace('-', ' ', $_GET['brands_oem']) . " " . str_replace('-', ' ', $_GET['pa_model']); ?>" id="text-brecumber" />
				<input type="hidden" value="<?php echo get_term_link(get_queried_object()); ?>" id="link-brecumber-before">
			<?php
			}
			?>
		</div>
	</header>
<?php endif; ?>
<div class="main-category">
	<?php
	$term = get_queried_object();
	$category_type = get_field('category_type', $term);
	if ($category_type == 'oem_parts') :
		if (isset($_GET['pa_years']) || isset($_GET['pa_model']) || isset($_GET['brands_oem'])) :
			echo '<div class="layout-list-cat">';
			/**
			 * Hook: woocommerce_before_main_content.
			 *
			 * @hooked woocommerce_output_content_wrapper - 10 (outputs opening divs for the content)
			 * @hooked woocommerce_breadcrumb - 20
			 * @hooked WC_Structured_Data::generate_website_data() - 30
			 */
			do_action('woocommerce_before_main_content');
			if (woocommerce_product_loop()) {

				/**
				 * Hook: woocommerce_before_shop_loop.
				 *
				 * @hooked woocommerce_output_all_notices - 10
				 * @hooked woocommerce_result_count - 20
				 * @hooked woocommerce_catalog_ordering - 30
				 */
				//	do_action('woocommerce_before_shop_loop');

				woocommerce_product_loop_start();

				if (wc_get_loop_prop('total')) {
					while (have_posts()) {
						the_post();

						/**
						 * Hook: woocommerce_shop_loop.
						 */
						do_action('woocommerce_shop_loop');

						wc_get_template_part('content', 'product');
					}
				}

				woocommerce_product_loop_end();

				/**
				 * Hook: woocommerce_after_shop_loop.
				 *
				 * @hooked woocommerce_pagination - 10
				 */
				//	do_action('woocommerce_after_shop_loop');
			} else {
				/**
				 * Hook: woocommerce_no_products_found.
				 *
				 * @hooked wc_no_products_found - 10
				 */
				do_action('woocommerce_no_products_found');
			}

			do_action('woocommerce_after_main_content');
			echo '</div>';
		else :
			$childcat = get_terms(array(
				'taxonomy' => 'product_cat',
				'hide_empty' => false,
				'parent'		=> $term->term_id,
			));
			foreach ($childcat as $cat) :
				$acfbrand = get_field('brands_oem', $cat);
				if ($acfbrand) :
					foreach ($acfbrand as $item) {
						$convert[$item][$cat->term_id] = $cat->name;
					}
				endif;
			endforeach;
	?>
			<div class="small-container d-flex flex-wrap oem-page">
				<?php

				$brands_oem = get_terms(array(
					'taxonomy' => 'pa_brands-oem',
					'hide_empty' => false,
					'meta_key'          => 'menu_order',
					'orderby'           => 'meta_value',
					'order'             => 'ASC'
				));
				foreach ($brands_oem as $brands) :
				?>
					<div class="col col3 item-brand">
						<div class="inner-col">
							<div class="title-brand click-toggle">
								<?php
								$attid = get_field('logo_brand', $brands);
								echo wp_get_attachment_image($attid, 'full') ?>
								<h5 class="title-brand">
									<?php echo $brands->name; ?>
									<?php if (is_array($convert) && array_key_exists($brands->term_id, $convert)) : ?>
										<span class="mobile">
											<svg width="14" height="8" viewBox="0 0 14 8" fill="none" xmlns="http://www.w3.org/2000/svg">
												<path d="M1 1L7 7L13 1" stroke="#7E7E8F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
											</svg>
										</span>
									<?php endif; ?>
								</h5>
							</div>
							<?php
							if (is_array($convert) &&  array_key_exists($brands->term_id, $convert)) : ?>
								<div class="list-cate-child">
									<ul class="listcat">
										<?php
										$childcatbybrand = $convert[$brands->term_id];
										$brandsslug = $brands->slug;
										foreach ($childcatbybrand as $key => $item) {
											echo '<li><a href="#" data-brands=' . $brandsslug . ' data-toggle="modal" data-target="#form_filter" class="clickpopup changebrand">' . $item . '</a></li>';
										}
										?>
									</ul>
								</div>
							<?php endif; ?>
						</div>
					</div>
				<?php endforeach; ?>
				<!-- Modal -->
				<div class="modal fade" id="form_filter" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
					<div class="modal-dialog" role="document">
						<div class="modal-content">
							<div class="modal-header">
								<h5 class="modal-title"><?php echo esc_html__('OEM Parts Finder', 'tokaikiza'); ?></h5>
								<button type="button" class="close" data-dismiss="modal" aria-label="Close">
									<svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
										<path d="M13 1L1 13M1 1L13 13" stroke="#2F2F39" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
									</svg>
								</button>
							</div>
							<div class="modal-body">
								<h6 class="title-form" class="append-brandcat"></h6>
								<form action="" method="get" id="parts-finder" class="parts-finder-archive">
									<input type="hidden" name="brands_oem" value="<?php echo $brandsslug; ?>" id="changebrands_oem" />
									<?php
									$attr = 'pa_year-model';
									$years = get_terms($attr, array(
										'hide_empty' => true,
									));
									if (taxonomy_exists($attr)) :
									?>
										<div class="form-control control-<?= $attr; ?>">
											<label for="<?= $attr; ?>"><?= esc_html__('Year', 'tokaikiza') ?></label>
											<select name="<?= $attr; ?>" id="<?= $attr; ?>">
												<option value=""><?= esc_html__('Select Year', 'tokaikiza') ?></option>
												<?php foreach ($years as $year) : ?>
													<option value="<?= $year->slug; ?>"><?= $year->name; ?></option>
												<?php endforeach; ?>
											</select>
										</div>
									<?php endif; ?>
									<?php
									$attr = 'pa_model';
									$pa_brand = get_terms($attr, array(
										'hide_empty' => true,
									));
									if (taxonomy_exists($attr)) :
									?>
										<div class="form-control control-<?= $attr; ?>">
											<label for="<?= $attr; ?>"><?= esc_html__('Model', 'tokaikiza') ?></label>
											<select name="<?= $attr; ?>" id="<?= $attr; ?>">
												<option value=""><?= esc_html__('Select Model', 'tokaikiza') ?></option>
												<?php foreach ($pa_brand as $item) : ?>
													<option value="<?= $item->slug; ?>"><?= $item->name; ?></option>
												<?php endforeach; ?>
											</select>
										</div>
									<?php endif; ?>
									<div class="form-control control-action d-flex">
										<button type="reset" class="btn-outline"><?= esc_html__('Reset', 'tokaikiza') ?></button>
										<button type="submit"><?= esc_html__('Search', 'tokaikiza') ?></button>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		<?php endif; ?>

	<?php else : ?>
		<div class="container main-cat <?php echo (isset($_GET['layout']) && $_GET['layout'] == 'full')  ? 'd-block' :  'd-flex'; ?> ">
			<?php if (isset($_GET['layout']) && $_GET['layout'] == 'full') : else : ?>
				<div class="col-sidebar">
					<div class="inner-sidebar">
						<?php
						/**
						 * Hook: woocommerce_sidebar.
						 *
						 * @hooked woocommerce_get_sidebar - 10
						 */
						do_action('woocommerce_sidebar'); ?>
						<div class="moible bottom-fixed d-flex list-button">
							<a href="<?php if (is_shop()) echo get_the_permalink(get_option('woocommerce_shop_page_id'));
										else echo get_term_link($term); ?>" class="btn btn-outline clear-all"><?php echo  esc_html__('Clear All', 'tokaikiza'); ?></a>
							<button class="btn btn-primary close-filter button-trigger" data-trigger=".opensidebar"><?php echo  esc_html__('Close Filters', 'tokaikiza'); ?></button>
						</div>
					</div>
				</div>
			<?php endif; ?>
			<div class="<?php if (isset($_GET['layout']) && $_GET['layout'] == 'full')  echo 'col-content-full';
						else  echo 'col-content'; ?>">
				<header class="woocommerce-products-header">
					<?php if (apply_filters('woocommerce_show_page_title', true)) : ?>
						<div class="title-count">
							<h1 class="woocommerce-products-header__title page-title"><?php woocommerce_page_title(); ?></h1>
							<?php do_action('woocommerce_result_count_toki'); ?>
						</div>
					<?php endif; ?>

					<?php
					/**
					 * Hook: woocommerce_archive_description.
					 *
					 * @hooked woocommerce_taxonomy_archive_description - 10
					 * @hooked woocommerce_product_archive_description - 10
					 */
					do_action('woocommerce_archive_description');
					?>
				</header>

				<?php

				/**
				 * Hook: woocommerce_before_shop_loop.
				 *
				 * @hooked woocommerce_output_all_notices - 10
				 * @hooked woocommerce_result_count - 20
				 * @hooked woocommerce_catalog_ordering - 30
				 */
				do_action('woocommerce_before_shop_loop');
				/**
				 * Hook: woocommerce_before_main_content.
				 *
				 * @hooked woocommerce_output_content_wrapper - 10 (outputs opening divs for the content)
				 * @hooked woocommerce_breadcrumb - 20
				 * @hooked WC_Structured_Data::generate_website_data() - 30
				 */
				do_action('woocommerce_before_main_content');
				echo '<span class="counterchange">';
				do_action('woocommerce_result_count_toki');
				echo '</span>';
				?>
				<?php
				if (woocommerce_product_loop()) {

					woocommerce_product_loop_start();

					if (wc_get_loop_prop('total')) {
						while (have_posts()) {
							the_post();

							/**
							 * Hook: woocommerce_shop_loop.
							 */
							do_action('woocommerce_shop_loop');

							wc_get_template_part('content', 'product');
						}
					}

					woocommerce_product_loop_end();

					/**
					 * Hook: woocommerce_after_shop_loop.
					 *
					 * @hooked woocommerce_pagination - 10
					 */
					do_action('woocommerce_after_shop_loop');
				} else {
					/**
					 * Hook: woocommerce_no_products_found.
					 *
					 * @hooked wc_no_products_found - 10
					 */
					do_action('woocommerce_no_products_found');
				}

				do_action('woocommerce_after_main_content');
				?>
			</div>
		</div>

		<div class="scroll-product">
			<?php
			dynamic_sidebar('sidebar-product');
			/**
			 * Hook: woocommerce_after_main_content.
			 *
			 * @hooked woocommerce_output_content_wrapper_end - 10 (outputs closing divs for the content)
			 */
			?>
		</div>
	<?php endif; ?>
</div>
<?php
if (is_product_category()) :
	$curent = get_queried_object();
	$args = [
		'post_type' => 'product',
		'posts_per_page' => -1,
		'tax_query' => [
			[
				'taxonomy' => $curent->taxonomy,
				'terms' => $curent->term_id,
				'include_children' => false // Remove if you need posts from term 7 child terms
			],
			[
				'taxonomy' => 'product_visibility',
				'field'    => 'name',
				'terms'    => 'exclude-from-catalog',
				'operator' => 'IN',
			]
		],
	];
	$the_query = new WP_Query($args);
	if ($the_query->have_posts()) {
		while ($the_query->have_posts()) {
			$the_query->the_post();
			$checkbrand = get_the_terms(get_the_id(), 'pa_brand', 'string');
			if (!empty($checkbrand)) :
				foreach ($checkbrand as $item) {
					$exbrands[$item->term_id] = $item->slug;
				}
			endif;
		}
	}
	if (!empty($exbrands)) :
		foreach ($exbrands as $key => $value) :
?>
			<style>
				.pa_brand-item-<?php echo $key; ?> {
					display: none;
				}
			</style>
<?php
		endforeach;
		wp_reset_postdata();
	endif;
endif;
get_footer('shop');
