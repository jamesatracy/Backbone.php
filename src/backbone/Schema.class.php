<?php
/**
 * Backbone.php
 * 
 * @author	James Tracy <james.a.tracy@gmail.com>
 * @copyright	2012-2013
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link https://github.com/jamesatracy/Backbone.php GitHub Page
 */

namespace Backbone;
use \Backbone;

/**
 * Schema represents the definition of a data resource and
 * is the base class for Backbone.php's Model class. 
 *
 * Schema is responsible for providing information about a model's
 * definition and for validation of data against that definition.
 * This provides a layer of security between data input and the 
 * database itself. 
 *
 * The schema is also used to populate new model's with default data.
 *
 * Schema data structure format, as a JSON string:
 *
 *		{
 *			// the name of the primary key
 *			"id" : "ID",
 *			// an array of column definitions. the key is the column name.
 *			"schema" : {
 *				"ID" : {
 *					// true if primary key
 *					"primary" : true,
 *					// see below for supported types
 *					"type" : "integer|float|string|char|timesstamp",
 *					// for integer types only. determines the range.
 *					"size" : "tinyint|smallint|mediumint|int|bigint",
 *					// for string and char types. determines max size.
 *					"length" : "255",
 *					// for integer types. may be 0 (signed) or 1
 *					"unsigned" : "0|1",
 *					// can this field accept null? 1 true, 0 false
 *					"acceptNULL" : "0|1",
 *					// the default value for this field
 *					"default" : "0|1"
 *				},
 *			...
 *		}			
 *
 * @since 0.1.0
 */
class Schema
{
	/** @var string Optional pointer to schema file name */
	public static $schemaFile = null;
	
	/** @var array A central cache for schema definitions */
	protected static $_schema_cache = array();
	
	/** @var array Associated array of field definitions */
	protected $_fields = array();
	
	/** @var string The primary key */
	protected $_id = "";
	
	/** @var array An array of error messages */
	protected $_errors = array();
	
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
		$schema = self::loadSchema($table);
		$this->_id = $schema['id'];
		$this->_fields = $schema['schema'];
		return $this->_fields;
	}
	
	/**
	 * Checks if the schema is initialized
	 *
	 * @since 0.1.0
	 * @return bool True if it is initialized
	 */
	public function isInitialized()
	{
		return (!empty($this->_fields));
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
		if($this->_fields && is_array($rules)) {
			foreach($rules as $key => $values) {
				if(isset($this->_fields[$key])) {
					$this->_fields[$key]['rules'] = $values;
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
		return (isset($this->_fields[$field]));
	}
	
	/**
	 * Convert the schema to a JSON array
	 *
	 * @since 0.1.0
	 * @return string The JSON formatted schema array
	 */
	public function schemaToJSON()
	{
		if(!$this->_fields) {
			return "";
		}
		return $this->_fields;
	}
	
	/**
	 * Export this schema definition to a file
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
	 * @throws RuntimeException
	 */
	public function validate($fields)
	{
		if(!$this->_fields) {
			throw new RuntimeException("Error: Schema has not been initialized.");
		}
		
		$this->_errors = array();
		foreach($fields as $key => $value) {
			if(!isset($this->_fields[$key])) {
				$this->_errors[] = "Invalid field name: ".$key.". ";
			} else {
				$this->_validateField($this->_fields[$key], $key, $value);
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
		if(!$this->_fields) {
			return $defaults;
		}
		
		foreach($this->_fields as $key => $definition) {
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
		
		// check for custom validation rules
		if(isset($field['rules'])) {
			Backbone::uses("Validate");
			foreach($field['rules'] as $rule => $args) {
				if(Validate::invoke($rule, $name, $value, $args) === false) {
					$this->_errors[] = Validate::$last_error;
					return;
				}
			}
		}
		
		$type = $field['type'];
		
		// check the field type
		if($type == "integer") {
			if(!is_int($value) && !is_numeric($value)) {
				// not a number
				$this->_errors[] = "Type mismatch `".$name."`: Expected integer and found ".Backbone::dump($value);
				return;
			}
			$int_value = intval($value);
			if($field['size'] == "tinyint") {
				if(($field['unsigned'] && ($int_value < 0 || $int_value > 255)) || (!$field['unsigned'] && ($int_value < -128 || $int_value > 127))) {
					// out of bounds
					$this->_errors[] = "Out of bounds error `".$name."`: Expected tinyint ".($field['unsigned'] ? "unsigned " : "")."and found ".Backbone::dump($value);
				}
			} else if($field['size'] == "smallint") {
				// out of bounds
				if(($field['unsigned'] && ($int_value < 0 || $int_value > 65535)) || (!$field['unsigned'] && ($int_value < -32768 || $int_value > 32767))) {
					// out of bounds
					$this->_errors[] = "Out of bounds error `".$name."`: Expected smallint ".($field['unsigned'] ? "unsigned " : "")."and found ".Backbone::dump($value);
				}
			} else if($field['size'] == "mediumint") {
				// out of bounds
				if(($field['unsigned'] && ($int_value < 0 || $int_value > 16777215)) || (!$field['unsigned'] && ($int_value < -8388608 || $int_value > 8388607))) {
					// out of bounds
					$this->_errors[] = "Out of bounds error `".$name."`: Expected mediumint ".($field['unsigned'] ? "unsigned " : "")."and found ".Backbone::dump($value);
				}
			} else if($field['size'] == "int") {
				// out of bounds
				if(($field['unsigned'] && ($int_value < 0 || $int_value > 4294967295)) || (!$field['unsigned'] && ($int_value < -2147483648 || $int_value > 2147483647))) {
					// out of bounds
					$this->_errors[] = "Out of bounds error `".$name."`: Expected int ".($field['unsigned'] ? "unsigned " : "")."and found ".Backbone::dump($value);
				}
			} else if($field['size'] == "bigint") {
				// out of bounds
				if(($field['unsigned'] && ($int_value < 0 || $int_value > 18446744073709551615)) || (!$field['unsigned'] && ($int_value < -9223372036854775808 || $int_value > 9223372036854775807))) {
					// out of bounds
					$this->_errors[] = "Out of bounds error `".$name."`: Expected bigint ".($field['unsigned'] ? "unsigned " : "")."and found ".Backbone::dump($value);
				}
			}
		} else if($type == "float") {
			if(!is_float($value) && !is_numeric($value)) {
				// not a float
				$this->_errors[] = "Type mismatch `".$name."`: Expected float and found ".Backbone::dump($value);
				return;
			}
		} else if($type == "string") {
			if($field['length'] != null) {
				$length = strlen(strval($value));
				if($length > $field['length']) {
					// string exceeds maximum character length
					$this->_errors[] = "String length (".$field['length'].") exceeded for `".$name."`: ".Backbone::dump($value)." (".$length.")";
				}
			}
		} else if($type == "char") {
			$length = strlen(strval($value));
			if($length > 255) {
				// string exceeds maximum character length
				$this->_errors[] = "Char length (".$field['length'].") exceeded for `".$name."`: ".Backbone::dump($value)." (".$length.")";
			} else if($length > $field['length']) {
				// string exceeds maximum character length
				$this->_errors[] = "Char length (".$field['length'].") exceeded for `".$name."`: ".Backbone::dump($value)." (".$length.")";
			}
		} else if($type == "datetime") {
			if(preg_match("/^(\d{4})-(\d{2})-(\d{2}) ([01][0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9])$/", $value, $matches)) { 
				if(!checkdate($matches[2], $matches[3], $matches[1])) { 
					if($value != "0000-00-00 00:00:00") {
						// invalid datetime
						$this->_errors[] = "Invalid datetime `".$name."`: ".Backbone::dump($value);
					}
				} else {
					if($value < "1000-01-01 00:00:00") {
						$this->_errors[] = "Out of bounds error `".$name."`: datetime must be >= '1000-01-01 00:00:00' but found ".$value;
					}
				}
			} else {
				// invalid datetime
				$this->_errors[] = "Expecting datetime `".$name."`: ".Backbone::dump($value);
			}
		} else if($type == "timestamp") {
			if(preg_match("/^(\d{4})-(\d{2})-(\d{2}) ([01][0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9])$/", $value, $matches)) { 
				if(!checkdate($matches[2], $matches[3], $matches[1])) { 
					// invalid timestamp
					if($value != "0000-00-00 00:00:00") {
						$this->_errors[] = "Invalid timestamp `".$name."`: ".Backbone::dump($value);
					}
				} else {
					if($value < "1970-01-01 00:00:01") {
						$this->_errors[] = "Out of bounds error `".$name."`: timestamp must be >= '1970-01-01 00:00:01' but found ".$value;
					}
				}
			} else {
				// invalid timestamp
				$this->_errors[] = "Expecting timestamp `".$name."`: ".Backbone::dump($value);
			}
		}
	}
	
	/**
	 * Loads a schema from an existing database table.
	 * 
	 * May also pull from a schema file or from the intrnal cache.
	 *
	 * @since 0.2.0
	 * @param string $table The table name
	 * @return array The schema structure
	 * @throws \RuntimeException
	 */
	public static function loadSchema($table)
	{
	    if(isset(Schema::$_schema_cache[$table])) {
			return Schema::$_schema_cache[$table];
		}

	    if(static::$schemaFile) {
			$cache = json_decode(file_get_contents(ABSPATH.static::$schemaFile), TRUE);
			Schema::$_schema_cache[$table] = $cache;
			return $cache;
		}
		
		if(!DB::isConnected()) {
			throw new \RuntimeException("Schema: Invalid connection");
		}
		
		$id = 0;
		$fields = array();
		$result = DB::table($table)->describe()->exec();
		foreach($result as $row) {
			$attrs = array();
			$field = $row['Field'];
			$type = $row['Type'];
			$null = $row['Null'];
			$default = $row['Default'];
			
			if($row['Key'] == "PRI") {
				// set the primary key
				$id = $field;
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
		$schema = array("id" => $id, "schema" => $fields);
		Schema::$_schema_cache[$table] = $schema;
		return $schema;
	}
};

?>