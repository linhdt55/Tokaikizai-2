<?php

function register_custom_elementor_widget($widgets_manager)
{

	require_once(__DIR__ . '/elementor/owl_slider.php');
	require_once(__DIR__ . '/elementor/breadcrumb_elementor.php');
	require_once(__DIR__ . '/elementor/parts-finder.php');
	require_once(__DIR__ . '/elementor/hitch-member.php');

	$widgets_manager->register(new \Elementor_Widget_Owl_Slider());
	$widgets_manager->register(new \Elementor_Breadcrumb_Elementor());
	$widgets_manager->register(new \Elementor_Parts_Finder());
	$widgets_manager->register(new \Elementor_Hitch_Member());
}
add_action('elementor/widgets/register', 'register_custom_elementor_widget');
