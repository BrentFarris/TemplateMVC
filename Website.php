<?php

/**
 * The base website class for constructing the entire web application
 * Class Website
 * @package Website
 */
class Website {
	/** @var WebRouter */
	private $router = null;

	/**
	 * Default constructor
	 * @throws \WebsiteException When the requested path is not found
	 */
	public function __construct() {
		// Create a new instance of the web router
		$this->router = new WebRouter();

		// Setup the router for the current URL
		$this->router->Route();
	}

	/**
	 * Used to begin the application and mainly route the user
	 */
	public function Start() {
		// Execute the route command to show the desired contents
		$this->router->Execute();
	}
}
