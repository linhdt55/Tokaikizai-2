<?php
// to check whether accessed directly
if (!defined('ABSPATH')) {
	exit;
}
?>

<div class="wrap" style="padding-left: 25px;width: 70%;">
   <div id="content">
	 <input type="hidden" id="pricing_discount_manage_user_roles" name="pricing_discount_manage_user_roles" value="add_user_role">
	 <div id="poststuff">
		<div class="postbox">
		   <h3 class="hndle_add_user_role" style="cursor:pointer;padding-left: 15px;padding-bottom: 15px;border-bottom: solid 1.5px black;color: #5b9dd9;"><?php
			  _e('Add Custom User Role', 'elex-catmode-rolebased-price'); ?></h3>
		   <div class="add_user_role" style="border-bottom: solid 1.5px black;">
			  <table class="form-table">
				 <tr>
					<th style="padding: 15px;">
					   <label for="eh_woocommerce_pricing_discount_user_role_name"><b><?php
						  _e('Role Name', 'elex-catmode-rolebased-price'); ?></b></label>
					</th>
					<td>
					   <input type="text" name="eh_woocommerce_pricing_discount_user_role_name" id = "eh_woocommerce_pricing_discount_user_role_name" class="regular-text" value= ><br />
					   <span class="description"><?php
						  _e('Enter the name of the user role you want to create and click the Save Role button.', 'elex-catmode-rolebased-price');
						  ?></span>
					</td>
				 </tr>
				 <tr>
				 	<th style="padding: 15px;">
					   <label for="eh_woocommerce_pricing_discount_user_role_descp"><b><?php
						  _e('Description', 'elex-catmode-rolebased-price'); ?></b></label>
					</th>
					<td>
					   <input type="text" name="eh_woocommerce_pricing_discount_user_role_descp" id="eh_woocommerce_pricing_discount_user_role_descp" class="regular-text" value= ><br />
					   <span class="description"><?php
						  _e('Enter the description to the user role you want to create and click the Save Role button.', 'elex-catmode-rolebased-price');
						  ?></span>
					</td>
				 </tr>
				 <tr>
				 	<td>
				 		<button type="button" id="elex_rp_pricing_discount_add_user_role"  ><?php _e('<b>Save Role</b>', 'elex-catmode-rolebased-price'); ?></button>
				 	</td>
				 </tr>
			  </table>
		   </div>
		   <h3 class="hndle_delete_user_role" style="cursor:pointer;padding-left: 15px;color: #5b9dd9;"><?php
			  _e('Modify User Role', 'elex-catmode-rolebased-price'); ?></h3>
				 <img src=" <?php echo untrailingslashit(plugins_url()).'/elex-catmode-rolebased-price/resources/loading.gif'; ?>"  align="center" style="padding-left:350px;height:100px" id="eh-loading" class="eh-loading">
		   <div class="delete_user_role" style="border-top: solid 1.5px black;">
			  <table class="form-table" id = 'hndle_tb_delete_user_role'>
			  	<tr class = "update_value_user_role" id = "update_value_user_role">
			  		<th> &nbsp;&nbsp;Select</th>
			  		<th> &nbsp;&nbsp;&nbsp;Name</th>
			  		<th>&nbsp;&nbsp;Description</th>
			  		<th>Action</th>
			  		<td>
                    
			  	</tr>
			</table>
		   </div>
		</div>
	 </div>
   </div>
</div>
<div id = 'dialog' title="Delete Role">
</div>
<script>
jQuery(document).ready(function(){
	jQuery('.eh-loading').hide();
	jQuery('.hndle_add_user_role').click(function(){
		elex_rp_pricing_discount_manage_role('add_user_role');
	});
	jQuery('.hndle_delete_user_role').click(function(){
		elex_rp_pricing_discount_manage_role('delete_user_role');
	});
        jQuery('.delete_user_role').hide();
        jQuery('.add_user_role').hide();
});
function elex_rp_pricing_discount_manage_role(manage_role){
	switch(manage_role){
		case 'add_user_role':
			jQuery('.add_user_role').slideDown('slow');
			jQuery('.delete_user_role').hide();
			jQuery('#pricing_discount_manage_user_roles').val('add_user_role');
			jQuery('.eh-loading').hide();
			jQuery("input[name='save']").val('Add User Role');
			break;
		case 'delete_user_role':
			jQuery('.add_user_role').slideUp('fast');
			jQuery('.delete_user_role').show();
			jQuery('.eh-loading').hide();
			jQuery('#pricing_discount_manage_user_roles').val('remove_user_role');
			jQuery("input[name='save']").val('Delete User Role');
			break;
	}
}
</script>