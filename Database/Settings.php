<?php

/**
 * A static class for pulling values from ini files
 * Class Settings
 */
class Settings {

	/**
	 * A list of cached settings for pulling multiple values
	 * @var Array
	 */
	private static $settings = array();

	/**
	 * The default settings file name
	 * @var String
	 */
	const DEFAULT_SETTINGS_FILE = 'default';

	/**
	 * Get a particular setting from the desired section
	 * @param String $section The name of the section to pull from
	 * @param String $attribute The attribute value that is to be pulled from the section
	 * @param String $fileName The name of the file to use, otherwise use default file name
	 * @return Mixed
	 */
	public static function GetSetting($section, $attribute, $fileName = self::DEFAULT_SETTINGS_FILE) {
		// Check to see if the requested setting has been pulled in this instance before, if not then load up the ini file
		if (!array_key_exists(self::DEFAULT_SETTINGS_FILE, self::$settings)) {
			// Cache the settings from this file
			self::$settings[self::DEFAULT_SETTINGS_FILE] = parse_ini_file(__DIR__ . '/' . $fileName . '.ini', true);
		}

		// Return the particular attribute from the cached ini
		return self::$settings[self::DEFAULT_SETTINGS_FILE][$section][$attribute];
	}
}
