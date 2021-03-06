<?php
/**
 * Backbone.php
 * 
 * @author	James Tracy <james.a.tracy@gmail.com>
 * @copyright	2012-2013
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link https://github.com/jamesatracy/Backbone.php GitHub Page
 */

Backbone::uses("Html");

/**
 * PHPUnit Test suite for Html class
 *
 * Tests for individual class methods following this naming
 * convention:
 *		public function testMethod_${name}
 *
 * Tests for general behavior following this naming 
 * conventions:
 * 		public function testBehavior_${description}
 */
class HtmlTest extends PHPUnit_Framework_TestCase
{
    public function testMethod_constructor()
    {
        $this->assertTrue(class_exists("HTML"));
    }
	
	public function testMethod_tag()
	{
		$tag = HTML::tag("div", "Hello World", array("class" => "section"));
		$this->assertEquals($tag, '<div class="section">Hello World</div>');
	}
}
?>