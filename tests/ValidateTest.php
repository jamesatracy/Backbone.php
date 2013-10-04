<?php
/**
 * Backbone.php
 * 
 * @author	James Tracy <james.a.tracy@gmail.com>
 * @copyright	2012-2013
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link https://github.com/jamesatracy/Backbone.php GitHub Page
 */

Backbone::uses("Validate");

/**
 * PHPUnit Test suite for Validate class
 *
 * Tests for individual class methods following this naming
 * convention:
 *		public function testMethod_${name}
 *
 * Tests for general behavior following this naming 
 * conventions:
 * 		public function testBehavior_${description}
 */
class ValidateTest extends PHPUnit_Framework_TestCase
{
	public function testMethod_required()
	{
		// numeral
		$this->assertTrue(Validate::required("field", 1));
		// float
		$this->assertTrue(Validate::required("field", 1.0));
		// zero
		$this->assertTrue(Validate::required("field", 0));
		$this->assertTrue(Validate::required("field", 0.0));
		// string
		$this->assertTrue(Validate::required("field", "one"));
		$this->assertTrue(Validate::required("field", "0"));
		// empty string
		$this->assertFalse(Validate::required("field", ""));
		// null
		$this->assertFalse(Validate::required("field", null));
	}
	
	public function testMethod_numeric()
	{
		$this->assertTrue(Validate::numeric("field", 1));
		$this->assertTrue(Validate::numeric("field", 1.1));
		$this->assertTrue(Validate::numeric("field", "1"));
		$this->assertTrue(Validate::numeric("field", "1.1"));
		$this->assertTrue(Validate::numeric("field", "0"));
		$this->assertFalse(Validate::numeric("field", "a"));
		$this->assertFalse(Validate::numeric("field", "1a"));
		$this->assertFalse(Validate::numeric("field", "1.a"));
		$this->assertFalse(Validate::numeric("field", null));
	}
	
	public function testMethod_email()
	{
		$this->assertTrue(Validate::email("field", "bob@test.com"));
		$this->assertTrue(Validate::email("field", "bob@test.net"));
		$this->assertTrue(Validate::email("field", "bob@test.org"));
		//$this->assertTrue(Validate::email("field", "bob@test"));
		$this->assertFalse(Validate::email("field", "bob.com"));
		$this->assertFalse(Validate::email("field", "bob"));
	}
	
	public function testMethod_url()
	{
		$this->assertTrue(Validate::url("field", "http://www.example.com"));
		$this->assertTrue(Validate::url("field", "https://www.example.com"));
		$this->assertTrue(Validate::url("field", "http://example.com"));
		$this->assertTrue(Validate::url("field", "https://example.com"));
		$this->assertTrue(Validate::url("field", "http://example.net"));
		$this->assertTrue(Validate::url("field", "http://example.org"));
	}
	
	public function testMethod_min()
	{
		$this->assertTrue(Validate::min("field", 1, 1));
		$this->assertTrue(Validate::min("field", 2, 1));
		$this->assertFalse(Validate::min("field", 0, 1));
		$this->assertFalse(Validate::min("field", -1, 1));
	}
	
	public function testMethod_max()
	{
		$this->assertTrue(Validate::max("field", 1, 1));
		$this->assertTrue(Validate::max("field", 0, 1));
		$this->assertTrue(Validate::max("field", -1, 1));
		$this->assertFalse(Validate::max("field", 2, 1));
	}
	
	public function testMethod_minlength()
	{
		$this->assertTrue(Validate::minlength("field", "string", 6));
		$this->assertTrue(Validate::minlength("field", "strings", 6));
		$this->assertFalse(Validate::minlength("field", "str", 6));
	}
	
	public function testMethod_maxlength()
	{
		$this->assertTrue(Validate::maxlength("field", "string", 6));
		$this->assertFalse(Validate::maxlength("field", "strings", 6));
		$this->assertTrue(Validate::maxlength("field", "str", 6));
	}
	
	public function testMethod_enum()
	{
		$this->assertTrue(Validate::enum("field", 1, array(1, 2, 3, "apple")));
		$this->assertTrue(Validate::enum("field", 2, array(1, 2, 3, "apple")));
		$this->assertTrue(Validate::enum("field", 3, array(1, 2, 3, "apple")));
		$this->assertTrue(Validate::enum("field", "apple", array(1, 2, 3, "apple")));
		$this->assertFalse(Validate::enum("field", 4, array(1, 2, 3, "apple")));
		$this->assertFalse(Validate::enum("field", "orange", array(1, 2, 3, "apple")));
	}
	
	public function testMethod_binary()
	{
		$this->assertTrue(Validate::binary("field", 0));
		$this->assertTrue(Validate::binary("field", "0"));
		$this->assertTrue(Validate::binary("field", 1));
		$this->assertTrue(Validate::binary("field", "1"));
		$this->assertFalse(Validate::binary("field", 1.1));
		$this->assertFalse(Validate::binary("field", 2));
		$this->assertFalse(Validate::binary("field", "2"));
	}
}
?>