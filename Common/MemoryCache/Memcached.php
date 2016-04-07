<?php

namespace Common\MemoryCache;

class MemoryCache {
	private static $cache = null;
	private static $mc = null;

	public static function GetInstance() {
		if (self::$cache === null) self::$cache = new MemoryCache();
		return self::$cache;
	}

	function __construct() {
		if (LOCAL) {
			self::$mc = new Memcache();
			self::$mc->addServer('dev-farris-ej01.cloudapp.net', 11211);
		} else {
			self::$mc = new Memcached();
			self::$mc->addServer('127.0.0.1', 11211);
		}
	}

	private static function Check() {
		if (self::$cache == null) { self::GetInstance(); }
	}

	public static function Get($key) {
		self::Check();
		return self::$mc->get($key);
	}

	public static function Set($key, $value, $ttl=86400) {
		self::Check();
		self::$mc->set($key, $value, $ttl);
	}

	public static function Replace($key, $value, $ttl=-1) {
		self::Check();
		if ($ttl < 0)
			self::$mc->replace($key, $value);
		else
			self::$mc->replace($key, $value, $ttl);
	}
}