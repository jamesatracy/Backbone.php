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
 * Query builder class.
 * 
 * Provides conveniencce methods for building SQL queries.
 * 
 * @since 0.3.0
 */
class Query
{	
	/** @var string The selected table for the current query. */
	protected $_table = null;
	
	/** @var string The command verb for the current query. */
	protected $_command = null;
	
	/** @var array For select statements, the columns to select. */
	protected $_select = array();
	
	/** @var array For update statements, the key / values to update. */
	protected $_updates = array();
	
	/** @var array For insert statements, the key / values to insert. */
	protected $_inserts = array();
	
	/** @var string The order by clause for the current query. */
	protected $_order_by = null;
	
	/** @var int The limit for the current query. */
	protected $_limit = 0;
	
	/** @var int The offset for the current query. */
	protected $_offset = 0;
	
	/** @var array Where clause expression stack */
	protected $_whereExpr = array();
	
	/** @var array Where clause operator stack */
	protected $_whereOps = array();
	
	/** 
	 * Creates a new query builder object with the given table name.
	 *
	 * @since 0.3.0
	 * @param string $name The table name to use.
	 */
	public function __construct($name)
	{
		$this->_table = $name;
	}
	
	/**
	 * Build a select query.
	 *
	 * Sets the current command to 'select' and optionally
	 * sets the columns. If none are provided then all will
	 * be selected.
	 *
	 * @since 0.3.0
	 * @param mixed 0 or more column names.
	 * @return Query The active query object.
	 */
	public function select()
	{
		$cols = func_get_args();
		$this->_select = $cols;
		$this->_command = "select";
		return $this;
	}
	
	/**
	 * Build an update query.
	 *
	 * Sets the current command to 'udpate'. Requires at least
	 * one set of key - value pairs to update.
	 *
	 * @since 0.3.0
	 * @param array $fields An array of key value pairs to update.
	 * @return Query The active query object.
	 * @throws InvalidArgumentException
	 */
	public function update($fields)
	{
		if(empty($fields)) {
			throw new \InvalidArgumentException("Query: Missing fields to update");
		}
		$this->_updates = $fields;
		$this->_command = "update";
		return $this;
	}
	
	/**
	 * Build an insert query.
	 *
	 * Sets the current command to 'insert'. Requires at least
	 * one set of key - value pairs to insert.
	 *
	 * @since 0.3.0
	 * @param array $fields An array of key value pairs to insert.
	 * @return Query The active query object.
	 * @throws InvalidArgumentException
	 */
	public function insert($fields)
	{
		if(empty($fields)) {
			throw new \InvalidArgumentException("Query: Missing fields to update");
		}
		$this->_inserts = $fields;
		$this->_command = "insert";
		return $this;
	}
	
	/**
	 * Build a delete query.
	 *
	 * Sets the current command to 'delete'.
	 *
	 * @since 0.3.0
	 * @return Query The active query object.
	 */
	public function delete()
	{
		$this->_command = "delete";
		return $this;
	}
	
	/**
	 * Build a count (select) query.
	 *
	 * Sets the current command to 'count' and optionally
	 * sets the columns. If none are provided then all will
	 * be passed to count().
	 *
	 * @since 0.3.0
	 * @param mixed 0 or more column names.
	 * @return Query The active query object.
	 */
	public function count()
	{
		$cols = func_get_args();
		$this->_select = $cols;
		$this->_command = "count";
		return $this;
	}
	
	/**
	 * Set the limit for the query.
	 *
	 * @since 0.3.0
	 * @param int $limit The limit.
	 * @return Query The active query object.
	 */
	public function limit($limit)
	{
		$this->_limit = $limit;
		return $this;
	}
	
	/**
	 * Set the order by clause for the query.
	 *
	 * Examples:
	 *	$query->orderBy('name ASC');
	 *	$query->orderBy('id DESC');
	 *
	 * @since 0.3.0
	 * @param int $limit The limit.
	 * @return Query The active query object.
	 */
	public function orderBy($order)
	{
		$this->_order_by = $order;
		return $this;
	}
	
	/**
	 * Set the offset for the query.
	 *
	 * @since 0.3.0
	 * @param int $offset The offset.
	 * @return Query The active query object.
	 */
	public function offset($offset)
	{
		$this->_offset = $offset;
		return $this;
	}
	
	/**
	 * Adds a where clause to the current query.
	 * Will be conjoined with an AND to previous expressions.
	 *
	 * @since 0.3.0
	 * @param string $field The field name
	 * @param string $op The operator, = is implied
	 * @param string $value The value to compare to.
	 * @return Query The active query object.
	 * @throws InvalidArgumentException
	 */
	public function where()
	{
		$args = func_get_args();
		$count = count($args);
		if($count === 0) {
			throw new \InvalidArgumentException("Query: Missing arguments to where");
		}
		$pdo = DB::getPDO();
		if($count === 3) {
			$this->_whereExpr[] = $args[0]." ".$args[1]." ".$pdo->quote($args[2]);
		} else if($count === 2) {
			$this->_whereExpr[] = $args[0]." = ".$pdo->quote($args[1]);
		} else {
			$this->_whereExpr[] = $args[0];
		}
		if(count($this->_whereExpr) > 1) {
			$this->_whereOps[] = "AND";
		}
		return $this;
	}
	
	/**
	 * Adds a where clause to the current query.
	 * Will be conjoined with a OR to previous expressions.
	 *
	 * @since 0.3.0
	 * @param string $field The field name
	 * @param string $op The operator, = is implied
	 * @param string $value The value to compare to.
	 * @return Query The active query object.
	 * @throws InvalidArgumentException
	 */
	public function orWhere()
	{
		$args = func_get_args();
		$count = count($args);
		if($count === 0) {
			throw new \InvalidArgumentException("Query: Missing arguments to orWhere");
		}
		$pdo = DB::getPDO();
		if($count === 3) {
			$this->_whereExpr[] = $args[0]." ".$args[1]." ".$pdo->quote($args[2]);
		} else if($count === 2) {
			$this->_whereExpr[] = $args[0]." = ".$pdo->quote($args[1]);
		} else {
			$this->_whereExpr[] = $args[0];
		}
		if(count($this->_whereExpr) > 1) {
			$this->_whereOps[] = "OR";
		}
		return $this;
	}
	
	/**
	 * Adds a where clause to the current query comparing the field
	 * to NULL. Will be conjoined with an AND to previous expressions.
	 *
	 * Example:
	 *	->whereNull("name");
	 *
	 *	WHERE name IS NULL
	 *
	 * @since 0.3.0
	 * @param string $field The name of the field to compare with NULL
	 * @return Query The active query object.
	 */
	public function whereNull($field)
	{
		$this->_whereExpr[] = $field." IS NULL";
		if(count($this->_whereExpr) > 1) {
			$this->_whereOps[] = "AND";
		}
		return $this;
	}
	
	/**
	 * Adds a where clause to the current query comparing the field
	 * to NULL. Will be conjoined with an OR to previous expressions.
	 *
	 * Example:
	 *	->whereNull("name");
	 *
	 *	WHERE name IS NULL
	 *
	 * @since 0.3.0
	 * @param string $field The name of the field to compare with NULL
	 * @return Query The active query object.
	 */
	public function orWhereNull($field)
	{
		$this->_whereExpr[] = $field." IS NULL";
		if(count($this->_whereExpr) > 1) {
			$this->_whereOps[] = "OR";
		}
		return $this;
	}
	
	/**
	 * Adds a where clause to the current query comparing the field
	 * to NOT NULL. Will be conjoined with an AND to previous expressions.
	 *
	 * Example:
	 *	->whereNotNull("name");
	 *
	 *	WHERE name IS NOT NULL
	 *
	 * @since 0.3.0
	 * @param string $field The name of the field to compare with NULL
	 * @return Query The active query object.
	 */
	public function whereNotNull($field)
	{
		$this->_whereExpr[] = $field." IS NOT NULL";
		if(count($this->_whereExpr) > 1) {
			$this->_whereOps[] = "AND";
		}
		return $this;
	}
	
	/**
	 * Adds a where clause to the current query comparing the field
	 * to NOT NULL. Will be conjoined with an OR to previous expressions.
	 *
	 * Example:
	 *	->orWhereNotNull("name");
	 *
	 *	WHERE name IS NOT NULL
	 *
	 * @since 0.3.0
	 * @param string $field The name of the field to compare with NULL
	 * @return Query The active query object.
	 */
	public function orWhereNotNull($field)
	{
		$this->_whereExpr[] = $field." IS NOT NULL";
		if(count($this->_whereExpr) > 1) {
			$this->_whereOps[] = "OR";
		}
		return $this;
	}
	
	/**
	 * Adds an IN statement to the current query's where clause
	 * and conjoins it with an AND to previous expressions.
	 *
	 * Example:
	 *	->whereIn("type", array("News","Featured","Opinion"))
	 *
	 * @since 0.3.0
	 * @param string $field The name of the field
	 * @param array $values An array of values to test
	 * @return Query The active query object.
	 */
	public function whereIn($type, $values)
	{
		$pdo = DB::getPDO();
		$tmp = array();
		foreach($values as $v) {
			$tmp[] = $pdo->quote($v);
		}
		
		return $this->where($type." IN (".join(",",$tmp).")");
	}
	
	/**
	 * Adds a NOT IN statement to the current query's where clause
	 * and conjoins it with an AND to previous expressions.
	 *
	 * Example:
	 *	->whereNotIn("type", array("News","Featured","Opinion"))
	 *
	 * @since 0.3.0
	 * @param string $field The name of the field
	 * @param array $values An array of values to test
	 * @return Query The active query object.
	 */
	public function whereNotIn($type, $values)
	{
		$pdo = DB::getPDO();
		$tmp = array();
		foreach($values as $v) {
			$tmp[] = $pdo->quote($v);
		}
		
		return $this->where($type." NOT IN (".join(",",$tmp).")");
	}
	
	/**
	 * Adds an IN statement to the current query's where clause
	 * and conjoins it with an OR to previous expressions.
	 *
	 * Example:
	 *	->whereIn("type", array("News","Featured","Opinion"))
	 *
	 * @since 0.3.0
	 * @param string $field The name of the field
	 * @param array $values An array of values to test
	 * @return Query The active query object.
	 */
	public function orWhereIn($type, $values)
	{
		$pdo = DB::getPDO();
		$tmp = array();
		foreach($values as $v) {
			$tmp[] = $pdo->quote($v);
		}
		
		return $this->orWhere($type." IN (".join(",",$tmp).")");
	}
	
	/**
	 * Adds a NOT IN statement to the current query's where clause
	 * and conjoins it with an OR to previous expressions.
	 *
	 * Example:
	 *	->whereNotIn("type", array("News","Featured","Opinion"))
	 *
	 * @since 0.3.0
	 * @param string $field The name of the field
	 * @param array $values An array of values to test
	 * @return Query The active query object.
	 */
	public function orWhereNotIn($type, $values)
	{
		$pdo = DB::getPDO();
		$tmp = array();
		foreach($values as $v) {
			$tmp[] = $pdo->quote($v);
		}
		
		return $this->orWhere($type." NOT IN (".join(",",$tmp).")");
	}
	
	/**
	 * Get the fully formed query string for the current query
	 * based on the data passed to any of the various query 
	 * builder methods.
	 *
	 * @since 0.3.0
	 * @return string The query string.
	 */
	public function getQuery()
	{
		if(!$this->_table) {
			return "";
		}
		switch($this->_command) {
			case "select":
			case "count":
				return $this->_buildSelect();
			
			case "update":
			return $this->_buildUpdate();
			
			case "insert":
			return $this->_buildInsert();
			
			case "delete":
			return $this->_buildDelete();
		}
		return "";
	}
	
	/**
	 * Get the fully formed wher clause for the current query
	 * based on the data passed to any of the various where 
	 * builder methods.
	 *
	 * @since 0.3.0
	 * @return string The query string.
	 */
	public function getWhere()
	{
		if(empty($this->_whereExpr)) {
			return "";
		}
		if(empty($this->_whereOps)) {
			return $this->_whereExpr[0];
		}
		$where = "";
		while(count($this->_whereOps) > 0) {
			$op = array_pop($this->_whereOps);
			$right = array_pop($this->_whereExpr);
			$left = array_pop($this->_whereExpr);
			$where .= $left." ".$op." ".$right;
		}
		return $where;
	}
	
	/**
	 * Executes the current query based on data passed to any
	 * of the various query builder methods.
	 *
	 * A command must be specified first.
	 *
	 * Select commands return the entire result set.
	 * Count commands return the count;
	 * All other commands return the number of rows affected.
	 *
	 * @since 0.3.0
	 * @return array|int The result set for select statements, 
	 * 	otherwise the number of rows affected.
	 * @throws RuntimeException
	 */
	public function exec()
	{
		if(!DB::isConnected()) {
			throw new \RuntimeException("Query: No valid DB connection");
		}
		$query = $this->getQuery();
		if(empty($query)) {
			return array();
		}
		$pdo = DB::getPDO();
		if($this->_command === "select") {
			// return the result set
			$results = array();
			foreach($pdo->query($query, \PDO::FETCH_ASSOC) as $column) {
				$results[] = $column;
			}
			return $results;
		} else if($this->_command === "count") {
			// return the count
			$smt = $pdo->query($query, \PDO::FETCH_NUM);
			$row = $smt->fetch();
			return $row[0];
		}
		$this->reset();
		return $pdo->exec($query);
	}
	
	/**
	 * Executes the query and returns the first row in the result set.
	 *
	 * Note that this only works on SELECT queries and it will 
	 * automatically set the limit for the query to 1.
	 *
	 * @since 0.3.0
	 * @return array The first row of the result set.
	 * @throws RuntimeException
	 */
	public function first()
	{
		if($this->_command !== "select") {
			return array();
		}
		$this->_limit = 1;
		$results = $this->exec();
		return $results[0];
	}
	
	/**
	 * Resets the active query builder to its default states.
	 * Note that the selected table is not reset.
	 *
	 * @since 0.3.0
	 */
	public function reset()
	{
		$this->_rawQuery = null;
		$this->_command = null;
		$this->_select = array();
		$this->_updates = array();
		$this->_inserts = array();
		$this->_order_by = null;
		$this->_limit = 0;
		$this->_offset = 0;
		$this->_whereExpr = array();
		$this->_whereOps = array();
	}
	
	protected function _buildSelect()
	{
		$table = $this->_table;
		
		// command
		$sql = "SELECT ";
		
		
		// fields
		if($this->_command === "count") {
			$sql .= "COUNT(".$this->_formatFields().")";
		} else {
			$sql .= $this->_formatFields();
		}
		
		// table
		$sql .= " FROM ".$table;
		
		// where
		if(!empty($this->_whereExpr)) {
			$sql .= " WHERE ".$this->getWhere();
		}
		
		// order by
		if($this->_order_by) {
			$sql .= " ORDER BY ".$this->_order_by;
		}
		
		// limit
		if($this->_limit > 0) {
			$sql .= " LIMIT ".$this->_limit;
		}
		
		// offset
		if($this->_offset > 0) {
			$sql .= " OFFSET ".$this->_offset;
		}
		
		return $sql;
	}
	
	protected function _buildUpdate()
	{
		$pdo = DB::getPDO();
		$updates = array();
		foreach($this->_updates as $key => $value) {
			$value = $pdo->quote($value);
			$updates[] = $key." = ".$value;
		}
			
		$sql = sprintf("UPDATE %s SET %s", $this->_table, join(", ", $updates));
		
		// where
		if(!empty($this->_whereExpr)) {
			$sql .= " WHERE ".$this->getWhere();
		}
		
		return $sql;
	}
	
	protected function _buildInsert()
	{
		$pdo = DB::getPDO();
		
		$keys = array_keys($this->_inserts);
		$values = array_values($this->_inserts);
		
		// values
		foreach($values as $i => $val) {
			$values[$i] = $pdo->quote($val);
		}
			
		$sql = sprintf("INSERT INTO %s (%s) VALUES (%s)", $this->_table, join(", ", $keys), join(", ", $values));
		
		return $sql;
	}
	
	protected function _buildDelete()
	{
		$pdo = DB::getPDO();
		
		$sql = "DELETE FROM ".$this->_table;
		
		// where
		if(!empty($this->_whereExpr)) {
			$sql .= " WHERE ".$this->getWhere();
		}
		
		return $sql;
	}
	
	protected function _formatFields()
	{
		if(empty($this->_select)) {
			return "*";
		} 
		return join(",", $this->_select);
	}
}
?>