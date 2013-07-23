<?php
if( !function_exists( 'otw_sbm_index' ) ){
	function otw_sbm_index( $index, $sidebars_widgets ){
		
		global $wp_registered_sidebars, $otw_replaced_sidebars;
		
		if( isset( $otw_replaced_sidebars[ $index ] ) ){//we have set replacemend.
		
			$requested_objects = otw_get_current_object();
			
			//check if the new sidebar is valid for the current requested resource
			foreach( $otw_replaced_sidebars[ $index ] as $repl_sidebar ){
				
				if( isset( $wp_registered_sidebars[ $repl_sidebar ] ) ){
					
					if( $wp_registered_sidebars[ $repl_sidebar ]['status'] == 'active'  ){
						
						if( otw_filter_strict_sidebar_index( $repl_sidebar ) ){
							
							foreach( $requested_objects as $objects ){
							
								list( $object, $object_id ) = $objects;
							
								if( $object && $object_id ){
									
									$tmp_index = otw_validate_sidebar_index( $repl_sidebar, $object, $object_id );
									
									if( $tmp_index ){
										if ( !empty($sidebars_widgets[$tmp_index]) ){
											$sidebars_widgets[$tmp_index] = otw_filter_siderbar_widgets( $tmp_index, $sidebars_widgets );
											
											if( count( $sidebars_widgets[$tmp_index] ) ){
												$index = $tmp_index;
												break 2;
											}
										}
									}
									
								}//end hs object and object id
								
							}//end loop requested objects
						}
					}
				}
			}
		}
		
		return $index;
	}
}

/** check if sidebar is active
  * @param string
  * @return string
  */
if( !function_exists( 'otw_is_active_sidebar' ) ){
	function otw_is_active_sidebar( $index ){
		
		global $wp_registered_sidebars;
		
		$index = ( is_int($index) ) ? "sidebar-$index" : sanitize_title($index);
		
		$index = otw_sidebar_index( $index );
		
		if( isset( $wp_registered_sidebars[ $index ] ) ){
		
			if( !array_key_exists( 'status', $wp_registered_sidebars[ $index ] ) || ( $wp_registered_sidebars[ $index ]['status'] == 'active' ) ){
			
				$sidebars_widgets = wp_get_sidebars_widgets();
				
				if ( !empty($sidebars_widgets[$index]) ){
					
					$sidebars_widgets[$index] = otw_filter_siderbar_widgets( $index, $sidebars_widgets );
					
					if( count( $sidebars_widgets[$index] ) ){
						return true;
					}
				}
			}
			
		}
		
		return false;
	} 
}


/** check if given sidebar is valid for the given object and object_id without checing the widgets
  *  @param string
  *  @param string
  *  @param string
  *  @return string
  */
if( !function_exists( 'otw_validate_sidebar_index' ) ){
	function otw_validate_sidebar_index( $sidebar, $object, $object_id ){
	
		global $wp_registered_sidebars;
		
		$tmp_index = false;
		
		if( preg_match( "/^otw\-/", $sidebar ) ){
			
			if( isset( $wp_registered_sidebars[ $sidebar ]['validfor'][ $object ][ $object_id ] ) || isset( $wp_registered_sidebars[ $sidebar ]['validfor'][ $object ][ 'all' ] ) || empty( $wp_registered_sidebars[ $sidebar ]['replace'] ) ){
				$tmp_index = $sidebar;
			}elseif( preg_match( "/^cpt\_(.*)/", $object, $matches ) ){
				$cpt_object = 'customposttype';
				$cpt_object_id = $matches[1];
			
				if( isset( $wp_registered_sidebars[ $sidebar ]['validfor'][ $cpt_object ][ $cpt_object_id ] ) || isset( $wp_registered_sidebars[ $sidebar ]['validfor'][ $cpt_object ][ 'all' ] ) ){
					$tmp_index = $sidebar;
				}
			}
			
		}else{
			$tmp_index = $sidebar;
		}
		return $tmp_index;
	}
}

/** filter widget for given sidebar
  *
  *  @param string
  *  @param array
  *  @return array
  */
if( !function_exists( 'otw_filter_siderbar_widgets' ) ){
	function otw_filter_siderbar_widgets( $index, $sidebars_widgets ){
		
		global $wp_registered_sidebars, $otw_plugin_options;
		
		$filtered_widgets = array();
		
		if( array_key_exists( $index, $sidebars_widgets ) ){
		
			if( isset( $otw_plugin_options['activate_appearence'] ) && $otw_plugin_options['activate_appearence'] ){
				
				$requested_objects = otw_get_current_object();
				
				foreach( $requested_objects as $objects ){
					
					list( $object, $object_id ) = $objects;
					
					$tmp_index = otw_validate_sidebar_index( $index, $object, $object_id );
					
					if( $tmp_index ){
					
						$otw_wc_invisible = array();
						$otw_wc_visible = array();
						if( isset( $wp_registered_sidebars[ $tmp_index ]['widgets_settings'][ $object ]['_otw_wc'] ) ){
							$filtered = true;
							foreach( $wp_registered_sidebars[ $tmp_index ]['widgets_settings'][ $object ]['_otw_wc'] as $tmp_widget => $tmp_widget_value ){
								if( $tmp_widget_value == 'vis' ){
									$filtered_widgets[] = $tmp_widget;
									$otw_wc_visible[ $tmp_widget ] = $tmp_widget;
								}elseif( $tmp_widget_value == 'invis' ){
									$otw_wc_invisible[ $tmp_widget ] = $tmp_widget;
								}
							}
						}
						if( isset( $wp_registered_sidebars[ $tmp_index ]['widgets_settings'][ $object ][ $object_id ]['exclude_widgets'] ) ){
						
							foreach( $sidebars_widgets[ $tmp_index ] as $tmp_widget ){
								$filtered = true;
								if( !array_key_exists( $tmp_widget, $wp_registered_sidebars[ $tmp_index ]['widgets_settings'][ $object ][ $object_id ]['exclude_widgets'] ) ){
									
									if( !array_key_exists( $tmp_widget, $otw_wc_invisible ) && !array_key_exists( $tmp_widget, $otw_wc_visible )  ){
										$filtered_widgets[] = $tmp_widget;
									}
								}
							}
						}else{
							foreach( $sidebars_widgets[ $tmp_index ] as $tmp_widget ){
								$filtered = true;
								
								if( !array_key_exists( $tmp_widget, $otw_wc_invisible ) && !array_key_exists( $tmp_widget, $otw_wc_visible )  ){
									$filtered_widgets[] = $tmp_widget;
								}
							}
						}
						
						if( count( $filtered_widgets ) ){
							break;
						}
					}
				}
				
				if( isset( $filtered_widgets ) && is_array( $filtered_widgets ) && count( $filtered_widgets ) ){
					$collected_widgets = array();
					foreach( $filtered_widgets as $widget_order => $widget_name ){
						$collected_widgets[ $widget_name ] = $widget_order;
					}
					$collected_widgets = otw_filter_strict_widgets( $index, $collected_widgets );
					
					//fix the order of widgets
					if( is_array( $collected_widgets ) && count( $collected_widgets ) ){
						$filtered_widgets = array();
						asort( $collected_widgets );
						foreach( $collected_widgets as $tmp_widget => $tmp_order ){
							$filtered_widgets[] = $tmp_widget;
						}
					}
					else{
						$filtered_widgets = array();
					}
				}

				
			}else{
				$filtered_widgets = $sidebars_widgets[ $index ];
			}
		}
		return $filtered_widgets;
	}
}

/**
 * request sidebar with shortcode
 *
 * @param array attributes
 *
 * @return string
 */
if( !function_exists( 'otw_call_sidebar' ) ){
	function otw_call_sidebar( $attributes ){
		
		global $wp_registered_sidebars, $wp_registered_widgets;
		
		$sidebar_output = '';
		
		if( isset( $attributes['sidebar'] ) ){
			
			if( is_active_sidebar( $attributes['sidebar'] ) ){
				
				$index = otw_sidebar_index( $attributes['sidebar'] );
				
				$sidebars_widgets = wp_get_sidebars_widgets();
				
				//filter widgets for ths sidebar
				$sidebars_widgets[ $index ] = otw_filter_siderbar_widgets( $index, $sidebars_widgets );
				
				if( !count( $sidebars_widgets[ $index ] ) ){
					return;
				}
				
				$container = '<div class="otw-sidebar '.$attributes['sidebar'].'';
				$sidebar = $wp_registered_sidebars[ $index ];
				
				$widget_percentage = 0;
				
				$widget_alignement = 'vertical';
				
				switch( $widget_alignement ){
					
					case 'horizontal':
							$container .= ' otw-sidebar-horizontal';
							
							$widget_percentage = round( 100 / count( $sidebars_widgets[$index] ), 1 );
							
						break;
					default:
							$container .= ' otw-sidebar-vertical';
						break;
				}
				$container .= '">';
				
				ob_start();
				echo $container;
				
				$widget_number = 0;
				foreach( $sidebars_widgets[$index] as $id ) {
					
					if( !isset( $wp_registered_widgets[$id] ) ){
						continue;
					}
					$widget_number++;
					
					$params = array_merge(
						array( array_merge( $sidebar, array('widget_id' => $id, 'widget_name' => $wp_registered_widgets[$id]['name']) ) ),
						(array) $wp_registered_widgets[$id]['params']
					);
					$classname_ = 'widget otw-widget-'.$widget_number;
					
					if( $widget_number == 1 ){
						$classname_ .= ' widget-first';
					}elseif( $widget_number == count( $sidebars_widgets[$index] ) ){
						$classname_ .= ' widget-last';
					}
					
					foreach ( (array) $wp_registered_widgets[$id]['classname'] as $cn ) {
						if ( is_string($cn) ){
							$classname_ .= ' ' . $cn;
						}elseif ( is_object($cn) ){
							$classname_ .= ' ' . get_class($cn);
						}
					}
					$classname_ = ltrim($classname_, '_');
					
					if( $widget_percentage ){
						$params[0]['before_widget'] = '<div class="'.$classname_.'" style="width: '.$widget_percentage.'%;">';
					}else{
						$params[0]['before_widget'] = '<div class="'.$classname_.'">';
					}
					$params[0]['after_widget'] = '</div>';
					
					$params = apply_filters( 'otw_shortcode_sidebar_params', $params );
					
					$callback = $wp_registered_widgets[$id]['callback'];
					
					do_action( 'dynamic_sidebar', $wp_registered_widgets[$id] );
					
					if( is_callable($callback) ) {
						call_user_func_array($callback, $params);
					}
				}
				
				echo '</div>';
				$sidebar_output = ob_get_contents();
				ob_end_clean();
			}
		}
		return $sidebar_output;
	}
}


if( !function_exists( 'otw_get_current_object' ) ){
	function otw_get_current_object(){
		
		global $wp_query;
		$object = '';
		$object_id = 0;
		
		$objects[0][0] = '';
		$objects[0][1] = 0;
		
		wp_reset_query();
		
		if( is_page() ){
			$object = 'page';
			$query_object = $wp_query->get_queried_object();
			
			$object_id = $query_object->ID;
			
			if( is_page_template() ){
				$template_string = get_page_template();
				$template_parts = explode( "/", $template_string );
				$o_id = $template_parts[ count( $template_parts ) - 1 ];
				if( $o_id != 'page.php' ){
					$objects[1][0] = 'pagetemplate';
					$objects[1][1] = $o_id;
				}
			}
			
		}elseif( is_single() ){
			$post_type = get_post_type();
			
			$custom_post_types = get_post_types( array(  'public'   => true, '_builtin' => false ), 'object' );
			
			if( array_key_exists( $post_type, $custom_post_types )  ){
				
				$object = 'cpt_'.$post_type;
				$object_slug = get_query_var( $post_type );
				$posts = get_posts( array( 'name' => $object_slug, 'post_type' => $post_type, 'numberposts' => -1 ) );
				
				if( is_array( $posts ) && count( $posts ) ){
					$object_id = $posts[0]->ID;
				}
			}else{
				$object = 'post';
				$query_object = $wp_query->get_queried_object();
				
				$object_id = $query_object->ID;
			}
			
		}elseif( is_category() ){
			$object = 'category';
			$query_object = $wp_query->get_queried_object();
			$object_id = $query_object->term_id;
			
		}elseif( is_tag() ){
			$object = 'posttag';
			$query_object = $wp_query->get_queried_object();
			$object_id = $query_object->term_id;
		}elseif( is_archive() ){
			$object = 'archive';
			$object_id = 0;
			
			$query_object = $wp_query->get_queried_object();
			
			if( is_tax() ){
				$q_object = $wp_query->get_queried_object();
				
				$object = 'ctx_'.$q_object->taxonomy;
				$object_id = $q_object->term_id;
			}
			elseif( isset( $wp_query->query['year'] ) && isset( $wp_query->query['monthnum'] ) && isset( $wp_query->query['daily'] ) ){
				$object_id = 'daily';
			}
			elseif( isset( $wp_query->query['year'] ) && isset( $wp_query->query['monthnum'] ) ){
				$object_id = 'monthly';
			}
			elseif( isset( $wp_query->query['year'] ) ){
				$object_id = 'yearly';
			}
			
		}
		
		$objects[0][0] = $object;
		$objects[0][1] = $object_id;
		
		//add Template Hierarchy as next object
		$object_key = count( $objects );
		
		if( $object_key == 1 && !$objects[0][0] ){
			$object_key = 0;
		}
		
		if( is_front_page() ){
			$objects[ $object_key ][0] = 'templatehierarchy';
			$objects[ $object_key ][1] = 'front';
			$object_key++;
		}
		if( is_home() ){
			$objects[ $object_key ][0] = 'templatehierarchy';
			$objects[ $object_key ][1] = 'home';
			$object_key++;
		}
		if( is_404() ){
			$objects[ $object_key ][0] = 'templatehierarchy';
			$objects[ $object_key ][1] = '404';
			$object_key++;
		}
		if( is_search() ){
			$objects[ $object_key ][0] = 'templatehierarchy';
			$objects[ $object_key ][1] = 'search';
			$object_key++;
		}
		if( is_date() ){
			$objects[ $object_key ][0] = 'templatehierarchy';
			$objects[ $object_key ][1] = 'date';
			$object_key++;
		}
		if( is_author() ){
			$objects[ $object_key ][0] = 'templatehierarchy';
			$objects[ $object_key ][1] = 'author';
			$object_key++;
		}
		if( is_category() ){
			$objects[ $object_key ][0] = 'templatehierarchy';
			$objects[ $object_key ][1] = 'category';
			$object_key++;
		}
		if( is_tag() ){
			$objects[ $object_key ][0] = 'templatehierarchy';
			$objects[ $object_key ][1] = 'tag';
			$object_key++;
		}
		if( is_tax() ){
			$objects[ $object_key ][0] = 'templatehierarchy';
			$objects[ $object_key ][1] = 'taxonomy';
			$object_key++;
		}
		if( is_archive() ){
			$objects[ $object_key ][0] = 'templatehierarchy';
			$objects[ $object_key ][1] = 'archive';
			$object_key++;
		}
		if( is_single() ){
			$objects[ $object_key ][0] = 'templatehierarchy';
			$objects[ $object_key ][1] = 'single';
			$object_key++;
		}
		if( is_attachment() ){
			$objects[ $object_key ][0] = 'templatehierarchy';
			$objects[ $object_key ][1] = 'attachment';
			$object_key++;
		}
		if( is_page() ){
			$objects[ $object_key ][0] = 'templatehierarchy';
			$objects[ $object_key ][1] = 'page';
			$object_key++;
		}
		return $objects;
	} }
	
/** overwrites sidebar index based otw sitebar settings
  * @param string
  * @return string
  */
if( !function_exists( 'otw_sidebar_index' ) ){
	function otw_sidebar_index( $index ){
	
		global $wp_registered_sidebars, $otw_replaced_sidebars;
		
		$sidebars_widgets = wp_get_sidebars_widgets();
		
		if( isset( $otw_replaced_sidebars[ $index ] ) ){//we have set replacemend.
			
			$requested_objects = otw_get_current_object();
			
			//check if the new sidebar is valid for the current requested resource
			foreach( $otw_replaced_sidebars[ $index ] as $repl_sidebar ){
				
				if( isset( $wp_registered_sidebars[ $repl_sidebar ] ) ){
					
					if( $wp_registered_sidebars[ $repl_sidebar ]['status'] == 'active'  ){
						
						foreach( $requested_objects as $objects ){
						
							list( $object, $object_id ) = $objects;
							
							if( $object && $object_id ){
								
								$tmp_index = otw_validate_sidebar_index( $repl_sidebar, $object, $object_id );
								
								if( $tmp_index ){
									if ( !empty($sidebars_widgets[$tmp_index]) ){
										$sidebars_widgets[$tmp_index] = otw_filter_siderbar_widgets( $tmp_index, $sidebars_widgets );
										
										if( count( $sidebars_widgets[$tmp_index] ) ){
											$index = $tmp_index;
											break 2;
										}
									}
								}
								
							}//end hs object and object id
							
						}//end loop requested objects
						
					}
				}
			}
		}
		return $index;
	}
}


/** overwrites the default dynamic sidebar function
  * @param string
  * @return string
  */
if( !function_exists( 'otw_dynamic_sidebar' ) ){
	function otw_dynamic_sidebar( $index = 1 ){
		
		global $wp_registered_sidebars, $wp_registered_widgets;
		
		if ( is_int($index) ) {
			$index = "sidebar-$index";
		} else {
			$index = sanitize_title($index);
			foreach ( (array) $wp_registered_sidebars as $key => $value ) {
				if ( sanitize_title($value['name']) == $index ) {
					$index = $key;
					break;
				}
			}
		}
		
		$index = otw_sidebar_index( $index );
		
		$sidebars_widgets = wp_get_sidebars_widgets();
		
		//filter widgets for ths sidebar
		if( !is_admin() ){
			$sidebars_widgets[ $index ] = otw_filter_siderbar_widgets( $index, $sidebars_widgets );
		}
		
		if ( empty($wp_registered_sidebars[$index]) || !array_key_exists($index, $sidebars_widgets) || !is_array($sidebars_widgets[$index]) || empty($sidebars_widgets[$index]) || !count($sidebars_widgets[$index]) )
			return false;
	
		$sidebar = $wp_registered_sidebars[$index];
		
		$did_one = false;
		foreach ( (array) $sidebars_widgets[$index] as $id ) {
	
			if ( !isset($wp_registered_widgets[$id]) ) continue;
	
			$params = array_merge(
				array( array_merge( $sidebar, array('widget_id' => $id, 'widget_name' => $wp_registered_widgets[$id]['name']) ) ),
				(array) $wp_registered_widgets[$id]['params']
			);
	
			// Substitute HTML id and class attributes into before_widget
			$classname_ = '';
			foreach ( (array) $wp_registered_widgets[$id]['classname'] as $cn ) {
				if ( is_string($cn) )
					$classname_ .= '_' . $cn;
				elseif ( is_object($cn) )
					$classname_ .= '_' . get_class($cn);
			}
			$classname_ = ltrim($classname_, '_');
			$params[0]['before_widget'] = sprintf($params[0]['before_widget'], $id, $classname_);
	
			$params = apply_filters( 'dynamic_sidebar_params', $params );
	
			$callback = $wp_registered_widgets[$id]['callback'];
	
			do_action( 'dynamic_sidebar', $wp_registered_widgets[$id] );
	
			if ( is_callable($callback) ) {
				call_user_func_array($callback, $params);
				$did_one = true;
			}
		}
		
		return $did_one;
	}
}

/** get wp items based on type
  * @param string
  * @return array
  */
if( !function_exists( 'otw_get_wp_items' ) ){
	function otw_get_wp_items( $item_type ){
		switch( $item_type ){
			case 'page':
					$pages = get_pages();
					$pages = otw_group_items( $pages, 'ID', 'post_parent', 0 );
					return $pages;
				break;
			case 'post':
					return get_posts( array( 'numberposts' => -1 )  );
				break;
			case 'category':
					$categories = get_categories(array('hide_empty' => 0));
					$categories = otw_group_items( $categories, 'cat_ID', 'parent', 0 );
					return $categories;
				break;
			case 'posttag':
					return get_terms( 'post_tag', '&orderby=name&hide_empty=0' );
				break;
			case 'pagetemplate':
					$templates = array();
					$all_templates = get_page_templates();
					
					if( is_array( $all_templates ) && count( $all_templates ) )
					{
						foreach( $all_templates as $page_template_name => $page_template_script )
						{
							$tplObject = new stdClass();
							$tplObject->name = $page_template_name;
							$tplObject->script = $page_template_script;
							$templates[] = $tplObject;
						}
					}
					return $templates;
				break;
			case 'archive':
					$archive_types = array();
					$a_types = array( 'daily' => 'Daily', 'monthly' => 'Monthly', 'yearly' => 'Yearly' );
					foreach( $a_types as $a_type => $a_name )
					{
						$aObject = new stdClass();
						$aObject->ID = $a_type;
						$aObject->name = $a_name;
						$archive_types[] = $aObject;
					}
					return $archive_types;
				break;
			case 'customposttype':
					return get_post_types( array(  'public'   => true, '_builtin' => false ), 'object' );
				break;
			case 'templatehierarchy':
					$h_types = array();
					$a_types = array( 
							'home'        =>    'Home',
							'front'       =>    'Front Page',
							'404'         =>    'Error 404 Page',
							'search'      =>    'Search',
							'date'        =>    'Date',
							'author'      =>    'Author',
							'category'    =>    'Category',
							'tag'         =>    'Tag',
							'taxonomy'    =>    'Taxonomy',
							'archive'     =>    'Archive',
							'single'      =>    'Singular',
							'attachment'  =>    'Attachment',
							'page'        =>    'Page'
						);
					
					foreach( $a_types as $a_type => $a_name )
					{
						$aObject = new stdClass();
						$aObject->ID = $a_type;
						$aObject->name = $a_name;
						$h_types[] = $aObject;
					}
					return $h_types;
				break;
			default:
					if( preg_match( "/^cpt_(.*)$/", $item_type, $matches ) ){
						return get_posts( array( 'post_type' =>  $matches[1], 'numberposts' => -1 )  );
					}elseif( preg_match( "/^ctx_(.*)$/", $item_type, $matches ) ){
						return get_terms( $matches[1], '&orderby=name&hide_empty=0' );
					}
				break;
		}
	}
}

/** group wp items by level for better view
 * 
 * @param array
 * @param string
 * @param string
 * @param string
 * @param integer
 * @return array
 */ 
if( !function_exists( 'otw_group_items' ) ){
	function otw_group_items( $items, $id, $parent, $level, $sub_level = 0 ){
		
		$result = array();
		
		if( is_array( $items ) && count( $items ) ){
			
			foreach( $items as $item ){
				
				if( $item->$parent == $level ){
					$item->_sub_level = $sub_level;
					$result[] = $item;
					
					$sub_items = otw_group_items( $items, $id, $parent, $item->$id, $sub_level + 1 );
					
					if( is_array( $sub_items ) && count( $sub_items ) ){
						foreach( $sub_items as $s_item ){
							$result[] = $s_item;
						}
					}
				}
			}
		}
		
		return $result;
	}
}

/** get the attribute of wp item
  *  @param string
  *  @param stdClass
  *  @return string
  */
if( !function_exists( 'otw_wp_item_attribute' ) ){
	function otw_wp_item_attribute( $item_type, $attribute, $object ){
		
		switch( $attribute ){
			
			case 'ID':
					switch( $item_type ){
						case 'category':
								return $object->cat_ID;
							break;
						case 'posttag':
								return $object->term_id;
							break;
						case 'pagetemplate':
								return $object->script;
							break;
						case 'customposttype':
								return $object->name;
							break;
						default:
								if( preg_match( "/^ctx_(.*)$/", $item_type, $matches ) ){
									return $object->term_id;
								}elseif( preg_match( "/^(.*)_in_ctx_(.*)$/", $item_type, $matches ) ){
									return $object->term_id;
								}
								return $object->ID;
							break;
					}
				break;
			case 'TITLE':
					switch( $item_type ){
						case 'page':
						case 'post':
								return $object->post_title;
							break;
						case 'customposttype':
								return $object->label;
							break;
						default:
								if( preg_match( "/^cpt_(.*)$/", $item_type, $matches ) ){
									return $object->post_title;
								}
								return $object->name;
							break;
					}
				break;
		}
	}
}

/** sidebar widgets hook
  *  @param array
  *  @return array
  */
if( !function_exists( 'otw_sidebars_widgets' ) ){
	function otw_sidebars_widgets( $sidebars_widgets ){
		
		global $otw_registered_sidebars, $otw_replaced_sidebars;
		
		if( !is_array( $otw_replaced_sidebars ) || !count( $otw_replaced_sidebars ) ){
		//	return $sidebars_widgets;
		}
		
		if( is_admin() ){
			return $sidebars_widgets;
		}
		
		foreach( $sidebars_widgets as $index => $widgets ){
			
			
			$tmp_index = otw_sbm_index( $index, $sidebars_widgets );
			
			if ( !empty($sidebars_widgets[$tmp_index]) ){
				$sidebars_widgets[$index] = otw_filter_siderbar_widgets( $tmp_index, $sidebars_widgets );
			}else{
				$sidebars_widgets[$index] = $sidebars_widgets[$tmp_index];
			}
			
		}
		return $sidebars_widgets;
	}
}

if( !function_exists( 'otw_get_strict_filters' ) ){
	function otw_get_strict_filters(){
		
		global $current_user;
		$filters = array();
		
		//apply user roles
		if ( function_exists('get_currentuserinfo') ){
			get_currentuserinfo();
		}
		
		if( isset( $current_user->ID ) && intval( $current_user->ID ) && isset( $current_user->roles ) && is_array( $current_user->roles ) && count( $current_user->roles ) ){
			
			$filter_key = count( $filters );
			$filters[ $filter_key ][0] = 'userroles';
			$filters[ $filter_key ][1] = array();
			foreach( $current_user->roles as $u_role ){
				$filters[ $filter_key ][1][] = $u_role;
			}
			$filters[ $filter_key ][2] = 'any';
		}
		else
		{
			$filter_key = count( $filters );
			$filters[ $filter_key ][0] = 'userroles';
			$filters[ $filter_key ][1] = array();
			$filters[ $filter_key ][1][] = 'notlogged';
			$filters[ $filter_key ][2] = 'any';
		}
		
		if( function_exists( 'icl_get_languages' ) && defined( 'ICL_LANGUAGE_CODE' ) ){
			
			$filter_key = count( $filters );
			$filters[ $filter_key ][0] = 'wpmllanguages';
			$filters[ $filter_key ][1] = array();
			$filters[ $filter_key ][1][] = ICL_LANGUAGE_CODE;
			$filters[ $filter_key ][2] = 'all';
		}
		return $filters;
	}
}
/**
 * check all colected widgets for a sidebar if match all strict filters
 * @param string sidebar index
 * @param array collected widgets
 * @return array
 */
if( !function_exists( 'otw_filter_strict_widgets' ) ){
	function otw_filter_strict_widgets( $index, $collected_widgets ){
		
		global  $wp_registered_sidebars;
		
		$filters = otw_get_strict_filters();
		
		$strict_filtered_widgets = $collected_widgets;
		
		if( is_array( $filters ) && count( $filters ) ){
			
			if( isset( $wp_registered_sidebars[ $index ] ) ){
				
				if( is_array( $strict_filtered_widgets ) && count( $strict_filtered_widgets ) ){
				
					$filters = otw_get_strict_filters();
					
					foreach( $collected_widgets as $widget => $widget_order){
						
						foreach( $filters as $filter ){
							
							switch( $filter[2] ){
								case 'any':
										$match_any = false;
										
										if( isset( $wp_registered_sidebars[$index]['widgets_settings'] ) &&  isset( $wp_registered_sidebars[$index]['widgets_settings'][$filter[0]] ) ){
											
											if( isset( $wp_registered_sidebars[$index]['widgets_settings'][ $filter[0] ]['_otw_wc'] ) && isset( $wp_registered_sidebars[$index]['widgets_settings'][ $filter[0] ]['_otw_wc'][ $widget ] ) && in_array( $wp_registered_sidebars[$index]['widgets_settings'][ $filter[0] ]['_otw_wc'][ $widget ] , array( 'vis', 'invis' ) )  ){
												
												if( $wp_registered_sidebars[$index]['widgets_settings'][ $filter[0] ]['_otw_wc'][ $widget ] == 'vis' ){
													$match_any = true;
												}
											}else{
												foreach( $filter[1] as $v_filter ){
													
													if( !isset( $wp_registered_sidebars[$index]['widgets_settings'][ $filter[0] ][ $v_filter ] ) || !isset( $wp_registered_sidebars[$index]['widgets_settings'][ $filter[0] ][ $v_filter ]['exclude_widgets'] ) || !isset( $wp_registered_sidebars[$index]['widgets_settings'][ $filter[0] ][ $v_filter ]['exclude_widgets'][$widget] ) ){
														$match_any = true;
														break;
													}
												}
											}
										}elseif( isset( $wp_registered_sidebars[$index]['widgets_settings'] ) && !isset( $wp_registered_sidebars[$index]['widgets_settings'][$filter[0]] ) ){
											$match_any = true;
										}
										
										if( !$match_any && isset( $strict_filtered_widgets[ $widget ] ) ){
											unset( $strict_filtered_widgets[ $widget ] );
										}
									break;
								case 'all':
										$dont_match_one = false;
										
										if( isset( $wp_registered_sidebars[$index]['widgets_settings'] ) &&  isset( $wp_registered_sidebars[$index]['widgets_settings'][$filter[0]] ) ){
										
											if( isset( $wp_registered_sidebars[$index]['widgets_settings'][ $filter[0] ]['_otw_wc'] ) && isset( $wp_registered_sidebars[$index]['widgets_settings'][ $filter[0] ]['_otw_wc'][ $widget ] ) ){
												
												if( $wp_registered_sidebars[$index]['widgets_settings'][ $filter[0] ]['_otw_wc'][ $widget ] == 'invis' ){
													$dont_match_one = true;
												}
											}else{
												foreach( $filter[1] as $v_filter ){
													
													if( isset( $wp_registered_sidebars[$index]['widgets_settings'][ $filter[0] ][ $v_filter ] ) && isset( $wp_registered_sidebars[$index]['widgets_settings'][ $filter[0] ][ $v_filter ]['exclude_widgets'] ) && isset( $wp_registered_sidebars[$index]['widgets_settings'][ $filter[0] ][ $v_filter ]['exclude_widgets'][$widget] ) ){
														$dont_match_one = true;
													}
												}
											}
										}
										
										if( $dont_match_one && isset( $strict_filtered_widgets[ $widget ] ) ){
											unset( $strict_filtered_widgets[ $widget ] );
										}
									break;
							}
						}
					}
				}
			}
		}
		return $strict_filtered_widgets;
	}
}
/**
 * check if given sidebar match all strict filters
 * @param index sidebar index
 * @return boolean
 */
if( !function_exists( 'otw_filter_strict_sidebar_index' ) ){
	function otw_filter_strict_sidebar_index( $index ){
		
		global $wp_registered_sidebars;
		
		$result = true;
		
		$filters = otw_get_strict_filters();
		
		if( is_array( $filters ) && count( $filters ) ){
			
			if( $result ){
				
				foreach( $filters as $filter ){
					
					switch( $filter[2] ){
					
						case 'any':
								$match_any = false;
								if( isset( $wp_registered_sidebars[ $index ]['validfor'][ $filter[0] ] ) && is_array( $wp_registered_sidebars[ $index ]['validfor'][ $filter[0] ] ) && count( $wp_registered_sidebars[ $index ]['validfor'][ $filter[0] ] ) ){
									
									if( isset( $wp_registered_sidebars[ $index ]['validfor'][ $filter[0] ]['all'] ) ){
										$match_any = true;
									}else{
										foreach( $filter[1] as $s_filter ){
											
											if( array_key_exists( $s_filter, $wp_registered_sidebars[ $index ]['validfor'][ $filter[0] ] ) ){
											
												$match_any = true;
												break;
											}
										}
									}
								}
								if( !$match_any ){
									$result = false;
								}
							break;
						case 'all':
								$dont_match_one = false;
								
								foreach( $filter[1] as $s_filter ){
								
									if( isset( $wp_registered_sidebars[ $index ]['validfor'][ $filter[0] ] ) && is_array( $wp_registered_sidebars[ $index ]['validfor'][ $filter[0] ] ) && count( $wp_registered_sidebars[ $index ]['validfor'][ $filter[0] ] ) ){
										
										if( !isset( $wp_registered_sidebars[ $index ]['validfor'][ $filter[0] ]['all'] ) ){
											
											if( !array_key_exists( $s_filter, $wp_registered_sidebars[ $index ]['validfor'][ $filter[0] ] ) ){
												$dont_match_one = true;
												break;
											}
										}
									}else{
										$dont_match_one = true;
										break;
									}
								}
								if( $dont_match_one ){
									$result = false;
								}
							break;
					}
				}
			}
		}
		
		return $result;
		
	}
}
?>