<?php

//
namespace Controller;

/**
 * Class View
 * @package View
 */
class Controller {

	/** @var String */
	private $calledClassDir = '';

	/**
	 * Used to get just the name of the called class without the namespace
	 * @var String
	 */
	protected $calledClass = '';

	/**
	 * A list of javascript variables and their values
	 * @var Array
	 */
	private $jsVars = array();

	protected function __construct($dir = __DIR__) {
		// Set the directory of the calling class
		$this->calledClassDir = $dir;

		// Get the name of the calling class
		$calledClassFull = explode("\\", get_called_class());

		// Remove the namespace and just get the class name
		$this->calledClass = end($calledClassFull);
	}

	/**
	 * Render a relative html page and use the supplied vars to inject data
	 * @param String $file The file to be loaded (Without the .html extension)
	 * @param Array $pageVars The list of variables that can be used on the page
	 */
	protected function RenderHTML($file, $pageVars) {
		// Get the contents of the specified html file
		$body = file_get_contents($this->calledClassDir . '/view/' . $file . '.html');

		// Go through and inject any of the supplied variables into the body string
		foreach ($pageVars as $key => $value) {
			// Do no allow mapping of any objects or arrays, just primitive data types
			if (!is_object($value) && !is_array($value)) {
				/* Replace any instance of <% $varname %> with the associated value */
				$body = str_replace('<% $' . $key . ' %>', $value, $body);
			}
		}

		// Replace any constants that are created by the user with their value
		foreach (get_defined_constants(true)['user'] as $key => $value) {
			// Do no allow mapping of any objects or arrays, just primitive data types
			if (!is_object($value) && !is_array($value)) {
				/* Replace any instance of <% CONST_NAME %> with the associated value */
				$body = str_replace('<% ' . $key . ' %>', $value, $body);
			}
		}

		// Return the modified version of the page body
		echo $body;
	}

	/**
	 * Create a include statement for the specified javascript file which is
	 * not passed with the trailing ".js" extension.  These files are stored
	 * in public/CLASS_NAME/js/FILE_NAME.js
	 * @param String $file The javascript FILE_NAME to be loaded
	 */
	protected function IncludeJS($file) {
		echo '<script src="' . URL . '/public/' . $this->calledClass . '/js/' . $file . '.js"></script>';
	}

	/**
	 * Create a include statement for the specified css file which is
	 * not passed with the trailing ".css" extension.  These files are stored
	 * in public/CLASS_NAME/css/FILE_NAME.css
	 * @param String $file The javascript FILE_NAME to be loaded
	 */
	protected function IncludeCSS($file) {
		if (LOCAL) {
			echo '<style type=""text/css"><!--';
			include_once($this->calledClassDir . '/../../public/' . $this->calledClass . '/css/' . $file . '.css');
			echo '--></style>';
		} else {
			echo '<link rel="stylesheet" type="text/css" href="' . URL . '/public/' . $this->calledClass . '/css/' . $file . '.css" />';
		}
	}

	/**
	 * Used to create javascript variables with a specified name and value on the page
	 * @param String $name The name that the javascript variable will have
	 * @param Mixed $value The value that the javascript variable will have
	 */
	protected function MapVarJS($name, $value) {
		// Check to see if the variable is a string variable to map it with quotes.
		if (is_string($value)) {
			// TODO:  Check for single or double quotes in string
			$value = "' . $value . '";
		} else {
			if (is_bool($value)) {
				$value = $value === true ? 'true' : 'false';
			}
		}

		// Add the variable to the list of variables to be injected
		$this->jsVars[] = 'var ' . $name . ' = ' . $value . ';';
	}

	/**
	 * Used to map a given array to javascript
	 * @param String $name The name that the javascript variable will have
	 * @param Array $values The array of values to be mapped to the given key name
	 */
	protected function MapArrayJS($name, $values) {
		// Setup the basic start of an array variable in JavaScript
		$result = 'var ' . $name . ' = [';

		// Map all of the given values into the JavaScript array
		foreach ($values as $value) {
			if (is_string($value)) {
				$result .= '"' . $value . '";';
			} else {
				$result .= $value;
			}
		}

		// End the Javascript Array
		$result .= '];';

		// Add the javascript to be injected into the page
		$this->jsVars[] = $result;
	}

	/**
	 * Get a list of the javascript variables to be dynamically created
	 * @return Array
	 */
	public function GetJSVars() {
		return $this->jsVars;
	}
}