<?php
/*
MySQL.class.php
Copyright (C) 2012 James Tracy

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/

/*
@fileoverview
Class for connecting and interacting with a MySQL database.

@since 0.1.0
*/

Backbone::uses(array("DataSource", "MySQLResult"));

// Default configurations
Backbone::$config->set("mysql.log", false);
Backbone::$config->set("mysql.logfile", "mysql.log");

/*
LOGGING
To set mysql logging to true, Backbone::$config->set("mysql.log", true)
To change the log file, Backbone::$config->set("mysql.logfile", "mysql.log")
	File name is relative to the application's web root and must be writable.
*/

/*
@fileoverview
DataSource for connecting and working with a MySQL database.
*/
class MySQL extends DataSource 
{	
	/* The mysql database connection */
	protected $_connection = null;
	
	/* The mysql fetch mode */
	protected $_fetch_mode = MySQLResult::FETCH_ASSOC;
	
	/* The last mysql result (MySQLResult) object */
	protected $_result = null;
	
	/* The time it took for the last mysql query to execute */
	protected $_duration = 0;
	
	/* The number of rows return in the last mysql query */
	protected $_num_rows = 0;
	
	/* An array of external hooks into the MySQL class */
	protected $_hooks = array();
	
	/* Query logging */
	protected $_queries_count = 0;
	protected $_queries_time = 0;
	
	/*
	Connect to a data source
	
	@param [array] $options An array of data source specific options.
	*/
	public function connect($options)
	{
		parent::connect($options);
		$this->_connection = mysql_connect($options['server'], $options['user'], $options['pass']) or die(mysql_error());
	}
	
	/*
	Disconnect from a data source
	*/
	public function disconnect()
	{
		if($this->isConnected())
		{
			$this->_is_connected = false;
			mysql_close($this->_connection);
		}
	}
	
	/*
	Get the MySQL error, if any
	
	@return [string] The error string, if any
	*/
	public function getError()
	{
		return mysql_error();
	}
	
	/*
	Hook into the MySQL class.
	Currently supported hooks:
		"table" => Called when a table name is being formatted
	
	@param [string] $name The hook name. See above.
	@param [array] $callback A valid parameter for call_user_func()
	*/
	public function hook($name, $callback)
	{
		$this->_hooks[$name] = $callback;
	}
	
	/*
	Escapes a string by adding slashes to special chars
	
	@param [string] $string The string to escape
	*/
	public function escape($string)
	{
		if($string == null || empty($string) || !is_string($string))
			return $string;
		if(!$this->isConnected())
			return addslashes($string);
		if(function_exists("magic_quotes_gpc") && magic_quotes_gpc())
			stripslashes();
		return mysql_real_escape_string($string);
	}
	
	/*
	Describe a table schema
	
	@param [string] $table The table name
		Ex: "name" or "database.name"
	@return [mixed] The table description
	*/
	public function describe($table)
	{
		if(!$this->isConnected())
			return new MySQLResult(null);
		if(empty($table))
			return new MySQLResult(null);
		return $this->query("DESCRIBE ".$this->formatTable($table));
	}
	
	/*
	Select rows from a table
	
	@param [string] $table The table name
	@param [string,array] $fields Attributes to select. 
		Either a string or an array of attributes.
		Ex: "*", "table.id", array("table.id", "table.comment")
	@param [array] $options Array of options
		
		array(
			"join" => array(
				"table" => array(
					"type" => "left", // default is "left" if not supplied
					"fields" => array("field1", "field2")
				)
			),
			"where" => array(
				"field1" => "2", // default operator is AND
				"OR" => array(
					"field2" => array("LIKE", "Jones"),
					"field3" => array("IN", array(1,2,4))
				)
			)
		)
		
	@return [mixed] The result set
	*/
	public function select($table, $fields, $options = array())
	{
		if(!$this->isConnected())
			return new MySQLResult(null);
		if(empty($table))
			return new MySQLResult(null);
		if(empty($fields))
			return new MySQLResult(null);
			
		// command
		$sql = "SELECT ";
		// fields
		if(!is_array($fields))
		$fields = array($fields);
		// loop over list of fields
		foreach($fields as $index => $field)
		{
			if($field == "COUNT(*)")
			{
				$fields[$index] = $field;
			}
			else
			{
				$fields[$index] = $this->format($field);
			}
		}
		
		$sql .= join(",", $fields);
		// table
		$sql .= " FROM ".$this->formatTable($table);
		// joins
		if(isset($options['join']))
		{
			$joins = "";
			foreach($options['join'] as $key => $value)
			{
				$type = "LEFT";
				if(isset($value['type']))
					$type = $value['type'];
				if(!isset($value['fields']))
					return new MySQLResult(null);
				$joins .= $type." JOIN ".$this->formatTable($key)." ON ".$this->format($value['fields'][0])." = ".$this->format($value['fields'][1])." ";
			}
			$sql .= " ".$joins;
		}
		// where
		if(isset($options['where']))
		{
			if(is_array($options['where']))
			{
				// structured data
				
				$where = $this->_where($options['where']);
			}
			else
			{
				// assume raw sql string
				$where = $options['where'];
			}
			$sql .= " WHERE ".$where;
		}
		// group by
		if(isset($options['group_by']))
		{
			$sql .= " GROUP BY ".$this->format($options['group_by']);
		}
		// order by
		if(isset($options['order_by']))
		{
			if(isset($options['order_by'][0]))
			{
				$sql .= " ORDER BY ".$this->format($options['order_by'][0])." ";
				if(isset($options['order_by'][1]))
				{
					$sql .= $options['order_by'][1];
				}
				else
				{
					$sql .= "ASC";
				}
			}
		}
		// limit
		if(isset($options['limit']))
		{
			$sql .= " LIMIT ".$options['limit'];
		}
		// offset
		if(isset($options['offset']))
		{
			$sql .= " OFFSET ".$options['offset'];
		}
		return $this->query($sql);
	}
	
	/*
	Wrapper for calling select with fields set to "*"
	See select() for description
	*/
	public function selectAll($table, $options)
	{
		return $this->select($table, "*", $options);
	}
	
	/*
	Wrapper for calling select with COUNT(*)
	See select() for description
	*/
	public function count($table, $options)
	{
		$result = $this->select($table, "COUNT(*)", $options);
		if($this->getError())
			return false;
		$row = $result->fetch();
		return $row["COUNT(*)"];
	}
	
	/*
	Insert data into a database
	
	@param [string] $table The table name
	@param [array] $fields An array of key => value pairs to insert
	@return [MySQLResult] The result set
	*/
	public function insert($table, $fields)
	{
		if(!$this->isConnected())
			return new MySQLResult(null);
		if(empty($table))
			return new MySQLResult(null);
		if(empty($fields))
			return new MySQLResult(null);
			
		$keys = array_keys($fields);
		$values = array_values($fields);
	
		// field names
		foreach($keys as $i => $key)
		{
			$keys[$i] = "`".$key."`";
		}
		
		// values
		foreach($values as $i => $val)
		{
			// add quotes?
			if(!in_array($val, array("NOW()")))
				$values[$i] = "'".$this->escape($val)."'";
		}
			
		$sql = sprintf("INSERT INTO %s (%s) VALUES (%s)", $this->formatTable($table), join(", ", $keys), join(", ", $values));
		return $this->query($sql);
	}
	
	/*
	Update data in a database
	
	@param [string] $table The table name
	@param [array] $fields An array of key => value pairs to update
	@param [array] $options An array of options, such as a where clause
	@return [MySQLResult] The result set
	*/
	public function update($table, $fields, $options)
	{
		if(!$this->isConnected())
			return new MySQLResult(null);
		if(empty($table))
			return new MySQLResult(null);
		if(empty($fields))
			return new MySQLResult(null);
			
		$updates = array();
		foreach($fields as $key => $value)
		{
			// add quotes?
			if(!in_array($value, array("NOW()")))
				$value = "'".$this->escape($value)."'";
			$updates[] = "`".$key."` = ".$value;
		}
			
		$sql = sprintf("UPDATE %s SET %s", $this->formatTable($table), join(", ", $updates));
		
		// where
		if(isset($options['where']))
		{
			if(is_array($options['where']))
			{
				// structured data
				
				$where = $this->_where($options['where']);
			}
			else
			{
				// assume raw sql string
				$where = $options['where'];
			}
			$sql .= " WHERE ".$where;
		}
		return $this->query($sql);
	}
	
	/*
	Delete data from a database
	
	@param [string] $table The table name
	@param [array] $options An array of options, such as a where clause
	@return [MySQLResult] The result set
	*/
	public function delete($table, $options)
	{
		if(!$this->isConnected())
			return new MySQLResult(null);
		if(empty($table))
			return new MySQLResult(null);
		
		$sql = "DELETE FROM ".$this->formatTable($table);
		if(isset($options['where']))
		{
			if(is_array($options['where']))
			{
				// structured data				
				$where = $this->_where($options['where']);
			}
			else
			{
				// assume raw sql string
				$where = $options['where'];
			}
			$sql .= " WHERE ".$where;
		}
		return $this->query($sql);
	}
	
	/*
	Query the database using a free text query
	
	@param [string] $query The SQL query to execute
	@return [MySQLResult] The result object
	*/
	public function query($query)
	{
		if(!$this->isConnected())
			return new MySQLResult(null);
		if(empty($query))
			return new MySQLResult(null);
		
		$t = microtime(true);
		$this->_result = new MySQLResult(mysql_query($query, $this->_connection));
				
		if(Backbone::$config->get("mysql.log"))
		{
			$this->_duration = round((microtime(true) - $t), 4);
			$this->_num_rows = $this->_result->numRows();
			$this->logQuery($query);
		}
		$this->_result->setFetchMode($this->_fetch_mode);
		return $this->_result;
	}
	
	/*
	Get the primary key of the last record inserted
	
	@return [integer] The primary key ID
	*/
	public function lastInsertID()
	{
		if(!$this->isConnected())
			return 0;
		return mysql_insert_id($this->_connection);
	}
	
	/*
	Log a mysql query
	To set mysql logging to true, Backbone::$config->set("mysql.log", true)
	To change the log file, Backbone::$config->set("mysql.logfile", "mysql.log")
		File name is relative to the application's web root.
		
	@param [string] $sql The query string
	*/
	public function logQuery($sql) 
	{
		$this->_queries_count++;
		$this->_queries_time += $this->_duration;

		$ob = $sql."\r\nNum Rows: ".$this->_num_rows."\r\nDuration: ".$this->_duration." seconds\r\n";
		if(mysql_error())
			$ob .= "Error: ".mysql_error()."\r\n";
		$ob .= "\r\n";
		file_put_contents(APPPATH.Backbone::$config->get("mysql.logfile"), $ob, FILE_APPEND);
	}
	
	/*
	Writes the current running totals to the end of the query log.
	*/
	public function tallyQueryLog()
	{
		if(Backbone::$config->get("mysql.log") == true)
		{
			if(file_exists(APPPATH.Backbone::$config->get("mysql.logfile")))
			{
				$ob = "----------------------------------------------------------------\r\n"; 
				$ob .= "Total Queries: ".$this->_queries_count."\r\n";
				$ob .= "Total Duration: ".$this->_queries_time." seconds\r\n";
				file_put_contents(APPPATH.Backbone::$config->get("mysql.logfile"), $ob, FILE_APPEND);
			}
		}
	}
	
	/*
	Clear the mysql log file if mysql logging is set to true.
	To set mysql logging to true, Backbone::$config->set("mysql.log", true)
	To change the log file, Backbone::$config->set("mysql.logfile", "mysql.log")
		File name is relative to the application's web root.
	*/
	public function clearQueryLog()
	{
		if(Backbone::$config->get("mysql.log") == true)
		{
			if(file_exists(APPPATH.Backbone::$config->get("mysql.logfile")))
			{
				file_put_contents(APPPATH.Backbone::$config->get("mysql.logfile"), "");
			}
		}
	}
	
	/* Get the current running total query count */
	public function getQueryCount()
	{
		return $this->_queries_count;
	}
	
	/* Get the current running total query time in milliseconds */
	public function getQueryTime()
	{
		return $this->_queries_time;
	}
	
	/*
	Function for formatting mysql table names
	Internally calls format but allows for an external hook to preprocess
	the table name.
	@param [string] $table The table name
		Ex: "name" or "database.name"
	@return [string] The formatted table name. Ex: "`database`.`name`"
	*/
	public function formatTable($name)
	{
		if(isset($this->_hooks['table'])/* && is_array($this->_hooks['table'])*/)
			$name = call_user_func($this->_hooks['table'], $name);
		return $this->format($name);
	}
	
	/*
	Function for formatting mysql table names or fields
	
	@param [string] $table The table name
		Ex: "name" or "database.name"
	@return [string] The formatted table name. Ex: "`database`.`name`"
	*/
	public function format($name)
	{
		if(strpos($name, "(") !== false)
        {
            // handle functions
            $first = strpos($name, "(");
            $last = strpos($name, ")");
            $var = substr($name, $first + 1, ($last - $first - 1));
            if($var == "*")
            {
                return $name;
            }
            else
            {
				$str = array();
				$tmp = explode(",", $var);
				$str[] = $this->format(trim($tmp[0]));
				if(isset($tmp[1]))
					$str[] = $tmp[1];
                return substr($name, 0, $first + 1).join(",",$str).")";
            }
        }

		if(strpos($name, ".") !== false)
		{
			$tmp = explode(".", $name);
			if(count($tmp) == 3)
			{
				return ("`".$tmp[0]."`.`".$tmp[1]."`.`".$tmp[2]."`");
			}
			else
			{
				return ("`".$tmp[0]."`.`".$tmp[1]."`");
			}
		}
		else
		{
			if($name == "*")
				return $name;
			return "`".$name."`";
		}
	}
	
	/*
	Internal function for composing a where clause
	
	@param [array] $where The array of conditions
	@param [string] $op The joining operator (AND, OR, NOT, XOR)
	@return [string] The combined where clause
	*/
	protected function _where($where, $op = "AND")
	{
		if(!in_array($op, array("AND", "OR", "NOT", "XOR")))
			return "";
			
		$tmp = array();
		foreach($where as $key => $value)
		{
			if(in_array($key, array("AND", "OR", "NOT", "XOR")))
			{
				// do a recursive combine
				$tmp[] = $this->_where($value, $key);
			}
			else
			{
				if(is_array($value))
				{
					// index 0 holds the operator
					if($value[0] == "IN" || $value[0] == "NOT IN")
					{
						$in = $value[0];
						array_shift($value);
						if(is_array($value[0]))
							$value = $value[0];
						$tmp[] = $this->format($key)." ".$in." (".join(",", $value).")";
					}
					else
					{
						$tmp[] = $this->format($key)." ".$value[0]." '".$value[1]."'";
					}
				}
				else
				{
					// simple '='
					$tmp[] = $this->format($key). " = '".$value."'";
				}
			}
		}
		return "(".join(" ".$op." ", $tmp).")";
	}
};
?>