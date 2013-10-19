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
 * Base class for all Backbone.php routers.
 * 
 * Router allows you to map one request url endpoint to one
 * function implementation if you pass a string along with
 * the pattern to match:
 * 
 * 		public function __construct()
 *		{
 *			parent::__construct(array(
 *				"/about/" => "aboutPage",
 *				"/about/careers/" => "careersPage"
 *				)
 *			));
 *		}
 * 
 * As of 0.2.4, you can also implement a RESTful interface by mapping
 * one request url endpoint to one or more function implementations
 * based on the request method.
 *
 * Example:
 *		I want to implement GET, POST, PUT, and DELETE methods for the
 *		the following endpoints:
 *
 *		GET		/api/dept/engineering/employee/:integer/
 *		POST	/api/dept/engineering/employee/
 *		PUT		/api/dept/engineering/employee/:integer/
 *		DELETE	/api/dept/engineering/employee/:integer/
 *
 * 		Here is what the code in the constructor would look like in
 *		the child class:
 *
 *		public function __construct()
 *		{
 *			parent::__construct(array(
 *				"/api/dept/engineering/employee/:integer/" => array(
 *					"GET" => "getEmployee",
 *					"PUT" => "updateEmployee",
 *					"DELETE" => "deleteEmployee"
 *				),
 *				"/api/dept/engineering/employee/" => array(
 *					"POST" => "createEmployee"
 *				)
 *			));
 *		}
 *
 *	You can map as many HTTP methods as you want. If an endpoint is called
 * 	with an unsupported method, Router will return a 405 Method Not
 *	Allowed response with a list of Allowed methods.
 * 
 *  You can also use these convenience methods as an alternative syntax:
 * 
 *      public function __construct()
 *      {
 *          parent::__construct();
 *          $this->get("/api/dept/engineering/employee/:integer/", "getEmployee");
 *          $this->put("/api/dept/engineering/employee/:integer/", "updateEmployee");
 *          $this->post("/api/dept/engineering/employee/", "createEmployee");
 *          $this->delete("/api/dept/engineering/employee/:integer/", "deleteEmployee");
 *      }
 *
 * @since 0.1.0
 */
class Router 
{
	/** @var array Array of route mappings */
	protected $_routes = array();
	
	/** @var View The active view class for the request */
	protected $view;
	
	/** 
	 * @var Response The active reponse object. 
	 * @since 0.2.0
	 */
	protected $response;
	
	/** 
	 * @var string The currently matched pattern, or empty string.
	 * @since 0.1.1
	 */
	protected $pattern = "";
	
	/**
	 * @var array The currently matched arguments, or empty array.
	 * @since 0.2.4
	 */
	protected $arguments = array();
	
	/** @var string The root of the router's url maps, if any */
	public $root = "";
	
	/**
	 * constructor
	 */
	public function __construct()
	{
		// empty
	}
	
	/**
	 * Get the list of routes for this Router
	 *
	 * @since 0.1.0
	 * @return array The array of routes
	 */
	public function getRoutes()
	{
		return $this->_routes;
	}
	
	/**
	 * Add a route or routes mapping to the Router
	 *
	 * Ex: add("/about/", "about")
	 * Ex: add(array("/about/" => "about", "/products/" => "products"
	 *
	 * @since 0.1.0
	 * @param array|string $routes Either an array of routes (path => callback method) or a path
	 * @param string $callback The callback method, if $routes is a string, otherwise null
	 */
	protected function add($routes, $callback = null)
	{
		if(is_array($routes)) {
			foreach($routes as $key => $route)
				$this->_routes[$key] = $route;
		} else {
			if($callback) {
				$this->_routes[$routes] = $callback;
			}
		}
	}
	
	/**
	 * Add a HTTP GET method route.
	 * 
	 * @since 0.2.4
	 * @param string $pattern The pattern to match
	 * @param string $callback The function to call when matched
	 */
	protected function get($pattern, $callback)
	{
	    $this->addMethodRoute("GET", $pattern, $callback);
	}
	
	/**
	 * Add a HTTP POST method route.
	 * 
	 * @since 0.2.4
	 * @param string $pattern The pattern to match
	 * @param string $callback The function to call when matched
	 */
	public function post($pattern, $callback)
	{
	    $this->addMethodRoute("POST", $pattern, $callback);
	}
	
	/**
	 * Add a HTTP PUT method route.
	 * 
	 * @since 0.2.4
	 * @param string $pattern The pattern to match
	 * @param string $callback The function to call when matched
	 */
	public function put($pattern, $callback)
	{
	    $this->addMethodRoute("PUT", $pattern, $callback);
	}
	
	/**
	 * Add a HTTP DELETE method route.
	 * 
	 * @since 0.2.4
	 * @param string $pattern The pattern to match
	 * @param string $callback The function to call when matched
	 */
	public function delete($pattern, $callback)
	{
	    $this->addMethodRoute("DELETE", $pattern, $callback);
	}
	
	/**
	 * Checks the lists of routes against the given request.
	 * If there is a match, then the callback method is invoked.
	 *
	 * @since 0.1.0
	 * @return bool True if the the request was routed, false otherwise
	 */
	public function route()
	{
		$request = Backbone::$request;
		$success = false;
		$uri = $request->here();
		
		// reset the last matched pattern and args
		$this->pattern = "";
		$this->arguments = array();
		
		if(!$this->onPreMatchHook($uri)) {
			return true;
		}
		if($this->root != "") {
			if(strpos($uri, $this->root) != 0)
				return false; // skip these routes
		}
		
		foreach($this->_routes as $pattern => $route) {
			if($this->routeMatches($uri, $pattern)) {
				// url match
				if(is_array($route)) {
				    // get the http method
		            $method = strtoupper(Backbone::$request->method());
		            if(isset($route[$method])) {
		                $callback = $route[$method];
		            } else {
		                // no method match...
		                $this->response = new Response();
            			$this->response->status(405);
            			$this->response->header("Allow", strtoupper(join(", ", array_keys($route))));
            			return true;
		            }
				} else {
				    // route is the callback method
				    $callback = $route;
				}
				if(method_exists($this, $callback)) {
					// check pre route hook
					if($this->onPreRouteHook($uri)) {
					    // routeMatches stores args in $this->arguments
						$this->invokeRouteCallback(array($this, $callback), $this->arguments);
					}
					$success = true;
				}
				break;
			}
		}
		return $success;
	}
	
	/**
	 * This function takes a callback and optional params and invokes the
	 * callback in the context of a properly initialized router.
	 *
	 * @since 0.2.3
	 * @param string|array $callback A valid php callback
	 * @param array $params Optional array of parameters for the callback.
	 */
	public function invokeRouteCallback($callback, $params = array())
	{
		// initialize the response and view objects
		$this->view = $this->createView();
		$this->response = new Response();
		$this->response->header("X-Backbone-Version", Backbone::version());
		
		// call the callback method
		// if there is an uncaught application exception, then a 500 error is sent
		ob_start();
		try {
			if(count($params) > 0) {				
				$return = call_user_func_array($callback, $params);
			} else {
				$return = call_user_func($callback);
			}
			$this->onPostRouteHook($return);
		} catch(Exception $e) {
			// return a 500 error
			$this->handleException($e);
		}
		// send the response and flush any output
		$this->sendResponse();
		ob_end_flush();
	}
	
	/**
	 * Loads a view file directly 
	 *
	 * @since 0.1.0
	 * @param string $name The name of the view
	*/
	public function loadView($name)
	{
		if($this->view) {
			$this->view->load($name);
		}
	}
	
	/**
	 * Get the matched pattern string.
	 * 
	 * @since 0.1.1
	 * @return string The last matched pattern or an empty string if none.
	 */
	public function getMatchedPattern()
	{
		return $this->pattern;
	}
	
	/**
	 * Get the active response object.
	 * 
	 * @since 0.2.4
	 * @return Response The active response object.
	 */
	public function getResponse()
	{
	    return $this->response;
	}
	
	/**
	 * Immediately stops execution of the script and returns the response code
	 * and body passed in as parameters OR it returns the headers contained in
	 * the Router's active response object.
	 * 
	 * @since 0.2.4
	 * @param int $status The status code
	 * @param string $body The message body
	 */
	public function stop($status = null, $body = null)
	{
	    if($stats === null && $body === null) {
	        $this->response->send();
	        exit(0);
	    }
	    if($body === null) {
	        $body = "";
	    }
	    
	    $resp = new Response();
	    $resp->status($status);
	    $resp->body($body);
	    $resp->send();
	    exit(0);
	}
	
	/**
	 * Stub function for providing a hook before a route is matched.
	 *
	 * You can prevent the route from being matched by returning false.
	 * This allows you to bypass the default route matching mechanism.
	 *
	 * @since 0.1.0
	 * @return bool
	 */
	public function onPreMatchHook($url)
	{
		return true;
	}
	
	/**
	 * Stub function for providing a hook before a route is dispatched.
	 *
	 * You can prevent the route from being dispatched by returning false.
	 * Useful for authentication logic.
	 *
	 * @since 0.1.0
	 * @return bool
	 */
	public function onPreRouteHook($url)
	{
		return true;
	}
	
	/**
	 * Stub function for providing a hook after a route is dispatched.
	 *
	 * It is past the return value of the router method that was invoked.
	 *
	 * @since 0.1.0
	 * @param bool $response The return value of the router method that was invoked.
	 */
	public function onPostRouteHook($response)
	{
		return true;
	}
	
	/**
	 * Initializes a view object for the current route.
	 *
	 * Child classes of Router can override this method to use a custom class
	 * descended from View.
	 *
	 * @since 0.2.3
	 * @return View An instantiated view object.
	 */
	protected function createView()
	{
		return new View();
	}
	
	/**
	 * Adds a route based on a given HTTP method.
	 * 
	 * @since 0.2.4
	 * @param string $method The HTTP method.
	 * @param string $pattern The pattern to match
	 * @param string $callback The function to call when matched
	 */
	public function addMethodRoute($method, $pattern, $callback)
	{
	    if(isset($this->_routes[$pattern])) {
	        $route = $this->_routes[$pattern];
	        if(is_array($route)) {
	            $route[$method] = $callback;
				$this->_routes[$pattern] = $route;
	        }
	    } else {
	        $this->_routes[$pattern] = array();
	        $this->_routes[$pattern][$method] = $callback;
	    }
	}
	
	/**
	 * Determines whether a given route matches a page route pattern.
	 *
	 * The matched pattern is saved to $this->pattern.
	 * The matched arguments are saved to $this->arguments.
	 *
	 * @since 0.2.4
	 * @param string $url The url fragement to match the pattern against
	 * 		Ex: /page/to/url/
	 * @param string $pattern The page's route pattern as defined in the page.
	 * @return bool True if the route matches the pattern.
	 */
	protected function routeMatches($url, $pattern)
	{
		// convert the pattern placeholders to regex style
		$test = str_replace(array(":alphanum", ":alpha", ":number"), array("([a-z0-9_\-]+)", "([a-z_\-]+)", "([0-9]+)"), $pattern);
		
		$params = array();
		if(preg_match("{^".$test."$}", $url, $params)) {
			// we have a match
			if(count($params) > 0) {
				$params = array_slice($params, 1);
			}
			$this->pattern = $pattern;
			$this->arguments = $params;
			return true;
		}
		return false;
	}
	
	/**
	 * Sends the response back to the client via the $this->response object.
	 *
	 * @since 0.2.0
	 */
	protected function sendResponse()
	{
		$this->response->send();
	}
	
	/**
	 * Handles an internal exception by sending a HTTP 500.
	 * 
	 * The error message will be included in the custom header X-Backbone-Exception.
	 *
	 * Applications can capture a 500 error and show a custom error page by
	 * binding an action to the "response.500" global event through the Events
	 * object.
	 *
	 * @since 0.2.0
	 * @param Exception $e The Exception object
	 */
	protected function handleException($e)
	{
		$resp = $this->response;
		$resp->status(500);
		$resp->header("X-Backbone-Exception", get_class($e).": ".$e->getMessage()." in ".$e->getFile()."(".$e->getLine().")");
		$resp->send();
		ob_end_clean();
		exit();
	}
};

?>
