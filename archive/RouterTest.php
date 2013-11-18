<?php
/**
 * Backbone.php
 * 
 * @author	James Tracy <james.a.tracy@gmail.com>
 * @copyright	2012-2013
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link https://github.com/jamesatracy/Backbone.php GitHub Page
 */

Backbone::uses("Events", "Router", "Request", "View");

/**
 * Test View class
 */
class TestView extends View
{
	public $test = true;
}

/**
 * Test router for testing.
 */
class TestRouter extends Router
{
	public $methodCalled = false;
	public $argsPassed = array();
	public $preMatchHookCalled = false;
	public $preRouteHookCalled = false;
	public $postRouteHookCalled = false;
	
	public function __construct()
	{
		$this->add(
			array(
				"/" => "onRoute",
				"/path/" => "onRoute",
				"/double/path/" => "onRoute",
				"/test-number-param/:number/" => "onRoute",
				"/test-alpha-param/:alpha/" => "onRoute",
				"/test-alphanum-param/:alphanum/" => "onRoute"
			)
		);
	}
	
	public function onRoute()
	{
		$this->methodCalled = true;
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
	
	public function getView()
	{
		return $this->view;
	}
	
	protected function createView()
	{
		return new TestView();
	}
}

/**
 * Test router for testing.
 */
class TestMethodRouter extends Router
{
	public $methodCalled = false;
	public $argsPassed = array();
	
	public function __construct()
	{
		$this->add(
			array(
				"/path/" => array(
				    "GET" => "onRoute"
				),
				"/another/path/" => array(
				    "PUT" => "onRoute" ,
				    "DELETE" => "onRoute"
				)
			)
		);
		
		$this->post("/a/path/to/post/", "onRoute");
	}
	
	public function onRoute()
	{
		$this->methodCalled = true;
		$this->argsPassed = func_get_args();
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
		$this->assertEquals($this->router->getMatchedPattern(), "/");
		$this->assertTrue($this->router->methodCalled);
		$this->assertEmpty($this->router->argsPassed);
		
		// test with root == /path/
		Backbone::$root = "/path/";
		$_SERVER['REQUEST_URI'] = "/path/";
		$this->assertTrue($this->router->route());
		$this->assertEquals($this->router->getMatchedPattern(), "/");
		$this->assertTrue($this->router->methodCalled);
		$this->assertEmpty($this->router->argsPassed);
		
		// test custom view class instantiated
		$this->assertEquals(get_class($this->router->getView()), "TestView");
	}
	
	public function testBehavior_simplePathRoute()
	{
		$_SERVER['REQUEST_URI'] = "/path/";
		$this->assertTrue($this->router->route());
		$this->assertEquals($this->router->getMatchedPattern(), "/path/");
		$this->assertTrue($this->router->methodCalled);
		$this->assertEmpty($this->router->argsPassed);
	}
	
	public function testBehavior_doublePathRoute()
	{
		$_SERVER['REQUEST_URI'] = "/double/path/";
		$this->assertTrue($this->router->route());
		$this->assertEquals($this->router->getMatchedPattern(), "/double/path/");
		$this->assertTrue($this->router->methodCalled);
		$this->assertEmpty($this->router->argsPassed);
	}
	
	public function testBehavior_numberParamRoute()
	{
		$_SERVER['REQUEST_URI'] = "/test-number-param/12/";
		$this->assertTrue($this->router->route());
		$this->assertEquals($this->router->getMatchedPattern(), "/test-number-param/:number/");
		$this->assertTrue($this->router->methodCalled);
		$this->assertCount(1, $this->router->argsPassed);
		$this->assertEquals($this->router->argsPassed[0], 12);
		
		$_SERVER['REQUEST_URI'] = "/test-number-param/abc/";
		$this->assertFalse($this->router->route());
	}
	
	public function testBehavior_alphaParamRoute()
	{
		$_SERVER['REQUEST_URI'] = "/test-alpha-param/abc/";
		$this->assertTrue($this->router->route());
		$this->assertEquals($this->router->getMatchedPattern(), "/test-alpha-param/:alpha/");
		$this->assertTrue($this->router->methodCalled);
		$this->assertCount(1, $this->router->argsPassed);
		$this->assertEquals($this->router->argsPassed[0], "abc");
		
		$_SERVER['REQUEST_URI'] = "/test-alpha-param/12/";
		$this->assertFalse($this->router->route());
	}
	
	public function testBehavior_alphanumParamRoute()
	{
		$_SERVER['REQUEST_URI'] = "/test-alphanum-param/abc/";
		$this->assertTrue($this->router->route());
		$this->assertEquals($this->router->getMatchedPattern(), "/test-alphanum-param/:alphanum/");
		$this->assertTrue($this->router->methodCalled);
		$this->assertCount(1, $this->router->argsPassed);
		$this->assertEquals($this->router->argsPassed[0], "abc");
		
		$_SERVER['REQUEST_URI'] = "/test-alphanum-param/12/";
		$this->router->methodCalled = false;
		$this->assertTrue($this->router->route());
		$this->assertEquals($this->router->getMatchedPattern(), "/test-alphanum-param/:alphanum/");
		$this->assertTrue($this->router->methodCalled);
		$this->assertCount(1, $this->router->argsPassed);
		$this->assertEquals($this->router->argsPassed[0], "12");
		
		$_SERVER['REQUEST_URI'] = "/test-alphanum-param/abc12/";
		$this->router->methodCalled = false;
		$this->assertTrue($this->router->route());
		$this->assertEquals($this->router->getMatchedPattern(), "/test-alphanum-param/:alphanum/");
		$this->assertTrue($this->router->methodCalled);
		$this->assertCount(1, $this->router->argsPassed);
		$this->assertEquals($this->router->argsPassed[0], "abc12");
	}
	
	public function testBehavior_httpMethodRoutes()
	{
	    $router = new TestMethodRouter(); 
		$_SERVER['REQUEST_URI'] = "/path/";
		$_SERVER['REQUEST_METHOD'] = "GET";
		$this->assertTrue($router->route());
		$this->assertEquals($router->getMatchedPattern(), "/path/");
		$this->assertTrue($router->methodCalled);
		$this->assertEmpty($router->argsPassed);
		
		$_SERVER['REQUEST_METHOD'] = "POST";
		$this->assertTrue($router->route());
		$resp = $router->getResponse();
		$this->assertEquals($resp->status(), 405);
		
		$_SERVER['REQUEST_URI'] = "/another/path/";
		$_SERVER['REQUEST_METHOD'] = "PUT";
		$this->assertTrue($router->route());
		$this->assertEquals($router->getMatchedPattern(), "/another/path/");
		$this->assertTrue($router->methodCalled);
		$this->assertEmpty($router->argsPassed);
		
		$_SERVER['REQUEST_METHOD'] = "DELETE";
		$this->assertTrue($router->route());
		$this->assertEquals($router->getMatchedPattern(), "/another/path/");
		$this->assertTrue($router->methodCalled);
		$this->assertEmpty($router->argsPassed);
		
		$_SERVER['REQUEST_URI'] = "/unmatched/path/";
		$this->assertFalse($router->route());
		
		$_SERVER['REQUEST_URI'] = "/a/path/to/post/";
		$_SERVER['REQUEST_METHOD'] = "POST";
		$this->assertTrue($router->route());
		$this->assertEquals($router->getMatchedPattern(), "/a/path/to/post/");
		$this->assertTrue($router->methodCalled);
		$this->assertEmpty($router->argsPassed);
	}
	
	/** Testing route hook methods */
	public function testMethod_onPreMatchHook()
	{
		Events::bind("Router:before:match", array($this->router, "onPreMatchHook"));
		$_SERVER['REQUEST_URI'] = "/";
		$this->router->route();
		$this->assertTrue($this->router->preMatchHookCalled);
	}
	
	public function testMethod_onPreRouteHook()
	{
		Events::bind("Router:before:route", array($this->router, "onPreRouteHook"));
		$_SERVER['REQUEST_URI'] = "/";
		$this->router->route();
		$this->assertTrue($this->router->preRouteHookCalled);
	}
	
	public function testMethod_onPostRouteHook()
	{
		Events::bind("Router:after:route", array($this->router, "onPostRouteHook"));
		$_SERVER['REQUEST_URI'] = "/";
		$this->router->route();
		$this->assertTrue($this->router->postRouteHookCalled);
	}
}
?>