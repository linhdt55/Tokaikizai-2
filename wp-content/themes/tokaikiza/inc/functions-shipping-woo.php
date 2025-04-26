<?php

/**
 * Style admin
 */
add_action('admin_head', 'admin_style_custom');
function admin_style_custom()
{
	echo '<style>
	.woocommerce table.form-table {
		z-index: 1;
	}
  </style>';
}
/**
 * Settings woocommerce shipping
 */
add_action('admin_menu', 'woocommerce_shipping_admin_add_page');
function woocommerce_shipping_admin_add_page()
{
	add_submenu_page('woocommerce', 'Setting Extra', 'Setting Extra', 'manage_options', 'woo-shipping-submenu-page', 'shipping_woocommerce_options');
}
function shipping_woocommerce_register_settings()
{
	add_option('delivery_shipping_locations');
	register_setting('woocommerce_shipping_options_group', 'delivery_shipping_locations', 'wooshipping_callback');
	
	add_option('payment_method_oem');
	register_setting('woocommerce_shipping_options_group', 'payment_method_oem', 'wooshipping_callback');
}
add_action('admin_init', 'shipping_woocommerce_register_settings');

function shipping_woocommerce_options()
{
?>
	<div class="admin-setting">
		<h2><?php echo _e('Setting Delivery Shipping', 'tokaikiza'); ?></h2>
		<form method="post" action="options.php">
			<?php settings_fields('woocommerce_shipping_options_group'); ?>
			<div class="item">
				<p><strong><label for="footer_content_email_customer_order"><?php echo _e('Delivery To:', 'tokaikiza'); ?></label></strong></p>
				<?php
				$allowed_countries   = WC()->countries->get_shipping_countries();
				$shipping_continents = WC()->countries->get_shipping_continents();
				$locations = get_option('delivery_shipping_locations');
				?>
				<div class="item-field" style="max-width: 600px;">
					<select multiple="multiple" data-attribute="delivery_shipping_locations" id="delivery_shipping_locations" name="delivery_shipping_locations[]" data-placeholder="<?php esc_attr_e('Select delivery shipping', 'tokaikiza'); ?>" class="wc-shipping-zone-region-select chosen_select">
						<?php
						foreach ($shipping_continents as $continent_code => $continent) {
							echo '<option value="continent:' . esc_attr($continent_code) . '"' . wc_selected("continent:$continent_code", $locations) . '>' . esc_html__($continent['name'], 'tokaikiza') . '</option>';

							$countries = array_intersect(array_keys($allowed_countries), $continent['countries']);

							foreach ($countries as $country_code) {
								echo '<option value="country:' . esc_attr($country_code) . '"' . wc_selected("country:$country_code", $locations) . '>' . esc_html__('&nbsp;&nbsp; ' . $allowed_countries[$country_code], 'tokaikiza') . '</option>';

								$states = WC()->countries->get_states($country_code);
								if ($states) {
									foreach ($states as $state_code => $state_name) {
										echo '<option value="state:' . esc_attr($country_code . ':' . $state_code) . '"' . wc_selected("state:$country_code:$state_code", $locations) . '>' . esc_html__('&nbsp;&nbsp;&nbsp;&nbsp; ' . $state_name . ', ' . $allowed_countries[$country_code], 'tokaikiza') . '</option>';
									}
								}
							}
						}
						?>
					</select>
				</div>
			</div>
			<div class="item" style="max-width: 600px;">
				<h2><?php echo _e('Setting Payment for OEM products', 'tokaikiza'); ?></h2>
				<p><strong><label for="footer_content_email_customer_order"><?php echo _e('Hide Payment Method:', 'tokaikiza'); ?></label></strong></p>
				<select multiple="multiple" data-attribute="payment_method_oem" id="payment_method_oem" name="payment_method_oem[]" data-placeholder="<?php esc_attr_e('Select delivery shipping', 'tokaikiza'); ?>" class="wc-shipping-zone-region-select chosen_select">
					  <?php
						$available_gatewayz = WC()->payment_gateways->get_available_payment_gateways();
						// Chosen Method.
						$payment_method_oem = get_option('payment_method_oem');
						foreach ( $available_gatewayz as $gatewayz ) {
							$option = '<option value="' . esc_attr( $gatewayz->id)  . '" ';
							if(!empty($payment_method_oem)){
								if(in_array($gatewayz->id, $payment_method_oem)){
									$option .= 'selected="selected"';
								}
							}

							$option .= '>';
							$option .= wp_kses_post( $gatewayz->get_title() ) ;
							$option .= '</option>';
							echo $option;
						}
							
					  ?>
				</select>
			</div>
			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}

/**
 * Class Custom_Woo_Shipping_Class_Fields
 *
 * @package Woo_Add_Shipping_Class
 */

class Custom_Woo_Shipping_Class_Fields
{

	/**
	 * Initilizing hooks
	 *
	 * @return void
	 */
	public function run()
	{
		add_filter('woocommerce_shipping_classes_columns', array($this, 'display_field_name_in_list_header'), 11, 1);
		add_action('woocommerce_shipping_classes_column_must', array($this, 'display_field_view'), 10);
		add_action('woocommerce_shipping_classes_save_class', array($this, 'save_field_values'), 11, 2);
		add_filter('woocommerce_get_shipping_classes', array($this, 'modify_shipping_class_object'), 11, 1);
	}

	/**
	 * Adding New Columns to List Table's Header
	 *
	 * @param array $shipping_class_columns    Array of intial shipping class columns.
	 * @return array modified $shipping_class_columns    Array of modified shipping class columns.
	 */
	public function display_field_name_in_list_header($shipping_class_columns)
	{
		$shipping_class_columns['must'] = __('Must charge', 'must');
		//Uncomment the following two line to re-positioning Prodcut Count to the last
		unset($shipping_class_columns['wc-shipping-class-count']);
		$shipping_class_columns['wc-shipping-class-count'] = __('Product count', 'woocommerce');
		return $shipping_class_columns;
	}

	/**
	 * View for the added attribure
	 *
	 * @return void
	 */
	public function display_field_view()
	{
		/**
		 * The fields are rendered in the client side and `data` is a localized variable
		 * You can find it in //loc: woocommerce/assets/js/admin/wc-shipping-classes.js
		 * Localization code can be found in //loc: woocommerce/includes/admin/settings/views/html-admin-page-shipping-classes.php
		 */
		$current_action = current_filter();

		// cropping out the current action name to get only the field name.
		$field = str_replace('woocommerce_shipping_classes_column_', '', $current_action);

		switch ($field) {
			case 'must':
	?>
				<div class="view">{{ data.must }}</div>
				<div class="edit"><input type="text" name="must[{{ data.term_id }}]" data-attribute="must" value="{{ data.must }}" placeholder="<?php esc_attr_e('Enter: 0 or 1', 'tokaikiza'); ?>" /></div>
		<?php
				break;
			default:
				break;
		}
	}

	/**
	 * Save Update Fields Values to shipping class meta data
	 *
	 * @param int   $term_id   shipping class id.
	 * @param array $data   Data obtained from the frontend.
	 * @return void
	 */
	public function save_field_values($term_id, $data)
	{
		foreach ($data as $key => $value) {
			if (in_array($key, array('must'), true)) {
				update_term_meta($term_id, $key, $value);
			}
		}
	}


	/**
	 * Modify Shipping Class default data before localization.
	 * This will add the values of new fields from the databse to the view
	 *
	 * @param array $shipping_class   Array of shipping classes.
	 * @return array $classes Array of modified classes return as stdClass instead.
	 */
	public function modify_shipping_class_object($shipping_class)
	{
		$classes          = array();
		$class_new_fields = array('must');
		foreach ($shipping_class as $key => $class) {
			// convert shipping class object to array.
			$data = (array) $class;

			// add new field value to array.
			foreach ($class_new_fields as $meta_field) {
				$data[$meta_field] = get_term_meta($class->term_id, $meta_field, true);
			}

			// convert back to object format. But this makes a object of stdClass instead, which will also work.
			$classes[$key] = (object) $data;
		}
		return $classes;
	}
}
$Shipping_Class = new Custom_Woo_Shipping_Class_Fields();
$Shipping_Class->run();

/**
 * Woocommerce action hook
 */

add_action('woocommerce_single_product_summary', 'woocommerce_single_product_shipping_class', 10);
function woocommerce_single_product_shipping_class()
{
	global $product;

	$locations = get_option('delivery_shipping_locations');
	// Get the product shipping class WP_Term Object
	$term_class = get_term_by('slug', $product->get_shipping_class(), 'product_shipping_class');
	if (!empty($term_class) && !empty($locations)) {
		?>
		<div class="product-shipping">
			<div class="item item-delivery-shipping">
				<div class="label"><?php esc_attr_e('Delivery To:', 'tokaikiza'); ?></div>
				<div class="item-info">
					<select id="delivery-shipping" data-id="<?php echo $term_class->term_id; ?>" class="wc-shipping chosen_select">
						<option value=""><?php esc_attr_e('Select delivery', 'tokaikiza'); ?></option>
						<?php foreach ($locations as $country_code) { ?>
							<?php
							$states = explode(':', $country_code);
							$state_name = WC()->countries->states[$states[1]][$states[2]];
							if ($states) {
								echo '<option value="' . $country_code . '">' . $state_name . '</option>';
							}
							?>
						<?php } ?>
					</select>
				</div>
			</div>
			<div class="item item-shipping-fee" style="display: none;">
				<div class="label"><?php esc_attr_e('Shipping Fee:', 'tokaikiza'); ?></div>
				<div class="item-info"></div>
			</div>
			<script type="text/javascript">
				jQuery(document).ready(function($) {
					var $container = $("body");
					$(document).on('change', '.product-shipping select#delivery-shipping', function(e) {
						e.preventDefault();
						if ($(this).val() != '') {
							$.ajax({
								type: "post",
								dataType: "html",
								url: '<?php echo admin_url('admin-ajax.php'); ?>',
								data: {
									action: "delivery_shipping_countries_ajax",
									shippingclass_id: $(this).data('id'),
									countries: $(this).val()
								},
								beforeSend: function() {
									$container.append('<div id="temp_load" class="loading"><span class="loading__anim"></span></div>');
								},
								success: function(response) {
									if (response.length) {
										$('.product-shipping .item-shipping-fee').show();
										$('.product-shipping .item-shipping-fee .item-info').html(response);
									}
									$("#temp_load").remove();
								},
								error: function(jqXHR, textStatus, errorThrown) {
									$("#temp_load").remove();
									console.log('The following error occured');
								}
							});
						} else {
							$('.product-shipping .item-shipping-fee').hide();
						}
					});
				});
			</script>
		</div>
	<?php
	}
}

/**
 * Woocommerce ajax shipping countries
 */

add_action('wp_ajax_delivery_shipping_countries_ajax', 'delivery_shipping_countries_ajax');
add_action('wp_ajax_nopriv_delivery_shipping_countries_ajax', 'delivery_shipping_countries_ajax');
function delivery_shipping_countries_ajax()
{
	$shippingclass_id = (isset($_POST['shippingclass_id'])) ? esc_attr($_POST['shippingclass_id']) : '';
	$countries = (isset($_POST['countries'])) ? esc_attr($_POST['countries']) : '';
	if (!empty($shippingclass_id) && !empty($countries)) {
		$delivery_zones = WC_Shipping_Zones::get_zones();
		$states = explode(':', $countries);

		$code = $states[1] . ':' . $states[2];
		$zone_id = '';
		$class_cost = '';
		if (!empty($delivery_zones)) {
			foreach ($delivery_zones as $zones) {
				if (!empty($zones['zone_locations'])) {
					foreach ($zones['zone_locations'] as $loca) {
						if ($loca->code == $code) {
							$zone_id = $zones['id'];
						}
					}
				}
			}
			foreach ($delivery_zones as $zones) {
				if ($zone_id == $zones['id']) {
					foreach ($zones['shipping_methods'] as $method) {
						if ($method->id == 'flat_rate') {
							$class_cost = $method->instance_settings['class_cost_' . $shippingclass_id];
						}
					}
				}
			}
		}
		if ($class_cost != '' && $class_cost > 0) {
			echo wc_price($class_cost);
		} else {
			echo '<span class="freeship">' . esc_html__('Free shipping', 'tokaikiza') . '</span>';
		}
	}
	die();
}

/**
 * Filters the shipping method cost with classes must
 * 
 * @param float $cost the shipping method cost
 * @param \WC_Shipping_Rate $method the shipping method
 * @return float cost
 */
function wc_shipping_rate_classes_must($cost, $method)
{
	$flat_rate = new WC_Shipping_Flat_Rate($method->get_instance_id());
	$must_check = woocommerce_get_shipping_classes_must_check();
	$must_none = woocommerce_get_shipping_classes_must_none();
	$cost_must_check = array();
	$cost_must_none = array();
	foreach (WC()->cart->get_cart() as $cart_item) {
		if (in_array($cart_item['data']->get_shipping_class_id(), $must_check)) {
			$cost_must_check[] = $flat_rate->instance_settings['class_cost_' . $cart_item['data']->get_shipping_class_id()];
		}
		if (in_array($cart_item['data']->get_shipping_class_id(), $must_none)) {
			$cost_must_none[] = $flat_rate->instance_settings['class_cost_' . $cart_item['data']->get_shipping_class_id()];
		}
	}
	if (!empty($cost_must_none) && !empty($cost_must_check)) {
		$cost = array_sum($cost_must_check) + max($cost_must_none);
	}
	return $cost;
}
add_filter('woocommerce_shipping_rate_cost', 'wc_shipping_rate_classes_must', 10, 2);
/**
 * Woocommerce shipping classes must check
 */
function woocommerce_get_shipping_classes_must_check()
{
	$shipping_classes = WC()->shipping()->get_shipping_classes();
	$shipping_must = array();
	if (!empty($shipping_classes)) {
		foreach ($shipping_classes as $shipping) {
			if ($shipping->must == 1) {
				$shipping_must[] = $shipping->term_id;
			}
		}
	}
	return $shipping_must;
}

/**
 * Woocommerce shipping classes must not check
 */
function woocommerce_get_shipping_classes_must_none()
{
	$shipping_classes = WC()->shipping()->get_shipping_classes();
	$shipping_must = array();
	if (!empty($shipping_classes)) {
		foreach ($shipping_classes as $shipping) {
			if ($shipping->must != 1) {
				$shipping_must[] = $shipping->term_id;
			}
		}
	}
	return $shipping_must;
}

add_filter('woocommerce_package_rates', 'hide_specific_shipping_rate_if_shipping_free_is_available', 10, 2);
function hide_specific_shipping_rate_if_shipping_free_is_available($rates, $package)
{
	$check_fee = false;
	// Get cart items for the current shipping package
	foreach ($package['contents'] as $cart_item) {
		$_product = wc_get_product($cart_item['product_id']);
		if ($_product->get_shipping_class()) {
			$check_fee = true;
			break;
		}
	}

	if ($check_fee == false) {
		foreach ($rates as $rate_key => $rate) {
			// Removing "Flat rate" shipping method
			if ('flat_rate' === $rate->method_id) {
				unset($rates[$rate_key]);
			}
		}
	} else {
		$check_remove_free = false;
		foreach ($rates as $rate_key => $rate) {
			if ('flat_rate' == $rate->method_id && $rate->cost >= 1) {
				$check_remove_free = true;
				break;
			}
		}

		foreach ($rates as $rate_key => $rate) {
			// Removing "Flat rate" shipping method
			if ('flat_rate' == $rate->method_id && $rate->cost == 0) {
				if ('flat_rate' == $rate->method_id) {
					unset($rates[$rate_key]);
				}
			}
			if ('free_shipping' == $rate->method_id) {
				if ($check_remove_free == true) {
					unset($rates[$rate_key]);
				}
			}
		}
	}
	return $rates;
}

/**
 * Woocommerce shipping disable on page cart
 */
function disable_shipping_calc_on_cart($show_shipping)
{
	if (is_cart()) {
		return false;
	}
	return $show_shipping;
}
add_filter('woocommerce_cart_ready_to_calc_shipping', 'disable_shipping_calc_on_cart', 99);

/**
 * Woocommerce ajax parts finder select
 */
add_action('wp_ajax_parts_finder_select_year_ajax', 'parts_finder_select_year_ajax');
add_action('wp_ajax_nopriv_parts_finder_select_year_ajax', 'parts_finder_select_year_ajax');
function parts_finder_select_year_ajax()
{
	$year = (isset($_POST['year'])) ? esc_attr($_POST['year']) : '';
	$out = array();
	if ($year != '') {
		$brands = get_term_meta((int) $year, 'brand', true);
		if (!empty($brands)) {
			$out[] = '<option value="">' . esc_html__('Select Brand', 'tokaikiza') . '</option>';
			foreach ($brands as $brand) {
				$catname = get_term_by('id', $brand, 'pa_brand');
				$out[] = '<option data-id="' . $brand . '" value="' . $catname->slug . '">' . $catname->name . '</option>';
			}
		}
	}
	wp_send_json_success($out);
	die();
}
add_action('wp_ajax_parts_finder_select_ajax', 'parts_finder_select_ajax');
add_action('wp_ajax_nopriv_parts_finder_select_ajax', 'parts_finder_select_ajax');
function parts_finder_select_ajax()
{
	$brand = (isset($_POST['brand'])) ? esc_attr($_POST['brand']) : '';
	$year = (isset($_POST['year'])) ? esc_attr($_POST['year']) : '';
	$catyear = get_term_by('slug', $year, 'pa_year-normal');
	$out = array();
	if ($brand != '') {
		$models = get_term_meta((int) $brand, 'model', true);
		if (!empty($models)) {
			$out[] = '<option value="">' . esc_html__('Select Model', 'tokaikiza') . '</option>';
			foreach ($models as $model) {
				$catname = get_term_by('id', $model, 'pa_model-normal');
				$years = get_term_meta($catname->term_id, 'year', true);
				if (in_array($catyear->term_id, $years)) {
					$out[] = '<option data-id="' . $model . '" value="' . $catname->slug . '">' . $catname->name . '</option>';
				}
			}
		}
	}
	wp_send_json_success($out);
	die();
}

/**
 * Woocommerce ajax hitch member select
 */
add_action('wp_ajax_hitch_select_manufacturer_ajax', 'hitch_select_manufacturer_ajax');
add_action('wp_ajax_nopriv_hitch_select_manufacturer_ajax', 'hitch_select_manufacturer_ajax');
function hitch_select_manufacturer_ajax()
{
	$manufa = (isset($_POST['manufa'])) ? esc_attr($_POST['manufa']) : '';
	$out = array();
	if ($manufa != '') {
		$carmodel = get_term_meta((int) $manufa, 'car_model', true);
		if (!empty($carmodel)) {
			$out[] = '<option value="">' . esc_html__('Select option', 'tokaikiza') . '</option>';
			foreach ($carmodel as $item) {
				$catname = get_term_by('id', $item, 'pa_car-model');
				$out[] = '<option data-id="' . $item . '" value="' . urldecode($catname->slug) . '">' . $catname->name . '</option>';
			}
		}
	}
	wp_send_json_success($out);
	die();
}
add_action('wp_ajax_hitch_select_carmodel_ajax', 'hitch_select_carmodel_ajax');
add_action('wp_ajax_nopriv_hitch_select_carmodel_ajax', 'hitch_select_carmodel_ajax');
function hitch_select_carmodel_ajax()
{
	$carmodel = (isset($_POST['carmodel'])) ? esc_attr($_POST['carmodel']) : '';
	$manufa = (isset($_POST['manufa'])) ? esc_attr($_POST['manufa']) : '';
	$catmanufa = get_term_by('id', $manufa, 'pa_hitch-manufacturer');
	$out = array();
	if ($carmodel != '') {
		$models = get_term_meta((int) $carmodel, 'applicable_model', true);
		if (!empty($models)) {
			$out[] = '<option value="">' . esc_html__('Select option', 'tokaikiza') . '</option>';
			foreach ($models as $model) {
				$catname = get_term_by('id', $model, 'pa_applicable-model');
				$manufacturer = get_term_meta($catname->term_id, 'hitch_manufacturer', true);
				if (in_array($catmanufa->term_id, $manufacturer)) {
					$out[] = '<option data-id="' . $model . '" value="' . urldecode($catname->slug) . '">' . $catname->name . '</option>';
				}
			}
		}
	}
	wp_send_json_success($out);
	die();
}

/**
 * Mega Menu link widget class
 *
 * @since 2.8.0
 */
class mega_Nav_Menu_Widget extends WP_Widget
{

	function __construct()
	{
		parent::__construct(
			'mega_nav_menu_widget',
			'Mega Menu link',
			array('description'  =>  'Widget Mega Menu link')
		);
	}

	function form($instance)
	{
		$default = array(
			'title' => '',
			'link_menu' => '',
			'nav_menu' => ''
		);
		$instance = wp_parse_args((array) $instance, $default);
		$title = esc_attr($instance['title']);
		$link_menu =  isset($instance['link_menu']) ? $instance['link_menu'] : '';
		$nav_menu = isset($instance['nav_menu']) ? $instance['nav_menu'] : '';

		// Get menus
		$menus = wp_get_nav_menus(array('orderby' => 'name'));

		// If no menus exists, direct the user to go and create some.
		if (!$menus) {
			echo '<p>' . sprintf(__('No menus have been created yet. <a href="%s">Create some</a>.'), admin_url('nav-menus.php')) . '</p>';
			return;
		}
	?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:') ?></label>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo $title; ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('link_menu'); ?>"><?php _e('Link menu:') ?></label>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id('link_menu'); ?>" name="<?php echo $this->get_field_name('link_menu'); ?>" value="<?php echo $link_menu; ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('nav_menu'); ?>"><?php _e('Select Menu:'); ?></label>
			<select id="<?php echo $this->get_field_id('nav_menu'); ?>" name="<?php echo $this->get_field_name('nav_menu'); ?>">
				<option value="0"><?php _e('&mdash; Select &mdash;') ?></option>
				<?php
				foreach ($menus as $menu) {
					echo '<option value="' . $menu->term_id . '"'
						. selected($nav_menu, $menu->term_id, false)
						. '>' . esc_html__($menu->name, 'tokaikiza') . '</option>';
				}
				?>
			</select>
		</p>
<?php

	}

	function update($new_instance, $old_instance)
	{
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['link_menu'] = $new_instance['link_menu'];
		$instance['nav_menu'] = (int) $new_instance['nav_menu'];
		return $instance;
	}

	function widget($args, $instance)
	{
		extract($args);

		// Get menu
		$nav_menu = !empty($instance['nav_menu']) ? wp_get_nav_menu_object($instance['nav_menu']) : false;

		if (!$nav_menu)
			return;

		/** This filter is documented in wp-includes/default-widgets.php */
		$instance['title'] = apply_filters('widget_title', empty($instance['title']) ? '' : $instance['title'], $instance, $this->id_base);

		echo $args['before_widget'];

		if (!empty($instance['title'])) {
			if (!empty($instance['link_menu'])) {
				echo $args['before_title'] . '<a href="' . $instance['link_menu'] . '">' . $instance['title'] . '</a>' . $args['after_title'];
			} else {
				echo $args['before_title'] . $instance['title'] . $args['after_title'];
			}
		}

		wp_nav_menu(array('fallback_cb' => '', 'menu' => $nav_menu, 'container_class' => 'soma_container', 'menu_class' => 'some_list',));

		echo $args['after_widget'];
	}
}

add_action('widgets_init', 'create_mega_Nav_Menu_news_widget');
function create_mega_Nav_Menu_news_widget()
{
	register_widget('mega_Nav_Menu_Widget');
}
/**
 * remove wishlist share key
 *
 * @return string
 */
function tinvwl_addtowishlist_redirect_new($output)
{
	$data['wishlist_url'] = tinv_url_wishlist_default();
	return $data['wishlist_url'];
}
add_filter('tinvwl_addtowishlist_redirect', 'tinvwl_addtowishlist_redirect_new', 9999);

/**  
 * Remove Checkout Fields  
 */
/* WooCommerce: The Code Below Removes Checkout Fields */

add_filter('woocommerce_checkout_fields', 'custom_override_checkout_fields', 9999);
function custom_override_checkout_fields($fields)
{
	// remove billing fields
	unset($fields['billing']['billing_company']);

	// remove shipping fields 
	unset($fields['shipping']['shipping_company']);

	$fields['billing']['billing_phone']['label'] = esc_html__('Phone Number', 'tokaikiza');
	$fields['billing']['billing_phone']['placeholder'] = esc_attr__('Enter your phone number', 'tokaikiza');
	$fields['shipping']['shipping_phone']['required'] = true;
	$fields['shipping']['shipping_phone']['label'] = esc_html__('Phone Number', 'tokaikiza');
	$fields['shipping']['shipping_phone']['placeholder'] = esc_attr__('Enter your phone number', 'tokaikiza');

	$fields['billing']['billing_email']['label'] = esc_html__('Email Address', 'tokaikiza');
	$fields['billing']['billing_email']['placeholder'] = esc_attr__('Enter your email address', 'tokaikiza');
	$fields['shipping']['shipping_email']['required'] = true;
	$fields['shipping']['shipping_email']['label'] = esc_html__('Email Address', 'tokaikiza');
	$fields['shipping']['shipping_email']['placeholder'] = esc_attr__('Enter your email address', 'tokaikiza');

	$fields['order']['order_comments']['placeholder'] = esc_attr__('Enter notes about your orders, delivery, etc.', 'tokaikiza');
	return $fields;
}

add_filter('woocommerce_default_address_fields', 'woocommerce_custom_checkout_fields', 9999);
function woocommerce_custom_checkout_fields($fields)
{
	$fields['country']['label'] = esc_html__('Country', 'tokaikiza');
	$fields['postcode']['label'] = esc_html__('Post Code/ZIP', 'tokaikiza');
	$fields['postcode']['placeholder'] = esc_attr__('Enter a post code or ZIP', 'tokaikiza');
	$fields['address_1']['label'] = esc_html__('Address', 'tokaikiza');
	$fields['address_1']['placeholder'] = esc_attr__('House number, street name, etc.', 'tokaikiza');
	$fields['first_name']['placeholder'] = esc_attr__('Enter your first name', 'tokaikiza');
	$fields['last_name']['placeholder'] = esc_attr__('Enter your last name', 'tokaikiza');
	$fields['city']['placeholder'] = esc_attr__('Enter your town/city', 'tokaikiza');
	return $fields;
}

add_filter('woocommerce_billing_fields', 'remove_account_billing_address_fields', 20, 1);
function remove_account_billing_address_fields($billing_fields)
{
	// Only on my account 'edit-address'
	if (is_wc_endpoint_url('edit-address')) {
		unset($billing_fields['billing_company']);
	}
	return $billing_fields;
}
add_filter('woocommerce_shipping_fields', 'remove_account_shipping_address_fields', 20, 1);
function remove_account_shipping_address_fields($shipping_fields)
{
	// Only on my account 'edit-address'
	if (is_wc_endpoint_url('edit-address')) {
		unset($shipping_fields['shipping_company']);

		$shipping_fields['shipping_phone']['required'] = true;
		$shipping_fields['shipping_phone']['label'] = esc_html__('Phone Number', 'tokaikiza');
		$shipping_fields['shipping_phone']['placeholder'] = esc_attr__('Enter your phone number', 'tokaikiza');

		$shipping_fields['shipping_email']['required'] = true;
		$shipping_fields['shipping_email']['label'] = esc_html__('Email Address', 'tokaikiza');
		$shipping_fields['shipping_email']['placeholder'] = esc_attr__('Enter your email address', 'tokaikiza');
	}
	return $shipping_fields;
}

// Check login add to cart
add_filter( 'woocommerce_is_purchasable', 'woo_guest_purchases' );
add_filter( 'woocommerce_variation_is_purchasable', 'woo_guest_purchases' );
function woo_guest_purchases( $is_purchasable ){
    if( ! is_user_logged_in() ){
        return false;
    }
    return $is_purchasable;
}

// get Categories OEM product
function get_list_categories_ids_oem() {
	$terms = get_terms( array(
		'taxonomy' => 'product_cat',
		'hide_empty' => false,
	) );
	$ids = array();
	if (!empty($terms)){
		foreach ($terms as $term){
			if(get_field("category_type", $term) == 'oem_parts'){
				$ids[] = $term->term_id;
			}
		}
	}
	return $ids;
}

/**
 * Disable Payment Method for Specific Category
 */
  
add_filter( 'woocommerce_available_payment_gateways', 'oem_unset_gateway_by_category' );
function oem_unset_gateway_by_category( $available_gateways ) {
    if ( is_admin() ) return $available_gateways;
    if ( ! is_checkout() ) return $available_gateways;
    $unset = false;
    $category_id = get_list_categories_ids_oem(); // TARGET CATEGORY
    foreach ( WC()->cart->get_cart_contents() as $key => $values ) {
        $terms = get_the_terms( $values['product_id'], 'product_cat' );    
        foreach ( $terms as $term ) {        
            if ( in_array($term->term_id, $category_id) ) {
                $unset = true; // CATEGORY IS IN THE CART
                break;
            }
        }
    }
	
	$payment_method_oem = get_option('payment_method_oem');
	
    if ( $unset == true && !empty($payment_method_oem) ){
		foreach($payment_method_oem as $id){
			unset( $available_gateways[$id] );
		}
	}
    return $available_gateways;
}