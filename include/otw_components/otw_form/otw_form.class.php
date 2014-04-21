<?php
class OTW_Form extends OTW_Component{
	
	
	/**
	 *  Init 
	 */
	public function init(){
		
		if( is_admin() ){
			wp_enqueue_script('otw_form_colorpicker_admin', $this->component_url.'js/colorpicker.js' , array( 'jquery' ), '1.1' );
			wp_enqueue_script('otw_form_admin', $this->component_url.'js/otw_form_admin.js' , array( 'jquery' ), '1.1' );
			
			wp_enqueue_style( 'otw_form_colorpicker_admin', $this->component_url.'css/colorpicker.css', array( ), '1.1' );
			wp_enqueue_style( 'otw_form_admin', $this->component_url.'css/otw_form_admin.css', array( ), '1.1' );
		}
	}

	/** select
	 *
	 *  @param array
	 *
	 *  @return string
	 */
	public static function select( $attributes = array() ){
		
		$html = '';
		
		$attributes = self::parse_attributes( $attributes, 'select' );
		
		if( isset( $attributes['parse'][ $attributes['id'] ] ) ){
			$attributes['value'] = $attributes['parse'][ $attributes['id'] ];
		}
		
		switch( $attributes['format'] ){
		
			default:
					$html .= "<div class=\"otw-form-control\">";
					if( $attributes['label'] ){
						$html .= "<label".self::format_attribute( 'for', 'id', $attributes ).">".$attributes['label']."</label>";
					}
					
					$html .= "<div class=\"otw-select-wrapper\">";
					$html .= "<span>";
					if( isset( $attributes['options'][ $attributes['value'] ] ) ){
						$html .= $attributes['options'][ $attributes['value'] ];
					}else{
						foreach( $attributes['options'] as $key => $value ){
							$html .= $value;
							break;
						}
					}
					$html .= "</span>";
					$html .= "<select ".self::format_attributes( array('id','name','class','style'), array(), $attributes )." ".$attributes['extra'].">";
					
					foreach( $attributes['options'] as $key => $value ){
						$selected = "";
						
						if( strnatcasecmp( $key, $attributes['value'] ) === 0 ){
							$selected = " selected=\"selected\"";
						}
						$html .= "<option value=\"".$key."\"".$selected.">".$value."</option>";
					}
					$html .= "</select>";
					$html .= "</div>";
					
					if( $attributes['description'] ){
							$html .= "<span class=\"otw-form-hint\">".$attributes['description']."</span>";
					}
					$html .= "</div>";
				break;
		}
		
		return $html;
	}
	
	/** input type text
	 *
	 *  @param array
	 *
	 *  @return string
	 */
	public static function text_input( $attributes = array() ){
		
		$html = '';
		
		$attributes = self::parse_attributes( $attributes, 'text-input' );
		
		if( isset( $attributes['parse'][ $attributes['id'] ] ) ){
			$attributes['value'] = $attributes['parse'][ $attributes['id'] ];
		}
		
		switch( $attributes['format'] ){
		
			default:
					$html .= "<div class=\"otw-form-control\">";
					if( $attributes['label'] ){
						$html .= "<label".self::format_attribute( 'for', 'id', $attributes ).">".$attributes['label']."</label>";
					}
					$html .= "<input type=\"text\"".self::format_attributes( array('id','name','value','class','style'), array(), $attributes )." ".$attributes['extra'].">";
					
					if( $attributes['description'] ){
							$html .= "<span class=\"otw-form-hint\">".$attributes['description']."</span>";
					}
					$html .= "</div>";
				break;
		}
		
		return $html;
	}
	
	/** color picker
	 *
	 *  @param array
	 *
	 *  @return string
	 */
	public static function color_picker( $attributes = array() ){
		
		$html = '';
		
		$attributes = self::parse_attributes( $attributes, 'color-picker' );
		
		if( isset( $attributes['parse'][ $attributes['id'] ] ) ){
			$attributes['value'] = $attributes['parse'][ $attributes['id'] ];
		}
		
		if( !isset( $attributes['maxlength'] ) ){
			$attributes['maxlength'] = 7;
		}
		
		if( !strlen( $attributes['value'] ) ){
			$attributes['value'] = '';
		}
		
		switch( $attributes['format'] ){
		
			default:
					$html .= "<div class=\"otw-form-control\">";
					if( $attributes['label'] ){
						$html .= "<label".self::format_attribute( 'for', 'id', $attributes ).">".$attributes['label']."</label>";
					}
					
					$html .= "<div class=\"otw-marker-colourpicker-control\">";
					$html .= "<div class=\"otw-color-selector\">";
					$html .= "<div style=\"background-color: ".$attributes['value']."\"></div>";
					$html .= "</div>";
					$html .= "<input type=\"text\"".self::format_attributes( array('id','name','value','class','style','maxlength'), array(), $attributes )." ".$attributes['extra'].">";
					$html .= "</div>";
					
					if( $attributes['description'] ){
							$html .= "<span class=\"otw-form-hint\">".$attributes['description']."</span>";
					}
					$html .= "</div>";
				break;
		}
		
		return $html;
	}

	
	
	/**
	 * parse attributes
	 *
	 *  @param array
	 *
	 *  @return array
	 */
	public static function parse_attributes( $attributes, $type ){
	
		if( !isset( $attributes['format'] ) ){
			$attributes['format'] = '';
		}
		if( !isset( $attributes['id'] ) ){
			$attributes['id'] = '';
		}
		if( !isset( $attributes['name'] ) ){
			$attributes['name'] = '';
		}
		if( !isset( $attributes['class'] ) ){
			switch( $type ){
				case 'text-input':
				case 'select':
				case 'color-picker':
						$attributes['class'] = 'otw-form-'.$type;
					break;
				default:
						$attributes['class'] = '';
					break;
			}
		}
		if( !isset( $attributes['style'] ) ){
			$attributes['style'] = '';
		}
		if( !isset( $attributes['extra'] ) ){
			$attributes['extra'] = '';
		}
		if( !isset( $attributes['label'] ) ){
			$attributes['label'] = '';
		}
		if( !isset( $attributes['value'] ) ){
			$attributes['value'] = '';
		}
		if( !isset( $attributes['options'] ) || !is_array( $attributes['options'] ) ){
			$attributes['options'] = array();
		}
		if( !isset( $attributes['description'] ) ){
			$attributes['description'] = '';
		}
		if( !isset( $attributes['parse'] ) ){
			$attributes['parse'] = '';
		}
		
		return $attributes;
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
	public static function format_attribute( $attribute_name, $attribute_key, $attributes, $show_empty = false ){
	
		if( isset( $attributes[ $attribute_key ] ) && strlen( trim( $attributes[ $attribute_key ] ) ) ){
			return ' '.$attribute_name.'="'.$attributes[ $attribute_key ].'"';
		}elseif( $show_empty ){
			return ' '.$attribute_name.'=""';
		}
	}
	
	/**
	 *  format attributes
	 *  
	 *  @param string array
	 *
	 *  @param string array
	 *
	 *  @param array with attributes
	 *
	 *  @param array create attribute if no value
	 *
	 *  @return string
	 */
	public static function format_attributes( $attribute_names, $attribute_keys, $attributes, $show_empty = array() ){
	    
		$html = '';
		foreach( $attribute_names as $a_key => $name ){
		
			$key = $name;
			if( isset( $attribute_keys[ $a_key ] ) ){
				$key = $attribute_keys[ $a_key ];
			}
			$empty = false;
			if( isset( $show_empty[ $a_key ] ) ){
				$empty =$show_empty[ $a_key ];
			}
			$html .= self::format_attribute( $name, $key, $attributes, $empty );
		}
		return $html;
	}
}
