<?php

// Determine if we are using localhost (testing)
if ($_SERVER['REMOTE_ADDR'] == '127.0.0.1' || $_SERVER['REMOTE_ADDR'] == '::1') {
	// The port this is being tested on
	$port = 8090;

	// Create a constant variable for the domain as local
	define('DOMAIN', 'localhost:' . $port);

	// Create a constant boolean to let us know if we are working locally
	define('LOCAL', true);
} else {
	// Create a constant variable for the domain as local
	define('DOMAIN', 'MyAwesomeWebsite.com');

	// Create a constant boolean to let us know if we are working locally
	define('LOCAL', false);
}

// The routing web url to be used throughout the site
define('URL', 'http://' . DOMAIN);

// Allow the pulling of any file that is in the public folder
if (strpos($_SERVER['REQUEST_URI'], '/public') === 0 || $_SERVER['REQUEST_URI'] === '/favicon.ico') {
	// Remove the leading '/' character to make the path relative
	require_once(ltrim($_SERVER['REQUEST_URI'], '/'));
	
	// We do not need to do any routing since we delivered the file
	exit;
}

// The timezone that we will be working on for the site
date_default_timezone_set('UTC');

// Report all errors, warnings and standards
error_reporting(E_ALL);

// Use this function when an exception has occurred
set_exception_handler('EchoException');

// The default base exception for the entire website
class WebsiteException extends  Exception { }

// The default function to be used to handle any exceptions that are thrown
function EchoException($exception) {
	// If the endpoint is
	if (LOCAL) {
		echo nl2br($exception);
	}

	// If this exception is a website exception vs any other exception
	if ($exception instanceof WebsiteException) {
		// TODO:  Something with errors
		error_log($exception);
	} else {
		error_log($exception);
	}

	//  TODO: Print something to the screen
	exit;
}

// Load in all the default routing and views
require_once('Controllers/IController.php');
require_once('Controllers/Controller.php');
require_once('Website.php');
require_once('WebRouter.php');

// Create a new instance of the website
$website = new Website();

// Start the website
$website->Start();
