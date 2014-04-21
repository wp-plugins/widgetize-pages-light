<?php
class OTW_Grid_Manager extends OTW_Component{
	
	/**
	 * Name of the meta field
	 * 
	 * @var  string 
	 */
	public $meta_name = 'otw_grid_manager_content';
	
	/** Available in pages
	 * 
	 *  @var boolean
	 */
	
	public $active_for_pages = false;
	
	/**
	 *  Numbers
	 *
	 *  @var array
	 */
	
	public $number_names = array( 'zero', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine', 'ten', 'eleven', 'twelve', 'thirteen', 'fourteen', 'fifteen', 'sixteen', 'seventeen', 'eighteen', 'nineteen', 'twenty', 'twentyone', 'twentytwo', 'twentythree', 'twentyfour');
	
	/** Grid size
	 *
	 *  @var integer
	 */
	
	public $grid_size = 24;
	
	/** Mobile grid size
	 *
	 *  @var integer
	 */
	
	private $mobile_grid_size = 6;
	
	/** Mobile grid columns
	 *
	 *  @var integer
	 */
	
	private $mobile_grid_columns = array( '1_1' => '1/1', '1_2' => '1/2', '1_3' => '1/3', '2_3' => '2/3');
	
	
	/** Available in posts
	 * 
	 *  @var boolean
	 */
	
	public $active_for_posts = false;
	
	/**
	 * Meta info text
	 */
	public $meta_info;
	
	
	public function __construct(){
		
		if( is_admin() ){
			add_action( 'wp_ajax_otw_grid_manager_column_dialog', array( &$this, 'build_add_column_dialog' ) );
			add_action( 'wp_ajax_otw_grid_manager_save_template', array( &$this, 'otw_save_template' ) );
			add_action( 'wp_ajax_otw_grid_manager_delete_template', array( &$this, 'otw_delete_template' ) );
			add_action( 'wp_ajax_otw_grid_manager_load_template', array( &$this, 'otw_load_template' ) );
		}
		
		add_shortcode( 'otw_shortcode_grid_column', array( &$this, 'otw_shortcode_grid_column' ) );
		add_filter('the_content','otw_pre_content_wrapper', 100000 );
		add_filter('the_content',array( &$this, 'filter_show_meta_content' ), 100001 );
		add_filter('the_content','otw_post_content_wrapper', 200001 );
	}
	
	/**
	 *  Init 
	 */
	public function init(){
		
		global $pagenow;
		
		$register_meta = false;
		
		if( $this->active_for_pages && ( get_post_type() == 'page' ) ){
			$register_meta = true;
		}
		
		if( $this->active_for_posts && ( $pagenow == 'post.php' || $pagenow == 'post-new.php' ) ){
			$register_meta = true;
		}
		
		
		if( $register_meta ){
			
			if( is_admin() ){
				wp_enqueue_script('otw_grid_manager_admin', $this->component_url.'js/otw_grid_manager_admin.js?' , array( 'jquery' ), '1.1' );
				wp_enqueue_style( 'otw_grid_manager_admin', $this->component_url.'css/otw-grid-admin.css', array( ), '1.1' );
				wp_enqueue_style( 'otw_grid_manager', $this->component_url.'css/otw-grid.css', array( ), '1.1' );
				add_action( 'add_meta_boxes', array( &$this, 'action_meta_boxes' ) );
				add_action( 'save_post', array( &$this, 'action_save_post' ) );
			}
		}
		
		if( !is_admin() ){
			wp_enqueue_style( 'otw_grid_manager', $this->component_url.'css/otw-grid.css', array( ), '1.1' );
		}
	}
	
	/** 
	 *  Meta boxes action
	 */
	public function action_meta_boxes(){
		
		add_meta_box( $this->meta_name, $this->get_label('OTW Grid Manager Light'), array( &$this, 'build_meta_box' ), '', 'normal', 'high');
	}
	
	/** 
	 *  Save post
	 */
	public function action_save_post( $post_id ){
		
		if ( !isset( $_POST[ $this->meta_name.'_noncename' ] ) || !wp_verify_nonce( $_POST[$this->meta_name.'_noncename' ],__FILE__) ){
			return $post_id;
		}
		
		// validate user can edit
		if($_POST['post_type'] == 'page'){
			if (!current_user_can('edit_page', $post_id)){
				return $post_id;
			}
		}else{
			if (!current_user_can('edit_post', $post_id)){
				return $post_id;
			}
		}
		
		$current_code = get_post_meta($post_id, $this->meta_name, TRUE);
		$new_code = $_POST[ '_'.$this->meta_name ]['code'];
		
		if( !$current_code && strlen( trim( $new_code ) ) ){
			update_post_meta($post_id,$this->meta_name,$new_code);
		}else{
			if( !strlen( trim( $new_code ) ) ){
				delete_post_meta($post_id,$this->meta_name);
			}else{
				update_post_meta($post_id,$this->meta_name,$new_code);
			}
		}
		
		return $post_id;
	}
	
	/**
	 *  Render meta box content
	 */
	public function build_meta_box(){
		
		global $post_id;
		
		$current_code = get_post_meta($post_id, $this->meta_name, TRUE);
		
		if( isset( $_POST['_'.$this->meta_name]['code'] ) ){
			$current_code = $_POST['_'.$this->meta_name]['code'];
		}
		
		//templates
		$templates = get_option( $this->meta_name.'_templates' );
		$js_templates = array();
		
		if( strlen( trim( $templates ) ) ){
			$templates_array = unserialize( $templates );
			
			if( is_array( $templates_array ) ){
				
				foreach( $templates_array as $template_key => $template ){
					$js_templates[] = array( $template_key, $template['name'] );
				}
			}
		
		}
		
		$content = "";
		
		$content .= "<div id=\"".$this->meta_name."_container\" class=\"otw_grid_manager_container\">";
		
		if( isset( $this->meta_info ) && strlen( $this->meta_info ) ){
			$content .= $this->meta_info;
		}
		
		$content .= "\n<p class=\"otw_grid_manager_info\">";
		$content .= "<a href=\"javascript:;\" id=\"".$this->meta_name."_info_button\" class=\"otw_grid_manager_info_button\">".$this->get_label('Info')."</a>";
		$content .= "\n</p>"; 
		$content .= "\n<p>";
		$content .= "<a href=\"javascript:;\" id=\"".$this->meta_name."_add_row\" class=\"button button-small\">".$this->get_label('Add Row')."</a>";
		$content .= "</p>";  
    $content .= "\n<p class=\"otw_grid_manager_info_block\" id=\"".$this->meta_name."_info_block\">";
		$content .= $this->get_label('Add some rows and columns in the rows.<br />Then you will be able to add items (actual content) in the columns.');
		
		$content .= "\n<div id=\"".$this->meta_name."_preview\" class=\"otw_grid_manager_content_preview\"></div>";
		$content .= "\n<input type=\"hidden\" id=\"".$this->meta_name."_code\" name=\"_".$this->meta_name."[code]\" value=\"".htmlentities( $current_code, ENT_QUOTES, "UTF-8")."\" />";
		$content .= "\n<input type=\"hidden\" name=\"".$this->meta_name."_noncename\" value=\"" . wp_create_nonce(__FILE__) . "\" />";
		
		$content .= "\n<script type=\"text/javascript\">";
		$content .= "\njQuery(document).ready(function(){
				otw_grid_manager_".$this->meta_name." = new otw_grid_manager_object( '".$this->meta_name."', ".json_encode( $this->labels ).", ".json_encode( $js_templates )."  );";
		$content .= "\n});";
		$content .=  "\n</script>";
		$content .= "</div>";
		echo $content;
	}
	
	public function build_add_column_dialog(){
		
		$content = "";
		
		$content .= "\n<div>";
		$content .= "\n<h3>".$this->get_label('Choose column type')."</h3>";
		$content .= "\n<div class=\"otw_grid_manager_column_dlg\">";
		$content .= "\n<div class=\"otw_grid_manager_column_dlg_container\">";
		$content .= "\n<div class=\"otw_grid_manager_column_dlg_row\">";
		$content .= "\n<div class=\"otw-twentyfour otw-columns\"><div class=\"otw-column-content otw-column-1_1\">1/1</div></div>";
		$content .= "\n</div>";
		$content .= "\n<div class=\"otw_grid_manager_column_dlg_row\">";
		$content .= "\n<div class=\"otw-twelve otw-columns\"><div class=\"otw-column-content otw-column-1_2\">1/2</div></div>";
		$content .= "\n</div>";
		$content .= "\n<div class=\"otw_grid_manager_column_dlg_row\">
				<div class=\"otw-eight otw-columns\"><div class=\"otw-column-content otw-column-1_3\">1/3</div></div>
				<div class=\"otw-sixteen otw-columns\"><div class=\"otw-column-content otw-column-2_3\">2/3</div></div>
			</div>";
		$content .= "\n<div class=\"otw_grid_manager_column_dlg_row\">
				<div class=\"otw-six otw-columns\"><div class=\"otw-column-content otw-column-1_4\">1/4</div></div>
				<div class=\"otw-eighteen otw-columns\"><div class=\"otw-column-content otw-column-3_4\">3/4</div></div>
			</div>";
		$content .= "\n<div class=\"otw_grid_manager_column_dlg_row\">
				<div class=\"otw-four otw-columns\"><div class=\"otw-column-content otw-column-1_6\">1/6</div></div>
				<div class=\"otw-twenty otw-columns\"><div class=\"otw-column-content otw-column-5_6\">5/6</div></div>
			</div>";
		$content .= "\n</div><div>";
		$content .= "\n<h3 class=\"adv_settings\" id=\"adv_settings\">".$this->get_label('Advanced settings')."</h3>";
		$content .= "\n<div class=\"adv_settings_container\" id=\"adv_settings_mobile_container\">";
		$content .= "\n<h4 class=\"otw_mobile\"><span>".$this->get_label( 'Mobile Grid' )."</span><img src=\"".$this->component_url."img/gm-advanced-down-arrow.png\" alt=\"\" /></h4>";
		$content .= "\n<div id=\"adv_settings_mobile_content\">";
		$content .= "\n<p>".$this->get_label( 'The grid has two modes of adapting for small displays like phones.<br />The first requires no work at all - the grid will linearize on a small device so your columns stack vertically (all in one column). If you don’t know what you are doing leave it as is and it should be all fine.<br />The other mode can take your existing grid elements and attach them to a six column phone grid. This means you can have 1, 2 or 3 columns on mobile devices. Note that you need to have all columns in a given row set up correctly in order to make it work.<br />Here is an example of two columns in a row.<br />This is correct: 1/2 +1/2<br />This is incorrect: 2/3 + 1/2 because it is more than a full row<br />This is incorrect: 1/3 + 1/3 because it doesn’t make a full row.' )."</p>";
		$content .= "\n".OTW_Form::select( array( 'id' => 'otw_mobile_column_size', 'options' => $this->mobile_grid_columns ) );
		$content .= "\n</div>";
		$content .= "\n</div>";
		
		$content .= "\n<div class=\"alignleft otw_grid_manager_column_dlg_row_buttons\">
					<input type=\"button\" accesskey=\"S\" value=\"".$this->get_label('Save')."\" name=\"save_settings\" class=\"button\" id=\"otw-shortcode-btn-save\">
					<input type=\"button\" accesskey=\"C\" value=\"".$this->get_label('Cancel')."\" name=\"cancel_settings\" class=\"button\" id=\"otw-shortcode-btn-cancel\">
				</div>";

		$content .= "\n</div>";
		echo $content;
		die;
	}
	
	/** 
	 *  Show meta content
	 */
	public function filter_show_meta_content( $post_content ){
		
		global $post;
		
		$meta_code = '';
		
		$meta_content = get_post_meta( $post->ID, $this->meta_name, TRUE );
		
		if( strlen( $meta_content ) ){
			
			$rows = json_decode( $meta_content );
			
			$is_content_sidebars_content = otw_is_content_sidebars_content();
			
			if( $rSize = count( $rows ) ){
				
				if( !$is_content_sidebars_content ){
					$meta_code .= " <div class=\"otw-row\">";
				}
				
				foreach( $rows as $row ){
					
					if( $cSize = count( $row->columns )){
					
						
						$meta_code .= "<div class=\"otw-row\">";
						
						$cCount = 1;
						foreach( $row->columns as $column ){
							
							$is_last = '';
							
							if( $cCount == $cSize ){
								$is_last = ' last="1"';
							}
							
							$column_class = $this->number_names[ ( ( $this->grid_size / $column->from_rows ) * $column->rows ) ];
							$meta_code .= '[otw_shortcode_grid_column rows="'.$column->rows.'" from_rows="'.$column->from_rows.'" mobile_rows="'.$column->mobile_rows.'" mobile_from_rows="'.$column->mobile_from_rows.'"'.$is_last.']';
							if( count( $column->shortcodes ) ){
								
								foreach( $column->shortcodes as $shortcode ){
									$meta_code .= $shortcode->shortcode_code;
								}
							}
							$meta_code .= '[/otw_shortcode_grid_column]';
							$cCount++;
						}
						$meta_code .= "</div>";
				
					}
				}
				if( !$is_content_sidebars_content ){
					$meta_code .= "</div>";
				}
			}
		}
		
		return $post_content.' '.$this->otw_shortcode_remove_wpautop( $meta_code );
	}
	/**
	 * Column Shortcode
	 */
	public function otw_shortcode_grid_column( $attributes, $content ){
	
		$column_class = $this->number_names[ ( ( $this->grid_size / $attributes['from_rows'] ) * $attributes['rows'] ) ];
		
		if( isset( $attributes['mobile_rows'] ) && isset( $attributes['mobile_from_rows'] ) && ( $attributes['mobile_rows'] > 0 ) && ( $attributes['mobile_from_rows'] > 0 ) ){
			$column_class .= ' mobile-'.$this->number_names[ ( ( $this->mobile_grid_size / $attributes['mobile_from_rows'] ) * $attributes['mobile_rows'] ) ];
		}
		
		$is_last = '';
		if( isset( $attributes['last'] ) && ( $attributes['last'] == 1 ) ){
			$is_last = ' end';
		}
		$html  = "";
		$html .= "<div class=\"otw-".$column_class." otw-columns".$is_last."\">";
		$html .= $this->otw_shortcode_remove_wpautop( $content );
		$html .= "</div>";
	
		return $html;
	}
	
	/**
	 * Load saved template
	 */
	public function otw_load_template(){
		
		if( isset( $_POST['grid_manager'] ) && ( $_POST['grid_manager'] == $this->meta_name ) && isset( $_POST['template_key']) ){
			
			$saved_templates = get_option( $this->meta_name.'_templates', '' );
			
			if( strlen( trim( $saved_templates ) ) ){
				$saved_templates_array = unserialize( $saved_templates );
				
				if( !$saved_templates_array ){
					$saved_templates_array = array();
				}
				
			}else{
				$saved_templates_array = array();
			}
			
			if( array_key_exists( $_POST['template_key'], $saved_templates_array ) ){
				
				echo stripslashes( $saved_templates_array[ $_POST['template_key'] ]['code'] );
				die;
			}
		}
		echo '-1';
		die;
	}
	
	/**
	 * Deleted saved template
	 */
	public function otw_delete_template(){
		
		if( isset( $_POST['grid_manager'] ) && ( $_POST['grid_manager'] == $this->meta_name ) && isset( $_POST['template_key']) ){
			
			$saved_templates = get_option( $this->meta_name.'_templates', '' );
			
			if( strlen( trim( $saved_templates ) ) ){
				$saved_templates_array = unserialize( $saved_templates );
				
				if( !$saved_templates_array ){
					$saved_templates_array = array();
				}
				
			}else{
				$saved_templates_array = array();
			}
			
			if( array_key_exists( $_POST['template_key'], $saved_templates_array ) ){
				unset( $saved_templates_array[ $_POST['template_key'] ] );
			}
			
			update_option(  $this->meta_name.'_templates',  serialize( $saved_templates_array ) );
			
			$js_template = array();
			foreach( $saved_templates_array as $template_key => $template ){
				$js_template[] = array( $template_key, $template['name'] );
			}
			echo json_encode( $js_template );
			die;
		}
		echo '-1';
		die;
	}
	
	/**
	 * Save current page as template
	 */
	public function otw_save_template(){
	
		$saved_templates = get_option( $this->meta_name.'_templates', '' );
		
		if( isset( $_POST['grid_manager'] ) && ( $_POST['grid_manager'] == $this->meta_name ) ){
			
			if( strlen( trim( $saved_templates ) ) ){
				$saved_templates_array = unserialize( $saved_templates );
				if( !$saved_templates_array ){
					$saved_templates_array = array();
				}
			}else{
				$saved_templates_array = array();
			}
			
			if( isset( $_POST['template_name'] ) &&  strlen( trim( $_POST['template_name'] ) ) ){
				
				$saved_templates_array[] = array( 'name' => $_POST['template_name'], 'code' => $_POST['template_code'] );
				
				update_option(  $this->meta_name.'_templates',  serialize( $saved_templates_array ) );
			}
			
			$js_template = array();
			foreach( $saved_templates_array as $template_key => $template ){
				$js_template[] = array( $template_key, $template['name'] );
			}
			echo json_encode( $js_template );
			die;
		}
		echo '-1';
		die;
	}
	
	/**
	 * Check if the content sidebars component will change the content of current requested object
	 * 
	 * @return boolean
	 */
	public function is_valid_for_object(){
		
		if( isset( $GLOBALS['wp_query'] ) ){
			
			global $post;
			
			if( isset( $post->ID ) && $post->ID ){
				
				$meta_content = get_post_meta( $post->ID, $this->meta_name, TRUE );
				
				if( strlen( $meta_content ) ){
				
					$rows = json_decode( $meta_content );
					
					if( $rSize = count( $rows ) ){
						
						foreach( $rows as $row ){
							return true;
						}
					}
				}
			}
		}
		
		return false;
	}

}
?>