/* global tinyMCE */
(function($){
	var media = wp.media, shortcode_string = 'boutique_banner';
	wp.mce = wp.mce || {};
	wp.mce.boutique_banner = {
		shortcode_data: {},
		template: media.template( 'editor-boutique-banner' ),
		getContent: function() {
			var options = this.shortcode.attrs.named;
			// options.innercontent = this.shortcode.content;
			options.innercontent = $('#characterize').val();
			// alert(this.shortcode.content);
			return this.template(options);
		},
		// View: { // before WP 4.2:
		// 	template: media.template( 'editor-boutique-banner' ),
		// 	postID: $('#post_ID').val(),
		// 	initialize: function( options ) {
		// 		this.shortcode = options.shortcode;
		// 		wp.mce.boutique_banner.shortcode_data = this.shortcode;
		// 	},
		// 	getHtml: function() {
		// 		var options = this.shortcode.attrs.named;
		// 		options.innercontent = this.shortcode.content;
		// 		return this.template(options);
		// 	}
		// },
		edit: function( data ) {
			var shortcode_data = wp.shortcode.next(shortcode_string, data);
			var values = shortcode_data.shortcode.attrs.named;
			values.innercontent = shortcode_data.shortcode.content;
			wp.mce.boutique_banner.popupwindow(tinyMCE.activeEditor, values);
		},
		// this is called from our tinymce plugin, also can call from our "edit" function above
		// wp.mce.boutique_banner.popupwindow(tinyMCE.activeEditor, "bird");
		popupwindow: function(editor, values, onsubmit_callback){
			values = values || [];
			if(typeof onsubmit_callback !== 'function'){
				onsubmit_callback = function( e ) {
					// alert(e.data.innercontent);
					$('#characterize').val(e.data.innercontent);

					// Insert content when the window form is submitted (this also replaces during edit, handy!)
					var args = {
							tag     : shortcode_string,
							type    : e.data.innercontent.length ? 'closed' : 'single',
							content : e.data.innercontent,
							attrs : {
								title    : e.data.title,
								language : e.data.language,
								characters : e.data.characters,
							}
						};
						// alert( wp.shortcode.string( args ) );
					editor.insertContent( wp.shortcode.string( args ) );
				};
			}
			editor.windowManager.open( {
				title: 'Insert Code',
				width: 700,
    			height: 500,

				body: [
					{
						type: 'textbox',
						name: 'title',
						label: 'Title',
						value: values.title
					},
					{
						type: 'textbox',
						name: 'language',
						label: 'Language',
						value: values.language
					},
					{
						type: 'textbox',
						name: 'characters',
						label: 'Characters',
						multiline: true,
						value: values.characters
					},
					{
						type: 'textbox',
						name: 'innercontent',
						label: 'Content',
						multiline: true,
						value: $('#characterize').val()
					}
				],
				onsubmit: onsubmit_callback
			} );
		}
	};
	wp.mce.views.register( shortcode_string, wp.mce.boutique_banner );
}(jQuery));

