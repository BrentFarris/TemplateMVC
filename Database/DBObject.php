<?php

/**
 * The base class for all database classes (Tables)
 * Class DBObject
 */
class DBObject {

	/**
	 * The section name used to setup this database table object
	 * @var String
	 */
	protected $sectionName = '';

	/**
	 * The database that this table object belongs to
	 * @var String
	 */
	protected $database = '';

	/**
	 * The fully qualified class name, including it's namespace
	 * @var String
	 */
	protected $qualifiedName = '';

	/**
	 * The table name without the namespace (Class Name)
	 * @var String
	 */
	protected $tableName = '';

	/**
	 * If this created instance is a valid object, meaning it actually pulled
	 * data from the database (was found)
	 * @var Bool
	 */
	protected $valid = false;

	/**
	 * The primary keys for this table
	 * @var Array
	 */
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

		// If this is just a blank object then return (nothing to look up in database)
		if (empty($where)) {
			return;
		}
		
		$db = Database::MakeConnection($this->sectionName);
		
		$data = $db->GetArray("SELECT * FROM `{$this->tableName}` WHERE {$where}", $values, true);

		// Determine if anything was pulled based on the query created for this object
		// If not, it is not a "Valid()" database object
		if (empty($data)) {
			$this->valid = false;
			return;
		}

		// Set up all the fields in the child class to have the value specified in the database
		foreach ($data as $key => $value) {
			$child->$key = $value;
		}

		// This is a valid object from the database
		$this->valid = true;
	}

	/**
	 * If this is a valid object from the database, otherwise it is a new object and this returns false
	 * @return Bool
	 */
	function Valid() { return $this->valid; }

	// TODO:  Allow saving to insert as well
	/**
	 * Save this object using the Update command
	 * @param Array $changedKeys
	 */
	public function Save(Array $changedKeys = array()) {
		$db = Database::MakeConnection($this->sectionName);

		// Get all of the fields for this object
		$fields = get_object_vars($this);

		// Fields to be ignored in saving this object
		$skips = array('keys', 'database', 'tableName', 'valid', 'qualifiedName');

		// Go through all of the fileds and assign the values for them for the query
		$sets = '';
		$values = array();
		foreach ($fields as $key=>$val) {
			// Don't do anything with the skipped fields
			if (in_array($key, $skips)) {
				continue;
			}

			if (!empty($sets)) {
				$sets .= ', ';
			}

			$sets .= '`' . $key . '`=?';
			$values[] = $val;
		}

		// Go through all the keys and make sure to only overwrite the matching object
		$keys = '';
		foreach ($this->keys as $key) {
			if (!empty($keys))
				$keys .= ' AND ';

			$keys .= '`'.$key.'`=?';

			// If any keys are changing, then use the previous key value and not the current value
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
	 * Map data to this object based off of a hashtable
	 * @param Array $data
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
	 * Get a property by it's name from this object
	 * @param String $name The name of the property
	 * @return Mixed|null
	 */
	public function GetPropertyByName($name) {
		if (property_exists($this->qualifiedName, $name)) {
			return $this->$name;
		}
		
		return null;
	}

	/**
	 * Create this object with a database row of data
	 * @param $dbRow
	 */
	public function CompileFromRow($dbRow) {
		foreach ($dbRow as $key => $value) {
			if (!empty($value)) {
				$this->$key = $value;
			}
		}
	}

	/**
	 * Returns a list of rows as the class type for the row
	 * @param String $where The SELECT condition for the rows with '?'
	 * @param array $values The arguments to fill the $where '?' with
	 * @param bool $asArrays Returns a numbered array rather than a hashtable
	 * @param string $sectionName The name of the database to select from
	 * @return Array|Mixed|null
	 * @throws \WebsiteException When this method was called from \DBObject
	 */
	public static function GetMany($where, Array $values=array(), $asArrays=false, $sectionName='DatabaseName') {
		// Get the fully qualified class name (including namespace)
		$class = get_called_class();

		// Find just the class name without the namespace
		$classStructure = explode("\\", $class);
		$table = end($classStructure);

		// Do not allow pulling of a table named "DBObject" as that would change the behavior of this object
		if ($table == 'DBObject') {
			throw new WebsiteException('You cannot use DBObject for GetMany, you must use a child class.');
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
