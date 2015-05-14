<?php

namespace Controller;

/**
 * The index page of the website, if no page is specified in the parameters
 * then use this class by default
 * Class Index
 * @package View
 */
class Index extends Controller implements IController {
	public function __construct($dir = __DIR__) {
		parent::__construct($dir);
	}

	public function Title() {
		echo 'PHP Template MVC';
	}

	public function JS() {
		$this->IncludeJS('index');
	}

	public function CSS() {
		$this->IncludeCSS('index');
	}

	public function Render() {
		// A variable to be injected into the page via the get_defined_vars() method
		$studioName = 'Bearded Man Studios, Inc.';

		// Render the "body.html" page found in the relative "html" folder
		// with the variables created above
		$this->RenderHTML('body', get_defined_vars());
	}

	public function EndJS() {

	}

	public function Footer() {

	}
}