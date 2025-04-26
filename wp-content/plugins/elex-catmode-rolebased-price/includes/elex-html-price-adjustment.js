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


