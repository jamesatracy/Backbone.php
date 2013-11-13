<?php
/**
 * Backbone.php
 * 
 * @author	James Tracy <james.a.tracy@gmail.com>
 * @copyright	2012-2013
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link https://github.com/jamesatracy/Backbone.php GitHub Page
 */

Backbone::uses("Model", "Collection", "/tests/helpers/MockModel", "/tests/helpers/MockCollection");

use Backbone\Model;
use Backbone\Collection;

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
	public function testMethod_constructor()
	{
		$collection = new MockCollection();
		$this->assertEquals($collection->length, 3);
	}
	
	public function testMethod_getAt()
	{
		$collection = new MockCollection();
		
		$model = $collection->getAt(0);
		$this->assertNotNull($model);
		$this->assertEquals(get_class($model), "MockModel");
		$this->assertEquals($model->get("ID"), 1);
		$this->assertEquals($model->get("first"), "John");
		
		$model = $collection->getAt(1);
		$this->assertNotNull($model);
		
		$model = $collection->getAt(2);
		$this->assertNotNull($model);
		
		// invalid index
		$model = $collection->getAt(3);
		$this->assertNull($model);
	}
	
	public function testBehavior_iterator()
	{
		$collection = new MockCollection();

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
		// next
		$next = $collection->next();
		$this->assertNotNull($next);
		$this->assertEquals(get_class($next), "MockModel");
		$this->assertEquals($collection->key(), 3);
		// is valid (we reach the end here)
		$this->assertFalse($collection->valid());
		// current
		$this->assertNull($collection->current());
		// rewind
		$collection->rewind();
		$this->assertEquals($collection->key(), 0);
	}
	
	public function testMethod_pluck()
	{
		$collection = new MockCollection();

		$attrs = $collection->pluck("first");
		$this->assertCount(3, $attrs);
		$this->assertEquals($attrs[0], "John");
		$this->assertEquals($attrs[1], "Jane");
		$this->assertEquals($attrs[2], "Jessie");
	}
	
	public function testMethod_getModelName()
	{
		$collection = new MockCollection();
		$this->assertEquals($collection->getModelName(), "MockModel");
	}
	
	
	public function testMethod_reset()
	{
		$collection = new MockCollection();

		$collection->reset();
		$this->assertEquals($collection->length, 0);
		$this->assertEmpty($collection->toJSON());
		$this->assertEmpty($collection->getErrors());
	}
}
?>