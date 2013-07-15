<?php

/*******************************************************************************
** thirstySetupAddonsMenu()
** Setup the plugin options menu
** @since 1.0
*******************************************************************************/
function thirstySetupAddonsMenu() {
	if (is_admin()) {
		add_submenu_page('edit.php?post_type=thirstylink', 'Add-ons', 'Add-ons', 'manage_options', 'thirsty-addons', 'thirstyAddonsPage');
	}
}

function thirstyAddonsPage() {
	
	if (!current_user_can('manage_options'))  {
		wp_die( __('You do not have suffifient permissions to access this page.') );
	}
	
	echo '<style>
	
	div.wrap {
		padding: 20px;
	}
	
	#thirstylogo {
		margin: 0px;
	}
	
	#thirstyaddonscontainer {
		margin-top: 20px;
	}
	
	.thirstyaddon {
		display: inline-block;
		width: 300px;
		min-height: 215px;
		margin-right: 15px;
		margin-bottom: 15px;
		padding: 5px 15px 15px 15px;
		vertical-align: top;
		border: 1px solid #cccccc;
		border-radius: 5px;
		-webkit-border-radius: 5px;
		-moz-border-radius: 5px;
	}

	.thirstyaddon h3 {
		font-size: 12pt;
	}
	
	.thirstyaddondescription {
		margin-bottom: 15px;
	}
	</style>';
	
	echo '<div class="wrap">';
	echo '<img id="thirstylogo" src="' . plugins_url('thirstyaffiliates/images/thirstylogo.png') . '" alt="ThirstyAffiliates" />';
	
	echo '<h2>Turbo Charge ThirstyAffiliates With These Add-ons</h2>';
	
	// get the products list from the RSS feed on thirstyaffiliates.com and 
	// print them into the page nicely
	$products = thirstyAddonsPageGetProducts();

	if (!empty($products)) {
		echo '<ul id="thirstyaddonscontainer">';
		
		foreach ($products as $product) {
			$productUrl = str_replace('utm_source=rss' , 'utm_source=plugin', $product['url']);
			$productUrl = str_replace('utm_medium=rss' , 'utm_medium=addonpage', $productUrl);
			
			echo '<li class="thirstyaddon">';
			echo '<h3>' . $product['title'] . '</h3>';
			echo '<div class="thirstyaddondescription">' . $product['description'] . '</div>';
			echo '<a class="button-primary" href="' . $productUrl . '" target="_blank">Learn more &rarr;</a>';
			echo '</li>';
		}
		
		echo '</ul>';
	}
	
	echo '</div>';
}

function thirstyAddonsPageGetProducts() {
	$rssXMLString = '';
	$rssUrl = 'http://thirstyaffiliates.com/rss?post_type=product';
	
	if (function_exists('curl_init')) { // cURL is installed on the server so use this preferably
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_URL, $rssUrl);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$rssXMLString = curl_exec($ch);
		curl_close($ch);
	} else { // try using file_get_contents, though this causes some issues on some servers
		$rssXMLString = file_get_contents($rssUrl, true);
	}	
	
	// DEFAULT BEHAVIOUR: if we can't get the country XML file return false
	if (empty($rssXMLString)) 
		return false;
	
	// Create XML object for transversing
	$rssXML = new SimpleXMLElement($rssXMLString);
	
	// Check against each bot we have on record
	if (!empty($rssXML)) {
		
		$products = array();
		foreach ($rssXML->channel->item as $product) {
			$title = (string)$product->title;
			$description = (string)$product->description;
			$url = (string)$product->link;
			
			$products[] = array(
				'title' => $title,
				'description' => $description,
				'url' => $url
			);
		}
		
	}
	
	// Return an array of (title, description, url) 
	return $products;
}

add_action('admin_menu', 'thirstySetupAddonsMenu', 90);
