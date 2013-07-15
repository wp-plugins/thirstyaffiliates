var frame;

jQuery(document).ready(function() {
	if (thirstyJSEnable == true) {
		jQuery('#thirstyOptionsLinkPrefix').change(function() {
				
			thirstySetRebuildFlag();
			
			if (jQuery(this).val() == 'custom') {
				jQuery('#thirstyCustomLinkPrefix').val("");
				jQuery('#thirstyCustomLinkPrefix').fadeIn(400);
			} else {
				jQuery('#thirstyCustomLinkPrefix').val("");
				jQuery('#thirstyCustomLinkPrefix').fadeOut(400);
			}
		});
		
		jQuery('.submit input[type=submit]').click(function() {
			if (jQuery('#thirstyOptionsLinkPrefix').val() == 'custom' && 
				jQuery('#thirstyCustomLinkPrefix').val() == '') {
				jQuery('#thirstyOptionsLinkPrefix').val('recommends');
				jQuery('#thirstyCustomLinkPrefix').fadeOut(400);
			}
		});
		jQuery('#post_name').remove();
		jQuery('#thirstyEditSlug').click(thirstyEditSlug);
		
		jQuery('#thirsty_upload_insert_img').click(thirstyAddImagesClicked);
		jQuery('#thirsty_upload_media_manager').click(thirstyMediaManagerAddImagesClicked);
		jQuery('.thirstyRemoveImg').click(thirstyRemoveImageClicked);
		
		jQuery('#thirstyOptionsShowCatInSlug').click(thirstySetRebuildFlag);
		jQuery('#thirstyForceLinkRebuild').click(thirstySetRebuildFlag);
	}
});

function thirstySetRebuildFlag() {
	jQuery('#thirstyHiddenRebuildFlag').val('true');
}

function thirstyRemoveImageClicked(event) {
	var attachId = jQuery(this).attr('id'); // get attachment id
	jQuery.post(
		thirstyAjaxLink,
		{
			action: 'thirstyUnattachImageFromLink',
			imgId: attachId
		},
		function(result) {
			jQuery('.thirstyImgHolder span#' + result).addClass('test').parent().fadeOut(300).delay(300).remove();
		}
	);
}

function thirstyMediaManagerAddImagesClicked(event) {
	jQuery('.thirstySaveMe').fadeIn(200);

	event.preventDefault();

	// If the media frame already exists, reopen it.
	if ( frame ) {
		frame.open();
		return;
	}

	// Create the media frame.
	frame = wp.media.frames.thirstyLink = wp.media({
		// Set the title of the modal.
		title: jQuery(this).data('uploader-title'),

		// Tell the modal to show only images.
		library: {
			type: 'image'
		},

		// Customize the submit button.
		button: {
			// Set the text of the button.
			text: jQuery(this).data('uploader-button-text'),
			// Tell the button not to close the modal, since we're
			// going to refresh the page when the image is selected.
			close: true
		}
	});

	// When an image is selected, run a callback.
	frame.on( 'select', function() {
		// Grab the selected attachment.
		var attachment = frame.state().get('selection').first();
		
		// Attach this image to the affiliate link
		attachment = attachment.toJSON();
		
		var post_id = jQuery('input[name=post_ID]').val();
		
		jQuery.post(
			thirstyAjaxLink,
			{
				action: 'thirstyAttachImageToLink',
				imgId: attachment.id,
				imgName: attachment.name,
				imgMime: attachment.mime,
				postId: post_id
			},
			function(result) {
				tb_remove();
				jQuery('#publish').trigger('click');
			}
		);
	});

	frame.open();
}

function thirstyAddImagesClicked() {
	// This is for the legacy thickbox uploader for installs pre-WP3.5
	jQuery('.thirstySaveMe').fadeIn(200);
	var post_id = jQuery('input[name=post_ID]').val();
	
	window.send_to_editor = function(html) {
		if (thirstyJSEnable == true) {
			if (html.match(/^<img/)) {
				// was uploaded from url, need to upload this file to our install
				var imgUrl = jQuery(html).attr('src');
				var post_id = jQuery('input[name=post_ID]').val();
				
				jQuery.post(
					thirstyAjaxLink,
					{
						action: 'thirstyUploadImageFromUrl',
						imgUrl: imgUrl,
						postId: post_id
					},
					function(result) {
						alert(result);
						tb_remove();
						jQuery('#publish').trigger('click');
					}
				);
			} else {
				tb_remove();
				jQuery('#publish').trigger('click');
			}
		}
	}
	
	tb_show(
		'Select/Upload Images To Affiliate Link',
		'media-upload.php?post_id=' + post_id + 'type=image&tab=library&TB_iframe=true'
	);
	
    return false;
}

function thirstyEditSlug() {
	jQuery('#thirsty_cloakedurl').hide();
	jQuery('#thirstyEditSlug').hide();
	jQuery('#thirstyVisitLink').hide();
	jQuery('#thirsty_linkslug').fadeIn(200);
	jQuery('#thirstySaveSlug').fadeIn(200);
	
	// Unbind everything
	jQuery('#thirstyEditSlug').unbind()
	jQuery('#thirstySaveSlug').unbind();
	
	// Rebind save button
	jQuery('#thirstySaveSlug').click(thirstyHideEditSlug);
	jQuery('#thirsty_linkslug').keypress(function(e){
		if (e.which == 13){
			jQuery('#thirstySaveSlug').trigger('click');
			return false;
		}
	});
}

function thirstyHideEditSlug() {
	
	jQuery('#thirsty_linkslug').hide();
	jQuery('#thirstySaveSlug').hide();
	jQuery('#thirsty_cloakedurl').fadeIn(200);
	jQuery('#thirstyEditSlug').fadeIn(200);
	jQuery('#thirstyVisitLink').fadeIn(200);
	
	var oldLink = jQuery('#thirsty_cloakedurl').val();
	var linkBase = oldLink.replace(/[^\/]+\/?$/g,'');
	var newLink = jQuery('#thirsty_linkslug').val();
	newLink = (newLink == '' ? oldLink.match(/[^\/]+$/) : newLink);
	jQuery('#thirsty_linkslug').val(newLink)
	jQuery('#thirsty_cloakedurl').val(linkBase + newLink);
	
	// Unbind everything
	jQuery('#thirstyEditSlug').unbind();
	jQuery('#thirstySaveSlug').unbind();
	
	jQuery('#thirstyEditSlug').click(thirstyEditSlug);
}
