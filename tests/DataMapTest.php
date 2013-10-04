<?php
/**
 * Backbone.php
 * 
 * @author	James Tracy <james.a.tracy@gmail.com>
 * @copyright	2012-2013
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link https://github.com/jamesatracy/Backbone.php GitHub Page
 */
 
Backbone::uses("DataMap");

/**
 * PHPUnit Test suite for DataMap class
 *
 * Tests for individual class methods following this naming
 * convention:
 *		public function testMethod_${name}
 *
 * Tests for general behavior following this naming 
 * conventions:
 * 		public function testBehavior_${description}
 */
class DataMapTest extends PHPUnit_Framework_TestCase
{
	public function testBehavior_SimpleKeys()
	{
		$set = new DataMap();
		
		// set a simple value
		$set->set("foo", "one");
		$this->assertEquals($set->get("foo"), "one");
		// change a simple value
		$set->set("foo", "two");
		$this->assertEquals($set->get("foo"), "two");
		// unset the same value
		$set->set("foo", null);
		$this->assertNull($set->get("foo"));
	}
	
	public function testBehavior_NestedKeys()
	{
		$set = new DataMap();
		
		// set a nested value
		$set->set("foo.homer", "simpson");
		$this->assertEquals($set->get("foo.homer"), "simpson");
		$this->assertTrue(is_array($set->get("foo")));
		$this->assertEquals($set->get("foo"), array("homer" => "simpson"));
		
		// change the nested value
		$set->set("foo.homer", "beer");
		$this->assertEquals($set->get("foo.homer"), "beer");
		$this->assertTrue(is_array($set->get("foo")));
		$this->assertEquals($set->get("foo"), array("homer" => "beer"));
		
		// unset the nested value
		$set->set("foo.homer", null);
		$this->assertNull($set->get("foo.homer"));
		
		// test three levels
		$set->set("one.two.three", "four");
		$this->assertEquals($set->get("one.two.three"), "four");
	}
}
?>