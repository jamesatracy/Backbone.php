<?php
/**
 * Backbone.php
 * 
 * @author	James Tracy <james.a.tracy@gmail.com>
 * @copyright	2012-2013
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link https://github.com/jamesatracy/Backbone.php GitHub Page
 */

Backbone::uses("Model", "/tests/helpers/MockDB", "/tests/helpers/MockModel");
use Backbone\Model;
use Backbone\DB;

/**
 * PHPUnit Test suite for Model class
 *
 * Tests for individual class methods following this naming
 * convention:
 *		public function testMethod_${name}
 *
 * Tests for general behavior following this naming 
 * conventions:
 * 		public function testBehavior_${description}
 */
class ModelTest extends PHPUnit_Framework_TestCase
{
	protected $db = null;
	
	public function setUp()
	{
		DB::connect("", "", "");
	}
	
	public function testBehavior_DefaultValues()
	{
		$model = new MockModel();
		
		$this->assertEquals($model->get("first"), "");
		$this->assertEquals($model->get("last"), "");
		$this->assertEquals($model->get("age"), 13);
		$this->assertEquals($model->get("gender"), "Male");
	}
	
	// Test the has() attributes method
	public function testMethod_has()
	{
		$model = new MockModel();
		
		$this->assertTrue($model->has("first"));
		$this->assertTrue($model->has("last"));
		$this->assertTrue($model->has("age"));
		$this->assertTrue($model->has("gender"));
		$this->assertFalse($model->has("xyz"));
	}
	
	public function testMethod_get()
	{
		$model = new MockModel();
		
		$model->first = "Tom";
		$this->assertEquals($model->get("first"), "Tom");
		
		// invalid field
		$this->assertNull($model->get("xyz"));
	}
	
	public function testMethod_getAttributes()
	{
		$model = new MockModel();
		
		$attrs = $model->getAttributes();
		$this->assertArrayHasKey("ID", $attrs);
		$this->assertArrayHasKey("first", $attrs);
		$this->assertArrayHasKey("last", $attrs);
		$this->assertArrayHasKey("age", $attrs);
		$this->assertArrayHasKey("gender", $attrs);
		$this->assertArrayHasKey("modified", $attrs);
		$this->assertArrayHasKey("created", $attrs);
		
		// test includes param
		$attrs = $model->getAttributes(array("first", "last"));
		$this->assertArrayHasKey("first", $attrs);
		$this->assertArrayHasKey("last", $attrs);
		$this->assertFalse(isset($attrs['ID']));
	}
	
		public function testMethod_set()
	{
		$model = new MockModel();
		
		// Basic setters and getters
		$model->set("first", "Tom");
		$this->assertEquals($model->get("first"), "Tom");
		$model->set("age", 13);
		$this->assertEquals($model->get("age"), 13);
		
		// Invalid field name
		$model->set("xyz", "foo");
		$this->assertNull($model->get("xyz"));
		
		// NULL case
		$model->set("gender", null);
		$this->assertEquals($model->get("gender"), "NULL");
	}
	
	// Test the isNew() method
	public function testMethod_isNew()
	{
		$model = new MockModel();
		
		$this->assertTrue($model->isNew());
		$model->set("ID", 1);
		$this->assertFalse($model->isNew());
	}
	
	// Test the fetch methdod
	public function testMethod_fetch()
	{
		$pdo = DB::getPDO();
		$pdo->setResultsData(array(
			array(
				"ID" => 1,
				"first" => "John",
				"last" => "Doe",
				"age" => 21,
				"gender" => "Male",
				"modified" => "0000-00-00 00:00:00",
				"created" => "0000-00-00 00:00:00"
			)
		));
// 		$model = MockModel::fetch(1);
// 		$this->assertNotNull($model);
// 		$this->assertEquals($model->ID, 1);
// 		$this->assertEquals($model->first, "John");
// 		$this->assertEquals($model->last, "Doe");
// 		$this->assertEquals($model->age, 21);
// 		$this->assertEquals($model->gender, "Male");
		
		// simulate no result
		$pdo->setResultsData(array());
		$this->assertNull(MockModel::fetch(1));
		
		// simulate invalid result
		$pdo->setResultsData(null);
		$this->assertNull(MockModel::fetch(1));
	}
}
?>