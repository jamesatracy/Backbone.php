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
Backbone::uses("Router");

/**
 * Test router for testing.
 */
class TestRouter extends Router
{
	public $methodCalled = null;
	public $argsPassed = array();
	public $preMatchHookCalled = false;
	public $preRouteHookCalled = false;
	public $postRouteHookCalled = false;
	
	public function __construct()
	{
		$this->add(
			array(
				"/" => "indexRoute",
				"/path/" => "simplePathRoute",
				"/double/path/" => "doublePathRoute"
			)
		);
	}
	
	public function indexRoute()
	{
		$this->methodCalled = "indexRoute";
		$this->argsPassed = func_get_args();
	}
	
	public function simplePathRoute()
	{
		$this->methodCalled = "simplePathRoute";
		$this->argsPassed = func_get_args();
	}
	
	public function doublePathRoute()
	{
		$this->methodCalled = "doublePathRoute";
		$this->argsPassed = func_get_args();
	}
	
	public function onPreMatchHook($url)
	{
		$this->preMatchHookCalled = true;
		return true;
	}
	
	public function onPreRouteHook($url)
	{
		$this->preRouteHookCalled = true;
		return true;
	}
	
	public function onPostRouteHook($response)
	{
		$this->postRouteHookCalled = true;
		return true;
	}
}

/**
 * PHPUnit Test suite for Router class
 *
 * Tests for individual class methods following this naming
 * convention:
 *		public function testMethod_${name}
 *
 * Tests for general behavior following this naming 
 * conventions:
 * 		public function testBehavior_${description}
 */
class RouterTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		Backbone::$root = "/";
		$this->router = new TestRouter();
	}
	
	/** Testing route behaviors */
	public function testBehavior_indexRoute()
	{
		$_SERVER['REQUEST_URI'] = "/";
		$this->assertTrue($this->router->route());
		$this->assertEquals($this->router->methodCalled, "indexRoute");
		$this->assertEmpty($this->router->argsPassed);
		
		// test with root == /path/
		Backbone::$root = "/path/";
		$_SERVER['REQUEST_URI'] = "/path/";
		$this->assertTrue($this->router->route());
		$this->assertEquals($this->router->methodCalled, "indexRoute");
		$this->assertEmpty($this->router->argsPassed);
	}
	
	public function testBehavior_simplePathRoute()
	{
		$_SERVER['REQUEST_URI'] = "/path/";
		$this->assertTrue($this->router->route());
		$this->assertEquals($this->router->methodCalled, "simplePathRoute");
		$this->assertEmpty($this->router->argsPassed);
	}
	
	/** Testing route hook methods */
	public function testMethod_onPreMatchHook()
	{
		$_SERVER['REQUEST_URI'] = "/";
		$this->router->route()
		$this->assertTrue($this->router->preMatchHookCalled);
	}
	
	public function testMethod_onPreRouteHook()
	{
		$_SERVER['REQUEST_URI'] = "/";
		$this->router->route()
		$this->assertTrue($this->router->preRouteHookCalled);
	}
	
	public function testMethod_onPostRouteHook()
	{
		$_SERVER['REQUEST_URI'] = "/";
		$this->router->route()
		$this->assertTrue($this->router->postRouteHookCalled);
	}
}
?>