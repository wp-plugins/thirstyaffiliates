<?php
/*******************************************************************************
** thirstyLinkByShortcode
** Allows user to specify a shortcode in the page/post to include a thirstylink
*******************************************************************************/
function thirstyLinkByShortcode($atts) {
    extract($atts);
    
    $output = '';
    
    // Sanity check, if the linkid attribute is empty we can't retrieve the link
    if (!empty($linkid)) {
    	// Remove linkid, linktext and linkclass to get final link attributes
    	$linkAttributes = array_diff_assoc($atts, array('linkid' => $linkid, 'linktext' => $linktext, 'linkclass' => $linkclass));
    	
    	// Backwards compatibility for linkclass shortcode attribute, should add this to the "class" link attribute
    	if (!empty($linkclass))
    		$linkAttributes['class'] = $linkAttributes['class'] . ' ' . $linkclass;
    	
    	// Retrieving via the link ID
    	if (is_numeric($linkid)) {
    		
    		// Get the link and global options
			$thirstyOptions = get_option('thirstyOptions');
			$link = get_post($linkid);
			$linkData = unserialize(get_post_meta($link->ID, 'thirstyData', true));
			
			// Get the link URL
    		$linkAttributes['href'] = get_post_permalink($linkid);
    		
    		// If the link text is empty, use the link name instead
    		if (empty($linktext)) {
    			$linktext = $link->post_title;
    		}
    		
    		// Check for no follow defaults if not specified in the shortcode attributes
    		if (empty($linkAttributes['rel'])) {
    			$linkAttributes['rel'] = (!empty($thirstyOptions['nofollow']) ? 'nofollow' : '');
    			
    			// Set the link's nofollow if global setting is not set
    			if (empty($linkAttributes['rel'])) 
    				$linkAttributes['rel'] = ($linkData['nofollow'] == 'on' ? 'nofollow' : '');
    		}
    			
    		// Check for no follow defaults if not specified in the shortcode attributes
    		if (empty($linkAttributes['target'])) {
    			$linkAttributes['target'] = (!empty($thirstyOptions['newwindow']) ? '_blank' : '');
    			
    			// Set the link's target value if global setting is not set
				if (empty($linkAttributes['target']))
					$linkAttributes['target'] = ($linkData['newwindow'] == 'on' ? '_blank' : '');
    		}
    		
    		// Provide a default value for link class when attribute is not given in shortcode
    		if (empty($linkAttributes['class'])) {
    			$linkAttributes['class'] = 'thirstylink';
    		}
    		
    		// Disable class output if global option set
    		if (!empty($thirstyOptions['disablethirstylinkclass']))
    			unset($linkAttributes['class']);
    		
    		// Provide a default value for the title attribute when attribute is not given in shortcode
    		if (empty($linkAttributes['title'])) {
    			$linkAttributes['title'] = $link->post_title;
    		}
    		
    		// Disable title attribute if global option set
    		if (!empty($thirstyOptions['disabletitleattribute']))
    			unset($linkAttributes['title']);
    		
			// Build the link ready for output
    		$output .= '<a';
    		
			foreach ($linkAttributes as $name => $value) {
				// Handle square bracket escaping (used for some addons, eg. Google Analytics click tracking)
				$value = html_entity_decode($value);
				$value = preg_replace('/&#91;/', '[', $value);
				$value = preg_replace('/&#93;/', ']', $value);
				$output .= (!empty($value) ? ' ' . $name . '="' . $value . '"' : '');
			}
			
			$output .= '>' . $linktext . '</a>';
		
    	} else {
    		$output .= '<span style="color: #0000ff;">SHORTCODE ERROR: ThirstyAffiliates did not detect a valid link id, please check your short code!</span>';
    	}
    	
    }
    
    return $output;
}

// Add a shortcode for thirsty affiliate links
add_shortcode('thirstylink', 'thirstyLinkByShortcode', 1); 
?>
