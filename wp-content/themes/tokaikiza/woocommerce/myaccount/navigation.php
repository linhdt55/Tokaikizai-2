<?php

/**
 * My Account navigation
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/navigation.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 2.6.0
 */

if (!defined('ABSPATH')) {
	exit;
}

do_action('woocommerce_before_account_navigation');
$ignore = array("downloads", "payment-methods", "customer-logout", "dashboard");
?>

<nav class="woocommerce-MyAccount-navigation account order">
	<h3 class="account-title"><?php get_the_title(); ?></h3>
	<ul>
		<li class="url-myaccount"><a href="<?php echo get_permalink(get_option('woocommerce_myaccount_page_id')); ?>"><?php echo esc_html__('Account details', 'tokaikiza'); ?></a></li>
		<?php foreach (wc_get_account_menu_items() as $endpoint => $label) : ?>
			<?php if (!in_array($endpoint, $ignore)) : ?>
				<li class="<?php echo wc_get_account_menu_item_classes($endpoint); ?>">
					<a href="<?php echo esc_url(wc_get_account_endpoint_url($endpoint)); ?>"><?php echo esc_html__($label, 'tokaikiza'); ?></a>
				</li>
			<?php endif; ?>
		<?php endforeach; ?>
		<li class="logout-url-ct"><a href="<?php echo wp_logout_url(home_url('/')); ?>"><?php echo esc_html__('Log Out', 'tokaikiza'); ?></a></li>
	</ul>
</nav>

<?php do_action('woocommerce_after_account_navigation'); ?>