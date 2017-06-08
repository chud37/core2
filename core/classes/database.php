<?
class Database {
	public $version = "2.18";

	public $version_history = array(
	"2.18" 	=> 	"Modified \$db->runSQL() to provide better feedback when using phpMyAdmin Dumps",
	"2.17"	=>	"Functions return a blank array() instead of false",
	"2.16"	=> 	"Cleaned up the error output to make it more readable.",
	"2.15"	=>	"Added prepared statement error feedback to \$this->select() and \$this->runSQL().",
	"2.14"	=>	"Fixed error handling in runSQL();",
	"2.13" 	=>	"Added checks and alternate INI file loading facility.",
	"2.12"	=>	"Added more advanced error reporting for select(), made \$surpressErrors public variable.",
	"2.11"	=>	"Fixed error reporting in runSQL();",
	"2.1" 	=>	"Removed old error reporting and replaced with \$this->error." 
	);
	
	
	
	private $database = false;		# The PDO database object.
	private $debug = false;
	public $prepared = array();		# Parameters for prepared statements.
	public $bind = array();			# Bind Parameters for more detailed binding. = array($key => value, type)
	public $databaseID = array();	# Credentials to connect to the database.
	public $fetchmode = PDO::FETCH_ASSOC;
	
	public $func = false;
	public $error = false;
	public $error_code = false;
	
	public $last_error = false;
	public $last_error_code = false;
	public $surpressErrors = false;
	
	public $sql;
	
	public function __construct($ini_credentials = false, $debug = false) {
		// Load credentials from /ini/database.ini
		$this->database = null;
		$this->debug = $debug;
		
		if(!defined("_DEBUG_")) define("_DEBUG_",false);
		
		if(!$this->databaseID) {
			if(is_file($ini_credentials)) {
				$this->databaseID = parse_ini_file($ini_credentials, false);
			} else {
				if(function_exists("load_ini_file")) {
					$this->databaseID = load_ini_file($ini_credentials);	
				} else {
					trigger_error("Function load_ini_file() not found.");	
				}
			}
		}
		try {
			$this->openDB();	
		} catch (Exception $e) {
			trigger_error($e->getMessage());
		}
	}
	public function openDB() {
		$this->database = null;
		
		if(!isset($this->databaseID['host'])) {$host = "localhost";} else {$host = $this->databaseID['host'];}
		if(!isset($this->databaseID['username'])) throw new Exception("No username set.");
		if(!isset($this->databaseID['password'])) throw new Exception("No password set.");
		if(!isset($this->databaseID['database'])) throw new Exception("No database set.");
		try {
			$this->database = new PDO("mysql:host={$host};dbname={$this->databaseID['database']}", $this->databaseID['username'], $this->databaseID['password']);
		} catch (PDOException $e) {
			if(_DEBUG_) {
				$msg = "Unable to connect to <b>{$this->databaseID['database']}</b><br/>Error: {$e}.";
			} else {
				$msg = "Unable to connect to <b>{$this->databaseID['database']}</b>";
			}
			throw new Exception($msg);
		}                          
	}                                                              
	public function select($sql, $singleRecord = false, $returnSingleArray = false) {
		$this->sql = $sql;
		try {
			return $this->sql_select($sql, $singleRecord, $returnSingleArray);	
		} catch (Exception $e) {
			trigger_error($e->getMessage());
		}
	}
	private function sql_select($sql, $singleRecord = false, $returnSingleArray = false) {
		$this->last_error = $this->error;
		$this->last_error_code = $this->error_code;
		$this->error = $this->error_code = $parsed = $results = false;
		$this->sql = $sql;
		
		$PDOErrorArray = array();
		$statement = $this->database->prepare($sql);
		$success = $statement->execute(($this->prepared ? $this->prepared : array()));
		if($statement->errorInfo()) $PDOErrorArray = $statement->errorInfo();	
		
		$parsed = array();
		
		if((isset($PDOErrorArray[2])) && ($PDOErrorArray[2] != "")) {
			$this->error = $PDOErrorArray[2];
			$this->error_code = $PDOErrorArray[0];
			if(!$this->surpressErrors) {
				// Remove whitespace / tabs from error messages (Mostley for Long SQL statements)
				$split = explode("\n",$sql);
				$formattedSQL = "";
				foreach($split as $s) {$formattedSQL .= trim($s) . "\n";}
				$split = explode("\n",$this->error);
				$formattedError = "";
				foreach($split as $s) {$formattedError .= trim($s) . "\n";}
				trigger_error(	$formattedError .
								(_DEBUG_ ? 
									"\n<b>From SQL Statement:</b>\n" . $formattedSQL . 
									($this->prepared ? "\n<b>Prepared Variables:</b>\n" . var_export($this->prepared, 1) : "\n\nThere were <b>no prepared variables.</b>") 
									: false)
								);
			}
			
		} else {
			$statement->setFetchMode($this->fetchmode);
			$count = 0;
			
			if($singleRecord) {
				$parsed = $statement->fetch(); 
				if(($returnSingleArray)) {
					if(is_array($parsed)) {
						reset($parsed);
						return $parsed[key($parsed)];
					} else {
						return $parsed;
					}
				} else {
					return $parsed;
				}
			} else {
				while($row = $statement->fetch()) {
					if($returnSingleArray) {
						foreach($row as $key => $val) {$parsed[] = $val;}
					} else {$parsed[] = $row;}
					$count++;
				}
				return $parsed;
			}
		}
	}
	public function runSQL($sql, $surpressErrors = false, $emulate_prepares = false) {
		$this->surpressErrors = $surpressErrors;
		$this->last_error = $this->error;
		$this->last_error_code = $this->error_code;
		$this->error = $this->error_code = false;
		$this->sql = $sql;
		
		if($emulate_prepares) $this->database->setAttribute(PDO::ATTR_EMULATE_PREPARES, 1);
		
		$PDOErrorArray = array();
		$statement = $this->database->prepare($sql);
		$success = $statement->execute(($this->prepared ? $this->prepared : array()));
		if($statement->errorInfo()) $PDOErrorArray = $statement->errorInfo();
		
		
		// Any errors?
		if((isset($PDOErrorArray[2])) && ($PDOErrorArray[2] != NULL)) {
			$this->error = $PDOErrorArray[2];
			$this->error_code = $PDOErrorArray[0];
			if(!$this->surpressErrors) {
				// Remove whitespace / tabs from error messages (Mostley for Long SQL statements)
				$split = explode("\n",$sql);
				$formattedSQL = "";
				foreach($split as $s) {$formattedSQL .= trim($s) . "\n";}
				$split = explode("\n",$this->error);
				$formattedError = "";
				foreach($split as $s) {$formattedError .= trim($s) . "\n";}
				trigger_error(	$formattedError .
								(_DEBUG_ ? 
									"\n\n<b>From SQL Statement:</b>\n" . $formattedSQL . 
									($this->prepared ? "\n\n<b>Prepared Variables:</b>\n" . var_export($this->prepared, 1) : "\n\nThere were <b>no prepared variables.</b>") 
									: false)
								);
			}
		}
		
		
		if((stripos($sql,"CREATE TABLE") !== false) || (stripos($sql,"DROP TABLE") !== false) || (stripos($sql,"-- phpMyAdmin SQL Dump") !== false)) {
			return $success;
		} else {
			if((stripos($sql, "UPDATE") !== false) || (stripos($sql, "DELETE FROM") !== false)) {
				if(!$this->error) {
					return $statement->rowCount();
				} else {return array();}
			} else {
				return $this->database->lastInsertId();
			}
		}
	}
	
	public function tableExists($tableName) {
		$mrSql = "SHOW TABLES LIKE :table_name";
		$mrStmt = $this->database->prepare($mrSql);
		//protect from injection attacks
		$mrStmt->bindParam(":table_name", $tableName, PDO::PARAM_STR);
	
		$sqlResult = $mrStmt->execute();
		if ($sqlResult) {
			$row = $mrStmt->fetch(PDO::FETCH_NUM);
			if ($row[0]) {
				//table was found
				return true;
			} else {
				//table was not found
				return false;
			}
		} else {
			//some PDO error occurred
			echo("Could not check if table exists, Error: ".var_export($pdo->errorInfo(), true));
			return false;
		}
	}
	
}
?>