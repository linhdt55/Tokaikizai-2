<?php

$ds_obj = Wbte\Uimpexp\Ds\Wbte_Ds::get_instance(WT_U_IEW_VERSION);
$wf_admin_view_path=plugin_dir_path(WT_U_IEW_PLUGIN_FILENAME).'admin/views/';

echo $ds_obj->get_component('header', array(
	'values' => array(
		'plugin_logo' => WT_U_IEW_PLUGIN_URL . 'assets/images/plugin_img.png',
		'plugin_name' => esc_html__('WebToffee Import Export', 'users-customers-import-export-for-wp-woocommerce'),
		'developed_by_txt' => esc_html__('Developed by ', 'users-customers-import-export-for-wp-woocommerce')
	),
	'class' => array(''),
));

echo $ds_obj->get_component('help-widget', array(
	'values' => array(
		'items' => array(
			array('title' => esc_html__('FAQ', 'users-customers-import-export-for-wp-woocommerce'), 'icon' => 'chat-1', 'href' => 'https://wordpress.org/plugins/users-customers-import-export-for-wp-woocommerce/#:~:text=import%20export%20log-,FAQ,-Does%20this%20plugin', 'target' => '_blank'),
			array('title' => esc_html__('Setup guide', 'users-customers-import-export-for-wp-woocommerce'), 'icon' => 'book', 'href' => 'https://www.webtoffee.com/category/basic-plugin-documentation/#:~:text=View%20All-,User%20Import/Export,-User%20Import%20Export', 'target' => '_blank'),
			array('title' => esc_html__('Contact support', 'users-customers-import-export-for-wp-woocommerce'), 'icon' => 'headphone', 'href' => 'https://wordpress.org/plugins/users-customers-import-export-for-wp-woocommerce/', 'target' => '_blank'),
			array('title' => esc_html__('Request a feature', 'users-customers-import-export-for-wp-woocommerce'), 'icon' => 'light-bulb-1'),
		),
		'hover_text' => esc_html__('Help', 'users-customers-import-export-for-wp-woocommerce'),
	)
));

include $wf_admin_view_path."top_upgrade_header.php";
