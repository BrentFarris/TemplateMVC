<?php

class Database {
	/** @var String */
	private $host;
	/** @var String */
	private $username;
	/** @var String */
	private $password;
	/** @var String */
	private $database;
	/** @var PDO */
	private $handle = null;
	/** @var Bool */
	private $connected;
	/** @var Bool */
	private $inTransaction;
	/** @var array Array */
	private static $connections = array();

	/**
	 * Get the current cached Database object for the specified database name or make a new connection
	 * using the settings ini then cache it for later use
	 * @param String $dbName The name of the attribute in the settings ini (database name) to pull information from
	 * @param Bool $connectNow Execute the connection on construction or wait for manual connection
	 * @return Database|null The database that was either cached or just created
	 */
	public static function MakeConnection($section, $connectNow=true) {
		$db = null;
		if (!array_key_exists($section, self::$connections)) {
			$host = Settings::GetSetting($section, 'host');
			$username = Settings::GetSetting($section, 'username');
			$password = Settings::GetSetting($section, 'password');
			$database = Settings::GetSetting($section, 'database');
			
			$db = new Database($host, $username, $password, $database, true);
			self::$connections[$section] = $db;
			return $db;
		} else
			$db = self::$connections[$section];
		
		if ($connectNow)
			$db->Connect();
		
		return $db;
	}

	/**
	 * Creates an instance of the Database class that is used to connect to a MySQL database using login credentials
	 * @param String $host The host address for the database
	 * @param String $user The username to be used to log into the database
	 * @param String $pass The password to be used to log into the database
	 * @param String $dbName The database name to connect to once a connection is established
	 * @param Bool $connectNow Execute the connection on construction or wait for manual connection
	 */
	private function __construct($host, $user, $pass, $dbName="", $connectNow=false) {
		$this->host = $host;
		$this->username = $user;
		$this->password = $pass;
		
		if ($dbName == '')
			$this->database = $user;
		else
			$this->database = $dbName;
		
		if ($connectNow)
			$this->Connect();
	}

	/**
	 * Make a connection to the database
	 */
	public function Connect() {
		if ($this->connected)
			return;
		
		$this->handle = new PDO('mysql:host='.$this->host.';dbname='.$this->database.';charset=utf8', $this->username, $this->password);
		$this->handle->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$this->handle->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		
		$this->connected = true;
	}

	/**
	 * Get the last inserted id
	 * @return Int
	 */
	public function LastInsertId() {
		return $this->handle->lastInsertId();
	}

	/**
	 * Gets the current handle for this database
	 * @return PDO The current handle for this database
	 */
	public function GetHandle() { return $this->handle; }

	/**
	 * Begins a transaction with the database
	 */
	public function Transaction() {
		$this->Connect();
		$this->handle->beginTransaction();
		$this->inTransaction = true;
	}

	/**
	 * Commits and closes the pending transaction
	 */
	public function Commit() {
		if ($this->inTransaction)
			$this->handle->commit();
		
		$this->inTransaction = false;
	}

	/**
	 * @param String $query The MySQL query to execute
	 * @param Array $values The values to be populated into the queries '?'
	 * @return Bool If the execute was successful
	 */
	public function Exec($query, Array $values = array()) {
		$this->Connect();
		
		return $this->handle->prepare($query)->execute($values);
		
		// Returns the number of affected rows
		//return $this->handle->exec($query);
	}

	/**
	 * Get an array of rows from the database based on the query and the values
	 * @param String $query The MySQL query to execute
	 * @param Array $values The values to be populated into the queries '?'
	 * @param Bool $one If true then a Limit 1 will be applied to the query
	 * @param Bool $numberedIndexes If true then the response will be an indexed array rather than a hashtable
	 * @return Array|Mixed The rows that were found that met the criteria
	 */
	public function GetArray($query, $values=array(), $one=false, $numberedIndexes=false) {
		$arr = array();
		$this->Connect();
		
		if ($one)
			$query .= " LIMIT 1";
		
		$obj = $this->handle->prepare($query);
		$obj->execute($values);
		//$this->handle->rowCount();
		
		if ($one)
			return $obj->fetch($numberedIndexes ? PDO::FETCH_NUM : PDO::FETCH_ASSOC);
		else
			return $obj->fetchall($numberedIndexes ? PDO::FETCH_NUM : PDO::FETCH_ASSOC);
	}

	/**
	 * Get a single variable (field) from a row in the database
	 * @param String $what The field to be selected
	 * @param String $table The table to select from
	 * @param String $where The WHERE conditions to be applied to the query
	 * @param Array $values The values to be populated into the queries '?'
	 * @return Mixed|null The single value in the specified field
	 */
	public function GetResult($what, $table, $where, $values=array()) {
		$this->Connect();
		$obj = $this->handle->prepare("SELECT {$what} FROM {$table} WHERE $where LIMIT 1");
		$obj->execute($values);
		
		return $obj->fetch(PDO::FETCH_NUM)[0];
	}

	/**
	 * Disconnects the current handled database instance
	 */
	function Disconnect() {
		$this->handle = null;
	}
}
