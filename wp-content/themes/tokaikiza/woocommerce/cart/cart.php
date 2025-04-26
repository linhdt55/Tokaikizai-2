<?php

/**
 * Cart Page
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/cart/cart.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.8.0
 */

defined('ABSPATH') || exit;

do_action('woocommerce_before_cart'); ?>
<span class="countitemcart">
	(<?php
		echo sprintf(
			_n('%d product', '%d products', WC()->cart->get_cart_contents_count(), 'woocommerce'),
			WC()->cart->get_cart_contents_count()
		);
		// echo WC()->cart->get_cart_contents_count();
		// echo ' ' . esc_html__('products', 'woocommerce');
		?>)
</span>
<div class="d-flex cartcontent">
	<div class="col-content">
		<form class="woocommerce-cart-form" action="<?php echo esc_url(wc_get_cart_url()); ?>" method="post">
			<?php do_action('woocommerce_before_cart_table'); ?>

			<table class="shop_table cart woocommerce-cart-form__contents" cellspacing="0">
				<thead>
					<tr>
						<th class="product-name thumbnail"><?php esc_html_e('Product', 'woocommerce'); ?></th>
						<th class="product-price"><?php esc_html_e('Price', 'woocommerce'); ?></th>
						<th class="product-quantity"><?php esc_html_e('Quantity', 'woocommerce'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php do_action('woocommerce_before_cart_contents'); ?>

					<?php
					foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
						$_product   = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
						$product_id = apply_filters('woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key);

						if ($_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters('woocommerce_cart_item_visible', true, $cart_item, $cart_item_key)) {
							$product_permalink = apply_filters('woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink($cart_item) : '', $cart_item, $cart_item_key);
					?>
							<tr class="woocommerce-cart-form__cart-item <?php echo esc_attr(apply_filters('woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key)); ?>">



								<td class="product-name" data-title="<?php esc_attr_e('Product', 'woocommerce'); ?>">
									<div class="product-thumbnail">
										<?php
										$thumbnail = apply_filters('woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key);

										if (!$product_permalink) {
											echo $thumbnail; // PHPCS: XSS ok.
										} else {
											printf('<a href="%s">%s</a>', esc_url($product_permalink), $thumbnail); // PHPCS: XSS ok.
										}
										?>

									</div>
									<div class="product-cart-content">
										<div class="top">
											<?php
											if (!$product_permalink) {
												echo wp_kses_post(apply_filters('woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key) . '&nbsp;');
											} else {
												echo wp_kses_post(apply_filters('woocommerce_cart_item_name', sprintf('<a href="%s">%s</a>', esc_url($product_permalink), $_product->get_name()), $cart_item, $cart_item_key));
											}

											do_action('woocommerce_after_cart_item_name', $cart_item, $cart_item_key);

											// Meta data.
											echo wc_get_formatted_cart_item_data($cart_item); // PHPCS: XSS ok.

											// Backorder notification.
											if ($_product->backorders_require_notification() && $_product->is_on_backorder($cart_item['quantity'])) {
												echo wp_kses_post(apply_filters('woocommerce_cart_item_backorder_notification', '<p class="backorder_notification">' . esc_html__('Available on backorder', 'woocommerce') . '</p>', $product_id));
											}
											?>
											<div class="product-price">
												<div class="price_cart mobile">
													<?php
													echo $_product->get_price_html();
													?>
												</div>
											</div>
										</div>
										<div class="button-remove bottom desktop">
											<?php
											echo apply_filters( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
												'woocommerce_cart_item_remove_link',
												sprintf(
													'<a href="%s" class="remove-item" aria-label="%s" data-product_id="%s" data-product_sku="%s">%s</a>',
													esc_url(wc_get_cart_remove_url($cart_item_key)),
													esc_html__('Remove this item', 'woocommerce'),
													esc_attr($product_id),
													esc_attr($_product->get_sku()),
													esc_html__('Remove', 'woocommerce')
												),
												$cart_item_key
											);
											?>
										</div>
									</div>
								</td>

								<td class="product-price" data-title="<?php esc_attr_e('Price', 'woocommerce'); ?>">
									<div class="price_cart"> <?php
																echo $_product->get_price_html();
																//echo apply_filters('woocommerce_cart_item_price', WC()->cart->get_product_price($_product), $cart_item, $cart_item_key); // PHPCS: XSS ok.
																?>
									</div>
								</td>

								<td class="product-quantity" data-title="<?php esc_attr_e('Quantity', 'woocommerce'); ?>">
									<?php
									if ($_product->is_sold_individually()) {
										$product_quantity = sprintf('1 <input type="hidden" name="cart[%s][qty]" value="1" />', $cart_item_key);
									} else {
										$product_quantity = woocommerce_quantity_input(
											array(
												'input_name'   => "cart[{$cart_item_key}][qty]",
												'input_value'  => $cart_item['quantity'],
												'max_value'    => $_product->get_max_purchase_quantity(),
												'min_value'    => '0',
												'product_name' => $_product->get_name(),
											),
											$_product,
											false
										);
									}

									echo apply_filters('woocommerce_cart_item_quantity', $product_quantity, $cart_item_key, $cart_item); // PHPCS: XSS ok.
									?>
									<div class="button-remove mobile">
										<?php
										echo apply_filters( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
											'woocommerce_cart_item_remove_link',
											sprintf(
												'<a href="%s" class="remove-item" aria-label="%s" data-product_id="%s" data-product_sku="%s">%s</a>',
												esc_url(wc_get_cart_remove_url($cart_item_key)),
												esc_html__('Remove this item', 'woocommerce'),
												esc_attr($product_id),
												esc_attr($_product->get_sku()),
												esc_html__('Remove', 'woocommerce')
											),
											$cart_item_key
										);
										?>
									</div>
								</td>

							</tr>
					<?php
						}
					}
					?>

					<?php do_action('woocommerce_cart_contents'); ?>

					<tr class="tr_coupon_code">
						<td colspan="6" class="actions">

							<?php if (wc_coupons_enabled()) { ?>
								<h5 class="item-label label-coupon mobile"><?php esc_html_e('Promotion Code', 'woocommerce'); ?></h5>
								<div class="coupon">
									<label for="coupon_code"><?php esc_html_e('Coupon:', 'woocommerce'); ?></label> <input type="text" name="coupon_code" class="input-text" id="coupon_code" value="" placeholder="<?php esc_attr_e('Enter your promotion code', 'tokaikiza'); ?>" /> <button type="submit" class="button" name="apply_coupon" disabled value="<?php esc_attr_e('Apply', 'woocommerce'); ?>"><?php esc_attr_e('Apply', 'woocommerce'); ?></button>
									<?php do_action('woocommerce_cart_coupon'); ?>
								</div>
							<?php } ?>

							<button type="submit" class="button update_cart" name="update_cart" value="<?php esc_attr_e('Update cart', 'woocommerce'); ?>"><?php esc_html_e('Update cart', 'woocommerce'); ?></button>

							<?php do_action('woocommerce_cart_actions'); ?>

							<?php wp_nonce_field('woocommerce-cart', 'woocommerce-cart-nonce'); ?>
						</td>
					</tr>

					<?php do_action('woocommerce_after_cart_contents'); ?>
				</tbody>
			</table>
			<?php do_action('woocommerce_after_cart_table'); ?>
		</form>

	</div>
	<div class="col-sidebar">
		<?php do_action('woocommerce_before_cart_collaterals'); ?>

		<div class="cart-collaterals">
			<?php
			/**
			 * Cart collaterals hook.
			 *
			 * @hooked woocommerce_cross_sell_display
			 * @hooked woocommerce_cart_totals - 10
			 */
			do_action('woocommerce_cart_collaterals');
			?>
		</div>

		<?php do_action('woocommerce_after_cart'); ?>
	</div>
</div>