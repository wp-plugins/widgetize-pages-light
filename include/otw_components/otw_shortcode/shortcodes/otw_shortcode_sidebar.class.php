<?php
class OTW_Shortcode_Sidebar extends OTW_Shortcodes{
	
	public function __construct(){
		
		$this->has_custom_options = false;
		
		$this->has_preview = false;
	}
	
	/**
	 * apply settings
	 */
	public function apply_settings(){
		
		global $wp_registered_sidebars;
		
		$this->settings = array();
		
		$this->settings['otw_sidebars_options'] = array( '' => '--/--' );
		
		if( is_array( $wp_registered_sidebars ) && count( $wp_registered_sidebars ) ){
			
			foreach( $wp_registered_sidebars as $sidebar ){
			
				if( preg_match( "/^otw\-sidebar\-/", $sidebar['id'] ) && $sidebar['replace'] == '' ){
					
					$this->settings['otw_sidebars_options'][ $sidebar['id'] ] = $sidebar['name'];
				}
			}
		}
		
		$this->settings['default_otw_sidebar'] = '';
	}
	/**
	 * Shortcode icon_link admin interface
	 */
	public function build_shortcode_editor_options(){
		
		$html = '';
		
		$source = array();
		if( isset( $_POST['shortcode_object'] ) ){
			$source = $_POST['shortcode_object'];
		}
		$html .= '<br />';
		$html .= OTW_Form::select( array( 'id' => 'otw-shortcode-element-sidebar_id', 'label' => $this->get_label( 'Select sidebar' ), 'parse' => $source, 'options' => $this->settings['otw_sidebars_options'], 'value' => $this->settings['default_otw_sidebar'] )  );
		
		return $html;
	}
	
	/** build icon link shortcode
	 *
	 *  @param array
	 *  @return string
	 */
	public function build_shortcode_code( $attributes ){
		
		$code = '';
		
		if( !isset( $attributes['sidebar_id'] ) || !strlen( trim( $attributes['sidebar_id'] ) ) ){
			$this->add_error( $this->get_label( 'Sidebar is required field' ) );
		}
		
		if( !$this->has_error ){
		
			$code = '[otw_shortcode_sidebar';
			
			$code .= $this->format_attribute( 'sidebar_id', 'sidebar_id', $attributes );
			
			$code .= ']';
			
			$code .= '[/otw_shortcode_sidebar]';
		}
		
		return $code;
	}
	
	/**
	 * Process shortcode icon link
	 */
	public function display_shortcode( $attributes, $content ){
		
		if( is_admin() ){
			$html = '<img src="'.$this->component_url.'img/sidebars-icon-placeholder.png'.'" alt=""/>';
		}else{
			$html = do_shortcode( '[otw_is sidebar="'.$attributes['sidebar_id'].'"]' );
		}
		
		return $html;
	}
	
	/**
	 * Return shortcode attributes
	 */
	public function get_shortcode_attributes( $attributes ){
		global $wp_registered_sidebars;
		
		$shortcode_attributes = array();
		
		if( isset( $wp_registered_sidebars[ $attributes['sidebar_id'] ] ) ){
			$shortcode_attributes['iname'] = $wp_registered_sidebars[ $attributes['sidebar_id'] ]['name'];
		}
		return $shortcode_attributes;
	}
}
?>