<?php

class DBObject {
	protected $sectionName = '';
	protected $database = '';
	protected $qualifiedName = '';
	protected $tableName = '';
	protected $valid = false;
	protected $keys = array();

	/**
	 * @param DBObject $child
	 * @param $where
	 * @param array $values
	 * @param $dbName
	 */
	function __construct(DBObject $child, $where, Array $values, $dbName) {
		$this->sectionName = $dbName;
		$this->database = $dbName;
		
		$this->qualifiedName = get_class($child);
		$tmp = explode("\\", $this->qualifiedName);
		$this->tableName = end($tmp);
		if (empty($where))
			return;
		
		$db = Database::MakeConnection($this->sectionName);
		
		$data = $db->GetArray("SELECT * FROM `{$this->tableName}` WHERE {$where}", $values, true);
		
		if (empty($data)) {
			$this->valid = false;
			return;
		}
		
		foreach ($data as $key => $value)
			$child->$key = $value;

		$this->valid = true;
	}

	/**
	 * @return bool
	 */
	function Valid() { return $this->valid; }

	/**
	 * @param array $changedKeys
	 */
	public function Save(Array $changedKeys = array()) {
		$db = Database::MakeConnection($this->sectionName);
		
		$fields = get_object_vars($this);
		
		$skips = array('keys', 'database', 'tableName', 'valid', 'qualifiedName');
		
		$sets = '';
		$values = array();
		foreach ($fields as $key=>$val) {
			if (in_array($key, $skips)) {
				continue;
			}

			if (!empty($sets)) {
				$sets .= ', ';
			}

			$sets .= '`' . $key . '`=?';
			$values[] = $val;
		}
		
		$keys = '';
		foreach ($this->keys as $key) {
			if (!empty($keys))
				$keys .= ' AND ';

			$keys .= '`'.$key.'`=?';
			if (array_key_exists($key, $changedKeys)) {
				$values[] = $changedKeys[$key];
			} else {
				$values[] = $this->$key;
			}
		}
		
		$db->Exec("UPDATE `{$this->tableName}` SET {$sets} WHERE {$keys}", $values);
	}

	/**
	 * Gets a field value based on its string name
	 * @param String $name The name of the field from the database to select
	 * @return mixed The value of the requested field
	 */
	public function GetByName($name) {
		$name = str_replace(' ', '', ucfirst(str_replace('_', ' ', $name)));
		return $this->$name;
	}

	/**
	 * @param $data
	 */
	public function MapData($data) {
		if (is_array($data)) {
			foreach ($data as $key => $value) {
				if (property_exists($this->qualifiedName, $key)) {
					$this->$key = $value;
					$this->valid = true;
				}
			}
		}
	}

	/**
	 * @param $name
	 * @return null
	 */
	public function GetPropertyByName($name) {
		if (property_exists($this->qualifiedName, $name)) {
			return $this->$name;
		}
		
		return null;
	}

	/**
	 * @param $dbRow
	 */
	function CompileFromRow($dbRow) {
		foreach ($dbRow as $key => $value)
			if (!empty($value))
				$this->$key = $value;
	}

	/**
	 * Returns a list of rows as the class type for the row
	 * @param String $where The SELECT condition for the rows with '?'
	 * @param array $values The arguments to fill the $where '?' with
	 * @param bool $asArrays
	 * @param string $sectionName The name of the database to select from
	 * @return Array|Mixed|null
	 * @throws ForgeException When this method was called from \DBObject
	 */
	static function GetMany($where, Array $values=array(), $asArrays=false, $sectionName='DatabaseName') {
		$class = get_called_class();
		$classStructure = explode("\\", $class);
		$table = end($classStructure);
		if ($table == 'DBObject') {
			throw new ForgeException('You cannot use DBObject for GetMany, you must use a child class.');
		}
		
		$db = Database::MakeConnection($sectionName);
		
		$dbObjects = array();
		$rows = $db->GetArray("SELECT * FROM `{$table}` WHERE {$where}", $values);

		if ($asArrays) {
			return $rows;
		}

		foreach ($rows as $row) {
			$dbObject = new $class;
			$dbObject->CompileFromRow($row);
			array_push($dbObjects, $dbObject);
		}
		
		return count($dbObjects) > 0 ? $dbObjects : null;
	}
}
