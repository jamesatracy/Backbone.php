<?php
/**
 * Backbone.php
 * 
 * @author	James Tracy <james.a.tracy@gmail.com>
 * @copyright	2012-2013
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link https://github.com/jamesatracy/Backbone.php GitHub Page
 */

Backbone::uses("Schema,/tests/helpers/MockDatabase");

use Backbone\Schema as Schema;
use Backbone\Connections as Connections;

/**
 * PHPUnit Test suite for Schema class
 *
 * Tests for individual class methods following this naming
 * convention:
 *		public function testMethod_${name}
 *
 * Tests for general behavior following this naming 
 * conventions:
 * 		public function testBehavior_${description}
 */
class SchemaTest extends PHPUnit_Framework_TestCase
{
    public function testMethod_constructor()
    {
        $db = Connections::create("default", "MockDatabase", array("server" => "localhost", "user" => "root", "pass" => ""));
        $schema = new Schema($db);
        $this->assertTrue(get_class($schema) === "Backbone\Schema");
    }
}
?>