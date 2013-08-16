<?php
/**
 * Backbone.php
 * 
 * @author	James Tracy <james.a.tracy@gmail.com>
 * @copyright	2012-2013
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link https://github.com/jamesatracy/Backbone.php GitHub Page
 */

Backbone::uses(array("Connections", "DataType", "SchemaRules"));

/**
 * Class for working with MySQL schemas as model representations. 
 *
 * Supports automatic validation against the schema.
 * Schema is the base class for Model.
 *
 * @since 0.1.0
 */
class Schema
{
	/** @var array Central cache for schema definitions */
	protected static $_schema_cache = array();
	
	/** @var object Database connection */
	protected $_db = null;
	
	/** @var string Optional pointer to schema file name */
	public $schemaFile = null;
	
	/** @var array The schema */
	protected $_schema = array();
	
	/** @var string The primary key */
	protected $_id = "";
	
	/** @var array An array of error messages */
	protected $_errors = array();
	
	/**
	 * Constructor
	 *
	 * @constructor
	 * @param string $connection The name of the database connection.
	 */
	public function __construct($connection = "default")
	{
		if(is_string($connection)) {
			// string name of the connection
			$this->_db = Connections::get($connection);
		} else {
			// assume database object instance
			$this->_db = $connection;
		}
		if(!$this->_db) {
			trigger_error("Error: Invalid connection suplied to Schema");
		}
	}
	
	/**
	 * Initialize the schema
	 *
	 * @since 0.1.0
	 * @param string $table The table name
	 * @param bool $cacheable Whether or not to cache the schema. Default is true.
	 * @return array The schema object as an associated array
	 */
	public function initialize($table, $cacheable = true)
	{
		if($this->_db && $this->_db->isConnected()) {
			if($cacheable) {
				if(isset(Schema::$_schema_cache[$table])) {
					$cache = Schema::$_schema_cache[$table];
					$this->_schema = $cache['schema'];
					$this->_id = $cache['id'];
					return $this->_schema;
				}
			}
			if($this->schemaFile) {
				$cache = json_decode(file_get_contents(ABSPATH.$this->schemaFile));
				$this->_schema = $cache['schema'];
				$this->_id = $cache['id'];
			} else {
				$this->_schema = $this->_loadSchema($table);
			}
			if($cacheable) {
				Schema::$_schema_cache[$table] = array("id" => $this->_id, "schema" => $this->_schema);
			}
		}
	}
	
	/**
	 * Checks if the schema is initialized
	 *
	 * @since 0.1.0
	 * @return bool True if it is initialized
	 */
	public function isInitialized()
	{
		return (!empty($this->_schema));
	}
	
	/**
	 * Add additional validation rules to a group of fields.
	 *
	 * @since 0.1.0
	 * @param array An array of key => value pairs, where the value corresponds to
	 *	a rule or an array of rules to add to the field.
	 *	
	 *	Ex:
	 *		$schema->rules(
	 *			array("email" => array("email" => true))
	 *		);
	 */
	public function rules($rules)
	{
		if($this->_schema && is_array($rules)) {
			foreach($rules as $key => $values) {
				if(isset($this->_schema[$key])) {
					$this->_schema[$key]['rules'] = $values;
				}
			}
		}
	}
	
	/**
	 * Get the primary key
	 * 
	 * @since 0.1.0
	 * @return [string] The field name of the primary key
	 */
	public function getID()
	{
		return $this->_id;
	}
	
	/**
	 * Checks for the existence of a field in the schema
	 *
	 * @since 0.1.0
	 * @param string $field The name of the field
	 * @return bool True if the field exists
	 */
	public function hasField($field)
	{
		if(!$this->isInitialized()) {
			return false;
		}
		return (isset($this->_schema[$field]));
	}
	
	/**
	 * Convert the schema to a JSON array
	 *
	 * @since 0.1.0
	 * @return string The JSON formatted schema array
	 */
	public function schemaToJSON()
	{
		if(!$this->_schema) {
			return "";
		}
		return $this->_schema;
	}
	
	/**
	 * Export this schema defintion to a file
	 *
	 * @since 0.1.0
	 * @param string $file The file name relative to the ABSPATH
	 *	Ex: '/path/to/file/name.schema'
	 */
	public function export($file)
	{
		if(substr($file, 0, 1) == "/") {
			$file = substr($file, 1);
		}
		file_put_contents(ABSPATH.$file, json_encode(array("id" => $this->getID(), "schema" => $this->schemaToJSON())));
	}
	
	/**
	 * Gets the last error message(s)
	 * 
	 * @since 0.1.0
	 * @return string The error message(s)
	 */
	public function getErrors()
	{
		return $this->_errors;
		//return join(" // ", $this->_errors);
	}
	
	/**
	 * Returns true if the last operation produced any error messages
	 *
	 * @since 0.1.0
	 * @return boolean True if the last operation produced any error messages
	 */
	public function hasErrors()
	{
		return (!empty($this->_errors));
	}
	
	/**
	 * Validate one or more fields.
	 *
	 * Error messages can be retrieved through getErrors()
	 *
	 * @since 0.1.0
	 * @param array $fields An array of field => value pairs to validate
	 * @returns bool True if all fields validate, false otherwise.
	 */
	public function validate($fields)
	{
		if(!$this->_schema) {
			$this->_errors = array("The Schema has not been initialized.");
			return false;
		}
		
		$this->_errors = array();
		foreach($fields as $key => $value) {
			if(!isset($this->_schema[$key])) {
				$this->_errors[] = "Invalid field name: ".$key.". ";
			} else {
				$this->_validateField($this->_schema[$key], $key, $value);
			}
		}
		
		if(empty($this->_errors)) {
			return true;
		}
		return false; // errors
	}
	
	/**
	 * Get an array initialized to the default values
	 *
	 * @since 0.1.0
	 * @return array An associative array of key => value pairs set to the default values as defined
	 * in the active schema.
	 */
	public function defaultValues()
	{
		$defaults = array();
		if(!$this->_schema) {
			return $defaults;
		}
		
		foreach($this->_schema as $key => $definition) {
			$value = $definition['default'];
			if($value == "CURRENT_TIMESTAMP") {
				$value = date('Y-m-d H:i:s', time());
			}
			$defaults[$key] = $value;
		}
			
		return $defaults;
	}
	
	/**
	 * Internal function for validating a field
	 *
	 * @since 0.1.0
	 * @param array $field The field schema object
	 * @param string $name The name of the field
	 * @param mixed $value The value of the field to validate against
	 */
	protected function _validateField($field, $name, $value)
	{
		if(isset($field['primary']) && $field['primary']) {
			return;
		}
			
		if($value === "NULL") {
			// can this field be null?
			if(!$field['acceptNULL']) {
				$this->_errors[] = "Field `".$name."` cannot be set to NULL. ".$value;
				return;
			}
		}
		
		// check for custom schema rules
		if(isset($field['rules'])) {
			foreach($field['rules'] as $rule => $args) {
				if(SchemaRules::invoke($rule, $name, $value, $args) === false) {
					$this->_errors[] = SchemaRules::$last_error;
					return;
				}
			}
		}
		
		$type = $field['type'];
		
		// check the field type
		if($type == "integer") {
			if(!is_int($value) && !is_numeric($value)) {
				// not a number
				$this->_errors[] = "Type mismatch `".$name."`: Expected integer and found ".DataType::export($value);
				return;
			}
			$int_value = intval($value);
			if($field['size'] == "tinyint") {
				if(($field['unsigned'] && ($int_value < 0 || $int_value > 255)) || (!$field['unsigned'] && ($int_value < -128 || $int_value > 127))) {
					// out of bounds
					$this->_errors[] = "Out of bounds error `".$name."`: Expected tinyint ".($field['unsigned'] ? "unsigned " : "")."and found ".DataType::export($value);
				}
			} else if($field['size'] == "smallint") {
				// out of bounds
				if(($field['unsigned'] && ($int_value < 0 || $int_value > 65535)) || (!$field['unsigned'] && ($int_value < -32768 || $int_value > 32767))) {
					// out of bounds
					$this->_errors[] = "Out of bounds error `".$name."`: Expected smallint ".($field['unsigned'] ? "unsigned " : "")."and found ".DataType::export($value);
				}
			} else if($field['size'] == "mediumint") {
				// out of bounds
				if(($field['unsigned'] && ($int_value < 0 || $int_value > 16777215)) || (!$field['unsigned'] && ($int_value < -8388608 || $int_value > 8388607))) {
					// out of bounds
					$this->_errors[] = "Out of bounds error `".$name."`: Expected mediumint ".($field['unsigned'] ? "unsigned " : "")."and found ".DataType::export($value);
				}
			} else if($field['size'] == "int") {
				// out of bounds
				if(($field['unsigned'] && ($int_value < 0 || $int_value > 4294967295)) || (!$field['unsigned'] && ($int_value < -2147483648 || $int_value > 2147483647))) {
					// out of bounds
					$this->_errors[] = "Out of bounds error `".$name."`: Expected int ".($field['unsigned'] ? "unsigned " : "")."and found ".DataType::export($value);
				}
			} else if($field['size'] == "bigint") {
				// out of bounds
				if(($field['unsigned'] && ($int_value < 0 || $int_value > 18446744073709551615)) || (!$field['unsigned'] && ($int_value < -9223372036854775808 || $int_value > 9223372036854775807))) {
					// out of bounds
					$this->_errors[] = "Out of bounds error `".$name."`: Expected bigint ".($field['unsigned'] ? "unsigned " : "")."and found ".DataType::export($value);
				}
			}
		} else if($type == "float") {
			if(!is_float($value) && !is_numeric($value)) {
				// not a float
				$this->_errors[] = "Type mismatch `".$name."`: Expected float and found ".DataType::export($value);
				return;
			}
		} else if($type == "string") {
			if($field['length'] != null) {
				$length = strlen(strval($value));
				if($length > $field['length']) {
					// string exceeds maximum character length
					$this->_errors[] = "String length (".$field['length'].") exceeded for `".$name."`: ".DataType::export($value)." (".$length.")";
				}
			}
		} else if($type == "char") {
			$length = strlen(strval($value));
			if($length > 255) {
				// string exceeds maximum character length
				$this->_errors[] = "Char length (".$field['length'].") exceeded for `".$name."`: ".DataType::export($value)." (".$length.")";
			} else if($length > $field['length']) {
				// string exceeds maximum character length
				$this->_errors[] = "Char length (".$field['length'].") exceeded for `".$name."`: ".DataType::export($value)." (".$length.")";
			}
		} else if($type == "datetime") {
			if(preg_match("/^(\d{4})-(\d{2})-(\d{2}) ([01][0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9])$/", $value, $matches)) { 
				if(!checkdate($matches[2], $matches[3], $matches[1])) { 
					if($value != "0000-00-00 00:00:00") {
						// invalid datetime
						$this->_errors[] = "Invalid datetime `".$name."`: ".DataType::export($value);
					}
				} else {
					if($value < "1000-01-01 00:00:00") {
						$this->_errors[] = "Out of bounds error `".$name."`: datetime must be >= '1000-01-01 00:00:00' but found ".$value;
					}
				}
			} else {
				// invalid datetime
				$this->_errors[] = "Expecting datetime `".$name."`: ".DataType::export($value);
			}
		} else if($type == "timestamp") {
			if(preg_match("/^(\d{4})-(\d{2})-(\d{2}) ([01][0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9])$/", $value, $matches)) { 
				if(!checkdate($matches[2], $matches[3], $matches[1])) { 
					// invalid timestamp
					if($value != "0000-00-00 00:00:00") {
						$this->_errors[] = "Invalid timestamp `".$name."`: ".DataType::export($value);
					}
				} else {
					if($value < "1970-01-01 00:00:01") {
						$this->_errors[] = "Out of bounds error `".$name."`: timestamp must be >= '1970-01-01 00:00:01' but found ".$value;
					}
				}
			} else {
				// invalid timestamp
				$this->_errors[] = "Expecting timestamp `".$name."`: ".DataType::export($value);
			}
		}
	}
	
	/**
	 * Internal function for loading a schema
	 *
	 * @since 0.1.0
	 * @param string $table The table name
	 * @return array The schema object as an associated array
	 */
	protected function _loadSchema($table)
	{
		$db = $this->_db;
		$fields = array();
		$result = $db->describe($table);
		while($row = $result->fetch()) {
			// print_r($row);
			// echo "<br/>";
			$attrs = array();
			$field = $row['Field'];
			$type = $row['Type'];
			$null = $row['Null'];
			$default = $row['Default'];
			
			if($row['Key'] == "PRI") {
				// set the primary key
				$this->_id = $field;
				$attrs['primary'] = true;
			}
			
			// format type
			if(substr($type, 0, 3) == "int" || substr($type, 0, 7) == "tinyint" || substr($type, 0, 8) == "smallint" || substr($type, 0, 9) == "mediumint" || substr($type, 0, 6) == "bigint") {
				// get size
				preg_match_all('/\((.*?)\)/', $type, $matches);
				$attrs["type"] = "integer";
				$attrs["size"] = substr($type, 0, strpos($type, "("));
				$attrs["length"] = $matches[1][0];
				
				if(stripos($type, "unsigned") !== false) {
					$attrs["unsigned"] = "1";
				} else {
					$attrs["unsigned"] = "0";
				}
				
				if($default == null && $null == "NO") {
					$default = "0";
				}
			} else if(substr($type, 0, 5) == "float" || substr($type, 0, 6) == "double") {
				// get size
				preg_match_all('/\((.*?)\)/', $type, $matches);
				$attrs["type"] = "float";
				$attrs["size"] = substr($type, 0, strpos($type, "("));
				$attrs["length"] = $matches[1][0];
			} else if(substr($type, 0, 7) == "varchar") {
				// get size
				preg_match_all('/\((.*?)\)/', $type, $matches);
				$attrs["type"] = "string";
				$attrs["length"] = $matches[1][0];
			} else if(substr($type, 0, 4) == "char") {
				// get size
				preg_match_all('/\((.*?)\)/', $type, $matches);
				$attrs["type"] = "char";
				$attrs["length"] = $matches[1][0];
			} else if(substr($type, 0, 4) == "text" || substr($type, 0, 8) == "longtext") {
				// get size
				$attrs["type"] = "string";
				$attrs["length"] = null;
			} else {
				$attrs["type"] = $type;
			}
			
			// null
			if($null == "NO") {
				$attrs["acceptNULL"] = '0';
			} else {
				$attrs["acceptNULL"] = '1';
			}
			
			// default
			$attrs["default"] = ($default == null ? "" : ($default == "NULL" ? null : $default));
			
			$fields[$field] = $attrs;
		}
		return $fields;
	}
};

?>
