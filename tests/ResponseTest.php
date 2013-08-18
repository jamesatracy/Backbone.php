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
Backbone::uses("Response");

/**
 * PHPUnit Test suite for Response class
 *
 * Tests for individual class methods following this naming
 * convention:
 *		public function testMethod_${name}
 *
 * Tests for general behavior following this naming 
 * conventions:
 * 		public function testBehavior_${description}
 */
class ResponseTest extends PHPUnit_Framework_TestCase
{
	public function testMethod_status()
	{
		$resp = new Response();
		
		// default status code
		$this->assertEquals($resp->status(), 200);
		// valid status code
		$resp->status(404);
		$this->assertEquals($resp->status(), 404);
		// invalid status code
		$this->assertFalse($resp->status(700));
		$this->assertEquals($resp->status(), 404);
	}
	
	public function testMethod_contentType()
	{
		$resp = new Response();
		
		// default is text/html
		$this->assertEquals($resp->contentType(), "text/html");
		// set content type
		$resp->contentType("application/json");
		$this->assertEquals($resp->contentType(), "application/json");
	}
	
	public function testMethod_header()
	{
		$resp = new Response();
		
		// default is empty
		$this->assertEmpty($resp->header());
		// single header
		$resp->header("Location", "http://example.com");
		$this->assertEquals($resp->header(), array("Location" => "http://example.com"));
		// multiple headers
		$resp->header(array(
			"WWW-Authenticate" => "Negotiate",
			"X-Extra" => "Extra"
		));
		$this->assertEquals($resp->header(), array(
			"Location" => "http://example.com",
			"WWW-Authenticate" => "Negotiate",
			"X-Extra" => "Extra"
		));
	}
	
	public function testMethod_body()
	{
		$resp = new Response();
		
		// default body is null
		$this->assertNull($resp->body());
		// set body
		$resp->body("Body Content");
		$this->assertEquals($resp->body(), "Body Content");
	}
	
	public function testMethod_send()
	{
		$resp = new Response();
		
		$this->expectOutputString("body content goes here");
		$resp->body("body content goes here");
		$resp->send();
	}
}
?>