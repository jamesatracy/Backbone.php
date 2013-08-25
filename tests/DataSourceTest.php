<?php
/**
 * Backbone.php
 * 
 * @author	James Tracy <james.a.tracy@gmail.com>
 * @copyright	2012-2013
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link https://github.com/jamesatracy/Backbone.php GitHub Page
 */

require("setup.php");
Backbone::uses("DataSource");

// DataSource is abstract, so need a class extension
class MockDataSource extends DataSource
{
	public function __construct() {}
}

/**
 * PHPUnit Test suite for DataSource class
 *
 * Tests for individual class methods following this naming
 * convention:
 *		public function testMethod_${name}
 *
 * Tests for general behavior following this naming 
 * conventions:
 * 		public function testBehavior_${description}
 */
class DataSourceTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		$this->db = new MockDataSource();
	}

	public function testMethod_connect()
	{
		$this->db->connect(array());
		$this->assertTrue($this->db->isConnected());
	}
	
	public function testMethod_disconnect()
	{
		$this->db->connect(array());
		$this->db->disconnect(array());
		$this->assertFalse($this->db->isConnected());
	}
	
	public function testMethod_escape()
	{
		$string = "'this' string 'haz quotes'!!";
		$this->assertEquals($this->db->escape($string), addslashes($string));
	}
}
?>