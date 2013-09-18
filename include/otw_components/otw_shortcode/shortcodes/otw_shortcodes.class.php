<?php
class OTW_Shortcodes{

	/**
	 * array with labels
	 */
	public $labels = array();
	
	/**
	 * array with settings
	 */
	public $settings = array();
	
	/**
	 * mode
	 */
	public $mode = '';
	
	/**
	 * has custom options
	 */
	public $has_custom_options = false;
	
	/**
	 * has preview
	 */
	public $has_preview = true;
	
	/**
	 * Errors
	 * 
	 * @var  array
	 */
	public $errors = array();
	
	/**
	 * has errors
	 * 
	 * @var  boolen
	 */
	public $has_error = false;

	/**
	 * component url
	 * 
	 * @var  string
	 */
	public $component_url = '';
	
	/**
	 * component path
	 * 
	 * @var  string
	 */
	public $component_path = '';
	
	/**
	 *  Get Label
	 */
	public function get_label( $label_key ){
		
		if( isset( $this->labels[ $label_key ] ) ){
		
			return $this->labels[ $label_key ];
		}
		
		if( $this->mode == 'dev' ){
			return strtoupper( $label_key );
		}
		
		return $label_key;
	}
	
	/**
	 * Build shortcode editor
	 */
	public function build_shortcode_editor_options(){
		
		return $this->get_label( 'Invalid shortcode' );
		
	}
	
	/**
	 * apply predefined settings
	 */
	public function apply_settings(){
		
	}
	
	/**
	 * Build shortcode editor
	 */
	public function build_shortcode_editor_custom_options(){
		
		return $this->get_label( 'Invalid shortcode' );
		
	}
	
	/**
	 * Build shortcode
	 */
	public function build_shortcode_code( $attributes ){
		
		echo $this->get_label( 'Invalid shortcode' );
		
	}
	
	
	/**
	 * Display shortcode
	 */
	public function display_shortcode( $attributes, $content ){
		return $this->get_label( 'Invalid shortcode' );
	}
	
	/**
	 *  format attribute
	 *  
	 *  @param string name
	 *
	 *  @param string key
	 *
	 *  @param array with attributes
	 *
	 *  @param boolean create attribute if no value
	 *
	 *  @return string
	 */
	public static function format_attribute( $attribute_name, $attribute_key, $attributes, $show_empty = false, $add_space = '' ){
	
		if( isset( $attributes[ $attribute_key ] ) && strlen( trim( $attributes[ $attribute_key ] ) ) ){
			if( $attribute_name ){
				return ' '.$attribute_name.'="'.$attributes[ $attribute_key ].'"';
			}else{
				if( strlen( $add_space ) ){
					return ' '.$attributes[ $attribute_key ];
				}
				return $attributes[ $attribute_key ];
			}
		}elseif( $show_empty ){
			if( $attribute_name ){
				return ' '.$attribute_name.'=""';
			}
		}
		return '';
	}
	
	/** append attribute to existing list with attributes
	 *
	 *  @param string
	 *  @param string
	 *  @return string
	 */
	public function append_attribute( $append_to, $attribute ){
		
		$result = $append_to;
		
		if( strlen( $result ) ){
			$result .= ' '.$attribute;
		}else{
			$result .= $attribute;
		}
		return $result;
	}
	
	/**
	 *  add error
	 */
	public function add_error( $error_string ){
		
		$this->errors[] = $error_string;
		$this->has_error = true;
	}
	
	/**
	 * Return shortcode attributes
	 */
	public function get_shortcode_attributes( $attributes ){
		return array();
	}
}