<?php
/**
 * Backbone.php
 * 
 * @author	James Tracy <james.a.tracy@gmail.com>
 * @copyright	2012-2013
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 */

/**
 * Base class for all Backbone.php routers.
 *
 * @since 0.1.0
 */
class Router 
{
	/* Array of route mappings */
	protected $_routes = array();
	
	/* The active view class for the request */
	protected $view;
	
	/* The root of the router's url maps, if any */
	public $root = "";
	
	public function __construct()
	{
		$this->view = new View();
	}
	
	/*
	Get the list of routes for this Router
	
	@return [array] The array of routes
	*/
	public function getRoutes()
	{
		return $this->_routes;
	}
	
	/*
	Add a route or routes mapping to the Router
	Ex: add("/about/", "about")
	Ex: add(array("/about/" => "about", "/products/" => "products"
	
	@param [array, string] $routes Either an array of routes (path => callback method) or a path
	@param [string] $callback The callback method, if $routes is a string, otherwise null
	*/
	protected function add($routes, $callback = null)
	{
		if(is_array($routes))
		{
			foreach($routes as $key => $route)
				$this->_routes[$key] = $route;
		}
		else
		{
			if($callback)
			{
				$this->_routes[$routes] = $callback;
			}
		}
	}
	
	/*
	Checks the lists of routes agaisnt the given request
	
	@return [boolean] true if the the request was routed, false otherwise
	*/
	public function route($request)
	{
		$request = Backbone::$request;
		$success = false;
		$uri = $request->here();
		if(!$this->onPreMatchHook($uri))
			return true;
		if($this->root != "")
		{
			if(strpos($uri, $this->root) != 0)
				return false; // skip these routes
		}
		foreach($this->_routes as $pattern => $callback)
		{
			$original = $pattern;
			$pattern = str_replace(array(":alphanum", ":alpha", ":number"), array("([a-z0-9_\-]+)", "([a-z_\-]+)", "([0-9]+)"), $pattern);
			if(preg_match("{^".$pattern."$}", $uri, $matches))
			{
				// url match
				$this->pattern = $original;
				if(method_exists($this, $callback))
				{
					if($this->onPreRouteHook($uri))
					{
						// call the callback method
						if(count($matches) > 1)
						{
							$params = array_slice($matches, 1);				
							$return = call_user_func_array(array($this, $callback), $params);
						}
						else
						{
							$return = call_user_func(array($this, $callback));
						}
						$this->onPostRouteHook($return);
					}
					$success = true;
				}
				break;
			}
		}
		return $success;
	}
	
	/* 
	Loads a view file directly 
	
	@param [string] $name The name of the view
	*/
	public function loadView($name)
	{
		$this->view->load($name);
	}
	
	/*
	Stub function for providing a hook before a route is matched.
	You can prevent the route from being matched by returning false.
	This allows you to bypass the default route matching mechanism.
	*/
	public function onPreMatchHook($url)
	{
		return true;
	}
	
	/*
	Stub function for providing a hook before a route is dispatched.
	You can prevent the route from being dispatched by returning false.
	Useful for authentication logic.
	*/
	public function onPreRouteHook($url)
	{
		return true;
	}
	
	/*
	Stub function for providing a hook after a route is dispatched.
	It is past the return value of the router method that was invoked.
	
	@param [mixed] $response The return value of the router method that was invoked.
	*/
	public function onPostRouteHook($response)
	{
		return true;
	}
};

?>