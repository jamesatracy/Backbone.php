<?php
/*
MySQLResult.class.php
Copyright (C) 2012 James Tracy

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/

/* 
@fileoverview
Wraps a mysql server result
*/
class MySQLResult
{
	const FETCH_ASSOC	= MYSQL_ASSOC;
	const FETCH_NUM		= MYSQL_NUM;
	const FETCH_BOTH	= MYSQL_BOTH;
	
	/* The mysql result resource */
	protected $_result = null;
	
	/* The fetch mode */
	protected $_fetch_mode = self::FETCH_ASSOC;
	
	/* 
	Constructor 
	
	@param [resource] $result The mysql result resource
	*/
	public function __construct($result)
	{
		$this->_result = $result;
	}
	
	/*
	Is this a valid result?
	
	@return [boolean] True if the result is valid
	*/
	public function isValid()
	{
		return ($this->_result != null && $this->_result != false);
	}
	
	/* 
	Get the num rows 
	
	@return [integer] The number of rows in the result set
	*/
	public function numRows()
	{
		if(!$this->isValid())
			return 0;
		if(is_bool($this->_result))
			return 0;
		return mysql_numrows($this->_result);
	}
	
	/* 
	Return the content of one cell
	
	@param [integer] $row The row number
	@param [string] $field The field name to fetch
	@return [mixed] The contents of the cell
	*/
	public function getField($row, $field)
	{
		if($this->_result == null)
			return null;
		return mysql_result($this->_result, $row, $field);
	}

	/*
	Fetch result row
	
	@param [constant] $mode The mysql fetch mode
	@return [array] A single row from the result set
	*/
	public function fetch($mode = null)
	{
		if($this->_result == null)
			return null;
		if($mode == null)
			$mode = $this->_fetch_mode;
		return mysql_fetch_array($this->_result, $mode);
	}
	
	/*
	Fetch all result rows
	
	@param [constant] $mode The mysql fetch mode
	@return [array] All rows from the result set
	*/
	public function fetchAll($mode = null)
	{
		if($this->_result == null)
			return null;
		$rows = array();
		while($current = $this->fetch($mode))
		{
			$rows[] = $current;
		}
		return $rows;
	}
	
	/* 
	Fetch result object
	
	@return [object] A single row from the result set, as an object
	*/
	public function fetchObject()
	{
		if($this->_result == null)
			return null;
		return mysql_fetch_object($this->_result);
	}
	
	/*
	Seek
	
	@param [integer] $row The row to seek to
	*/
	public function seek($row)
	{
		if($this->_result == null)
			return;
		mysql_data_seek($this->_result, $row);
	}
	
	/* 
	Set the fetch mode
	
	@param [constant] $mode The mysql fetch mode
	*/
	public function setFetchMode($mode)
	{
		$this->_fetch_mode = $mode;
	}
	
	/*
	Get the fetch mode
	
	@return [constant] The mysql fetch mode
	*/
	public function getFetchMode()
	{
		return $this->_fetch_mode;
	}
};

?>