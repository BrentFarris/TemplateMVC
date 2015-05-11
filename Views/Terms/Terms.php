<?php

namespace View;

/**
 * The terms page for the website, you can render this page by going
 * to your website url "/Terms"
 * Class Terms
 * @package View
 */
class Terms extends View implements IView {
	public function __construct($dir = __DIR__) {
		parent::__construct($dir);
	}

	public function Title() {
		echo 'Terms';
	}

	public function JS() {

	}

	public function CSS() {

	}

	public function Render() {
		$this->RenderHTML('terms', get_defined_vars());
	}

	public function EndJS() {

	}

	public function Footer() {

	}
}