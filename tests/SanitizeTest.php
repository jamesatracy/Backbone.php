<?php
/**
 * Backbone.php
 * 
 * @author	James Tracy <james.a.tracy@gmail.com>
 * @copyright	2012-2013
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link https://github.com/jamesatracy/Backbone.php GitHub Page
 */

Backbone::uses("Sanitize");

use Backbone\Sanitize as Sanitize;

/**
 * PHPUnit Test suite for Sanitize class
 *
 * Tests for individual class methods following this naming
 * convention:
 *		public function testMethod_${name}
 *
 * Tests for general behavior following this naming 
 * conventions:
 * 		public function testBehavior_${description}
 */
class SanitizeTest extends PHPUnit_Framework_TestCase
{
    public function testSanity()
    {
        $this->assertTrue(class_exists("Backbone\Sanitize"));
    }
}
?>