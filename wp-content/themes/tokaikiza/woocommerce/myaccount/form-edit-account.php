<?php

/**
 * Edit account form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/form-edit-account.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.5.0
 */

defined('ABSPATH') || exit;
do_action('woocommerce_before_edit_account_form'); ?>
<div class="account-title"><?php echo esc_html__("Account details", 'tokaikiza') ?></div>
<div class="hr"></div>
<div class="account-container">
	<form class="woocommerce-EditAccountForm edit-account" action="" method="post" <?php do_action('woocommerce_edit_account_form_tag'); ?>>
		<div class="edit-account account-detail">
			<div class="edit-info-title"><?php echo esc_html__("Edit Information", 'tokaikiza') ?></div>
			<?php do_action('woocommerce_edit_account_form_start'); ?>
			<p class="woocommerce-form-row woocommerce-form-row--first form-row form-row-first">
				<label for="account_first_name"><?php esc_html_e('会社名', 'tokaikiza'); ?><span class="icon-required">*</span>&nbsp;<span class="required"></span></label>
				<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="account_first_name" id="account_first_name" autocomplete="given-name" value="<?php echo esc_attr($user->first_name); ?>" />
			</p>
			<!-- <p class="woocommerce-form-row woocommerce-form-row--last form-row form-row-last">
				<label for="account_last_name"><?php esc_html_e('last name', 'tokaikiza'); ?><span class="icon-required">*</span>&nbsp;<span class="required"></span></label>
				<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="account_last_name" id="account_last_name" autocomplete="family-name" value="<?php echo esc_attr($user->last_name); ?>" />
			</p> -->
			<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
				<label for="account_email"><?php esc_html_e('email', 'tokaikiza'); ?><span class="icon-required">*</span>&nbsp;<span class="required"></span></label>
				<input type="email" class="woocommerce-Input woocommerce-Input--email input-text" name="account_email" id="account_email" autocomplete="email" value="<?php echo esc_attr($user->user_email); ?>" />
			</p>
			<input type="hidden" class="woocommerce-Input woocommerce-Input--text input-text" name="account_display_name" id="account_display_name" value="<?php echo esc_attr( $user->display_name ); ?>" />
		</div>
		<div class="form-change-pw">
			<fieldset>
				<div class="edit-info-title change-pw"><?php esc_html_e('Change Password', 'tokaikiza'); ?></div>

				<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide icon">
					<label for="password_current"><?php esc_html_e('Current Password', 'tokaikiza'); ?><span class="icon-required">*</span></label>
					<input placeholder="<?php esc_attr_e('Enter current password', 'tokaikiza'); ?>" type="password" class="woocommerce-Input woocommerce-Input--password input-text" name="password_current" id="password_current" autocomplete="off" />
				</p>
				<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide icon">
					<label for="password_1"><?php esc_html_e('New password', 'tokaikiza'); ?><span class="icon-required">*</span></label>
					<input placeholder="<?php esc_attr_e('Enter new password', 'tokaikiza'); ?>" type="password" class="woocommerce-Input woocommerce-Input--password input-text" name="password_1" id="password_1" autocomplete="off" />
				</p>
				<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wid icon">
					<label for="password_2"><?php esc_html_e('Confirm password', 'tokaikiza'); ?><span class="icon-required">*</span></label>
					<input placeholder="<?php esc_attr_e('Enter confirm password', 'tokaikiza'); ?>" type="password" class="woocommerce-Input woocommerce-Input--password input-text" name="password_2" id="password_2" autocomplete="off" />

				</p>
			</fieldset>
		</div>
		<?php do_action('woocommerce_edit_account_form'); ?>
		<p class="btn-save">
			<?php wp_nonce_field('save_account_details', 'save-account-details-nonce'); ?>
			<button type="submit" class="woocommerce-Button button" name="save_account_details" value="<?php esc_attr_e('Edit', 'tokaikiza'); ?>"><?php esc_html_e('Save', 'tokaikiza'); ?></button>
			<input type="hidden" name="action" value="save_account_details" />
		</p>
		<?php do_action('woocommerce_edit_account_form_end'); ?>
	</form>

</div>
<?php do_action('woocommerce_after_edit_account_form'); ?>