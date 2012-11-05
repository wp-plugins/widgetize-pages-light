(function(){
	tinymce.PluginManager.requireLangPack('otwsbm');
	
	tinymce.create('tinymce.plugins.OTWSBMPlugin', {
	
		init : function(ed, url) {
			
			ed.addCommand('otwShortCode', function() {
			
				tb_show( jQuery('#content_otwsbm').attr( 'title' ), 'admin-ajax.php?action=otw_wpl_editor_dialog' );
				
			});
			
			// Register example button
			ed.addButton('otwsbm', {
				
				title : 'Insert Sidebar ShortCode',
				cmd : 'otwShortCode',
				image : url + '/../images/application_side_boxes.png'
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
				longname : 'Sidebar & Widget Manager plugin',
				author : 'OTWthemes.com',
				authorurl : 'http://themeforest.net/user/OTWthemes',
				infourl : 'http://OTWthemes.com',
				version : "1.0"
			}
		}
	});
	
	// Register plugin
	tinymce.PluginManager.add('otwsbm', tinymce.plugins.OTWSBMPlugin);
	
})();

function insertShortCode(){
	var select_box = jQuery( '#o_sidebar' )
	
	tinyMCE.activeEditor.execCommand( "mceInsertContent", false, '[otw_is sidebar=' + select_box.val() + ']' );
	tb_remove();
}

//http://www.tinymce.com/wiki.php/Creating_a_plugin