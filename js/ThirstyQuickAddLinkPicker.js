jQuery(document).ready(function($) {

	/*========================================
	=            Global Variables            =
	========================================*/
	var quick_add_link_container	=	$("#quick-add-link-container"),
		allClear					=	true,
		errList						=	{};


	/*=========================================
	=            Utility Functions            =
	=========================================*/

	// Initialize form
	function initializeForm(){

		// Clear all error related stuff
		quick_add_link_container
			.find("input[type='text']")
				.removeClass('err')
			.siblings('.errmsg')
				.css("display","none")
				.text('')
		.closest("#quick-add-link-container")
			.find("#error-bulletin")
				.css("display","none")
				.text('');

		// Re initialize checkpoint flag
		allClear	=	true;

		// Re initialize error list object
		errList		=	{};
	}

	// Validate Link Name
	function validateLinkName(linkname) {
		if(linkname == ""){
			allClear					=	false;
			errList["#qal_link_name"]	=	"Required Field, Can't be empty";
		}
	}

	// Validate Destination URL
	function validateDestinationURL(linkurl){
		if(linkurl == ""){
			allClear						=	false;
			errList["#qal_destination_url"]	=	"Required Field, Can't be empty";
		}else{
			var urlRegex = /(ftp|http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/;
			if(!urlRegex.test(linkurl)){
				allClear						=	false;
				errList["#qal_destination_url"]	=	"Invalid URL Supplied";
			}
		}
	}

	// Prompt Error Message
	function promptErrorMessage(){
		$.each(errList, function( id, errmsg ) {
		  quick_add_link_container
		  	.find(id)
		  		.addClass('err')
	  		.siblings('.errmsg')
	  			.css("display","block")
	  			.text(errmsg);
		});
	}

	
	/*=======================================================================
	=           Add New Affiliate Link and Insert to Post Editor            =
	=======================================================================*/
	$('#quick-add-link').click(function() {

		/*==========  Init  ==========*/
		initializeForm();

		/*==========  Data Sanitation  ==========*/
		// Note: JS Validation should not be relied on
		// 		 Just for user convenience purposes
		var linkname         = $.trim(quick_add_link_container.find("#qal_link_name").val()),
			nonce     	     = quick_add_link_container.find("#quick_add_aff_link_nonce").val(),
			linkurl          = $.trim(quick_add_link_container.find("#qal_destination_url").val()),
			nofollow         = $.trim(quick_add_link_container.find("#qal_no_follow_link:checked").val()),
			newwindow        = $.trim(quick_add_link_container.find("#qal_new_window:checked").val()),
			linkredirecttype = $.trim(quick_add_link_container.find("input[name='qal_redirect_type']:checked").val()),
			linkCategory	 = $.trim(quick_add_link_container.find("#qal_link_categories").val());

		// Link Name
		validateLinkName(linkname);

		// Link URL
		validateDestinationURL(linkurl);
		
		// Checkpoint
		if(allClear){

			/*==========  Ajax Call  ==========*/
			// TODO: Refactor this ajax call to another function
			$.post(
				thirstyAjaxLink,
				{
					action           : 'quickCreateAffiliateLink',
					nonce            : nonce,
					linkname         : linkname,
					linkurl          : linkurl,
					nofollow         : nofollow,
					newwindow        : newwindow,
					linkredirecttype : linkredirecttype,
					linkCategory	 : linkCategory
				},
				function(data){

					if(!isNaN(data)){
						
						// Success
						var linkID	   = data;
						var copiedText = thirstyGetCopiedText();

						// Check if there are anything selected on the editor
						// If none, use the linkname
						if((copiedText == "") || ($.trim(copiedText) == "")){
							copiedText = linkname;
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

					}else{
						
						// Failure
						quick_add_link_container
							.find("#error-bulletin")
								.text(data)
								.css("display","block");
					
					}

				}
			);

		}else{

			// Prompt Message
			promptErrorMessage();

		}

		// Prevent evernt bubbling
		return false;

	});//$('#quick-add-link').click
	

	/*==============================================
	=           Add New Affiliate Link             =
	==============================================*/
	$("#add-link").click(function(){

		/*==========  Init  ==========*/
		initializeForm();

		/*==========  Data Sanitation  ==========*/
		// Note: JS Validation should not be relied on
		// 		 Just for user convenience purposes
		var linkname         = $.trim(quick_add_link_container.find("#qal_link_name").val()),
			nonce     	     = quick_add_link_container.find("#quick_add_aff_link_nonce").val(),
			linkurl          = $.trim(quick_add_link_container.find("#qal_destination_url").val()),
			nofollow         = $.trim(quick_add_link_container.find("#qal_no_follow_link:checked").val()),
			newwindow        = $.trim(quick_add_link_container.find("#qal_new_window:checked").val()),
			linkredirecttype = $.trim(quick_add_link_container.find("input[name='qal_redirect_type']:checked").val()),
			linkCategory	 = $.trim(quick_add_link_container.find("#qal_link_categories").val());

		// Link Name
		validateLinkName(linkname);

		// Link URL
		validateDestinationURL(linkurl);
		
		// Checkpoint
		if(allClear){

			/*==========  Ajax Call  ==========*/
			// TODO: Refactor this ajax call to another function
			$.post(
				thirstyAjaxLink,
				{
					action           : 'quickCreateAffiliateLink',
					nonce            : nonce,
					linkname         : linkname,
					linkurl          : linkurl,
					nofollow         : nofollow,
					newwindow        : newwindow,
					linkredirecttype : linkredirecttype,
					linkCategory	 : linkCategory
				},
				function(data){

					if(!isNaN(data)){
						
						// Success
						parent.thirstyDismissLinkPicker();
						
					}else{
						
						// Failure
						quick_add_link_container
							.find("#error-bulletin")
								.text(data)
								.css("display","block");
					
					}

				}
			);

		}else{

			// Prompt Message
			promptErrorMessage();

		}

		// Prevent evernt bubbling
		return false;

	});//$('#quick-add-link').click
	
});//document ready



// TODO: Suggest to move this function from ThirstyLinkPicker.js To thistyPickerHelper.js
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