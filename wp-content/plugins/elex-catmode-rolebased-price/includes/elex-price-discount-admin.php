<?php
// to check whether accessed directly
if (!defined('ABSPATH')) {
	exit;
}


class Elex_Price_Discount_Admin {

    public function __construct($execute = true) {

        $this->sales_method = get_option('eh_product_choose_sale_regular', 'sale');
        $this->is_markup = false;
        if ($execute == true) {
            $this->elex_rp_add_filter_for_get_price();

            if (WC()->version < '2.7.0') {
                add_filter('woocommerce_product_tax_class', array($this, 'elex_rp_product_tax_class'), 99, 1);
            } else {
                add_filter('woocommerce_product_get_tax_class', array($this, 'elex_rp_product_tax_class'), 99, 1);
                add_filter('woocommerce_product_variation_get_tax_class', array($this, 'elex_rp_product_tax_class'), 99, 1);
            }
            add_action('woocommerce_single_product_summary', array($this, 'elex_rp_product_page_remove_add_to_cart_option')); //function to remove add to cart at product page
            
            add_filter('woocommerce_loop_add_to_cart_link', array($this, 'elex_rp_shop_remove_add_to_cart'), 100, 2); // function to remove add to cart from shop page
           // add_action('wp_head', array($this, 'custom_css_for_add_to_cart'));

		   //Custom registration plugin
		   add_action('wp_ajax_nopriv_elex_rp_add_user_role',array($this,'elex_rp_add_user_role'));// function to add user role for unregistered user 

            add_filter('woocommerce_is_purchasable', array(&$this, 'elex_rp_is_product_purchasable'), 10, 2); //to hide add to cart button when price is hidden
            add_filter('woocommerce_loop_add_to_cart_link', array($this, 'elex_rp_add_to_cart_text_url_replace'), 1, 2); //to replace add to cart with user defined url
            add_filter('woocommerce_product_single_add_to_cart_text', array($this, 'elex_rp_add_to_cart_text_content_replace'), 1, 1); //to replace add to cart with user defined placeholder text for product page
            add_filter('woocommerce_get_price_html', array($this, 'elex_rp_get_price_html'), 99, 2); //to modify display for various options of settings page
            //-----for tax
            add_filter('pre_option_woocommerce_tax_display_shop', array($this, 'elex_rp_override_tax_display_setting_in'));
            add_filter('pre_option_woocommerce_tax_display_cart', array($this, 'elex_rp_override_tax_display_setting_in'));
            add_filter('pre_option_woocommerce_tax_display_checkout', array($this, 'elex_rp_override_tax_display_setting_in'));

            if (WC()->version > '3.1') {
                add_filter('woocommerce_cart_subtotal', array($this, 'elex_rp_get_cart_subtotal'), 100, 1);
                add_filter('woocommerce_cart_product_price', array($this, 'elex_rp_get_product_price'), 100, 2);
                add_filter('woocommerce_cart_product_subtotal', array($this, 'elex_rp_get_product_subtotal'), 100, 3);
            }
			//TM-product compatibility
			if ( in_array( 'woocommerce-tm-extra-product-options/tm-woo-extra-product-options.php', apply_filters( 'active_plugins', get_option('active_plugins' ) ) ) ) {
			   add_action( 'woocommerce_before_calculate_totals', array( $this,'elexr_rp_after_calculate_totals' ), 999,1 );
			   add_filter( 'elex_rp_tm_extra_option_individual_discount', array( $this, 'elex_rp_get_adjustment_for_individual_products' ), 99, 3 );
			   add_filter( 'elex_rp_tm_extra_option_global_discount', array( $this, 'elex_rp_get_adjustment_amount' ), 99, 4 );

		   }
		   $this->addon_check = false;
		   	if ( in_array( 'elex-user-price-adjustment-addon/elex-user-price-adjustment-addon.php', apply_filters( 'active_plugins', get_option('active_plugins' ) ) ) ) {
				$this->addon_check = true;
			}
            //------------
            add_filter('woocommerce_product_is_on_sale', array($this, 'elex_rp_product_is_on_sale'), 99, 2);
            add_filter('woocommerce_product_add_to_cart_text', array($this, 'elex_rp_view_product_text'), 99, 2);
            //add_filter('woocommerce_product_is_visible', array($this, 'get_product_visibility'), 100, 2);
			add_filter('woocommerce_product_query_meta_query', array($this, "elex_rp_product_query_meta_query"), 100,2);         
			add_filter('woocommerce_product_get_children', array($this, 'elex_rp_get_product_under_grouped_visibility'), 100, 2);
        }
        //----for price filter
        add_filter('woocommerce_price_filter_widget_min_amount', array($this, 'elex_rp_get_min_price'), 100, 1);
        add_filter('woocommerce_price_filter_widget_max_amount', array($this, 'elex_rp_get_max_price'), 100, 1);
        //----------
        add_action( 'wp', array( $this, 'elex_rp_hide_cart_checkout_pages' ) );
        $this->init_fields();
    }

	public function elex_rp_add_user_role() {
		global $woocommerce;
		if(isset($_POST['user_role'])){
			$woocommerce->session->set( 'elex_afreg_select_user_role', $_POST['user_role']);
		}
		die ;
	}

	public function elexr_rp_after_calculate_totals($cart_object){
        global $woocommerce;
		if(is_cart()||is_checkout()){
        $cart_details = $woocommerce->cart->cart_contents;
        $amount = 0;
        $tax_amt = 0;
        foreach ($cart_details as $val) {
			$product_id = $val['product_id'];
			$product = wc_get_product($product_id);
			if(!empty($val['variation_id'])){
				$product_id = $val['variation_id'];
			}
			if ($product->is_type( 'simple' )) {
				$discounted_price     =  $product->get_price();
			}
			elseif($product->is_type('variable')){
				$product = new WC_Product_Variation($product_id);
			 	$discounted_price    =  $product->get_price();
			}
			$temp_amt = 0;
			$temp_option_amt  = 0;
            if (isset($val['tmcartepo']) && is_array($val['tmcartepo']) && !empty($val['tmcartepo'])) {
				foreach($val['tmcartepo'] as $key => $tm_cart_price){
					if(is_numeric($val['tmcartepo'][$key]['price'])){
						$temp_option_amt += $val['tmcartepo'][$key]['price'] ;
					}
				}
              $temp_amt = $temp_option_amt + $discounted_price;
			  $val['data']->set_price($temp_amt);
            }else{
				$val['data']->set_price($discounted_price);	
			}
	
        }

		$this->elex_rp_remove_filter_for_get_price();			
		}
    }

    public function elex_rp_hide_cart_checkout_pages() {
        $hide = FALSE;
        if ('yes' == get_option('eh_pricing_discount_cart_catalog_mode_remove_cart_checkout')) {
            if(! (get_option('eh_pricing_discount_price_catalog_mode_exclude_admin') == 'yes' && $this->current_user_role =='administrator')) {
                $hide = TRUE;
            }
        }
        elseif (is_user_logged_in()) {
            $remove_settings_cart_roles = get_option('eh_pricing_discount_cart_user_role_remove_cart_checkout');
            if (is_array($remove_settings_cart_roles) && in_array($this->current_user_role, $remove_settings_cart_roles)) {
                $hide = TRUE;
            }
        }
        else {
            if ('yes' == get_option('eh_pricing_discount_cart_unregistered_user_remove_cart_checkout')) {
                $hide = TRUE;
            }
        }
            $cart     = is_page( wc_get_page_id( 'cart' ) );
            $checkout = is_page( wc_get_page_id( 'checkout' ) );

            wp_reset_query();
            if ( $hide && ($cart || $checkout) ) {

                    wp_redirect( home_url() );
                    exit;
            }
    }


    
    
    public function elex_rp_shop_remove_add_to_cart($args, $product) {
        $product_id = $this->elex_rp_get_product_id($product);
        $add_to_cart_link = $args;
        if (('yes' == get_post_meta($product_id, 'product_adjustment_hide_addtocart_catalog', true)) && ('yes' == get_post_meta($product_id, 'product_adjustment_hide_addtocart_catalog_shop', true) || '' == get_post_meta($product_id, 'product_adjustment_hide_addtocart_catalog_shop', true))) {
            if(! (get_post_meta($product_id, 'product_adjustment_exclude_admin_catalog', true) == 'yes' && $this->current_user_role =='administrator')) {
                $add_to_cart_link = '';
                $place_holder = get_post_meta($product_id, 'product_adjustment_hide_addtocart_placeholder_catalog', true);
                if(!empty($place_holder)) {
                    $place_holder = $this->elex_rp_return_wpml_string($place_holder, 'Remove Add-to-cart - Shop');
                    echo $place_holder;
                }
            }
        }
        
         elseif ('yes' == get_option('eh_pricing_discount_cart_catalog_mode') && 'yes' == get_option('elex_catalog_remove_addtocart_shop')) {
            
            if(! (get_option('eh_pricing_discount_price_catalog_mode_exclude_admin') == 'yes' && $this->current_user_role =='administrator')) {
                $add_to_cart_link = '';
                $place_holder = get_option('eh_pricing_discount_cart_catalog_mode_text');
                if(!empty($place_holder)) {
                    $place_holder = $this->elex_rp_return_wpml_string($place_holder, 'Remove Add-to-cart - Shop');
                   
                    echo $place_holder;
                }
            }
        }
      
        
        elseif (is_user_logged_in()) {
            $remove_settings_cart_roles = get_option('eh_pricing_discount_cart_user_role');
            $remove_product_cart_roles = get_post_meta($product_id, 'eh_pricing_adjustment_product_addtocart_user_role', true);
            if (is_array($remove_product_cart_roles) && in_array($this->current_user_role, $remove_product_cart_roles) && (('yes' == (get_post_meta($product_id, 'product_adjustment_hide_addtocart_user_role_shop', true))) || ('' == (get_post_meta($product_id, 'product_adjustment_hide_addtocart_user_role_shop', true))))) {               
                $add_to_cart_link = '';
                $this->elex_rp_get_add_to_cart_product_placeholder_text($product_id);
            } elseif (is_array($remove_settings_cart_roles) && in_array($this->current_user_role, $remove_settings_cart_roles) && 'yes' == get_option('elex_user_role_remove_addtocart_shop')) {
                $add_to_cart_link = '';
                $this->elex_rp_get_add_to_cart_placeholder_text();
            }
        } else {
            if (('yes' == (get_post_meta($product_id, 'product_adjustment_hide_addtocart_unregistered', true))) && (('yes' == (get_post_meta($product_id, 'product_adjustment_hide_addtocart_unregistered_shop', true))) || ('' == (get_post_meta($product_id, 'product_adjustment_hide_addtocart_unregistered_shop', true))))) {
                $add_to_cart_link = '';
                $this->elex_rp_get_add_to_cart_product_placeholder_text($product_id);
            } elseif ('yes' == get_option('eh_pricing_discount_cart_unregistered_user') && 'yes' == get_option('elex_unregistered_remove_addtocart_shop')) {
                $add_to_cart_link = '';
                $this->elex_rp_get_add_to_cart_placeholder_text();
            }
        }
        
        return $add_to_cart_link;
    }

    public function elex_rp_get_product_subtotal($product_subtotal, $product = '', $quantity = '') {
        $price = $product->get_price();
        if ($product->is_taxable()) {
            if ($this->enable_role_tax) {
                if(is_user_logged_in() || (ELEX_CUSTOM_REGISTRATION_STATUS && !(is_user_logged_in()))) {
                    $user_role = $this->current_user_role;
                } else {
                    $user_role = 'unregistered_user';
                }
                //checks if we have to show price including tax or excluding tax on cart page
                if (!empty($this->role_tax_option[$user_role]) && !empty($this->role_tax_option[$user_role]['tax_option'])) {
                    switch ($this->role_tax_option[$user_role]['tax_option']) {
                        case 'show_price_including_tax':
                            $row_price = wc_get_price_including_tax($product, array('qty' => $quantity));
                            $product_subtotal = wc_price($row_price);


                            $product_subtotal .= ' <small class="tax_label">' . WC()->countries->inc_tax_or_vat() . '</small>';

                            break;
                        case 'show_price_excluding_tax':
                            $row_price = wc_get_price_excluding_tax($product, array('qty' => $quantity));
                            $product_subtotal = wc_price($row_price);


                            $product_subtotal .= ' <small class="tax_label">' . WC()->countries->ex_tax_or_vat() . '</small>';

                            break;
                        case 'show_price_including_tax_shop':
                            $row_price = wc_get_price_excluding_tax($product, array('qty' => $quantity));
                            $product_subtotal = wc_price($row_price);


                            $product_subtotal .= ' <small class="tax_label">' . WC()->countries->ex_tax_or_vat() . '</small>';

                            break;
                        case 'show_price_including_tax_cart_checkout':
                            $row_price = wc_get_price_including_tax($product, array('qty' => $quantity));
                            $product_subtotal = wc_price($row_price);


                            $product_subtotal .= ' <small class="tax_label">' . WC()->countries->inc_tax_or_vat() . '</small>';

                            break;
                        case 'show_price_excluding_tax_shop':
                            $row_price = wc_get_price_including_tax($product, array('qty' => $quantity));
                            $product_subtotal = wc_price($row_price);


                            $product_subtotal .= ' <small class="tax_label">' . WC()->countries->inc_tax_or_vat() . '</small>';

                            break;
                        case 'show_price_excluding_tax_cart_checkout':
                            $row_price = wc_get_price_excluding_tax($product, array('qty' => $quantity));
                            $product_subtotal = wc_price($row_price);


                            $product_subtotal .= ' <small class="tax_label">' . WC()->countries->ex_tax_or_vat() . '</small>';

                            break;
                        case 'default':
                            break;
                    }
                }
            }
        } else {
            $row_price = $price * $quantity;
            $product_subtotal = wc_price($row_price);
        }
        return $product_subtotal;
    }

    public function elex_rp_get_product_price($product_price, $product) {        
        if ($this->enable_role_tax) { //to make dependent on enable tax checkbox
			if(is_user_logged_in() || (ELEX_CUSTOM_REGISTRATION_STATUS && !(is_user_logged_in()))) {
                $user_role = $this->current_user_role;
            } else {
                $user_role = 'unregistered_user';
            }
            //checks if we have to show price including tax or excluding tax on cart page
            if (!empty($this->role_tax_option[$user_role]) && !empty($this->role_tax_option[$user_role]['tax_option'])) {
                switch ($this->role_tax_option[$user_role]['tax_option']) {
                    case 'show_price_including_tax':
                        $product_price = wc_get_price_including_tax($product);
                        $product_price = wc_price($product_price);
                        break;
                    case 'show_price_excluding_tax':
                        $product_price = wc_get_price_excluding_tax($product);
                        $product_price = wc_price($product_price);
                        break;
                    case 'show_price_including_tax_shop':
                        $product_price = wc_get_price_excluding_tax($product);
                        $product_price = wc_price($product_price);
                        break;
                    case 'show_price_including_tax_cart_checkout':
                        $product_price = wc_get_price_including_tax($product);
                        $product_price = wc_price($product_price);
                        break;
                    case 'show_price_excluding_tax_shop':
                        $product_price = wc_get_price_including_tax($product);
                        $product_price = wc_price($product_price);
                        break;
                    case 'show_price_excluding_tax_cart_checkout':
                        $product_price = wc_get_price_excluding_tax($product);
                        $product_price = wc_price($product_price);
                        break;
                    case 'default':
                        break;
                }
            }
        }
        return $product_price;
    }

    public function elex_rp_get_cart_subtotal($cart_subtotal) {
        if ($this->enable_role_tax) { //to make dependent on enable tax checkbox
            //checks if we have to show price including tax or excluding tax on specific page
			if(is_user_logged_in() || (ELEX_CUSTOM_REGISTRATION_STATUS && !(is_user_logged_in()))) {
                $user_role = $this->current_user_role;
            } else {
                $user_role = 'unregistered_user';
            }

            if (!empty($this->role_tax_option[$user_role]) && !empty($this->role_tax_option[$user_role]['tax_option'])) {
                switch ($this->role_tax_option[$user_role]['tax_option']) {
                    case 'show_price_including_tax':
                        $cart_subtotal = wc_price(WC()->cart->cart_contents_total + WC()->cart->tax_total);
                        $cart_subtotal .= ' <small class="tax_label">' . WC()->countries->inc_tax_or_vat() . '</small>';
                        break;
                    case 'show_price_excluding_tax':
                        $cart_subtotal = wc_price(WC()->cart->cart_contents_total);
                        $cart_subtotal .= ' <small class="tax_label">' . WC()->countries->ex_tax_or_vat() . '</small>';
                        break;
                    case 'show_price_including_tax_shop':
                        $cart_subtotal = wc_price(WC()->cart->cart_contents_total);
                        $cart_subtotal .= ' <small class="tax_label">' . WC()->countries->ex_tax_or_vat() . '</small>';
                        break;
                    case 'show_price_including_tax_cart_checkout':
                        $cart_subtotal = wc_price(WC()->cart->cart_contents_total + WC()->cart->tax_total);
                        $cart_subtotal .= ' <small class="tax_label">' . WC()->countries->inc_tax_or_vat() . '</small>';
                        break;
                    case 'show_price_excluding_tax_shop':
                        $cart_subtotal = wc_price(WC()->cart->cart_contents_total + WC()->cart->tax_total);
                        $cart_subtotal .= ' <small class="tax_label">' . WC()->countries->inc_tax_or_vat() . '</small>';
                        break;
                    case 'show_price_excluding_tax_cart_checkout':
                        $cart_subtotal = wc_price(WC()->cart->cart_contents_total);
                        $cart_subtotal .= ' <small class="tax_label">' . WC()->countries->ex_tax_or_vat() . '</small>';
                        break;
                    case 'default':
                        break;
                }
            }
        }return $cart_subtotal;
    }

	public function elex_rp_variable_product_amount() {
		global $wpdb;
		$table_name = $wpdb->prefix;
		$max_amount_query = "SELECT DISTINCT ID FROM {$table_name}posts LEFT JOIN {$table_name}term_relationships on {$table_name}term_relationships.object_id={$table_name}posts.ID LEFT JOIN {$table_name}term_taxonomy on {$table_name}term_taxonomy.term_taxonomy_id  = {$table_name}term_relationships.term_taxonomy_id LEFT JOIN {$table_name}terms on {$table_name}terms.term_id={$table_name}term_taxonomy.term_id LEFT JOIN {$table_name}postmeta on {$table_name}postmeta.post_id={$table_name}posts.ID WHERE taxonomy='product_type'  AND slug  IN ('variable') AND post_status = 'publish'";
		$all_product_data = $wpdb->get_results( ( $wpdb->prepare( '%1s', $max_amount_query ) ? stripslashes( $wpdb->prepare( '%1s', $max_amount_query ) ) : $wpdb->prepare( '%s', '' ) ), ARRAY_A );           
		$max_prices = array();
		for ( $i = 0; $i < count( $all_product_data ); $i++ ) {
			$p_id = $all_product_data[ $i ]['ID'];
			$product_data = wc_get_product( $p_id );
			if ( $product_data->is_type( 'variable' ) ) {
				$prices = $product_data->get_variation_prices( true );
				if ( empty( $prices['price'] ) ) {
					continue;
				}
				foreach ( $prices['price'] as $pid => $old_price ) {
					$pobj = wc_get_product( $pid );
					$prices['price'][ $pid ] = wc_get_price_to_display( $pobj );
				}
				$max_prices[ $i ] = $prices['price'];
			}
		}
		return $max_prices;
	}

   public function elex_rp_get_min_price( $price ) {
		$user_roles = get_option( 'eh_pricing_discount_product_price_user_role' );
		if ( is_array( $user_roles ) && in_array( $this->current_user_role, $user_roles ) ) {
			$min_prices = $this->elex_rp_variable_product_amount();
			$min_prices = array_map(
				function( $prices ) {
					return min( $prices );
				}, 
				$min_prices
			);
			$price = ! empty( $min_prices ) ? min( $min_prices ) : $price;
		}
		return $price;
	}

	public function elex_rp_get_max_price( $price ) {
		$user_roles = get_option( 'eh_pricing_discount_product_price_user_role' );
		if ( is_array( $user_roles ) && in_array( $this->current_user_role, $user_roles ) ) {
			$max_prices = $this->elex_rp_variable_product_amount();
			$max_prices = array_map(
				function( $prices ) {
					return max( $prices );
				}, 
				$max_prices
			);
			$price = ! empty( $max_prices ) ? max( $max_prices ) : $price;
		}
		return $price;
	}

    public function elex_rp_view_product_text($text, $product) {
        if ($this->elex_rp_is_hide_price($product) === true) {
            $text = 'Read more';
        }
        return $text;
    }

    public function elex_rp_product_is_on_sale($on_sale, $product) {
        if ($this->elex_rp_is_hide_price($product) === true || $this->elex_rp_is_hide_regular_price($product)) {
            $on_sale = false;
        } else {
            if ($this->elex_rp_get_product_type($product) != 'grouped') {
                $regular_price = $product->get_regular_price();
                $sale_price = $product->get_price();
                if (empty($sale_price)) {
                    $on_sale = false;
                } else {
                    if ($sale_price < $regular_price) {
                        $on_sale = true;
                    }
                }
            }
        }
        return $on_sale;
    }

    //function to hide product from shop page
    public function  elex_rp_product_query_meta_query($meta_query, $obj){
        if (is_user_logged_in()) {
            $hide_prod_role[]=array(
                'relation' => 'OR',
                array(
                    'key'     => 'eh_pricing_adjustment_product_visibility_user_role',
                    'compare' => 'NOT EXISTS'
                ),
                array(
                    'key'     => 'eh_pricing_adjustment_product_visibility_user_role',
                    'value'   => $this->current_user_role,
                    'compare' => 'NOT LIKE',
                ),
            ); 
        } else {
            $hide_prod_role[]=array(
                'relation' => 'OR',
                array(
                    'key'     => 'product_adjustment_product_visibility_unregistered',
                    'compare' => 'NOT EXISTS'
                ),
                array(
                    'key'     => 'product_adjustment_product_visibility_unregistered',
                    'value'   => 'yes',
                    'compare' => 'NOT LIKE',
                ),
            ); 
            
        }
        return $hide_prod_role;
    }

    // function to hide simple product from grouped product
    public function elex_rp_get_product_under_grouped_visibility($children, $product) {
        if ($this->elex_rp_get_product_type($product) == 'grouped') {
            foreach ($children as $key => $child_id) {
                $visible = true;
                if (is_user_logged_in()) {
                    $remove_product_visibility_roles = get_post_meta($child_id, 'eh_pricing_adjustment_product_visibility_user_role');
                    if (is_array(current($remove_product_visibility_roles)) && in_array($this->current_user_role, current($remove_product_visibility_roles))) {
                        $visible = false;
                    }
                } else {
                    if ('yes' == (get_post_meta($child_id, 'product_adjustment_product_visibility_unregistered', true))) {
                        $visible = false;
                    }
                }
                if ($visible == FALSE) {
                    unset($children[$key]);
                }
            }
        }
        return $children;
    }

    public function elex_rp_add_filter_for_get_price() {
        if (WC()->version < '2.7.0') {
            if ($this->sales_method === 'regular_sale' || $this->sales_method === 'regular' ) {
                add_filter('woocommerce_get_regular_price', array($this, 'elex_rp_get_price'), 99, 2); //function to modify product sale price
            } else {
                add_filter('woocommerce_get_sale_price', array($this, 'elex_rp_get_price'), 99, 2); //function to modify product sale price
            }
            add_filter('woocommerce_get_price', array($this, 'elex_rp_get_price'), 99, 2); //function to modify product price at all level
        } else {
            if ($this->sales_method === 'regular_sale' || $this->sales_method === 'regular') {
                add_filter('woocommerce_product_get_regular_price', array($this, 'elex_rp_get_price'), 99, 2);
                add_filter('woocommerce_product_variation_get_regular_price', array($this, 'elex_rp_get_price'), 99, 2);
                add_filter('woocommerce_get_variation_regular_price', array($this, 'elex_rp_get_price'), 99, 2);
            } else {
                add_filter('woocommerce_product_get_sale_price', array($this, 'elex_rp_get_price'), 99, 2);
            }
            add_filter('woocommerce_product_get_price', array($this, 'elex_rp_get_price'), 99, 2);
            add_filter('woocommerce_product_variation_get_price', array($this, 'elex_rp_get_price'), 99, 2);
        }
    }

    public function elex_rp_remove_filter_for_get_price() {
        if (WC()->version < '2.7.0') {
            if ($this->sales_method === 'regular_sale') {
                remove_filter('woocommerce_get_regular_price', array($this, 'elex_rp_get_price'), 99, 2); //function to modify product sale price
            } else {
                remove_filter('woocommerce_get_sale_price', array($this, 'elex_rp_get_price'), 99, 2); //function to modify product sale price
            }
            remove_filter('woocommerce_get_price', array($this, 'elex_rp_get_price'), 99, 2); //function to modify product price at all level
        } else {
            if ($this->sales_method === 'regular_sale') {
                remove_filter('woocommerce_product_get_regular_price', array($this, 'elex_rp_get_price'), 99, 2);
                remove_filter('woocommerce_product_variation_get_regular_price', array($this, 'elex_rp_get_price'), 99, 2);
                remove_filter('woocommerce_get_variation_regular_price', array($this, 'elex_rp_get_price'), 99, 2);
                remove_filter('woocommerce_product_get_price', array($this, 'elex_rp_get_price'), 99, 2);
                remove_filter('woocommerce_product_variation_get_price', array($this, 'elex_rp_get_price'), 99, 2);
            } elseif($this->sales_method === 'sale') {
                remove_filter('woocommerce_product_get_sale_price', array($this, 'elex_rp_get_price'), 99, 2);
                remove_filter('woocommerce_product_variation_get_price', array($this, 'elex_rp_get_price'), 99, 2);
            }
            remove_filter('woocommerce_product_get_price', array($this, 'elex_rp_get_price'), 99, 2);
        }
    }

    public function elex_rp_override_tax_display_setting_in($status) {
        if ($this->enable_role_tax) { //to make dependent on enable tax checkbox
            //checks if we have to show price including tax or excluding tax on specific page
			if(is_user_logged_in() || (ELEX_CUSTOM_REGISTRATION_STATUS && !(is_user_logged_in()))) {
                $user_role = $this->current_user_role;
            } else {
                $user_role = 'unregistered_user';
            }
            if (!empty($this->role_tax_option[$user_role]) && !empty($this->role_tax_option[$user_role]['tax_option'])) {
                switch ($this->role_tax_option[$user_role]['tax_option']) {
                    case 'show_price_including_tax':
                        $status = 'incl';
                        break;
                    case 'show_price_excluding_tax':
                        $status = 'excl';
                        break;
                    case 'show_price_including_tax_shop':
                        if (is_shop() || is_product()) {
                            $status = 'incl';
                        } else {
                            $status = 'excl';
                        }
                        break;
                    case 'show_price_including_tax_cart_checkout':
                        if (is_cart() || is_checkout()) {
                            $status = 'incl';
                        } else {
                            $status = 'excl';
                        }
                        break;
                    case 'show_price_excluding_tax_shop':
                        if (is_shop() || is_product()) {
                            $status = 'excl';
                        } else {
                            $status = 'incl';
                        }
                        break;
                    case 'show_price_excluding_tax_cart_checkout':
                        if (is_cart() || is_checkout()) {
                            $status = 'excl';
                        } else {
                            $status = 'incl';
                        }
                        break;
                    case 'default':
                        break;
                }
            }
        }
        return $status;
    }

    function elex_rp_product_tax_class($tax_class) {
        if ($this->enable_role_tax) {

            // returns selected tax class
            if (isset($this->role_tax_option[$this->current_user_role]) && isset($this->role_tax_option[$this->current_user_role]['tax_classes']) && $this->role_tax_option[$this->current_user_role]['tax_classes'] != 'default') {
                return $this->role_tax_option[$this->current_user_role]['tax_classes'];
            }
        }
        return $tax_class;
    }

    function elex_rp_get_add_to_cart_placeholder_text() {
        if (is_user_logged_in()) {
            $add_to_cart_text = get_option('eh_pricing_discount_cart_user_role_text');
        } else {
            $add_to_cart_text = get_option('eh_pricing_discount_cart_unregistered_user_text');
        }

        if (!empty($add_to_cart_text)) {
            $add_to_cart_text = $this->elex_rp_return_wpml_string($add_to_cart_text, 'Remove Add-to-cart - Product');
            echo $add_to_cart_text;
        }
    }
    function elex_rp_get_add_to_cart_product_placeholder_text($product_id) {
        if (is_user_logged_in()) {
            $add_to_cart_text = get_post_meta($product_id, 'product_adjustment_hide_addtocart_placeholder_role',true);
        } else {
            $add_to_cart_text = get_post_meta($product_id, 'product_adjustment_hide_addtocart_placeholder_unregistered',true);
        }

        if (!empty($add_to_cart_text)) {
            $add_to_cart_text = $this->elex_rp_return_wpml_string($add_to_cart_text, 'Remove Add-to-cart - Product');
            echo $add_to_cart_text;
        }
    }

    public function elex_rp_product_page_remove_add_to_cart_option() {
        global $product;
        $product_id = $this->elex_rp_get_product_id($product);
		$temp_data = $this->elex_rp_get_product_type($product);
        if ($temp_data == 'variation') {
            $product_id = $this->elex_rp_get_product_parent_id($product);
        }
		$hide_price = $this->elex_rp_is_hide_price($product);
          //individual catalog mode
        if (('yes' == get_post_meta($product_id, 'product_adjustment_hide_addtocart_catalog', true)) && (('yes' == get_post_meta($product_id, 'product_adjustment_hide_addtocart_catalog_product', true)) || ('' == get_post_meta($product_id, 'product_adjustment_hide_addtocart_catalog_product', true)))) {
            if(! (get_post_meta($product_id, 'product_adjustment_exclude_admin_catalog', true) == 'yes' && $this->current_user_role =='administrator')) {
                $this->elex_rp_remove_add_to_cart_action_product_page($product);
                $place_holder = get_post_meta($product_id, 'product_adjustment_hide_addtocart_placeholder_catalog', true);
                if(!empty($place_holder)) {
                    $place_holder = $this->elex_rp_return_wpml_string($place_holder, 'Remove Add-to-cart - Product');
                    echo $place_holder;
                }
            }
        }
        elseif('yes' == get_post_meta($product_id, 'product_adjustment_customize_addtocart_catalog', true) && !$hide_price){
            $url_product_page = get_post_meta($product_id, 'product_adjustment_customize_addtocart_btn_url_catalog', true);
            $button_text_product_page = get_post_meta($product_id, 'product_adjustment_customize_addtocart_prod_btn_text_catalog', true);
            if($url_product_page !='' && $button_text_product_page !=''){
                if(! (get_post_meta($product_id, 'product_adjustment_exclude_admin_catalog', true) == 'yes' && $this->current_user_role =='administrator')) {
                    $this->elex_rp_remove_add_to_cart_action_product_page($product);
                    $this->elex_rp_redirect_addtocart_product_page($url_product_page,$button_text_product_page);
                }
            }
        }
        
        elseif ('yes' == get_option('eh_pricing_discount_cart_catalog_mode') && 'yes' == get_option('elex_catalog_remove_addtocart_product')) {
            if(! (get_option('eh_pricing_discount_price_catalog_mode_exclude_admin') == 'yes' && $this->current_user_role =='administrator')) {
                $this->elex_rp_remove_add_to_cart_action_product_page($product);
                $place_holder = get_option('eh_pricing_discount_cart_catalog_mode_text');
                if(!empty($place_holder)) {
                    $place_holder = $this->elex_rp_return_wpml_string($place_holder, 'Remove Add-to-cart - Product');
                    echo $place_holder;
                }
            }
        }
        elseif('yes' == get_option('eh_pricing_discount_replace_cart_catalog_mode') && !$hide_price){
            
            if($this->replace_add_to_cart_button_url_shop_catalog !='' && $this->replace_add_to_cart_button_text_product_catalog !=''){
              
                if(! (get_option('eh_pricing_discount_price_catalog_mode_exclude_admin') == 'yes' && $this->current_user_role =='administrator')) {
                    $this->elex_rp_remove_add_to_cart_action_product_page($product);
                    $this->elex_rp_redirect_addtocart_product_page($this->replace_add_to_cart_button_url_shop_catalog,$this->replace_add_to_cart_button_text_product_catalog);
                }
            }
        }
      
        
        elseif (is_user_logged_in()) {
            $remove_settings_cart_roles = get_option('eh_pricing_discount_cart_user_role');
            $remove_product_cart_roles = get_post_meta($product_id, 'eh_pricing_adjustment_product_addtocart_user_role', true);
            $replace_product_cart_roles = get_post_meta($product_id, 'eh_pricing_adjustment_product_customize_addtocart_user_role', true);
            $replace_cart_user_role = get_option('eh_pricing_discount_replace_cart_user_role');
		
            if (is_array($remove_product_cart_roles) && in_array($this->current_user_role, $remove_product_cart_roles) && (('yes' == (get_post_meta($product_id, 'product_adjustment_hide_addtocart_user_role_product', true))) || ('' == (get_post_meta($product_id, 'product_adjustment_hide_addtocart_user_role_product', true))))) {
                $this->elex_rp_remove_add_to_cart_action_product_page($product);
                $this->elex_rp_get_add_to_cart_product_placeholder_text($product_id);
            }
            elseif (is_array($replace_product_cart_roles) && in_array($this->current_user_role, $replace_product_cart_roles) && !$hide_price ) {
                $url_product_page = get_post_meta($product_id, 'product_adjustment_customize_addtocart_btn_url_role', true);
                $button_text_product_page = get_post_meta($product_id, 'product_adjustment_customize_addtocart_prod_btn_text_role', true);
                    if($url_product_page !='' && $button_text_product_page !=''){
                        $this->elex_rp_remove_add_to_cart_action_product_page($product);
                        $this->elex_rp_redirect_addtocart_product_page($url_product_page,$button_text_product_page);
                    }
            }
            elseif (is_array($remove_settings_cart_roles) && in_array($this->current_user_role, $remove_settings_cart_roles) && 'yes' == get_option('elex_user_role_remove_addtocart_product')) {
                $this->elex_rp_remove_add_to_cart_action_product_page($product);
                $this->elex_rp_get_add_to_cart_placeholder_text();
            }
            elseif(is_array($replace_cart_user_role) && in_array($this->current_user_role, $replace_cart_user_role) && !$hide_price ){
                if($this->replace_add_to_cart_user_role_url_shop !='' && $this->replace_add_to_cart_user_role_button_text_product !=''){
                    $this->elex_rp_remove_add_to_cart_action_product_page($product);
                    $this->elex_rp_redirect_addtocart_product_page($this->replace_add_to_cart_user_role_url_shop,$this->replace_add_to_cart_user_role_button_text_product);
                }
            }
        } else {
            if (('yes' == (get_post_meta($product_id, 'product_adjustment_hide_addtocart_unregistered', true))) && (('yes' == (get_post_meta($product_id, 'product_adjustment_hide_addtocart_unregistered_product', true))) || ('' == (get_post_meta($product_id, 'product_adjustment_hide_addtocart_unregistered_product', true))))) {
                $this->elex_rp_remove_add_to_cart_action_product_page($product);
                $this->elex_rp_get_add_to_cart_product_placeholder_text($product_id);
            }
            elseif('yes' == get_post_meta($product_id, 'product_adjustment_customize_addtocart_unregistered', true) && !$hide_price){
                $url_product_page = get_post_meta($product_id, 'product_adjustment_customize_addtocart_btn_url_unregistered', true);
                $button_text_product_page = get_post_meta($product_id, 'product_adjustment_customize_addtocart_prod_btn_text_unregistered', true);
                if($url_product_page !='' && $button_text_product_page !=''){
                    $this->elex_rp_remove_add_to_cart_action_product_page($product);
                    $this->elex_rp_redirect_addtocart_product_page($url_product_page,$button_text_product_page);
                }
            }
            elseif ('yes' == get_option('eh_pricing_discount_cart_unregistered_user') && 'yes' == get_option('elex_unregistered_remove_addtocart_product')) {
                $this->elex_rp_remove_add_to_cart_action_product_page($product);
                $this->elex_rp_get_add_to_cart_placeholder_text();
            }
            elseif('yes' == get_option('eh_pricing_discount_replace_cart_unregistered_user') && !$hide_price){
                if($this->replace_add_to_cart_button_url_shop !='' && $this->replace_add_to_cart_button_text_product !=''){
                    $this->elex_rp_remove_add_to_cart_action_product_page($product);
                    $this->elex_rp_redirect_addtocart_product_page($this->replace_add_to_cart_button_url_shop, $this->replace_add_to_cart_button_text_product);
                }
            }
        }
    }
    
    function elex_rp_remove_add_to_cart_action_product_page($product) {
        if( $this->elex_rp_get_product_type($product) == 'variable') {
        remove_action( 'woocommerce_single_variation', 'woocommerce_single_variation_add_to_cart_button', 20 );
        }
        else {
            remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30);
        }
        // To hide the add-to-cart but some themes which couldn't hide using above actions
        if (!in_array('elex_request_a_quote_premium/class-elex-request-a-quote-premium.php', apply_filters('active_plugins', get_option('active_plugins'))) && !in_array('elex_request_a_quote/class-elex-request-a-quote.php', apply_filters('active_plugins', get_option('active_plugins')))){
            ?>
            <style>
                .single_add_to_cart_button {
                    display: none !important;
                }
            </style>
        <?php
        }
    }
            
    function elex_rp_redirect_addtocart_product_page($url_product_page,$button_text_product_page){
        $button_text_product_page = $this->elex_rp_return_wpml_string($button_text_product_page, 'Replace Add-to-cart - Product');
        $secure = strpos($url_product_page, 'https://');
        $url_product_page = str_replace('https://', '', $url_product_page);
        $url_product_page = str_replace('http://', '', $url_product_page);
        $suff = ($secure === false) ? 'http://' : 'https://';
        ?>
            <div id="elex_prod_div">
                <button id="elex_prod_btn" class="btn btn-success" onclick=" window.open('<?php echo $suff.$url_product_page ?>','_self')"><?php echo $button_text_product_page ?></button>
            </div>
        <?php
        //Misalignment  of add-to-cart button in some themes
        add_action( 'woocommerce_single_product_summary', array($this,'elex_rp_extra_button_on_product_page'), 30);
    }
    function elex_rp_extra_button_on_product_page() {
        ?>
        <div id='elex_prod_new_div'></div>
        <script>
             var dom = jQuery('#elex_prod_div').html();
            jQuery('#elex_prod_btn').remove();
            jQuery( "#elex_prod_new_div" ).append(dom);
            </script>
        <?php
    }

    public function init_fields() {
		global $woocommerce;
        $this->role_price_adjustment = get_option('eh_pricing_discount_price_adjustment_options', array());
        $this->current_user_role = $this->elex_rp_get_priority_user_role(wp_get_current_user()->roles, $this->role_price_adjustment);
		$this->multiple_users = ! empty( wp_get_current_user()->roles ) ? wp_get_current_user()->roles : array( 'unregistered_user' );
		$this->current_user_id = get_current_user_id();
		$this->current_user_mail = wp_get_current_user()->user_email;
		$this->enable_role_tax = get_option('eh_pricing_discount_enable_tax_options') == 'yes' ? true : false;
        $this->role_tax_option = get_option('eh_pricing_discount_price_tax_options', array());
        $this->tax_user_role = $this->elex_rp_get_priority_user_role(wp_get_current_user()->roles, $this->role_tax_option);
        $this->price_suffix_option = get_option('eh_pricing_discount_enable_price_suffix', 'none');
        $this->general_price_suffix = get_option('eh_pricing_discount_price_general_price_suffix', '');
        $this->role_price_suffix = get_option('eh_pricing_discount_role_price_suffix', array());
        $this->suffix_user_role = $this->elex_rp_get_priority_user_role(wp_get_current_user()->roles, $this->role_price_suffix);
        $this->price_suffix_user_role = $this->suffix_user_role != '' ? $this->suffix_user_role : 'unregistered_user';
        $this->replace_add_to_cart = get_option('eh_pricing_discount_replace_cart_unregistered_user') == 'yes' ? true : false;
        $this->replace_add_to_cart_button_text_product = get_option('eh_pricing_discount_replace_cart_unregistered_user_text_product', '');
        $this->replace_add_to_cart_button_text_shop = get_option('eh_pricing_discount_replace_cart_unregistered_user_text_shop', '');
        $this->replace_add_to_cart_button_url_shop = get_option('eh_pricing_discount_replace_cart_unregistered_user_url_shop', '');
        $this->replace_add_to_cart_catalog = get_option('eh_pricing_discount_replace_cart_catalog_mode') == 'yes' ? true : false;
        $this->replace_add_to_cart_button_text_product_catalog = get_option('eh_pricing_discount_replace_cart_catalog_mode_text_product', '');
        $this->replace_add_to_cart_button_text_shop_catalog = get_option('eh_pricing_discount_replace_cart_catalog_mode_text_shop', '');
        $this->replace_add_to_cart_button_url_shop_catalog = get_option('eh_pricing_discount_replace_cart_catalog_mode_url_shop', '');
        $this->replace_add_to_cart_user_role = get_option('eh_pricing_discount_replace_cart_user_role', array());
        $this->replace_add_to_cart_user_role_button_text_product = get_option('eh_pricing_discount_replace_cart_user_role_text_product', '');
        $this->replace_add_to_cart_user_role_button_text_shop = get_option('eh_pricing_discount_replace_cart_user_role_text_shop', '');
        $this->replace_add_to_cart_user_role_url_shop = get_option('eh_pricing_discount_replace_cart_user_role_url_shop', '');
        $this->individual_product_adjustment_roles = get_option('eh_pricing_discount_product_price_user_role', array());
        $this->individual_product_adjustment_for_users = get_option('eh_pricing_discount_product_on_users', array());
		if(ELEX_CUSTOM_REGISTRATION_STATUS && !(is_user_logged_in()) && $woocommerce && $woocommerce->session && $woocommerce->session->get('elex_afreg_select_user_role')){
			$user_role = $woocommerce->session->get('elex_afreg_select_user_role');

			if(!empty($user_role) ){
				$this->current_user_role = $this->elex_rp_get_priority_user_role($user_role, $this->role_price_adjustment);
				$this->tax_user_role = $this->elex_rp_get_priority_user_role($user_role, $this->role_tax_option);
				$this->suffix_user_role = $this->elex_rp_get_priority_user_role($user_role, $this->role_price_suffix);
			}
	    }
	}

    //function to determine the user role to use in case of multiple user roles for one user
    public function elex_rp_get_priority_user_role($user_roles, $role_priority_list) {
        if (is_user_logged_in()) {
            if (!empty($role_priority_list['roles'])) {
                foreach ($role_priority_list as $id => $value) {
                    if (in_array($id, $user_roles)) {
                        return $id;
                    }
                }
            } else {
                // Return the first element in the array, irrespective of index.
				return array_values($user_roles)[0];
            }
        } else {
			if(ELEX_CUSTOM_REGISTRATION_STATUS){
				if(empty($user_roles)){
					return 'unregistered_user';	
			   }else{
				   return $user_roles;
			   }
			}else{
				return 'unregistered_user';
			}
           
        }
    }

    //function to replace add to cart with another url for user role and unregistered user 
    public function elex_rp_add_to_cart_text_url_replace($link, $product) {
        $temp_data = $this->elex_rp_get_product_type($product);
        $product_id = $this->elex_rp_get_product_id($product);
		if( $temp_data  == 'variation'){
			$product_id = $this->elex_rp_get_product_parent_id($product);
		}
		$hide_price = $this->elex_rp_is_hide_price($product);
        $cart_text_content = $link;
        if ($temp_data === 'simple' || $temp_data === 'variable' || $temp_data === 'grouped') {

            if('yes' == get_post_meta($product_id, 'product_adjustment_customize_addtocart_catalog', true) && !$hide_price ){
                $url_product_page = get_post_meta($product_id, 'product_adjustment_customize_addtocart_btn_url_catalog', true);
                $button_text_shop_page = get_post_meta($product_id, 'product_adjustment_customize_addtocart_shop_btn_text_catalog', true);
                if( $button_text_shop_page !=''){
                    if(! (get_post_meta($product_id, 'product_adjustment_exclude_admin_catalog', true) == 'yes' && $this->current_user_role =='administrator')) {
                       if($url_product_page == ''){
                           $cart_text_content = $this-> elex_rp_replace_add_cart_text_shop($cart_text_content,$button_text_shop_page);
                       } else {  
                            $cart_text_content = $this-> elex_rp_replace_add_cart_text_shop_with_url($cart_text_content,$button_text_shop_page,$url_product_page);
                        }
                    
                    }
                }
            }
            elseif ($this->replace_add_to_cart_catalog && $this->replace_add_to_cart_button_text_shop_catalog != '' && !$hide_price) {
                if(! (get_option('eh_pricing_discount_price_catalog_mode_exclude_admin') == 'yes' && $this->current_user_role =='administrator')) {
                    if (empty($this->replace_add_to_cart_button_url_shop_catalog)) {
                        $cart_text_content = $this-> elex_rp_replace_add_cart_text_shop($cart_text_content,$this->replace_add_to_cart_button_text_shop_catalog);
                    } else {
                        $cart_text_content = $this-> elex_rp_replace_add_cart_text_shop_with_url($cart_text_content,$this->replace_add_to_cart_button_text_shop_catalog,$this->replace_add_to_cart_button_url_shop_catalog);
                    }
                }
            }
            
           
            
            elseif ((is_user_logged_in())) {
                $role_shop_btn_text = get_post_meta($product_id, 'product_adjustment_customize_addtocart_shop_btn_text_role',true);
                $role_btn_url = get_post_meta($product_id, 'product_adjustment_customize_addtocart_btn_url_role',true);
                $replace_addtocart = get_post_meta($product_id, 'eh_pricing_adjustment_product_customize_addtocart_user_role',true);
			
                if (is_array($replace_addtocart) && in_array($this->current_user_role, $replace_addtocart) && $role_shop_btn_text != '' && !$hide_price) {
                    if (empty($role_btn_url)) {
                        $cart_text_content = $this-> elex_rp_replace_add_cart_text_shop($cart_text_content,$role_shop_btn_text);
                    }
                    else {
                        $cart_text_content = $this-> elex_rp_replace_add_cart_text_shop_with_url($cart_text_content,$role_shop_btn_text,$role_btn_url);
                    }
                }

                elseif (is_array($this->replace_add_to_cart_user_role) && in_array($this->current_user_role, $this->replace_add_to_cart_user_role) && $this->replace_add_to_cart_user_role_button_text_shop != '' && !$hide_price ) {
                    if (empty($this->replace_add_to_cart_user_role_url_shop)) {
                        $cart_text_content = $this-> elex_rp_replace_add_cart_text_shop($cart_text_content,$this->replace_add_to_cart_user_role_button_text_shop);
                    } else {
                        $cart_text_content = $this-> elex_rp_replace_add_cart_text_shop_with_url($cart_text_content,$this->replace_add_to_cart_user_role_button_text_shop,$this->replace_add_to_cart_user_role_url_shop);
                    }
                }
            }
            elseif (!is_user_logged_in()) {
                $unregistered_shop_btn_text = get_post_meta($product_id, 'product_adjustment_customize_addtocart_shop_btn_text_unregistered',true);
                $unregistered_btn_url = get_post_meta($product_id, 'product_adjustment_customize_addtocart_btn_url_unregistered',true);
                if ('yes' == (get_post_meta($product_id, 'product_adjustment_customize_addtocart_unregistered', true)) && $unregistered_shop_btn_text != '' && !$hide_price) {
                    if (empty($unregistered_btn_url)) {
                        $cart_text_content = $this-> elex_rp_replace_add_cart_text_shop($cart_text_content,$unregistered_shop_btn_text);
                    }
                    else {
                        $cart_text_content = $this-> elex_rp_replace_add_cart_text_shop_with_url($cart_text_content,$unregistered_shop_btn_text,$unregistered_btn_url);
                    }
                }
                elseif ($this->replace_add_to_cart && $this->replace_add_to_cart_button_text_shop != '' && !$hide_price) {
                    if (empty($this->replace_add_to_cart_button_url_shop)) {
                        $cart_text_content = $this-> elex_rp_replace_add_cart_text_shop($cart_text_content,$this->replace_add_to_cart_button_text_shop);
                    } else {
                        $cart_text_content = $this-> elex_rp_replace_add_cart_text_shop_with_url($cart_text_content,$this->replace_add_to_cart_button_text_shop,$this->replace_add_to_cart_button_url_shop);
                    }
                }
            }
        }
        return $cart_text_content;
    }
    function elex_rp_replace_add_cart_text_shop ($cart_text_content,$shop_addtocart_text) {
        $cart_text_content = str_replace('Add to cart', $shop_addtocart_text, $cart_text_content);
        $cart_text_content = str_replace('Select options', $shop_addtocart_text, $cart_text_content);
        $cart_text_content = str_replace('View products', $shop_addtocart_text, $cart_text_content);
        $cart_text_content = $this->elex_rp_return_wpml_string($cart_text_content, 'Replace Add-to-cart - Shop');
        return $cart_text_content;
    }
    function elex_rp_replace_add_cart_text_shop_with_url ($cart_text_content,$shop_addtocart_text,$url) {
        $shop_addtocart_text = $this->elex_rp_return_wpml_string($shop_addtocart_text, 'Replace Add-to-cart - Shop');
        $secure = strpos($url, 'https://');
        $url = str_replace('https://', '', $url);
        $url = str_replace('http://', '', $url);
        $suff = ($secure === false) ? 'http://' : 'https://';
        $cart_text_content = '<a href="' . $suff . $url . '" class="button alt">' . $shop_addtocart_text . '</a>';
        return $cart_text_content;
    }



    //function to edit add to cart text of product page with placeholder text when replace add to cart button is selected

    public function elex_rp_add_to_cart_text_content_replace($text) {
        $cart_text_content = $text;
        global $product;
        $product_id = $this->elex_rp_get_product_id($product);
        $button_text_product_page = get_post_meta($product_id, 'product_adjustment_customize_addtocart_prod_btn_text_catalog', true);
        $product_button_text_checkbox=get_post_meta($product_id, 'product_adjustment_customize_addtocart_catalog', true);
        if ($product_button_text_checkbox =='yes' && $button_text_product_page  != '') {
             if(!(get_post_meta($product_id, 'product_adjustment_exclude_admin_catalog', true) == 'yes' && $this->current_user_role =='administrator')) {
                $cart_text_content = $button_text_product_page;
            }
        }

        elseif ($this->replace_add_to_cart_catalog && $this->replace_add_to_cart_button_text_product_catalog != '') {
            if(!(get_option('eh_pricing_discount_price_catalog_mode_exclude_admin') == 'yes' && $this->current_user_role =='administrator')) {
               
                $cart_text_content = $this->replace_add_to_cart_button_text_product_catalog;
            }
        }
        elseif ((is_user_logged_in())) {
            $individual_prod_btn_text = get_post_meta($product_id, 'product_adjustment_customize_addtocart_prod_btn_text_role',true);
            $replace_addtocart = get_post_meta($product_id, 'eh_pricing_adjustment_product_customize_addtocart_user_role',true);
            if (is_array($replace_addtocart) && in_array($this->current_user_role, $replace_addtocart) && $individual_prod_btn_text != '') {
                $cart_text_content = $individual_prod_btn_text;
            }
            elseif (is_array($this->replace_add_to_cart_user_role) && in_array($this->current_user_role, $this->replace_add_to_cart_user_role) && $this->replace_add_to_cart_user_role_button_text_product != '') {
                $cart_text_content = $this->replace_add_to_cart_user_role_button_text_product;
            }
        }
        elseif (!is_user_logged_in()) {
            $individual_prod_btn_text = get_post_meta($product_id, 'product_adjustment_customize_addtocart_prod_btn_text_unregistered',true);
            if ('yes' == (get_post_meta($product_id, 'product_adjustment_customize_addtocart_unregistered', true)) && $individual_prod_btn_text != '') {
                $cart_text_content = $individual_prod_btn_text;
            }
            elseif ($this->replace_add_to_cart && $this->replace_add_to_cart_button_text_product != '') {
                $cart_text_content = $this->replace_add_to_cart_button_text_product;
            }
        }
        $cart_text_content = $this->elex_rp_return_wpml_string($cart_text_content, 'Replace Add-to-cart - Product');
        return $cart_text_content;
    }

    //to get category ids for a product
    public function elex_rp_get_product_category_using_id($prod_id) {
        $terms = get_the_terms($prod_id, 'product_cat');
        if ($terms) {
            $cats_ids_array = array();
            foreach ($terms as $key => $term) {
                array_push($cats_ids_array, $term->term_id);
                $term2 = $term;

                if (!in_array($term2->parent, $cats_ids_array)) {
                    while ($term2->parent > 0) {
                        array_push($cats_ids_array, $term2->parent);
                        $term2 = get_term_by("id", $term2->parent, "product_cat");
                    }
                }
            }
            return $cats_ids_array;
        }
        return array();
    }

    public function elex_rp_get_price($price = '', $product = null) {	
        $temp_price = $price; // Store the initial value of price.
        if(isset($_POST['order_id'])) {
            $user_id = get_post_meta($_POST['order_id'],'_customer_user',true);
            if($user_id) {
                $user_meta = get_userdata($user_id);
                $user_roles = $user_meta->roles;
				$this->current_user_id = $user_id;
				$this->multiple_users = $user_roles;
				$this->current_user_mail = $user_meta->user_email;
                $this->current_user_role = isset( $user_roles[0] ) ? $user_roles[0] : 'unregistered_user' ;
            }
        }
        $current_user_id = get_current_user_id();
       
        if(doing_filter('woocommerce_get_cart_item_from_session')){
            return $price;
        }
        if ($this->elex_rp_is_hide_price($product)) {
            if( $this->elex_rp_get_product_type($product) == 'variation') {
                    remove_action( 'woocommerce_single_variation', 'woocommerce_single_variation_add_to_cart_button', 20 );
                    return false;
                }
            if ($this->elex_rp_is_price_hidden_in_product_meta($product)) {
                $price = '';
            } else {
                $price = '';
            }
            return $price;
        }

        $pid = $this->elex_rp_get_product_id($product);
        $temp_data = $this->elex_rp_get_product_type($product);
		$product_actual_price = get_post_meta( $pid, '_price', true);

		$exchange_rate = !empty( $temp_price ) && !empty( $product_actual_price ) ? $temp_price/$product_actual_price : 1;
        if( apply_filters('xa_pbu_skip_product',false,$pid) != false || apply_filters('xa_pbu_skip_product_on_sale',false,$pid) != false ) {
            //Role Based Price (individual product skip price change)	
            return $price;
        }
        $pid = $this->elex_rp_get_product_id($product);
        $temp_data = $this->elex_rp_get_product_type($product);
        $current_user_email = $this->current_user_mail;

	 if ((is_array($this->individual_product_adjustment_roles)||is_array($this->individual_product_adjustment_for_users) )&& ELEX_USER_MULTIPLE_ROLE_STATUS || (in_array($this->current_user_role, $this->individual_product_adjustment_roles)||!empty(get_post_meta($pid, 'product_role_based_price_user_'. $current_user_email)))) {
            //Role Based Price (individual product page price change)
			// bug fix customization for user multiple role plugin.
            $current_user_role = $this->current_user_role;
			$multiple_role_option = get_option('eh_pricing_discount_multiple_role_price');
			$count_multiple_role =	count($this->multiple_users);
		    $role_value = array();
			$product_users_price = !empty(get_post_meta($pid, 'product_role_based_price_user_'. $current_user_email)) ? get_post_meta($pid, 'product_role_based_price_user_'. $current_user_email) : '';
		
			if(is_array($this->individual_product_adjustment_for_users) && is_array($product_users_price) && isset($product_users_price[0]) && !empty($product_users_price[0])){ 
				$product_user_price = $product_users_price[0];
			}
		elseif($count_multiple_role>1 ){
			$consolidate_price =0;
			foreach($this->multiple_users as $multiple_role_key => $multiple_role_val){
				if(in_array($multiple_role_val,$this->individual_product_adjustment_roles))	{
					$product_users_role_price = get_post_meta($pid, 'product_role_based_price_'.$multiple_role_val);
					if (is_array($product_users_role_price) && isset($product_users_role_price[0]) && !empty($product_users_role_price[0])) {
						$role_value[] =$product_users_role_price[0];
					}
				} 
			}	
		if(is_array($role_value) && !empty($role_value)){
			asort($role_value);
			$min_role_price = current($role_value);
			$max_role_price = end($role_value);
		
		if($multiple_role_option == 'max_role_price'){
			$product_user_price = $max_role_price;
		}elseif($multiple_role_option == 'min_role_price'){
			$product_user_price = $min_role_price;
		}else {
			foreach($role_value as $price_val){
			
				$consolidate_price +=$price_val;
			}
			$product_user_price = $consolidate_price;
		}
	   }
	}
	else{
        $product_user_role_price = get_post_meta($pid, 'product_role_based_price_'.$this->current_user_role);   
    	 if (is_array($product_user_role_price) && isset($product_user_role_price[0]) && !empty($product_user_role_price[0])) {
            $product_user_price = $product_user_role_price[0];
        }
	}

            if (!empty($product_user_price)) {
                if ($current_user_role) {
                    $price_value = $product_user_price;
                    //If decimal separator is comma.
                    if ( preg_match('/^[0-9,\.]/', $price_value) !== false) {
                    $price_value = preg_replace('/,/', '.',$price_value);
                    }
                    $val = floatval($price_value);
                    if (is_numeric($val)) {
                        $price = $val * $exchange_rate; 
                    }
                }
                return $price;
            }
            
          
        }
		if ( empty( $price ) ) {
            return $price;
        }
        if ( $temp_data == 'variation' ) {
            $pid = $this->elex_rp_get_product_parent_id($product);
        }
        //----------------------analyse this for bugs
        //price adjustment display for discount when price adjustment on both regular and sale price
        if ($this->sales_method == 'regular_sale' && (doing_filter('woocommerce_product_get_regular_price') || doing_filter('woocommerce_product_variation_get_regular_price') || doing_filter('woocommerce_get_variation_regular_price'))) {
                $adjustment_value = $this->elex_rp_get_adjustment_for_individual_products($pid, $price, $temp_data);
                if($adjustment_value == 'no_amount') {
                    $adjustment_value = 0;
                }
                else {
                    $price += $adjustment_value;
                    $this->elex_rp_add_filter_for_get_price();
                    return $price;
                }
                if (is_array($this->individual_product_adjustment_roles) && in_array($this->current_user_role, $this->individual_product_adjustment_roles)) {
                //common page adjustment
                if ($temp_data === 'variation') {
                    $prdct_id = $this->elex_rp_get_product_category_using_id($this->elex_rp_get_product_parent_id($product));
                } else {
                    if (WC()->version < '2.7.0') {
                        $temp_post_id = $product->post->ID;
                    } else {
                        $temp_post_id = $product->get_id();
                    }
                    $prdct_id = $this->elex_rp_get_product_category_using_id($temp_post_id);
                }
                //Code snippet to apply different discounts for different categories
                $categ_disc = apply_filters('elex_catalog_discount_for_categories', false);
                if ($categ_disc) {
                    $adjustment_value = $this->elex_rp_cat_snippet_to_give_discount_different_category($categ_disc, $prdct_id, $adjustment_value, $price);
                    $price += $adjustment_value;
                } else {
                    $price = $this->elex_rp_get_adjustment_amount($price, $prdct_id, $temp_data, $adjustment_value);
                }
                $this->elex_rp_add_filter_for_get_price();
                return $price;
            } else {
                $temp_data = $this->elex_rp_get_product_type($product);
                if ($temp_data === 'variation') {
                    $prdct_id = $this->elex_rp_get_product_category_using_id($this->elex_rp_get_product_parent_id($product));
                } else {
                    if (WC()->version < '2.7.0') {
                        $temp_post_id = $product->post->ID;
                    } else {
                        $temp_post_id = $product->get_id();
                    }
                    $prdct_id = $this->elex_rp_get_product_category_using_id($temp_post_id);
                }
                $adjustment_value = 0;
                //Code snippet to apply different discounts for different categories
                $categ_disc = apply_filters('elex_catalog_discount_for_categories', false);
                if ($categ_disc) {
                    $adjustment_value = $this->elex_rp_cat_snippet_to_give_discount_different_category($categ_disc, $prdct_id, $adjustment_value, $price);
                    $price += $adjustment_value;
                } else {
                    $price = $this->elex_rp_get_adjustment_amount($price, $prdct_id, $temp_data, $adjustment_value);
                }
                $this->elex_rp_add_filter_for_get_price();
                return $price;
            }
        }
        //------------------------

        $this->elex_rp_remove_filter_for_get_price();
        

        

            if ($temp_data == 'variation') {
                $pid = $this->elex_rp_get_product_parent_id($product);
            }
            $adjustment_value = $this->elex_rp_get_adjustment_for_individual_products($pid, $price, $temp_data);
            if($adjustment_value == 'no_amount') {
                $adjustment_value = 0;
            }
            else {
                $price += $adjustment_value;
                $this->elex_rp_add_filter_for_get_price();
                return $price;
            }

        //common price adjustment 
        add_filter('woocommerce_available_variation', function ($value, $object = null, $variation = null) {
            if ($value['price_html'] == '') {
                $value['price_html'] = '<span class="price">' . $variation->get_price_html() . '</span>';
            }
            return $value;
        }, 10, 3);
        if ($temp_data === 'variation') {
            $prdct_id = $this->elex_rp_get_product_category_using_id($this->elex_rp_get_product_parent_id($product));
        } else {
            if (WC()->version < '2.7.0') {
                $temp_post_id = $product->post->ID;
            } else {
                $temp_post_id = $product->get_id();
            }
            $prdct_id = $this->elex_rp_get_product_category_using_id($temp_post_id);
        }
        //Code snippet to apply different discounts for different categories
        $categ_disc = apply_filters('elex_catalog_discount_for_categories', false);
        if ($categ_disc) {
            $adjustment_value = $this->elex_rp_cat_snippet_to_give_discount_different_category($categ_disc,$prdct_id,$adjustment_value,$price);
            $price += $adjustment_value;
        } else {
            $price = $this->elex_rp_get_adjustment_amount($price, $prdct_id, $temp_data, $adjustment_value);
        }

        if ($this->sales_method == 'regular'){
           
            if($product->is_on_sale($product->get_id())){
			  $regular_price =$product->get_regular_price($product->get_id());
			    if($regular_price!=$temp_price){
					$this->elex_rp_add_filter_for_get_price();
					if(floatval($price)!=floatval($temp_price)){ // check if regular price discount is less than sale price.
						return $temp_price;
					} else {
						return $price;
					}
			    }
            } else{
                if('simple'==$product->get_type()){ // simple and grouped.
                    $this->elex_rp_add_filter_for_get_price();
                    return $price;
                } else { // variable.
                    $is_markup = $this->is_markup;
                    if ($is_markup) {
                        $this->elex_rp_add_filter_for_get_price();
                        return $price;
                    } else {
						$this->elex_rp_add_filter_for_get_price();
                        return $price;
                    }
                }
            }
        }
        $this->elex_rp_add_filter_for_get_price();
        return $price;
    }

    function elex_rp_get_adjustment_for_individual_products($pid, $price, $temp_data = ''){
        $adjustment_value = 0;
        $current_user_id = get_current_user_id();
        $product_price_adjustment_users = get_post_meta($pid, 'product_price_adjustment_for_users', true);
        $product_price_adjustment_roles = get_post_meta($pid, 'product_price_adjustment', true);
		if ( $this->addon_check && ( ( is_array($this->individual_product_adjustment_roles ) && in_array( $this->current_user_role, $this->individual_product_adjustment_roles ) ) || ( is_array( $this->individual_product_adjustment_for_users ) && isset( $this->individual_product_adjustment_for_users['users'] ) && in_array( $current_user_id, $this->individual_product_adjustment_for_users['users'] ) ) ) ) {
			$users_price_adjustment = apply_filters( 'elex_price_adjustment_individual_users_override_individual_setting', $price, $pid, $current_user_id, $temp_data ); 
			if ( !empty( $users_price_adjustment ) && 'no_amount' !== $users_price_adjustment ) {
				return $users_price_adjustment;
			}
		}
        $current_user_product_rule = '';
        if (is_array($this->individual_product_adjustment_for_users) && isset($this->individual_product_adjustment_for_users['users']) && in_array($current_user_id, $this->individual_product_adjustment_for_users['users']) && isset($product_price_adjustment_users[$current_user_id]) && isset($product_price_adjustment_users[$current_user_id]['role_price']) && $product_price_adjustment_users[$current_user_id]['role_price'] == 'on') {
            $current_user_product_rule = $product_price_adjustment_users[$current_user_id];
        }
        else if (is_array($this->individual_product_adjustment_roles) && in_array($this->current_user_role, $this->individual_product_adjustment_roles) && isset($product_price_adjustment_roles[$this->current_user_role]) && isset($product_price_adjustment_roles[$this->current_user_role]['role_price']) && $product_price_adjustment_roles[$this->current_user_role]['role_price'] == 'on') {
            $current_user_product_rule = $product_price_adjustment_roles[$this->current_user_role];
        }
        //individual product page price adjustment (discount/markup from settings page))
        $enforce_button_check_for_product = get_post_meta($pid, 'product_based_price_adjustment', true);
        if ($enforce_button_check_for_product == 'yes' && $current_user_product_rule) {
            
            
            if (!empty($current_user_product_rule['adjustment_price']) && is_numeric($current_user_product_rule['adjustment_price'])) {
                if(isset($current_user_product_rule['adj_prod_price_dis']) && $current_user_product_rule['adj_prod_price_dis'] == 'markup') {
                    $adjustment_value += (float) $current_user_product_rule['adjustment_price'];
                }
                else {
                    $adjustment_value -= (float) $current_user_product_rule['adjustment_price'];
                }
            }
            if (!empty($current_user_product_rule['adjustment_percent']) && is_numeric($current_user_product_rule['adjustment_percent'])) {
                if(isset($current_user_product_rule['adj_prod_percent_dis']) && $current_user_product_rule['adj_prod_percent_dis'] == 'markup') {
                    $adjustment_value += $price * ((float) $current_user_product_rule['adjustment_percent']) / 100;
                }
                else {
                    $adjustment_value -= $price * ((float) $current_user_product_rule['adjustment_percent']) / 100;
                }
            }
            //discount/markup ajustment to $price
           return $adjustment_value;
        }
        return 'no_amount';
    }
    function elex_rp_get_adjustment_amount($price, $prdct_id, $temp_data, $adjustment_value) {
        $common_price_adjustment_table = get_option('eh_pricing_discount_price_adjustment_options', array());
        $current_user_id = $this->current_user_id;
		$multiple_role_option = get_option('eh_pricing_discount_multiple_role_price');
		$multiple_roles=$this->multiple_users;
        $rule_satisfied = false;
        $index = 0;
		$length = !empty( $common_price_adjustment_table ) ? count( $common_price_adjustment_table ) : 0;
		//delete same user role value
		if ( !empty( $common_price_adjustment_table ) && is_array( $common_price_adjustment_table ) ) {
		foreach ( $common_price_adjustment_table as $key => $value ) {
			
			if ( array_key_exists( $key, $common_price_adjustment_table ) && is_numeric( $length ) && is_numeric( $key ) ) {
				$j = 0;
				for ( $j = $key + 1; $j <= $length; $j++ ) { 
					if ( array_key_exists( $j, $common_price_adjustment_table ) ) {
						if ( isset( $common_price_adjustment_table[ $key ]['roles'] ) && isset( $common_price_adjustment_table[ $j ]['roles'] ) && ( isset( $value['role_price'] ) && 'on' === $value['role_price'] ) && ( isset( $common_price_adjustment_table[ $j ]['role_price'] ) && 'on' === $common_price_adjustment_table[ $j ]['role_price'] ) && ! isset( $common_price_adjustment_table[ $key ]['users'] ) && ! isset( $common_price_adjustment_table[ $j ]['users'] ) && ! ( isset( $common_price_adjustment_table[ $key ]['category'] ) && isset( $common_price_adjustment_table[ $j ]['category'] ) ) ) {
			
							if ( $common_price_adjustment_table[ $key ]['roles'] === $common_price_adjustment_table[ $j ]['roles'] ) {
								unset( $common_price_adjustment_table[ $j ] ); 
							} elseif ( isset( $common_price_adjustment_table[ $j ]['roles'] ) && ! empty( $common_price_adjustment_table[ $j ]['roles'] ) ) {
								foreach ( $common_price_adjustment_table[ $j ]['roles'] as $index => $role_val ) {
									if ( in_array( $role_val, $common_price_adjustment_table[ $key ]['roles'] ) ) {
										unset( $common_price_adjustment_table[ $j ]['roles'][ $index ] );
									}
								}
							}           
						} elseif ( ! isset( $common_price_adjustment_table[ $key ]['users'] ) && ! isset( $common_price_adjustment_table[ $j ]['users'] ) && isset( $common_price_adjustment_table[ $key ]['roles'] ) && isset( $common_price_adjustment_table[ $j ]['roles'] ) && $common_price_adjustment_table[ $key ]['roles'] === $common_price_adjustment_table[ $j ]['roles'] && ( isset( $common_price_adjustment_table[ $key ]['category'] ) && isset( $common_price_adjustment_table[ $j ]['category'] ) ) ) {
							foreach ( $common_price_adjustment_table[ $j ]['category'] as $index => $value ) {
								if ( in_array( $value, $common_price_adjustment_table[ $key ]['category'] ) ) {
									unset( $common_price_adjustment_table[ $j ]['category'][ $index ] );
								}
							}       
						} elseif ( isset( $common_price_adjustment_table[ $key ]['users'] ) && isset( $common_price_adjustment_table[ $j ]['users'] ) && $common_price_adjustment_table[ $key ]['users'] === $common_price_adjustment_table[ $j ]['users'] && isset( $common_price_adjustment_table[ $key ]['roles'] ) && isset( $common_price_adjustment_table[ $j ]['roles'] ) && $common_price_adjustment_table[ $key ]['roles'] === $common_price_adjustment_table[ $j ]['roles'] && ( isset( $common_price_adjustment_table[ $key ]['category'] ) && isset( $common_price_adjustment_table[ $j ]['category'] ) ) ) {
							foreach ( $common_price_adjustment_table[ $j ]['category'] as $index => $value ) {
								if ( in_array( $value, $common_price_adjustment_table[ $key ]['category'] ) ) {
									unset( $common_price_adjustment_table[ $j ]['category'][ $index ] );
								}
							}
						}
					}  
				}
			}
		}
		//If multiple roles present so apply discount according to option given
		$count_multiple_role = count( $multiple_roles );
		$role_value = array();

		// Store all the prices/percentages discount/markup of a specific user in an array.
		$roles_users_adjustment_prices_array = array();
		$roles_users_adjustment_percentages_array = array();
	
		
		
		
		
		foreach ( $common_price_adjustment_table as $key => $value ) {
			foreach ( $multiple_roles as $multiple_role_key => $multiple_role_val ) {
		
				if ( ( isset( $value['roles'] ) && in_array( $multiple_role_val, $value['roles'] ) ) 
					|| 
					( isset( $value['users'] ) && in_array( $current_user_id, $value['users'] ) )  
					) { // Apply price adjustment to applicable users or roles only.
							
					$current_user_product_rule = $common_price_adjustment_table[ $key ];
					
					if ( ! empty( $multiple_role_option ) && ! empty( $value['users'] ) && ! isset( $value['roles'] ) ) {
						// Adjustment on user is given priority over user role. So modify and return early.
						// User customization neglects above rules.
						// Here user and user role will have equal priority.
						if ( isset( $value['users'] ) && in_array( $current_user_id, $value['users'] ) && ! empty( $value['adjustment_price'] ) && isset( $value['role_price'] ) && 'on' === $value['role_price'] ) {
							$adjustment_value = $this->elex_rp_adjust_price_for_user_roles( $prdct_id, $current_user_product_rule, $temp_data, $adjustment_value );
							$price += $adjustment_value;
							return $price;
						} elseif ( isset( $value['users'] ) && in_array( $current_user_id, $value['users'] ) && ! empty( $value['adjustment_percent'] ) && isset( $value['role_price'] ) && 'on' === $value['role_price'] ) {
							$adjustment_value = $this->elex_rp_adjust_percent_for_user_roles( $prdct_id, $current_user_product_rule, $price, $temp_data, $adjustment_value );
							$price += $adjustment_value;
							return $price;
						}                       
					} else {

						if ( isset( $value['roles'] ) && ! empty( $value['adjustment_price'] ) && isset( $value['role_price'] ) && 'on' === $value['role_price'] ) {
						  array_push( $roles_users_adjustment_prices_array, $value['adjustment_price'] ); 
						  break;
						} elseif ( isset( $value['roles'] ) && ! empty( $value['adjustment_percent'] ) && isset( $value['role_price'] ) && 'on' === $value['role_price'] ) {
							array_push( $roles_users_adjustment_percentages_array, $value['adjustment_percent'] );
							break;
						}                       
					}
				}
			}
		}
	   
		$min_role_percentage_val = 0;
		$max_role_percentage_val = 0;
		$sum_role_percentage_val = 0;

		$min_role_price_val = 0;
		$max_role_price_val = 0;
		$sum_role_price_val = 0;


		if ( is_array( $roles_users_adjustment_prices_array ) && ! empty( $roles_users_adjustment_prices_array ) ) {
			$min_role_price_val = min( $roles_users_adjustment_prices_array );
			$max_role_price_val = max( $roles_users_adjustment_prices_array );
			$sum_role_price_val = array_sum( $roles_users_adjustment_prices_array );
		}
		if ( is_array( $roles_users_adjustment_percentages_array ) && ! empty( $roles_users_adjustment_percentages_array ) ) {
			$min_role_percentage_val = min( $roles_users_adjustment_percentages_array );
			$max_role_percentage_val = max( $roles_users_adjustment_percentages_array );
			$sum_role_percentage_val = array_sum( $roles_users_adjustment_percentages_array );
		}

		if ( $sum_role_percentage_val > 100 ) {
			$roles_users_adjustment_percentages_array = array( 100 ); // Max discount that can be applied to a product is 100%.
		}

		foreach ( $common_price_adjustment_table as $key => $value ) {
			foreach ( $multiple_roles as $multiple_role_key => $multiple_role_val ) {
				if ( isset( $common_price_adjustment_table[ $key ] ) && ( ( isset( $value['roles'] ) && in_array( $multiple_role_val, $value['roles'] ) ) || ( isset( $value['users'] ) && in_array( get_current_user_id(), $value['users'] ) ) ) ) { // Price Adjustment applicable to selected users/roles only.
				$current_user_product_rule = $common_price_adjustment_table[ $key ];
					if ( isset( $current_user_product_rule['role_price'] ) && 'on' === $current_user_product_rule['role_price'] ) {
					
						if ( ! empty( $current_user_product_rule['adjustment_price'] ) && is_numeric( $current_user_product_rule['adjustment_price'] ) ) {
							if ( 'consolidate_price' === $multiple_role_option ) {
								// $current_user_product_rule['adjustment_price'] = $sum_role_price_val;
								// Iterate through all rules and apply them one by one
								$adjustment_value = $this->elex_rp_adjust_price_for_user_roles( $prdct_id, $current_user_product_rule, $temp_data, $adjustment_value );
								$price += $adjustment_value;
								break;
								
							} elseif ( 'max_role_price' === $multiple_role_option ) {
								$current_user_product_rule['adjustment_price'] = $max_role_price_val;
								$adjustment_value = $this->elex_rp_adjust_price_for_user_roles( $prdct_id, $current_user_product_rule, $temp_data, $adjustment_value );
								$price += $adjustment_value;
								return $price;
							} elseif ( 'min_role_price' === $multiple_role_option ) {
							
								$current_user_product_rule['adjustment_price'] = $min_role_price_val;
								$adjustment_value = $this->elex_rp_adjust_price_for_user_roles( $prdct_id, $current_user_product_rule, $temp_data, $adjustment_value );
								$price += $adjustment_value;
								return $price;
							}                       
						}

						if ( ! empty( $current_user_product_rule['adjustment_percent'] ) && is_numeric( $current_user_product_rule['adjustment_percent'] ) ) {
							
							if ( 'consolidate_price' === $multiple_role_option ) {
								// Iterate through all rules and apply them one by one.
								$adjustment_value = $this->elex_rp_adjust_percent_for_user_roles( $prdct_id, $current_user_product_rule, $price, $temp_data, $adjustment_value );
								$price += $adjustment_value;
								break;  
							} elseif ( 'max_role_price' === $multiple_role_option ) {
								$current_user_product_rule['adjustment_percent'] = $max_role_percentage_val;
								$adjustment_value = $this->elex_rp_adjust_percent_for_user_roles( $prdct_id, $current_user_product_rule, $price, $temp_data, $adjustment_value );
								$price += $adjustment_value;
								return $price;
							} elseif ( 'min_role_price' === $multiple_role_option ) {
								$current_user_product_rule['adjustment_percent'] = $min_role_percentage_val;
								$adjustment_value = $this->elex_rp_adjust_percent_for_user_roles( $prdct_id, $current_user_product_rule, $price, $temp_data, $adjustment_value );                   
								$price += $adjustment_value;
								return $price;
							}  
						}
					}
				}
			}
		}
	}
     
        return $price;
    }
    function elex_rp_cat_snippet_to_give_discount_different_category($categ_disc,$prdct_id,$adjustment_value,$price) {
        $cat_keys = array_keys($categ_disc);
        $res_arr = array_intersect($prdct_id, $cat_keys);
        if (!empty($res_arr)) {
            foreach ($categ_disc[current($res_arr)] as $key => $details) {
                if ($this->current_user_role == $details[0]) {
                    if ($details[1] != '') {
                        if ($details[3] == 'markup') {
                            $adjustment_value += (float) $details[1];
                        }
                        if ($details[3] == 'discount') {
                            $adjustment_value -= (float) $details[1];
                        }
                    }
                    if ($details[2] != '') {
                        if ($details[3] == 'markup') {
                            $adjustment_value += $price * ((float) $details[2]) / 100;
                        }
                        if ($details[3] == 'discount') {
                            $adjustment_value -= $price * ((float) $details[2]) / 100;
                        }
                    }
                    break;
                }
            }
        }
        return $adjustment_value;
    }

    function elex_rp_adjust_percent_for_user_roles ($prdct_id,$current_user_product_rule,$price,$temp_data,$adjustment_value) {
        if (isset($current_user_product_rule['category'])) {
            $cat_display = $current_user_product_rule['category'];
            if ($temp_data != 'grouped')
                $result_chk = array_intersect($prdct_id, $cat_display);
            if (empty($result_chk)) {
                $adjustment_value = 0;
            } else {
                if(isset($current_user_product_rule['adj_percent_dis']) && $current_user_product_rule['adj_percent_dis'] == 'markup') {
                    $adjustment_value = 0;
					$this->is_markup = true;
                    $adjustment_value += $price * ((float) $current_user_product_rule['adjustment_percent']) / 100;
                }
                else {
					$adjustment_value = 0;
                    $this->is_markup = false;
                    $adjustment_value -= $price * ((float) $current_user_product_rule['adjustment_percent']) / 100;
                }
            }
        } else {
            if(isset($current_user_product_rule['adj_percent_dis']) && $current_user_product_rule['adj_percent_dis'] == 'markup') {
                $adjustment_value = 0;
				$this->is_markup = true;
                $adjustment_value += $price * ((float) $current_user_product_rule['adjustment_percent']) / 100;
            }
            else {
				$adjustment_value = 0;
                $this->is_markup = false;
                $adjustment_value -= $price * ((float) $current_user_product_rule['adjustment_percent']) / 100;
            }
        }
        return $adjustment_value;
    }
    
    function elex_rp_adjust_price_for_user_roles($prdct_id,$current_user_product_rule,$temp_data,$adjustment_value) {
        if (isset($current_user_product_rule['category'])) {
			
            $cat_display = $current_user_product_rule['category'];
            if ($temp_data != 'grouped')
                $result_chk = array_intersect($prdct_id, $cat_display);
            if (empty($result_chk)) {
                $adjustment_value = 0;
            } else {
                if(isset($current_user_product_rule['adj_price_dis']) && $current_user_product_rule['adj_price_dis'] == 'markup') {
					$adjustment_value = 0;
					$this->is_markup = true;
                    $adjustment_value += (float) $current_user_product_rule['adjustment_price'];
                }
                else {
					$adjustment_value = 0;
                    $this->is_markup = false;	
                    $adjustment_value -= (float) $current_user_product_rule['adjustment_price'];
                }
            }
        } else {
                if(isset($current_user_product_rule['adj_price_dis']) && $current_user_product_rule['adj_price_dis'] == 'markup') {
					$adjustment_value = 0;
					$this->is_markup = true;
                    $adjustment_value += (float) $current_user_product_rule['adjustment_price'];
                }
                else {
					$adjustment_value = 0;
                    $this->is_markup = false;

                    $adjustment_value -= (float) $current_user_product_rule['adjustment_price'];
                }
        }
	
        return $adjustment_value;
        
    }

    public function elex_rp_get_price_html($price, $product) {
        $reg_price = get_post_meta( get_the_ID(), '_regular_price', true);
        if(! $product->is_purchasable()) {
           if ($this->elex_rp_is_hide_price($product)) {
                if ($this->elex_rp_is_price_hidden_in_product_meta($product)) {
                    $price = $this->elex_rp_get_placeholder_text_product_hide_price($product);
                } else {
                    $price = $this->elex_rp_get_placeholder_text($product, $price);
                }
            }
            return $price;
        }
        if ($this->elex_rp_get_product_type($product) == 'simple' || $this->elex_rp_get_product_type($product) == 'variation') {
            $discount_price=$product->get_regular_price();
            $variation_id = $product->get_id();
            $var_reg_price = get_post_meta($variation_id , '_regular_price', true);
            $temp_data = $this->elex_rp_get_product_type($product);
            $current_user = wp_get_current_user();
            if($temp_data=='variation'){
                $pid = $variation_id;
            }
            else{
                $pid = $this->elex_rp_get_product_id($product); 
            }
			$current_user_role = $this->current_user_role;
			// bug fix customization for user multiple role plugin.
			if (ELEX_USER_MULTIPLE_ROLE_STATUS) {
				foreach($this->individual_product_adjustment_roles as $key=>$val){
					if(in_array($val, wp_get_current_user()->roles)){
						$product_users_role_price = get_post_meta($pid, 'product_role_based_price_'.$val);
					
						if (is_array($product_users_role_price ) && isset($product_users_role_price [0]) && !empty($product_users_role_price [0])) {
							$current_user_role = $val;
							$product_user_price = get_post_meta($pid, 'product_role_based_price_'.$val);
							break;
						}
					}
                }
		    }
            $product_users_price = get_post_meta($pid, 'product_role_based_price_user_'. $current_user->user_email);
            $product_user_role_price = get_post_meta($pid, 'product_role_based_price_'.$this->current_user_role);
            if (is_array($product_users_price) && isset($product_users_price[0]) && !empty($product_users_price[0])) {
                $product_user_price = $product_users_price;
            }
            else if (is_array($product_user_role_price) && isset($product_user_role_price[0]) && !empty($product_user_role_price[0])) {
                $product_user_price = $product_user_role_price;
            }
            if (!empty($product_user_price)) {	
                if ($current_user_role) {
                    $product_user_price_value = $product_user_price;
                    if (is_numeric($product_user_price_value[0])) {	
						if($this->elex_rp_get_product_type($product) == 'simple'  && $reg_price>$product->get_price() && $this->elex_rp_is_hide_regular_price($product) == false){
							$price = wc_format_sale_price(wc_get_price_to_display($product, array('price' => $reg_price)),wc_get_price_to_display($product)) . $product->get_price_suffix();  
						}
						else if($this->elex_rp_get_product_type($product) == 'variation'  && $var_reg_price>$product->get_price() && $this->elex_rp_is_hide_regular_price($product) == false){
							$price = wc_format_sale_price(wc_get_price_to_display($product, array('price' => $var_reg_price)),wc_get_price_to_display($product)) . $product->get_price_suffix();  
						}
						else{
							$price = wc_price(wc_get_price_to_display($product)) . $product->get_price_suffix();
						}
					}
                }
            }
            elseif ($product->is_on_sale() && $this->elex_rp_is_hide_regular_price($product) === false) {
				
                $price = wc_format_sale_price(wc_get_price_to_display($product, array('price' => $product->get_regular_price())), wc_get_price_to_display($product)) . $product->get_price_suffix();
            } else {
                if(($this->sales_method == 'regular') && ($discount_price != $reg_price) && ($discount_price < $reg_price) &&( $this->elex_rp_get_product_type($product) == 'simple') && $this->elex_rp_is_hide_regular_price($product) == false){
                    $price = wc_format_sale_price(wc_get_price_to_display($product, array('price' => $reg_price)), wc_get_price_to_display($product, array('price' => $product->get_regular_price()))) . $product->get_price_suffix();
                   }
                else if(($this->sales_method == 'regular') && ($discount_price != $var_reg_price) && ($discount_price < $var_reg_price) && ($this->elex_rp_get_product_type($product) == 'variation' )  && $this->elex_rp_is_hide_regular_price($product) == false){
                    $price=wc_format_sale_price(wc_get_price_to_display($product, array('price' => $var_reg_price)), wc_get_price_to_display($product, array('price' => $product->get_regular_price()))) . $product->get_price_suffix();
                   }
                   else{
                $price = wc_price(wc_get_price_to_display($product)) . $product->get_price_suffix();
                   }
            }
            if ($this->elex_rp_is_hide_price($product)) {
                if ($this->elex_rp_is_price_hidden_in_product_meta($product)) {
                    $price = $this->elex_rp_get_placeholder_text_product_hide_price($product);
                } else {
                    $price = $this->elex_rp_get_placeholder_text($product, $price);
                }
            }
        } elseif ($this->elex_rp_get_product_type($product) == 'variable') {
            $prices = $product->get_variation_prices(true);
            if (empty($prices['price'])) {
                return $price;
            }
            foreach ($prices['price'] as $pid => $old_price) {
                $pobj = wc_get_product($pid);
                $prices['price'][$pid] = wc_get_price_to_display($pobj);
            }
            asort($prices['price']);
            asort($prices['regular_price']);
            $min_price = current($prices['price']);
            $max_price = end($prices['price']);

            //if variation prices are same and role price is set so don't show strike out price
            if($min_price == $max_price){
                $product_id = $product->get_id();
                $product = wc_get_product($product_id);
                $current_products = $product->get_children();
                $current_user = wp_get_current_user();
                foreach( $current_products as $key =>$value){
                    $product_users_price = get_post_meta($value, 'product_role_based_price_user_'. $current_user->user_email);
                    $product_user_role_price = get_post_meta($value, 'product_role_based_price_'.$this->current_user_role); 
                    if (is_array($product_users_price) && isset($product_users_price[0]) && !empty($product_users_price[0])) {
                        $product_user_price = $product_users_price;
                    }
                    else if (is_array($product_user_role_price) && isset($product_user_role_price[0]) && !empty($product_user_role_price[0])) {
                        $product_user_price = $product_user_role_price;
                    }
                }
            }
               
            if($min_price == $max_price && !empty($product_user_price[0])){
                $price = wc_price($max_price) . $product->get_price_suffix(); 
            }elseif($min_price !== $max_price) {
                $price = wc_format_price_range($min_price, $max_price) . $product->get_price_suffix();
            } else {
                $count = 0;
                $product_id = $product->get_id();
                $adjustment_value = current($prices['regular_price']);
                if ($this->sales_method == 'regular_sale' && is_array($this->individual_product_adjustment_roles) && in_array($this->current_user_role, $this->individual_product_adjustment_roles)) {
                    //individual product page price adjustment (discount/markup from settings page))
                    $enforce_button_check_for_product = get_post_meta($product_id, 'product_based_price_adjustment', true);
                    $product_price_adjustment = get_post_meta($product_id, 'product_price_adjustment', true);
                    if ($enforce_button_check_for_product == 'yes' && isset($product_price_adjustment[$this->current_user_role]) && isset($product_price_adjustment[$this->current_user_role]['role_price']) && $product_price_adjustment[$this->current_user_role]['role_price'] == 'on') {
                        $current_user_product_rule = $product_price_adjustment[$this->current_user_role];
                        $count = 1;
                        if (!empty($current_user_product_rule['adjustment_price']) && is_numeric($current_user_product_rule['adjustment_price'])) {
                            if (isset($current_user_product_rule['adj_prod_price_dis']) && $current_user_product_rule['adj_prod_price_dis'] == 'markup') {
                                $adjustment_value += (float) $current_user_product_rule['adjustment_price'];
                            } else {
                                $adjustment_value -= (float) $current_user_product_rule['adjustment_price'];
                            }
                        }

                        if (!empty($current_user_product_rule['adjustment_percent']) && is_numeric($current_user_product_rule['adjustment_percent'])) {
                            if (isset($current_user_product_rule['adj_prod_percent_dis']) && $current_user_product_rule['adj_prod_percent_dis'] == 'markup') {
                                $adjustment_value += $adjustment_value * ((float) $current_user_product_rule['adjustment_percent']) / 100;
                            } else {

                                $adjustment_value -= $adjustment_value * ((float) $current_user_product_rule['adjustment_percent']) / 100;
                            }
                        }
                    }
                }
                $prdct_id = $this->elex_rp_get_product_category_using_id($product_id);

                $common_price_adjustment_table = get_option('eh_pricing_discount_price_adjustment_options', array());
				if( !empty( $common_price_adjustment_table ) && is_array( $common_price_adjustment_table ) ){
					foreach ($common_price_adjustment_table as $key => $value) {

						if (!$count && isset($value['category'])) {
							$cat_display = $value['category'];
							$result_chk = array_intersect($prdct_id, $cat_display);
							if (empty($result_chk)) {
								$count = 1;
							}
						}
						if (!$count && $this->sales_method == 'regular_sale' || $this->sales_method == 'regular' && in_array($this->current_user_role, $value['roles']) && isset($value['role_price']) && $value['role_price'] == 'on') {
							$index = $key;
							$current_user_product_rule = $common_price_adjustment_table[$index];
							if (!empty($current_user_product_rule['adjustment_price']) && is_numeric($current_user_product_rule['adjustment_price'])) {
								$adjustment_value = $this->elex_rp_adjust_price_for_user_roles($prdct_id, $current_user_product_rule, 'variable', current($prices['regular_price']));
							}
							if (!empty($current_user_product_rule['adjustment_percent']) && is_numeric($current_user_product_rule['adjustment_percent'])) {
								$adjustment_value = $this->elex_rp_adjust_percent_for_user_roles($prdct_id, $current_user_product_rule, current($prices['regular_price']), 'variable', $adjustment_value);
							}
						}
					}
				}
                
                    if (current($prices['price']) >= $adjustment_value || $this->elex_rp_is_hide_regular_price($product) || current($prices['regular_price']) != end($prices['regular_price'])) {
                        $price = wc_price(current($prices['price'])) . $product->get_price_suffix();
                    } elseif($this->sales_method == 'regular' && !empty(current($prices['sale_price'])) && $adjustment_value !=current($prices['sale_price'])){ 
                        $price = wc_format_sale_price($adjustment_value, current($prices['sale_price'])) . $product->get_price_suffix();
                    }else{  
                        $price = wc_format_sale_price($adjustment_value, current($prices['price'])) . $product->get_price_suffix();
                    }
                        
                }
            if ($this->elex_rp_is_hide_price($product)) {
                if ($this->elex_rp_is_price_hidden_in_product_meta($product)) {
                    $price = $this->elex_rp_get_placeholder_text_product_hide_price($product);
                } else {
                    $price = $this->elex_rp_get_placeholder_text($product, $price);
                }
            }
        } elseif ($this->elex_rp_get_product_type($product) == 'grouped') {
            if ($this->elex_rp_is_hide_price($product)) {
                if ($this->elex_rp_is_price_hidden_in_product_meta($product)) {
                    $price = $this->elex_rp_get_placeholder_text_product_hide_price($product);
                } else {
                    $price = $this->elex_rp_get_placeholder_text($product, $price);
                }
            } else {
                $child_prices = array();
                foreach ($product->get_children() as $child_id) {
                    $child = wc_get_product($child_id);
                    if ($child->is_type('variable')) {
                        $prices = $child->get_variation_prices(true);

                        if (empty($prices['price'])) {
                            return '';
                        }
                        foreach ($prices['price'] as $pid => $old_price) {
                            $prices['price'][$pid] = wc_get_price_to_display(wc_get_product($pid));
                        }
                        asort($prices['price']);
                        $min_price = current($prices['price']);
                        $child_prices[] = $min_price;
                    } else {
                        if (!$this->elex_rp_is_hide_price($child)) {
                            $child_prices[] = wc_get_price_to_display($child);
                        }
                    }
                }
                if (!empty($child_prices)) {
                    $min_price = min($child_prices);
                    $max_price = max($child_prices);
                } else {
                    $min_price = '';
                    $max_price = '';
                }

                if ('' !== $min_price) {
                    $price = $min_price !== $max_price ? sprintf(_x('%1$s&ndash;%2$s', 'Price range: from-to', 'woocommerce'), wc_price($min_price), wc_price($max_price)) : wc_price($min_price);
                    $is_free = ( 0 == $min_price && 0 == $max_price );

                    if ($is_free) {
                        $price = apply_filters('woocommerce_grouped_free_price_html', __('String(Free!)', 'woocommerce'), $product);
                    } else {
                        $price = apply_filters('woocommerce_grouped_price_html', $price . $product->get_price_suffix(), $product, $child_prices);
                    }
                } else {
                    $price = apply_filters('woocommerce_grouped_empty_price_html', '', $product);
                }
            }
        }
        
        return apply_filters('eh_pricing_adjustment_modfiy_price', $this->elex_rp_pricing_add_price_suffix($price, $product), $this->current_user_role);
    }

    function elex_rp_is_hide_regular_price($product) {
        $hide = false;
		$product_id = $this->elex_rp_get_product_id($product);
        $temp_data = $this->elex_rp_get_product_type($product);
        if ($temp_data == 'variation') {
            $product_id = $this->elex_rp_get_product_parent_id($product);
        }
        if (!is_user_logged_in()) {
			$remove_individual_product_regular_price_unregistered = get_post_meta ( $product_id, 'product_adjustment_hide_regular_price_unregistered', true);
			if(get_option('eh_pricing_discount_hide_regular_price_unregistered', 'no') == 'yes' || $remove_individual_product_regular_price_unregistered == 'yes' ){
				$hide = true;
			}
        } else {
            $remove_settings_regular_price_roles = get_option('eh_pricing_discount_regular_price_user_role', array());
			$remove_individual_product_regular_price_roles = get_post_meta ( $product_id, 'eh_pricing_adjustment_product_hide_regular_price_user_role', true);
			if (is_array($remove_individual_product_regular_price_roles) && in_array($this->current_user_role, $remove_individual_product_regular_price_roles)) {
                $hide = true;
            }
            if (is_array($remove_settings_regular_price_roles) && in_array($this->current_user_role, $remove_settings_regular_price_roles)) {
                $hide = true;
            }
        }
        return $hide;
    }

    function elex_rp_is_hide_price($product) {
        $hide = false;
        $product_id = $this->elex_rp_get_product_id($product);
        $temp_data = $this->elex_rp_get_product_type($product);
        if ($temp_data == 'variation') {
            $product_id = $this->elex_rp_get_product_parent_id($product);
        }
        if('yes' == get_post_meta($product_id, 'product_adjustment_hide_price_catalog', true)){
            if(! (get_post_meta($product_id, 'product_adjustment_exclude_admin_catalog', true) == 'yes' && $this->current_user_role =='administrator')) {
                   $hide = true;
            } else {
                $hide = false;
            }
        }
        elseif (get_option('eh_pricing_discount_price_catalog_mode') == 'yes') {
            if(get_option('eh_pricing_discount_price_catalog_mode_exclude_admin') == 'yes' && $this->current_user_role =='administrator') {
            $hide = false;
            }
            else
                $hide = true;
        }
        elseif (is_user_logged_in()) {
            $remove_settings_price_roles = get_option('eh_pricing_discount_price_user_role', array());
            $remove_product_price_roles = get_post_meta($product_id, 'eh_pricing_adjustment_product_price_user_role', true);
			if (is_array($remove_product_price_roles) && in_array($this->current_user_role, $remove_product_price_roles)) {
                $hide = true;
			}
            elseif (is_array($remove_settings_price_roles) && in_array($this->current_user_role, $remove_settings_price_roles)) {
                $hide = true;
            }
           
        } 
		else {
            $remove_product_price_roles = get_post_meta($product_id, 'product_adjustment_hide_price_unregistered', true);
            if (get_option('eh_pricing_discount_price_unregistered_user') == 'yes' || $remove_product_price_roles == 'yes') {
                $hide = true;
            }
        }
        return $hide;
    }


    public function elex_rp_is_product_purchasable($is_purchasable, $product) {
        if ($this->elex_rp_is_hide_price($product) === true || !$is_purchasable) {
            return false;
        } else {
            return true;
        }
    }

    function elex_rp_is_price_hidden_in_product_meta($product) {
        $product_id = $this->elex_rp_get_product_id($product);

        if ($this->elex_rp_get_product_type($product) == 'variation') {
            $product_id = $this->elex_rp_get_product_parent_id($product);
        }
		if('yes' == get_post_meta( $product_id, 'product_adjustment_hide_price_catalog', true)){
			return true;
		}
        elseif (is_user_logged_in()) {
            $remove_product_price_roles = get_post_meta($product_id, 'eh_pricing_adjustment_product_price_user_role', true);
            if (is_array($remove_product_price_roles) && in_array($this->current_user_role, $remove_product_price_roles)) {
                return true;
            } else {
                return false;
            }
        } else {
            $remove_product_price_roles = get_post_meta($product_id, 'product_adjustment_hide_price_unregistered', true);
            if ($remove_product_price_roles == 'yes') {
                return true;
            } else {
                return false;
            }
        }
    }

    function elex_rp_get_placeholder_text($product, $price) {
        $placeholder = '';
        $product_id = $this->elex_rp_get_product_id($product);
        if ($this->elex_rp_is_hide_price($product) == true) {
           
            if (get_option('eh_pricing_discount_price_catalog_mode') == 'yes') {
                $placeholder = get_option('eh_pricing_discount_price_catalog_mode_text');
            }
           
            elseif (is_user_logged_in()) {
                $placeholder = get_option('eh_pricing_discount_price_user_role_text');
            } else {
                $placeholder = get_option('eh_pricing_discount_price_unregistered_user_text');
            }
            $placeholder = $this->elex_rp_return_wpml_string($placeholder, 'Price placeholder - Global');
            return $placeholder;
        } else {
            return $price;
        }
    }
    function elex_rp_get_placeholder_text_product_hide_price($product) {
        $placeholder = '';
        $prod_id = $this->elex_rp_get_product_id($product);
			if('yes' == get_post_meta($prod_id, 'product_adjustment_hide_price_catalog', true)){
				$placeholder = get_post_meta($prod_id, 'product_adjustment_hide_price_placeholder_catalog', true);
			}
            elseif (is_user_logged_in()) {
                $placeholder = get_post_meta($prod_id, 'product_adjustment_hide_price_placeholder_role',true);
            } else {
                $placeholder = get_post_meta($prod_id, 'product_adjustment_hide_price_placeholder_unregistered',true);
            }
            $placeholder = $this->elex_rp_return_wpml_string($placeholder, 'Price placeholder - Individual');
			return $placeholder;
        
    }
    function elex_rp_return_wpml_string($string_to_translate, $name){
        do_action( 'wpml_register_single_string', 'elex-catmode-rolebased-price', $name, $string_to_translate );
        $ret_string = apply_filters('wpml_translate_single_string', $string_to_translate, 'elex-catmode-rolebased-price', $name );
        return $ret_string;
    }

    function elex_rp_get_product_type($product) {
        if (empty($product)) {
            return 'not a valid object';
        }
        if (WC()->version < '2.7.0') {
            $product_type = $product->product_type;
        } else {
            $product_type = $product->get_type();
        }
        return $product_type;
    }

    function elex_rp_get_product_id($product) {
        if (empty($product)) {
            return 'not a valid object';
        }
        if (WC()->version < '2.7.0') {
            $product_id = $product->post->id;
        } else {
            $product_id = $product->get_id();
        }
        return $product_id;
    }

    function elex_rp_get_product_parent_id($product) {
        if (empty($product)) {
            return 'not a valid object';
        }
        if (WC()->version < '2.7.0') {
            $product_parent_id = $product->parent->id;
        } else {
            $product_parent_id = $product->get_parent_id();
        }
        return $product_parent_id;
    }

    //function to add price suffix
    public function elex_rp_pricing_add_price_suffix($price, $product) {
        $price_suffix;
        if ($this->price_suffix_option == 'general') {
            $price_suffix = ' <small class="woocommerce-price-suffix">' . $this->general_price_suffix . '</small>';
        } else if ($this->price_suffix_option == 'role_specific') {
            $user_role;
            if (is_user_logged_in()) {
                $user_role = $this->price_suffix_user_role;
            } else {
                $user_role = 'unregistered_user';
            }
            if (is_array($this->role_price_suffix) && key_exists($user_role, $this->role_price_suffix) && isset($this->role_price_suffix[$user_role]['price_suffix']) && $this->role_price_suffix[$user_role]['price_suffix'] != '') {
                $price_suffix = ' <small class="woocommerce-price-suffix">' . $this->role_price_suffix[$user_role]['price_suffix'] . '</small>';
            }
        }
        if (!empty($price_suffix) && $this->elex_rp_is_hide_price($product) === false) {

            $find = array(
                '{price_including_tax}',
                '{price_excluding_tax}'
            );
            $replace = array(
                wc_price((WC()->version < '2.7.0') ? $product->get_price_including_tax() : wc_get_price_including_tax($product)),
                wc_price((WC()->version < '2.7.0') ? $product->get_price_excluding_tax() : wc_get_price_excluding_tax($product))
            );
            $price_suffix = str_replace($find, $replace, $price_suffix);
            $price .= $price_suffix;
        }
        return $price;
    }

}

new Elex_Price_Discount_Admin();
