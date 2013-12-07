<?php
/**
 * Backbone.php
 * 
 * @author	James Tracy <james.a.tracy@gmail.com>
 * @copyright	2012-2013
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link https://github.com/jamesatracy/Backbone.php GitHub Page
 */

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
    public function testSanity()
    {
        $this->assertTrue(class_exists("Request"));
    }
    
    public function testMethod_getRequest()
    {
        $request = new Request(array(
            "a" => 1,
            "b" => 2,
            "c" => 3
        ));
        $this->assertEquals($request->getRequest("a"), 1);
        $this->assertEquals($request->getRequest("b"), 2);
        $this->assertEquals($request->getRequest("c"), 3);
        $this->assertEquals($request->getRequest("d"), "");
        $this->assertEquals($request->getRequest("e", 4), 4);
    }
    
    public function testMethod_setRequest()
    {
        $request = new Request();
        $request->setRequest("a", 1);
        $this->assertEquals($request->getRequest("a"), 1);
    }
    
    public function testMethod_getQuery()
    {
        $request = new Request(
            array(),
            array(
            "a" => 1,
            "b" => 2,
            "c" => 3
        ));
        $this->assertEquals($request->getQuery("a"), 1);
        $this->assertEquals($request->getQuery("b"), 2);
        $this->assertEquals($request->getQuery("c"), 3);
        $this->assertEquals($request->getQuery("d"), "");
        $this->assertEquals($request->getQuery("e", 4), 4);
    }
    
    public function testMethod_setQuery()
    {
        $request = new Request();
        $request->setQuery("a", 1);
        $this->assertEquals($request->getQuery("a"), 1);
    }
    
    public function testMethod_getFile()
    {
        $request = new Request(array(), array(),
            array(
            "a" => 1,
            "b" => 2,
            "c" => 3
        ));
        $this->assertEquals($request->getFile("a"), 1);
        $this->assertEquals($request->getFile("b"), 2);
        $this->assertEquals($request->getFile("c"), 3);
        $this->assertEquals($request->getFile("d"), "");
        $this->assertEquals($request->getFile("e", 4), 4);
    }
    
    public function testMethod_setFile()
    {
        $request = new Request();
        $request->setFile("a", 1);
        $this->assertEquals($request->getFile("a"), 1);
    }
    
    public function testMethod_getServer()
    {
        $request = new Request(array(), array(), array(),
            array(
            "a" => 1,
            "b" => 2,
            "c" => 3
        ));
        $this->assertEquals($request->getServer("a"), 1);
        $this->assertEquals($request->getServer("b"), 2);
        $this->assertEquals($request->getServer("c"), 3);
        $this->assertEquals($request->getServer("d"), "");
        $this->assertEquals($request->getServer("e", 4), 4);
    }
    
    public function testMethod_setServer()
    {
        $request = new Request();
        $request->setServer("a", 1);
        $this->assertEquals($request->getServer("a"), 1);
    }
    
    public function testMethod_getHeader()
    {
        $request = new Request(array(), array(), array(),  array(),
            array(
            "a" => 1,
            "b" => 2,
            "c" => 3
        ));
        $this->assertEquals($request->getHeader("a"), 1);
        $this->assertEquals($request->getHeader("b"), 2);
        $this->assertEquals($request->getHeader("c"), 3);
        $this->assertEquals($request->getHeader("d"), "");
        $this->assertEquals($request->getHeader("e", 4), 4);
    }
    
    public function testMethod_setHeader()
    {
        $request = new Request();
        $request->setHeader("a", 1);
        $this->assertEquals($request->getHeader("a"), 1);
    }
    
    public function testMethod_getMethod()
    {
        $request = new Request();
        $request->setServer('REQUEST_METHOD', 'GET');
        $this->assertEquals($request->getMethod(), 'GET');
        $request = new Request();
        $request->setServer('REQUEST_METHOD', 'post');
        $this->assertEquals($request->getMethod(), 'POST');
        $request = new Request();
        $request->setServer('REQUEST_METHOD', 'put');
        $this->assertEquals($request->getMethod(), 'PUT');
        $request = new Request();
        $request->setServer('REQUEST_METHOD', 'put');
        $request->setHeader('X-Http-Method-Override', 'post');
        $this->assertEquals($request->getMethod(), 'POST');
    }
    
    public function testMethod_isSecure()
    {
        $request = new Request();
        $request->setServer('HTTPS', true);
        $this->assertTrue($request->isSecure());
        $request->setServer('HTTPS', false);
        $this->assertFalse($request->isSecure());
    }
    
    public function testMethod_getScheme()
    {
        $request = new Request();
        $request->setServer('HTTPS', true);
        $this->assertEquals($request->getScheme(), 'https');
        $request = new Request();
        $request->setServer('HTTPS', false);
        $this->assertEquals($request->getScheme(), 'http');
    }
    
    public function testMethod_getPort()
    {
        $request = new Request();
        $request->setServer('SERVER_PORT', 80);
        $this->assertEquals($request->getPort(), 80);
    }
    
    public function testMethod_getHost()
    {
        $request = new Request();
        $request->setServer('SERVER_NAME', 'www.name.com');
        $this->assertEquals($request->getHost(), 'www.name.com');
        
        $request = new Request();
        $request->setServer('SERVER_NAME', 'www.name.com');
        $request->setServer('SERVER_PORT', 80);
        $this->assertEquals($request->getHost(), 'www.name.com');
        
        $request = new Request();
        $request->setServer('SERVER_NAME', 'www.name.com');
        $request->setServer('SERVER_PORT', 443);
        $request->setServer('HTTPS', true);
        $this->assertEquals($request->getHost(), 'www.name.com');
        
        $request = new Request();
        $request->setServer('SERVER_NAME', 'www.name.com');
        $request->setServer('SERVER_PORT', 300);
        $this->assertEquals($request->getHost(), 'www.name.com:300');
    }
    
    public function testMethod_getBasePath()
    {
        $request = new Request();
        $request->setServer('SCRIPT_NAME', 'index.php');
        $this->assertEquals($request->getBasePath(), '/');
        
        $request = new Request();
        $request->setServer('SCRIPT_NAME', '/index.php');
        $this->assertEquals($request->getBasePath(), '/');
        
        $request = new Request();
        $request->setServer('SCRIPT_NAME', '/htdocs/index.php');
        $this->assertEquals($request->getBasePath(), '/htdocs/');
    }
    
    public function testMethod_getURI()
    {
        $request = new Request();
        $request->setServer('SCRIPT_NAME', '/htdocs/index.php');
        $request->setServer('REQUEST_URI', '/htdocs/path/');
        $this->assertEquals($request->getURI(), '/htdocs/path/');
        
        $request = new Request();
        $request->setServer('SCRIPT_NAME', '/htdocs/index.php');
        $request->setServer('QUERY_STRING', 'a=1&b=2&c=3');
        $request->setServer('REQUEST_URI', '/htdocs/path/?a=1&b=2&c=3');
        $this->assertEquals($request->getURI(), '/htdocs/path/');
    }
    
    public function testMethod_getPath()
    {
        $request = new Request();
        $request->setServer('SCRIPT_NAME', '/htdocs/index.php');
        $request->setServer('REQUEST_URI', '/htdocs/path/');
        $this->assertEquals($request->getPath(), '/path/');
        
        $request = new Request();
        $request->setServer('SCRIPT_NAME', '/htdocs/index.php');
        $request->setServer('QUERY_STRING', 'a=1&b=2&c=3');
        $request->setServer('REQUEST_URI', '/htdocs/path/?a=1&b=2&c=3');
        $this->assertEquals($request->getPath(), '/path/');
    }
    
    public function testMethod_getBaseUrl()
    {
        $request = new Request();
        $request->setServer('SERVER_NAME', 'www.name.com');
        $request->setServer('SCRIPT_NAME', 'index.php');
        $request->setServer('REQUEST_URI', '/htdocs/');
        $this->assertEquals($request->getBaseUrl(), 'http://www.name.com/');
        
        $request = new Request();
        $request->setServer('SERVER_NAME', 'www.name.com');
        $request->setServer('SCRIPT_NAME', '/htdocs/index.php');
        $request->setServer('REQUEST_URI', '/htdocs/');
        $this->assertEquals($request->getBaseUrl(), 'http://www.name.com/htdocs/');
        
        $request = new Request();
        $request->setServer('SERVER_NAME', 'www.name.com');
        $request->setServer('SCRIPT_NAME', '/htdocs/index.php');
        $request->setServer('REQUEST_URI', '/htdocs/path/');
        $this->assertEquals($request->getBaseUrl(), 'http://www.name.com/htdocs/');
        
        $request = new Request();
        $request->setServer('SERVER_NAME', 'www.name.com');
        $request->setServer('SCRIPT_NAME', '/htdocs/index.php');
        $request->setServer('QUERY_STRING', 'a=1&b=2&c=3');
        $request->setServer('REQUEST_URI', '/htdocs/path/?a=1&b=2&c=3');
        $this->assertEquals($request->getBaseUrl(), 'http://www.name.com/htdocs/');
        
        $request = new Request();
        $request->setServer('HTTPS', true);
        $request->setServer('SERVER_NAME', 'www.name.com');
        $request->setServer('SCRIPT_NAME', '/htdocs/index.php');
        $request->setServer('REQUEST_URI', '/htdocs/path/');
        $this->assertEquals($request->getBaseUrl(), 'https://www.name.com/htdocs/');
        
        $request = new Request();
        $request->setServer('SERVER_PORT', 300);
        $request->setServer('SERVER_NAME', 'www.name.com');
        $request->setServer('SCRIPT_NAME', '/htdocs/index.php');
        $request->setServer('REQUEST_URI', '/htdocs/path/');
        $this->assertEquals($request->getBaseUrl(), 'http://www.name.com:300/htdocs/');
        
        $request = new Request();
        $request->setServer('HTTPS', true);
        $request->setServer('SERVER_PORT', 300);
        $request->setServer('SERVER_NAME', 'www.name.com');
        $request->setServer('SCRIPT_NAME', '/htdocs/index.php');
        $request->setServer('REQUEST_URI', '/htdocs/path/');
        $this->assertEquals($request->getBaseUrl(), 'https://www.name.com:300/htdocs/');
    }
    
    public function testMethod_getUrl()
    {
        $request = new Request();
        $request->setServer('SERVER_NAME', 'www.name.com');
        $request->setServer('SCRIPT_NAME', 'index.php');
        $request->setServer('REQUEST_URI', '/');
        $this->assertEquals($request->getUrl(), 'http://www.name.com/');
        
        $request = new Request();
        $request->setServer('SERVER_NAME', 'www.name.com');
        $request->setServer('SCRIPT_NAME', 'index.php');
        $request->setServer('REQUEST_URI', '/htdocs/');
        $this->assertEquals($request->getUrl(), 'http://www.name.com/htdocs/');
        
        $request = new Request();
        $request->setServer('SERVER_NAME', 'www.name.com');
        $request->setServer('SCRIPT_NAME', '/htdocs/index.php');
        $request->setServer('REQUEST_URI', '/htdocs/');
        $this->assertEquals($request->getUrl(), 'http://www.name.com/htdocs/');
        
        $request = new Request();
        $request->setServer('SERVER_NAME', 'www.name.com');
        $request->setServer('SCRIPT_NAME', '/htdocs/index.php');
        $request->setServer('REQUEST_URI', '/htdocs/path/');
        $this->assertEquals($request->getUrl(), 'http://www.name.com/htdocs/path/');
        
        $request = new Request();
        $request->setServer('SERVER_NAME', 'www.name.com');
        $request->setServer('SCRIPT_NAME', '/htdocs/index.php');
        $request->setServer('QUERY_STRING', 'a=1&b=2&c=3');
        $request->setServer('REQUEST_URI', '/htdocs/path/?a=1&b=2&c=3');
        $this->assertEquals($request->getUrl(), 'http://www.name.com/htdocs/path/?a=1&b=2&c=3');
        
        $request = new Request();
        $request->setServer('HTTPS', true);
        $request->setServer('SERVER_NAME', 'www.name.com');
        $request->setServer('SCRIPT_NAME', '/htdocs/index.php');
        $request->setServer('REQUEST_URI', '/htdocs/path/');
        $this->assertEquals($request->getUrl(), 'https://www.name.com/htdocs/path/');
        
        $request = new Request();
        $request->setServer('SERVER_PORT', 300);
        $request->setServer('SERVER_NAME', 'www.name.com');
        $request->setServer('SCRIPT_NAME', '/htdocs/index.php');
        $request->setServer('REQUEST_URI', '/htdocs/path/');
        $this->assertEquals($request->getUrl(), 'http://www.name.com:300/htdocs/path/');
        
        $request = new Request();
        $request->setServer('HTTPS', true);
        $request->setServer('SERVER_PORT', 300);
        $request->setServer('SERVER_NAME', 'www.name.com');
        $request->setServer('SCRIPT_NAME', '/htdocs/index.php');
        $request->setServer('REQUEST_URI', '/htdocs/path/');
        $this->assertEquals($request->getUrl(), 'https://www.name.com:300/htdocs/path/');
    }
    
    public function testMethod_makeUrl()
    {
        $request = new Request();
        $request->setServer('SERVER_NAME', 'www.name.com');
        $request->setServer('SCRIPT_NAME', 'index.php');
        $this->assertEquals($request->makeUrl('/'), 'http://www.name.com/');
        
        $request = new Request();
        $request->setServer('SERVER_NAME', 'www.name.com');
        $request->setServer('SCRIPT_NAME', 'index.php');
        $this->assertEquals($request->makeUrl('/htdocs/'), 'http://www.name.com/htdocs/');
        
        $request = new Request();
        $request->setServer('SERVER_NAME', 'www.name.com');
        $request->setServer('SCRIPT_NAME', '/htdocs/index.php');
        $this->assertEquals($request->makeUrl('/path/'), 'http://www.name.com/htdocs/path/');
        
        $request = new Request();
        $request->setServer('SERVER_NAME', 'www.name.com');
        $request->setServer('SCRIPT_NAME', '/htdocs/index.php');
        $this->assertEquals($request->makeUrl('/path/'), 'http://www.name.com/htdocs/path/');
        
        $request = new Request();
        $request->setServer('SERVER_NAME', 'www.name.com');
        $request->setServer('SCRIPT_NAME', '/htdocs/index.php');
        $this->assertEquals($request->makeUrl('/path/?a=1&b=2&c=3'), 'http://www.name.com/htdocs/path/?a=1&b=2&c=3');
        
        $request = new Request();
        $request->setServer('HTTPS', true);
        $request->setServer('SERVER_NAME', 'www.name.com');
        $request->setServer('SCRIPT_NAME', '/htdocs/index.php');
        $this->assertEquals($request->makeUrl('/path/'), 'https://www.name.com/htdocs/path/');
        
        $request = new Request();
        $request->setServer('SERVER_PORT', 300);
        $request->setServer('SERVER_NAME', 'www.name.com');
        $request->setServer('SCRIPT_NAME', '/htdocs/index.php');
        $this->assertEquals($request->makeUrl('/path/'), 'http://www.name.com:300/htdocs/path/');
        
        $request = new Request();
        $request->setServer('HTTPS', true);
        $request->setServer('SERVER_PORT', 300);
        $request->setServer('SERVER_NAME', 'www.name.com');
        $request->setServer('SCRIPT_NAME', '/htdocs/index.php');
        $this->assertEquals($request->makeUrl('/path/'), 'https://www.name.com:300/htdocs/path/');
    }
}
?>