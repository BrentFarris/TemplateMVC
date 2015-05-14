<?php

class WebRouter {
	/** @var Array */
	private $requestPath = array();	// The URL that was requested
	/** @var String */
	private $controllerName;				// The version of the API to use

	public function __construct() {
		// Check to see if an actual page (other than home) was requested
		if (trim($this->GetRequestPathString()) != '/') {
			/*  Separate the path by '/' for routing
				so /about/sparta would be come ['about', 'sparta'] array
			*/
			$this->requestPath = array_slice(explode('/', $this->GetRequestPathString()), 1);
		}
	}

	/**
	 * @return Array
	 */
	public function GetRequestPath() {
		return $this->requestPath;
	}

	/**
	 * @return Int
	 */
	public function GetControllerName() {
		return $this->controllerName;
	}

	/**
	 * @return String
	 */
	public function GetParsedPath() {
		return dirname(__FILE__) . '/Controllers/' . $this->controllerName . '/' . $this->controllerName . '.php';
	}

	/**
	 * Get the requested URI string
	 * @return String
	 */
	public function GetRequestPathString() {
		return $_SERVER['REQUEST_URI'];
	}

	/**
	 * Setup the current routing information by the requested URL
	 * @throws \WebsiteException When the requested path is not found
	 */
	public function Route() {
		// Get the number of request params found
		$count = count($this->requestPath);

		// If there are no input parameters then the index will be laoded
		if ($count == 0) {
			$this->controllerName = 'Index';
		} else {
			// Otherwise route to the first request path found
			$this->controllerName = $this->requestPath[0];
		}

		// If the specified controller name is not found as a file then throw an exception
		if (!file_exists($this->GetParsedPath())) {
			throw new \WebsiteException('The requested controller (' . $this->controllerName . ') is missing', 800);
		}
	}

	/**
	 * Execute the routing and get the data that is to be rendered to
	 * the end user based on their request parameters
	 */
	public function Execute() {
		// Load up the requested path class file
		require_once($this->GetParsedPath());

		// Create a string name for the class to be created
		$controller = "\\Controller\\" . $this->controllerName;

		// Dynamically create the class from the string name
		$handle = new $controller;

		// Include the website headers
		include_once(__DIR__ . '/Templates/head.html');

		// Render the title from the requested controller class
		call_user_func_array(array($handle, 'Title'), array());

		// Close the website title tag
		include_once(__DIR__ . '/Templates/endTitle.html');

		// Load in the javascript files  to the header for the requested controller class
		call_user_func_array(array($handle, 'JS'), array());

		// Load in the css files to the header for the requested controller class
		call_user_func_array(array($handle, 'CSS'), array());

		// Setup the default body for the website
		include_once(__DIR__ . '/Templates/body.html');

		// Render the main body of the website from the requested controller class
		call_user_func_array(array($handle, 'Render'), array());

		// Begin the footers for the website
		include_once(__DIR__ . '/Templates/footer.html');

		// Show any view specific footer information from the requested controller class
		call_user_func_array(array($handle, 'Footer'), array());

		// Load in any javascript to the end of the body from the requested controller class
		call_user_func_array(array($handle, 'EndJS'), array());

		// Close off all of the html view and end the render
		include_once(__DIR__ . '/Templates/end.html');
	}
}
