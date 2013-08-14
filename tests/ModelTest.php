<?php
/**
 * Backbone.php
 * 
 * @author	James Tracy <james.a.tracy@gmail.com>
 * @copyright	2012-2013
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link https://github.com/jamesatracy/Backbone.php GitHub Page
 */
 
define('ABSPATH', dirname(__FILE__).'/../');
define('FRAMEWORK', ABSPATH.'backbone/');

require_once(FRAMEWORK.'Backbone.class.php');
Backbone::uses("Model");

/**
 * A mock model class for testing purposes.
 */
class MockModel extends Model
{
	public function __construct()
	{
		// automatically stamp created field
		$this->created = "created";
		// load a mock schema for testing
		$this->schemaFile = "tests/fixtures/model_test_fixture1.json";
		parent::__construct("table");
	}
}

/**
 * PHPUnit Test suite for Model class
 */
class ModelTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		Backbone::uses("/tests/MockDB");
		Connections::create("default", "MockDB", array("server" => "localhost", "user" => "root", "pass" => ""));
	}
	
	public function testDefaults()
	{
		$model = new MockModel();
		
		$this->assertEquals($model->get("first"), "");
		$this->assertEquals($model->get("last"), "");
		$this->assertEquals($model->get("age"), 13);
		$this->assertEquals($model->get("gender"), "Male");
	}
	
	// Test the has() attributes method
	public function testHasAttribute()
	{
		$model = new MockModel();
		
		$this->assertTrue($model->has("first"));
		$this->assertTrue($model->has("last"));
		$this->assertTrue($model->has("age"));
		$this->assertTrue($model->has("gender"));
		$this->assertFalse($model->has("xyz"));
	}
	
	public function testSetters()
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
	}
	
	// Test the change() attributes method
	public function testChangedAttributes()
	{
		$model = new MockModel();
		
		// On a new model, every field is marked as changed
		$model->set("first", "Tom");
		$this->assertTrue($model->changed("first"));
		$this->assertTrue($model->changed("last"));
		$this->assertTrue($model->changed("age"));
		$this->assertTrue($model->changed("gender"));
		
		// Clear the changed values
		$model->clearChanged();
		$model->set("first", "Tom");
		$this->assertFalse($model->changed("first"));	// was previously Tom
		$model->set("first", "John");
		$this->assertTrue($model->changed("first"));
		$this->assertFalse($model->changed("last"));
		
		// Invalid field name
		$this->assertFalse($model->changed("xyz"));	
	}
	
	// Test the hasChanged() method
	public function testHasChanged()
	{
		$model = new MockModel();
		$model->clearChanged();
		$this->assertFalse($model->hasChanged());
		$model->set("first", "John");
		$this->assertTrue($model->hasChanged());
	}
}
?>