(function() {

	tinymce.create('tinymce.plugins.thirstyaffiliates', {
		/**
		 * Returns information about the plugin as a name/value array.
		 * The current keys are longname, author, authorurl, infourl and version.
		 *
		 * @return {Object} Name/value array containing information about the plugin.
		 */
		getInfo : function() {
			return {
				longname : 'ThirstyAffiliates plugin',
				author : 'Josh Kohlbach',
				authorurl : 'http://thirstyaffiliates.com',
				infourl : 'http://thirstyaffiliates.com',
				version : "1.0"
			};
		},
		
		/**
		 * Initializes the plugin, this will be executed after the plugin has been created.
		 * This call is done before the editor instance has finished it's initialization so use the onInit event
		 * of the editor instance to intercept that event.
		 *
		 * @param {tinymce.Editor} ed Editor instance that the plugin is initialized in.
		 * @param {string} url Absolute URL to where the plugin is located.
		 */
		init : function(ed, url) {
			// Register commands
			ed.addCommand('ThirstyAffiliates', function() {
				
				/* Open the link chooser and pass the editor into it for 
				** updating later */
				
				thirstyOpenLinkPicker(ed);
				
			});

			ed.addCommand('ThirstyAffiliatesQuickAddLink', function() {
				
				/* Open the quick add link thick box */
				
				thirstyOpenQuickAddLinkPicker(ed);
				
			});			
			
			ed.addButton('thirstyaffiliates_button', {
				title : 'Add Affiliate Link',
				image : url + '/img/aff.gif',
				cmd : 'ThirstyAffiliates'
			});

			ed.addButton('thirstyaffiliates_quickaddlink_button', {
				title : 'Quick Add Affiliate Link',
				image : url + '/img/aff-new.gif',
				cmd : 'ThirstyAffiliatesQuickAddLink'
			});
		}
		
	});

	// Register plugin
	tinymce.PluginManager.add('thirstyaffiliates', tinymce.plugins.thirstyaffiliates);
})();
