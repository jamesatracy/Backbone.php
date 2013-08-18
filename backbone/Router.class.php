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
	
	/** @var string The root of the router's url maps, if any */
	public $root = "";
	
	/**
	 * constructor
	 */
	public function __construct()
	{
		$this->view = new View();
		$this->response = new Response();
		$this->response->header("X-Backbone-Version", Backbone::version());
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
	 * Checks the lists of routes agaisnt the given request
	 *
	 * @since 0.1.0
	 * @return [boolean] true if the the request was routed, false otherwise
	 */
	public function route($request)
	{
		$request = Backbone::$request;
		$success = false;
		$uri = $request->here();
		
		// reset the last matched pattern
		$this->pattern = "";
		
		if(!$this->onPreMatchHook($uri)) {
			return true;
		}
		if($this->root != "") {
			if(strpos($uri, $this->root) != 0)
				return false; // skip these routes
		}
		
		foreach($this->_routes as $pattern => $callback) {
			$original = $pattern;
			$pattern = str_replace(array(":alphanum", ":alpha", ":number"), array("([a-z0-9_\-]+)", "([a-z_\-]+)", "([0-9]+)"), $pattern);
			if(preg_match("{^".$pattern."$}", $uri, $matches)) {
				// url match
				$this->pattern = $original;
				if(method_exists($this, $callback)) {
					if($this->onPreRouteHook($uri)) {
						// call the callback method
						// if there is an uncaught application exception, then a 500 error is sent
						ob_start();
						try {
							if(count($matches) > 1) {
								$params = array_slice($matches, 1);				
								$return = call_user_func_array(array($this, $callback), $params);
							} else {
								$return = call_user_func(array($this, $callback));
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
					$success = true;
				}
				break;
			}
		}
		return $success;
	}
	
	/**
	 * Loads a view file directly 
	 *
	 * @since 0.1.0
	 * @param string $name The name of the view
	*/
	public function loadView($name)
	{
		$this->view->load($name);
	}
	
	/**
	 * Get the matched pattern string
	 * 
	 * @since 0.1.1
	 * @return string The last matched pattern or an empty string if none.
	 */
	public function getMatchedPattern()
	{
		return $this->pattern;
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
	 * @since 0.2.0
	 * @protected
	 * @param Exception $e The Exception object
	 */
	protected function handleException($e)
	{
		$resp = $this->response;
		$resp->status(500);
		$resp->header("X-Backbone-Exception", $e->getMessage());
		$resp->send();
		ob_end_clean();
		// trigger a 500 error response event
		// the application may bind to this event and present a custom 500 page
		Events::trigger("response.500");
		exit();
	}
};

?>
