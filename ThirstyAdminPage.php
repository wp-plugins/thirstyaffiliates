<?php

/*******************************************************************************
** thirstySetupMenu()
** Setup the plugin options menu
** @since 1.0
*******************************************************************************/
function thirstySetupMenu() {
	if (is_admin()) {
		register_setting('thirstyOptions', 'thirstyOptions');
		add_submenu_page('edit.php?post_type=thirstylink', 'Settings', 'Settings', 'manage_options', 'thirsty-settings', 'thirstyAdminOptions');
	}
}

/*******************************************************************************
** thirstyAdminOptions
** Present the options page
** @since 1.0
*******************************************************************************/
function thirstyAdminOptions() {
	if (!current_user_can('manage_options'))  {
		wp_die( __('You do not have suffifient permissions to access this page.') );
	}

	$thirstyOptions = get_option('thirstyOptions');

	$linksRebuilt = false;
	if ($thirstyOptions['rebuildlinks'] == 'true') {
		$thirstyOptions['rebuildlinks'] = 'false';
		update_option('thirstyOptions', $thirstyOptions);
		$thirstyOptions = get_option('thirstyOptions');
		thirstyResaveAllLinks();
		flush_rewrite_rules();
		$linksRebuilt = true;
	}

	// Sanity check on link prefix
	if (empty($thirstyOptions['linkprefix'])) {
		$thirstyOptions['linkprefix'] = 'recommends';
		update_option('thirstyOptions', $thirstyOptions);
	}

	$redirectTypes = thirstyGetRedirectTypes();

	// Sanity check on link redirect type
	if (empty($thirstyOptions['linkredirecttype'])) {
		$thirstyOptions['linkredirecttype'] = '301';
		update_option('thirstyOptions', $thirstyOptions);
	}

	$thirstyOptions['nofollow'] = isset($thirstyOptions['nofollow']) ? 'checked="checked"' : '';
	$thirstyOptions['newwindow'] = isset($thirstyOptions['newwindow']) ? 'checked="checked"' : '';
	$thirstyOptions['showcatinslug'] = isset($thirstyOptions['showcatinslug']) ? 'checked="checked"' : '';
	$thirstyOptions['legacyuploader'] = isset($thirstyOptions['legacyuploader']) ? 'checked="checked"' : '';
	$thirstyOptions['disabletitleattribute'] = isset($thirstyOptions['disabletitleattribute']) ? 'checked="checked"' : '';
	$thirstyOptions['disablethirstylinkclass'] = isset($thirstyOptions['disablethirstylinkclass']) ? 'checked="checked"' : '';
	$thirstyOptions['disableslugshortening'] = isset($thirstyOptions['disableslugshortening']) ? 'checked="checked"' : '';
	$thirstyOptions['disablevisualeditorbuttons'] = isset($thirstyOptions['disablevisualeditorbuttons']) ? 'checked="checked"' : '';
	$thirstyOptions['disabletexteditorbuttons'] = isset($thirstyOptions['disabletexteditorbuttons']) ? 'checked="checked"' : '';

	echo '<script type="text/javascript">var thirstyPluginDir = "' .
	plugins_url('thirstyaffiliates/') . '";
	var thirstyJSEnable = true;
	</script>';

	echo '<div class="wrap">';

	echo '<img id="thirstylogo" src="' . plugins_url('thirstyaffiliates/images/thirstylogo.png') . '" alt="ThirstyAffiliates" />';

	echo '<form id="thirstySettingsForm" method="post" action="options.php">';

	wp_nonce_field('update-options');
	settings_fields('thirstyOptions');

	if (!empty($_GET['settings-updated'])) {
		echo '<div id="message" class="updated below-h2"><p>Settings updated.</p>' .
		($linksRebuilt ? '<p>Links rebuilt.</p>' : '') . '</div>';
	}

	echo '
	<table class="thirstyTable form-table" cellspacing="0" cellpadding="0">

	<tr><td><h3 style="margin-top: 0;">General Settings</h3></td></tr>

	<tr>
		<th>
			<label for="thirstyOptions[linkprefix]">Link Prefix:</label>
		</th>
		<td>
			<select id="thirstyOptionsLinkPrefix" name="thirstyOptions[linkprefix]">
				<option value="custom"' . (!empty($thirstyOptions['linkprefix']) && $thirstyOptions['linkprefix'] == 'custom' ? ' selected' : '') . '>-- Custom --</option>';

		thirstyGenerateSelectOptions(array("recommends", "link", "go", "review",
			"product", "suggests", "follow", "endorses", "proceed", "fly", "goto",
			"get", "find", "act", "click", "move", "offer", "run"), true);

		echo '</select><br />
			<input type="text" id="thirstyCustomLinkPrefix" value="' . (isset($thirstyOptions['linkprefixcustom']) ? $thirstyOptions['linkprefixcustom'] : '') . '" name="thirstyOptions[linkprefixcustom]" />';

		if (!empty($thirstyOptions['linkprefix']) && $thirstyOptions['linkprefix'] == 'custom') {
			echo '<script type="text/javascript">
			jQuery("#thirstyCustomLinkPrefix").show();</script>';
		}

		echo '</td>
		<td>
			<span class="description">The prefix that comes before your cloaked link\'s slug.<br />eg. ' .
			trailingslashit(get_bloginfo('url')) . '<span style="font-weight: bold;">' . thirstyGetCurrentSlug() . '</span>/your-affiliate-link-name</span>
			<br /><span class="description"><b>Warning:</b> Changing this setting after you\'ve used links in a post could break those links. Be careful!</span>
		</td>
	</tr>

	<tr>
		<th>
			<label for="thirstyOptions[showcatinslug]">Show Link Category in URL?</label>
		<td>
			<input type="checkbox" name="thirstyOptions[showcatinslug]" id="thirstyOptionsShowCatInSlug" ' .
			$thirstyOptions['showcatinslug'] . ' />
		</td>
		<td>
			<span class="description">Show the selected category in the url. eg. ' .
			trailingslashit(get_bloginfo('url')) . '' . thirstyGetCurrentSlug() . '/<span style="font-weight: bold;">link-category</span>/your-affiliate-link-name</span></span>
			<br /><span class="description"><b>Warning:</b> Changing this setting after you\'ve used links in a post could break those links. Be careful!</span>
		</td>
	</tr>

	<tr>
		<th>
			<label for="thirstyOptions[linkredirecttype]">Link Redirect Type:</label>
		<td>';

	foreach ($redirectTypes as $redirectTypeCode => $redirectTypeDesc) {

		$linkTypeSelected = false;
		if (strcasecmp($thirstyOptions['linkredirecttype'], $redirectTypeCode) == 0)
			$linkTypeSelected = true;

		echo '<input type="radio" name="thirstyOptions[linkredirecttype]" id="thirstyOptionsLinkRedirectType' . $redirectTypeCode .'" ' .
			($linkTypeSelected ? 'checked="checked" ' : '') . 'value="' . $redirectTypeCode . '" /> <label for="thirstyOptionsLinkRedirectType' . $redirectTypeCode .'">' . $redirectTypeDesc . '</label><br />';

	}

	echo '
		</td>
		<td>
			<span class="description">This is the type of redirect ThirstyAffiliates will use to redirect the user to your affiliate link.</span>
		</td>
	</tr>

	<tr>
		<th>
			<label for="thirstyOptions[nofollow]">Use no follow on links?</label>
		<td>
			<input type="checkbox" name="thirstyOptions[nofollow]" id="thirstyOptionsNofollow" ' .
			$thirstyOptions['nofollow'] . ' />
		</td>
		<td>
			<span class="description">Add the nofollow attribute to links so search engines don\'t index them</span>
		</td>
	</tr>

	<tr>
		<th>
			<label for="thirstyOptions[newwindow]">Open links in new window?</label>
		<td>
			<input type="checkbox" name="thirstyOptions[newwindow]" id="thirstyOptionsNewwindow" ' .
			$thirstyOptions['newwindow'] . ' />
		</td>
		<td>
			<span class="description">Force the user to open links in a new window or tab</span>
		</td>
	</tr>

	<tr>
		<th>
			<label for="thirstyOptions[legacyuploader]">Revert to legacy image uploader?</label>
		<td>
			<input type="checkbox" name="thirstyOptions[legacyuploader]" id="thirstyOptionsLegacyUploader" ' .
			$thirstyOptions['legacyuploader'] . ' />
		</td>
		<td>
			<span class="description">Disable the new media uploader in favour of the old style uploader</span>
		</td>
	</tr>

	<tr>
		<th>
			<label for="thirstyOptions[disabletitleattribute]">Disable title attribute output on link insertion?</label>
		<td>
			<input type="checkbox" name="thirstyOptions[disabletitleattribute]" id="thirstyOptionsDisableTitleAttribute" ' .
			$thirstyOptions['disabletitleattribute'] . ' />
		</td>
		<td>
			<span class="description">Links are automatically output with a title html attribute (by default this shows the text
			that you have linked), this option lets you disable the output of the title attribute on your links.</span>
		</td>
	</tr>

	<tr>
		<th>
			<label for="thirstyOptions[disablethirstylinkclass]">Disable automatic output of ThirstyAffiliates CSS classes?</label>
		<td>
			<input type="checkbox" name="thirstyOptions[disablethirstylinkclass]" id="thirstyOptionsDisableThirstylinkClass" ' .
			$thirstyOptions['disablethirstylinkclass'] . ' />
		</td>
		<td>
			<span class="description">To help with styling your affiliate links a CSS class called "thirstylink" is added
			to the link and a CSS class called "thirstylinkimg" is added to images (when inserting image affiliate links),
			this option disables the addition of both of these CSS classes.</span>
		</td>
	</tr>

	<tr>
		<th>
			<label for="thirstyOptions[disableslugshortening]">Disable slug shortening?</label>
		<td>
			<input type="checkbox" name="thirstyOptions[disableslugshortening]" id="thirstyOptionsDisableSlugShortening" ' .
			$thirstyOptions['disableslugshortening'] . ' />
		</td>
		<td>
			<span class="description">By default, ThirstyAffiliates removes superfluous words from your cloaked link URLs, this option turns that feature off.</span>
		</td>
	</tr>

	<tr>
		<th>
			<label for="thirstyOptions[disablevisualeditorbuttons]">Disable buttons on the Visual editor?</label>
		<td>
			<input type="checkbox" name="thirstyOptions[disablevisualeditorbuttons]" id="thirstyOptionsDisableVisualEditorButtons" ' .
			$thirstyOptions['disablevisualeditorbuttons'] . ' />
		</td>
		<td>
			<span class="description">Hide the ThirstyAffiliates buttons on the Visual editor.</span>
		</td>
	</tr>

	<tr>
		<th>
			<label for="thirstyOptions[disabletexteditorbuttons]">Disable buttons on the Text/Quicktags editor?</label>
		<td>
			<input type="checkbox" name="thirstyOptions[disabletexteditorbuttons]" id="thirstyOptionsDisableTextEditorButtons" ' .
			$thirstyOptions['disabletexteditorbuttons'] . ' />
		</td>
		<td>
			<span class="description">Hide the ThirstyAffiliates buttons on the Text editor.</span>
		</td>
	</tr>';

	do_action('thirstyAffiliatesAfterMainSettings');

	echo '
	</table>

	<input type="hidden" name="thirstyOptions[rebuildlinks]" id="thirstyHiddenRebuildFlag" value="false" />

	<input type="hidden" name="page_options" value="thirstyOptions" />

	<p class="submit">
	<input type="submit" class="button-primary" value="Save All Changes" />
	<input type="submit" id="thirstyForceLinkRebuild" class="button-secondary" value="Save & Force Link Rebuild" />
	</p>

	</form>

	<div class="thirstyWhiteBox">

		<h3>Plugin Information</h3>

		ThirstyAffiliates Version: ' . THIRSTY_VERSION . '<br />';

		do_action('thirstyAffiliatesPluginInformation');

	echo '</div><!-- /.thirstyWhiteBox -->';

	echo '
		<div class="thirstyWhiteBox">
			<h3>Join The Community</h3>
			<ul id="thirstyCommunityLinks"><li><a href="http://thirstyaffiliates.com">Visit Our Website</a></li>
				<li><a href="' . admin_url('edit.php?post_type=thirstylink&page=thirsty-addons') . '">Browse ThirstyAffiliates Add-ons</a></li>
				<li><a href="http://thirstyaffiliates.com/affiliates">Join Our Affiliate Program</a> (up to 50% commissions)</li>
				<li><a href="http://facebook.com/thirstyaffiliates" style="margin-right: 10px;">Like us on Facebook</a><iframe src="//www.facebook.com/plugins/like.php?href=https%3A%2F%2Fwww.facebook.com%2Fthirstyaffiliates&amp;send=false&amp;layout=button_count&amp;width=450&amp;show_faces=false&amp;font=arial&amp;colorscheme=light&amp;action=like&amp;height=21" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:450px; height:21px; vertical-align: bottom;" allowTransparency="true"></iframe></li>
				<li><a href="http://twitter.com/thirstyaff" style="margin-right: 10px;">Follow us on Twitter</a> <a href="https://twitter.com/thirstyaff" class="twitter-follow-button" data-show-count="true" style="vertical-align: bottom;">Follow @thirstyaff</a><script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?"http":"https";if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+"://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document, "script", "twitter-wjs");</script></li>
			</ul>
		</div><!-- /.thirstyWhiteBox -->

	</div><!-- /.wrap -->';

	// Provide debug output for diagnostics and support use
    if(isset($_GET['debug'])){
        if ($_GET['debug'] == 'true') {
            $thirstyOptions = get_option('thirstyOptions'); // re-retrieve options in case any of the filters/actions messed with it
            echo '<pre>DEBUG: ' . print_r($thirstyOptions, true) . '</pre>';
        }
    }
}

/*******************************************************************************
** thirstyResaveAllLinks
** Resave all ThirstyAffiliates links in the system. Allows us to regenerate the
** slug and permalink after big settings changes.
** @since 2.1
*******************************************************************************/
function thirstyResaveAllLinks() {

	$thirstyLinkQuery = new WP_Query(array(
		'post_type' => 'thirstylink',
		'post_status' => 'publish',
		'posts_per_page' => -1,
		'ignore_sticky_posts'=> 1
	));

	if($thirstyLinkQuery->have_posts()) {
		while ($thirstyLinkQuery->have_posts()) {
			$thirstyLinkQuery->the_post();

			$thirstyLink['ID'] = get_the_ID();
			wp_update_post($thirstyLink);
		}
	}
}

/*******************************************************************************
** thirstyGenerateSelectOptions
** Helper function to generate selection boxes for admin page
** @since 1.0
*******************************************************************************/
function thirstyGenerateSelectOptions($selectNames, $echo = false) {
	$thirstyOptions = get_option('thirstyOptions');
	$html = '';

	foreach ($selectNames as $selectName) {
		$html .= '<option value="' . $selectName . '"' . ($thirstyOptions['linkprefix'] == $selectName ? ' selected' : '') . '>' . $selectName . '</option>';
	}

	if ($echo)
		echo $html;
	else
		return $html;
}

add_action('admin_menu', 'thirstySetupMenu', 99);
?>
