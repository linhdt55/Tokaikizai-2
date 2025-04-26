<?php
class Elementor_Breadcrumb_Elementor extends \Elementor\Widget_Base
{

    public function get_name()
    {
        return 'breadcrumb_elementor';
    }

    public function get_title()
    {
        return esc_html__('Breadcrumb Elementor', 'tokaikiza');
    }

    public function get_icon()
    {
        return 'eicon-product-breadcrumbs';
    }

    public function get_categories()
    {
        return ['basic'];
    }

    public function get_keywords()
    {
        return ['breadcrumb'];
    }

    protected function render()
    {
        if (!is_front_page()) {
            do_action('woocommerce_breadcrumb_toki');
        }
    }
}
