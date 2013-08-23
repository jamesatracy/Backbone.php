<?php
/**
 * Backbone.php
 * 
 * @author	James Tracy <james.a.tracy@gmail.com>
 * @copyright	2012-2013
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link https://github.com/jamesatracy/Backbone.php GitHub Page
 */

if(!defined('ABSPATH')) 
	define('ABSPATH', dirname(__FILE__).'/../');
if(!defined('FRAMEWORK'))
	define('FRAMEWORK', ABSPATH.'backbone/');

require_once(FRAMEWORK.'Backbone.class.php');
Backbone::uses(array("Model", "Collection", "/tests/helpers/MockCollection"));

/**
 * PHPUnit Test suite for Collection class
 *
 * Tests for individual class methods following this naming
 * convention:
 *		public function testMethod_${name}
 *
 * Tests for general behavior following this naming 
 * conventions:
 * 		public function testBehavior_${description}
 */
class CollectionTest extends PHPUnit_Framework_TestCase
{
	protected $db = null;
	
	public function setUp()
	{
		Backbone::uses("/tests/helpers/MockDatabase");
		$this->db = Connections::create("default", "MockDatabase", array("server" => "localhost", "user" => "root", "pass" => ""));
	}
	
	public function testMethod_fetch()
	{
		$collection = new MockCollection();
		
		$this->db->setResultsData(json_decode(file_get_contents(ABSPATH."tests/fixtures/collection_test_fixture.json"), TRUE));
		
		$this->assertTrue($collection->fetch());
		$this->assertEquals($collection->length, 3);
	}
	
	public function testMethod_get()
	{
		$collection = new MockCollection();
		
		$this->db->setResultsData(json_decode(file_get_contents(ABSPATH."tests/fixtures/collection_test_fixture.json"), TRUE));
		
		$this->assertTrue($collection->fetch());
		
		$model = $collection->get(1);
		$this->assertNotNull($model);
		$this->assertEquals(get_class($model), "MockModel");
		$this->assertEquals($model->get("ID"), 1);
		$this->assertEquals($model->get("first"), "John");
		
		$model = $collection->get(2);
		$this->assertNotNull($model);
		
		$model = $collection->get(3);
		$this->assertNotNull($model);
		
		// invalid ID
		$model = $collection->get(4);
		$this->assertNull($model);
	}
	
	public function testBehavior_iterator()
	{
		$collection = new MockCollection();

		$this->db->setResultsData(json_decode(file_get_contents(ABSPATH."tests/fixtures/collection_test_fixture.json"), TRUE));

		$this->assertTrue($collection->fetch());
		// starts at position 0
		$this->assertEquals($collection->key(), 0);
		// is valid
		$this->assertTrue($collection->valid());
		// current
		$this->assertNotNull($collection->current());
		$this->assertEquals(get_class($collection->current()), "MockModel");
		// next
		$next = $collection->next();
		$this->assertNotNull($next);
		$this->assertEquals(get_class($next), "MockModel");
		$this->assertEquals($collection->key(), 1);
		// is valid
		$this->assertTrue($collection->valid());
		// current
		$this->assertNotNull($collection->current());
		$this->assertEquals(get_class($collection->current()), "MockModel");
		// next
		$next = $collection->next();
		$this->assertNotNull($next);
		$this->assertEquals(get_class($next), "MockModel");
		$this->assertEquals($collection->key(), 2);
		// is valid
		$this->assertTrue($collection->valid());
		// current
		$this->assertNotNull($collection->current());
		$this->assertEquals(get_class($collection->current()), "MockModel");
		// next (we reach the end here)
		$next = $collection->next();
		$this->assertNull($next);
		$this->assertFalse($collection->valid());
		// rewind
		$collection->rewind();
		$this->assertEquals($collection->key(), 0);
	}
	
	public function testMethod_pluck()
	{
		$collection = new MockCollection();

		$this->db->setResultsData(json_decode(file_get_contents(ABSPATH."tests/fixtures/collection_test_fixture.json"), TRUE));

		$this->assertTrue($collection->fetch());
		$attrs = $collection->pluck("first");
		$this->assertCount(3, $attrs);
		$this->assertEquals($attrs[0], "John");
		$this->assertEquals($attrs[1], "Jane");
		$this->assertEquals($attrs[2], "Jessie");
	}
	
	public function testMethod_getModelName()
	{
		$collection = new MockCollection();

		$this->db->setResultsData(json_decode(file_get_contents(ABSPATH."tests/fixtures/collection_test_fixture.json"), TRUE));
		
		$this->assertEquals($collection->getModelName(), "/tests/helpers/MockModel");
	}
	
	public function testMethod_getTableName()
	{
		$collection = new MockCollection();

		$this->db->setResultsData(json_decode(file_get_contents(ABSPATH."tests/fixtures/collection_test_fixture.json"), TRUE));
		
		$this->assertEquals($collection->getTableName(), "table");
	}
	
	public function testMethod_reset()
	{
		$collection = new MockCollection();

		$this->db->setResultsData(json_decode(file_get_contents(ABSPATH."tests/fixtures/collection_test_fixture.json"), TRUE));

		$this->assertTrue($collection->fetch());
		$collection->reset();
		$this->assertEquals($collection->length, 0);
		$this->assertEmpty($collection->toJSON());
		$this->assertEmpty($collection->getErrors());
	}
}
?>