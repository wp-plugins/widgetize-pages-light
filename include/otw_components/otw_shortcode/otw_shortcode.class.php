<?php
class OTW_Shortcode extends OTW_Component{
	
	
	/**
	 *  List with all available shortcodes
	 */
	public $shortcodes = array();
	
	/**
	 *  List settings for all shortcodes
	 */
	public $shortcode_settings = array();
	
	/**
	 * construct
	 */
	public function __construct(){
		
	}
	
	public function apply_shortcode_settings(){
		
		foreach( $this->shortcodes as $shortcode_key => $shortcode_settings ){
			$this->shortcodes[ $shortcode_key ]['object']->apply_settings();
		}
	}
	
	public function include_shortcodes(){
		
		include_once( $this->component_path.'shortcodes/otw_shortcodes.class.php' );
		
		foreach( $this->shortcodes as $shortcode_key => $shortcode_settings ){
		
			if( !class_exists( 'OTW_ShortCode_'.$shortcode_key ) ){
				if( isset( $shortcode_settings['path'] ) ){
					include_once( $shortcode_settings['path'].'shortcodes/otw_shortcode_'.$shortcode_key.'.class.php' );
				}else{
					include_once( $this->component_path.'shortcodes/otw_shortcode_'.$shortcode_key.'.class.php' );
				}
			}
			$class_name = 'OTW_ShortCode_'.$shortcode_key;
			$this->shortcodes[ $shortcode_key ]['object'] = new $class_name;
			$this->shortcodes[ $shortcode_key ]['object']->labels = $this->labels;
			$this->shortcodes[ $shortcode_key ]['object']->mode = $this->mode;
			
			if( isset( $shortcode_settings['url'] ) ){
				$this->shortcodes[ $shortcode_key ]['object']->component_url = $shortcode_settings['url'];
			}else{
				$this->shortcodes[ $shortcode_key ]['object']->component_url = $this->component_url;
			}
			
			if( isset( $shortcode_settings['path'] ) ){
				$this->shortcodes[ $shortcode_key ]['object']->component_path = $shortcode_settings['path'];
			}else{
				$this->shortcodes[ $shortcode_key ]['object']->component_path = $this->component_path;
			}
		}
	}
	
	public function register_shortcodes(){
	
		if( count( $this->shortcodes ) ){
			uasort( $this->shortcodes, array( $this, 'sort_shortcodes' ) );
		}
		
		foreach( $this->shortcodes as $shortcode_key => $shortcode_data ){
			add_shortcode( 'otw_shortcode_'.$shortcode_key, array( &$this->shortcodes[ $shortcode_key ]['object'], 'display_shortcode' ) );
		}
	}
	
	/**
	 *  Init 
	 */
	public function init(){
		
		$this->include_shortcodes();
		
		$this->apply_shortcode_settings();
		
		$this->register_shortcodes();
		
		if( is_admin() ){
			wp_enqueue_script('otw_shortcode_admin', $this->component_url.'js/otw_shortcode_admin.js' , array( 'jquery' ), '1.1' );
			wp_enqueue_style( 'otw_shortocde_admin', $this->component_url.'css/otw_shortcode_admin.css', array( ), '1.1' );
			
			add_action( 'admin_footer', array( &$this, 'load_admin_js' ) );
			
			add_action( 'wp_ajax_otw_shortcode_editor_dialog', array( &$this, 'build_shortcode_editor_dialog' ) );
			add_action( 'wp_ajax_otw_shortcode_get_code', array( &$this, 'get_code' ) );
			add_action( 'wp_ajax_otw_shortcode_live_preview', array( &$this, 'live_preview' ) );
			add_action( 'wp_ajax_otw_shortcode_preview_shortcodes', array( &$this, 'preview_shortcodes' ) );
		}else{
			wp_enqueue_style( 'general_foundicons', $this->component_url.'css/general_foundicons.css', array( ), '1.1' );
			wp_enqueue_style( 'social_foundicons', $this->component_url.'css/social_foundicons.css', array( ), '1.1' );
			wp_enqueue_style( 'otw_shortocde', $this->component_url.'css/otw_shortcode.css', array( ), '1.1' );
		}
	}
	
	/**
	 *  Add admin js
	 *
	 */
	public function load_admin_js(){
	
		$js  = "<script type=\"text/javascript\">";
		$js .= "otw_shortcode_component = new otw_shortcode_object();";
		$js .= "otw_shortcode_component.shortcodes = ".json_encode( $this->shortcodes ).";";
		$js .= "otw_shortcode_component.labels = ".json_encode( $this->labels ).";";
		$js .= "</script>";
		
		echo $js;
	}
	
	/**
	 * Short code editor dialog interface
	 */
	public function build_shortcode_editor_dialog(){
		
		$shortcode = '';
		if( isset( $_GET['shortcode'] ) && array_key_exists( $_GET['shortcode'], $this->shortcodes ) ){
			
			$shortcode = $this->shortcodes[ $_GET['shortcode'] ];
			
			$content  = "\n<div style=\"min-height:100%; position:relative; \">";
			$content .= "\n<div class=\"otw-clear\" id=\"otw-shortcode-editor-buttons\">
					<div class=\"alignleft\">
						<input type=\"button\" accesskey=\"C\" value=\"".$this->get_label('Cancel')."\" name=\"cancel\" class=\"button\" id=\"otw-shortcode-btn-cancel\">
					</div>
					<div class=\"alignright\">
						<input type=\"button\" accesskey=\"I\" value=\"".$this->get_label('Insert')."\" name=\"insert\" class=\"button-primary\" id=\"otw-shortcode-btn-insert\">
					</div>
					<div class=\"otw-clear\"></div>
					</div>";
			$content .= "<table cellspacing=\"2\" cellpadding=\"0\" class=\"otw-shortcode-editor-body\">";
			$content .= "<tr>";
			$content .= "<th>".$this->get_label('Options')."</th>";
			$content .= "<th class=\"otw_empty_head\">&nbsp;</th>";
			
			if( $this->shortcodes[ $_GET['shortcode'] ]['object']->has_custom_options ){
				$content .= "<td  valign=\"top\" rowspan=\"4\">";
			}else{
				$content .= "<td  valign=\"top\" rowspan=\"2\">";
			}
			
			if( $this->shortcodes[ $_GET['shortcode'] ]['object']->has_preview ){
			
				$content .= "<div class=\"otw-shortcode-editor-preview-container\">
								
								<div class=\"otw-shortcode-editor-preview-wrapper\">
								<h3>".$this->get_label('Preview')."</h3>
								<div class=\"otw-shortcode-editor-preview\">
								</div>
								</div>
						</div>";
			}else{
				$content .= "&nbsp;";
			}
			$content .= "\n</td>";
			$content .= "\n</tr>";
			$content .= "<tr>";
			$content .= "<td class=\"otw-shortcode-editor-fields\" valign=\"top\">";
			$content .= $this->shortcodes[ $_GET['shortcode'] ]['object']->build_shortcode_editor_options();
			$content .= "</td>";
			$content .= "<td>&nbsp;</td>";
			$content .= "</tr>";
			
			if( $this->shortcodes[ $_GET['shortcode'] ]['object']->has_custom_options ){
				$content .= "<tr>";
					$content .= "<th>".$this->get_label('Custom Options')."</th>";
					$content .= "<th class=\"otw_empty_head\">&nbsp;</th>";
				$content .= "</tr>";
				$content .= "<tr>";
					$content .= "<td class=\"otw-shortcode-editor-fields\" valign=\"top\">";
					$content .= $this->shortcodes[ $_GET['shortcode'] ]['object']->build_shortcode_editor_custom_options();
					$content .= "</td>";
					$content .= "<td>&nbsp;</td>";
				$content .= "</tr>";
			}
			$content .= "</table>";
			
			$content .= "\n<div class=\"otw-clear\" id=\"otw-shortcode-editor-buttons-bottom\">
						<div class=\"alignleft\">
							<input type=\"button\" accesskey=\"C\" value=\"".$this->get_label('Cancel')."\" name=\"cancel\" class=\"button\" id=\"otw-shortcode-btn-cancel-bottom\">
						</div>
						<div class=\"alignright\">
							<input type=\"button\" accesskey=\"I\" value=\"".$this->get_label('Insert')."\" name=\"insert\" class=\"button-primary\" id=\"otw-shortcode-btn-insert-bottom\">
						</div>
						<div class=\"otw-clear\"></div>
					</div>";
			$content .= "\n</div>";
			echo $content;
			die;
		}else{
			wp_die( $this->get_label('Invalid shortcode') );
		}
		
	}
	
	/** Shortcodes preview
	 *
	 */
	public function preview_shortcodes(){
	
		$result = array();
		if( isset( $_POST['shortcode'] ) )
		{
			$result['shortcodes'] = $_POST['shortcode'];
			foreach( $result['shortcodes'] as $shortcode_key => $shortcode )
			{
				$result['shortcodes'][ $shortcode_key ]['preview'] = '';
				
				$result['shortcodes'][ $shortcode_key ]['preview'] .= '<link rel="stylesheet" type="text/css" href="'.( esc_url(  $this->component_url.'css/general_foundicons.css' ) ).'" />';
				$result['shortcodes'][ $shortcode_key ]['preview'] .= '<link rel="stylesheet" type="text/css" href="'.( esc_url(  $this->component_url.'css/social_foundicons.css' ) ).'" />';
				$result['shortcodes'][ $shortcode_key ]['preview'] .= '<link rel="stylesheet" type="text/css" href="'.( esc_url(  $this->component_url . 'css/otw_shortcode.css' ) ).'" />';
				
				$result['shortcodes'][ $shortcode_key ]['preview'] .= '<div style="text-align: center;">';
				
				$result['shortcodes'][ $shortcode_key ]['preview'] .= do_shortcode( stripslashes( $shortcode['code'] ) );
				$result['shortcodes'][ $shortcode_key ]['preview'] .= '</div>';
			}
		}
		
		echo json_encode( $result );
		die;
	}
	/** Shortcode live preview
	 *
	 */
	public function live_preview(){
		
		global $post;
		
		if( !$post && isset( $_POST['post'] ) ){
			$post = get_post( $_POST['post'] );
		}
		
		if( isset( $_POST['shortcode'] ) ){
			
			echo '<link rel="stylesheet" type="text/css" href="'.( esc_url(  $this->component_url.'css/general_foundicons.css' ) ).'" />';
			echo '<link rel="stylesheet" type="text/css" href="'.( esc_url(  $this->component_url.'css/social_foundicons.css' ) ).'" />';
			echo '<link rel="stylesheet" type="text/css" href="'.( esc_url( $this->component_url . 'css/otw_shortcode.css' ) ).'" />';
			echo '<div style="text-align: center;">';
			$attributes = $_POST['shortcode'];
			
			if( isset( $attributes['shortcode_type'] ) && array_key_exists( $attributes['shortcode_type'], $this->shortcodes ) ){
				
				if( $shortcode = $this->shortcodes[ $attributes['shortcode_type'] ]['object']->build_shortcode_code( $attributes ) ){
					echo do_shortcode( stripslashes( $shortcode ) );
				}
			}
			echo '</div>';
		}
		die;
	}
	
	/** Get shortcode by given params from editor interace
	 *
	 */
	public function get_code(){
		
		$response = array();
		$response['code'] = '';
		
		$attributes = $_POST;
		
		if( isset( $attributes['shortcode_type'] ) && array_key_exists( $attributes['shortcode_type'], $this->shortcodes ) ){
			
			if( $shortcode = $this->shortcodes[  $attributes['shortcode_type'] ]['object']->build_shortcode_code( $attributes ) ){
				$response['code'] = $shortcode;
			}
			if( $shortcode_attributes = $this->shortcodes[  $attributes['shortcode_type'] ]['object']->get_shortcode_attributes( $attributes ) ){
				$response['shortcode_attributes'] = $shortcode_attributes;
			}
			if( $this->shortcodes[  $attributes['shortcode_type'] ]['object']->has_error ){
				foreach( $this->shortcodes[  $attributes['shortcode_type'] ]['object']->errors as $error ){
					$this->add_error( $error );
				}
			}
		}else{
			$this->add_error( $this->get_label( 'Invalid shortcode' ) );
		}
		
		$response['has_error'] = $this->has_error;
		$response['errors'] = $this->errors;
		
		echo json_encode( $response );
		die;
	}
	
	/** Sort shortcodes basedn on order field
	 *
	 */
	public function sort_shortcodes( $a, $b ){
		if( $a['order'] > $b['order'] ){
			return 1;
		}
		elseif( $a['order'] < $b['order'] ){
			return -1;
		}
		
		return 0;
	}
}
?>