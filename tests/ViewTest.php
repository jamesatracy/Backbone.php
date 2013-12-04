<?php
/**
 * Backbone.php
 * 
 * @author	James Tracy <james.a.tracy@gmail.com>
 * @copyright	2012-2013
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link https://github.com/jamesatracy/Backbone.php GitHub Page
 */

Backbone::uses("View", "Request");

/**
 * PHPUnit Test suite for View class
 *
 * Tests for individual class methods following this naming
 * convention:
 *		public function testMethod_${name}
 *
 * Tests for general behavior following this naming 
 * conventions:
 * 		public function testBehavior_${description}
 */
class ViewTest extends PHPUnit_Framework_TestCase
{
    public function testMethod_constructor()
    {
		$request = Request::create();
        $view = new View($request, "view-test-hello");
        $this->assertTrue(get_class($view) === "View");
		$this->assertEquals($view->name, "view-test-hello");
		$this->assertTrue(get_class(View::create($request, "view-test-hello")) === "Response");
    }
    
    public function testMethod_create()
    {
        $request = Request::create();
        $resp = View::create($request, "view-test-hello");
        $this->assertEquals($resp->status(), 200);
        $this->assertEquals($resp->body(), '<p>Hello World</p>');
    }
    
    public function testMethod_load()
    {
        $request = Request::create();
        $view = new View($request, "view-test-hello");
        $resp = $view->load();
        $this->assertEquals($resp->status(), 200);
        $this->assertEquals($resp->body(), '<p>Hello World</p>');
    }
    
    /**
     * @expectedException RuntimeException
     */
    public function testBehavior_missingView()
    {
        $request = Request::create();
        $resp = View::create($request, "i-dont-exist");
    }
    
    public function testBehavior_block()
    {
        $request = Request::create();
        $resp = View::create($request, "block-test");
        $this->assertEquals($resp->status(), 200);
        $this->assertEquals($resp->body(), '');
    }
    
    public function testBehavior_extend()
    {
        $request = Request::create();
        $resp = View::create($request, "extension-test");
        $this->assertEquals($resp->status(), 200);
        $this->assertEquals($resp->body(), '<html><p>Hello World</p></html>');
    }
}
?>