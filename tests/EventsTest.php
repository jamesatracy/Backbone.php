<?php
/**
 * Backbone.php
 * 
 * @author	James Tracy <james.a.tracy@gmail.com>
 * @copyright	2012-2013
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link https://github.com/jamesatracy/Backbone.php GitHub Page
 */

Backbone::uses("Events");

/**
 * PHPUnit Test suite for Events class
 *
 * Tests for individual class methods following this naming
 * convention:
 *		public function testMethod_${name}
 *
 * Tests for general behavior following this naming 
 * conventions:
 * 		public function testBehavior_${description}
 */
class EventsTest extends PHPUnit_Framework_TestCase
{
	protected $firstCallbackCalled = false;
	protected $secondCallbackCalled = false;
	
	public function setUp()
	{
		$this->firstCallbackCalled = false;
		$this->secondCallbackCalled = false;
	}

	public function testBehavior_singleEventBinding()
	{
		Events::bind("test.single.event", array($this, "onFirstEventTriggered"));
		Events::trigger("different.event");
		$this->assertFalse($this->firstCallbackCalled);
		Events::trigger("test.single.event");
		$this->assertTrue($this->firstCallbackCalled);
	}
	
	public function testBehavior_multipleEventBinding()
	{
		Events::bind("test.multiple.event", array($this, "onFirstEventTriggered"));
		Events::bind("test.multiple.event", array($this, "onSecondEventTriggered"));
		Events::trigger("different.event");
		$this->assertFalse($this->firstCallbackCalled);
		$this->assertFalse($this->secondCallbackCalled);
		Events::trigger("test.multiple.event");
		$this->assertTrue($this->firstCallbackCalled);
		$this->assertTrue($this->secondCallbackCalled);
	}
	
	public function testBehavior_singleEventUnBinding()
	{
		Events::unbind("test.single.event");
		Events::trigger("test.single.event");
		$this->assertFalse($this->firstCallbackCalled);
	}
	
	public function testBehavior_multipleEventUnBinding()
	{
		Events::unbind("test.multiple.event");
		Events::trigger("test.multiple.event");
		$this->assertFalse($this->firstCallbackCalled);
		$this->assertFalse($this->secondCallbackCalled);
	}
	
	public function onFirstEventTriggered()
	{
		$this->firstCallbackCalled = true;
	}
	
	public function onSecondEventTriggered()
	{
		$this->secondCallbackCalled = true;
	}
}
?>