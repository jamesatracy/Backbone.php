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
Backbone::uses("Request");

/**
 * PHPUnit Test suite for Request class
 *
 * Tests for individual class methods following this naming
 * convention:
 *		public function testMethod_${name}
 *
 * Tests for general behavior following this naming 
 * conventions:
 * 		public function testBehavior_${description}
 */
class RequestTest extends PHPUnit_Framework_TestCase
{
	protected function setUp()
	{
		$this->request = new Request();
	}
	
	public function testMethod_base()
	{
		$_SERVER['HTTPS'] = null;
		$_SERVER['HTTP_HOST'] = "www.example.com";
		
		// base()
		$this->assertEquals($this->request->base(), "http://www.example.com");
		$_SERVER['HTTPS'] = true;
		$this->assertEquals($this->request->base(), "https://www.example.com");
	}
	
	public function testMethod_url()
	{
		$_SERVER['HTTPS'] = null;
		$_SERVER['HTTP_HOST'] = "www.example.com";
		
		// url()
		$_SERVER['REQUEST_URI'] = "/path/to/here/";
		$this->assertEquals($this->request->url(), "http://www.example.com/path/to/here/");
		$_SERVER['REQUEST_URI'] = "/path/to/here/?p=1";
		$this->assertEquals($this->request->url(), "http://www.example.com/path/to/here/?p=1");
	}
	
	public function testMethod_path()
	{
		$_SERVER['HTTPS'] = null;
		$_SERVER['HTTP_HOST'] = "www.example.com";
		
		// path()
		$_SERVER['REQUEST_URI'] = "/path/to/here/";
		$this->assertEquals($this->request->path(), "/path/to/here/");
		$_SERVER['REQUEST_URI'] = "/path/to/here/?p=1";
		$_SERVER['QUERY_STRING'] = "p=1";
		$this->assertEquals($this->request->path(), "/path/to/here/");
	}
	
	public function testMethod_here()
	{
		$_SERVER['HTTPS'] = null;
		$_SERVER['HTTP_HOST'] = "www.example.com";
		
		// here()
		$_SERVER['REQUEST_URI'] = "/path/to/here/";
		$_SERVER['QUERY_STRING'] = "p=1";
		Backbone::$root = "";
		$this->assertEquals($this->request->here(), "/path/to/here/");
		Backbone::$root = "/path/";
		$this->assertEquals($this->request->here(), "/to/here/");
	}
	
	public function testMethod_queryString()
	{
		$_SERVER['HTTPS'] = null;
		$_SERVER['HTTP_HOST'] = "www.example.com";
		
		// queryString()
		$_SERVER['QUERY_STRING'] = "p=1&q=2";
		$this->assertEquals($this->request->queryString(), "p=1&q=2");
	}
	
	public function testMethod_ipaddress()
	{
		// ipaddress()
		$_SERVER["REMOTE_ADDR"] = "127.0.0.1";
		$this->assertEquals($this->request->ipaddress(), "127.0.0.1");
	}
	
	public function testMethod_query()
	{
		// query()
		// test valid arguments
		$_SERVER['QUERY_STRING'] = "p=1&q=2";
		$this->assertEquals($this->request->query("p"), "1");
		$this->assertEquals($this->request->query("q"), "2");
		
		// test invalid arguments
		$_SERVER['QUERY_STRING'] = "p=1&q=2";
		$this->assertNull($this->request->query("a"));
		$this->assertNull($this->request->query("b"));
		
		// test empty arguments
		$_SERVER['QUERY_STRING'] = "p=&q=";
		$this->assertEquals($this->request->query("p"), "");
		$this->assertEquals($this->request->query("q"), "");
		
		// test no arguments
		$_SERVER['QUERY_STRING'] = "";
		$this->assertNull($this->request->query("a"));
	}
	
	public function testMethod_link()
	{		 
		// link()
		$_SERVER['HTTPS'] = null;
		$_SERVER['HTTP_HOST'] = "www.example.com";
		Backbone::$root = "/";
		
		// test non-ssl doc root link
		$this->assertEquals($this->request->link("/path/to/here/"), "http://www.example.com/path/to/here/");
		$this->assertEquals($this->request->link("path/to/here/"), "http://www.example.com/path/to/here/");
		
		// test non-ssl doc root link force to ssl
		$this->assertEquals($this->request->link("/path/to/here/", false, true), "https://www.example.com/path/to/here/");
		$this->assertEquals($this->request->link("path/to/here/", false, true), "https://www.example.com/path/to/here/");
		
		$_SERVER['HTTPS'] = true;
		// test ssl doc root link
		$this->assertEquals($this->request->link("/path/to/here/"), "https://www.example.com/path/to/here/");
		$this->assertEquals($this->request->link("path/to/here/"), "https://www.example.com/path/to/here/");
		
		$_SERVER['HTTPS'] = null;
		Backbone::$root = "/under/doc/root/";
		// test non-ssl doc subpath link
		$this->assertEquals($this->request->link("/path/to/here/"), "http://www.example.com/under/doc/root/path/to/here/");
		$this->assertEquals($this->request->link("path/to/here/"), "http://www.example.com/under/doc/root/path/to/here/");
		
		// test non-ssl doc subpath link force to ssl
		$this->assertEquals($this->request->link("/path/to/here/", false, true), "https://www.example.com/under/doc/root/path/to/here/");
		$this->assertEquals($this->request->link("path/to/here/", false, true), "https://www.example.com/under/doc/root/path/to/here/");
		
		$_SERVER['HTTPS'] = true;
		// test ssl doc subpath link
		$this->assertEquals($this->request->link("/path/to/here/"), "https://www.example.com/under/doc/root/path/to/here/");
		$this->assertEquals($this->request->link("path/to/here/"), "https://www.example.com/under/doc/root/path/to/here/");
	}
	
	public function testMethod_getHeaders()
	{
		$_SERVER['HTTP_X_REQUESTED_WITH'] = "foobar";
		$headers = $this->request->getHeaders();
		$this->assertArrayHasKey("X-Requested-With", $headers);
		$this->assertEquals($headers['X-Requested-With'], "foobar");
	}
	
	public function testMethod_get()
	{
		$_GET = null;
		
		// GET
		// test empty GET
		$this->assertNull($this->request->get());
		$_GET = array("p" => "1", "q" => "2");
		// test GET
		$this->assertTrue(is_array($this->request->get()));
		// test GET argument
		$this->assertEquals($this->request->get("p"), "1");
		$this->assertEquals($this->request->get("q"), "2");
	}
	
	public function testMethod_post()
	{
		$_POST = null;
		
		// POST
		// test empty POST"
		$this->assertNull($this->request->post());
		$_POST = array("p" => "1", "q" => "2");
		// test POST
		$this->assertTrue(is_array($this->request->post()));
		// test POST argument
		$this->assertEquals($this->request->post("p"), "1");
		$this->assertEquals($this->request->post("q"), "2");
	}
	
	public function testMethod_files()
	{
		$_FILES = null;
		
		// FILES
		// test empty FILES
		$this->assertNull($this->request->files());
		$_FILES = array("file1" => "1", "file2" => "2");
		// test FILES
		$this->assertTrue(is_array($this->request->files()));
		// test FILES argument
		$this->assertEquals($this->request->files("file1"), "1");
		$this->assertEquals($this->request->files("file2"), "2");
	}
	
	public function testMethod_method()
	{
		// get
		$_SERVER['REQUEST_METHOD'] = "GET";
		$this->assertEquals($this->request->method(), "GET");
		
		// post
		$_SERVER['REQUEST_METHOD'] = "POST";
		$this->assertEquals($this->request->method(), "POST");
		
		// put
		$_SERVER['REQUEST_METHOD'] = "PUT";
		$this->assertEquals($this->request->method(), "PUT");
		
		// delete
		$_SERVER['REQUEST_METHOD'] = "DELETE";
		$this->assertEquals($this->request->method(), "DELETE");
		
		// test no method
		unset($_SERVER['REQUEST_METHOD']);
		$this->assertEquals($this->request->method(), "");
	}
	
	public function testMEethod_is()
	{	
		$_SERVER['REQUEST_METHOD'] = "GET";
		// test is('get')
		$this->assertTrue($this->request->is("GET"));
		$this->assertTrue($this->request->is("get"));
		
		$_SERVER['REQUEST_METHOD'] = "POST";
		// test is('post')
		$this->assertTrue($this->request->is("POST"));
		$this->assertTrue($this->request->is("post"));
		
		$_SERVER['REQUEST_METHOD'] = "PUT";
		// test is('put')
		$this->assertTrue($this->request->is("PUT"));
		$this->assertTrue($this->request->is("put"));
		
		$_SERVER['REQUEST_METHOD'] = "DELETE";
		// test is('delete')
		$this->assertTrue($this->request->is("DELETE"));
		$this->assertTrue($this->request->is("delete"));
		
		$_SERVER['HTTP_X_REQUESTED_WITH'] = "xmlhttprequest";
		// test is('ajax')
		$this->assertTrue($this->request->is("ajax"));
		
		$_SERVER['HTTPS'] = null;
		// test is('ssl')
		$this->assertFalse($this->request->is("ssl"));
		$_SERVER['HTTPS'] = true;
		$this->assertTrue($this->request->is("ssl"));
	}
}
?>