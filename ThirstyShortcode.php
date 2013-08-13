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
    	
    	// Remove linkid and linktext to get final link attributes
    	$linkAttributes = array_diff($atts, array('linkid' => $linkid, 'linktext' => $linktext, 'linkclass' => $linkclass));
    	
    	// Backwards compatibility for linkclass shortcode attribute, should add this to the "class" link attribute
    	if (!empty($linkclass))
    		$linkAttributes['class'] = $linkAttributes['class'] . ' ' . $linkclass;
    	
    	// Retrieving via the link ID
    	if (is_numeric($linkid)) {
    		
    		// Get the link URL
    		$linkAttributes['href'] = get_post_permalink($linkid);
    		
    		// If the link text is empty, use the link name instead
    		if (empty($linktext)) {
    			$link = get_post($linkid);
    			$linktext = $link->post_title;
    		}
    		
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
