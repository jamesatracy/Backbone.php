<?php

Backbone::uses(array("TestSuite", "DataSet"));

BackboneTest::describe(
	// class name
	"RequestTest", 
	// title
	"Request class test suite"
);

class RequestTest extends TestSuite
{
	public static function getTests()
	{
		return array(
			"testURLs" => "Test the url getters",
			"testQuery" => "Test the query method",
			"testLink" => "Test the link method",
			"testGet" => "Test the get method",
			"testPost" => "Test the post method",
			"testFiles" => "Test the files method",
			"testIs" => "Test the is method"
		);
	}
	
	public function startUp()
	{
		// save values
		$this->server = $_SERVER;
		$this->root = Backbone::$root;
	}
	
	public function tearDown()
	{
		// restore values
		$_SERVER = $this->server;
		Backbone::$root = $this->root;
	}
	
	public function testURLs()
	{
		$request = new Request();
		
		$_SERVER['HTTPS'] = null;
		$_SERVER['HTTP_HOST'] = "www.example.com";
		
		// base()
		$this->caption("test base()");
		$this->is($this->isEqual($request->base(), "http://www.example.com"));
		$_SERVER['HTTPS'] = true;
		$this->is($this->isEqual($request->base(), "https://www.example.com"));
		$_SERVER['HTTPS'] = null;
		
		// url()
		$this->caption("test url()");
		$_SERVER['REQUEST_URI'] = "/path/to/here/";
		$this->is($this->isEqual($request->url(), "http://www.example.com/path/to/here/"));
		$_SERVER['REQUEST_URI'] = "/path/to/here/?p=1";
		$this->is($this->isEqual($request->url(), "http://www.example.com/path/to/here/?p=1"));
		
		// path()
		$this->caption("test path()");
		$_SERVER['REQUEST_URI'] = "/path/to/here/";
		$this->is($this->isEqual($request->path(), "/path/to/here/"));
		$_SERVER['REQUEST_URI'] = "/path/to/here/?p=1";
		$_SERVER['QUERY_STRING'] = "p=1";
		$this->is($this->isEqual($request->path(), "/path/to/here/"));
		
		// here()
		$this->caption("test here()");
		$_SERVER['REQUEST_URI'] = "/path/to/here/";
		$_SERVER['QUERY_STRING'] = "p=1";
		Backbone::$root = "";
		$this->is($this->isEqual($request->here(), "/path/to/here/"));
		Backbone::$root = "/path/";
		$this->is($this->isEqual($request->here(), "/to/here/"));
		
		// queryString()
		$this->caption("test queryString()");
		$_SERVER['QUERY_STRING'] = "p=1&q=2";
		$this->is($this->isEqual($request->queryString(), "p=1&q=2"));
		
		// ipaddress()
		$this->caption("test ipaddress()");
		$_SERVER["REMOTE_ADDR"] = "127.0.0.1";
		$this->is($this->isEqual($request->ipaddress(), "127.0.0.1"));
	}
	
	public function testQuery()
	{
		$request = new Request();
		
		// query()
		$this->caption("test valid arguments");
		$_SERVER['QUERY_STRING'] = "p=1&q=2";
		$this->is($this->isEqual($request->query("p"), "1"));
		$this->is($this->isEqual($request->query("q"), "2"));
		
		$this->caption("test invalid arguments");
		$_SERVER['QUERY_STRING'] = "p=1&q=2";
		$this->is($this->isNull($request->query("a")));
		$this->is($this->isNull($request->query("b")));
		
		$this->caption("test empty arguments");
		$_SERVER['QUERY_STRING'] = "p=&q=";
		$this->is($this->isEqual($request->query("p"), ""));
		$this->is($this->isEqual($request->query("q"), ""));
		
		$this->caption("test no arguments");
		$_SERVER['QUERY_STRING'] = "";
		$this->is($this->isNull($request->query("a")));
	}
	
	public function testLink()
	{
		$request = new Request();
		 
		// link()
		$_SERVER['HTTPS'] = null;
		$_SERVER['HTTP_HOST'] = "www.example.com";
		Backbone::$root = "/";
		
		$this->caption("test non-ssl doc root link");
		$this->is($this->isEqual($request->link("/path/to/here/"), "http://www.example.com/path/to/here/"));
		$this->is($this->isEqual($request->link("path/to/here/"), "http://www.example.com/path/to/here/"));
		
		$this->caption("test non-ssl doc root link force to ssl");
		$this->is($this->isEqual($request->link("/path/to/here/", false, true), "https://www.example.com/path/to/here/"));
		$this->is($this->isEqual($request->link("path/to/here/", false, true), "https://www.example.com/path/to/here/"));
		
		$_SERVER['HTTPS'] = true;
		$this->caption("test ssl doc root link");
		$this->is($this->isEqual($request->link("/path/to/here/"), "https://www.example.com/path/to/here/"));
		$this->is($this->isEqual($request->link("path/to/here/"), "https://www.example.com/path/to/here/"));
		
		$_SERVER['HTTPS'] = null;
		Backbone::$root = "/under/doc/root/";
		$this->caption("test non-ssl doc subpath link");
		$this->is($this->isEqual($request->link("/path/to/here/"), "http://www.example.com/under/doc/root/path/to/here/"));
		$this->is($this->isEqual($request->link("path/to/here/"), "http://www.example.com/under/doc/root/path/to/here/"));
		
		$this->caption("test non-ssl doc subpath link force to ssl");
		$this->is($this->isEqual($request->link("/path/to/here/", false, true), "https://www.example.com/under/doc/root/path/to/here/"));
		$this->is($this->isEqual($request->link("path/to/here/", false, true), "https://www.example.com/under/doc/root/path/to/here/"));
		
		$_SERVER['HTTPS'] = true;
		$this->caption("test ssl doc subpath link");
		$this->is($this->isEqual($request->link("/path/to/here/"), "https://www.example.com/under/doc/root/path/to/here/"));
		$this->is($this->isEqual($request->link("path/to/here/"), "https://www.example.com/under/doc/root/path/to/here/"));
	}
	
	public function testGet()
	{
		$request = new Request();
		$_GET = null;
		
		// GET
		$this->caption("test empty GET");
		$this->is($this->isNull($request->get()));
		$_GET = array("p" => "1", "q" => "2");
		$this->caption("test GET");
		$this->is($this->isArray($request->get()));
		$this->caption("test GET argument");
		$this->is($this->isEqual($request->get("p"), "1"));
		$this->is($this->isEqual($request->get("q"), "2"));
	}
	
	public function testPost()
	{
		$request = new Request();
		$_POST = null;
		
		// POST
		$this->caption("test empty POST");
		$this->is($this->isNull($request->post()));
		$_POST = array("p" => "1", "q" => "2");
		$this->caption("test POST");
		$this->is($this->isArray($request->post()));
		$this->caption("test POST argument");
		$this->is($this->isEqual($request->post("p"), "1"));
		$this->is($this->isEqual($request->post("q"), "2"));
	}
	
	public function testFiles()
	{
		$request = new Request();
		$_FILES = null;
		
		// FILES
		$this->caption("test empty FILES");
		$this->is($this->isNull($request->files()));
		$_FILES = array("file1" => "1", "file2" => "2");
		$this->caption("test FILES");
		$this->is($this->isArray($request->files()));
		$this->caption("test FILES argument");
		$this->is($this->isEqual($request->files("file1"), "1"));
		$this->is($this->isEqual($request->files("file2"), "2"));
	}
	
	public function testIs()
	{
		$request = new Request();
		
		$_SERVER['REQUEST_METHOD'] = "GET";
		$this->caption("test is('get')");
		$this->is($this->isTrue($request->is("get")));
		
		$_SERVER['REQUEST_METHOD'] = "POST";
		$this->caption("test is('post')");
		$this->is($this->isTrue($request->is("post")));
		
		$_SERVER['REQUEST_METHOD'] = "PUT";
		$this->caption("test is('put')");
		$this->is($this->isTrue($request->is("put")));
		
		$_SERVER['HTTP_X_REQUESTED_WITH'] = "xmlhttprequest";
		$this->caption("test is('ajax')");
		$this->is($this->isTrue($request->is("ajax")));
		
		$_SERVER['HTTPS'] = null;
		$this->caption("test is('ssl')");
		$this->is($this->isFalse($request->is("ssl")));
		$_SERVER['HTTPS'] = true;
		$this->is($this->isTrue($request->is("ssl")));
	}
}
?>