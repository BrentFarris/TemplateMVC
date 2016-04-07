<?php

class DBObject {
	protected $database = '';
	protected $qualifiedName = '';
	protected $tableName = '';
	protected $valid = false;
	protected $keys = array();
	protected $dirtyFields = array();

	/**
	 * @param DBObject $child
	 * @param $where
	 * @param array $values
	 * @param $dbName
	 */
	function __construct(DBObject $child, $where, array $values, $dbName) {
		$this->database = $dbName;

		$this->qualifiedName = get_class($child);
		$tmp = explode("\\", $this->qualifiedName);
		$this->tableName = end($tmp);
		if (empty($where))
			return;

		$db = Database::MakeConnection($this->database);

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

	protected function SetFieldDirty($field) {
		if (!array_key_exists($field, $this->dirtyFields)) {
			$this->dirtyFields[] = $field;
		}
	}

	public function Insert($ignore = false) {
		if ($this->Valid()) {
			return false;
		}

		$db = Database::MakeConnection($this->database);
		$fields = get_object_vars($this);
		$skips = array('keys', 'database', 'tableName', 'valid', 'qualifiedName', 'dirtyFields');

		$insertFields = '';
		$valueString = '';
		$values = array();
		foreach ($fields as $key => $val) {
			if (in_array($key, $skips)) {
				continue;
			}

			if (!is_null($val)) {
				if (!empty($insertFields)) {
					$insertFields .= ', ';
					$valueString .= ', ';
				}

				$insertFields .= '`' . $key . '`';
				$values[] = $val;
				$valueString .= '?';
			}
		}

		// Don't save arrays to the database
		for ($i = 0; $i < count($values); $i++) {
			if (is_array($values[$i])) {
				$values[$i] = json_encode($values[$i]);
			}
		}

		if ($ignore) {
			$success = $db->Exec("INSERT IGNORE `{$this->tableName}` ({$insertFields}) VALUES ({$valueString})", $values);
		} else {
			$success = $db->Exec("INSERT INTO `{$this->tableName}` ({$insertFields}) VALUES ({$valueString})", $values);
		}

		if (!$success) {
			return false;
		}

		$this->valid = true;
		return $db->LastInsertId();
	}

	/**
	 * @param array $changedKeys
	 */
	public function Save(array $changedKeys = array()) {
		if (!$this->Valid()) {
			$this->Insert();
		}

		// There is nothing to save
		if (empty($this->dirtyFields)) {
			return;
		}

		$db = Database::MakeConnection($this->database);
		$fields = get_object_vars($this);
		$skips = array('keys', 'database', 'tableName', 'valid', 'qualifiedName', 'dirtyFields');

		$sets = '';
		$values = array();
		foreach ($fields as $key => $val) {
			if (in_array($key, $skips)) {
				continue;
			}

			if (!in_array($key, $this->dirtyFields)) {
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

			$keys .= '`' . $key . '`=?';
			if (array_key_exists($key, $changedKeys)) {
				$values[] = $changedKeys[$key];
			} else {
				$values[] = $this->$key;
			}
		}

		// Don't save arrays to the database
		for ($i = 0; $i < count($values); $i++) {
			if (is_array($values[$i])) {
				$values[$i] = json_encode($values[$i]);
			}
		}

		$db->Exec("UPDATE `{$this->tableName}` SET {$sets} WHERE {$keys}", $values);
	}

	public function Delete() {
		if (!$this->Valid()) {
			return;
		}

		$db = Database::MakeConnection($this->database);

		$keys = '';
		foreach ($this->keys as $key) {
			if (!empty($keys))
				$keys .= ' AND ';

			$keys .= '`' . $key . '`=?';
			if (is_null($this->$key)) {
				$values[] = '';
			} else {
				$values[] = $this->$key;
			}
		}

		$db->Exec("DELETE FROM `{$this->tableName}` WHERE {$keys}", $values);
	}

	/**
	 * Gets a field value based on its string name
	 * @param string $name The name of the field from the database to select
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
		foreach ($dbRow as $key => $value) {
			if (strlen($value)) {
				if (is_numeric($this->$key)) {
					if (is_float($value)) {
						$this->$key = floatval($value);
					} else {
						$this->$key = intval($value);
					}
				} else {
					$this->$key = $value;
				}
			}
		}
	}

	public function GetFields() {
		return array_keys(get_object_vars($this));
	}

	/**
	 * @param DBObject[] $dbItems
	 * @param array $sansFields
	 * @param bool $compress
	 * @return array
	 */
	public static function AllToArray(array $dbItems, array $sansFields = array(), $compress = false) {
		$arr = array();

		foreach ($dbItems as $dbItem) {
			$arr[] = $dbItem->ToArray($sansFields, $compress);
		}

		return $arr;
	}

	/**
	 * Returns a list of rows as the class type for the row
	 * @param string $where The SELECT condition for the rows with '?'
	 * @param array $values The arguments to fill the $where '?' with
	 * @param string $dbName The name of the database to select from
	 * @return DBObject[]|null The list of objects from the database
	 * @throws Exception When this method was called from \DBObject
	 */
	public static function GetMany($where, array $values=array(), $dbName='jsRPG', $asArrays=false) {
		$class = get_called_class();
		$classStructure = explode("\\", $class);
		$table = end($classStructure);
		if ($table == 'DBObject') {
			throw new BaseException('You cannot use DBObject for GetMany, you must use a child class.');
		}

		$db = Database::MakeConnection($dbName);

		$dbObjects = array();
		$rows = $db->GetArray("SELECT * FROM `{$table}` WHERE {$where}", $values);

		if ($asArrays) {
			return $rows;
		}

		foreach ($rows as $row) {
			/** @var DBObject $dbObject */
			$dbObject = new $class;
			$dbObject->CompileFromRow($row);
			$dbObject->valid = true;

			array_push($dbObjects, $dbObject);
		}

		return count($dbObjects) > 0 ? $dbObjects : array();
	}

	/**
	 * @param DBObject[] $dbObjects
	 * @param bool $ignoreInto
	 * @param bool $updateOnDuplicate
	 * @return bool
	 */
	public static function InsertMany(array $dbObjects, $ignoreInto = false, $updateOnDuplicate = false) {
		if ($updateOnDuplicate) {
			$ignoreInto = false;
		}

		if (!count($dbObjects)) {
			return false;
		}

		$sampleObject = $dbObjects[0];
		$sampleClass = get_class($dbObjects[0]);

		$skips = array('keys', 'database', 'tableName', 'valid', 'qualifiedName', 'dirtyFields');

		$insertFields = '';
		$duplicateUpdates = '';
		$fields = get_object_vars($sampleObject);
		foreach ($fields as $key => $val) {
			if (in_array($key, $skips)) {
				continue;
			}

			if (!empty($insertFields)) {
				$insertFields .= ', ';
			}

			$insertFields .= '`' . $key . '`';

			if ($updateOnDuplicate) {
				if (!empty($duplicateUpdates)) {
					$duplicateUpdates .= ',';
				}

				$duplicateUpdates .= '`' . $key . '`=VALUES(`' . $key . '`)';
			}
		}

		$valueString = '';
		$values = array();
		foreach ($dbObjects as $dbObject) {
			if ($dbObject->Valid() && !$updateOnDuplicate) {
				continue;
			}

			if (get_class($dbObject) != $sampleClass) {
				continue;
			}

			$fields = get_object_vars($dbObject);

			if (!empty($valueString)) {
				$valueString .= ', ';
			}

			$valueString .= '(';
			$first = true;
			foreach ($fields as $key => $val) {
				if (in_array($key, $skips)) {
					continue;
				}

				if (!$first) {
					$valueString .= ', ';
				} else {
					$first = false;
				}

				$values[] = $val;
				$valueString .= '?';
			}

			$valueString .= ')';

			$dbObject->valid = true;
		}

		$db = Database::MakeConnection($sampleObject->database);

		if (!$updateOnDuplicate) {
			if (!$ignoreInto) {
				$success = $db->Exec("INSERT INTO `{$sampleObject->tableName}` ({$insertFields}) VALUES {$valueString}", $values);
			} else {
				$success = $db->Exec("INSERT IGNORE `{$sampleObject->tableName}` ({$insertFields}) VALUES {$valueString}", $values);
			}
		} else {
			$success = $db->Exec("INSERT INTO `{$sampleObject->tableName}` ({$insertFields}) VALUES {$valueString} ON DUPLICATE KEY UPDATE {$duplicateUpdates}", $values);
		}

		if (!$success) {
			return false;
		}

		return true;
	}
}