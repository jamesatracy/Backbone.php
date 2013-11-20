<?php
/**
 * Backbone.php
 * 
 * @author	James Tracy <james.a.tracy@gmail.com>
 * @copyright	2012-2013
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link https://github.com/jamesatracy/Backbone.php GitHub Page
 */

/**
 * Holds information related to a HTTP request.
 *
 * @since 0.3.0
 */
class Request
{	
    /** @var array The request (post) parameters. */
	public $request = array();
	
	/** @var array The query (get) parameters. */
	public $query = array();
	
	/** @var array The file (files) parameters. */
	public $files = array();
	
	/** @var array Wraps the $_SERVER superglobal. */
	public $server = array();
	
	/** @var array The request headers. */
	public $headers = array();
	
	/** @var string The request method, uppercase. This is a variable cache. */
	protected $method = null;
	
	/** @var string The request scheme (http or https). This is a variable cache. */
	protected $scheme = null;
	
	/** @var string The request host name. This is a variable cache. */
	protected $host = null;
	
	/** @var string The request URI. This is a variable cache. */
	protected $uri = null;
	
	/** @var string The request port number. This is a variable cache. */
	protected $port = null;
	
	/** @var string The request query string. This is a variable cache. */
	protected $query_string = null;
	
	/** @var string The root path for this request. */
	protected $root = null;
	
	/** @var string The relative (to root) path for this request. */
	protected $path = null;
	
	/**
	 * @since 0.3.0
	 * @constructor
	 */
	public function __construct($request = array(), $query = array(), $files = array(), $server = array(), $headers = array())
	{
		$this->request = $request;
		$this->query = $query;
		$this->files = $files;
		$this->server = $server;
		$this->headers = $headers;
	}
	
	/**
	 * Constructs a new Request object based on the current PHP super globals.
	 * @since 0.3.0
	 * @return Request A new request object.
	 */
	public static function create()
	{
		// get the http headers
		$headers = array();
		foreach($_SERVER as $key => $value) {
			if(substr($key, 0, 5) != "HTTP_") {
				continue;
			}
			$header = str_replace(" ", "-", ucwords(str_replace("_", " ", strtolower(substr($key, 5)))));
			$headers[$header] = $value;
		}
		return new Request($_POST, $_GET, $_FILES, $_SERVER, $headers);
	}
	
	/**
	 * @since 0.3.0
	 */
	public function getRequest($key, $default = "")
	{
		if(isset($this->request[$key])) {
			return $this->request[$key];
		}
		return $default;
	}
	
	/**
	 * @since 0.3.0
	 */
	public function setRequest($key, $value)
	{
		$this->request[$key] = $value;
	}
	
	/**
	 * @since 0.3.0
	 */
	public function getQuery($key, $default = "")
	{
		if(isset($this->query[$key])) {
			return $this->query[$key];
		}
		return $default;
	}
	
	/**
	 * @since 0.3.0
	 */
	public function setQuery($key, $value)
	{
		$this->query[$key] = $value;
	}
	
	/**
	 * @since 0.3.0
	 */
	public function getFile($key, $default = "")
	{
		if(isset($this->files[$key])) {
			return $this->files[$key];
		}
		return $default;
	}
	
	/**
	 * @since 0.3.0
	 */
	public function setFile($key, $value)
	{
		$this->files[$key] = $value;
	}
	
	/**
	 * @since 0.3.0
	 */
	public function getServer($key, $default = "")
	{
		if(isset($this->server[$key])) {
			return $this->server[$key];
		}
		return $default;
	}
	
	/**
	 * @since 0.3.0
	 */
	public function setServer($key, $value)
	{
		$this->server[$key] = $value;
	}
	
	/**
	 * @since 0.3.0
	 */
	public function getHeader($key, $default = "")
	{
		if(isset($this->headers[$key])) {
			return $this->headers[$key];
		}
		return $default;
	}
	
	/**
	 * @since 0.3.0
	 */
	public function setHeader($key, $value)
	{
		$this->headers[$key] = $value;
	}
	
	/**
	 * Get the fully resolved URL string.
	 * @since 0.3.0
	 * @return string The URL.
	 */
	public function getURL()
	{
		return ($this->getScheme()."://".$this->getHost().$this->getURI().($this->getQueryString() ? "?".$this->getQueryString() : ""));
	}
	
	/**
	 * Get the fully resolved base URL string.
	 * @since 0.3.0
	 * @return string The base URL.
	 */
	public function getBaseURL()
	{
		return ($this->getScheme()."://".$this->getHost().$this->getBasePath());
	}
	
	/**
	 * @since 0.3.0
	 */
	public function getMethod()
	{
		if($this->method === null) {
			$this->method = strtoupper($this->getServer('REQUEST_METHOD', 'GET'));
			
			if($method = $this->getHeader('X-Http-Method-Override')) {
				$this->method = strtoupper($method);
			}
		} 
		return $this->method;
	}
	
	/**
	 * @since 0.3.0
	 */
	public function isSecure()
	{
		return ($this->getServer("HTTPS") ? true : false);
	}
	
	/**
	 * @since 0.3.0
	 */
	public function getScheme()
	{
		if($this->scheme === null) {
			$this->scheme = ($this->isSecure() ? "https" : "http");
		}
		return $this->scheme;
	}
	
	/**
	 * @since 0.3.0
	 */
	public function getHost()
	{
		if($this->host === null) {
			$this->host = $this->getServer('SERVER_NAME');
		}
		
		$port = $this->getPort();
		$scheme = $this->getScheme();
		if (($scheme == 'http' && $port == 80) || ($scheme == 'https' && $port == 443)) {
            return $this->host;
        }
		
		return $this->host.":".$port;
	}
	
	/**
	 * @since 0.3.0
	 */
	public function getURI()
	{
		if($this->uri === null) {
			$this->uri = str_replace("?".$this->getQueryString(), "", $this->getServer('REQUEST_URI'));
		}
		return $this->uri;
	}
	
	/**
	 * @since 0.3.0
	 */
	public function getPort()
	{
		if($this->port === null) {
			$this->port = $this->getServer('SERVER_PORT');
		}
		return $this->port;
	}
	
	/**
	 * @since 0.3.0
	 */
	public function getQueryString()
	{
		if($this->query_string === null) {
			$this->query_string = $this->getServer('QUERY_STRING');
		}
		return $this->query_string;
	}
	
	/**
	 * @since 0.3.0
	 */
	public function getBasePath()
	{
		if($this->root === null) {
			$script_name = $this->getServer('SCRIPT_NAME');
			$this->root = substr($script_name, 0, strrpos($script_name, "/") + 1);
		}
		return $this->root;
	}
	
	/**
	 * @since 0.3.0
	 */
	public function getPath()
	{
		if($this->path === null) {
			$root = $this->getBasePath();
			
			if($root != "/" ) {
				$this->path = str_replace($root, "", $this->getURI());
			} else {
				$this->path = $this->getURI();
			}
			
			if(substr($this->path, 0, 1) != "/") {
				$this->path = "/".$this->path;
			}
		}
		return $this->path;
	}
};
?>