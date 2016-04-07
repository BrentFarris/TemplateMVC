<?php

define('NOW', time());
define('ENDPOINT', $_SERVER['REMOTE_ADDR']);

// Determine if we are using localhost (testing)
if (ENDPOINT == '127.0.0.1' || ENDPOINT == '::1') {
	// Create a constant variable for the domain as local
	define('DOMAIN', 'localhost:' . $_SERVER['SERVER_PORT']);

	// Create a constant boolean to let us know if we are working locally
	define('LOCAL', true);
} else {
	// Create a constant variable for the domain we are currently on
	define('DOMAIN', $_SERVER['SERVER_NAME']);

	// Create a constant boolean to let us know if we are working locally
	define('LOCAL', false);
}

// The routing web url to be used throughout the site
define('URL', 'http://' . DOMAIN . '/');
define('URL_CURRENT', URL . ltrim($_SERVER['REQUEST_URI'], '/'));

// Get, Post, Put, or Delete
define('HTTP_VERB_GET', 'GET');
define('HTTP_VERB_POST', 'POST');
define('HTTP_VERB_PUT', 'PUT');
define('HTTP_VERB_DELETE', 'DELETE');
define('HTTP_VERB', $_SERVER['REQUEST_METHOD']);

$_PUT = array();
$_DELETE = array();

if (HTTP_VERB == HTTP_VERB_POST && empty($_POST)) {
	parse_str(file_get_contents('php://input'), $_POST);
} else if (HTTP_VERB == HTTP_VERB_PUT) {
	parse_str(file_get_contents("php://input"), $_PUT);
} else if (HTTP_VERB == HTTP_VERB_DELETE) {
	parse_str(file_get_contents("php://input"), $_DELETE);
}

define('APP_NAME', 'My Application');