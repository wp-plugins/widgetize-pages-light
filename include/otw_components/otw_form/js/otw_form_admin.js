jQuery(document).ready(function($) {
	
	otw_form_init_fields();
});

otw_form_init_fields = function(){
	
	jQuery( '.otw-form-select' ).change( function(){
		jQuery( this ).parent().find( 'span' ).html( this.options[ this.selectedIndex ].text );
	} );
	
	var startingColour = '000000';
	jQuery( '.otw-color-selector' ).each( function(){ 
		
		var colourPicker = jQuery(this).ColorPicker({
		
		color: startingColour,
			onShow: function (colpkr) {
				jQuery(colpkr).fadeIn(500);
				return false;
			},
			onHide: function (colpkr) {
				jQuery(colpkr).fadeOut(500);
				jQuery(colourPicker).next( 'input').change();
				return false;
			},
			onChange: function (hsb, hex, rgb) {
				jQuery(colourPicker).children( 'div').css( 'backgroundColor', '#' + hex);
				jQuery(colourPicker).next( 'input').attr( 'value','#' + hex);
				
			}
		
		});
	});
	jQuery( '.otw-form-color-picker' ).change( function(){
		jQuery( this ).parent( 'div' ).children( 'div' ).children( 'div' ).css( 'backgroundColor', this.value );
	});
};