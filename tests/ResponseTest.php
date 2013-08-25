<?php
/**
 * Backbone.php
 * 
 * @author	James Tracy <james.a.tracy@gmail.com>
 * @copyright	2012-2013
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link https://github.com/jamesatracy/Backbone.php GitHub Page
 */

require("setup.php");
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
	protected $eventResponse200Triggered = false;
	protected $eventResponse500Triggered = false;
	
	public function testMethod_status()
	{
		$resp = new Response();
		
		// default status code
		$this->assertEquals($resp->status(), 200);
		// valid status code
		$resp->status(404);
		$this->assertEquals($resp->status(), 404);
		// invalid status code
		$this->setExpectedException("InvalidArgumentException", "Response: Invalid status code 700");
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
		
		// test response event triggered
		Events::bind("response.200", array($this, "onResponse200"));
		
		$this->expectOutputString("body content goes here");
		$resp->body("body content goes here");
		$resp->send();
		$this->assertTrue($this->eventResponse200Triggered);
	}
	
	public function testMethod_send_withParam()
	{
		$resp = new Response();
		
		// test response event triggered
		Events::bind("response.404", array($this, "onResponse404"));
		
		$this->expectOutputString("body content goes here");
		$resp->body("body content goes here");
		$resp->send(404);
		$this->assertTrue($this->eventResponse404Triggered);
	}
	
	public function testBehavior_send500()
	{
		$resp = new Response();
		
		// test response event for 500 status code
		Events::bind("response.500", array($this, "onResponse500"));
		
		$resp->status(500);
		$resp->send();
		$this->assertTrue($this->eventResponse500Triggered);
	}
	
	public function onResponse200()
	{
		$this->eventResponse200Triggered = true;
	}
	
	public function onResponse404()
	{
		$this->eventResponse404Triggered = true;
	}
	
	public function onResponse500()
	{
		$this->eventResponse500Triggered = true;
	}
}
?>