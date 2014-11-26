=== ThirstyAffiliates ===
Contributors: jkohlbach, RymeraWebCo
Donate link:
Tags: thirstyaffiliates, thirsty affiliates, affiliate marketing, affiliate link management, link cloaking, affiliate links, affiliate link, affiliate link manager, manage affiliate links, affiliate link redirect, link cloak, link cloaker, link redirect
Requires at least: 3.4
Tested up to: 4.0
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Earn more with affiliate marketing using ThirstyAffiliates – the professional affiliate link management & link cloaking tool for affiliates.

== Description ==

Website: http://thirstyaffiliates.com
PRO Add-ons: http://thirstyaffiliates.com/add-ons

ThirstyAffiliates empowers website owners with the tools they need to monetize their WordPress website with affiliate marketing.

It lets you administer your affiliate links, assists you with inserting them into your posts, pages and comments and gives you a central location in WordPress to manage all of your affiliate links.

ThirstyAffiliates will create pretty links from ugly affiliate links (aka link redirection or link cloaking) all the while giving you the double benefit of protecting your commissions from theft.

= Features at a glance =

* Inbuilt affiliate link shortening/link cloaking (yourwebsite.com/recommends/your-affiliate-link)
* Commission protecting affiliate link 301 redirection
* Customizable link URL prefixes – loads to choose from or use your own custom prefix
* Hierarchical link categorization to easily segment links
* Handy affiliate link picker tool (with full instant search capabilities) makes it easy to insert affiliate links in posts, pages and comments!
* Handy quick add tool lets you easily create new affiliate links without even leaving the post edit screen
* Show category slugs in link URLs
* DoFollow/NoFollow options (global or per link)
* Open in new window options (global or per link)
* Full importing and exporting support via standard WordPress tools
* Full backup compatibility via standard WordPress backup solutions
* Uses WordPress approved storage techniques – doesn't bloat your database with extra tables!
* Using the link picker insert affiliate links as standard links, shortcodes (great for adding classes for link styling), or pre-linked images.
* Add your graphics, banners and other images to affiliate links for easy insertion

= Want more PRO features? =

Some of our popular add-ons include:

* Autolinker - get massive increase in your affiliate income by automatically linking affiliate links to keywords throughout your site
* Stats - the insight you need to find out what links are popular on your site
* Google Click Tracking - adds the special Google Analytics Click Event code on your affiliate links as you insert them
* CSV Importer - import your links from other packages in simple CSV format
* Geolocations - geo target your visitors and redirect them to geographically appropriate alternative affiliate URLs, a great way to level up your income
* Scheduled Links - create special schedules for your affiliate links to automatically change the destination URL, great for sales running during a specific time period

[Check out all the ThirstyAffiliates Add-ons](http://thirstyaffiliates.com/add-ons)

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

1. Affiliate link listing

2. Edit screen for a single affiliate link

3. The affiliate link picker dialog, shows up when you press the green 'aff' icon on any editor box

4. Insertion options for affiliate link in the affiliate link picker

5. Settings page

== Changelog ==

= 2.4.10 =
* Bugfix: Additional data on thirsty data not appearing in fields on the edit page was accidentally being blown away, needed to merge data with existing on save
* Bugfix: Use the absolute path during media metadata creation when cloning an image from the media library (thanks Olaf)

= 2.4.9 =
* Bugfix: Fix compatibility issue with some page builders
* Bugfix: Corrected bug with legacy media uploader script loading in the background when it shouldn't be
* Feature: Add new global setting for appending additional rel attributes to links during link insertion
* Bugfix: Fixed image attaching problem where URL would be malformed when duplicating an existing attachment

= 2.4.8 =
* Bugfix: Removed add-on notices from sidebar of edit screens. Causing too many issues, we'll revisit this later.

= 2.4.7 =
* Feature: Add ability to upload multiple images in one hit to affiliate links (thanks Mozie)
* Bugfix: Added mechanism to only download RSS feed of add-ons at most once per day instead of during each page load
* Other: Improved the look of the add-ons page and added meta box to link edit screens for add-on notices

= 2.4.6 =
* Feature: Add option to turn of auto selection of category during save process
* Feature: Show message to user if they have default permalinks selected (we require pretty permalinks)
* Feature: Added an admin function for showing global notices when there is a critical configuration problem
* Bugfix: Links without categories selected should still resolve even if the "show categories in URLs" option is enabled (thanks Olaf)

= 2.4.5 =
* Feature: Add 307 temporary redirection type
* Bugfix: Adding additional info to before redirect action to support upcoming new features in Stats add-on

= 2.4.4 =
* Bugfix: Notify the plain text editor of change event after link is returned
* Bugfix: Hardening up checks for when editor is not properly defined
* Bugfix: Added check to see if debug index is present before using it to prevent outputting notices when wp_debug is set to true, on the settings page
* Feature: Add switches for turning off editor buttons on Visual or Text/Quicktags editors

= 2.4.3 =
* Bugfix: Add html character encoding on link name field on edit screen so link names with quotes are displayed properly

= 2.4.2 =
* Bugfix: Had to retire data filtering with mysql_ escape functions in favour of using esc_sql as provided by WordPress core. Some servers now don't support mysql_ functions at all (thanks Daniel)
* Bugfix: If the post isn't an affiliate link, skip slug shortening
* Feature: Added disable slug shortening option to turn off removing stop words from affiliate link urls

= 2.4.1 =
* IMPORTANT BUG FIX: Default post status is no longer set to 'publish' when left empty in 3.8.2 so we need to set in our custom post save box otherwise you can't save new links.

= 2.4 =
* New Feature: Quick Add button on TinyMCE editor lets you add new affiliate links on the fly without leaving the edit screen
* Fixed admin site debug warning on non-ThirstyAffiliates pages

= 2.3.1 =
* IMPORTANT BUG FIX: Fixed issue with special char filtering causing invalid redirects on some merchants, advise people who are on 2.3 to update to this version immediately.

= 2.3 =
* New Feature: Added the option to choose a global redirect type (301 or 302 at this stage) and also the option to override this per link
* Admin layout compatibility with WordPress 3.8 specifically responsiveness
* Changed menu name to Affiliate Links as 3.8 made ThirstyAffiliates word wrap to the next line
* Inserting images using the link picker stopped working due to imageID not being passed properly
* Moving some core styles for the settings page to the core stylesheet
* Improved data filtering function to selectively strip html on input data
* Filtered data wasn't being passed to array elements properly and hence was unfiltered

= 2.2.6 =
* Tweaked add-on page layout

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
