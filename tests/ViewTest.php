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
        $view = new View($request, "home");
        $this->assertTrue(get_class($view) === "View");
		$this->assertEquals($view->name, "home");
		$this->assertTrue(get_class(View::create($request, "home")) === "View");
    }
}
?>