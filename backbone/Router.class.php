<?php
/*
Router.class.php
Copyright (C) 2012 James Tracy

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/

class Router 
{
	/* Array of route mappings */
	protected $_routes = array();
	
	/* The active view class for the request */
	protected $view;
	
	public function __construct()
	{
		$this->view = new View();
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
		foreach($this->_routes as $pattern => $callback)
		{
			$pattern = str_replace(array(":alpha", ":alphanum", ":number"), array("([a-z_]+)", "([a-z0-9_]+)", "([0-9]+)"), $pattern);
			if(preg_match("{^".$pattern."$}", $uri, $matches))
			{
				// url match
				if(method_exists($this, $callback))
				{
					// call the callback method
					if(count($matches) > 1)
					{
						$params = array_slice($matches, 1);				
						call_user_func_array(array($this, $callback), $params);
					}
					else
					{
						call_user_func(array($this, $callback));
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
};

?>