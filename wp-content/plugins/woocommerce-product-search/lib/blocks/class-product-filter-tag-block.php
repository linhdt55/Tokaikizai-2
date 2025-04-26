<?php
/**
 * class-product-filter-tag-block.php
 *
 * Copyright (c) "kento" Karim Rahimpur www.itthinx.com
 *
 * This code is provided subject to the license granted.
 * Unauthorized use and distribution is prohibited.
 * See COPYRIGHT.txt and LICENSE.txt
 *
 * This code is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * This header and all notices must be kept intact.
 *
 * @author itthinx
 * @package woocommerce-product-search
 * @since 4.0.0
 */

namespace com\itthinx\woocommerce\search;

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

class Product_Filter_Tag_Block extends Block {

	public static function register_block_type() {
		register_block_type(
			'woocommerce-product-search/woocommerce-product-filter-tag',
			array(
				'api_version' => 2,
				'style' => 'product-search',
				'editor_style' => 'woocommerce-product-search-blocks-editor',
				'editor_script' => 'woocommerce-product-search-blocks',
				'render_callback' => array( __CLASS__, 'render' ),
				'attributes' => array(
					'container_class' => array(
						'type' => 'string',
						'default' => ''
					),
					'container_id' => array(
						'type' => 'string',
						'default' => ''
					),
					'filter' => array(
						'type' => 'boolean',
						'default' => true
					),
					'format' => array(
						'type' => 'string',
						'default' => 'flat'
					),
					'heading' => array(
						'type' => array( 'null', 'string' )
					),
					'heading_class' => array(
						'type' => 'string',
						'default' => ''
					),
					'heading_element' => array(
						'type' => 'string',
						'default' => 'div'
					),
					'heading_id' => array(
						'type' => 'string',
						'default' => ''
					),
					'heading_no_results' => array(
						'type' => 'string',
						'default' => ''
					),
					'hide_empty' => array(
						'type' => 'boolean',
						'default' => true
					),
					'multiple' => array(
						'type' => 'boolean',
						'default' => false
					),
					'number' => array(
						'type' => array( 'number', 'string' ),
						'default' => ''
					),
					'order' => array(
						'type' => 'string',
						'default' => 'ASC'
					),
					'orderby' => array(
						'type' => 'string',
						'default' => 'name'
					),
					'shop_only' => array(
						'type' => 'boolean',
						'default' => 'no'
					),
					'show' => array(
						'type' => 'string',
						'default' => 'all'
					),
					'show_count' => array(
						'type' => 'boolean',
						'default' => false
					),
					'show_heading' => array(
						'type' => 'boolean',
						'default' => true
					),
					'show_names' => array(
						'type' => 'boolean',
						'default' => true
					),
					'show_thumbnails' => array(
						'type' => 'boolean',
						'default' => false
					),
					'sizing' => array(
						'type' => 'string',
						'default' => 'none'
					),
					'style' => array(
						'type' => 'string',
						'default' => 'inline'
					),
					'taxonomy' => array(
						'type' => 'string',
						'default' => 'product_tag'
					),
					'terms' => array(
						'type' => 'string',
						'default' => ''
					),
					'thumbnail_sizing_factor' => array(
						'type' => 'number',
						'default' => 1.3
					),
					'toggle' => array(
						'type' => 'boolean',
						'default' => true
					)
				)
			)
		);
	}

	public static function render( $atts, $content = '' ) {
		return woocommerce_product_search_filter_tag( $atts );
	}
}

Product_Filter_Tag_Block::init();
