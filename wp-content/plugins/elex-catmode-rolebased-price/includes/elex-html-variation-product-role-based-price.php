<?php
// to check whether accessed directly
if (!defined('ABSPATH')) {
	exit;
}
?>
<strong><p style="text-align: center; font-size: 20px;">
<?php 
	$user_roles = get_option('eh_pricing_discount_product_price_user_role');
	$users= get_option('eh_pricing_discount_product_on_users');
	if(is_array($user_roles) && !empty($user_roles)) {
	_e( 'Role Based Price', 'elex-catmode-rolebased-price' ); 
	global $wp_roles;
?>
</p></strong>
<table class="product_role_based_price widefat" id="eh_pricing_discount_product_price_adjustment_data">
	<thead>
		<th class="sort">&nbsp;</th>
		<th><?php _e( 'User Role', 'elex-catmode-rolebased-price' ); ?></th>
		<th><?php echo sprintf( __( 'Price (%s)', 'elex-catmode-rolebased-price' ), get_woocommerce_currency_symbol() ); ?></th>
	</thead>
	<tbody>
	
		<?php
			$this->price_table = array();
			$i=0;
			$product_adjustment_price;
			$user_roles = get_option('eh_pricing_discount_product_price_user_role');
			foreach ( $user_roles as $id => $value ) {
				$product_adjustment_price = get_post_meta( $variation->ID, 'product_role_based_price_'.$value, false );
				$user_roles_setting = $wp_roles->role_names;
			    $user_roles_setting['unregistered_user'] = 'Unregistered User';
				$this->price_table[$i]['id'] = $value;
				$this->price_table[$i]['name'] =  $user_roles_setting[$value];
				if((!empty($product_adjustment_price)) && isset($value,$product_adjustment_price )) {
					$this->price_table[$i]['role_price'] = $product_adjustment_price[0];
				}
				$i++;
			}
			foreach ( $this->price_table as $key => $value ) {
				?>
				<tr>
					<td class="sort" style="padding: 10px;">
						<input type="hidden" class="order" name="product_role_based_price[<?php echo $loop; ?>][<?php echo $this->price_table[ $key ]['id'] ?>]" value="<?php echo $this->price_table[ $key ]['id']; ?>" />
					</td>
					<td style="padding: 10px;">
						<label name="product_role_based_price[<?php echo $loop; ?>][<?php echo $this->price_table[ $key ]['id']; ?>][name]" style="margin-left:0px;"><?php echo isset( $this->price_table[ $key ]['name'] ) ? $this->price_table[ $key ]['name'] : ''; ?></label>
					</td>
					<td style="padding: 10px;">
						<?php echo get_woocommerce_currency_symbol(); ?><input style="width:30%;" type="text" name="product_role_based_price[<?php echo $loop; ?>][<?php echo $this->price_table[ $key ]['id']; ?>][role_price]" class = "product_role_based_price_<?php echo $this->price_table[$key]['id']; ?>" data-role-price=<?php echo $this->price_table[$key]['id']; ?> id="product_role_based_price_<?php echo $this->price_table[$key]['id']; ?>" placeholder="N/A" value="<?php echo isset( $this->price_table[$key]['role_price'] ) ? $this->price_table[$key]['role_price'] : ''; ?>" size="4" />
					</td>
				</tr>
				<?php
			}
		?>
	</tbody>
</table>
<?php
 }
 if(is_array($users) && !empty($users)) {

 global $wp_roles;
 ?>
 </p></strong>
 <table class="product_role_based_price_user widefat" id="eh_pricing_discount_product_price_adjustment_data_for_users">
	 <thead>
		 <th class="sort">&nbsp;</th>
		 <th><?php _e( 'Users', 'elex-catmode-rolebased-price' ); ?></th>
		 <th><?php echo sprintf( __( 'Price (%s)', 'elex-catmode-rolebased-price' ), get_woocommerce_currency_symbol() ); ?></th>
	 </thead>
	 <tbody>
	 
		 <?php
			 $this->price_table = array();
			 $i=0;
			 $product_adjustment_price;
			 foreach ( $users['users'] as $id => $value ) {
				$user = get_user_by('id', $value);
                $user_name = '';
				if (is_object($user)) {
					$user_name = $user->display_name.'(#'.$user->ID.') - '.$user->user_email;
				} 
				 $product_adjustment_price = get_post_meta( $variation->ID, 'product_role_based_price_user_'.$user->user_email, false );
                 $this->price_table[$i]['id'] = $user->user_email;
				 $this->price_table[$i]['name'] = $user_name;
				 if((!empty($product_adjustment_price)) && isset($user->user_email,$product_adjustment_price )) {
					 $this->price_table[$i]['role_price'] = $product_adjustment_price[0];
				 }
				 $i++;
			 }
			 foreach ( $this->price_table as $key => $value ) {
				 ?>
				 <tr>
					 <td class="sort" style="padding: 10px;">
						 <input type="hidden" class="order" name="product_role_based_price_user[<?php echo $loop; ?>][<?php echo $this->price_table[ $key ]['id'] ?>]" value="<?php echo $this->price_table[ $key ]['id']; ?>" />
					 </td>
					 <td style="padding: 10px;">
						 <label name="product_role_based_price_user[<?php echo $loop; ?>][<?php echo $this->price_table[ $key ]['id']; ?>][name]" style="margin-left:0px;"><?php echo isset( $this->price_table[ $key ]['name'] ) ? $this->price_table[ $key ]['name'] : ''; ?></label>
					 </td>
					 <td style="padding: 10px;">
						 <?php echo get_woocommerce_currency_symbol(); ?><input style="width:30%;" type="text" name="product_role_based_price_user[<?php echo $loop; ?>][<?php echo $this->price_table[ $key ]['id']; ?>][role_price]" class ="product_role_based_price_user" data-role-price=<?php echo $this->price_table[$key]['id'];?> id="product_role_based_price_user_<?php echo $this->price_table[$key]['id']; ?>" placeholder="N/A" value="<?php echo isset( $this->price_table[$key]['role_price'] ) ? $this->price_table[$key]['role_price'] : ''; ?>" size="4" />
					 </td>
				 </tr>
				 <?php
			 }
		 ?>
	 </tbody>
 </table>
 <?php 
 }
	

else {
    _e( 'Role Based Price ', 'elex-catmode-rolebased-price' ); 
    ?>
<table class="product_role_based_price widefat" id="eh_pricing_discount_product_price_adjustment_data">
<th><?php _e( 'For setting up user roles eligible for individual price adjustment, go to Role Based Pricing settings -> Add roles for the field "Individual Price Adjustment".', 'elex-catmode-rolebased-price' ); ?></th>
</table>
</p></strong>
<?php
}
?>