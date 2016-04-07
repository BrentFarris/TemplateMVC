<?php

// The timezone that we will be working on for the site
date_default_timezone_set('UTC');

// Report all errors, warnings and standards
error_reporting(E_ALL);

session_start();
require_once('lib/Core/Exceptions.php');
require_once('lib/Core/Autoloader.php');
require_once('lib/Methods/Strings.php');
require_once('lib/Core/Constants.php');
require_once('lib/Core/PublicFolder.php');
require_once('lib/Methods/Routing.php');
require_once('lib/Methods/Cryptography.php');

// Load in all the default routing and views
require_once('Website.php');
require_once('WebRouter.php');

// Use this function when an exception has occurred
set_exception_handler('EchoException');

// Create a new instance of the website
$website = new Website();

// Start the website
$website->Start();
