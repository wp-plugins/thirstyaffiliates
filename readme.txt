=== ThirstyAffiliates ===
Contributors: jkohlbach,rymerawebco
Donate link: 
Tags: thirstyaffiliates, thirsty affiliates, affiliate marketing, affiliate, affiliate links, affiliate link, affiliate link management, affiliate link manager, manage links, manage affiliate links, redirection, redirect, cloak, cloaker, cloaking, redirecting, redirect links, link cloaking, link redirect
Requires at least: 3.4
Tested up to: 3.6
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Affiliate link management for affiliate marketers. This affiliate marketing plugin helps you organize your affiliate links, can give you accurate click tracking & help you monetize your WordPress website on autopilot.

== Description ==

Website: http://thirstyaffiliates.com

ThirstyAffiliates empowers website owners with the tools they need to monetize their WordPress website with affiliate marketing.

It lets you administer the affiliate links you’re inserting into your website from a central location in WordPress.

ThirstyAffiliates will create pretty links from ugly affiliate links all the while giving you the double benefit of protecting your commissions from theft.

= Features at a glance =

* Inbuilt affiliate link shortening (yourwebsite.com/recommends/your-affiliate-link)
* Commission protecting affiliate link 301 redirection
* Customizable link URL prefixes – dozens to choose from or use your own custom prefix
* Hierarchical link categorization to easily segment links
* Show category slugs in link URLs
* DoFollow/NoFollow options (global or per link)
* Open in new window options (global or per link)
* Full importing and exporting support via standard WordPress tools
* Full backup support via standard WordPress backup solutions
* Uses WordPress approved coding techniques, doesn’t bloat your database with extra tables
* Handy link builder authoring tool with full search capabilities
* Insert using standard link
* Shortcode link support for link styling
* Add images to affiliate links for easy insertion
* ... and loads more via our [official add-ons](http://thirstyaffiliates.com/add-ons)!

== Installation ==

1. Upload the `thirstyaffiliates/` folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Visit the new 'ThirstyAffiliates' menu and click 'Add New' to add an affiliate link.
1. Visit [http://thirstyaffiliates.com/](http://thirstyaffiliates.com/) for more information, add-ons and support.

== Frequently asked questions ==

= Help, my links aren't redirecting! It just brings up a 404 error. =

This is a common issue usually caused by another plugin or theme not flushing redirect rules correctly after setting up another custom post type.

To resolve the problem follow these steps:

1. Deactivate ThirstyAffiliates, don't worry your links are safely stored in the database.
1. Reactivate ThirstyAffiliates
1. Visit the Settings->Permalinks page and click save.
1. Go back to ThirstyAffiliates->All Affiliate Links and view one of the links, it should be redirecting properly.

= Are there any known conflicts? =
WordPress supports a very vibrant ecosystem of themes and plugins so from time to time it's expected that there will be some minor conflicts with other products.

Here is a list of known conflicts and their workarounds (if one exists):

**Bulletproof Security plugin - conflict with ThirstyAffiliates link picker not showing.**

Fix: add this to your .htaccess file in your WP root directory
`# Thirstyaffiliates skip/bypass rule
RewriteCond %{REQUEST_URI} ^/wp-content/plugins/thirstyaffiliates/ [NC]
RewriteRule . - [S=13]`

**WooThemes – minor conflict with image uploader being taken over by the theme. Happens with Canvas and some other WooThemes themes.**

Fix: add this to your functions.php in your theme:
`add_action( 'admin_enqueue_scripts', 'ta_remove_wf_media_assets' );

function ta_remove_wf_media_assets ( $hook ) {
	if ( 'post.php' == $hook && 'thirstylink' == get_post_type() ) {
		remove_action( 'admin_print_styles', 'woothemes_mlu_css', 0 );
		remove_action( 'admin_print_scripts', 'woothemes_mlu_js', 0 );
	}
} // End ta_remove_wf_media_assets()`

Thanks to Matty of WooThemes for the workaround.

== Screenshots ==

Coming soon!

== Changelog ==

= 2.2.5 =
* Bug fix title attribute not showing on shortcodes
* Make shortcodes obey the global flags properly
* Output shortcode defaults properly when no attributes specified (backwards compatibility)

= 2.2.4 =
* Fixing regression in shortcode functionality (thanks Roshan)

= 2.2.3 =
* Fixed link of categories a link belongs to in list view to show a list of links from that category
* Fixed warning on settings page and added sanity checks for link prefix option
* Added debug flag to settings page for support and testing
* Deprecated PHP function split() replaced with preg_split(), was throwing a warning on PHP 5.3.0+ servers.

= 2.2.2 =
* During link insertion check if copied text contains HTML and handle appropriately
* Add global switch to turn off title attribute on inserted links
* Add global switch to turn off thirstylink and thirstylinkimg link classes
* Strip slashes from apostrophes in copied text during link insertion
* Remove ajax url from front end (no longer used)

= 2.2.1 =
* Fixing add-ons page (thanks Hesham)
* Adding javascript layout tweak

= 2.2 =
* Adding API hooks for future development
* Cleaning up code structure
* Fixing ajax calls to be fully documentation compliant
* Fixes for the image adding including support for new media loader
* Added better object handling for image attachments
* Moved off some add-on specific settings to their own plugin code bases
* Lots of other small bug fixes and improvements
* Adding plugin to the WordPress.org directory!

= 2.1.3 =
* Removing unneccesary data from allagents.xml
* Adding no listing directive to plugin directory to prevent indexing

= 2.1.2 =
* Bug fix for link names with quotes in them

= 2.1.1 =
* Remove superfluous words from auto generated slugs on link creation

= 2.1 =
* Allow categories in link slugs
* Add option to force rebuild links (re-saves all existing links)
* Fix saving bug on nofollow/new window options when not using global setting
* Fix compatibility issue with Thesis theme
* Other various small bug fixes

= 2.0.3 =
* Fix for curly braces in links

= 2.0.2 =
* Fixing incompatibilities with some premium themes

= 2.0.1 =
* Fixed menu disappearing bug due to WP update

= 2.0 =
* Introduced automatic updating
* Improved user interface
* Added web crawler robot blocking
* Other various small bug fixes

= Prior to 2.0 =
* Initial version

== Upgrade notice ==

There is a new version of ThirstyAffiliates available.
