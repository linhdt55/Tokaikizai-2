<?php
// to check whether accessed directly
if (!defined('ABSPATH')) {
	exit;
}
?>
<tr valign="top" >
    <td class="forminp" colspan="2" style="padding-left:0px">
            <?php
            global $wp_roles;           
            ?>
        <table class="price_adjustment widefat" id="eh_pricing_discount_price_adjustment_options">
            <thead>
            <th class="sort">&nbsp;</th>
            <th><?php _e('User Role', 'elex-catmode-rolebased-price'); ?></th>
            <th style="text-align:center;"><?php _e('Users', 'elex-catmode-rolebased-price'); ?></th>
            <th style="text-align:center;"><?php _e('Categories', 'elex-catmode-rolebased-price'); ?></th>
            <th style="text-align:center;"><?php echo sprintf(__('Price Adjustment (%s)', 'elex-catmode-rolebased-price'), get_woocommerce_currency_symbol()); ?></th>
            <th style="text-align:center;"><?php _e('Price Adjustment (%)', 'elex-catmode-rolebased-price'); ?></th>
            <th style="text-align:center;"><?php _e('Enable', 'elex-catmode-rolebased-price'); ?></th>
            <th style="text-align:center;padding-right:10px;"><?php _e('Remove', 'elex-catmode-rolebased-price'); ?></th>
        </thead>
        <tbody id='elex_rp_table_body'>

            <?php
            $this->price_table = array();
            $i = 0;
            $decimal_steps = 1;
            $woo_decimal = wc_get_price_decimals();
            for ($temp=0;$temp<$woo_decimal;$temp++) {
                $decimal_steps = $decimal_steps/10;
            }
            $user_adjustment_price = ! empty( get_option( 'eh_pricing_discount_price_adjustment_options' ) ) ? get_option( 'eh_pricing_discount_price_adjustment_options', array() ) : array();
			$wordpress_roles = $wp_roles->role_names;
            $wordpress_roles['unregistered_user'] = 'Unregistered User';
            $user_role_options = '';
            foreach ($wordpress_roles as $k => $v) {
                $user_role_options.='<option value="'.$k.'" >'.$v.'</option>';
            }
            //Previously saved data
            $temp_user_adjustment = array();
            if($user_adjustment_price && is_array($user_adjustment_price)) {
                if(!empty ($user_adjustment_price) &&  array_keys($user_adjustment_price,$wordpress_roles)) {
                    foreach ($user_adjustment_price as $key => $value) {
                        if(isset($value['category']) || $value['adjustment_price'] || $value['adjustment_percent']) {
                            $value['roles'] = array($key);
                            $temp_user_adjustment[] = $value;
                            
                        }
                    }
                }
            }
            if(!empty($temp_user_adjustment)) {
                $user_adjustment_price = $temp_user_adjustment;
            }
            
            $product_category = get_terms('product_cat', array('fields' => 'id=>name', 'hide_empty' => false, 'orderby' => 'title', 'order' => 'ASC',));
            $category_options = '';
            foreach ($product_category as $k => $v) {
                $category_options.='<option value="'.$k.'" >'.$v.'</option>';
            }
            foreach ($user_adjustment_price as $key => $value) {
                ?>
                <tr>
                    <td class="sort">
                        <input type="hidden" class="order" name="eh_pricing_discount_price_adjustment_options[<?php echo $key; ?>]" value="<?php echo $key; ?>" />
                    </td>
                    <td style="width:15%;">
                        <select  data-placeholder="N/A" class="wc-enhanced-select" name="eh_pricing_discount_price_adjustment_options[<?php echo $key ?>][roles][]"  multiple="multiple" style="float: left;">
                            <?php
                            foreach ($wordpress_roles as $role_id => $role_name) {
                                if (isset($value['roles']) && is_array($value['roles']) && in_array($role_id, $value['roles'])) {
                                    echo '<option value="' . $role_id . '" selected >' . $role_name . '</option>';
                                } else {
                                    echo '<option value="' . $role_id . '" >' . $role_name . '</option>';
                                }
                            }
                            ?>

                        </select>

                    </td>
                    <td style="text-align:center;">
                        <select  data-placeholder="N/A" class="wc-customer-search" name="eh_pricing_discount_price_adjustment_options[<?php echo $key ?>][users][]" multiple="multiple" style="width: 25%;float: left;">

                            <?php
                                $user_ids = isset($value['users']) ? $value['users'] : array();  // selected user ids
                                foreach ($user_ids as $user_id) {
                                    $user = get_user_by('id', $user_id);
                                    if (is_object($user)) {
                                        echo '<option value="' . esc_attr($user_id) . '"' . selected(true, true, false) . '>'.$user->display_name.'(#'.$user->ID.') - '.$user->user_email.'</option>';
                                    }
                                }
                            ?>
                            
                        </select>
                    </td>
                    <td style="text-align:center;">
                        <select  data-placeholder="N/A" class="wc-enhanced-select" name="eh_pricing_discount_price_adjustment_options[<?php echo $key ?>][category][]"  multiple="multiple" style="width: 25%;float: left;">
                            <?php
                            foreach ($product_category as $id => $product_category_one) {
                                if (isset($value['category']) && is_array($value['category']) && in_array($id, $value['category'])) {
                                    echo '<option value="' . $id . '" selected >' . $product_category_one . '</option>';
                                } else {
                                    echo '<option value="' . $id . '" >' . $product_category_one . '</option>';
                                }
                            }
                            ?>

                        </select>
                    </td>
                    <td style="text-align:center;">
                        <?php echo get_woocommerce_currency_symbol(); ?><input type="number" style="width:50% !important;" min="0" step="<?php echo $decimal_steps ?>" name="eh_pricing_discount_price_adjustment_options[<?php echo $key; ?>][adjustment_price]" placeholder="N/A" value="<?php echo isset($value['adjustment_price']) ? $value['adjustment_price'] : ''; ?>" />
                        <?php
                        $select_price_dis = 'selected';
                        $select_price_mar = '';
                        if(isset($value['adj_percent_dis']) && $value['adj_price_dis'] == 'markup'){
                            $select_price_mar = 'selected';
                            $select_price_dis = '';
                        }
                        ?>
                        <select name="eh_pricing_discount_price_adjustment_options[<?php echo $key; ?>][adj_price_dis]"><option value="discount" <?php echo $select_price_dis ?>>D</option><option value="markup" <?php echo $select_price_mar ?>>M</option></select>
                    </td>
                    <td style="text-align:center;">
                        <input type="number" style="width:50% !important;" min="0" step="<?php echo $decimal_steps ?>" name="eh_pricing_discount_price_adjustment_options[<?php echo $key; ?>][adjustment_percent]" placeholder="N/A" value="<?php echo isset($value['adjustment_percent']) ? $value['adjustment_percent'] : ''; ?>"/>%
                        <?php
                        $select_percent_dis = 'selected';
                        $select_percent_mar = '';
                        if(isset($value['adj_percent_dis']) && $value['adj_percent_dis'] == 'markup'){
                            $select_percent_mar = 'selected';
                            $select_percent_dis = '';
                        }
                        ?>
                        <select name="eh_pricing_discount_price_adjustment_options[<?php echo $key; ?>][adj_percent_dis]"><option value="discount" <?php echo $select_percent_dis ?>>D</option><option value="markup" <?php echo $select_percent_mar ?>>M</option></select>
                    </td>
                    <td style="text-align:center; width: 5%;">
                        <label>
                            <?php $checked = (!empty($value['role_price']) ) ? true : false; ?>
                            <input type="checkbox" name="eh_pricing_discount_price_adjustment_options[<?php echo $key; ?>][role_price]" <?php checked($checked, true); ?> />
                        </label>
                    </td>
                    <td class="remove_icon" style="text-align:center; width: 5%;">
                    </td>
                </tr>
                <?php
            }
            ?>
            
        </tbody>
        <tr>
        <td></td>
        <td style="padding-bottom:20px;">
            <br>
            <button type="button" id="elex_rp_add_rule"  ><?php _e('Add Rule', 'elex-catmode-rolebased-price'); ?></button>
        </td>
        </tr>
    </table>
</td>
</tr>
<script type="text/javascript">
    jQuery("#elex_rp_table_body").on('click', '.remove_icon', function () {
        jQuery(this).closest("tr").remove();
    });
    jQuery('#elex_rp_add_rule').click( function() {
        var tbody = jQuery('.price_adjustment').find('tbody');
        var size = tbody.find('tr').size();
        var x = document.getElementsByName('eh_pricing_discount_price_adjustment_options['+size+']');
        for (i = 0; i < x.length; i++) {
            if (x[i].name ==  'eh_pricing_discount_price_adjustment_options['+size+']') {
                size++;
            }
        }
        var user_roles = '<?php echo $user_role_options; ?>';
        var categories = '<?php echo addcslashes( $category_options, "'" ); ?>';
        var decimal_steps = '<?php echo $decimal_steps ?>';
        var currency_symbol = '<?php echo get_woocommerce_currency_symbol(); ?>';
        var code = '<tr >\
                    <td class="sort"><input type="hidden" class="order" name="eh_pricing_discount_price_adjustment_options['+size+']"/></td>\
                    <td style="width: 15%;"><select id="roles_field"  data-placeholder="N/A" class="wc-enhanced-select" name="eh_pricing_discount_price_adjustment_options['+size+'][roles][]"  multiple="multiple" style="width: 25%;float: left;">' + user_roles + '</select></td>\
                    <td style="width: 15%;"><select  data-placeholder="N/A" class="wc-customer-search" name="eh_pricing_discount_price_adjustment_options['+size+'][users][]"  multiple="multiple" style="width: 25%;float: left;"></select></td>\
                    <td style="width: 15%;"><select  data-placeholder="N/A" class="wc-enhanced-select" name="eh_pricing_discount_price_adjustment_options['+size+'][category][]"  multiple="multiple" style="width: 25%;float: left;">' + categories + '</select></td>\
                    <td style="text-align:center;">'+currency_symbol+'<input type="number" style="width:50% !important;" min="0" step="'+decimal_steps+'" name="eh_pricing_discount_price_adjustment_options['+size+'][adjustment_price]" placeholder="N/A"  /> <select name="eh_pricing_discount_price_adjustment_options['+size+'][adj_price_dis]"><option value="discount">D</option><option value="markup" >M</option></select></td>\
                    <td style="text-align:center;"><input type="number" style="width:50% !important;" min="0" step="'+decimal_steps+'" name="eh_pricing_discount_price_adjustment_options['+size+'][adjustment_percent]" placeholder="N/A"/>%<select name="eh_pricing_discount_price_adjustment_options['+size+'][adj_percent_dis]"><option value="discount">D</option><option value="markup">M</option></select></td>\
                    <td style="text-align:center; width: 5%;"><label><input type="checkbox" name="eh_pricing_discount_price_adjustment_options['+size+'][role_price]" /></label></td>\
                    <td class="remove_icon" style="text-align:center; width: 5%;"></td>\
                                                     </tr>';
                    jQuery('#elex_rp_table_body').append( code );
                    jQuery('#roles_field').trigger('wc-enhanced-select-init');
                    return false;
    });


</script>

<style type="text/css">
    .price_adjustment td {
        vertical-align: middle;
        padding: 4px 7px;
    }
    .price_adjustment th {
        padding: 9px 7px;
    }
    .price_adjustment td input {
        margin-right: 4px;
    }
    .price_adjustment .check-column {
        vertical-align: middle;
        text-align: left;
        padding: 0 7px;
    }
    .woocommerce table.form-table .select2-container {
        min-width: 100px!important;
    }
    .price_adjustment th.sort {
        width: 16px;
        padding: 0 16px;
    }
    .price_adjustment td.sort {
        width: 16px;
        padding: 0 16px;
        cursor: move;
        background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAgAAAAICAYAAADED76LAAAAHUlEQVQYV2O8f//+fwY8gJGgAny6QXKETRgEVgAAXxAVsa5Xr3QAAAAASUVORK5CYII=) no-repeat center;	
        }
    table.form-table td{
        padding: 5px 10px;
    }
    .price_adjustment td.remove_icon  {
        width: 16px;
        padding: 0 16px;
        cursor: pointer;
        background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABIAAAASCAYAAABWzo5XAAAAA3NCSVQICAjb4U/gAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAAEnQAABJ0Ad5mH3gAAAAZdEVYdFNvZnR3YXJlAHd3dy5pbmtzY2FwZS5vcmeb7jwaAAAB70lEQVQ4T61UPUhbURT+7n0vLxE1vmqFEBTR6lS7uHaTYpfqopsu0qkgODh0EURwadFBEJy62a3t0BbBIm5dXdTJP/whiFr7EpUmL3m5nnNixR80EfLBXe79vu+ce865VxkCbsHf2Ud6eQWZzS1k1tZlL/y8DeFnLYh0vIDT1CB713HDKPBS8D5/xemPX8hns1COA2VbcmZyAYzvQ4dCqO7ugtvfC8uNyhnjyiibOMDByDhyngdFZKW1EG7D5PMwFMR2XcSmxhCKx2RfjIJkCol375E7IZMwZaGUHN4Hjm0yPuxaF/HZD7BqopCw3twXBH9LM2Ewh7msYS1D+zt7OP25CNh0HdqQaCUsCUca1rKHTi+vIk9FVFrR/YmUTsP8K7KYQ1zWsJY91OHHGXO29Fu6Y7k1iPa8ptwlNY55F3x1Okp9X6AuJ6WbVZ0voXYHh01w9AegbjitzYhPT1wqHkZieBT+xjYVR8OqrysUuxwo39WS3+bN8cwnWFWVhWL7GSE+CPJSTliKHZyd4+nQW+hIRzs0PYX/XVCRCFRFkcWcyy6ztuDR1IjqN6+AXFYSkWErYUnSpGEte0ix3YE+WE9cGXsetmKQoSQua1jLECN+K7HJMdhsRgPGD/M+yKMlDnNZw1pG+b+R63j8xwZcADXJQNHUd268AAAAAElFTkSuQmCC) no-repeat center
    }
    .price_adjustment #elex_rp_add_rule {
        cursor: pointer;
    }				
</style>