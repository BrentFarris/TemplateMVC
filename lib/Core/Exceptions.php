<?php

// The default base exception for the entire website
class BaseException extends Exception { }

class AutoloadException extends BaseException { }

class WebsiteException extends BaseException { }

// The default function to be used to handle any exceptions that are thrown
function EchoException(Exception $exception) {
	if (LOCAL) {
		echo nl2br($exception->getMessage());
		echo '<br /><br />';
		echo nl2br($exception);
		return;
	} else if ($exception instanceof BaseException) {
		error_log($exception);
		redirect(URL . 'Error?e=' . $exception->getMessage());
	} else {
		error_log($exception);
		redirect(URL);
	}

	exit;
}