<?php
/**
 * Backbone.php
 * 
 * @author	James Tracy <james.a.tracy@gmail.com>
 * @copyright	2012-2013
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link https://github.com/jamesatracy/Backbone.php GitHub Page
 */
 
 /**
  * Mocks a database result object.
  *
  * Basically, this allows you to fake a database result by passing
  * in your mock results into the MockDatabaseResult constructor or
  * using the setMockData() method. 
  *
  * From that point onward, the
  * result object will work almost as you would normally expect it
  * to with real data (the exception being that switching fetch
  * modes will not make any differences - what you give it is what
  * it will return).
  */
 class MockDatabaseResult
 {
	/** @var array The mock result data */
	protected $rows = array();
	
	/** @var int The current fetch position */
	protected $current = 0;
	
	/**
	 * Constructor
	 *
	 * @constructor
	 * @param array $rows The mock result data
	 */
	public function __construct($rows = array())
	{
		$this->setMockData($rows);
	}
	
	/**
	 * Set the mock result data
	 * 
	 * @param array $rows The mock result data
	 */
	public function setMockData($rows)
	{
		$this->rows = $rows;
		$this->current = 0;
	}
	
	/**
	 * Is the result valid? In this case, the result is always valid.
	 *
	 * @return boolean True.
	 */
	public function isValid()
	{
		return true;
	}
	
	/**
	 * Number of rows of mock data.
	 *
	 * @return int
	 */
	public function numRows()
	{
		return count($this->rows);
	}
	
	/**
	 * Return the content of one cell
	 *
	 * @param int $row The row number
	 * @param string $field The field name to fetch
	 * @return mixed The contents of the cell
	 */
	public function getField($row, $field)
	{
		$results = null;
		if(isset($this->rows[$this->current])) {
			$row = $this->rows[$this->current];
			if(isset($row[$field])) {
				$results = $row[$field];
			}
		}
		return $results;
	}
	
	/**
	 * Fetch the current row of mock data.
	 * 
	 * @param int $mode Ignored
	 * @return array
	 */
	public function fetch($mode = null)
	{
		$results = null;
		if(isset($this->rows[$this->current])) {
			$results = $this->rows[$this->current];
			$this->current++;
		}
		return $results;
	}
	
	/**
	 * Fetch all result rows
	 *
	 * @param int $mode Ignored
	 * @return array All rows from the result set
	 */
	public function fetchAll($mode = null)
	{
		return $this->rows;
	}
	
	/**
	 * Fetch result object
	 *
	 * @return object A single row from the result set, as an object
	 */
	public function fetchObject()
	{
		$row = $this->fetch();
		if($row) {
			$row = (object)$row;
		}
		return $row;
	}
	
	/**
	 * Seek
	 *
	 * @param int $row The row to seek to
	 */
	public function seek($row)
	{
		$this->current = $row;
	}
	
	/**
	 * Set the fetch mode. This is here only as a stub
	 *
	 * @param int $mode The mysql fetch mode
	 */
	public function setFetchMode($mode)
	{
		return;
	}
	
	/**
	 * Get the fetch mode. This is here only a stub.
	 *
	 * @since 0.1.0
	 * @return int The mysql fetch mode
	 */
	public function getFetchMode()
	{
		return;
	}
 }
 
 ?>