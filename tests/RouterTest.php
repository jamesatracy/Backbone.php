<?php
/**
 * Backbone.php
 * 
 * @author	James Tracy <james.a.tracy@gmail.com>
 * @copyright	2012-2013
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link https://github.com/jamesatracy/Backbone.php GitHub Page
 */

Backbone::uses("Router", "Request");

function global_func_callback()
{
	return;
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
    protected static $methodCalled = false;
    protected static $argsPassed = array();
    
    public function setUp()
    {
        Router::clear();
        self::$methodCalled = false;
        self::$argsPassed = array();
    }
    
    public function onRoute()
    {
        self::$methodCalled = true;
        self::$argsPassed = func_get_args();
    }
    
	public function test_Sanity()
	{
		$this->assertTrue(class_exists("Router"));
	}
	
	public function testBehavior_indexRoute()
	{
	    Router::get("/", "global_func_callback");
	    
	    // test with root == /
		$request = new Request(array(), array(), array(), 
			array(
				'REQUEST_METHOD' => "GET",
				'SERVER_NAME' => "/",
				'REQUEST_URI' => "/"
			)
		);
		$this->assertTrue(Router::dispatch($request) !== false);
		
		// test with root == /path/
		$request = new Request(array(), array(), array(), 
			array(
				'REQUEST_METHOD' => "GET",
				'SERVER_NAME' => "/",
				'REQUEST_URI' => "/path/",
				'SCRIPT_NAME' => "/path/"
			)
		);
		$this->assertTrue(Router::dispatch($request) !== false);
	}
	
	public function testBehavior_simplePathRoute()
	{
	    Router::get("/path/", "global_func_callback");
	    
		$request = new Request(array(), array(), array(), 
			array(
				'REQUEST_METHOD' => "GET",
				'SERVER_NAME' => "/",
				'REQUEST_URI' => "/path/"
			)
		);
		$this->assertTrue(Router::dispatch($request) !== false);
	}
	
	public function testBehavior_unmatchedRoute()
	{
	    Router::get("/", "global_func_callback");
	    
		$request = new Request(array(), array(), array(), 
			array(
				'REQUEST_METHOD' => "GET",
				'SERVER_NAME' => "/",
				'REQUEST_URI' => "/path/"
			)
		);
		$this->assertTrue(Router::dispatch($request) == false);
	}
	
	public function testBehavior_doublePathRoute()
	{
	    Router::get("/double/path/", "global_func_callback");
	    
		$request = new Request(array(), array(), array(), 
			array(
				'REQUEST_METHOD' => "GET",
				'SERVER_NAME' => "/",
				'REQUEST_URI' => "/double/path/"
			)
		);
		$this->assertTrue(Router::dispatch($request) !== false);
	}
	
	public function testBehavior_simpleControllerRoute()
	{
	    Router::get("/path/", "/tests/RouterTest@onRoute");
	    
		$request = new Request(array(), array(), array(), 
			array(
				'REQUEST_METHOD' => "GET",
				'SERVER_NAME' => "/",
				'REQUEST_URI' => "/path/"
			)
		);
		$this->assertTrue(Router::dispatch($request) !== false);
		$this->assertTrue(self::$methodCalled);
	}
	
	public function testBehavior_numberParamRoute()
	{
	    Router::get("/path/:id/", "/tests/RouterTest@onRoute");
	    
		$request = new Request(array(), array(), array(), 
			array(
				'REQUEST_METHOD' => "GET",
				'SERVER_NAME' => "/",
				'REQUEST_URI' => "/path/12/"
			)
		);
		$this->assertTrue(Router::dispatch($request) !== false);
		$this->assertTrue(self::$methodCalled);
		// first argument is the request object
		$this->assertCount(2, self::$argsPassed);
		$this->assertEquals(get_class(self::$argsPassed[0]), "Request");
		$this->assertEquals(self::$argsPassed[1], 12);
	}
	
	public function testBehavior_alphaParamRoute()
	{
		Router::get("/path/:name/", "/tests/RouterTest@onRoute");
	    
		$request = new Request(array(), array(), array(), 
			array(
				'REQUEST_METHOD' => "GET",
				'SERVER_NAME' => "/",
				'REQUEST_URI' => "/path/abc/"
			)
		);
		$this->assertTrue(Router::dispatch($request) !== false);
		$this->assertTrue(self::$methodCalled);
		// first argument is the request object
		$this->assertCount(2, self::$argsPassed);
		$this->assertEquals(get_class(self::$argsPassed[0]), "Request");
		$this->assertEquals(self::$argsPassed[1], "abc");
	}
	
	public function testBehavior_multipleParamRoute()
	{
		Router::get("/path/:id/:name/", "/tests/RouterTest@onRoute");
	    
		$request = new Request(array(), array(), array(), 
			array(
				'REQUEST_METHOD' => "GET",
				'SERVER_NAME' => "/",
				'REQUEST_URI' => "/path/12/abc/"
			)
		);
		$this->assertTrue(Router::dispatch($request) !== false);
		$this->assertTrue(self::$methodCalled);
		// first argument is the request object
		$this->assertCount(3, self::$argsPassed);
		$this->assertEquals(get_class(self::$argsPassed[0]), "Request");
		$this->assertEquals(self::$argsPassed[1], 12);
		$this->assertEquals(self::$argsPassed[2], "abc");
	}
	
	public function testBehavior_unmatchedMethodRoute()
	{
	    Router::get("/path/", "global_func_callback");
	    
		$request = new Request(array(), array(), array(), 
			array(
				'REQUEST_METHOD' => "POST",
				'SERVER_NAME' => "/",
				'REQUEST_URI' => "/path/"
			)
		);
		$resp = Router::dispatch($request);
		$this->assertTrue($resp !== false);
		$this->assertEquals(get_class($resp), "Response");
		$this->assertEquals($resp->status(), 405);
		$this->assertEquals($resp->header("Allow"), "GET");
	}
	
	public function testBehavior_routeAliases()
	{
	    Router::get("/", "global_func_callback")->alias("home");
	    Router::get("/path/", "global_func_callback")->alias("path");
	    Router::get("/path/:name/:id/", "global_func_callback")->alias("path-name-id");
	    
		$this->assertEquals(Router::getRouteFromAlias("home"), "/");
		$this->assertEquals(Router::getRouteFromAlias("path"), "/path/");
		$this->assertEquals(Router::getRouteFromAlias("path-name-id", array("abc", 12)), "/path/abc/12/");
	}
}
?>