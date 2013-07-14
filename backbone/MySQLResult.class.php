<?php
/**
 * Backbone.php
 * 
 * @author	James Tracy <james.a.tracy@gmail.com>
 * @copyright	2012-2013
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 */
 
/**
 * Wraps a MySQL result.
 *
 * @since 0.1.0
 */
class MySQLResult
{
	const FETCH_ASSOC	= MYSQL_ASSOC;
	const FETCH_NUM		= MYSQL_NUM;
	const FETCH_BOTH	= MYSQL_BOTH;
	
	/** @var result The mysql result resource */
	protected $_result = null;
	
	/** @var int The fetch mode */
	protected $_fetch_mode = self::FETCH_ASSOC;
	
	/**
	 * Constructor 
	 * 
	 * @since 0.1.0
	 * @constructor
	 * @param resource $result The mysql result resource
	 */
	public function __construct($result)
	{
		$this->_result = $result;
	}
	
	/**
	 * Is this a valid result?
	 *
	 * @since 0.1.0
	 * @return bool True if the result is valid
	 */
	public function isValid()
	{
		return ($this->_result != null && $this->_result != false);
	}
	
	/**
	 * Get the num rows 
	 *
	 * @since 0.1.0
	 * @return int The number of rows in the result set
	 */
	public function numRows()
	{
		if(!$this->isValid()) {
			return 0;
		}
		if(is_bool($this->_result)) {
			return 0;
		}
		return mysql_numrows($this->_result);
	}
	
	/**
	 * Return the content of one cell
	 *
	 * @since 0.1.0
	 * @param int $row The row number
	 * @param string $field The field name to fetch
	 * @return mixed The contents of the cell
	 */
	public function getField($row, $field)
	{
		if($this->_result == null) {
			return null;
		}
		return mysql_result($this->_result, $row, $field);
	}

	/**
	 * Fetch result row
	 *
	 * @since 0.1.0
	 * @param int $mode The mysql fetch mode
	 * @return [array] A single row from the result set
	 */
	public function fetch($mode = null)
	{
		if($this->_result == null) {
			return null;
		}
		if($mode == null) {
			$mode = $this->_fetch_mode;
		}
		return mysql_fetch_array($this->_result, $mode);
	}
	
	/**
	 * Fetch all result rows
	 *
	 * @since 0.1.0
	 * @param int $mode The mysql fetch mode
	 * @return array All rows from the result set
	 */
	public function fetchAll($mode = null)
	{
		if($this->_result == null) {
			return null;
		}
		$rows = array();
		while($current = $this->fetch($mode)) {
			$rows[] = $current;
		}
		return $rows;
	}
	
	/**
	 * Fetch result object
	 *
	 * @since 0.1.0
	 * @return object A single row from the result set, as an object
	 */
	public function fetchObject()
	{
		if($this->_result == null) {
			return null;
		}
		return mysql_fetch_object($this->_result);
	}
	
	/**
	 * Seek
	 *
	 * @since 0.1.0
	 * @param int $row The row to seek to
	 */
	public function seek($row)
	{
		if($this->_result == null) {
			return;
		}
		mysql_data_seek($this->_result, $row);
	}
	
	/**
	 * Set the fetch mode
	 *
	 * @since 0.1.0
	 * @param int $mode The mysql fetch mode
	 */
	public function setFetchMode($mode)
	{
		$this->_fetch_mode = $mode;
	}
	
	/**
	 * Get the fetch mode
	 *
	 * @since 0.1.0
	 * @return int The mysql fetch mode
	 */
	public function getFetchMode()
	{
		return $this->_fetch_mode;
	}
};

?>