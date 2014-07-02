<?php
/**
Plugin Name: Widgetize pages Light
Plugin URI: http://otwthemes.com/?utm_source=wp.org&utm_medium=admin&utm_content=site&utm_campaign=wpl
Description: Drop widgets in page or post content area. Widgetize a page. Build your custom page layout in no time. No coding, easy and fun! 
Author: OTWthemes.com
Version: 1.14
Author URI: http://otwthemes.com/?utm_source=wp.org&utm_medium=admin&utm_content=site&utm_campaign=wpl
*/
$wp_wpl_int_items = array(
	'page'              => array( array(), __( 'Pages' ), __( 'All pages' ) )
);

global $otw_plugin_options;

$otw_plugin_options = get_option( 'otw_plugin_options' );

$otw_wpl_plugin_url = plugins_url( substr( dirname( __FILE__ ), strlen( dirname( dirname( __FILE__ ) ) ) ) );

require_once( plugin_dir_path( __FILE__ ).'/include/otw_functions.php' );

//otw components
$otw_wpl_grid_manager_component = false;
$otw_wpl_grid_manager_object = false;

$otw_wpl_shortcode_component = false;

$otw_wpl_form_component = false;

//load core component functions
@include_once( 'include/otw_components/otw_functions/otw_functions.php' );

if( !function_exists( 'otw_register_component' ) ){
	wp_die( 'Please include otw components' );
}

//register grid manager component
otw_register_component( 'otw_grid_manager', dirname( __FILE__ ).'/include/otw_components/otw_grid_manager/', $otw_wpl_plugin_url.'/include/otw_components/otw_grid_manager/' );

//register form component
otw_register_component( 'otw_form', dirname( __FILE__ ).'/include/otw_components/otw_form/', $otw_wpl_plugin_url.'/include/otw_components/otw_form/' );

//register shortcode component
otw_register_component( 'otw_shortcode', dirname( __FILE__ ).'/include/otw_components/otw_shortcode/', $otw_wpl_plugin_url.'/include/otw_components/otw_shortcode/' );

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

function otw_wpl_editor_dialog(){
	require_once( 'include/otw_editor_dialog.php' );
	die;
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
	global $otw_wpl_plugin_url;
	
	add_menu_page('Widgetize pages', 'Widgetize pages', 'manage_options', 'otw-wpl', 'otw_wpl_sidebars_list', $otw_wpl_plugin_url.'/images/otw-sbm-icon.png' );
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
	global $otw_wpl_plugin_url;
	wp_enqueue_style( 'otw_wpl_sidebar', $otw_wpl_plugin_url.'/css/otw_sbm_admin.css', array( 'thickbox' ), '1.1' );
}

/**
 * register admin menu 
 */
add_action('admin_menu', 'otw_wpl_admin_actions');
add_action('admin_notices', 'otw_wpl_admin_notice');
add_filter('sidebars_widgets', 'otw_sidebars_widgets');

/**
 * include plugin js and css.
 */
add_action('admin_enqueue_scripts', 'enqueue_wpl_scripts');
add_action('admin_print_styles', 'enqueue_wpl_styles' );
add_shortcode('otw_is', 'otw_call_sidebar');

//register some admin actions
if( is_admin() ){
	add_action( 'wp_ajax_otw_wpl_shortcode_editor_dialog', 'otw_wpl_editor_dialog' );
}
/** 
 *call init plugin function
 */
add_action('init', 'otw_wpl_plugin_init', 104 );
?>