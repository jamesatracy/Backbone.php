<?php
/**
 * Backbone.php
 * 
 * @author	James Tracy <james.a.tracy@gmail.com>
 * @copyright	2012-2013
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link https://github.com/jamesatracy/Backbone.php GitHub Page
 */
 
Backbone::uses(array("DataSource", "/tests/helpers/MockDatabaseResult"));

/**
 * Mock database class for testing db functions without a live db.
 *
 * For code the requires a live database connection, you can use
 * this mock database. It will always report that the connection
 * is "connected" regardless of whether or not you need to fetch
 * or update data in your tests.
 *
 * Q: How does this work, exactly?
 *
 * A: This class allows you to fake database results by passing your
 * data into the MockDatabase class prior to your db call. When
 * data is fetched through MockDatabase, the class will return
 * your data set exactly as is through a MockDatabaseResult object.
 *
 * 	$db = new MockDatabase();
 *	$db->setResultsData(array(
 *			array("first" => "John", "last" => "Doe")
 *		));
 *	$result = $db->select("blah", array());
 *		// the parameters to select() do not matter...
 *  $row = $result->fetch();
 *		// $row = array("first" => "John", "last" => "Doe")
 *
 *  NOTE that you can pass an array of arrays to MockDatabase and
 *	thereby simulate fetching multiple rows of data.
 */
class MockDatabase extends DataSource
{
	/** @var array Holds the mock data */
	protected $data = array();
	
	/** 
	 * @var string Indicates the last db method that was called:
	 * 	select, insert, update, delete
	 */
	protected $methodCalled = "";
	
	/** @var null|string Holds the last error message */
	protected $lastError = null;
	
	/**
	 * Set the mock results data. 
	 *
	 * @param array $data 
	 */
	public function setResultsData($data)
	{
		$this->data = $data;
	}
	
	/**
	 * Get the mock results data.
	 *
	 * @return array The mock results data.
	 */
	public function getResultsData()
	{
		return $this->data;
	}
	
	/** 
	 * Get the last method called (select, insert, update, or delete)
	 *
	 * @return string The method name
	 */
	public function getMethodCalled()
	{
		return $this->methodCalled;
	}
	
	/**
	 * The mock database is always connected!
	 */
	public function isConnected()
	{
		return true;
	}
	
	/** 
	 * Get the last error.
	 *
	 * You can simulate an error condition by setting last error to
	 * a string value. Otherwise, it should be set to null.
	 *
	 * @return null|string The last error
	 */
	public function getError()
	{
		return $this->lastError;
	}
	
	/**
	 * Set the last error. See above.
	 */
	public function setError($msg)
	{
		$this->lastError = $msg;
	}
	
	/**
	 * Returns the last insert ID, which is always 1.
	 *
	 * @return int The last insert ID (1)
	 */
	public function lastInsertID()
	{
		return 1;
	}
	
	/**
	 * Fetch data from the mock database
	 *
	 * @return MockDatabaseResult
	 */
	public function select($table, $fields, $options = array())
	{
		$this->methodCalled = "select";
		if(empty($this->data)) {
			return new MockDatabaseResult(array());
		}
		return new MockDatabaseResult($this->data);
	}
	
	/**
	 * Select all fields
	 *
	 * @return MockDatabaseResult
	 */
	public function selectAll($table, $options)
	{
		$this->methodCalled = "select";
		if(empty($this->data)) {
			return new MockDatabaseResult(array());
		}
		return new MockDatabaseResult($this->data);
	}
	
	/**
	 * Wrapper for calling select with COUNT(*). See select() for description
	 *
	 * @return int The number of rows
	 */
	public function count($table, $options)
	{
		$this->methodCalled = "select";
		return count($this->data);
	}
	
	/**
	 * Simulate an insert.
	 *
	 * This works by simply overwriting the mock data set with the 
	 * data that was passed to insert. This way you can retrieve
	 * these values and verify that the correct dataset was passed
	 * to the db insert call.
	 *
	 * @param string $table The table name
	 * @param array $fields An array of key => value pairs to insert
	 * @return MockDatabaseResult
	 */
	public function insert($table, $fields)
	{
		$this->methodCalled = "insert";
		$this->data = array($fields);
		return new MockDatabaseResult(array());
	}
	
	/**
	 * Simulate an update.
	 *
	 * This works by simply overwriting the mock data set with the 
	 * data that was passed to update. This way you can retrieve
	 * these values and verify that the correct dataset was passed
	 * to the db update call.
	 *
	 * @param string $table The table name
	 * @param array $fields An array of key => value pairs to update
	 * @param array $options An array of options, such as a where clause
	 * @return MockDatabaseResult
	 */
	public function update($table, $fields)
	{
		$this->methodCalled = "update";
		$this->data = array($fields);
		return new MockDatabaseResult(array());
	}
	
	/** 
	 * Simulate a delete.
	 *
	 * This doesn't actually do anything but clear the results data
	 * and set the method called to "delete".
	 *
	 * @param string $table The table name
	 * @param array $options An array of options, such as a where clause
	 * @return MockDatabaseResult
	 */
	public function delete($table, $options)
	{
		$this->methodCalled = "delete";
		return new MockDatabaseResult(array());
	}
}
?>