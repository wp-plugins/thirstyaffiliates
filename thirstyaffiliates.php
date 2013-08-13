<?php
/*
* Plugin Name: ThirstyAffiliates
*
* Description: ThirstyAffiliates is a revolution in affiliate link management. Collect, collate and store your affiliate links for use in your posts and pages.
*
* Author: ThirstyAffiliates
* Author URI: http://thirstyaffiliates.com
* Plugin URI: http://thirstyaffiliates.com
* Version: 2.2.5
*/

define('THIRSTY_VERSION', '2.2.5', true);

/******************************************************************************* 
** thirstyRegisterPostType
** Register the post types required by the plugin
** @since 1.0
*******************************************************************************/
function thirstyRegisterPostType() {
	$thirstyOptions = get_option('thirstyOptions');
	$slug = thirstyGetCurrentSlug();
	
	/* Register the taxonomy for the affiliate links */
	register_taxonomy(
		'thirstylink-category',
		'thirstylink',
		array(
			'labels' => array(
				'name' => 'Link Categories',
				'singular_name' => 'Link Category'
			),
			'public' => true,
			'show_ui' => true,
			'hierarchical' => true,
			'show_tagcloud' => false,
			'rewrite' => false
		)
	);
	
	/* Register the post type */
	register_post_type(
		'thirstylink',
		array(
			'labels' => array(
				'name' => __('Affiliate Links'),
				'singular_name' => __('Affiliate Link'),
				'add_new_item' => __('Add New Affiliate Link'),
				'edit_item' => __('Edit Affiliate Link'),
				'view_item' => __('View Affiliate Link'),
				'search_items' =>  __('Search Affiliate Links'),
				'not_found' => __('No Affiliate Links found!'),
				'not_found_in_trash' => __('No Affiliate Links found in trash'),
				'menu_name' => __('ThirstyAffiliates'),
				'all_items' => __('All Affiliate Links')
			),
			'description' => 'ThirstyAffiliates affiliate links',
			'public' => true,
			'menu_position' => 20,
			'hierarchical' => true,
			'supports' => array(
				'title' => false,
				'editor' => false,
				'author' => false,
				'thumbnail' => false,
				'excerpt' => false,
				'trackbacks' => false,
				'comments' => false,
				'revisions' => false,
				'page-attributes' => false,
				'post-formats' => false			
			),
			'show_in_menu' => true,
			'show_in_nav_menus' => true,
			'can_export' => true,
			'has_archive' => false,
			'rewrite' => array(
				'slug' => $slug,
				'with_front' => false,
				'pages' => false
			),
			'menu_icon' => plugins_url('thirstyaffiliates/images/icon-aff.png'),
			'exclude_from_search' => true
		)
	);
	
	add_rewrite_tag('%' . $slug . '%', '([^&]+)');
	
	if (!empty($thirstyOptions['showcatinslug']) && $thirstyOptions['showcatinslug'] == 'on') {
		add_rewrite_tag('%thirstylink-category%', '([^&]+)');
		add_rewrite_rule("$slug/([^/]+)?/?([^/]+)?/?",'index.php?thirstylink=$matches[2]&thirstylink-category=$matches[1]', 'top');
	}
	
	if (get_option('thirstyaffiliates_flush') == 'true') {
        flush_rewrite_rules();
        delete_option('thirstyaffiliates_flush');
    }
	
	/* Set the list page columns */
	add_filter('manage_thirstylink_posts_columns', 'thirstyAddDestinationColumnToList');
	add_filter('manage_thirstylink_posts_columns', 'thirstyAddCategoryColumnToList');
	add_action('manage_pages_custom_column', 'thirstyShowCategoryColumnInList');
	add_action('manage_pages_custom_column', 'thirstyShowDestinationColumnInList');
	
	/* Setup the filter drop down */
	add_action('restrict_manage_posts', 'thirstyRestrictLinksByCategory');
	add_filter('parse_query', 'thirstyConvertLinkCatIdToSlugInQuery');
}

/******************************************************************************* 
** thirstyForceSend
** Force showing the "Insert into Post" button on the media handler for links
** @since 2.1
*******************************************************************************/
function thirstyForceSend($args){
	if (!empty($_GET['post_id']))
		$pid = $_GET['post_id'];
	else
		return $args;
	
	if(get_post_type($pid) == 'thirstylink') {
		$args['send'] = true;
	}
	return $args;
}

/******************************************************************************* 
** thirstyGetCurrentSlug
** Get the current link prefix setting
** @since 2.1
*******************************************************************************/
function thirstyGetCurrentSlug() {
	$thirstyOptions = get_option('thirstyOptions');
	$slug = 'recommends';
	
	if (isset($thirstyOptions['linkprefix'])) {
		if ($thirstyOptions['linkprefix'] == 'custom' && isset($thirstyOptions['linkprefixcustom'])) {
			$slug = $thirstyOptions['linkprefixcustom'];
		} else {
			$slug = $thirstyOptions['linkprefix'];
		}
	}
	
	return $slug;
}

/******************************************************************************* 
** thirstyCatLinks
** Handle how link slugs are created and optionally add in the categories
** @since 2.1
*******************************************************************************/
function thirstyCatLinks($post_link, $post) {
	$thirstyOptions = get_option('thirstyOptions');
	$slug = thirstyGetCurrentSlug();	
	
	if (is_wp_error($post) || 
		empty($post) || 
		(!empty($post) && $post->post_type != 'thirstylink') || 
		empty($post->post_name))
		return $post_link;
	
	// Get the link category:
	$terms = get_the_terms($post->ID, 'thirstylink-category');
	
	if (empty($thirstyOptions['showcatinslug']) || 
		$thirstyOptions['showcatinslug'] != 'on' || 
		is_wp_error($terms) || !$terms) {
		$linkCat = '';
	} else {
		$linkCatObj = array_pop($terms);
		$linkCat = $linkCatObj->slug . '/';
		
	}
	
	return home_url(user_trailingslashit("$slug/$linkCat$post->post_name"));
	
}

/******************************************************************************* 
** thirstyAddDestinationColumnToList
** Add the destination column to the list page
** @since 1.0
*******************************************************************************/
function thirstyAddDestinationColumnToList($posts_columns) {
    if (!isset($posts_columns['date'])) {
        $new_posts_columns = $posts_columns;
    } else {
        $new_posts_columns = array();
        $index = 0;
        foreach($posts_columns as $key => $posts_column) {
            if ($key=='date')
                $new_posts_columns['thirstylink-destination'] = null;
            $new_posts_columns[$key] = $posts_column;
        }
    }
    $new_posts_columns['thirstylink-destination'] = 'Link Destination';
    return $new_posts_columns;
}

/******************************************************************************* 
** thirstyAddCategoryColumnToList
** Add the category column to the list page
** @since 1.0
*******************************************************************************/
function thirstyAddCategoryColumnToList($posts_columns) {
    if (!isset($posts_columns['date'])) {
        $new_posts_columns = $posts_columns;
    } else {
        $new_posts_columns = array();
        $index = 0;
        foreach($posts_columns as $key => $posts_column) {
            if ($key=='date')
                $new_posts_columns['thirstylink-category'] = null;
            $new_posts_columns[$key] = $posts_column;
        }
    }
    $new_posts_columns['thirstylink-category'] = 'Link Categories';
    return $new_posts_columns;
}

/******************************************************************************* 
** thirstyShowDestinationColumnInList
** Get the destination details for the list page
** @since 1.0
*******************************************************************************/
function thirstyShowDestinationColumnInList($column) {
	global $typenow;
	global $post;
	
	if ($typenow == 'thirstylink') {
		
		switch ($column) {
		case 'thirstylink-destination':
			$linkData = unserialize(get_post_meta($post->ID, 'thirstyData', true));
			echo $linkData['linkurl'];
			break;
		}
		
	}
}

/******************************************************************************* 
** thirstyShowCategoryColumnInList
** Get the category details for the list page
** @since 1.0
*******************************************************************************/
function thirstyShowCategoryColumnInList($column) {
	global $typenow;
	global $post;
	
	if ($typenow == 'thirstylink') {
		
		switch ($column) {
		case 'thirstylink-category':
			$taxonomy = 'thirstylink-category';
			$thirstyCats = get_the_terms($post->ID, $taxonomy);
			$thirstyCatsFormatted = array();

			if (is_array($thirstyCats) && !empty($thirstyCats)) {
				
				// pre-sort array by parent value
				uasort($thirstyCats, 'thirstySortArrayByParent');
				
				// setup sorted array
				$sortedCats = array();
				
				// loop through all cats
				while (!empty($thirstyCats)) {
					$term = current($thirstyCats);
					$key = key($thirstyCats);

					$sortedCats[] = $term;
					unset($thirstyCats[$key]); // pop current parent term
					$children = array();
					$children = thirstyGetChildrenOfCat($term, $thirstyCats);
					
					// add each child to the array
					while ($childterm = current($children)) {
						$sortedCats[] = $childterm;
						unset($thirstyCats[key($children)]);
						next($children);
					}
				}
				
				foreach ($sortedCats as $key => $term) {
					$editLink = admin_url('edit.php?thirstylink-category=' . $term->slug . '&post_type=thirstylink');
					$is_parent = $term->parent == 0;
					
					echo ($is_parent ? '<p><b>' : '&nbsp;&nbsp;') . 
					'<a href="' . $editLink . '">' . $term->name . '</a>' . 
					($is_parent ? '</b><br />' : '');
				}
				
			}
			break;
		}
		
	}
}

/******************************************************************************* 
** thirstySortArrayByParent
** Convenience function to sort an array by it's parent
** @since 1.0
*******************************************************************************/
function thirstySortArrayByParent($a, $b) {
	if ($a->parent < $b->parent) {
		return -1;
	} else if ($a->parent > $b->parent) {
		return 1;
	} else {
		return 0;
	}
}

/******************************************************************************* 
** thirstyGetChildrenOfCat
** Get the children of a thirsty affiliates category and return them
** @since 1.0
*******************************************************************************/
function thirstyGetChildrenOfCat($parent, $cats) {
	$children = array();
	
	if (!empty($cats)) {
		foreach ($cats as $key => $term) {
			if ($term->parent == $parent->term_id) {
				// is a child of the parent
				$children[$key] = $term;
			}
		}
	}
	
	return $children;
}

/******************************************************************************* 
** thirstyRestrictLinksByCategory
** Setup the filter box for the list page so people can filter their links via 
** category
** @since 1.0
*******************************************************************************/
function thirstyRestrictLinksByCategory() {
	global $typenow;
	global $wp_query;
	
	if ($typenow == 'thirstylink') {
		
		$taxonomy = 'thirstylink-category';
		$thirstyTax = get_taxonomy($taxonomy);
		
		wp_dropdown_categories(array(
			'show_option_all' => __("Show {$thirstyTax->labels->all_items}"),
			'taxonomy' => $taxonomy,
			'name' => $taxonomy,
			'orderby' => 'name',
			'selected' => (isset($wp_query->query[$taxonomy]) ? $wp_query->query[$taxonomy] : ''),
			'hierarchical' => true,
			'depth' => 4,
			'show_count' => true,
			'hide_empty' => true
		));
	}
}

/******************************************************************************* 
** thirstyConvertLinkCatIdToSlugInQuery
** Setup the filter box for the list page so people can filter their links via 
** category
** @since 1.0
*******************************************************************************/
function thirstyConvertLinkCatIdToSlugInQuery($query) {
	global $pagenow;
	$qv = &$query->query_vars;
	
	if (isset($qv['thirstylink-category']) && 
		is_numeric($qv['thirstylink-category'])) {
		
		$term = get_term_by('id', $qv['thirstylink-category'], 'thirstylink-category');
		$qv['thirstylink-category'] = $term->slug;
		
	}
}

/******************************************************************************* 
** thirstySetupPostBoxes
** Setup the input boxes for the link post type
** @since 1.0
*******************************************************************************/
function thirstySetupPostBoxes() {
	add_meta_box(
		'thirstylink-link-name-meta',
		'Affiliate Link Name',
		'thirstyLinkNameMeta',
		'thirstylink',
		'normal',
		'high'
	);
	
	add_meta_box(
		'thirstylink-link-url-meta',
		'URLs',
		'thirstyLinkUrlMeta',
		'thirstylink',
		'normal',
		'high'
	);
	
	add_meta_box(
		'thirstylink-link-images-meta',
		'Attach Images',
		'thirstyLinkImagesMeta',
		'thirstylink',
		'normal',
		'high'
	);
	
	remove_meta_box( 'submitdiv', 'thirstylink', 'side' );
	
	add_meta_box(
		'thirstylink-save-link-side-meta',
		'Save Affiliate Link',
		'thirstySaveLinkMeta',
		'thirstyLink',
		'side',
		'high'
	);
	
	add_meta_box(
		'thirstylink-save-link-bottom-meta',
		'Save Affiliate Link',
		'thirstySaveLinkMeta',
		'thirstyLink',
		'normal',
		'low'
	);
}

/******************************************************************************* 
** thirstySaveLinkMeta
** Save link meta box
** @since 1.0
*******************************************************************************/
function thirstySaveLinkMeta() {
	global $post;
	
	echo '<p class="thirstySaveMe">NOTE: Please save your link after adding or removing images</p>';
	echo '<input name="original_publish" type="hidden" id="original_publish" value="Save" />';
	echo '<input name="save" type="submit" class="button-primary" id="publish" tabindex="5" accesskey="p" value="Save Link">';
	
	if (current_user_can("delete_post", $post->ID)) {
		if (!EMPTY_TRASH_DAYS)
			$delete_text = __('Delete Permanently');
		else
			$delete_text = __('Move to Trash');
			
		echo '&nbsp;&nbsp;<a class="submitdelete deletion" href="' . get_delete_post_link($post->ID) . '">' . $delete_text . '</a>';
	}
}

/******************************************************************************* 
** thirstyLinkNameMeta
** Link name meta box
** @since 1.0
*******************************************************************************/
function thirstyLinkNameMeta() {
	wp_nonce_field( plugin_basename(__FILE__), 'thirstyaffiliates_noncename' );
	
	global $post;
	$linkData = unserialize(get_post_meta($post->ID, 'thirstyData', true));
	
	$thirstyOptions = get_option('thirstyOptions');
	echo '<p><label class="infolabel" for="post_title">Link Name:</label></p>';
	echo '<p><input id="thirsty_linkname" name="post_title" value="' . (!empty($linkData['linkname']) ? $linkData['linkname'] : '') . 
	'" size="50" type="text" /></p>';
	
}

/******************************************************************************* 
** thirstyLinkUrlMeta
** Link slug meta box
** @since 1.0
*******************************************************************************/
function thirstyLinkUrlMeta() {
	wp_nonce_field( plugin_basename(__FILE__), 'thirstyaffiliates_noncename' );
	
	global $post;
	$linkData = unserialize(get_post_meta($post->ID, 'thirstyData', true));
	$linkData['nofollow'] = isset($linkData['nofollow']) ? 'checked="checked"' : '';
	$linkData['newwindow'] = isset($linkData['newwindow']) ? 'checked="checked"' : '';
	
	$thirstyOptions = get_option('thirstyOptions');
	
	echo '<style>
	label.infolabel {
		margin-right: 10px;
	}
	</style>';
	
	echo '<p><label class="infolabel" for="thirsty[linkurl]">Destination URL:</label></p>';
	echo '<p><input id="thirsty_linkurl" name="thirsty[linkurl]" value="' . html_entity_decode((!empty($linkData['linkurl']) ? $linkData['linkurl'] : '')) . '" size="50" type="text" /></p>';
	
	/* Only show permalink if it's an existing post */
	if (!empty($post->post_title)) {
		echo '<p><label class="infolabel">Cloaked URL:</label></p>';
		echo '<input type="text" readonly="readonly" id="thirsty_cloakedurl" value="' . get_post_permalink($post->ID) . '"> <span class="button-secondary" id="thirstyEditSlug">Edit Slug</span> <a href="' . get_post_permalink($post->ID) . '" target="_blank"><span class="button-secondary" id="thirstyVisitLink">Visit Link</span></a><input id="thirsty_linkslug" name="post_name" value="' . $post->post_name . '" size="50" type="text" /></span> <input id="thirstySaveSlug" type="button" value="Save" class="button-secondary" /></p>';
	}
	
	/* Only display link nofollow setting if the global nofollow setting is disabled */
	if ($thirstyOptions['nofollow'] != 'on') {
		echo '<p><label class="infolabel" for="thirsty_nofollow">No follow this link?:</label>
		<input id="thirsty_nofollow" name="thirsty[nofollow]" ' . $linkData['nofollow'] . ' type="checkbox" />
		<span class="thirsty_description">Adds the rel="nofollow" tag so search engines don\'t pass link juice</span></p>';
	}
	
	/* Only display link new window setting if the global new window setting is disabled */
	if ($thirstyOptions['newwindow'] != 'on') {
		echo '<p><label class="infolabel" for="thirsty_newwindow">Open this link in new window?</label>
		<input id="thirsty_newwindow" name="thirsty[newwindow]" ' . $linkData['newwindow'] . ' type="checkbox" />
		<span class="thirsty_description">Opens links in a new window when clicked on</span></p>';
	}
}

/******************************************************************************* 
** thirstyLinkImagesMeta
** Link image control meta box
** @since 1.0
*******************************************************************************/
function thirstyLinkImagesMeta() {
	wp_nonce_field( plugin_basename(__FILE__), 'thirstyaffiliates_noncename' );
	
	global $post;
	$thirstyOptions = get_option('thirstyOptions');
	$legacyUploader = (isset($thirstyOptions['autolinkbbpress']) && $thirstyOptions['autolinkbbpress'] == 'on') ? true : false;
	
	if (function_exists('wp_enqueue_media') && !$legacyUploader) {
		// New media uploader
		echo '<div id="thirsty_upload_media_manager" data-editor="content" data-uploader-title="Add Image To Affiliate Link" data-uploader-button-text="Add To Affiliate Link" class="button-secondary">Upload/Insert&nbsp;&nbsp;<img id="thirsty_add_images" src="' . plugins_url('thirstyaffiliates/') . 'images/media-button.png" alt="Upload/Insert images" /></div>';
	} else {
		// Legacy thickbox uploader
		echo '<div id="thirsty_upload_insert_img" class="button-secondary">Upload/Insert&nbsp;&nbsp;<a class="thickbox" href="' . trailingslashit(get_bloginfo('url')) . 
		'wp-admin/media-upload.php?post_id=' . $post->ID . '?type=image&TB_iframe=1"><img id="thirsty_add_images" src="' . plugins_url('thirstyaffiliates/') . 'images/media-button.png" alt="Upload/Insert images" /></a></div>';
	}
	
	
	echo '<div id="content">&nbsp;</div>
	<script type="text/javascript">';
	
	global $wp_version;
	if ($wp_version >= 3.3) {
		// JMK: WP 3.3+ fix for insert post bug
		echo 'var wpActiveEditor = \'content\';';
	} else {
		// JMK: Pre WP 3.3 fix for insert post bug
		echo 'edCanvas = document.getElementById("content");';
	}
		
	echo '</script>';
	
	$attachment_args = array(
		'post_type' => 'attachment',
		'posts_per_page' => -1,
		'post_status' => null,
		'post_parent' => $post->ID,
		'orderby' => 'menu_order',
		'order' => 'ASC'
	);
	
	$attachments = get_posts($attachment_args);
	
	if ($attachments) {
		echo '<div id="thirsty_image_holder">';
		foreach ($attachments as $attachment) {
			$img = wp_get_attachment_image_src($attachment->ID, 'full');
			echo '<div class="thirstyImgHolder"><span class="thirstyRemoveImg" title="Remove This Image" id="' . $attachment->ID . '"></span><a class="thirstyImg thickbox" href="' . $img[0] . '" rel="gallery-linkimgs" title="' . $attachment->post_title . '">';
			echo wp_get_attachment_image($attachment->ID, array(100, 100));
			echo '</a></div>';
		}
		echo '</div>';
	}
}

/******************************************************************************* 
** thirstySavePost
** Save the link post data into the post's meta
** @since 1.0
*******************************************************************************/
function thirstySavePost($post_id) {
	
	/* Make sure we only do this for thirstylinks on regular saves and we have permission */
	if (empty($_POST['post_type']) || $_POST['post_type'] != 'thirstylink') {
		return $post_id;
	}
	
	if (!wp_verify_nonce( $_POST['thirstyaffiliates_noncename'], plugin_basename(__FILE__) ) ||
		(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) ||
		!current_user_can( 'edit_page', $post_id ) ) {
		return $post_id;
	}
	
	/* Get ThirstyAffiliates settings */
	$thirstyOptions = get_option('thirstyOptions');
	
	/* Get link data from post array */	
	$linkDataNew = array();
	$linkDataNew = $_POST['thirsty'];

	/* Set the link data to be the new link data */
	$linkData = thirstyFilterData($linkDataNew);
	
	/* Because we trick wordpress into setting the post title by using our field
	** name as post_title we need to make sure our meta data is updated to reflect
	** that correct name.
	** New in 2.1.2: also need to stripslashes here so we can handle quotes below */
	$linkData['linkname'] = stripslashes($_POST['post_title']);
	
	/* Manually handle curly brackets { } and quotes */
	$linkData['linkurl'] = str_replace('{', '%7B', $linkData['linkurl']);
	$linkData['linkurl'] = str_replace('}', '%7D', $linkData['linkurl']);
	$linkData['linkname'] = str_replace("'", '', $linkData['linkname']);
	$linkData['linkname'] = str_replace('"', '', $linkData['linkname']);
	
	/* If we are using categories in slugs force user to select a category */
	if (!empty($thirstyOptions['showcatinslug']) && $thirstyOptions['showcatinslug'] == 'on') {
		$selectedLinkCats = wp_get_post_terms($post_id, 'thirstylink-category');
		
		if(empty($selectedLinkCats)) {
			$defaultCat = 'Uncategorized';
			
			// create the default term if it doesn't exist
			if (!term_exists($defaultCat, 'thirstylink-category')) {
				wp_insert_term($defaultCat, 'thirstylink-category');
			}
			
			// get the default term and set this post to have it
			$defaultTerm = get_term_by('name', $defaultCat, 'thirstylink-category');
			wp_set_post_terms($post_id, $defaultTerm->term_id, 'thirstylink-category');
		}
	}
	
	/* Update the link data */
	update_post_meta($post_id, 'thirstyData', serialize($linkData));
	
	if (isset($linkData['linkslug']) && !empty($linkData['linkslug'])) {
		$_POST['post_name'] = $linkData['linkslug'];
	}
	
	$_POST['post_status'] = 'publish';
}

/******************************************************************************* 
** thirstyFilterData
** Filter all the data for nasty surprises in the input forms
** @since 2.1.2
*******************************************************************************/
function thirstyFilterData($data) {
	if (is_array($data)) {
		foreach ($data as $elem) {
			thirstyFilterData($elem);
		}
	} else {
		$data = trim(htmlentities(strip_tags($data)));
		if (get_magic_quotes_gpc())
			$data = stripslashes($data);
		
		$data = mysql_real_escape_string($data);
	}
	
    return $data;
}

/******************************************************************************* 
** thirstyDraftToPublish
** Don't let user save drafts, make them go straight to published
** @since 1.0
*******************************************************************************/
function thirstyDraftToPublish($post_id) {
	$update_status_post = array();
	$update_status_post['ID'] = $post_id;
	$update_status_post['post_status'] = 'publish';
	
	// Update the post into the database
	wp_update_post($update_status_post);
}

/******************************************************************************* 
** thirstyEditorButtons
** Add the tinyMCE button
** @since 1.0
*******************************************************************************/
function thirstyEditorButtons() {
	if (!current_user_can('edit_posts') && !current_user_can('edit_pages'))
		return;
	
	if (get_user_option('rich_editing') == 'true') {
		add_filter('mce_external_plugins', 'thirstyMCEButton');
		add_filter('mce_buttons', 'thirstyRegisterMCEButton', 5);
	}
}

/******************************************************************************* 
** thirstyMCEButton
** Add the tinyMCE button
** @return array - array of plugins for tinyMCE with ThirstyAffiliates plugin
** @since 1.0
*******************************************************************************/
function thirstyMCEButton($plugin_array) {
	$plugin_array['thirstyaffiliates'] = plugins_url('thirstyaffiliates/thirstymce/editor_plugin.js');   
	return $plugin_array;
}

/******************************************************************************* 
** thirstyRegisterMCEButton
** Register the tinyMCE button
** @return array - buttons array with thirstyaffiliate button included
** @since 1.0
*******************************************************************************/
function thirstyRegisterMCEButton($buttons) {
	array_push($buttons, 'separator', 'thirstyaffiliates_button');
	return $buttons;
}

/******************************************************************************* 
** thirstyRedirectUrl
** Handle redirects to thirstylink link urls
** @since 1.0
*******************************************************************************/
function thirstyRedirectUrl() {
	global $post;
	
	if (get_post_type($post) == 'thirstylink') {
		// Get link data and set the redirect url
		$linkData = unserialize(get_post_meta($post->ID, 'thirstyData', true));
		$redirectUrl = $linkData['linkurl'];
		
		// Apply any filters to the url before redirecting
		$redirectUrl = apply_filters('thirstyFilterRedirectUrl', $redirectUrl);
		
		// Perform any actions before redirecting
		do_action('thirstyBeforeLinkRedirect');
		
		// Redirect the page
		if (!empty($redirectUrl))
			wp_redirect($redirectUrl, 301);
		exit();
	}
}

/******************************************************************************* 
** thirstyAdminHeader
** Add some javascript/css to the admin header that is required later
** @since 1.0
*******************************************************************************/
function thirstyAdminHeader() {
	global $post;
	$thirstyOptions = get_option('thirstyOptions');
	$legacyUploader = (isset($thirstyOptions['autolinkbbpress']) && $thirstyOptions['autolinkbbpress'] == 'on') ? true : false;
	
	$thirstyJSEnable = 'false';
	
	if (!empty($post->post_type) && $post->post_type == 'thirstylink') {
		$thirstyJSEnable = 'true';
	}
	
	echo "\n<!-- ThirstyAffiliates -->\n" .
	'<script type="text/javascript">' . "\n" . 
	'	var thirstyAjaxLink = "' . admin_url('admin-ajax.php') . '";' . "\n" .
	'	var thirstyPluginDir = "' . plugins_url('thirstyaffiliates/') . '";' . "\n" .
	'	var thirstyJSEnable = ' . $thirstyJSEnable . ';' . "\n" .
	"</script>\n\n";

	// always queue thickbox
	wp_enqueue_script('thickbox', true);
	wp_enqueue_style('thickbox');
	if (function_exists('wp_enqueue_media') && !$legacyUploader) {
		wp_enqueue_media();
	} else {
		wp_enqueue_script('media');
	}
	
	if (!empty($post->post_type) && $post->post_type == 'thirstylink') {
		wp_enqueue_style( 'thirstyStylesheet', plugins_url('thirstyaffiliates/css/thirstystyle.css'));
		
		wp_dequeue_script('jquery-ui-sortable');
		wp_dequeue_script('admin-scripts');
		wp_enqueue_script(
			'thirstyhelper', 
			plugins_url('thirstyaffiliates/js/thirstyhelper.js'), 
			array('jquery')
		);
	}
	
	thirstyQuicktags();
}

/******************************************************************************* 
** thirstyHeader
** Add some javascript/css front end header that is required later
** @since 2.2
*******************************************************************************/
function thirstyHeader() {
	// Reserved for future use
}

/******************************************************************************* 
** thirstyAddSettingsLinkToPluginPage
** Add a settings link to the plugin on the plugins page
** @since 1.0
*******************************************************************************/
function thirstyAddSettingsLinkToPluginPage($links, $file) {
	if ($file == plugin_basename(__FILE__)){
		$settings_link = '<a href="edit.php?post_type=thirstylink&page=thirsty-settings">' . __('Settings', 'thirstyaffiliates') . '</a>';
		array_unshift($links, $settings_link);
	}
	return $links;
}

/******************************************************************************* 
** thirstyTrimSlugs
** Make sure links are nice and short. This functionality was adapted from SEO 
** slugs plugin by Andrei Mikrukov.
** @since 2.1.1
*******************************************************************************/
function thirstyTrimSlugs($slug) {
    /* Don't change existing slugs */
	if ($slug)
		return $slug;

	/* Get the slug from the title */
	$shortSlug = strtolower(stripslashes($_POST['post_title']));

	/* Sanitize the slug string */
	$shortSlug = preg_replace('/&.+?;/', '', $shortSlug);
    $shortSlug = preg_replace ("/[^a-zA-Z0-9 \']/", "", $shortSlug);
    
    /* Strip common words */
    $commonWords = array("a", "able", "about", "above", "abroad", "according", "accordingly", "across", "actually", "adj", "after", "afterwards", "again", "against", "ago", "ahead", "ain't", "all", "allow", "allows", "almost", "alone", "along", "alongside", "already", "also", "although", "always", "am", "amid", "amidst", "among", "amongst", "an", "and", "another", "any", "anybody", "anyhow", "anyone", "anything", "anyway", "anyways", "anywhere", "apart", "appear", "appreciate", "appropriate", "are", "aren't", "around", "as", "a's", "aside", "ask", "asking", "associated", "at", "available", "away", "awfully", "b", "back", "backward", "backwards", "be", "became", "because", "become", "becomes", "becoming", "been", "before", "beforehand", "begin", "behind", "being", "believe", "below", "beside", "besides", "best", "better", "between", "beyond", "both", "brief", "but", "by", "c", "came", "can", "cannot", "cant", "can't", "caption", "cause", "causes", "certain", "certainly", "changes", "clearly", "c'mon", "co", "co.", "com", "come", "comes", "concerning", "consequently", "consider", "considering", "contain", "containing", "contains", "corresponding", "could", "couldn't", "course", "c's", "currently", "d", "dare", "daren't", "definitely", "described", "despite", "did", "didn't", "different", "directly", "do", "does", "doesn't", "doing", "done", "don't", "down", "downwards", "during", "e", "each", "edu", "eg", "eight", "eighty", "either", "else", "elsewhere", "end", "ending", "enough", "entirely", "especially", "et", "etc", "even", "ever", "evermore", "every", "everybody", "everyone", "everything", "everywhere", "ex", "exactly", "example", "except", "f", "fairly", "far", "farther", "few", "fewer", "fifth", "first", "five", "followed", "following", "follows", "for", "forever", "former", "formerly", "forth", "forward", "found", "four", "from", "further", "furthermore", "g", "get", "gets", "getting", "given", "gives", "go", "goes", "going", "gone", "got", "gotten", "greetings", "h", "had", "hadn't", "half", "happens", "hardly", "has", "hasn't", "have", "haven't", "having", "he", "he'd", "he'll", "hello", "help", "hence", "her", "here", "hereafter", "hereby", "herein", "here's", "hereupon", "hers", "herself", "he's", "hi", "him", "himself", "his", "hither", "hopefully", "how", "howbeit", "however", "hundred", "i", "i'd", "ie", "if", "ignored", "i'll", "i'm", "immediate", "in", "inasmuch", "inc", "inc.", "indeed", "indicate", "indicated", "indicates", "inner", "inside", "insofar", "instead", "into", "inward", "is", "isn't", "it", "it'd", "it'll", "its", "it's", "itself", "i've", "j", "just", "k", "keep", "keeps", "kept", "know", "known", "knows", "l", "last", "lately", "later", "latter", "latterly", "least", "less", "lest", "let", "let's", "like", "liked", "likely", "likewise", "little", "look", "looking", "looks", "low", "lower", "ltd", "m", "made", "mainly", "make", "makes", "many", "may", "maybe", "mayn't", "me", "mean", "meantime", "meanwhile", "merely", "might", "mightn't", "mine", "minus", "miss", "more", "moreover", "most", "mostly", "mr", "mrs", "much", "must", "mustn't", "my", "myself", "n", "name", "namely", "nd", "near", "nearly", "necessary", "need", "needn't", "needs", "neither", "never", "neverf", "neverless", "nevertheless", "new", "next", "nine", "ninety", "no", "nobody", "non", "none", "nonetheless", "noone", "no-one", "nor", "normally", "not", "nothing", "notwithstanding", "novel", "now", "nowhere", "o", "obviously", "of", "off", "often", "oh", "ok", "okay", "old", "on", "once", "one", "ones", "one's", "only", "onto", "opposite", "or", "other", "others", "otherwise", "ought", "oughtn't", "our", "ours", "ourselves", "out", "outside", "over", "overall", "own", "p", "particular", "particularly", "past", "per", "perhaps", "placed", "please", "plus", "possible", "presumably", "probably", "provided", "provides", "q", "que", "quite", "qv", "r", "rather", "rd", "re", "really", "reasonably", "recent", "recently", "regarding", "regardless", "regards", "relatively", "respectively", "right", "round", "s", "said", "same", "saw", "say", "saying", "says", "second", "secondly", "see", "seeing", "seem", "seemed", "seeming", "seems", "seen", "self", "selves", "sensible", "sent", "serious", "seriously", "seven", "several", "shall", "shan't", "she", "she'd", "she'll", "she's", "should", "shouldn't", "since", "six", "so", "some", "somebody", "someday", "somehow", "someone", "something", "sometime", "sometimes", "somewhat", "somewhere", "soon", "sorry", "specified", "specify", "specifying", "still", "sub", "such", "sup", "sure", "t", "take", "taken", "taking", "tell", "tends", "th", "than", "thank", "thanks", "thanx", "that", "that'll", "thats", "that's", "that've", "the", "their", "theirs", "them", "themselves", "then", "thence", "there", "thereafter", "thereby", "there'd", "therefore", "therein", "there'll", "there're", "theres", "there's", "thereupon", "there've", "these", "they", "they'd", "they'll", "they're", "they've", "thing", "things", "think", "third", "thirty", "this", "thorough", "thoroughly", "those", "though", "three", "through", "throughout", "thru", "thus", "till", "to", "together", "too", "took", "toward", "towards", "tried", "tries", "truly", "try", "trying", "t's", "twice", "two", "u", "un", "under", "underneath", "undoing", "unfortunately", "unless", "unlike", "unlikely", "until", "unto", "up", "upon", "upwards", "us", "use", "used", "useful", "uses", "using", "usually", "v", "value", "various", "versus", "very", "via", "viz", "vs", "w", "want", "wants", "was", "wasn't", "way", "we", "we'd", "welcome", "well", "we'll", "went", "were", "we're", "weren't", "we've", "what", "whatever", "what'll", "what's", "what've", "when", "whence", "whenever", "where", "whereafter", "whereas", "whereby", "wherein", "where's", "whereupon", "wherever", "whether", "which", "whichever", "while", "whilst", "whither", "who", "who'd", "whoever", "whole", "who'll", "whom", "whomever", "who's", "whose", "why", "will", "willing", "wish", "with", "within", "without", "wonder", "won't", "would", "wouldn't", "x", "y", "yes", "yet", "you", "you'd", "you'll", "your", "you're", "yours", "yourself", "yourselves", "you've", "z", "zero"); 
    $shortSlugArray = array_diff(preg_split("/ /", $shortSlug), $commonWords);

    /* Turn it back into a string before returning */
    $shortSlug = join("-", $shortSlugArray);
	return $shortSlug;
}

/******************************************************************************* 
** thirstyQuicktags
** Setup quicktags for adding the affiliate link button to the HTML editor
** @since 2.2
*******************************************************************************/
function thirstyQuicktags() {
	echo '<script type="text/javascript" charset="utf-8">
	jQuery(document).ready(function() {
		if (typeof QTags != "undefined")
			QTags.addButton("ThirstyAffiliates_Aff_Link", "affiliate link", thirstyQTagsButton, "", "", "Open the ThirstyAffiliates link picker", 30);
	});
	
	function thirstyQTagsButton() {
		if (typeof tinymce != "undefined") {
			thirstyOpenLinkPicker(tinymce.activeEditor);
		} else {
			thirstyOpenLinkPicker();
		}
	}
	
	function thirstyGetHTMLEditorSelection() {
		var textComponent;
		textComponent = parent.document.getElementById("replycontent");
		if (typeof textComponent == "undefined" || !jQuery(textComponent).is(":visible")) // is not a comment reply
			textComponent = parent.document.getElementById("content");
			
		var selectedText = {};
		
		// IE version
		if (parent.document.selection != undefined) {
			textComponent.focus();
			var sel = parent.document.selection.createRange();
			selectedText.text = sel.text;
			selectedText.start = sel.start;
			selectedText.end = sel.end;
		}
		
		// Mozilla version
		else if (textComponent.selectionStart != undefined) {
			var startPos = textComponent.selectionStart;
			var endPos = textComponent.selectionEnd;
			selectedText.text = textComponent.value.substring(startPos, endPos)
			selectedText.start = startPos;
			selectedText.end = endPos;
		}
		
		return selectedText;
	}
	
	</script>
	
	<style>
	.quicktags-toolbar input[value="affiliate link"] {
		text-decoration: underline;
		font-style: italic;
	}</style>';

}

/******************************************************************************* 
** thirstyUnattachImageFromLink
** Remove an image from a link, but don't delete it because it could be attached
** to something else
** @since 2.2
*******************************************************************************/
function thirstyUnattachImageFromLink() {
	$imgId = (!empty($_POST['imgId']) ? $_POST['imgId'] : '');
	
	if (empty($imgId))
		return;
	
	$img = array(
		'ID' => $imgId,
		'post_parent' => ''
	);
	
	wp_update_post($img);
	
	echo $imgId;
	die();
}

/******************************************************************************* 
** thirstyAttachImageToLink
** Attach an image to a link, make a carbon copy of the attachment object to do
** but link it to the existing image
** @since 2.2
*******************************************************************************/
function thirstyAttachImageToLink() {
	$imgId = (!empty($_POST['imgId']) ? $_POST['imgId'] : '');
	$imgName = (!empty($_POST['imgName']) ? $_POST['imgName'] : '');
	$imgMime = (!empty($_POST['imgMime']) ? $_POST['imgMime'] : '');
	$postId = (!empty($_POST['postId']) ? $_POST['postId'] : '');
	$wp_upload_dir = wp_upload_dir();
	
	if (empty($imgId) || empty($postId))
		return;
	
	$img = wp_get_attachment_metadata($imgId, true);
	$imgPost = get_post($imgId);
	
	if (!empty($imgPost->post_parent)) {
		$attachment = array(
			'guid' => trailingslashit($wp_upload_dir['basedir']) . $img['file'], 
			'post_mime_type' => $imgMime,
			'post_title' => $imgName,
			'post_content' => '',
			'post_status' => 'inherit'
		);
	  
		$attach_id = wp_insert_attachment(
			$attachment, 
			trailingslashit($wp_upload_dir['baseurl']) . $img['file'], 
			$postId
		);
		
		require_once(ABSPATH . 'wp-admin/includes/image.php');
		$attach_data = wp_generate_attachment_metadata( $attach_id, trailingslashit($wp_upload_dir['basedir']) . $img['file'] );
		wp_update_attachment_metadata( $attach_id, $attach_data );
		
		$img = wp_get_attachment_metadata($attach_id, true);
	} else {
		$imgPost->post_parent = $postId;
		wp_update_post($imgPost);
	}
	die();
}

/******************************************************************************* 
** thirstyUploadImageFromUrl
** Upload an image from a URL into the system (used for legacy Insert from URL)
** @since 2.2
*******************************************************************************/
function thirstyUploadImageFromUrl() {
	$imgUrl = (!empty($_POST['imgUrl']) ? $_POST['imgUrl'] : '');
	$postId = (!empty($_POST['postId']) ? $_POST['postId'] : '');
	
	if (empty($imgUrl) || empty($postId))
		return;
	
	$image = media_sideload_image($imgUrl, $postId, '');
	echo $image;
	die();
}

/******************************************************************************* 
** thirstyLinkPickerSearch
** Worker function for searching for an affiliate link, this is called via ajax
** @since 2.2
*******************************************************************************/
function thirstyLinkPickerSearch() {
	$search_query = mysql_escape_string((!empty($_POST['search_query']) ? $_POST['search_query'] : ''));
	$search_offset = (!empty($_POST['search_offset']) ? $_POST['search_offset'] : '');
	$cats_query = (!empty($_POST['cats_query']) ? $_POST['cats_query'] : '');
	
	global $wpdb;
	$querystr = "SELECT * FROM $wpdb->posts	WHERE post_type = 'thirstylink' AND post_status = 'publish' ";
	
	if (!empty($search_query))
		$querystr .= " AND LOWER(post_title) like '%" . strtolower($search_query) . "%' ";
	
	$querystr .= " ORDER BY post_date DESC";
	
	if (empty($search_query)) {
		$querystr .= " LIMIT 10";
		
		if (!empty($search_offset)) {
			$querystr .= " OFFSET " . $search_offset;
		}
	}
	
	$linkQuery = $wpdb->get_results($querystr, OBJECT);
	
	$thirstyOptions = get_option('thirstyOptions');		
	$nofollow = (!empty($thirstyOptions['nofollow']) ? 'nofollow="true" ' : ' ');
	$target = (!empty($thirstyOptions['newwindow']) ? 'newwindow="true" ' : ' ');
	
	if (!empty($linkQuery)) {
		$i = 0;
		foreach ($linkQuery as $link) {
			// if not a search, then only display 10 most recent
			if (empty($search_query) && $i >= 10) break;
			
			$linkData = unserialize(get_post_meta($link->ID, 'thirstyData', true));
			// Set the link's override for nofollow if applicable
			if (!empty($linkData['nofollow'])) {
				$nofollow = ($linkData['nofollow'] == 'on' ? 'nofollow="true" ' : ' ');
			}
			
			// Set the link's override for target if applicable
			if (!empty($linkData['newwindow'])) {
				$target = ($linkData['newwindow'] == 'on' ? 'newwindow="true" ' : ' ');
			}
			
			// get images
			$imageThumbsHTML = '';
			$attachment_args = array(
				'post_type' => 'attachment',
				'numberposts' => null,
				'post_status' => null,
				'post_parent' => $link->ID
			);
			
			$attachments = get_posts($attachment_args);
			$imageThumbsHTML .= '<img class="insert_img_link' . (count($attachments) > 0 ? '' : ' img_link_disabled') . '" src="' . plugins_url('thirstyaffiliates/') . 'images/icon-images' . (count($attachments) > 0 ? '' : '-disabled') . 
				'.png" alt="Insert Image Link" ' . 
				'title="Insert Image Link" /><div class="img_choices">';
				
			if (count($attachments) > 0) {
				
				foreach ($attachments as $attachment) {
					$img = wp_get_attachment_image_src($attachment->ID, 'full');
					$imageThumbsHTML .= '<p><span class="thirstyImg" linkID="' . $link->ID . '" imageId="' . $attachment->ID . '">';
					$imageThumbsHTML .= wp_get_attachment_image($attachment->ID, array(75, 75));
					$imageThumbsHTML .= '</span></p>';
				}
				
				$imageThumbsHTML .= '</div>';
			}
			
			// Output the code
			echo '<tr' . ($i % 2 == 1 ? ' class="alternate"' : '') . '><td>' . 
			'<span class="linkname">' . $link->post_title . 
			'</span>' . 
			'</td><td class="right">
			<img class="insert_link" linkID="' . $link->ID . '" src="' . plugins_url('thirstyaffiliates/') . 'images/icon-link.png" alt="Insert Plain Link" title="Insert Plain Link" />
			<img class="insert_shortcode_link" linkID="' . $link->ID . '" src="' . plugins_url('thirstyaffiliates/') . 'images/icon-shortcode.png" alt="Insert Shortcode" title="Insert Shortcode" />
			' . $imageThumbsHTML . '
			</td></tr>';
			
			$i++;
		}
	} else {
		if (!empty($search_query)) // make sure it's a search query and not just a request for more links
			echo '<tr><td>Sorry, no affiliate links found.</td></tr>';
	}
	
	die();
}

/******************************************************************************* 
** thirstyGetLinkCode
** Worker function for building the link code ready for insertion. This handles
** creating the code to insert into posts, pages, comments, etc and covers three
** link types: standard, shortcode and images.
** @since 2.2
*******************************************************************************/
function thirstyGetLinkCode($linkType = '', $linkID = '', $copiedText = '', $imageID = '', $echo = true) {
	
	if (empty($linkType))
		$linkType = (!empty($_POST['linkType']) ? $_POST['linkType'] : '');
	if (empty($linkID))
		$linkID = (!empty($_POST['linkID']) ? $_POST['linkID'] : '');
	if (empty($copiedText))
		$copiedText = (!empty($_POST['copiedText']) ? $_POST['copiedText'] : '');

	if (empty($linkID))
		return; // not a valid link, so don't bother doing any of this
	
	if (empty($linkType))
		$linkType = 'link';
	
	// Get the link and thirsty options
	$thirstyOptions = get_option('thirstyOptions');
	$link = get_post($linkID);
	$linkData = unserialize(get_post_meta($link->ID, 'thirstyData', true));
	
	if ($linkType == 'image') {
		if (empty($imageID))
			$imageID = (!empty($_POST['imageID']) ? $_POST['imageID'] : '');
		$image = get_post($imageID);
	}
	
	$nofollow = (!empty($thirstyOptions['nofollow']) ? 'nofollow' : '');
	$target = (!empty($thirstyOptions['newwindow']) ? '_blank' : '');
	$linkclass = (!empty($thirstyOptions['disablethirstylinkclass']) ? '' : 'thirstylink');
	$disabletitle = (!empty($thirstyOptions['disabletitleattribute']) ? true : false);
	
	// Set the link's nofollow if global setting is not set
	if (empty($nofollow)) 
		$nofollow = ($linkData['nofollow'] == 'on' ? 'nofollow' : '');
	
	// Set the link's target value if global setting is not set
	if (empty($target))
		$target = ($linkData['newwindow'] == 'on' ? '_blank' : '');
	
	// Check if copied text contains HTML
	$copiedTextContainsHTML = false;
	if($copiedText != strip_tags($copiedText)) {
		$copiedTextContainsHTML = true;
		$disabletitle = true;
		
		// We don't support using shortcode links or image links on top of copied 
		// text that has an image tag in it
		if (($linkType == 'shortcode' || $linkType == 'image') &&
			preg_match('/<img/', $copiedText)) {
			$output = stripslashes($copiedText);
			if ($echo)
				echo $output;
			else
				return $output;
			die();
		}
	}
	
	$linkAttributes = array(
		'href' => get_post_permalink($link->ID),
		'class' => $linkclass,
		'id' => '',
		'rel' => $nofollow,
		'target' => $target,
		'title' => ((!empty($copiedText) && !$disabletitle) ? $copiedText : (!$disabletitle ? $linkData['linkname'] : ''))
	);
	
	// filter link attributes
	$linkAttributes = apply_filters('thirstyFilterLinkAttributesBeforeInsert', $linkAttributes, $linkID);
	
	if ($linkType == 'image') {
		$imageDetails = wp_get_attachment_image_src($image->ID, 'full');
		$imageAttributes = array(
			'src' => $imageDetails[0],
			'width' => $imageDetails[1],
			'height' => $imageDetails[2],
			'alt' => (!empty($copiedText) ? strip_tags($copiedText) : $linkData['linkname']),
			'title' => ((!empty($copiedText) && !$disabletitle) ? $copiedText : (!$disabletitle ? $linkData['linkname'] : '')),
			'class' => (!empty($linkclass) ? 'thirstylinkimg' : ''),
			'id' => ''
		);
		
		// filter link image attributes
		$imageAttributes = apply_filters('thirstyFilterLinkImageAttributesBeforeInsert', $imageAttributes, $imageID, $linkID);
	}
	
	$output = '';
	switch ($linkType) {
	case 'shortcode':
		$output .= '[thirstylink linkid="' . $link->ID . '" linktext="' . $copiedText . '"';
		
		unset($linkAttributes['href']);
		unset($linkAttributes['rel']);
		unset($linkAttributes['target']);
		
		foreach ($linkAttributes as $name => $value) {
			// Handle square bracket escaping (used for some addons, eg. Google Analytics click tracking)
			$value = str_replace("[", "&#91;", $value);
			$value = str_replace("]", "&#93;", $value);
			$value = htmlentities($value);
			$output .= (!empty($value) ? ' ' . $name . '="' . $value . '"' : '');
		}
		
		$output .= ']';
		
		break;
	case 'image':
		
		$output .= '<a';
		
		foreach ($linkAttributes as $name => $value) {
			$output .= (!empty($value) ? ' ' . $name . '="' . $value . '"' : '');
		}
		
		$output .= '>';
		
		$output .= '<img';
		
		foreach ($imageAttributes as $name => $value) {
			$output .= (!empty($value) ? ' ' . $name . '="' . $value . '"' : '');
		}
		
		$output .= ' /></a>';
		
		break;
	case 'link':
	default:
		$output .= '<a';
		
		foreach ($linkAttributes as $name => $value) {
			$output .= (!empty($value) ? ' ' . $name . '="' . $value . '"' : '');
		}
		
		$output .= '>' . stripslashes($copiedText) . '</a>';
		
		break;
	}
	
	if ($echo)
		echo $output;
	else
		return $output;
	
	die();
}

function thirstyGetThickboxContent() {
	?>
	
	<html>
	<head>
	
	<?php
		wp_enqueue_script('editor');
		wp_dequeue_script('jquery-ui-sortable');
		wp_dequeue_script('admin-scripts');
		do_action('admin_print_styles');
		do_action('admin_print_scripts');
		do_action('admin_head');
	?>
	<style>
	
	body {
		font: 14px/16px sans-serif;
		background: #f5f5f5;
	}
	
	#picker_container, #picker_content {
		padding: 10px;
		overflow: hidden;
		text-align: center;
	}
	
	#picker_container h1 {
		font-size: 16px;
		font-weight: bold;
	}
	
	#picker_container table {
		width: 100%;
		text-align: left;
	}
	
	#picker_container table tr, #picker_container table tr.alternate {
		background: #e5e5e5;
		height: 40px;
		vertical-align: top;
	}
	
	#picker_container table tr.alternate {
		background: #eeeeee;
	}
	
	#picker_container table tr td {
		padding: 15px 20px 15px 20px;
		font-size: 16px;
		text-align: left;
	}
	
	#picker_container table tr td.right {
		text-align: center;
		vertical-align: middle;
		width: 75px;
		padding-right: 10px;
	}
	
	#picker_container #heading_title {
		margin: 10px auto 0px auto;
	}
	
	#picker_container #search_box {
		height: 30px;
		margin-top: 20px;
	}
	
	#picker_container #search_box label {
		color: #202020;
		padding: 4px;
		margin-right: 5px;
	}
	
	#picker_container #search_input {
		width: 185px;
	}
	
	#picker_container .linkname {
		font-weight: normal;
		font-size: 16px;
		color: #21759b;
	}
	
	#picker_container div.linkcats {
		margin-top: 5px;
	}
	
	#picker_container .linkcat {
		font-size: 10px;
		background: #e1e1e1;
		padding: 3px;
		margin-right: 3px;
		color: #808080;
		white-space: nowrap;
	}
	
	#picker_container .insert_link, 
	#picker_container .insert_img_link, 
	#picker_container .insert_shortcode_link {
		white-space: nowrap;
		float: left;
		margin: 0; 
		padding: 0;
		text-decoration: underline;
		cursor: pointer;
		vertical-align: middle;
		margin-right: 5px;
	}
	
	#picker_container .img_link_disabled {
		cursor: default;
	}
	
	#picker_container .show_url, #picker_container .hide_url {
		font-size: 10px;
		color: #808080;
		text-decoration: underline;
		cursor: pointer;
		margin-left: 10px;
		white-space: nowrap;
	}
	
	#picker_container .img_choices {
		display: none;
		float: left;
		clear: both;
	}
	
	#picker_container .thirstyImg {
		cursor: pointer;
	}
	
	#picker_container #show_more {
		cursor: pointer;
		display: none;
		right: 30px;
		position: absolute;
		margin-top: 10px;
		padding-bottom: 20px;
	}
	
	#picker_container #show_more_loader {
		display: none;
		right: 150px;
		margin-top: 15px;
		position: absolute;
	}
	
	</style>
	</head>
	<body>
	<div id="picker_container">
		<img id="heading_title" src="<?php echo plugins_url('thirstyaffiliates/'); ?>images/thirstylogo.png" alt="Affiliate Link Picker" />
		
		<div id="search_box">
		<label for="search_input">Search ...</label>
		<input type="text" value="" size="35" id="search_input" name="search_input" />
		</div>
		<table id="picker_content" cellspacing="0" cellpadding="0">
			&nbsp;
		</table>
		<img id="show_more_loader" src="<?php echo plugins_url('thirstyaffiliates/'); ?>images/thirsty-loader.gif" alt="Loading ..." />&nbsp;<img id="show_more" src="<?php echo plugins_url('thirstyaffiliates/'); ?>images/search-load-more.png" alt="Load more ..." />
	</div>
	
	<?php echo '<script type="text/javascript">var thirstyPluginDir = "' . 
			plugins_url('thirstyaffiliates/') . '";
			var thirstyMCE;</script>';?>
			
	<script type="text/javascript" src="<?php echo plugins_url('thirstyaffiliates/'); ?>js/ThirstyLinkPicker.js"></script>
	</body>
	</html>
	
	<?php
	die();
}

/******************************************************************************* 
** thirstyAffiliatesActivation
** On activation add flush flag which gets removed after flushing the rules once
** @since 1.3
*******************************************************************************/
function thirstyAffiliatesActivation() {
    add_option('thirstyaffiliates_flush', 'true');
}

/******************************************************************************* 
** thirstyAffiliatesDeactivation
** On deactivation remove flush flag
** @since 1.3
*******************************************************************************/
function thirstyAffiliatesDeactivation() {
    delete_option('thirstyaffiliates_flush');
}

/******************************************************************************* 
** thirstyInit
** Initialize the plugin
** @since 1.0
*******************************************************************************/
function thirstyInit() {
	$thirstyOptions = get_option('thirstyOptions');
	
	thirstyRegisterPostType();
	
	/* Add filter to create category links */
	add_filter('post_type_link', 'thirstyCatLinks', 10, 2);
	
	/* Add filter to always show the insert into post button for thirstylinks */
	add_filter('get_media_item_args', 'thirstyForceSend');
	
	/* Add filter to automatically trim useless words out of slugs */
	add_filter('name_save_pre', 'thirstyTrimSlugs', 0);
	
	/* Add meta boxes and saving functions */
	add_action('add_meta_boxes', 'thirstySetupPostBoxes');
	add_action('save_post', 'thirstySavePost');
	add_action('draft_thirstylink', 'thirstyDraftToPublish');
	
	/* Add the shortcode */
	require_once("ThirstyShortcode.php");
		
	/* Control redirection */
	add_action('template_redirect', 'thirstyRedirectUrl', 1);
	
	if (is_admin()) {
		require_once("ThirstyAdminPage.php");
		require_once("ThirstyAddonPage.php");
		
		if ((!empty($_GET['post']) && get_post_type($_GET['post']) == 'thirstylink') || 
			(!empty($_GET['post_type']) && $_GET['post_type'] == 'thirstylink')) {
			wp_enqueue_script(
				'thirstyhelper', 
				plugins_url('thirstyaffiliates/js/thirstyhelper.js'), 
				array('jquery')
			);
		} else {
			
		}
		wp_enqueue_script(
			'thirstyPickerHelper', 
			plugins_url('thirstyaffiliates/js/thirstyPickerHelper.js'), 
			array('jquery')
		);
	}
	
	/* Register ajax calls */
	add_action('wp_ajax_thirstyLinkPickerSearch', 'thirstyLinkPickerSearch');
	add_action('wp_ajax_thirstyUploadImageFromUrl', 'thirstyUploadImageFromUrl');
	add_action('wp_ajax_thirstyAttachImageToLink', 'thirstyAttachImageToLink');
	add_action('wp_ajax_thirstyUnattachImageFromLink', 'thirstyUnattachImageFromLink');
	add_action('wp_ajax_thirstyGetLinkCode', 'thirstyGetLinkCode');
	add_action('wp_ajax_thirstyGetThickboxContent', 'thirstyGetThickboxContent');
}

register_activation_hook(__FILE__, 'thirstyAffiliatesActivation');
register_deactivation_hook(__FILE__, 'thirstyAffiliatesDeactivation');

/* Initialize the plugin */
add_action('init', 'thirstyInit');

/* Add settings link to plugin page */
add_filter('plugin_action_links', 'thirstyAddSettingsLinkToPluginPage', 10, 2 );

/* Add the tinyMCE plugin */
add_action('init', 'thirstyEditorButtons');

/* Add necessary javascript for the admin page */
add_action('admin_head', 'thirstyAdminHeader');

/* Output front end header stuff */
add_action('wp_head', 'thirstyHeader', 10);

?>
