<?php

function AutoLoader($className) {
	$file = __DIR__ . '/../../' . str_replace("\\", '/', $className) . '.php';

	if (file_exists($file)) {
		require_once($file);
	} else {
		throw new AutoloadException("The {$className} object could not be found");
	}
}

spl_autoload_register('AutoLoader');