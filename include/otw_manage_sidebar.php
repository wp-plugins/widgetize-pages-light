<?php
/** Create/edit otw sidebar
  *
  */
	global $wp_registered_sidebars, $validate_messages, $wp_wpl_int_items;
	
	$otw_sidebar_values = array(
		'sbm_title'              =>  '',
		'sbm_description'        =>  '',
		'sbm_replace'            =>  '',
		'sbm_status'             =>  'active',
		'sbm_widget_alignment'   =>  'vertical'
	);
	
	$otw_sidebar_id = '';
	
	$page_title = __( 'Create New Sidebar' );

	if( isset( $_GET['sidebar'] ) ){
		
		$otw_sidebar_id = $_GET['sidebar'];
		$otw_sidebars = get_option( 'otw_sidebars' );
		
		if( is_array( $otw_sidebars ) && isset( $otw_sidebars[ $otw_sidebar_id ] ) && ( $otw_sidebars[ $otw_sidebar_id ]['replace'] == '' )  ){
			
			$otw_sidebar_values['sbm_title'] = $otw_sidebars[ $otw_sidebar_id ]['title'];
			$otw_sidebar_values['sbm_description'] = $otw_sidebars[ $otw_sidebar_id ]['description'];
			$otw_sidebar_values['sbm_replace'] = $otw_sidebars[ $otw_sidebar_id ]['replace'];
			$otw_sidebar_values['sbm_status'] = $otw_sidebars[ $otw_sidebar_id ]['status'];
			if( isset( $otw_sidebars[ $otw_sidebar_id ]['widget_alignment'] ) ){
				$otw_sidebar_values['sbm_widget_alignment'] = $otw_sidebars[ $otw_sidebar_id ]['widget_alignment'];
			}
			$otw_sidebar_values['sbm_validfor'] = $otw_sidebars[ $otw_sidebar_id ]['validfor'];
			$page_title = __( 'Edit Sidebar' );
		}
	}
	//apply post values
	if( isset( $_POST['otw_action'] ) ){
		foreach( $otw_sidebar_values as $otw_field_key => $otw_field_default_value ){
			if( isset( $_POST[ $otw_field_key ] ) ){
				$otw_sidebar_values[ $otw_field_key ] = $_POST[ $otw_field_key ];
			}
		}
	}

	foreach( $wp_wpl_int_items as $wp_item_type => $wp_item_data ){
		$wp_wpl_int_items[ $wp_item_type ][0] = otw_get_wp_items( $wp_item_type );
	}
	
/** set class name of each item block
  *  @param array
  *  @return void
  */
function otw_sidebar_block_class( $item_type, $sidebar_data ){
	
	if( isset( $_POST['otw_action'] ) ){
		if( !isset( $_POST[ 'otw_sbi_'.$item_type ] ) || !count( $_POST[ 'otw_sbi_'.$item_type ] ) ){
			echo ' open';
		}
	}else{
		if( !isset( $sidebar_data['sbm_validfor'][ $item_type ] ) || !count( $sidebar_data['sbm_validfor'][ $item_type ] ) ){
			echo ' open';
		}
	}
}

/** set html ot each item row
  *  @param string 
  *  @param string 
  *  @param string
  *  @param array
  *  @return void
  */
if (!function_exists( "otw_sidebar_item_attributes" )){
	function otw_sidebar_item_attributes( $tag, $item_type, $item_id, $sidebar_data, $item_data ){
      
		$attributes = '';
		
		switch( $tag ){
			case 'p':
					$attributes_array = array();
					if( isset( $_POST['otw_action'] ) ){
						if( isset( $_POST[ 'otw_sbi_'.$item_type ][ $item_id ] ) || isset( $_POST[ 'otw_sbi_'.$item_type ][ 'all' ] ) ){
							$attributes_array['class'][] = 'sitem_selected';
						}else{
							$attributes_array['class'][] = 'sitem_notselected';
						}
					}else{
						if( isset( $sidebar_data['sbm_validfor'][ $item_type ]['all'] ) ){
							$attributes_array['class'][] = 'sitem_selected';
						}elseif( isset( $sidebar_data['sbm_validfor'][ $item_type ][ $item_id ] ) ){
							$attributes_array['class'][] = 'sitem_selected';
						}else{
							$attributes_array['class'][] = 'sitem_notselected';
						}
					}
					if( isset( $attributes_array['class'] ) ){
						$attributes .= ' class="'.implode( ' ', $attributes_array['class'] ).'"';
					}
				break;
			case 'c':
					if( isset( $_POST['otw_action'] ) ){
						if( isset( $_POST[ 'otw_sbi_'.$item_type ][ $item_id ] )  || isset( $_POST[ 'otw_sbi_'.$item_type ][ 'all' ] ) ){
							$attributes .= ' checked="checked"';
						}
					}else{
						if( isset( $sidebar_data['sbm_validfor'][ $item_type ]['all'] ) ){
							$attributes .= ' checked="checked"';
						}elseif( isset( $sidebar_data['sbm_validfor'][ $item_type ][ $item_id ] ) ){
							$attributes .= ' checked="checked"';
						}
					}
				break;
			case 'ap':
					if( isset( $_POST['otw_action'] ) ){
						if( isset( $_POST[ 'otw_sbi_'.$item_type ][ $item_id ] ) ){
							$attributes .= ' class="all sitem_selected"';
						}else{
							$attributes .= ' class="all sitem_notselected"';
						}
					}else{
						if( isset( $sidebar_data['sbm_validfor'][ $item_type ][ $item_id ] ) ){
							$attributes .= ' class="all sitem_selected"';
						}else{
							$attributes .= ' class="all sitem_notselected"';
						}
					}
				break;
			case 'ac':
					if( isset( $_POST['otw_action'] ) ){
						if( isset( $_POST[ 'otw_sbi_'.$item_type ][ $item_id ] ) ){
							$attributes .= ' checked="checked"';
						}
					}else{
						if( isset( $sidebar_data['sbm_validfor'][ $item_type ][ $item_id ] ) ){
							$attributes .= ' checked="checked"';
						}
					}
				break;
			case 'l':
					if( isset( $item_data->_sub_level ) && $item_data->_sub_level ){
						$attributes .= ' style="margin-left: '.( $item_data->_sub_level * 20 ).'px"';
					}
				break;
		}
		echo $attributes;
	}
}
?>
<div class="updated"><p>Check out the <a href="http://otwthemes.com/online-documentation-widgetize-pages-light/?utm_source=wp.org&utm_medium=admin&utm_content=docs&utm_campaign=wpl">Online documentation</a> for this plugin<br /><br /> 
Upgrade to the full version of <a href="http://codecanyon.net/item/sidebar-widget-manager-for-wordpress/2287447?ref=OTWthemes">Sidebar and Widget Manager</a> | <a href="http://otwthemes.com/demos/1ts/?item=Sidebar%20Widget%20Manager&utm_source=wp.org&utm_medium=admin&utm_content=upgrade&utm_campaign=wpl">Demo site</a><br /><br />
<a href="http://otwthemes.com/widgetizing-pages-in-wordpress-can-be-even-easier-and-faster?utm_source=wp.org&utm_medium=admin&utm_content=site&utm_campaign=wpl">Create responsive layouts in minutes, drag & drop interface, feature rich.</a></p></div>
<div class="wrap">
	<div id="icon-edit" class="icon32"><br/></div>
	<h2>
		<?php echo $page_title; ?>
		<a class="button add-new-h2" href="admin.php?page=otw-wpl">Back To Available Sidebars</a>
	</h2>
	<?php if( isset( $validate_messages ) && count( $validate_messages ) ){?>
		<div id="message" class="error">
			<?php foreach( $validate_messages as $v_message ){
				echo '<p>'.$v_message.'</p>';
			}?>
		</div>
	<?php }?>
	<div class="form-wrap" id="poststuff">
		<form method="post" action="" class="validate">
			<input type="hidden" name="otw_wpl_action" value="manage_otw_sidebar" />
			<?php wp_original_referer_field(true, 'previous'); wp_nonce_field('otw-sbm-manage'); ?>

			<div id="post-body">
				<div id="post-body-content">
					<div id="col-right">
						&nbsp;
					</div>
					<div id="col-left">
						<div class="form-field form-required">
							<label for="sbm_title"><?php _e( 'Sidebar title' );?></label>
							<input type="text" id="sbm_title" value="<?php echo $otw_sidebar_values['sbm_title']?>" tabindex="1" size="30" name="sbm_title"/>
							<p><?php _e( 'The name is how it appears on your site.' );?></p>
						</div>
						<div class="form-field">
							<label for="sbm_description"><?php _e( 'Description' )?></label>
							<textarea id="sbm_description" name="sbm_description" tabindex="4" rows="3" cols="10"><?php echo $otw_sidebar_values['sbm_description']?></textarea>
							<p><?php _e( 'Short description for your reference at the admin panel.')?></p>
						</div>
						<p class="submit">
							<input type="submit" value="<?php _e( 'Save Sidebar') ?>" name="submit" class="button"/>
						</p>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>