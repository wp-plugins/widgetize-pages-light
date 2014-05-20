(function(){
	tinymce.PluginManager.requireLangPack('otwsbm');
	
	tinymce.create('tinymce.plugins.OTWSBMPlugin', {
	
		init : function(ed, url) {
			
			ed.addCommand('otwShortCode', function() {
			
				
				jQuery.get( 'admin-ajax.php?action=otw_shortcode_editor_dialog&shortcode=sidebar' ,function(b){
								
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
					
					otw_shortcode_editor = new otw_shortcode_editor_object( 'sidebar' );
					otw_shortcode_editor.init_fields();
					otw_shortcode_editor.shortcode_created = function( shortcode_object ){
						insertShortCode( shortcode_object.sidebar_id );
					}
					tb_show( 'Insert sidebar', "#TB_inline?width="+f+"&height="+b+"&inlineId=otw-dialog" );
					
				} );
				
			});
			
			// Register example button
			ed.addButton('otwsbm', {
				
				title : 'Insert Sidebar ShortCode',
				cmd : 'otwShortCode',
				image : url + '/../images/otw-sbm-icon.png'
			});
			
			ed.onNodeChange.add(function(ed, cm, n) {
				cm.setActive('otwsbm', n.nodeName == 'IMG');
			});
		},
		createControl : function(n, cm) {
			return null;
		},
		getInfo : function() {
			return { 
				longname : 'Widgetize pages Light plugin',
				author : 'OTWthemes.com',
				authorurl : 'http://themeforest.net/user/OTWthemes',
				infourl : 'http://OTWthemes.com',
				version : "3.0"
			}
		}
	});
	
	// Register plugin
	tinymce.PluginManager.add('otwsbm', tinymce.plugins.OTWSBMPlugin);
	
})();

function insertShortCode( sidebar_id ){
	
	tinyMCE.activeEditor.execCommand( "mceInsertContent", false, '[otw_is sidebar=' + sidebar_id + ']' );
	tb_remove();
}

//http://www.tinymce.com/wiki.php/Creating_a_plugin