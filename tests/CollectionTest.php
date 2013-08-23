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
Backbone::uses(array("Model", "Collection"));

/**
 * A mock model class for testing purposes.
 */
class MockModel extends Model
{
	public function __construct()
	{
		// automatically stamp created field
		$this->created = "created";
		// load a mock schema for testing
		$this->schemaFile = "tests/fixtures/model_test_fixture.json";
		parent::__construct("table");
	}
}

/**
 * PHPUnit Test suite for Collection class
 *
 * Tests for individual class methods following this naming
 * convention:
 *		public function testMethod_${name}
 *
 * Tests for general behavior following this naming 
 * conventions:
 * 		public function testBehavior_${description}
 */
class CollectionTest extends PHPUnit_Framework_TestCase
{
	protected $db = null;
	
	public function setUp()
	{
		Backbone::uses("/tests/helpers/MockDatabase");
		$this->db = Connections::create("default", "MockDatabase", array("server" => "localhost", "user" => "root", "pass" => ""));
	}
}
?>