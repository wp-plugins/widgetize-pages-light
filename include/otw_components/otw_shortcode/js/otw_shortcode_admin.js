function otw_shortcode_object(){
	
	this.shortcodes = {};
	
	this.labels = {};
	
	this.dropdown_menu = false;
	
}
otw_shortcode_object.prototype.open_drowpdown_menu = function( append_to ){
	
	this.dropdown_menu = jQuery( '#otw_shortcode_dropdown_menu' );
	
	if( !this.dropdown_menu.size() ){
	
		var links = '<div id=\"otw_shortcode_dropdown_menu\">';
		
		links = links + '<ul>';
		
		for( var shortcode in this.shortcodes ){
			
			if( this.shortcodes[ shortcode ].enabled ){
				
				if( this.shortcodes[ shortcode ].children ){
				
				}else{
					links = links + '<li><a class="otw-shortcode-dropdown-action-' + shortcode + '">' + this.shortcodes[ shortcode ].title + '</a></li>';
				}
			};
		};
		links = links + '<li class="otw-dropdown-line"><a class="otw-shortcode-dropdown-action-close">' + this.get_label( 'Close' ) + '</a></li>';
		
		links = links + '</ul>';
		
		links = links + '</div>';
		
		this.dropdown_menu = jQuery( links );
		
		this.init_dropdown_actions();
		
		with( this ){
			jQuery( document ).click( function(){
				if( dropdown_menu.css( 'display' ) == 'block' ){
					dropdown_menu.hide( );
				};
			});
		};
		this.dropdown_menu.appendTo( jQuery( 'body' ) );
	}
	else
	{
		this.dropdown_menu.hide();
	}
	var link = jQuery( append_to );
	
	var link_height = link.outerHeight();
	
	this.dropdown_menu.css("top", link.offset().top + link_height );
	
	var dropdown_right_postion = link.offset().left + this.dropdown_menu.width();
	
	if( ( dropdown_right_postion ) > jQuery(document).width() ){
		this.dropdown_menu.css("left", link.offset().left - this.dropdown_menu.width() + link.width() );
	}else{
		this.dropdown_menu.css("left", link.offset().left );
	};
	
	this.dropdown_menu.slideDown(100);
	this.dropdown_menu.show();
	
};

otw_shortcode_object.prototype.insert_code = function( shortcode_object ){
	
};

otw_shortcode_object.prototype.init_dropdown_actions = function(){
	
	with( this ){
		dropdown_menu.find( 'a' ).click( function(){
			
			var class_name = jQuery( this ).attr( 'class' );
			
			if( class_name ){
				
				var matches = false;
				if( matches = jQuery( this ).attr( 'class' ).match( /^otw\-shortcode\-dropdown\-action\-([a-z_]+)$/ ) ){
					
					switch( matches[1] ){
						
						case 'close':
								dropdown_menu.hide();
							break;
						default:
								jQuery.get( 'admin-ajax.php?action=otw_shortcode_editor_dialog&shortcode=' + matches[1] ,function(b){
								
									jQuery( "#otw-dialog").remove();
									var cont = jQuery( '<div id="otw-dialog">' + b + '</div>' );
									jQuery( "body").append( cont );
									jQuery( "#otw-dialog").hide();
									tb_position = function(){
										var isIE6 = typeof document.body.style.maxHeight === "undefined";
										var b=jQuery(window).height();
										jQuery("#TB_window").css({marginLeft: '-' + parseInt((TB_WIDTH / 2),10) + 'px', width: TB_WIDTH + 'px'});
										if ( ! isIE6 ) { // take away IE6
											jQuery("#TB_window").css({marginTop: '-' + parseInt((TB_HEIGHT / 2),10) + 'px'});
										}
										jQuery( '#TB_ajaxContent' ).css( 'width', '950px' );
										jQuery( '#TB_ajaxContent' ).css( 'padding', '0' );
										
									}
									
									var f=jQuery(window).width();
									b=jQuery(window).height();
									f=1000<f?1000:f;
									f-=80;
									/*b-=84;*/
									b=760<b?760:b;
									b-=110; 
									otw_form_init_fields();
									
									otw_shortcode_editor = new otw_shortcode_editor_object( matches[1] );
									otw_shortcode_editor.init_fields();
									otw_shortcode_editor.shortcode_created = function( shortcode_object ){
										insert_code( shortcode_object );
									}
									tb_show( get_label( 'Insert' ) + ' OTW ' + shortcodes[ matches[1] ].title, "#TB_inline?width="+f+"&height="+b+"&inlineId=otw-dialog" );
									
								} );
								dropdown_menu.hide();

							break;
					};
				};
			};
		} );
	};
};

otw_shortcode_object.prototype.get_label = function( label ){

	if( this.labels[ label ] ){
		return this.labels[ label ];
	};
	
	return label;
};
otw_shortcode_editor_object = function( type ){
	
	this.fields = {};
	
	this.shortcode_type = type;
	
	this.code = '';
	
	this.init_action_buttons();
};
otw_shortcode_editor_object.prototype.init_action_buttons = function(){
	
	with( this ){
		
		jQuery( '#otw-shortcode-btn-cancel' ).click( function(){
			tb_remove();
		});
		
		jQuery( '#otw-shortcode-btn-insert' ).click( function(){
			
			get_code();
		} );
		
		jQuery( '#otw-shortcode-btn-cancel-bottom' ).click( function(){
			tb_remove();
		});
		
		jQuery( '#otw-shortcode-btn-insert-bottom' ).click( function(){
			
			get_code();
		} );
	};
};

otw_shortcode_editor_object.prototype.live_preview = function(){
	
	if( !jQuery( '.otw-shortcode-editor-preview' ).size() ){
		return ;
	};
	
	var preview_html = '<iframe width="100%" scrolling="auto" id="otw-shortcode-preview"></iframe>';
	jQuery( '.otw-shortcode-editor-preview' ).html( preview_html );
	
	var s_code = this.get_values();
	
	var matches = false;
	var post_id = 0;
	if( matches = location.href.match( /post\=([0-9]+)/ ) ){
		post_id = matches[1];
	}
	
	jQuery.post( 'admin-ajax.php?action=otw_shortcode_live_preview&shortcode=' + this.shortcode_type , { 'shortcode': s_code, 'post': post_id }, function( response ){
		
		jQuery( '#otw-shortcode-editor-buttons' ).show();
		jQuery( '#otw-shortcode-preview' ).contents().find('body').html( '' );
		jQuery( '#otw-shortcode-preview' ).contents().find('body').append(response);
		jQuery( '#otw-shortcode-preview' ).contents().find('body')[0].style.border=  'none';
		jQuery( '#otw-shortcode-preview' ).contents().find('body')[0].style.background =  'none';
		jQuery( '#otw-shortcode-preview' ).contents().find('a,input').click( function( event ){
			event.stopPropagation();
			return false;
		});
		
		jQuery( '.otw-shortcode-editor-preview' ).fadeIn();
		
		jQuery( '#TB_ajaxContent' ).scroll( function(){
			jQuery( '#otw-shortcode-preview' ).parents( '.otw-shortcode-editor-preview-container' ).css( 'padding-top', this.scrollTop + 'px');
		});
	});
};

otw_shortcode_editor_object.prototype.shortcode_error = function( errors ){
	
	var error_html = '<div class=\"otw-shortcode-editor-error\" >';
	
	for( var cE = 0; cE < errors.length; cE++){
	
		error_html = error_html + '<p>' + errors[ cE ]  + '</p>';
	}
	
	error_html = error_html + '</div>';
	
	jQuery( '.otw-shortcode-editor-preview' ).html( error_html );
}

otw_shortcode_editor_object.prototype.get_values = function(){

	v_code = {};
	v_code.shortcode_code = '';
	v_code.shortcode_type = this.shortcode_type;
	
	for( var field in otw_shortcode_editor.fields ){
	
		var matches = false;
		if( matches = field.match( /^otw\-shortcode\-element\-([a-z0-9\_]+)$/ ) ){
			v_code[ matches[1] ] = otw_shortcode_editor.fields[ field ].current_value;
		};
	};
	
	return v_code;
};

otw_shortcode_editor_object.prototype.get_code = function(){
	
	this.code = this.get_values();
	
	with( this ){
		//here make request to get the code validated
		jQuery.post( 'admin-ajax.php?action=otw_shortcode_get_code&shortcode=' + this.shortcode_type , this.code, function( response ){
			
			var response_code = jQuery.parseJSON( response );
			
			if( !response_code.has_error ){
				code.shortcode_code = response_code.code;
				
				if( typeof( response_code.shortcode_attributes ) != 'undefined' ){
				
					for( var sA in response_code.shortcode_attributes ){
					
						code[ sA ] = response_code.shortcode_attributes[ sA ];
					}
				}
				shortcode_created( code );
			}else{
				shortcode_error( response_code.errors );
			};
		});
	};
};

otw_shortcode_editor_object.prototype.init_fields = function(){
	
	//collect inputs
	with( this ){
		jQuery( '.otw-shortcode-editor-fields' ).find( 'input[type=text]' ).each( function(){
		
			var element = jQuery( this );
			
			if( element.attr( 'id' ) ){
				fields[ element.attr( 'id' ) ] = new otw_shortcode_editor_element( 'text_input', element );
			}
			element.change( function(){
				live_preview();
			});
		} );
		jQuery( '.otw-shortcode-editor-fields' ).find( 'select' ).each( function(){
		
			var element = jQuery( this );
			
			if( element.attr( 'id' ) ){
				fields[ element.attr( 'id' ) ] = new otw_shortcode_editor_element( 'select', element );
			}
			element.change( function(){
				live_preview();
			});
		} );
	};
	this.live_preview();
};

otw_shortcode_editor_element = function( element_type, element ){
	
	this.element_type = element_type;
	
	this.element = element;
	
	this.initial_value = this.element.val();
	
	this.current_value = this.initial_value;
	
	this.is_changed = false;
	
	with( this ){
		element.change( function(){
			
			current_value = element.val();
			
			if( current_value != initial_value ){
				is_changed = true;
			}else{
				is_changed = false;
			}
		} );
	};
};

otw_shortcode_component = null;
otw_shortcode_editor = null;