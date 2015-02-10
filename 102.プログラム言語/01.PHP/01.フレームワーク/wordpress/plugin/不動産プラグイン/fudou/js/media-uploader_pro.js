/*
	jquery: media-uploader_pro.js Pro
	Version: 2014-03-14
	@nendeb
*/

jQuery(document).ready(function($){
	var custom_uploader;

	if(typeof image_uploaders != "undefined"){
		image_uploaders.forEach(function(obj) {

			$( '#'+ obj ).click(function(e) {

				e.preventDefault();

				//Extend the wp.media object
				custom_uploader = wp.media.frames.file_frame = wp.media({
					title: '画像登録',
					library: { type: 'image' }, 
					button: { text: '画像決定' },
					multiple: false
				});

				//When a file is selected, grab the URL and set it as the text field's value
				custom_uploader.on('select', function() {
					attachment = custom_uploader.state().get('selection').first().toJSON();
					$("input:text[name=" + obj + "]").val(attachment.filename);
            				$('#thumbnail'+ obj).html('<img class="box3image" src="'+ attachment.url +'" style="width: 90px; height: 90px; float: left; margin: 0 5px 0 0; " />');
				});
				//Open the uploader dialog
				custom_uploader.open();
			});

		}); //end forEach
	};
});

