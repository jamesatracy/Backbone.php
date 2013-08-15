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
	public function __construct($rows)
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
	 * Fetch the current row of mock data.
	 *
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
 }
 
 ?>