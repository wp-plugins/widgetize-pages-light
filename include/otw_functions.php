<?php
/** init plugin
  *
  */
function otw_wpl_plugin_init(){
	
	global $wp_registered_sidebars, $otw_replaced_sidebars, $wp_wpl_int_items, $otw_wpl_plugin_url, $otw_wpl_grid_manager_component, $otw_wpl_shortcode_component, $otw_wpl_form_component, $otw_wpl_grid_manager_object;
	
	if( is_admin() ){
		if( function_exists( 'otwrem_dynamic_sidebar' ) ){
			update_option( 'otw_wpl_plugin_error', '' );
		}
	}
	
	$otw_registered_sidebars = get_option( 'otw_sidebars' );
	$otw_widget_settings = get_option( 'otw_widget_settings' );
	
	if( !is_array( $otw_widget_settings ) ){
		$otw_widget_settings = array();
		update_option( 'otw_widget_settings', $otw_widget_settings );
	}
	
	if( is_array( $otw_registered_sidebars ) && count( $otw_registered_sidebars ) ){
		
		foreach( $otw_registered_sidebars as $otw_sidebar_id => $otw_sidebar ){
			
			$sidebar_params = array();
			$sidebar_params['id']  = $otw_sidebar_id;
			$sidebar_params['name']  = $otw_sidebar['title'];
			$sidebar_params['description']  = $otw_sidebar['description'];
			$sidebar_params['replace']  = $otw_sidebar['replace'];
			$sidebar_params['status']  = $otw_sidebar['status'];
			if( isset( $otw_sidebar['widget_alignment'] ) ){
				$sidebar_params['widget_alignment']  = $otw_sidebar['widget_alignment'];
			}
			$sidebar_params['validfor']  = $otw_sidebar['validfor'];
			
			//collect all replacements for faster search in font end
			if( strlen( $sidebar_params['replace'] ) ){
			
				if( !isset( $otw_replaced_sidebars[ $sidebar_params['replace'] ] ) ){
					$otw_replaced_sidebars[ $sidebar_params['replace'] ] = array();
				}
				$otw_replaced_sidebars[ $sidebar_params['replace'] ][ $sidebar_params['id'] ] = $sidebar_params['id'];
				
				if( isset( $wp_registered_sidebars[ $sidebar_params['replace'] ] ) ){
					if( isset( $wp_registered_sidebars[ $sidebar_params['replace'] ]['class'] ) ){
						$sidebar_params['class'] = $wp_registered_sidebars[ $sidebar_params['replace'] ]['class'];
					}
					if( isset( $wp_registered_sidebars[ $sidebar_params['replace'] ]['before_widget'] ) ){
						$sidebar_params['before_widget'] = $wp_registered_sidebars[ $sidebar_params['replace'] ]['before_widget'];
					}
					if( isset( $wp_registered_sidebars[ $sidebar_params['replace'] ]['after_widget'] ) ){
						$sidebar_params['after_widget'] = $wp_registered_sidebars[ $sidebar_params['replace'] ]['after_widget'];
					}
					if( isset( $wp_registered_sidebars[ $sidebar_params['replace'] ]['before_title'] ) ){
						$sidebar_params['before_title'] = $wp_registered_sidebars[ $sidebar_params['replace'] ]['before_title'];
					}
					if( isset( $wp_registered_sidebars[ $sidebar_params['replace'] ]['after_title'] ) ){
						$sidebar_params['after_title'] = $wp_registered_sidebars[ $sidebar_params['replace'] ]['after_title'];
					}
				}
				
			}else{
				$sidebar_params['before_widget'] = '';
				$sidebar_params['after_widget']  = '';
			}
			
			register_sidebar( $sidebar_params );
		}
	}
	
	//apply validfor settings to all sidebars
	if( is_array( $wp_registered_sidebars ) && count( $wp_registered_sidebars ) ){
		foreach( $wp_registered_sidebars as $wp_widget_key => $wo_widget_data ){
		
			if( array_key_exists( $wp_widget_key, $otw_widget_settings ) ){
				$wp_registered_sidebars[ $wp_widget_key ]['widgets_settings'] = $otw_widget_settings[ $wp_widget_key ];
			}else{
				$wp_registered_sidebars[ $wp_widget_key ]['widgets_settings'] = array();
			}
		}
	}
	
	//otw grid manager component
	include_once( plugin_dir_path( __FILE__ ).'otw_wpl_grid_meta_info.php' );
		
	$otw_wpl_grid_manager_component = otw_load_component( 'otw_grid_manager' );
	$otw_wpl_grid_manager_object = otw_get_component( $otw_wpl_grid_manager_component );
	$otw_wpl_grid_manager_object->active_for_posts = true;
	$otw_wpl_grid_manager_object->meta_info = $otw_wpl_grid_meta_info;
	
	include_once( plugin_dir_path( __FILE__ ).'otw_labels/otw_sbm_grid_manager_object.labels.php' );
	$otw_wpl_grid_manager_object->init();
	
	//shortcode component
	$otw_wpl_shortcode_component = otw_load_component( 'otw_shortcode' );
	$otw_wpl_shortcode_object = otw_get_component( $otw_wpl_shortcode_component );
	$otw_wpl_shortcode_object->shortcodes['sidebar'] = array( 'title' => __('OTW Sidebar', 'otw_sbm'),'enabled' => true,'children' => false,'order' => 100000,'path' => dirname( __FILE__ ).'/otw_components/otw_shortcode/', 'url' => $otw_wpl_plugin_url.'/include/otw_components/otw_shortcode/' );
	include_once( plugin_dir_path( __FILE__ ).'otw_labels/otw_sbm_shortcode_object.labels.php' );
	$otw_wpl_shortcode_object->init();
	
	//form component
	$otw_wpl_form_component = otw_load_component( 'otw_form' );
	$otw_wpl_form_object = otw_get_component( $otw_wpl_form_component );
	include_once( plugin_dir_path( __FILE__ ).'otw_labels/otw_sbm_form_object.labels.php' );
	$otw_wpl_form_object->init();
	
	if( is_admin() ){
		require_once( plugin_dir_path( __FILE__ ).'/otw_process_actions.php' );
		
		if( get_user_option('rich_editing') ){
			add_filter('mce_external_plugins', 'add_otw_wpl_tinymce_plugin');
			add_filter('mce_buttons', 'register_otw_wpl_tinymce_button');
		}
	}else{
	}
}

/**
 * add tinymce plugin
 */
function add_otw_wpl_tinymce_plugin($plugin_array){
	global $otw_wpl_plugin_url;
	$plugin_array['otwsbm'] = $otw_wpl_plugin_url.'/js/otw_editor_plugin.js';
	return $plugin_array;
}
/**
 * register button plugin
 */
function register_otw_wpl_tinymce_button($buttons){
	array_push($buttons, "separator", "otwsbm");
	return $buttons;
}

function otw_wpl_admin_notice(){
	$plugin_error = get_option( 'otw_wpl_plugin_error' );
	
	if( $plugin_error ){
		echo '<div class="error"><p>';
		echo 'Widgetize pages Light Plugin Error: '.$plugin_error;
		echo '</p></div>';
	}
}


require_once( plugin_dir_path( __FILE__ ).'otw_sbm_core.php' );
?>