<?php

/*******************************************************************************
** thirstySetupAddonsMenu()
** Setup the plugin options menu
** @since 2.0
*******************************************************************************/
function thirstySetupAddonsMenu() {
	if (is_admin()) {
		add_submenu_page('edit.php?post_type=thirstylink', 'Add-ons', 'Add-ons', 'manage_options', 'thirsty-addons', 'thirstyAddonsPage');
	}
}

/*******************************************************************************
** thirstyAddonsPage()
** Create the add-ons page
** @since 2.0
*******************************************************************************/
function thirstyAddonsPage() {

	if (!current_user_can('manage_options'))  {
		wp_die( __('You do not have suffifient permissions to access this page.') );
	}

	echo '<div class="wrap">';
	echo '<img id="thirstylogo" src="' . plugins_url('thirstyaffiliates/images/thirstylogo.png') . '" alt="ThirstyAffiliates" />';

	echo '<h2>Turbo Charge ThirstyAffiliates With These Add-ons</h2>';

	// get the products list from the RSS feed on thirstyaffiliates.com and
	// print them into the page nicely
	$products = thirstyAddonsPageGetProducts();

	if (!empty($products)) {
		echo '<ul id="thirstyaddonscontainer" class="columns-2">';

		foreach ($products as $product) {
			$productUrl = str_replace('utm_source=rss' , 'utm_source=plugin', $product['url']);
			$productUrl = str_replace('utm_medium=rss' , 'utm_medium=addonpage', $productUrl);
			$productTitle = str_replace('ThirstyAffiliates ', '', $product['title']);
			$productTitle = str_replace(' Add-on', '', $productTitle);

			echo '<li class="thirstyaddon">';
			echo '<h3>' . $productTitle . '</h3>';
			echo '<div class="thirstyaddondescription">' . $product['description'] . '</div>';
			echo '<a class="button-primary" href="' . $productUrl . '" target="_blank">Visit Add-on Page &rarr;</a>';
			echo '</li>';
		}

		echo '</ul>';

		echo '<script type="text/javascript">
		jQuery(document).ready(function() {
			var addonBoxHeight = 0;
			jQuery(".thirstyaddon").each(function() {
				if (jQuery(this).height() > addonBoxHeight) {
					addonBoxHeight = jQuery(this).height();
				}
			});
			jQuery(".thirstyaddon").height(addonBoxHeight);
		});
		</script>';
	}

	echo '</div>';
}

/*******************************************************************************
** thirstyAddonsPageGetProducts()
** Get the add-ons feed
** @since 2.0
*******************************************************************************/
function thirstyAddonsPageGetProducts($forceNew = false) {
	$thirstyAddonsRSS = get_option('thirstyAddonsRSS');
	$expired = false;

	/* If the timestamp hasn't been set or if it is expired or if we're forcing
	** make sure we fetch a new feed */
	if (isset($thirstyAddonsRSS) && !empty($thirstyAddonsRSS) && !$forceNew) {

		$oneDayAgo = current_time('timestamp', 0) - (24 * 60 * 60); // current time minus 1 day

		if (!isset($thirstyAddonsRSS['timestamp']) ||
			empty($thirstyAddonsRSS['timestamp']) ||
			$oneDayAgo > $thirstyAddonsRSS['timestamp']) {

			$expired = true;
		}

	} else {
		$expired = true;
	}

	// Check if we need to get a new RSS feed
	if (!isset($thirstyAddonsRSS['products']) || empty($thirstyAddonsRSS['products']) || $expired) {

		$rssXMLString = '';
		$rssUrl = 'http://thirstyaffiliates.com/feed?post_type=product';

		if (function_exists('curl_init')) { // cURL is installed on the server so use this preferably
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_HEADER, false);
			curl_setopt($ch, CURLOPT_URL, $rssUrl);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: text/xml")); // provide a http header to please some curl setups
			curl_setopt($ch, CURLOPT_USERAGENT, $ua);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_AUTOREFERER, true);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
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

			$timestamp = current_time('timestamp', 0);
			update_option('thirstyAddonsRSS', array('products' => $products, 'timestamp' => $timestamp));
		}

	}

	// Return products array
	return $thirstyAddonsRSS['products'];
}

add_action('admin_menu', 'thirstySetupAddonsMenu', 90);
