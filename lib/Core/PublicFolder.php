<?php

// Allow the pulling of any file that is in the public folder
if (strpos($_SERVER['REQUEST_URI'], '/public') === 0 || $_SERVER['REQUEST_URI'] === '/favicon.ico' || $_SERVER['REQUEST_URI'] === '/sitemap.xml' || $_SERVER['REQUEST_URI'] === '/robots.txt' || $_SERVER['REQUEST_URI'] === '/app.appcache') {
	$file = __DIR__ . '/../../' . ltrim($_SERVER['REQUEST_URI'], '/');
	if (strpos($file, '?') !== false) {
		$file = substr($file, 0, strpos($file, '?'));
	}

	if (!file_exists($file)) {
		exit;
	}

	if (ends_with($file, '.css')) {
		header('Content-type: text/css');
	} else if (ends_with($file, '.js')) {
		header('Content-type: text/javascript');
	} else if (ends_with($file, '.csv')) {
		$fileName = explode('/', $_SERVER['REQUEST_URI']);
		$fileName = end($fileName);
		header('Content-Disposition: attachment; filename="' . $fileName . '";');
	} else {
		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		header('Content-type: ' . finfo_file($finfo, $file));
	}

	// Remove the leading '/' character to make the path relative
	readfile($file);
	// We do not need to do any routing since we delivered the file
	exit;
}