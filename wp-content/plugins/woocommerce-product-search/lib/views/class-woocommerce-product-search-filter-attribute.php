<?php
/**
 * class-woocommerce-product-search-filter-attribute.php
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
 * @since 2.0.0
 */

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

if ( !function_exists( 'woocommerce_product_search_filter_attribute' ) ) {
	/**
	 * Renders a product attribute filter which is returned as HTML and loads
	 * required resources.
	 *
	 * @param array $atts desired filter options
	 * @return string form HTML
	 */
	function woocommerce_product_search_filter_attribute( $atts = array() ) {
		return WooCommerce_Product_Search_Filter_Attribute::render( $atts );
	}
}

/**
 * Filter by attribute.
 */
class WooCommerce_Product_Search_Filter_Attribute {

	private static $instances = 0;

	/**
	 * Adds the shortcode.
	 */
	public static function init() {
		add_shortcode( 'woocommerce_product_filter_attribute', array( __CLASS__, 'shortcode' ) );
	}

	/**
	 * Enqueues scripts and styles needed to render our search facility.
	 */
	public static function load_resources() {
		$options = get_option( 'woocommerce-product-search', array() );
		$enable_css = isset( $options[WooCommerce_Product_Search::ENABLE_CSS] ) ? $options[WooCommerce_Product_Search::ENABLE_CSS] : WooCommerce_Product_Search::ENABLE_CSS_DEFAULT;

		wp_enqueue_script( 'product-filter' );
		if ( $enable_css ) {
			wp_enqueue_style( 'product-search' );
		}
	}

	/**
	 * [woocommerce_product_filter_attribute] shortcode renderer.
	 *
	 * @param array $atts shortcode parameters
	 * @param string $content not used
	 *
	 * @return string|mixed
	 */
	public static function shortcode( $atts = array(), $content = '' ) {
		return self::render( $atts );
	}

	/**
	 * Instance ID.
	 *
	 * @since 4.0.0
	 *
	 * @return string
	 */
	private static function get_n() {
		$n = self::$instances;
		if ( function_exists( 'wp_is_json_request' ) && wp_is_json_request() ) {
			$n .= '-' . md5( rand() );
		}
		return $n;
	}

	/**
	 * Product attribute filter renderer.
	 *
	 * @param array $atts
	 * @param array $results
	 *
	 * @return string
	 */
	public static function render( $atts = array(), &$results = null ) {

		self::load_resources();

		$_atts = $atts;

		$atts = shortcode_atts(
			array(
				'attribute'          => null,
				'child_of'           => '',
				'container_class'    => '',
				'container_id'       => null,
				'depth'              => 0,
				'exclude'            => null,
				'filter'             => 'yes',
				'heading'            => null,
				'heading_class'      => null,
				'heading_element'    => 'div',
				'heading_id'         => null,
				'heading_no_results' => '',
				'height'             => '',
				'hide_empty'         => 'yes',
				'hierarchical'       => 'no',
				'include'            => null,
				'multiple'           => 'yes',
				'none_selected'      => __( 'Any', 'woocommerce-product-search' ),
				'number'             => null,
				'order'              => 'ASC',
				'orderby'            => 'name',
				'shop_only'          => 'no',
				'show'               => 'all',
				'show_count'         => 'no',
				'show_heading'       => 'yes',
				'show_names'         => 'yes',
				'show_selected_thumbnails' => 'yes',
				'show_thumbnails'    => 'yes',
				'size'               => '',
				'style'              => 'list',
				'taxonomy'           => null,
				'toggle'             => 'yes',
				'toggle_widget'      => 'yes'
			),
			$atts
		);

		$n               = self::get_n();
		$container_class = '';
		$container_id    = sprintf( 'product-search-filter-attribute-%d', $n );
		$heading_class   = 'product-search-filter-terms-heading product-search-filter-attribute-heading';
		$heading_id      = sprintf( 'product-search-filter-attribute-heading-%d', $n );

		$taxonomy = false;
		if ( $atts['attribute'] !== null ) {
			$atts['attribute'] = trim( $atts['attribute'] );
		}
		$attribute = $atts['attribute'];
		if ( !empty( $attribute ) ) {
			$attribute_taxonomies = wc_get_attribute_taxonomies();
			if ( !empty( $attribute_taxonomies ) && is_array( $attribute_taxonomies ) ) {

				foreach ( $attribute_taxonomies as $attribute_taxonomy ) {
					if (
						$attribute_taxonomy->attribute_label == $attribute ||
						$attribute_taxonomy->attribute_name == $attribute
					) {

						if ( $taxonomy = get_taxonomy( wc_attribute_taxonomy_name( $attribute_taxonomy->attribute_name ) ) ) {
							$attribute = $attribute_taxonomy->attribute_name;
							$atts['attribute'] = $attribute;
							break;
						}
					}
				}

				if ( !$taxonomy ) {
					foreach ( $attribute_taxonomies as $attribute_taxonomy ) {
						if (
							strtolower( $attribute_taxonomy->attribute_label ) == strtolower( $attribute ) ||
							strtolower( $attribute_taxonomy->attribute_name ) == strtolower( $attribute ) ||
							stripos( $attribute_taxonomy->attribute_label, $attribute ) !== false ||
							stripos( $attribute_taxonomy->attribute_name, $attribute ) !== false
						) {

							if ( $taxonomy = get_taxonomy( wc_attribute_taxonomy_name( $attribute_taxonomy->attribute_name ) ) ) {
								$attribute = $attribute_taxonomy->attribute_name;
								$atts['attribute'] = $attribute;
								break;
							}
						}
					}
				}
				if ( !$taxonomy ) {
					$attribute = null;
					$atts['attribute'] = null;
				}
			}
		}
		if ( !$taxonomy ) {
			$taxonomy = get_taxonomy( trim( $atts['taxonomy'] ) );
		}
		if ( $taxonomy === false ) {
			wps_log_warning(
				sprintf(
					__( 'WooCommerce Product Search: Invalid taxonomy/attribute specified for attribute filter: %1$s / %2$s', 'woocommerce-product-search' ),
					!empty( $_atts['taxonomy'] ) ? esc_html( $_atts['taxonomy'] ) : '',
					!empty( $_atts['attribute'] ) ? esc_html( $_atts['attribute'] ) : ''
				)
			);
			return '';
		} else {
			if ( $atts['heading'] === null || $atts['heading'] === '' ) {
				if ( !empty( $taxonomy->labels ) && !empty( $taxonomy->labels->singular_name ) ) {
					$atts['heading'] = _x( $taxonomy->labels->singular_name, 'product attribute singular name', 'woocommerce-product-search' );
				} else {
					$atts['heading'] = _x( $taxonomy->label, 'product attribute label', 'woocommerce-product-search' );
				}
			}
			$taxonomy = $taxonomy->name;
		}
		$atts['taxonomy'] = $taxonomy;

		$no_valid_include_terms = false;

		$params = array();
		foreach ( $atts as $key => $value ) {
			$is_param = true;
			if ( $value !== null ) {
				if ( is_string( $value ) ) {
					$value = strip_tags( trim( $value ) );
				}
				switch ( $key ) {
					case 'child_of' :
						if ( $value == '{current}' ) {
							$value = '';
							if ( $queried_object = get_queried_object() ) {
								if ( isset( $queried_object->term_id ) ) {
									$value = intval( $queried_object->term_id );
								}
							}
						} else {
							$key = $value;
							if ( !( $term = get_term_by( 'id', $key, $taxonomy ) ) ) {
								if ( !( $term = get_term_by( 'slug', $key, $taxonomy ) ) ) {
									$term = get_term_by( 'name', $key, $taxonomy );
								}
							}
							if ( $term ) {
								$value = $term->term_id;
							}
						}
						break;
					case 'exclude' :
					case 'include' :
						if ( is_string( $value ) ) {
							$value = explode( ',', $value );
						}
						if ( is_array( $value ) ) {
							$entries = array_map( 'trim', $value );
							$n_entries = 0;
							$term_ids = array();
							foreach( $entries as $entry ) {
								if ( strlen( $entry ) > 0 ) {
									$n_entries++;
									if ( !( $term = get_term_by( 'id', $entry, $taxonomy ) ) ) {
										if ( !( $term = get_term_by( 'slug', $entry, $taxonomy ) ) ) {
											$term = get_term_by( 'name', $entry, $taxonomy );
										}
									}
									if ( $term ) {
										$term_ids[] = $term->term_id;
									}
								}
							}
							if ( $key === 'include' && $n_entries > 0 ) {

								$hide_empty = in_array( strtolower( $atts['hide_empty'] ), array( 'true', 'yes', '1' ) );
								$processed_term_ids = get_terms( array(
									'taxonomy' => $taxonomy,
									'fields' => 'ids',
									'include' => $term_ids,
									'hide_empty' => $hide_empty
								) );
								if ( is_array( $processed_term_ids ) ) {
									$term_ids = array_intersect( $term_ids, $processed_term_ids );
								} else {
									$term_ids = array();
								}
							}
							if ( count( $term_ids ) === 0 ) {
								if ( $key === 'include' ) {
									if ( $n_entries !== 0 ) {

										$no_valid_include_terms = true;
									}
								}
								$value = null;
							} else {
								$value = $term_ids;
							}
						} else {
							$value = null;
						}
						break;
					case 'filter' :
					case 'hide_empty' :
					case 'hierarchical' :
					case 'multiple' :
					case 'shop_only' :
					case 'show_count' :
					case 'show_heading' :
					case 'show_names' :
					case 'show_selected_thumbnails' :
					case 'show_thumbnails' :
					case 'toggle' :
					case 'toggle_widget' :
						$value = strtolower( $value );
						$value = $value == 'true' || $value == 'yes' || $value == '1';
						break;
					case 'orderby' :
						switch ( $value ) {
							case 'count' :
							case 'id' :
							case 'term_order' :
							case 'name' :
							case 'slug' :
							case 'description' :
							case 'name_num' :
								break;
							default :
								$value = 'term_order';
						}
						break;
					case 'order' :
						$value = strtoupper( trim( $value ) );
						switch ( $value ) {
							case 'ASC' :
							case 'DESC' :
								break;
							default :
								$value = 'ASC';
						}
						break;
					case 'depth' :
					case 'number' :
					case 'size' :
						$value = intval( $value );
						break;
					case 'height' :
						$value = WooCommerce_Product_Search_Utility::get_css_unit( $value );
						break;
					case 'attribute' :
					case 'taxonomy' :

						break;

					case 'container_class' :
					case 'container_id' :
					case 'heading_class' :
					case 'heading_id' :
						$value = preg_replace( '/[^a-zA-Z0-9 _.#-]/', '', $value );
						$value = trim( $value );
						$containers[$key] = $value;
						$is_param = false;
						break;

					case 'heading_element' :
						if ( !in_array( $value, WooCommerce_Product_Search_Filter::get_allowed_filter_heading_elements() ) ) {
							$value = 'div';
						}
						break;

					case 'heading' :
					case 'heading_no_results' :
						$value = esc_html( $value );
						break;

					case 'show' :
						$value = trim( strtolower( $value ) );
						switch ( $value ) {
							case 'all' :
							case 'set' :
								break;
							default :
								$value = 'all';
						}
						break;

					case 'style' :
						$value = trim( strtolower( $value ) );
						switch( $value ) {
							case 'list' :
							case 'inline' :
							case 'select' :
							case 'dropdown' :
								break;
							default :
								$value = 'list';
						}
						break;
				}
			}
			if ( $is_param ) {
				$params[$key] = $value;
			}
		}

		if ( $params['shop_only'] && !woocommerce_product_search_is_shop() ) {
			return '';
		}

		if ( !empty( $containers['container_class'] ) ) {
			$container_class = $containers['container_class'];
		}
		if ( !empty( $containers['container_id'] ) ) {
			$container_id = $containers['container_id'];
		}
		if ( !empty( $containers['heading_class'] ) ) {
			$heading_class = $containers['heading_class'];
		}
		if ( !empty( $containers['heading_id'] ) ) {
			$heading_id = $containers['heading_id'];
		}

		$list_classes = array();
		switch( $params['style'] ) {
			case 'list' :
				$list_classes[] = 'style-list';
				break;
			case 'inline' :
				$list_classes[] = 'style-inline';
				break;
			case 'select' :
			case 'dropdown' :
				$list_classes[] = 'style-select';
				break;
		}
		if ( $params['show_thumbnails'] ) {
			$list_classes[] = 'show-thumbnails';
		} else {
			$list_classes[] = 'hide-thumbnails';
		}
		if ( $params['show_names'] ) {
			$list_classes[] = 'show-names';
		} else {
			$list_classes[] = 'hide-names';
		}
		$list_class = implode( ' ', $list_classes );

		$params['echo'] = false;
		$params['title_li'] = '';
		$params['show_option_none'] = '';

		if ( !empty( $params['exclude'] ) ) {
			$exclude_term_ids = $params['exclude'];
			foreach( $params['exclude'] as $term_id ) {
				$exclude_term_ids = array_merge(
					$exclude_term_ids,
					(array) get_terms( $taxonomy, array( 'child_of' => intval( $term_id ), 'fields' => 'ids', 'hide_empty' => 0 ) )
				);
			}
			$params['exclude'] = $exclude_term_ids;
		}

		if ( !empty( $params['include'] ) ) {
			$include_term_ids = $params['include'];
			foreach( $params['include'] as $term_id ) {
				$include_term_ids = array_merge(
					$include_term_ids,
					(array) get_terms( $taxonomy, array( 'child_of' => intval( $term_id ), 'fields' => 'ids', 'hide_empty' => 0 ) )
				);
			}
			$params['include'] = $include_term_ids;
		}

		$current_term_ids = array();
		$parent_term_ids = array();
		if (
			isset( $_REQUEST['ixwpst'] ) &&
			isset( $_REQUEST['ixwpst'][$taxonomy] ) &&
			is_array( $_REQUEST['ixwpst'][$taxonomy] )
		) {
			$include_term_ids = array();
			foreach ( $_REQUEST['ixwpst'][$taxonomy] as $term_id ) {
				if ( $term = get_term( $term_id, $taxonomy ) ) {
					if ( ( $term !== null ) && !( $term instanceof WP_Error) ) {
						$include_term_ids[] = $term->term_id;
						$child_term_ids     = get_terms( array( 'taxonomy' => $term->taxonomy, 'fields' => 'ids', 'child_of' => $term->term_id, 'hierarchical' => true ) );
						$include_term_ids   = array_merge( $include_term_ids, $child_term_ids );
						$include_term_ids   = array_unique( $include_term_ids );

						$current_term_ids[] = $term->term_id;
						$current_term_ids   = array_unique( $current_term_ids );

						$i = 0;
						if ( !empty( $term->parent ) ) {
							$parent = get_term( $term->parent, $taxonomy );
							if ( ( $parent !== null ) && !( $parent instanceof WP_Error) ) {
								while ( $parent && $i < 5 ) {
									$parent_term_ids[$term->term_id][] = $parent->term_id;
									if ( !empty( $parent->parent ) ) {
										$parent = get_term( $parent->parent, $taxonomy );
										if ( ( $parent === null ) || ( $parent instanceof WP_Error) ) {
											break;
										}
									} else {
										break;
									}
									$i++;
								}
								$parent_term_ids[$term->term_id] = array_reverse( $parent_term_ids[$term->term_id] );
							}
						}
					}
				}
			}
			if ( count( $include_term_ids ) > 0 ) {
				if ( !$params['multiple'] && $params['show'] === 'set' ) {
					if ( !empty( $params['include'] ) && is_array( $params['include'] ) ) {
						$include_term_ids = array_intersect( $params['include'], $include_term_ids );
					}
					$params['include'] = $include_term_ids;
				}
			}
			if ( count( $current_term_ids ) > 0 ) {
				$params['current_terms'] = $current_term_ids;
			}
		}

		if ( $no_valid_include_terms ) {
			$params['include'] = array();
		}

		$has_eligible_terms = true;
		if ( is_array( $params['include'] ) && count( $params['include'] ) === 0 ) {

			$params['include'] = array( PHP_INT_MAX );
			$has_eligible_terms = false;
		}

		$query_ixwpst = isset( $_GET['ixwpst'] ) ? $_GET['ixwpst'] : null;
		$current_url = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		$current_url = remove_query_arg( array( 'ixwpst' ), $current_url );
		if ( $query_ixwpst !== null ) {
			unset( $query_ixwpst[$taxonomy] );
			if ( count( $query_ixwpst ) > 0 ) {
				$current_url = add_query_arg( array( 'ixwpst' => $query_ixwpst ), $current_url );
			}
		}

		$parent_term_urls      = array();
		$added_parent_term_ids = array();
		foreach ( $parent_term_ids as $tid => $ids ) {
			foreach ( $ids as $id ) {
				if ( !in_array( $id, $added_parent_term_ids ) ) {
					$parent_term_url = add_query_arg( array( 'ixwpst' => array( $taxonomy => array( $id ) ) ), $current_url );
					$parent_term_urls[$id] = $parent_term_url;
				}
			}
		}

		require_once WOO_PS_VIEWS_LIB . '/class-woocommerce-product-search-term-walker.php';
		$params['walker'] = new WooCommerce_Product_Search_Term_Walker( $taxonomy );
		$params['walker']->current_terms = $current_term_ids;
		$params['walker']->show_names = $params['show_names'];
		$params['walker']->show_thumbnails = $params['show_thumbnails'];

		$prefix_output = apply_filters(
			"woocommerce_product_search_filter_{$taxonomy}_prefix",
			sprintf(
				'<div id="%s" class="product-search-filter-terms %s %s" data-multiple="%s">',
				esc_attr( $container_id ),
				esc_attr( $container_class ),
				$params['filter'] ? '' : 'filter-dead',
				$params['multiple'] ? '1' : ''
			)
		);
		switch ( $params['style'] ) {
			case 'select' :
			case 'dropdown' :
				$open_inside_output = '';
				$close_inside_output = '';
				if ( $params['style'] === 'dropdown' ) {
					wp_enqueue_script( 'selectize' );
					wp_enqueue_script( 'selectize-ix' );
					wp_enqueue_style( 'selectize' );
				}
				break;
			default :
				$open_inside_output = sprintf(
					'<ul class="product-attribute product-search-filter-items product-search-filter-attribute product-search-filter-%s %s%s%s">',
					esc_attr( $taxonomy ),
					esc_attr( $list_class ),
					$params['toggle'] ? ' product-search-filter-toggle' : '',
					$params['toggle_widget'] ? ' product-search-filter-toggle-widget' : ''
				);
				$close_inside_output = '</ul>';
		}

		$clear_output = '';
		if ( $has_eligible_terms && isset( $_GET['ixwpst'] ) && isset( $_GET['ixwpst'][$taxonomy] ) ) {
			switch ( $params['style'] ) {
				case 'select' :
				case 'dropdown' :
					$clear_output .= sprintf(
						'<div data-term="" data-taxonomy="%s" class="attribute-item-all nav-back product-search-%s-filter-item product-search-attribute-filter-item"><a href="%s">%s</a></div>',
						esc_attr( $taxonomy ),
						esc_attr( $taxonomy ),
						esc_url( $current_url ),
						__( 'Clear', 'woocommerce-product-search' )
					);
					break;
				default :
					$clear_output .= sprintf(
						'<li data-term="" data-taxonomy="%s" class="attribute-item-all nav-back product-search-%s-filter-item product-search-attribute-filter-item"><a href="%s">%s</a></li>',
						esc_attr( $taxonomy ),
						esc_attr( $taxonomy ),
						esc_url( $current_url ),
						__( 'Clear', 'woocommerce-product-search' )
					);
			}
		}

		$parent_terms_output = '';
		foreach ( $parent_term_urls as $parent_term_id => $parent_term_url ) {
			if ( $parent_term = get_term( $parent_term_id, $taxonomy ) ) {
				if ( ( $parent !== null ) && !( $parent instanceof WP_Error) ) {
					$parent_terms_output .= sprintf(
						( $params['style'] !== 'select' && $params['style'] !== 'dropdown' ) ?
							'<li data-term="%s" data-taxonomy="%s" class="attribute-item-parent nav-back product-search-%s-filter-item"><a href="%s">%s</a></li>' :
							'<div data-term="%s" data-taxonomy="%s" class="attribute-item-parent nav-back product-search-%s-filter-item"><a href="%s">%s</a></div>',
						esc_attr( $parent_term->term_id ),
						esc_attr( $taxonomy ),
						esc_attr( $taxonomy ),
						esc_url( $parent_term_url ),
						esc_html( $parent_term->name )
					);
				}
			}
		}

		$elements_displayed = 0;
		$terms_output = '';
		if ( $has_eligible_terms ) {
			$root_class = sprintf( 'product-attribute product-search-filter-items product-search-filter-attribute product-search-filter-%s %s', $taxonomy, $list_class );
			if ( $params['style'] === 'dropdown' ) {
				$root_class .= ' apply-selectize';
			}
			if ( $params['toggle'] ) {
				$root_class .= ' product-search-filter-toggle';
			}
			if ( $params['toggle_widget'] ) {
				$root_class .= ' product-search-filter-toggle-widget';
			}
			$params['fields'] = 'ids';
			$term_ids = WooCommerce_Product_Search_Service::get_term_ids_for_request( $params, $taxonomy );
			$node = new WooCommerce_Product_Search_Term_Node( $term_ids, $taxonomy, array( 'hide_empty' => $params['hide_empty'] ) );
			$node->sort( $params['orderby'], $params['order'] );
			if ( !empty( $params['number'] ) && $params['number'] > 0 ) {
				$node->crop( 0, $params['number'] );
			}
			switch( $params['style'] ) {
				case 'select' :
				case 'dropdown' :
					$node_renderer = new WooCommerce_Product_Search_Term_Node_Select_Renderer( array(
						'current_term_ids'          => $current_term_ids,

						'depth'                     => $params['depth'] > 0 ? $params['depth'] : null,
						'hierarchical'              => $params['hierarchical'],
						'multiple'                  => $params['multiple'],
						'none_selected'             => $params['none_selected'],
						'render_root_container'     => true,
						'root_id'                   => 'product-search-filter-select-' . $taxonomy . '-' . $n,
						'root_name'                 => 'product-search-filter-' . $taxonomy,
						'root_class'                => $root_class,
						'size'                      => $params['size'],
						'show_count'                => $params['show_count'],

						'show_thumbnails'           => $params['show_thumbnails'],
					) );
					break;
				default :
					$node_renderer = new WooCommerce_Product_Search_Term_Node_Tree_Renderer( array(
						'auto_expand'               => false,
						'auto_retract'              => false,
						'current_term_ids'          => $current_term_ids,

						'depth'                     => $params['depth'] > 0 ? $params['depth'] : null,

						'expander'                  => false,
						'hierarchical'              => $params['hierarchical'],
						'render_root_container'     => false,
						'root_class'                => $root_class,
						'show_count'                => $params['show_count'],
						'show_names'                => $params['show_names'],
						'show_thumbnails'           => $params['show_thumbnails'],
					) );
			}

			$terms_output = apply_filters(
				"woocommerce_product_search_filter_{$taxonomy}_content",
				$node_renderer->render( $node ),
				$atts,
				$params
			);
			$elements_displayed = $node_renderer->get_elements_displayed();
			unset( $node_renderer );
			unset( $node );
		}

		$heading_output = '';
		if ( $params['show_heading'] ) {
			$heading_output .= sprintf(
				'<%s class="%s" id="%s">%s</%s>',
				esc_html( $params['heading_element'] ),
				esc_attr( $heading_class ),
				esc_attr( $heading_id ),
				$elements_displayed > 0 ? esc_html( $params['heading'] ) : esc_html( $params['heading_no_results'] ),
				esc_html( $params['heading_element'] )
			);
		}

		if ( $elements_displayed === 0 ) {
			$clear_output = '';
		}

		$output = $prefix_output;
		$output .= $heading_output;
		$output .= $open_inside_output;
		$output .= $clear_output;
		$output .= $parent_terms_output;
		$output .= $terms_output;
		$output .= $close_inside_output;

		$output .= apply_filters(
			"woocommerce_product_search_filter_{$taxonomy}_suffix",
			'</div>'
		);

		$inline_script = '';
		$js_object = sprintf( '{taxonomy:"%s"', esc_attr( $taxonomy ) );
		$js_object .= ',multiple:' . ( $params['multiple'] ? 'true' : 'false' );
		$js_object .= ',filter:' . ( $params['filter'] ? 'true' : 'false' );
		$js_object .= sprintf( ',show:"%s"', esc_attr( $params['show'] ) );
		$js_object .= sprintf( ',origin_id:"%s"', esc_attr( $container_id ) );
		$js_object .= '}';

		$inline_script .= 'if ( typeof jQuery !== "undefined" ) {';
		$inline_script .= 'if ( typeof ixwpsf !== "undefined" && typeof ixwpsf.taxonomy !== "undefined" ) {';
		$inline_script .= 'ixwpsf.taxonomy.push(' . $js_object . ');';
		$inline_script .= '}';
		$inline_script .= '}';

		$inline_script = woocommerce_product_search_safex( $inline_script );
		wp_add_inline_script( 'product-filter', $inline_script );

		if ( $params['style'] === 'dropdown' ) {

			$selectize_parameters = array();

			$height = null;
			$adjust_size = null;
			$class = 'ixnorm ';

			$selectize_parameters['hideSelected'] = false;

			if ( !empty( $params['height'] ) ) {
				if ( $params['show_thumbnails'] ) {
					$selectize_parameters['plugins'] = array(
						'ixnorm' => [],
						'ixboxed' => [],
						'ixremove' => [],
						'ixthumbnail' => [
							'show_selected_thumbnails' => $params['show_selected_thumbnails'] ? true : false
						],
					);
				} else {
					$selectize_parameters['plugins'] = array(
						'ixnorm' => [],
						'ixboxed' => [],
						'ixremove' => []
					);
				}
				if ( is_numeric( $params['height'] ) ) {
					$adjust_size = intval( $params['height'] );
				} else {
					$height = $params['height'];
					$class .= 'ixboxed';
				}
			} else {
				if ( $params['show_thumbnails'] ) {
					$selectize_parameters['plugins'] = array(
						'ixnorm' => [],
						'ixremove' => [],
						'ixthumbnail' => array(
							'show_selected_thumbnails' => $params['show_selected_thumbnails'] ? true : false
						)
					);
				} else {
					$selectize_parameters['plugins'] = array(
						'ixnorm' => [],
						'ixremove' => []
					);
				}
			}

			$selectize_parameters['wrapperClass'] = sprintf(
				'selectize-control %s %s %s',
				esc_attr( 'product-search-filter-select-' . $taxonomy . '-selectize' ),
				esc_attr( 'product-search-filter-select-' . $taxonomy . '-' . $n . '-selectize' ),
				$class
			);

			if ( $params['multiple'] ) {
				$selectize_parameters['maxItems'] = null;
			} else {
				$selectize_parameters['maxItems'] = 1;
			}

			$max_options = apply_filters( 'woocommerce_product_search_selectize_max_options', 100000, $params );
			if ( $max_options !== null ) {
				$max_options = intval( $max_options );
			}
			$selectize_parameters['maxOptions'] = $max_options;

			$inline_script = '';

			$inline_script .= 'if ( typeof jQuery !== "undefined" ) {';

			$inline_script .= 'if ( window.parent && window.parent.document !== document ) {';
			$inline_script .= 'if ( window.frameElement && jQuery( window.frameElement ).prop( "tagName" ).toLowerCase() === "iframe" ) {';
			$inline_script .= 'if ( jQuery( window.frameElement ).attr( "name" ).indexOf( "customize-preview" ) >= 0 ) {';
			$inline_script .= 'jQuery( document ).ready( function() {';
			$inline_script .= 'jQuery( "select.apply-selectize" ).trigger( "apply-selectize" );';
			$inline_script .= ' } );';
			$inline_script .= '}';
			$inline_script .= '}';
			$inline_script .= '}';

			$inline_script .= sprintf( 'jQuery( document ).on( "apply-selectize", "#%s", function( e ) {', esc_attr( 'product-search-filter-select-' . $taxonomy . '-' . $n ) );
			$inline_script .= 'if ( typeof jQuery().selectize !== "undefined" ) {';
			$inline_script .= 'jQuery( this ).prop( "disabled", false );';
			$inline_script .= sprintf( 'var selectized = jQuery( this ).selectize( %s );', json_encode( $selectize_parameters, JSON_FORCE_OBJECT ) );

			if ( $adjust_size !== null ) {
				$inline_script .= sprintf(
					'ixboxed.adjustSize( "#%s", %d ); ',
					esc_attr( 'product-search-filter-select-' . $taxonomy . '-' . $n . '-selectized' ),
					esc_attr( $adjust_size )
				);
			}

			$inline_script .= '}';
			$inline_script .= '});';

			$inline_script .= 'if ( window.wps_did_apply_selectize !== undefined ) {';
			$inline_script .= sprintf( 'jQuery( "#%s" ).trigger( "apply-selectize" );', esc_attr( 'product-search-filter-select-' . $taxonomy . '-' . $n ) );
			$inline_script .= '}';

			$inline_script .= '}';

			$inline_script = woocommerce_product_search_safex( $inline_script );

			wp_add_inline_script( 'selectize-ix', $inline_script );

			$output .= sprintf(
				'<div style="display:none!important" class="woocommerce-product-search-terms-observer" data-id="%s" data-taxonomy="%s" data-parameters="%s" data-adjust_size="%s" data-height="%s"></div>',
				esc_attr( 'product-search-filter-select-' . $taxonomy . '-' . $n ),
				esc_attr( $taxonomy ),
				_wp_specialchars( json_encode( $selectize_parameters, JSON_FORCE_OBJECT ), ENT_QUOTES, false, true ),
				esc_attr( $adjust_size !== null ? $adjust_size : '' ),
				esc_attr( $height !== null ? $height : '' )
			);

			if ( $height !== null ) {

				$output .= '<style type="text/css">';
				$output .= sprintf(
					'.%s .selectize-dropdown { height: %s; max-height: %s; }',
					esc_attr( 'product-search-filter-select-' . $taxonomy . '-' . $n . '-selectize' ),
					esc_attr( $height ),
					esc_attr( $height )
				);
				$output .= '</style>';
			}
		}

		WooCommerce_Product_Search_Filter::filter_added();

		$results = array(
			'elements_displayed' => $params['walker']->get_elements_displayed(),
			'container_id'       => $container_id,
		);

		self::$instances++;

		return $output;
	}

}
WooCommerce_Product_Search_Filter_Attribute::init();
