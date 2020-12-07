<?php
/*
 * admin related functions
 */

// override home_url() function to return url according to which panel user's in
if(is_admin_panel() || is_math_panel())
{
	add_filter('home_url', 'ik_subdomain_home_url', 100, 1);
}

function ik_subdomain_home_url($path = '')
{
	$subdomain = get_subdomain();

	if(strpos($path, '//www.') !== false) {
		$path = str_replace('//www.', '//www.' . $subdomain . '.', $path);
	}
	else {
		$path = str_replace('//', '//' . $subdomain . '.', $path);
	}

	return $path;
	
	//	Get the siteURl to check against
	/* $siteurl = site_url();
	$is_https = isset($_SERVER['HTTPS']) && (strcasecmp('off', $_SERVER['HTTPS']) !== 0);
	$protocol = $is_https ? 'https' : 'http';
	$strippedURL = str_replace($protocol . '://','',$siteurl);

	return $protocol . '://' . $subdomain . '.' . $strippedURL; */
}
