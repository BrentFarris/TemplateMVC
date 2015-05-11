<?php

namespace View;

/**
 * The default interface for all views
 * Interface IView
 * @package View
 */
interface IView {

	// Get a list of javascript variables
	function GetJSVars();

	// Used to set the title of the page
	function Title();

	// Used to load in any javascript files into the header
	function JS();

	// Used to load in any css files into the header
	function CSS();

	// Used to render the body of the page
	function Render();

	// Used to set any custom footers for the page
	function Footer();

	// Used to load in any javascript files at the end of the page
	function EndJS();
}