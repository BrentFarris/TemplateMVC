<?php

function find_end_of($haystack, $needle) {
	$pos = strpos($haystack, $needle);

	if ($pos === false) {
		return false;
	}

	return $pos + strlen($needle);
}

function br2nl($string) {
	return preg_replace('#<br\s*?/?>#i', "\n", $string);
}

function trim_html($str) {
	$str = str_replace('<br />', '', $str);
	return $str;
}

function date_from_time($time) {
	return date('M d, Y H:i:s', $time) . ' UTC';
}

function format_time($fTime) {
	return date("g:i a", strtotime($fTime));
}

function starts_with($haystack, $needle) {
	// Search backwards starting from haystack length characters from the end
	return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== false;
}

function ends_with($haystack, $needle) {
	// Search forward starting from end minus needle length characters
	return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== false);
}

function format_date($fDate, $dateTime=false) {
	$datetime = explode(" ", $fDate);
	$fDate = explode("-", $datetime[0]);
	$date = $fDate[1].".".$fDate[2].'.'.$fDate[0];
	$time = $datetime[1];

	if ($dateTime)
		return $date." ".$time;
	else
		return $date;
}

function replace_all($str, $find, $replaceWith = '') {
	while (strpos($str, $find) !== false) {
		$str = str_replace($find, $replaceWith, $str);
	}

	return $str;
}

function clean_spaces($str) {
	$str = trim(str_replace('&nbsp;', ' ', $str));

	$str = replace_all($str, '  ', ' ');
	$str = replace_all($str, "\r");
	$str = replace_all($str, "\n \n", "\n\n");
	$str = replace_all($str, "\n\n\n", "\n\n");

	return $str;
}

function strip_new_line($str) {
	return trim(str_replace("\n", '', str_replace("\r", '', $str)));
}

function pick_first_on_request($key) {
	global $_PUT, $_DELETE;

	if (isset($_GET[$key])) {
		return $_GET[$key];
	} else if (isset($_POST[$key])) {
		return $_POST[$key];
	} else if (isset($_PUT[$key])) {
		return $_PUT[$key];
	} else if (isset($_DELETE[$key])) {
		return $_DELETE[$key];
	} else if (isset($_SESSION[$key])) {
		return $_SESSION[$key];
	} else if (isset($_COOKIE[$key])) {
		return $_COOKIE[ $key ];
	}

	return null;
}