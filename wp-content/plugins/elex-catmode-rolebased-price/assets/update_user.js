jQuery(window).on('load',function () {//ajax call to set session to unregistered user when page reloads
	var userRole  = '';
	var saveUserRoleAction =  jQuery.ajax({
		type : 'post',
		url  : elex_rp_add_user_role_checkout.ajax_url,
		data : {
		action : 'elex_rp_add_user_role',
		_ajax_nonce: elex_rp_add_user_role_checkout.nonce,
		user_role : userRole,
		},
	});
		jQuery(document.body).trigger("update_checkout");
});
	
	//ajax call to save the user role 
	jQuery(document).ready(function(){
	jQuery('#afreg_select_user_role').on( 'change', function() { 	
		var userRole  = jQuery(this).val();
		var saveUserRoleAction =  jQuery.ajax({
			type : 'post',
			url  : elex_rp_add_user_role_checkout.ajax_url,
			data : {
			action : 'elex_rp_add_user_role',
			_ajax_nonce: elex_rp_add_user_role_checkout.nonce,
			user_role : userRole,
			},
			
		});
		 jQuery(document.body).trigger("update_checkout");

	});
});