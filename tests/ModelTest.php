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
		$this->schemaFile = "tests/fixtures/model_test_fixture.json";
		parent::__construct("table");
	}
}

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
		Backbone::uses("/tests/helpers/MockDatabase");
		$this->db = Connections::create("default", "MockDatabase", array("server" => "localhost", "user" => "root", "pass" => ""));
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
		$model = new MockModel();
		
		$this->db->setData(array(
			"ID" => 1,
			"first" => "John",
			"last" => "Doe",
			"age" => 21,
			"gender" => "Male",
			"modified" => "0000-00-00 00:00:00",
			"created" => "0000-00-00 00:00:00"
		));
		$this->assertTrue($model->fetch(1));
		$this->assertEquals($model->ID, 1);
		$this->assertEquals($model->first, "John");
		$this->assertEquals($model->last, "Doe");
		$this->assertEquals($model->age, 21);
		$this->assertEquals($model->gender, "Male");
	}
	
	// Test the clearChanged() method
	public function testMethod_clearChanged()
	{
		$model = new MockModel();
		
		$model->clearChanged();
		$this->assertEquals(count($model->changedAttributes()), 0);
		$model->set("first", "Tom");
		$this->assertEquals(count($model->changedAttributes()), 1);
		$model->clearChanged();
		$this->assertEquals(count($model->changedAttributes()), 0);
	}
	
	// Test the changedAttributes() method
	public function testMethod_changedAttributes()
	{
		$model = new MockModel();
		
		$model->clearChanged();
		$this->assertEquals(count($model->changedAttributes()), 0);
		$model->set("first", "Tom");
		$changed = $model->changedAttributes();
		$this->assertTrue(isset($changed['first']));
	}
	
	// Test the change() attributes method
	public function testMethod_changed()
	{
		$model = new MockModel();
		
		// On a new model, every field is marked as changed
		$model->set("first", "Tom");
		$this->assertTrue($model->changed("first"));
		$this->assertTrue($model->changed("last"));
		$model->clearChanged();
		// set the first name to the same value, no changed
		$model->set("first", "Tom");
		$this->assertFalse($model->changed("first"));
		// set the first name to a new value, triggers changed
		$model->set("first", "John");
		$this->assertTrue($model->changed("first"));
		// test an attribute that has not changed
		$this->assertFalse($model->changed("last"));
		
		// Invalid field name
		$this->assertFalse($model->changed("xyz"));	
	}
	
	// Test the hasChanged() method
	public function testMethod_hasChanged()
	{
		$model = new MockModel();
		$model->clearChanged();
		$this->assertFalse($model->hasChanged());
		$model->set("first", "John");
		$this->assertTrue($model->hasChanged());
	}
}
?>