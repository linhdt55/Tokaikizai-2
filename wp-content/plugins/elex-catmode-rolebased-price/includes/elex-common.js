    jQuery(window).on('load',function () {
        // Ordering
        jQuery('.price_adjustment tbody').sortable({
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
                elex_rp_price_adjustment_row_indexes();
            }
        });

        elex_rp_hide_cart_placeholder_text('#eh_pricing_discount_cart_unregistered_user', '#eh_pricing_discount_cart_unregistered_user_text','#elex_unregistered_remove_addtocart_shop','#elex_unregistered_remove_addtocart_product');
        elex_rp_hide_placeholder_text('#eh_pricing_discount_price_unregistered_user', '#eh_pricing_discount_price_unregistered_user_text');
        elex_rp_hide_placeholder_text('#eh_pricing_discount_price_catalog_mode', '#eh_pricing_discount_price_catalog_mode_text');
        elex_rp_hide_addtocart_user_placeholder_text('#eh_pricing_discount_cart_user_role', '#eh_pricing_discount_cart_user_role_text');
        elex_rp_hide_user_placeholder_text('#eh_pricing_discount_price_user_role', '#eh_pricing_discount_price_user_role_text');
        elex_rp_hide_cart_placeholder_text('#eh_pricing_discount_cart_catalog_mode', '#eh_pricing_discount_cart_catalog_mode_text','#elex_catalog_remove_addtocart_shop','#elex_catalog_remove_addtocart_product');
        elex_rp_hide_user_replace_addtocart();
        elex_rp_hide_tax_options_table('#eh_pricing_discount_enable_tax_options', '#tax_options_table');
        elex_rp_replace_addtocart();
        elex_rp_replace_addtocart_catalog();
        elex_rp_price_suffix();


        jQuery('#eh_pricing_discount_cart_unregistered_user').change(function () {
            elex_rp_hide_cart_placeholder_text('#eh_pricing_discount_cart_unregistered_user', '#eh_pricing_discount_cart_unregistered_user_text','#elex_unregistered_remove_addtocart_shop','#elex_unregistered_remove_addtocart_product');
        });
        jQuery('#eh_pricing_discount_cart_catalog_mode').change(function () {
            elex_rp_hide_cart_placeholder_text('#eh_pricing_discount_cart_catalog_mode', '#eh_pricing_discount_cart_catalog_mode_text','#elex_catalog_remove_addtocart_shop','#elex_catalog_remove_addtocart_product');
        });

        jQuery('#eh_pricing_discount_price_unregistered_user').change(function () {
            elex_rp_hide_placeholder_text('#eh_pricing_discount_price_unregistered_user', '#eh_pricing_discount_price_unregistered_user_text');
        });
        jQuery('#eh_pricing_discount_price_catalog_mode').change(function () {
            elex_rp_hide_placeholder_text('#eh_pricing_discount_price_catalog_mode', '#eh_pricing_discount_price_catalog_mode_text');
        });

        jQuery('#eh_pricing_discount_cart_user_role').change(function () {
            elex_rp_hide_addtocart_user_placeholder_text('#eh_pricing_discount_cart_user_role', '#eh_pricing_discount_cart_user_role_text');
        });

        jQuery('#eh_pricing_discount_price_user_role').change(function () {
            elex_rp_hide_user_placeholder_text('#eh_pricing_discount_price_user_role', '#eh_pricing_discount_price_user_role_text');
        });

        jQuery('#eh_pricing_discount_replace_cart_user_role').change(function () {
            elex_rp_hide_user_replace_addtocart();
        });

        jQuery('#eh_pricing_discount_enable_tax_options').change(function () {
            elex_rp_hide_tax_options_table('#eh_pricing_discount_enable_tax_options', '#tax_options_table');
        });

        jQuery('#eh_pricing_discount_replace_cart_unregistered_user').change(function () {
            elex_rp_replace_addtocart();
        });
        jQuery('#eh_pricing_discount_replace_cart_catalog_mode').change(function () {
            elex_rp_replace_addtocart_catalog();
        });

        jQuery('#eh_pricing_discount_enable_price_suffix').change(function () {
            elex_rp_price_suffix();
        });


        function elex_rp_price_adjustment_row_indexes() {
            jQuery('.price_adjustment tbody tr').each(function (index, el) {
                jQuery('input.order', el).val(parseInt(jQuery(el).index('.price_adjustment tr')));
            });
        }
        ;

        function elex_rp_hide_placeholder_text(check, hide_field) {
            if (jQuery(check).is(":checked")) {
                jQuery(hide_field).closest("tr").show();
            } else {
                jQuery(hide_field).closest("tr").hide();
            }
        }
        ;

        function elex_rp_hide_cart_placeholder_text(check, hide_field1, hide_field2, hide_field3) {
            if (jQuery(check).is(":checked")) {
                jQuery(hide_field1).closest("tr").show();
                jQuery(hide_field2).closest("tr").show();
                jQuery(hide_field3).closest("tr").show();

            } else {
                jQuery(hide_field1).closest("tr").hide();
                jQuery(hide_field2).closest("tr").hide();
                jQuery(hide_field3).closest("tr").hide();

            }
        }
        ;

    function elex_rp_hide_user_placeholder_text(check, hide_field) {
        options = jQuery(check).val();
        if (options != null) {
            jQuery(hide_field).closest("tr").show();
        } else {
            jQuery(hide_field).closest("tr").hide();
        }
    }
    ;
    function elex_rp_hide_addtocart_user_placeholder_text(check, hide_field) {
        options = jQuery(check).val();
        if (options != null) {
            jQuery(hide_field).closest("tr").show();
            jQuery('#elex_user_role_remove_addtocart_shop').closest("tr").show();
            jQuery('#elex_user_role_remove_addtocart_product').closest("tr").show();
        } else {
            jQuery(hide_field).closest("tr").hide();
            jQuery('#elex_user_role_remove_addtocart_shop').closest("tr").hide();
            jQuery('#elex_user_role_remove_addtocart_product').closest("tr").hide();
        }
    }
    ;

        //ajax call to save the user role   
        jQuery('#elex_rp_pricing_discount_add_user_role').click(function(){
            var userRole  = jQuery('#eh_woocommerce_pricing_discount_user_role_name').val();
            var userDesc  = jQuery('#eh_woocommerce_pricing_discount_user_role_descp').val(); 
        var saveUserRoleAction = jQuery.ajax({
            type : 'post',
            url  : ajaxurl,
            data : {
            action : 'elex_rp_pricing_discount_add_user_role',
            user_role : userRole,
            user_desc : userDesc
            },
        });
        saveUserRoleAction.done(function(response){
                if(saveUserRoleAction.status == 200){
                    alert('Custom user role has been successfully created ');
                }else{
                    alert('Please Check the user role and try again');
                }
            });

    });
        var count = 0; 
        //user role to show the view
         jQuery('.hndle_delete_user_role').click(function(){
            elex_delete_role();      
        });

         //function to show all user roles
         function elex_delete_role(){
            jQuery('.eh-loading').show();
            jQuery('.update_value_user_role').hide();
            //jQuery('#hndle_tb_delete_user_role').slideDown('slow');
            if(count >= 1){
                jQuery('.show_roles'+count).remove();
                jQuery('.delete_icon'+count).remove()
            }
            var deleteUserRoleAction = jQuery.ajax({
            type : 'post',
            url  : ajaxurl,
            data : {
            action : 'elex_rp_ajax_pricing_discount_show_user_role',
            },
            dataType: 'json',
        });
         deleteUserRoleAction.done(function(response){
            jQuery('.eh-loading').hide();
            jQuery('.update_value_user_role').slideDown('slow');
            count++; 
           var available_roles         = response.available_roles;
           var edit_url                = response.edit_url;
           window.modify_url           = response.modify_url;
           for (var i = 0; i < available_roles.length; i++) {
                                    
                                    tr = jQuery('<tr class="show_roles'+count+'">');
                                    var service_label_cost = available_roles[i];
                                    for(let key in service_label_cost){
                                        let val = service_label_cost[key];

                                        tr.append('<td><input type= "checkbox" class= "delete_check_box" id= delete_check_'+val['user_role']+' value= '+val['user_role']+'></input></td>')
                                        tr.append("<td class= 'user_role_catmode_elex' id ="+val['user_role']+" value = "+val['user_role']+"><small>" + val['user_role_name'] + "</small></td>");
                                        tr.append('<td class = "user_desc_catmode_elex" id = user_desc_'+val['user_role']+' ><small> ' + val['user_role_desc'] +'</small></td>');
                                        tr.append("<td><img src= "+ edit_url +" title = 'Edit' class = edit_user_role id = edit_"+ val['user_role'] +" data-value = "+val['user_role']+" style=height:15px; >");

                                    }
                                    tr.append('</tr>');
                                     jQuery('.delete_user_role').find('table').append(tr);
                                }
                                td = jQuery('<tr class = "delete_icon'+count+'">');
                                td.append('<td> <button type="button" class = "update_user_roles" style = height:30px;width:100px;font-size:13px><b> Delete Roles</b> </button>');
                                td.append('</tr>');
                                jQuery('.delete_user_role').find('table').append(td);

                         });
             deleteUserRoleAction.fail(function(jqXHR, textStatus){
                jQuery('.eh-loading').hide();
                alert('Please select the roles and try again');
             });
         }



         //Edit the User role and User role Description
         jQuery(".delete_user_role").find('table').on("click", "img.edit_user_role" , function(event) {
            var user_role_id =  this.getAttribute('data-value');
            var modify   = window.modify_url;
            jQuery('#user_desc_'+user_role_id).attr('contenteditable','true');
            jQuery('#user_desc_'+user_role_id).css('padding:4px');
            jQuery('#user_desc_'+user_role_id).focus();
            jQuery('#edit_'+user_role_id).hide();
             var td = document.getElementById('edit_'+user_role_id);
            var th = "<td><img src= "+ modify +" title = 'Save' class = modify_user_role id = edit_"+ user_role_id +" data-value = "+user_role_id+" style=height:15px; >";
            td.insertAdjacentHTML('afterend',th);
        });

         //Make text field as non-editable 
        jQuery(".delete_user_role").find('table').focusout('.user_desc_catmode_elex' , function() {
            jQuery('.edit_user_role').show();
            jQuery('.user_desc_catmode_elex').attr('contenteditable','false');
            jQuery('.modify_user_role').remove();
        });
    
        //Delete User Roles
         jQuery(".delete_user_role").find('table').on("click", "button.update_user_roles" , function() {
                    var delUserRole = [];
                    var user_desc = [];
                  
                    jQuery('.user_role_catmode_elex').each(function(index){
                        var user_role_val = this.getAttribute('value');
                        if(jQuery('#delete_check_'+user_role_val).prop('checked')== true){
                         delUserRole.push(this.getAttribute('value'));
                        }
                    });

                    if(delUserRole.length !=0){
                        if( confirm( 'Are you sure you want to delete the selected user roles?' )){
                            var checker=jQuery.ajax({
                                type : 'post',
                                url  : ajaxurl,
                                data : {
                                action : 'elex_rp_ajax_pricing_discount_delete_user_role',
                                    del_user_role : delUserRole,
                                },
                               
                            });
                            checker.done(function(response){
                                elex_delete_role();
                                
                            });
                            checker.fail(function(response){
                                alert('Please select atleast one role to delete.');
                            });
                        }
                    }else{
                        alert('Please select atleast one user role to delete.');
                    }
                
                    
         });



         //Update the values of user-role description  
         jQuery(".delete_user_role").find('table').on("mousedown", "img.modify_user_role" , function() {
             var update_user_value = { };
             var user_role_val = this.getAttribute('data-value');
                        var user_role_vals = String(user_role_val);
                        var text = jQuery('#user_desc_'+user_role_vals).text();
                        update_user_value[user_role_vals] = text;
            var checker=jQuery.ajax({
                            type : 'post',
                            url  : ajaxurl,
                            data : {
                            action : 'elex_rp_ajax_pricing_discount_update_user_role',
                               updated_user_role :update_user_value
                            },
                            
                        });
                        checker.done(function(response){
                            if(checker.status == 200){
                                elex_delete_role();
                            }else{
                                alert('Please modify atleast any one roles to be updated');
                            }
                        });
                        checker.fail(function(response){
                            alert('Please select atleast any one roles to be updated');
                        });
            
         });
        function elex_rp_hide_user_replace_addtocart() {
            options = jQuery('#eh_pricing_discount_replace_cart_user_role').val();
            if (options != null) {
                jQuery('#eh_pricing_discount_replace_cart_user_role_text_product').closest("tr").show();
                jQuery('#eh_pricing_discount_replace_cart_user_role_text_shop').closest("tr").show();
                jQuery('#eh_pricing_discount_replace_cart_user_role_url_shop').closest("tr").show();
            } else {
                jQuery('#eh_pricing_discount_replace_cart_user_role_text_product').closest("tr").hide();
                jQuery('#eh_pricing_discount_replace_cart_user_role_text_shop').closest("tr").hide();
                jQuery('#eh_pricing_discount_replace_cart_user_role_url_shop').closest("tr").hide();
            }
        }
        ;
        function elex_rp_hide_tax_options_table(check, hide_field) {
            if (jQuery(check).is(":checked")) {
                jQuery(hide_field).show();
            } else {
                jQuery(hide_field).hide();
            }
        }
        ;
        //---------------------------edited by nandana
        //To show/hide placeholder text and url for replace add to cart button for unregistered user
        function elex_rp_replace_addtocart() {
            if (jQuery('#eh_pricing_discount_replace_cart_unregistered_user').is(":checked")) {
                jQuery('#eh_pricing_discount_replace_cart_unregistered_user_text_shop').closest("tr").show();
                jQuery('#eh_pricing_discount_replace_cart_unregistered_user_url_shop').closest("tr").show();
                jQuery('#eh_pricing_discount_replace_cart_unregistered_user_text_product').closest("tr").show();
            } else {
                jQuery('#eh_pricing_discount_replace_cart_unregistered_user_text_shop').closest("tr").hide();
                jQuery('#eh_pricing_discount_replace_cart_unregistered_user_url_shop').closest("tr").hide();
                jQuery('#eh_pricing_discount_replace_cart_unregistered_user_text_product').closest("tr").hide();
            }
        }
        ;
         
        //To show/hide placeholder text and url for replace add to cart button for Catalog mode
        function elex_rp_replace_addtocart_catalog() {
            if (jQuery('#eh_pricing_discount_replace_cart_catalog_mode').is(":checked")) {
                jQuery('#eh_pricing_discount_replace_cart_catalog_mode_text_shop').closest("tr").show();
                jQuery('#eh_pricing_discount_replace_cart_catalog_mode_url_shop').closest("tr").show();
                jQuery('#eh_pricing_discount_replace_cart_catalog_mode_text_product').closest("tr").show();
            } else {
                jQuery('#eh_pricing_discount_replace_cart_catalog_mode_text_shop').closest("tr").hide();
                jQuery('#eh_pricing_discount_replace_cart_catalog_mode_url_shop').closest("tr").hide();
                jQuery('#eh_pricing_discount_replace_cart_catalog_mode_text_product').closest("tr").hide();
            }
        }
        ;
        //----------------------------
        function elex_rp_price_suffix() {
            options = jQuery('#eh_pricing_discount_enable_price_suffix').val();
            if (options == 'general') {
                jQuery('#eh_pricing_discount_price_general_price_suffix').closest("tr").show();
                jQuery('#price_suffix_table').hide();
            } else if (options == 'role_specific') {
                jQuery('#eh_pricing_discount_price_general_price_suffix').closest("tr").hide();
                jQuery('#price_suffix_table').show();
            } else {
                jQuery('#eh_pricing_discount_price_general_price_suffix').closest("tr").hide();
                jQuery('#price_suffix_table').hide();
            }
        }
        ;

    });


