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
	 * Limited functionality.
	 *
	 * @return MockDatabaseResult
	 */
	public function select($table, $fields, $options = array())
	{
		if(empty($this->data)) {
			return new MockDatabaseResult(array());
		}
		
		if(!is_array($fields)) {
			if($fields === "*") {
				return $this->selectAll($table, $options);
			}
			$fields = array($fields);
		}
		
		if(!isset($options['where'])) {
			// everything
			$results = array();
			foreach($this->data as $i => $row) {
				$cur = array();
				foreach($fields as $name) {
					$cur[$name] = $row[$name];
				}
				$results[] = $cur;
			}
			return new MockDatabaseResult($results);
		}
		
		$where = $options['where'];
		
		// use the first where condition
		$keys = array_keys($where);
		$key = $keys[0];
		$values = array_values($where);
		$value = $values[0];
		
		$results = array();
		foreach($this->data as $i => $row) {
			if($row[$key] == $value) {
				$cur = array();
				foreach($fields as $name) {
					$cur[$name] = $row[$name];
				}
				$results[] = $cur;
			}
		}
		return new MockDatabaseResult($results);
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
		
		if(!isset($options['where'])) {
			// everything
			$results = array();
			foreach($this->data as $i => $row) {
				$results[] = $row;
			}
			return new MockDatabaseResult($results);
		}
		
		$where = $options['where'];
		
		// use the first where condition
		$keys = array_keys($where);
		$key = $keys[0];
		$values = array_values($where);
		$value = $values[0];
		
		$results = array();
		foreach($this->data as $i => $row) {
			if($row[$key] == $value) {
				$results[] = $row;
			}
		}
		return new MockDatabaseResult($results);
	}
}
?>