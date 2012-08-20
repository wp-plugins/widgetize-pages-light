<?php
/**
Plugin Name: Widgetize pages Light
Plugin URI: http://otwthemes.com/?utm_source=wp.org&utm_medium=admin&utm_content=site&utm_campaign=wpl
Description:  Get full control over your sidebars (widgetized areas) and widgets. You can now customize each page with specific content and widgets that are relative to the content on that page. No coding required.
Author: OTWthemes.com
Version: 1.0
Author URI: http://otwthemes.com/?utm_source=wp.org&utm_medium=admin&utm_content=site&utm_campaign=wpl
*/
$wp_int_items = array(
	'page'              => array( array(), __( 'Pages' ), __( 'All pages' ) )
);

global $otw_plugin_options;

$otw_plugin_options = get_option( 'otw_plugin_options' );

include_once( plugin_dir_path( __FILE__ ).'/include/otw_plugin_activation.php' );
require_once( plugin_dir_path( __FILE__ ).'/include/otw_functions.php' );

/** calls list of available sidebars
  *
  */
function otw_wpl_sidebars_list(){
	if( isset( $_GET['action'] ) && $_GET['action'] == 'edit' ){
		require_once( 'include/otw_manage_sidebar.php' );
	}else{
		require_once( 'include/otw_list_sidebars.php' );
	}
}

/** calls page where to create new sidebars
  *
  */
function otw_wpl_sidebars_manage(){;
	require_once( 'include/otw_manage_sidebar.php' );
}

/** delete sidebar
  *
  */
function otw_wpl_sidebars_action(){
	require_once( 'include/otw_sidebar_action.php' );
}


/** plugin info
  *
  */
function otw_wpl_info(){
	require_once( 'include/otw_sidebar_info.php' );
}


/** admin menu actions
  * add the top level menu and register the submenus.
  */ 
function otw_wpl_admin_actions(){
	
	add_menu_page('Widgetize pages', 'Widgetize pages', 'manage_options', 'otw-wpl', 'otw_wpl_sidebars_list', plugins_url( 'otw_wpl/images/application_side_boxes.png' ) );
	add_submenu_page( 'otw-wpl', 'Sidebars', 'Sidebars', 'manage_options', 'otw-wpl', 'otw_wpl_sidebars_list' );
	add_submenu_page( 'otw-wpl', 'Add Sidebar', 'Add Sidebar', 'manage_options', 'otw-wpl-add', 'otw_wpl_sidebars_manage' );
	add_submenu_page( 'otw-wpl', 'Info', 'Info', 'manage_options', 'otw-wpl-info', 'otw_wpl_info' );
	add_submenu_page( __FILE__, 'Manage widget', 'Manage widget', 'manage_options', 'otw-wpl-action', 'otw_wpl_sidebars_action' );
}

/** include needed javascript scripts based on current page
  *  @param string
  */
function enqueue_wpl_scripts( $requested_page ){

}

/**
 * include needed styles
 */
function enqueue_wpl_styles( $requested_page ){
	wp_enqueue_style( 'otw_wpl_sidebar', plugins_url('otw_wpl/css/otw_sbm_admin.css'), array( 'thickbox' ), '1.1' );
}

/**
 * register admin menu 
 */
add_action('admin_menu', 'otw_wpl_admin_actions');
add_action('admin_notices', 'otw_wpl_admin_notice');

/**
 * include plugin js and css.
 */
add_action('admin_enqueue_scripts', 'enqueue_wpl_scripts');
add_action('admin_print_styles', 'enqueue_wpl_styles' );
add_shortcode('otw_is', 'otw_call_sidebar');

/** 
 *call init plugin function
 */
add_action('init', 'otw_wpl_plugin_init', 100 );

include_once( plugin_dir_path( __FILE__ ).'/include/otw_plugin_activation.php' );

register_activation_hook(  __FILE__,'otw_wpl_plugin_activate');
register_deactivation_hook(  __FILE__,'otw_wpl_plugin_deactivate');