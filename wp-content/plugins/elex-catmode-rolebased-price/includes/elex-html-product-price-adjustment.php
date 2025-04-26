<?php
// to check whether accessed directly
if (!defined('ABSPATH')) {
	exit;
}
?>
<div>
    <?php
    global $wp_roles;
    global $post;
    $product = wc_get_product($_REQUEST['post']);
    if (!empty($product)) {
        if (WC()->version < '2.7.0') {
            $product_type = $product->product_type;
        } else {
            $product_type = $product->get_type();
        }
    }
    $decimal_steps = 1;
    $woo_decimal = wc_get_price_decimals();
    for ($temp=0;$temp<$woo_decimal;$temp++) {
        $decimal_steps = $decimal_steps/10;
    }
    ?>
</h3>

<!-- Option to hide add to cart for unregistered user-->
<h4 style="padding-left: 3%;">Unregistered Users:</h4>
<div style="padding-left: 3%;height: 60px;">
    <label style="margin-left: 0px;width: 40%;float: left;"><?php _e('Remove Add to Cart', 'elex-catmode-rolebased-price'); ?></label>
    <?php $checked = (( get_post_meta($post->ID, 'product_adjustment_hide_addtocart_unregistered', true) ) == 'yes' ) ? true : false; ?>
    <input type="checkbox" style="float: left;margin-left: 0px;" name="product_adjustment_hide_addtocart_unregistered" id="product_adjustment_hide_addtocart_unregistered" <?php checked($checked, true); ?> />
    <label style="float: left;margin-left:5px;"><?php _e('Enable', 'elex-catmode-rolebased-price'); ?></label>
    <span class="description" style="width: 60%;float: right;margin-top: 6px;">
        <?php _e('Check to remove Add to Cart.', 'elex-catmode-rolebased-price'); ?></span>
</div>
<div style="padding-left: 3%;height: 60px;" id="shop_product_checkbox_prod_hide_addtocart_unregistered">
    <?php $checked_shop = (( get_post_meta($post->ID, 'product_adjustment_hide_addtocart_unregistered_shop', true) ) == 'yes' ) ? true : false; 
     $checked_product = (( get_post_meta($post->ID, 'product_adjustment_hide_addtocart_unregistered_product', true) ) == 'yes' ) ? true : false; ?>
    <input type="checkbox" style="float: left;margin-left: 40%;" name="product_adjustment_hide_addtocart_unregistered_shop" id="product_adjustment_hide_addtocart_unregistered_shop" <?php checked($checked_shop, true); ?> />
    <label style="float: left;margin-left:5px;"><?php _e('Shop Page', 'elex-catmode-rolebased-price'); ?></label>
    <input type="checkbox" style="float: left;margin-left: 0px;" name="product_adjustment_hide_addtocart_unregistered_product" id="product_adjustment_hide_addtocart_unregistered_product" <?php checked($checked_product, true); ?> />
    <label style="float: left;margin-left:5px;"><?php _e('Product Page', 'elex-catmode-rolebased-price'); ?></label>
</div>
<div style="padding-left: 3%;height: 60px;" id="place_holder_prod_hide_addtocart_unregistered">
    <label style="margin-left: 0px;width: 40%;float: left;"><?php _e('Placeholder text', 'elex-catmode-rolebased-price'); ?></label>
    <?php $place_text =  get_post_meta($post->ID, 'product_adjustment_hide_addtocart_placeholder_unregistered',true); ?>
    <textarea name="product_adjustment_hide_addtocart_placeholder_unregistered" id="product_adjustment_hide_addtocart_placeholder_unregistered" style="width: 40%;"><?php echo $place_text; ?></textarea>
    <span style="font-size: 1.4em;"> <?php echo wc_help_tip(__("Enter a text or html content to display when Add to Cart button is removed. Leave it empty if you don't want to show any content.","elex-catmode-rolebased-price")); ?></span>
</div>

<!-- Option to Customize add to cart for unregistered user-->
<div style="padding-left: 3%;height: 60px;">
    <label style="margin-left: 0px;width: 40%;float: left;"><?php _e('Customize Add to Cart', 'elex-catmode-rolebased-price'); ?></label>
    <?php $checked = (( get_post_meta($post->ID, 'product_adjustment_customize_addtocart_unregistered', true) ) == 'yes' ) ? true : false; ?>
    <input type="checkbox" style="float: left;margin-left: 0px;" name="product_adjustment_customize_addtocart_unregistered" id="product_adjustment_customize_addtocart_unregistered" <?php checked($checked, true); ?> />
    <label style="float: left;margin-left:5px;"><?php _e('Enable', 'elex-catmode-rolebased-price'); ?></label>
    <span class="description" style="width: 60%;float: right;margin-top: 6px;">
        <?php _e('Check to customize Add to Cart.', 'elex-catmode-rolebased-price'); ?></span>
</div>
<div style="padding-left: 3%;height: 60px;" id="btn_text_prod_replace_addtocart_unregistered">
    <label style="margin-left: 0px;width: 40%;float: left;"><?php _e('Change Button Text (Product Page)', 'elex-catmode-rolebased-price'); ?></label>
    <?php $place_text =  get_post_meta($post->ID, 'product_adjustment_customize_addtocart_prod_btn_text_unregistered',true); ?>
    <input type="text" value="<?php echo $place_text ?>" name="product_adjustment_customize_addtocart_prod_btn_text_unregistered" id="product_adjustment_customize_addtocart_prod_btn_text_unregistered" style="width: 40%;">
    <span style="font-size: 1.4em;"> <?php echo wc_help_tip(__("Enter a text to replace the existing Add to Cart button text on the product page.","elex-catmode-rolebased-price")); ?></span>
</div>
<div style="padding-left: 3%;height: 60px;" id="btn_text_shop_replace_addtocart_unregistered">
    <label style="margin-left: 0px;width: 40%;float: left;"><?php _e('Change Button Text (Shop Page)', 'elex-catmode-rolebased-price'); ?></label>
    <?php $place_text =  get_post_meta($post->ID, 'product_adjustment_customize_addtocart_shop_btn_text_unregistered',true); ?>
    <input type="text" value="<?php echo $place_text ?>" name="product_adjustment_customize_addtocart_shop_btn_text_unregistered" id="product_adjustment_customize_addtocart_shop_btn_text_unregistered" style="width: 40%;">
    <span style="font-size: 1.4em;"> <?php echo wc_help_tip(__("Enter a text to replace the existing Add to Cart button text on the shop page.","elex-catmode-rolebased-price")); ?></span>
</div>
<div style="padding-left: 3%;height: 60px;" id="btn_url_replace_addtocart_unregistered">
    <label style="margin-left: 0px;width: 40%;float: left;"><?php _e('Change Button URL', 'elex-catmode-rolebased-price'); ?></label>
    <?php $place_text =  get_post_meta($post->ID, 'product_adjustment_customize_addtocart_btn_url_unregistered',true); ?>
    <input type="text" value="<?php echo $place_text ?>" name="product_adjustment_customize_addtocart_btn_url_unregistered" id="product_adjustment_customize_addtocart_btn_url_unregistered" style="width: 40%;">
    <span style="font-size: 1.4em;"> <?php echo wc_help_tip(__("Enter a url to redirect customers from Add to Cart button. Leave this field empty to not change the button functionality. Make sure to enter a text in the above fields to apply these changes.","elex-catmode-rolebased-price")); ?></span>
</div>

<!-- Option to hide price for unregistered user-->
<div style="padding-left: 3%;height: 60px;">
    <label style="margin-left: 0px;width: 40%;float: left;"><?php _e('Hide Regular Price', 'elex-catmode-rolebased-price'); ?></label>
    <?php $checked = (( get_post_meta($post->ID, 'product_adjustment_hide_regular_price_unregistered', true) ) == 'yes' ) ? true : false; ?>
    <input type="checkbox" style="float: left;margin-left: 0px;" name="product_adjustment_hide_regular_price_unregistered" id="product_adjustment_hide_regular_price_unregistered" <?php checked($checked, true); ?> />
    <label style="float: left;margin-left:5px;"><?php _e('Enable', 'elex-catmode-rolebased-price'); ?></label>
    <span class="description" style="width: 60%;float: right;margin-top: 6px;">
        <?php _e('Check to hide regular price.', 'elex-catmode-rolebased-price'); ?></span>
</div>
<div style="padding-left: 3%;height: 60px;">
    <label style="margin-left: 0px;width: 40%;float: left;"><?php _e('Hide Price', 'elex-catmode-rolebased-price'); ?></label>
    <?php $checked = (( get_post_meta($post->ID, 'product_adjustment_hide_price_unregistered', true) ) == 'yes' ) ? true : false; ?>
    <input type="checkbox" style="float: left;margin-left: 0px;" name="product_adjustment_hide_price_unregistered" id="product_adjustment_hide_price_unregistered" <?php checked($checked, true); ?> />
    <label style="float: left;margin-left:5px;"><?php _e('Enable', 'elex-catmode-rolebased-price'); ?></label>
    <span class="description" style="width: 60%;float: right;margin-top: 6px;">
        <?php _e('Check to hide price.', 'elex-catmode-rolebased-price'); ?></span>
</div>
<div style="padding-left: 3%;height: 60px;" id="place_holder_prod_hide_price_unregistered">
    <label style="margin-left: 0px;width: 40%;float: left;"><?php _e('Placeholder text', 'elex-catmode-rolebased-price'); ?></label>
    <?php $place_text =  get_post_meta($post->ID, 'product_adjustment_hide_price_placeholder_unregistered',true); ?>
    <textarea name="product_adjustment_hide_price_placeholder_unregistered" id="product_adjustment_hide_price_placeholder_unregistered" style="width: 40%;"><?php echo $place_text; ?></textarea>
    <span style="font-size: 1.4em;"> <?php echo wc_help_tip(__("Enter the text you want to display when price is removed. Leave it empty if you don't want to show any placeholder text.","elex-catmode-rolebased-price")); ?></span>
</div>

<!-- Option to hide product for unregistered user-->
<div style="padding-left: 3%;height: 60px;">
    <label style="margin-left: 0px;width: 40%;float: left;"><?php _e('Hide Product', 'elex-catmode-rolebased-price'); ?></label>
    <?php $checked = (( get_post_meta($post->ID, 'product_adjustment_product_visibility_unregistered', true) ) == 'yes' ) ? true : false; ?>
    <input type="checkbox" style="float: left;margin-left: 0px;" name="product_adjustment_product_visibility_unregistered" id="product_adjustment_product_visibility_unregistered" <?php checked($checked, true); ?> />
    <label style="float: left;margin-left:5px;"><?php _e('Enable', 'elex-catmode-rolebased-price'); ?></label>
    <span class="description" style="width: 60%;float: right;margin-top: 6px;">
        <?php _e('Check to hide product.', 'elex-catmode-rolebased-price'); ?></span>
</div>

<h4 style="padding-left: 3%;">Registered Users:</h4>
<!-- Option to hide add to cart for user role-->
<div style="padding-left: 3%;height: 60px;overflow : auto;">
    <label for="eh_pricing_adjustment_product_addtocart_user_role" style="margin-left: 0px;width: 40%;float: left;"><?php _e(' Remove Add to Cart', 'elex-catmode-rolebased-price'); ?></label>
    <select class="wc-enhanced-select" name="eh_pricing_adjustment_product_addtocart_user_role[]" id="eh_pricing_adjustment_product_addtocart_user_role" multiple="multiple" style="width: 50%;float: left;">
        <?php
        $hide_addtocart_role = get_post_meta($post->ID, 'eh_pricing_adjustment_product_addtocart_user_role', false);
        $user_roles = $wp_roles->role_names;
        foreach ($user_roles as $id => $name) {
            if (is_array(current($hide_addtocart_role)) && in_array($id, current($hide_addtocart_role))) {
                echo '<option value="' . $id . '" selected="selected">' . $name . '</option>';
            } else {
                echo '<option value="' . $id . '">' . $name . '</option>';
            }
        }
        ?>
    </select>
    <span style="font-size: 1.4em;"><?php echo wc_help_tip(__(' Select the user role(s) for which you want to remove Add to Cart.', 'elex-catmode-rolebased-price')); ?></span>
</div>
<div style="padding-left: 3%;height: 60px;" id="shop_product_checkbox_prod_hide_addtocart_role">
    <?php $checked_shop_role = (( get_post_meta($post->ID, 'product_adjustment_hide_addtocart_user_role_shop', true) ) == 'yes' ) ? true : false; 
     $checked_product_role = (( get_post_meta($post->ID, 'product_adjustment_hide_addtocart_user_role_product', true) ) == 'yes' ) ? true : false; ?>
    <input type="checkbox" style="float: left;margin-left: 40%;" name="product_adjustment_hide_addtocart_user_role_shop" id="product_adjustment_hide_addtocart_user_role_shop" <?php checked($checked_shop_role, true); ?> />
    <label style="float: left;margin-left:5px;"><?php _e('Shop Page', 'elex-catmode-rolebased-price'); ?></label>
    <input type="checkbox" style="float: left;margin-left: 0px;" name="product_adjustment_hide_addtocart_user_role_product" id="product_adjustment_hide_addtocart_user_role_product" <?php checked($checked_product_role, true); ?> />
    <label style="float: left;margin-left:5px;"><?php _e('Product Page', 'elex-catmode-rolebased-price'); ?></label>
</div>
<div style="padding-left: 3%;height: 60px;" id="place_holder_prod_hide_addtocart_role">
    <label style="margin-left: 0px;width: 40%;float: left;"><?php _e('Placeholder text', 'elex-catmode-rolebased-price'); ?></label>
    <?php $place_text =  get_post_meta($post->ID, 'product_adjustment_hide_addtocart_placeholder_role',true); ?>
    <textarea name="product_adjustment_hide_addtocart_placeholder_role" id="product_adjustment_hide_addtocart_placeholder_role" style="width: 40%;"><?php echo $place_text; ?></textarea>
    <span style="font-size: 1.4em;"> <?php echo wc_help_tip(__("Enter a text or html content to display when Add to Cart button is removed. Leave it empty if you don't want to show any content.","elex-catmode-rolebased-price")); ?></span>
</div>

<!-- Option to customize add to cart for user role-->
<div style="padding-left: 3%;height: 60px;overflow : auto;">
    <label for="eh_pricing_adjustment_product_customize_addtocart_user_role" style="margin-left: 0px;width: 40%;float: left;"><?php _e(' Customize Add to Cart', 'elex-catmode-rolebased-price'); ?></label>
    <select class="wc-enhanced-select" name="eh_pricing_adjustment_product_customize_addtocart_user_role[]" id="eh_pricing_adjustment_product_customize_addtocart_user_role" multiple="multiple" style="width: 50%;float: left;">
        <?php
        $customize_addtocart_role = get_post_meta($post->ID, 'eh_pricing_adjustment_product_customize_addtocart_user_role', false);
        $user_roles = $wp_roles->role_names;
        foreach ($user_roles as $id => $name) {
            if (is_array(current($customize_addtocart_role)) && in_array($id, current($customize_addtocart_role))) {
                echo '<option value="' . $id . '" selected="selected">' . $name . '</option>';
            } else {
                echo '<option value="' . $id . '">' . $name . '</option>';
            }
        }
        ?>
    </select>
    <span style="font-size: 1.4em;"><?php echo wc_help_tip(__(' Select the user role(s) for which you want to customize Add to Cart.', 'elex-catmode-rolebased-price')); ?></span>
</div>
<div style="padding-left: 3%;height: 60px;" id="btn_text_prod_replace_addtocart_role">
    <label style="margin-left: 0px;width: 40%;float: left;"><?php _e('Change Button Text (Product Page)', 'elex-catmode-rolebased-price'); ?></label>
    <?php $place_text =  get_post_meta($post->ID, 'product_adjustment_customize_addtocart_prod_btn_text_role',true); ?>
    <input type="text" value="<?php echo $place_text ?>" name="product_adjustment_customize_addtocart_prod_btn_text_role" id="product_adjustment_customize_addtocart_prod_btn_text_role" style="width: 40%;">
    <span style="font-size: 1.4em;"> <?php echo wc_help_tip(__("Enter a text to replace the existing Add to Cart button text on the product page.","elex-catmode-rolebased-price")); ?></span>
</div>
<div style="padding-left: 3%;height: 60px;" id="btn_text_shop_replace_addtocart_role">
    <label style="margin-left: 0px;width: 40%;float: left;"><?php _e('Change Button Text (Shop Page)', 'elex-catmode-rolebased-price'); ?></label>
    <?php $place_text =  get_post_meta($post->ID, 'product_adjustment_customize_addtocart_shop_btn_text_role',true); ?>
    <input type="text" value="<?php echo $place_text ?>" name="product_adjustment_customize_addtocart_shop_btn_text_role" id="product_adjustment_customize_addtocart_shop_btn_text_role" style="width: 40%;">
    <span style="font-size: 1.4em;"> <?php echo wc_help_tip(__("Enter a text to replace the existing Add to Cart button text on the shop page.","elex-catmode-rolebased-price")); ?></span>
</div>
<div style="padding-left: 3%;height: 60px;" id="btn_url_replace_addtocart_role">
    <label style="margin-left: 0px;width: 40%;float: left;"><?php _e('Change Button URL', 'elex-catmode-rolebased-price'); ?></label>
    <?php $place_text =  get_post_meta($post->ID, 'product_adjustment_customize_addtocart_btn_url_role',true); ?>
    <input type="text" value="<?php echo $place_text ?>" name="product_adjustment_customize_addtocart_btn_url_role" id="product_adjustment_customize_addtocart_btn_url_role" style="width: 40%;">
    <span style="font-size: 1.4em;"> <?php echo wc_help_tip(__("Enter a url to redirect customers from Add to Cart button. Leave this field empty to not change the button functionality. Make sure to enter a text in the above fields to apply these changes.","elex-catmode-rolebased-price")); ?></span>
</div>

<!-- Option to hide price for user role-->
<div style="padding-left: 3%;height: 60px;overflow : auto;">
    <label for="eh_pricing_adjustment_product_hide_regular_price_user_role" style="margin-left: 0px;width: 40%;float: left;"><?php _e(' Hide Regular Price', 'elex-catmode-rolebased-price'); ?></label>
    <select class="wc-enhanced-select" name="eh_pricing_adjustment_product_hide_regular_price_user_role[]" id="eh_pricing_adjustment_product_hide_regular_price_user_role" multiple="multiple" style="width: 50%;float: left;">
        <?php
        $hide_price_role = get_post_meta($post->ID, 'eh_pricing_adjustment_product_hide_regular_price_user_role', false);
        $user_roles = $wp_roles->role_names;
        foreach ($user_roles as $id => $name) {
            if (is_array(current($hide_price_role)) && in_array($id, current($hide_price_role))) {
                echo '<option value="' . $id . '" selected="selected">' . $name . '</option>';
            } else {
                echo '<option value="' . $id . '">' . $name . '</option>';
            }
        }
        ?>
    </select>
    <span style="font-size: 1.4em;"><?php echo wc_help_tip(__(' Select the user role(s) for which you want to hide the Regular Price. This will be applicable if the product has either discounted or sales price.', 'elex-catmode-rolebased-price')); ?></span>
</div>
<div style="padding-left: 3%;height: 60px;overflow : auto;">
    <label for="eh_pricing_adjustment_product_price_user_role" style="margin-left: 0px;width: 40%;float: left;"><?php _e(' Hide Price', 'elex-catmode-rolebased-price'); ?></label>
    <select class="wc-enhanced-select" name="eh_pricing_adjustment_product_price_user_role[]" id="eh_pricing_adjustment_product_price_user_role" multiple="multiple" style="width: 50%;float: left;">
        <?php
        $hide_price_role = get_post_meta($post->ID, 'eh_pricing_adjustment_product_price_user_role', false);
        $user_roles = $wp_roles->role_names;
        foreach ($user_roles as $id => $name) {
            if (is_array(current($hide_price_role)) && in_array($id, current($hide_price_role))) {
                echo '<option value="' . $id . '" selected="selected">' . $name . '</option>';
            } else {
                echo '<option value="' . $id . '">' . $name . '</option>';
            }
        }
        ?>
    </select>
    <span style="font-size: 1.4em;"><?php echo wc_help_tip(__(' Select the user role(s) for which you want to hide price.', 'elex-catmode-rolebased-price')); ?></span>
</div>
<div style="padding-left: 3%;height: 60px;" id="place_holder_prod_hide_price_role">
    <label style="margin-left: 0px;width: 40%;float: left;"><?php _e('Placeholder text', 'elex-catmode-rolebased-price'); ?></label>
    <?php $place_text =  get_post_meta($post->ID, 'product_adjustment_hide_price_placeholder_role',true); ?>
    <textarea name="product_adjustment_hide_price_placeholder_role" id="product_adjustment_hide_price_placeholder_role" style="width: 40%;"><?php echo $place_text; ?></textarea>
    <span style="font-size: 1.4em;"> <?php echo wc_help_tip(__("Enter the text you want to display when price is removed. Leave it empty if you don't want to show any placeholder text.","elex-catmode-rolebased-price")); ?></span>
</div>

<!-- Option to hide product for user role-->
<div style="padding-left: 3%;height: 60px;overflow : auto;">
    <label for="eh_pricing_adjustment_product_visibility_user_role" style="margin-left: 0px;width: 40%;float: left;"><?php _e(' Hide Product', 'elex-catmode-rolebased-price'); ?></label>
    <select class="wc-enhanced-select" name="eh_pricing_adjustment_product_visibility_user_role[]" id="eh_pricing_adjustment_product_visibility_user_role" multiple="multiple" style="width: 50%;float: left;">
        <?php
        $hide_product_role = get_post_meta($post->ID, 'eh_pricing_adjustment_product_visibility_user_role', false);
        $user_roles = $wp_roles->role_names;
        foreach ($user_roles as $id => $name) {
            if (is_array(current($hide_product_role)) && in_array($id, current($hide_product_role))) {
                echo '<option value="' . $id . '" selected="selected">' . $name . '</option>';
            } else {
                echo '<option value="' . $id . '">' . $name . '</option>';
            }
        }
        ?>
    </select>
    <span style="font-size: 1.4em;"><?php echo wc_help_tip(__(' Select the user role(s) for which you want to hide product.', 'elex-catmode-rolebased-price'));?></span>
</div>
<br>
<!-- Option to enforce porduct based adjustment-->
<?php
$user_roles = get_option('eh_pricing_discount_product_price_user_role');
$users= get_option('eh_pricing_discount_product_on_users');
if (is_array($user_roles) && !empty($user_roles)) {
    if ($product_type == 'simple' || $product_type == 'variable') {
        ?>
<h4 style="padding-left: 3%;">Price Adjustment:</h4>
        <h4 style="padding-left: 3%;color: green">To set a price for the product, go to <a href="" onclick="if (jQuery('#product-type').val() == 'variable') {
                            jQuery('.variations_tab').find('a').click();
                        } else {
                            jQuery('.general_tab').find('a').click();
                        }
                        return false;"><?php
                                                                                                       if ($product_type == 'variable') {
                                                                                                           ?>
                    variations settings<?php } elseif ($product_type == 'simple') {
                                                                                                           ?>
                    general settings<?php
                }
                ?> </a></h4>


        <div style="padding-left: 3%;height: 50px;">
            <label style="width: 40%; height: 40px; float: left;margin-left: 0px;"><?php _e(' Individual Product Price Adjustment', 'elex-catmode-rolebased-price'); ?></label>
            <?php $checked = ( ( get_post_meta($post->ID, 'product_based_price_adjustment', true) ) == 'yes' ) ? true : false; ?>
            <input type="checkbox" name="product_based_price_adjustment" id="product_based_price_adjustment" <?php checked($checked, true); ?> />
            <label style="width: 57%;float: right;margin-top: 3px;"><?php _e(' Enable', 'elex-catmode-rolebased-price'); ?></label>

        </div>

        <!-- Option to determine user role based adjustment-->
        <div id="individual_product_price_adjustment_field" style="padding-left: 3%;padding-bottom: 3%;width: 94%;overflow : auto;">
            <p class="description" style="margin-left: 0px;"> <?php _e(" Drag and drop user roles to set priority. If a single user has multiple user roles assigned, the user role with the highest priority will be chosen. Choose 'D' for DISCOUNT and 'M' for MARKUP.", "elex-catmode-rolebased-price"); ?></p>
            <table class="product_price_adjustment widefat" id="eh_pricing_discount_product_price_adjustment_data">
                <thead>
                    <tr>
                        <th class="sort">&nbsp;</th>
                        <th><?php _e('User Role', 'elex-catmode-rolebased-price'); ?></th>
                        <th style="text-align:center;"><?php echo sprintf(__('Price Adjustment (%s)', 'elex-catmode-rolebased-price'), get_woocommerce_currency_symbol()); ?></th>
                        <th style="text-align:center;"><?php _e('Price Adjustment (%)', 'elex-catmode-rolebased-price'); ?></th>
                        <th style="text-align:center;"><?php _e('Enable', 'elex-catmode-rolebased-price'); ?></th>
                    </tr>
                </thead>
                <tbody>

                    <?php
                    $this->price_table = array();
                    $i = 0;
                    $product_adjustment_price = get_post_meta($post->ID, 'product_price_adjustment', false);
					$wordpress_roles = $wp_roles->role_names;
					$wordpress_roles['unregistered_user'] = 'Unregistered User';
					
					if (empty($product_adjustment_price)) {
                        foreach ($user_roles as $id => $value) {
                            $this->price_table[$i]['id'] = $value;
                            $this->price_table[$i]['name'] = $wordpress_roles[$value];
                            $this->price_table[$i]['adjustment_price'] = '';
                            $this->price_table[$i]['adjustment_percent'] = '';
                            $this->price_table[$i]['role_price'] = '';
                            $this->price_table[$i]['adj_prod_percent_dis'] = '';
                            $this->price_table[$i]['adj_prod_price_dis'] = '';
                            $i++;
                        }
                    } else {
                        if (is_array(current($product_adjustment_price)) || is_object(current($product_adjustment_price))) {
                            foreach (current($product_adjustment_price) as $id => $value) {
                                if (in_array($id, $user_roles)) {
                                    $current_adjustment_price = current($product_adjustment_price);
                                    $this->price_table[$i]['id'] = $id;
                                    $this->price_table[$i]['name'] = $wordpress_roles[$id];
                                    $this->price_table[$i]['adjustment_price'] = $current_adjustment_price[$id]['adjustment_price'];
                                    $this->price_table[$i]['adjustment_percent'] = $current_adjustment_price[$id]['adjustment_percent'];
                                     $this->price_table[$i]['adj_prod_percent_dis'] = isset($current_adjustment_price[$id]['adj_prod_percent_dis']) ? $current_adjustment_price[$id]['adj_prod_percent_dis'] : 'discount';
                                     $this->price_table[$i]['adj_prod_price_dis'] = isset($current_adjustment_price[$id]['adj_prod_price_dis']) ? $current_adjustment_price[$id]['adj_prod_price_dis'] : 'discount';
                                    if (key_exists('role_price', $current_adjustment_price[$id])) {
                                        $this->price_table[$i]['role_price'] = $current_adjustment_price[$id]['role_price'];
                                    } else {
                                        $this->price_table[$i]['role_price'] = '';
                                    }
                                    $i++;
                                    unset($user_roles[array_search($id, $user_roles)]);
                                }
                            }
                        }

                        if (!empty($user_roles)) {
                            foreach ($user_roles as $id => $value) {
                                $this->price_table[$i]['id'] = $value;
                                $this->price_table[$i]['name'] = $wordpress_roles[$value];
                                $this->price_table[$i]['adjustment_price'] = '';
                                $this->price_table[$i]['adjustment_percent'] = '';
                                $this->price_table[$i]['role_price'] = '';
                                $i++;
                            }
                        }
                    }

                    foreach ($this->price_table as $key => $value) {
                        ?>
                        <tr>
                            <td class="sort">
                                <input type="hidden" class="order" name="product_price_adjustment[<?php echo $this->price_table[$key]['id'] ?>]" value="<?php echo $this->price_table[$key]['id']; ?>" />
                            </td>
                            <td>
                                <label name="product_price_adjustment[<?php echo $this->price_table[$key]['id']; ?>][name]" style="margin-left:0px;"><?php echo isset($this->price_table[$key]['name']) ? $this->price_table[$key]['name'] : ''; ?></label>
                            </td>
                            <td style="text-align: center;">
                                <?php echo get_woocommerce_currency_symbol(); ?><input type="number" style="width: 50%;" min="0" step="<?php echo $decimal_steps ?>" name="product_price_adjustment[<?php echo $this->price_table[$key]['id']; ?>][adjustment_price]" placeholder="N/A" value="<?php echo isset($this->price_table[$key]['adjustment_price']) ? $this->price_table[$key]['adjustment_price'] : ''; ?>" />
                                 <?php
                                $select_prod_price_dis = 'selected';
                                $select_prod_price_mar = '';
                                if(isset($this->price_table[$key]['adj_prod_price_dis']) && $this->price_table[$key]['adj_prod_price_dis'] == 'markup'){
                                    $select_prod_price_mar = 'selected';
                                    $select_prod_price_dis = '';
                                }
                                ?>
                                <select name="product_price_adjustment[<?php echo $this->price_table[$key]['id']; ?>][adj_prod_price_dis]"><option value="discount" <?php echo $select_prod_price_dis ?>>D</option><option value="markup" <?php echo $select_prod_price_mar ?>>M</option></select>
                            </td>
                            <td style="text-align: center;">
                                <input type="number" style="width: 50%;" min="0" step="<?php echo $decimal_steps ?>" name="product_price_adjustment[<?php echo $this->price_table[$key]['id']; ?>][adjustment_percent]" placeholder="N/A" value="<?php echo isset($this->price_table[$key]['adjustment_percent']) ? $this->price_table[$key]['adjustment_percent'] : ''; ?>" />%
                                <?php
                                $select_prod_percent_dis = 'selected';
                                $select_prod_percent_mar = '';
                                if(isset($this->price_table[$key]['adj_prod_percent_dis']) && $this->price_table[$key]['adj_prod_percent_dis'] == 'markup'){
                                    $select_prod_percent_mar = 'selected';
                                    $select_prod_percent_dis = '';
                                }
                             ?>
                                <select name="product_price_adjustment[<?php echo $this->price_table[$key]['id']; ?>][adj_prod_percent_dis]"><option value="discount" <?php echo $select_prod_percent_dis ?>>D</option><option value="markup" <?php echo $select_prod_percent_mar ?>>M</option></select>
                            </td>
                            <td style="text-align:center;">
                                <label style="margin-left:0px; width: 5%;">
                                    <?php $checked = (!empty($this->price_table[$key]['role_price']) ) ? true : false; ?>
                                    <input type="checkbox" name="product_price_adjustment[<?php echo $this->price_table[$key]['id']; ?>][role_price]" <?php checked($checked, true); ?> />
                                </label>
                            </td>
                        </tr>
                        <?php
                    }
                    ?>
                </tbody>
            </table>
        </div>
<?php 
	}
}
if (is_array($users) && !empty($users)) {
	?>
        <div id="individual_product_price_adjustment_field_for_users" style="padding-left: 3%;padding-bottom: 3%;width: 94%;overflow : auto;">
            
            <table class="product_price_adjustment_for_users widefat" id="eh_pricing_discount_product_price_adjustment_data_for_users">
                <thead>
                    <tr>
                        <th class="sort">&nbsp;</th>
                        <th><?php _e('Users', 'elex-catmode-rolebased-price'); ?></th>
                        <th style="text-align:center;"><?php echo sprintf(__('Price Adjustment (%s)', 'elex-catmode-rolebased-price'), get_woocommerce_currency_symbol()); ?></th>
                        <th style="text-align:center;"><?php _e('Price Adjustment (%)', 'elex-catmode-rolebased-price'); ?></th>
                        <th style="text-align:center;"><?php _e('Enable', 'elex-catmode-rolebased-price'); ?></th>
                    </tr>
                </thead>
                <tbody>

                    <?php
                    $this->price_table = array();
                    $user_roles = get_option('eh_pricing_discount_product_on_users');
                    $i = 0;
                    $product_adjustment_price = get_post_meta($post->ID, 'product_price_adjustment_for_users', false);
                    if (empty($product_adjustment_price)) {
                        foreach ($user_roles['users'] as $id => $value) {
                            $this->price_table[$i]['id'] = $value;
                            $user = get_user_by('id', $value);
                            $user_name = '';
                            if (is_object($user)) {
                                $user_name = $user->display_name.'(#'.$user->ID.') - '.$user->user_email;
                            }   
                            $this->price_table[$i]['name'] = $user_name;
                            $this->price_table[$i]['adjustment_price'] = '';
                            $this->price_table[$i]['adjustment_percent'] = '';
                            $this->price_table[$i]['role_price'] = '';
                            $this->price_table[$i]['adj_prod_percent_dis'] = '';
                            $this->price_table[$i]['adj_prod_price_dis'] = '';
                            $i++;
                        }
                    } else {
                        if (is_array(current($product_adjustment_price)) || is_object(current($product_adjustment_price))) {
                            foreach (current($product_adjustment_price) as $id => $value) {
                                if (in_array($id, $user_roles['users'])) {
                                    $current_adjustment_price = current($product_adjustment_price);
                                    $this->price_table[$i]['id'] = $id;
                                    $user = get_user_by('id', $id);
                                    $user_name = '';
                                    if (is_object($user)) {
                                        $user_name = $user->display_name.'(#'.$user->ID.') - '.$user->user_email;
                                    } 
                                    $this->price_table[$i]['name'] = $user_name;
                                    $this->price_table[$i]['adjustment_price'] = $current_adjustment_price[$id]['adjustment_price'];
                                    $this->price_table[$i]['adjustment_percent'] = $current_adjustment_price[$id]['adjustment_percent'];
                                     $this->price_table[$i]['adj_prod_percent_dis'] = isset($current_adjustment_price[$id]['adj_prod_percent_dis']) ? $current_adjustment_price[$id]['adj_prod_percent_dis'] : 'discount';
                                     $this->price_table[$i]['adj_prod_price_dis'] = isset($current_adjustment_price[$id]['adj_prod_price_dis']) ? $current_adjustment_price[$id]['adj_prod_price_dis'] : 'discount';
                                    if (key_exists('role_price', $current_adjustment_price[$id])) {
                                        $this->price_table[$i]['role_price'] = $current_adjustment_price[$id]['role_price'];
                                    } else {
                                        $this->price_table[$i]['role_price'] = '';
                                    }
                                    $i++;
                                    unset($user_roles['users'][array_search($id, $user_roles['users'])]);
                                }
                            }
                        }

                        if (!empty($user_roles['users'])) {
                            foreach ($user_roles['users'] as $id => $value) {
                                $this->price_table[$i]['id'] = $value;
                                $user = get_user_by('id', $value);
                                $user_name = '';
                                if (is_object($user)) {
                                    $user_name = $user->display_name.'(#'.$user->ID.') - '.$user->user_email;
                                } 
                                $this->price_table[$i]['name'] = $user_name;
                                $this->price_table[$i]['adjustment_price'] = '';
                                $this->price_table[$i]['adjustment_percent'] = '';
                                $this->price_table[$i]['role_price'] = '';
                                $i++;
                            }
                        }
                    }

                    foreach ($this->price_table as $key => $value) {
                        ?>
                        <tr>
                            <td class="sort">
                                <input type="hidden" class="order" name="product_price_adjustment_for_users[<?php echo $this->price_table[$key]['id'] ?>]" value="<?php echo $this->price_table[$key]['id']; ?>" />
                            </td>
                            <td>
                                <label name="product_price_adjustment_for_users[<?php echo $this->price_table[$key]['id']; ?>][name]" style="margin-left:0px;"><?php echo isset($this->price_table[$key]['name']) ? $this->price_table[$key]['name'] : ''; ?></label>
                            </td>
                            <td style="text-align: center;">
                                <?php echo get_woocommerce_currency_symbol(); ?><input type="number" style="width: 50%;" min="0" step="<?php echo $decimal_steps ?>" name="product_price_adjustment_for_users[<?php echo $this->price_table[$key]['id']; ?>][adjustment_price]" placeholder="N/A" value="<?php echo isset($this->price_table[$key]['adjustment_price']) ? $this->price_table[$key]['adjustment_price'] : ''; ?>" />
                                 <?php
                                $select_prod_price_dis = 'selected';
                                $select_prod_price_mar = '';
                                if(isset($this->price_table[$key]['adj_prod_price_dis']) && $this->price_table[$key]['adj_prod_price_dis'] == 'markup'){
                                    $select_prod_price_mar = 'selected';
                                    $select_prod_price_dis = '';
                                }
                                ?>
                                <select name="product_price_adjustment_for_users[<?php echo $this->price_table[$key]['id']; ?>][adj_prod_price_dis]"><option value="discount" <?php echo $select_prod_price_dis ?>>D</option><option value="markup" <?php echo $select_prod_price_mar ?>>M</option></select>
                            </td>
                            <td style="text-align: center;">
                                <input type="number" style="width: 50%;" min="0" step="<?php echo $decimal_steps ?>" name="product_price_adjustment_for_users[<?php echo $this->price_table[$key]['id']; ?>][adjustment_percent]" placeholder="N/A" value="<?php echo isset($this->price_table[$key]['adjustment_percent']) ? $this->price_table[$key]['adjustment_percent'] : ''; ?>" />%
                                <?php
                                $select_prod_percent_dis = 'selected';
                                $select_prod_percent_mar = '';
                                if(isset($this->price_table[$key]['adj_prod_percent_dis']) && $this->price_table[$key]['adj_prod_percent_dis'] == 'markup'){
                                    $select_prod_percent_mar = 'selected';
                                    $select_prod_percent_dis = '';
                                }
                             ?>
                                <select name="product_price_adjustment_for_users[<?php echo $this->price_table[$key]['id']; ?>][adj_prod_percent_dis]"><option value="discount" <?php echo $select_prod_percent_dis ?>>D</option><option value="markup" <?php echo $select_prod_percent_mar ?>>M</option></select>
                            </td>
                            <td style="text-align:center;">
                                <label style="margin-left:0px; width: 5%;">
                                    <?php $checked = (!empty($this->price_table[$key]['role_price']) ) ? true : false; ?>
                                    <input type="checkbox" name="product_price_adjustment_for_users[<?php echo $this->price_table[$key]['id']; ?>][role_price]" <?php checked($checked, true); ?> />
                                </label>
                            </td>
                        </tr>
                        <?php
                    }
                    ?>
                </tbody>
            </table>
        </div>
    <?php 
}
?>
</div>
<script type="text/javascript">

    jQuery(window).on('load', function () {
        // Ordering
        jQuery('.product_price_adjustment tbody, .product_price_adjustment_for_users tbody').sortable({
            items: 'tr',
            cursor: 'move',
            axis: 'y',
            handle: '.sort',
            scrollSensitivity: 40,
            forcePlaceholderSize: true,
            helper: 'clone',
            opacity: 0.65,
            placeholder: 'wc-metabox-sortable-placeholder',
            start: function (event, ui) {
                ui.item.css('baclbsround-color', '#f6f6f6');
            },
            stop: function (event, ui) {
                ui.item.removeAttr('style');
            }
        });

        

        elex_rp_hide_product_placeholder_and_shop_product_checkbox('#product_adjustment_hide_addtocart_unregistered','#place_holder_prod_hide_addtocart_unregistered','#shop_product_checkbox_prod_hide_addtocart_unregistered');
        elex_rp_hide_product_placeholder_text('#product_adjustment_hide_price_unregistered','#place_holder_prod_hide_price_unregistered');
        elex_rp_hide_product_user_placeholder_and_shop_product_checkbox('#eh_pricing_adjustment_product_addtocart_user_role','#place_holder_prod_hide_addtocart_role','#shop_product_checkbox_prod_hide_addtocart_role');
        elex_rp_hide_product_user_placeholder_text('#eh_pricing_adjustment_product_price_user_role','#place_holder_prod_hide_price_role');
        elex_rp_customize_addtocart_product_unrigistered_customize_addtocart();
        elex_rp_customize_addtocart_product_role_customize_addtocart();
        elex_rp_hide_individual_product_price_adjustment_field();
        jQuery("#product_adjustment_hide_addtocart_unregistered").click(function () {
            elex_rp_hide_product_placeholder_and_shop_product_checkbox('#product_adjustment_hide_addtocart_unregistered','#place_holder_prod_hide_addtocart_unregistered','#shop_product_checkbox_prod_hide_addtocart_unregistered');
        });
        jQuery("#product_adjustment_hide_price_unregistered").click(function () {
            elex_rp_hide_product_placeholder_text('#product_adjustment_hide_price_unregistered','#place_holder_prod_hide_price_unregistered');
        });
        jQuery('#eh_pricing_adjustment_product_addtocart_user_role').change(function () {
            elex_rp_hide_product_user_placeholder_and_shop_product_checkbox('#eh_pricing_adjustment_product_addtocart_user_role','#place_holder_prod_hide_addtocart_role','#shop_product_checkbox_prod_hide_addtocart_role');
        });
        jQuery('#eh_pricing_adjustment_product_price_user_role').change(function () {
            elex_rp_hide_product_user_placeholder_text('#eh_pricing_adjustment_product_price_user_role','#place_holder_prod_hide_price_role');
        });
        jQuery('#product_adjustment_customize_addtocart_unregistered').change(function () {
            elex_rp_customize_addtocart_product_unrigistered_customize_addtocart();
        });
        jQuery('#eh_pricing_adjustment_product_customize_addtocart_user_role').change(function () {
            elex_rp_customize_addtocart_product_role_customize_addtocart();
        });
        jQuery('#product_based_price_adjustment').change(function () {
            elex_rp_hide_individual_product_price_adjustment_field();
        });
        
        function elex_rp_hide_product_placeholder_text(check, hide_field) {
            if (jQuery(check).is(":checked")) {
                jQuery(hide_field).show();
            } else {
                jQuery(hide_field).hide();
            }
        }
        function elex_rp_hide_product_placeholder_and_shop_product_checkbox(check, hide_field1, hide_field2) {
            if (jQuery(check).is(":checked")) {
                jQuery(hide_field1).show();
                jQuery(hide_field2).show();
            } else {
                jQuery(hide_field1).hide();
                jQuery(hide_field2).hide();
            }
        }
        function elex_rp_hide_product_user_placeholder_text(check, hide_field) {
            options = jQuery(check).val();
            if (options != null) {
                jQuery(hide_field).show();
            } else {
                jQuery(hide_field).hide();
            }
        }
        function elex_rp_hide_product_user_placeholder_and_shop_product_checkbox(check, hide_field1, hide_field2) {
            options = jQuery(check).val();
            if (options != null) {
                jQuery(hide_field1).show();
                jQuery(hide_field2).show();
            } else {
                jQuery(hide_field1).hide();
                jQuery(hide_field2).hide();
            }
        }
        function elex_rp_customize_addtocart_product_unrigistered_customize_addtocart() {
            if (jQuery('#product_adjustment_customize_addtocart_unregistered').is(":checked")) {
                jQuery('#btn_text_prod_replace_addtocart_unregistered').show();
                jQuery('#btn_text_shop_replace_addtocart_unregistered').show();
                jQuery('#btn_url_replace_addtocart_unregistered').show();
            } else {
                jQuery('#btn_text_prod_replace_addtocart_unregistered').hide();
                jQuery('#btn_text_shop_replace_addtocart_unregistered').hide();
                jQuery('#btn_url_replace_addtocart_unregistered').hide();
            }
        }
        function elex_rp_customize_addtocart_product_role_customize_addtocart() {
            options = jQuery('#eh_pricing_adjustment_product_customize_addtocart_user_role').val();
            if (options != null) {
                jQuery('#btn_text_prod_replace_addtocart_role').show();
                jQuery('#btn_text_shop_replace_addtocart_role').show();
                jQuery('#btn_url_replace_addtocart_role').show();
            } else {
                jQuery('#btn_text_prod_replace_addtocart_role').hide();
                jQuery('#btn_text_shop_replace_addtocart_role').hide();
                jQuery('#btn_url_replace_addtocart_role').hide();
            }
        }
        function elex_rp_hide_individual_product_price_adjustment_field() {
            if (jQuery('#product_based_price_adjustment').is(":checked")) {
                jQuery('#individual_product_price_adjustment_field').show();
                jQuery('#individual_product_price_adjustment_field_for_users').show();
            }
            else {
                jQuery('#individual_product_price_adjustment_field').hide();
                jQuery('#individual_product_price_adjustment_field_for_users').hide();
            }
        }

    });

</script>

<style type="text/css">
    .product_price_adjustment th.sort, .product_price_adjustment_for_users th.sort {
        width: 16px;
        padding: 0 16px;
    }
    .product_price_adjustment td.sort, .product_price_adjustment_for_users td.sort {
        cursor: move;
        width: 16px;
        padding: 0 16px;
        cursor: move;
        background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAgAAAAICAYAAADED76LAAAAHUlEQVQYV2O8f//+fwY8gJGgAny6QXKETRgEVgAAXxAVsa5Xr3QAAAAASUVORK5CYII=) no-repeat center;
    }

</style>