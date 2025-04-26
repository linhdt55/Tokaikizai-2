<?php

/**
 * My Account Dashboard
 *
 * Shows the first intro screen on the account dashboard.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/dashboard.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 4.4.0
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

$allowed_html = array(
	'a' => array(
		'href' => array(),
	),
);

?>
<?php
$current_user = wp_get_current_user(); ?>
<div class="account-title my-account"><?php echo esc_html__("Account details", 'tokaikiza') ?></div>
<div class="hr"></div>
<input type="hidden" value="<?php echo esc_html__("Account details", 'tokaikiza') ?>" id="text-brecumber" />
<input type="hidden" value="<?php echo get_the_permalink(); ?>" id="link-brecumber-before">
<form class="woocommerce-EditAccountForm edit-account account-details" action="" method="post" <?php do_action('woocommerce_edit_account_form_tag'); ?>>
	<div class="user-wrp">
		<!-- <div class="user  first-name">
			<div class="title"><?php echo esc_html__("first name", 'tokaikiza') ?>:</div>
			<div class="content"><?php echo ($current_user->user_firstname)  . '<br />'; ?></div>
		</div>

		<div class="user last-name">
			<div class="title"><?php echo esc_html__("last name", 'tokaikiza') ?>:</div>
			<div class="content"><?php echo ($current_user->user_lastname)  . '<br />'; ?></div>
		</div> -->

		<div class="user  first-name">
			<div class="title"><?php echo esc_html__("会社名", 'tokaikiza') ?>:</div>
			<div class="content"><?php echo ($current_user->user_firstname)  . '<br />'; ?></div>
		</div>

		<div class="user email">
			<div class="title"><?php echo esc_html__("email", 'tokaikiza') ?>:</div>
			<div class="content"><?php echo ($current_user->user_email)  . '<br />'; ?></div>
		</div>

		<div class="user password">
			<div class="title"><?php echo esc_html__("password", 'tokaikiza') ?>:</div>
			<div class="content"><input type="password" value="<?php echo ($current_user->user_password)  . '<br />'; ?>"></input></div>
		</div>
		<button type="submit" class="woocommerce-Button button" name="save_account_details" value="<?php esc_attr_e('Save change', 'woocommerce'); ?>"><a href="<?php echo woocommerce_customer_edit_account_url() ?>"><?php esc_html_e('Edit', 'tokaikiza'); ?></a></button>
	</div>

</form>

<?php
$current_user = wp_get_current_user();

/*
 * @example Safe usage: $current_user = wp_get_current_user();
 * if ( ! ( $current_user instanceof WP_User ) ) {
 *     return;
 * }
 */




/**
 * My Account dashboard.
 *
 * @since 2.6.0
 */
do_action('woocommerce_account_dashboard');

/**
 * Deprecated woocommerce_before_my_account action.
 *
 * @deprecated 2.6.0
 */
do_action('woocommerce_before_my_account');

/**
 * Deprecated woocommerce_after_my_account action.
 *
 * @deprecated 2.6.0
 */
do_action('woocommerce_after_my_account');

/* Omit closing PHP tag at the end of PHP files to avoid "headers already sent" issues. */
