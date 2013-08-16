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
 *	$db->setData(array("first" => "John", "last" => "Doe"));
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
	protected $data = array();
	
	/**
	 * Set the mock data. 
	 *
	 * @param array $data An array of data arrays.
	 */
	public function setData($data)
	{
		$this->data = $data;
	}
	
	/**
	 * The mock database is always connected!
	 */
	public function isConnected()
	{
		return true;
	}
	
	/**
	 * Fetch data from the mock database
	 *
	 * @return MockDatabaseResult
	 */
	public function select($table, $fields, $options = array())
	{
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
		if(empty($this->data)) {
			return new MockDatabaseResult(array());
		}
		return new MockDatabaseResult($this->data);
	}
}
?>