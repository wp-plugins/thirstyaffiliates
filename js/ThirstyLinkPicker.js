var search_offset = 0;

function thirstyPerformSearch(searchQueryText) {
	var catsQueryIDs = '';

	if (searchQueryText && searchQueryText.length > 0) {
		jQuery('#show_more').fadeOut(200);
	} else {
		jQuery('#show_more').delay(500).fadeIn(400);
		search_offset = 0;
	}

	jQuery.post(
		thirstyAjaxLink,
		{
			action: 'thirstyLinkPickerSearch',
			search_query: searchQueryText
		},
		replaceSearchResults
	);
}

function showImages() {
	jQuery(this).next('.img_choices').slideDown();
	jQuery(this).unbind();
	jQuery(this).click(hideImages);
	jQuery(this).html('Insert Image &laquo;');
}

function hideImages() {
	jQuery(this).next('.img_choices').slideUp();
	jQuery(this).unbind();
	jQuery(this).click(showImages);
	jQuery(this).html('Insert Image &raquo;');
}

thirstyPerformSearch();

jQuery('input#search_input').keyup(function() {
	thirstyPerformSearch(jQuery(this).val());
});

jQuery(document).ready(function() {
	jQuery('input#search_input').focus();

	jQuery('#show_more').click(function() {
		search_offset = search_offset + 10;
		jQuery('#show_more_loader').show();
		jQuery.post(
			thirstyAjaxLink,
			{
				action: 'thirstyLinkPickerSearch',
				search_offset: search_offset
			},
			appendSearchResults
		);
	});
});

function appendSearchResults(html) { jQuery('#show_more_loader').hide(); printSearchResults(html, false); }
function replaceSearchResults(html) { printSearchResults(html, true); }

function printSearchResults(html, replace) {
	if (replace == true)
		jQuery('#picker_content').html(html);
	else
		jQuery('#picker_content').append(html);

	jQuery('.insert_shortcode_link').unbind();
	jQuery('.insert_link').unbind();

	jQuery('.insert_link').click(function() {

		var linkID = jQuery(this).attr('linkID');
		var copiedText = thirstyGetCopiedText();

		// Check if there are anything selected on the editor
		// If none, use the linkname
		if((copiedText == "") || (jQuery.trim(copiedText) == "")){

			// Select the image control with appropriate linkid
			// Go up to the closest table row
			// Go down to that particular row's span with a class of linkname
			// Get the text
			var linkname	=	jQuery("img[linkid='"+linkID+"']").closest("tr").find(".linkname").text();
			copiedText 		=	linkname;
		}

		// Make ajax call to get the link code
		jQuery.post(
			thirstyAjaxLink,
			{
				action: 'thirstyGetLinkCode',
				linkType: 'link',
				linkID: linkID,
				copiedText: copiedText
			},
			function(linkCode) {
				parent.thirstyInsertLink(linkCode);
				parent.thirstyDismissLinkPicker();
			}
		);

	});

	jQuery('.insert_shortcode_link').click(function() {
		var linkID = jQuery(this).attr('linkID');
		var copiedText = thirstyGetCopiedText();

		// Make ajax call to get the link code
		jQuery.post(
			thirstyAjaxLink,
			{
				action: 'thirstyGetLinkCode',
				linkType: 'shortcode',
				linkID: linkID,
				copiedText: copiedText
			},
			function(linkCode) {
				parent.thirstyInsertLink(linkCode);
				parent.thirstyDismissLinkPicker();
			}
		);
	});

	jQuery('.thirstyImg').click(function() {
		var linkID = jQuery(this).attr('linkID');
		var imageID = jQuery(this).attr('imageID');
		var copiedText = thirstyGetCopiedText();

		// Make ajax call to get the link code
		jQuery.post(
			thirstyAjaxLink,
			{
				action: 'thirstyGetLinkCode',
				linkType: 'image',
				linkID: linkID,
				copiedText: copiedText,
				imageID: imageID
			},
			function(linkCode) {
				parent.thirstyInsertLink(linkCode);
				parent.thirstyDismissLinkPicker();
			}
		);

	});

	jQuery('.insert_img_link').click(showImages);
}

function thirstyGetCopiedText() {
	var copiedText = '';

	var richEditorActive = false;
	if (parent.thirstyMCE != null && !parent.thirstyMCE.isHidden()) {
		richEditorActive = true;
	}
	
	if (!richEditorActive) {
		var selectedText = parent.thirstyGetHTMLEditorSelection();
		copiedText = selectedText.text;
	} else {
		copiedText = parent.thirstyMCE.selection.getContent();
	}

	return copiedText;
}
