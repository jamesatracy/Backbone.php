<?php
/**
 * Backbone.php
 * 
 * @author	James Tracy <james.a.tracy@gmail.com>
 * @copyright	2012-2013
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link https://github.com/jamesatracy/Backbone.php GitHub Page
 */

class Router
{
	/** 
	 * @var array The array of route patterns to match the request against 
	 *
	 * Each Router pattern is stored as follows:
	 *	$_routes['GET']['/path/to/:model/:id/'] = 
	 *		array(
	 *			'regex' => '/path/to/([a-z0-9_\-]+)/([a-z0-9_\-]+)/',
	 *			'callback' => '/controllers/Controller@handleRoute;
	 *		);
	 */
	protected static $_routes = array();
	
	/** @var array Alias mapping to route patterns */
	protected static $_aliases = array();
	
	/** @var string The pattern assigned to the current Router instance */
	protected $_route = "";
	
	/**
	 * Creates a new Router object
	 * @since 0.3.0
	 * @constructor
	 * @param string $method The HTTP method.
	 * @param string $route The route to match.
	 * @param string $callback Callback method.
	 */
	public function __construct($method, $route, $callback)
	{
		
		$this->_route = $route;
		if(!isset(self::$_routes[$method])) {
			self::$_routes[$method] = array();
		}
		
		self::$_routes[$method][$route] = array(
			"regex" => preg_replace("/(:[a-z0-9_\-]+)/", "([a-z0-9_\-]+)", $route),
			"callback" => $callback
		);
	}
	
	public static function get($route, $callback)
	{
		return new Router("GET", $route, $callback);
	}
	
	public static function post($route, $callback)
	{
		return new Router("POST", $route, $callback);
	}
	
	public static function put($route, $callback)
	{
		return new Router("PUT", $route, $callback);
	}
	
	public static function delete($route, $callback)
	{
		return new Router("DELETE", $route, $callback);
	}
	
	public static function patch($route, $callback)
	{
		return new Router("PATCH", $route, $callback);
	}
	
	public static function method($method, $route, $callback)
	{
		return new Router($method, $route, $callback);
	}
	
	/**
	 * Dispatch the request.
	 *
	 * The dispatch method will either return value returned by the
	 * callback handler for the matched route, or false if no routes
	 * were matched.
	 *
	 * @since 0.3.0
	 * @param Request $request The request object.
	 * @return Response|bool The response or false if there is no response.
	 */
	public static function dispatch($request)
	{
		$response = false;
		$method = $request->getMethod();
		if(!isset(self::$_routes[$method])) {
			return false;
		}
		// get all routes for this http method
		$routes = self::$_routes[$method];
		$path = $request->getPath();
		if(isset($routes[$path])) {
			// exact match
			$route = $routes[$path];
			$callback = $route['callback'];
			Events::trigger("router.match", $request, $path);
			// invoke the callback method
			$response = self::invokeCallback($callback);
		} else {
			// may container route parameters
			foreach($routes as $pattern => $route) {
				$regex = $route['regex'];
				$callback = $route['callback'];
				$params = array();
				if(preg_match("{^".$regex."$}", $path, $params)) {
					// we have a match
					Events::trigger("router.match", $request, $path);
					if(count($params) > 0) {
						$params = array_slice($params, 1);
					}
					// invoke the callback method
					$response = self::invokeCallback($callback, $params);
				}
			}
		}
		
		if(is_string($response)) {
			$response = Response::create(200, $response);
		}
		return $response;
	}
	
	/**
	 * Returns a route for a given alias, if one exists. Any parameter
	 * placeholders in the route will be replaced by the given array
	 * of arguments.
	 *
	 * @since 0.3.0
	 * @param string $alias The alias name.
	 * @param array $args Optional route parameters.
	 * @return string|bool Returns the route or false if the alias 
	 *	does not exist.
	 */
	public static function getRouteFromAlias($alias, $args = array())
	{
		if(isset(self::$_aliases[$alias])) {
			$route = self::$_aliases[$alias];
			if(count($args) > 0) {
				$patterns = array();
				foreach($args as $val) {
					$patterns[] = "/(:[a-z0-9_\-]+)/";
				}
				return preg_replace($patterns, $args, $route, 1);
			} 
			return $route;
		}
		return false;
	}
	
	/**
	 * Create an alias for the route.
	 *
	 * An alias alows you to construct a fully qualified url for a
	 * valid route, including route parameters, without the need
	 * to manually type out the path. As such, you could change the
	 * route for a path without the need to also change all references
	 * to it elsewhere.
	 *
	 * @since 0.3.0
	 * @param string $name The name of the alias.
	 * @return Router The $this object.
	 */
	public function alias($name)
	{
		self::$_aliases[$name] = $this->_route;
		return $this;
	}
	
	/**
	 * Invoke a route's callback method.
	 * 
	 * Fires a router.controller event.
	 *
	 * @since 0.3.0
	 * @param string|array $callback The callback.
	 * @param array $args Optional arguments for the callback.
	 * @return mixed The return value of the callback method.
	 */
	protected static function invokeCallback($callback, $args = array())
	{
		$response = false;
		Events::trigger("router.controller", $callback, $args);
		// invoke the callback method
		if(is_array($callback)) {
			$response = call_user_func_array($callback, $args);
		} else {
			if(strpos($callback, "@") > -1) {
				$tmp = explode("@", $callback);
				$classpath = $tmp[0];
				$classname = Backbone::getClassName($classpath);
				$method = $tmp[1];
				Backbone::uses($classpath);
				$obj = new $classname();
				if(method_exists($obj, $method)) {
					$response = call_user_func_array(array($obj, $method), $args);
				}
			} else {
				$response = call_user_func_array($callback, $args);
			}
		}
		return $response;
	}
}
?>