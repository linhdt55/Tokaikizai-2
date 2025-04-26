<?php

/**
 * Pagination - Show numbered pagination for catalog pages
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/loop/pagination.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.3.1
 */

if (!defined('ABSPATH')) {
	exit;
}

$total   = isset($total) ? $total : wc_get_loop_prop('total_pages');
$current = isset($current) ? $current : wc_get_loop_prop('current_page');
$base    = isset($base) ? $base : esc_url_raw(str_replace(999999999, '%#%', remove_query_arg('add-to-cart', get_pagenum_link(999999999, false))));
$format  = isset($format) ? $format : '';

if ($total <= 1) {
	return;
}
?>
<nav class="woocommerce-pagination">
	<?php
	echo paginate_links(
		apply_filters(
			'woocommerce_pagination_args',
			array( // WPCS: XSS ok.
				'base'      => $base,
				'format'    => $format,
				'add_args'  => false,
				'current'   => max(1, $current),
				'total'     => $total,
				'prev_text' => is_rtl() ? '<svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12.8337 7.00002H1.16699M1.16699 7.00002L7.00033 12.8334M1.16699 7.00002L7.00033 1.16669" stroke="#2F2F39" stroke-width="1.67" stroke-linecap="round" stroke-linejoin="round"/></svg>Previous' : '<svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12.8337 7.00002H1.16699M1.16699 7.00002L7.00033 12.8334M1.16699 7.00002L7.00033 1.16669" stroke="#2F2F39" stroke-width="1.67" stroke-linecap="round" stroke-linejoin="round"/></svg>Previous',
				'next_text' => is_rtl() ? 'Next<svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M1.16699 7.00002H12.8337M12.8337 7.00002L7.00033 1.16669M12.8337 7.00002L7.00033 12.8334" stroke="#2F2F39" stroke-width="1.67" stroke-linecap="round" stroke-linejoin="round"/></svg>' : 'Next<svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M1.16699 7.00002H12.8337M12.8337 7.00002L7.00033 1.16669M12.8337 7.00002L7.00033 12.8334" stroke="#2F2F39" stroke-width="1.67" stroke-linecap="round" stroke-linejoin="round"/></svg>',
				'type'      => 'list',
				'end_size'  => 1,
				'mid_size'  => 1,
			)
		)
	);
	?>
</nav>