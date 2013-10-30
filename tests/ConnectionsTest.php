<?php
/**
 * Backbone.php
 * 
 * @author	James Tracy <james.a.tracy@gmail.com>
 * @copyright	2012-2013
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link https://github.com/jamesatracy/Backbone.php GitHub Page
 */

Backbone::uses("Connections");

use Backbone\Connections as Connections;

/**
 * PHPUnit Test suite for Connections class
 *
 * Tests for individual class methods following this naming
 * convention:
 *		public function testMethod_${name}
 *
 * Tests for general behavior following this naming 
 * conventions:
 * 		public function testBehavior_${description}
 */
class ConnectionsTest extends PHPUnit_Framework_TestCase
{
    public function testSanity()
    {
        $this->assertTrue(class_exists("Backbone\Connections"));
    }
}
?>