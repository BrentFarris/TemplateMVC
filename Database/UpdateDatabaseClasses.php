<?php

// TODO:  Comment this beast

if (count($argv) < 2 || empty($argv[1]))
	die("Database name required for generation.\n");

require_once(__DIR__.'/Settings.php');
require_once(__DIR__.'/Database.php');

function Tables($name) {
	$dbName = Settings::GetSetting($name, 'database');
	
	$dbo = Database::MakeConnection($name);
	$schema = Database::MakeConnection('information_schema', 'information_schema');
	
	$template = '<?php namespace ' . $name . '; class __CLASS_NAME__ extends \DBObject { __CLASS_FIELDS__ function __construct($where="", Array $values = array()) { parent::__construct($this, $where, $values, \'' . $name . '\'); __ASSIGN_KEYS__ } __JSONIFY__ ';

	$nameIndex = strpos($template, '__CLASS_NAME__');
	$template = str_replace('__CLASS_NAME__', '', $template);
	$fieldsIndex = strpos($template, '__CLASS_FIELDS__');
	$template = str_replace('__CLASS_FIELDS__', '', $template);
	$keysIndex = strpos($template, '__ASSIGN_KEYS__');
	$template = str_replace('__ASSIGN_KEYS__', '', $template);
	$jsonifyIndex = strpos($template, '__JSONIFY__');
	$template = str_replace('__JSONIFY__', '', $template);
	
	$tables = $schema->GetArray("SELECT `TABLE_NAME` FROM `TABLES` WHERE `TABLE_TYPE`='BASE TABLE' AND `TABLE_SCHEMA`=?", array($dbName), false, true);
	
	if (count($tables)) {
		if (!file_exists($name))
			mkdir($name);
	}
	
	foreach ($tables as $table) {
		$keyFields = $dbo->GetArray('SHOW INDEX FROM ' . $table[0]);
		$keys = array();
		foreach ($keyFields as $keyField) {
			if ($keyField['Key_name'] == 'PRIMARY') {
				$keys[] = $keyField['Column_name'];
			}
		}
		
		$keysSetup = '$this->keys = array(\'' . implode("','", $keys) . '\');';
		
		$fields = $dbo->GetArray('DESCRIBE ' . $table[0]);
		$classFields = '';
		$jsonify = 'public function ToArray($sans = array(), $compress = false) { $tmp = array(';
		foreach ($fields as $field) {
			$fieldName = $field['Field'];
			
			if (!empty($classFields))
				$jsonify .= ',';
			
			$classFields .= 'protected $'  .$fieldName . '; function Get' . (str_replace(' ', '', ucwords(str_replace('_', ' ', $fieldName)))) . '() { return $this->' . $fieldName . '; } function Set' . (str_replace(' ', '', ucwords(str_replace('_', ' ', $fieldName)))) . '($val) { $this->' . $fieldName . ' = $val; } ';
			$jsonify .= "'" . $fieldName . "' => \$this->" . $fieldName;
		}
		$jsonify .= '); foreach($sans as $remove) { unset($tmp[$remove]); } if ($compress) { foreach (array_keys($tmp) as $key) { if ($tmp[$key] == null) { unset($tmp[$key]); } } } return $tmp; }';
		
		$php = substr_replace($template, $jsonify, $jsonifyIndex, 0);
		$php = substr_replace($php, $keysSetup, $keysIndex, 0);
		$php = substr_replace($php, $classFields, $fieldsIndex, 0);
		$php = substr_replace($php, $table, $nameIndex, 0);

		$fileName = $name . '/' . $table[0] . '.php';
		if (file_exists($fileName)) {
			$current  = file_get_contents($fileName);
			$lines	= explode("\n", $current);
			$lines[0] = $php;
			$current  = implode("\n", $lines);
		} else {
			$current = $php . "\n\n\n" . '}';
		}

		file_put_contents($fileName, $current);
	}
}

function MakeModule($name) {
	if (!file_exists($name))
		return;
	
	$module = '<?php require_once(__DIR__ . \'/../Database.php\'); require_once(__DIR__ . \'/../DBObject.php\'); ';
	
	$files = glob($name.'/*.{php}', GLOB_BRACE);
	foreach($files as $file) {
		if (strpos($file, 'DatabaseModule.php') !== false)
			continue;
		
		$file = str_replace($name.'/', '', $file);
		$module .= "require_once(__DIR__ . '/{$file}'); ";
	}
	
	file_put_contents($name.'/DatabaseModule.php', $module);
}

$sectionName = $argv[1];
Tables($sectionName);
MakeModule($sectionName);