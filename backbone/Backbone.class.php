<?php
/*
Backbone.class.php
Copyright (C) 2012 James Tracy

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/

// Load required framework classes
require_once(FRAMEWORK."Request.class.php");
require_once(FRAMEWORK."Router.class.php");
require_once(FRAMEWORK."Events.class.php");
require_once(FRAMEWORK."DataSet.class.php");
require_once(FRAMEWORK."View.class.php");
require_once(FRAMEWORK."Html.class.php");

class Backbone
{	
	/* Defines the root URI directory */
	public static $root = "/";
	
	/* Global configuration object for application specific configurations */
	public static $config = null;
	
	/* List of registered routers */
	public static $routers = array();
	
	/* List of loaded modules */
	public static $modules = array();
	
	/* Global request object */
	public static $request = null;
	
	/* Version info */
	protected static $version = "0.0.1";
	
	/* Initialize Backbone.php */
	public static function initialize()
	{
		self::$config = new DataSet();
		return;
	}
	
	/* 
	Get the version info 
	
	@return [string] The version number
	*/
	public static function version()
	{
		return self::$version;
	}
	
	/*
	Include a class module.
	For framework code, the $name parameter should be the classname.
	For non-framework code, the $name parameter should begin with "/"
	
	@param [string, array] $name The class name or an array of class names
	*/
	public static function uses($name)
	{
		if(is_array($name))
		{
			foreach($name as $classname)
				self::loadModule($classname);
		}
		else
		{
			self::loadModule($name);
		}
	}
	
	/* 
	Registers a router with Backbone.php 
	
	@param [Router] $router An instance of the Router derived class
	*/
	public static function addRouter($router)
	{
		array_push(self::$routers, $router);
	}
	
	/* 
	Loads a Router class.
	Router must be located at [root]/routers/{name}.class.php
	
	@param [string] $name The name of the router
	*/
	public static function loadRouter($name)
	{
		require_once(APPPATH."routers/".$name.".class.php");
	}
	
	/* 
	Dispatches the request by trying to find a matching route among the registered routers.
	
	@param [Request] $request The active request object
	@return [boolean] true if the request was successfully routed, false otherwise
	*/
	public static function dispatch($request)
	{
		$success = false;
		foreach(self::$routers as $router)
		{
			if($router->route($request))
			{
				$success = true;
				break;
			}
		}
		if(!$success)
		{
			$here = trim($request->here(), "/");
			// No routes are defined.
			// Auto route this page if we find a corresponding view that ends in '-page'
			if(file_exists(VIEWPATH.$here."-page.php"))
			{
				$router = new Router();
				$router->loadView($here."-page");
				$success = true;
			}
		}
		return $success;
	}
	
	/*
	Include a class module. Used internally by uses().
	For framework code, the $name parameter should be the classname.
	For plugin code, the $name parameter should begin with "plugins/" 
		followed by the classname.
	For non-framework code, the $name parameter should begin with "/" and be 
		relative to the application's root directory.
	
	@param [string, array] $name The class name or an array of class names
	*/
	protected static function loadModule($name)
	{
		if(!isset(self::$modules[$name]))
		{
			self::$modules[$name] = true;
			if(substr($name, 0, 1) == "/")
			{
				// load app specific code
				$name = substr($name, 1);
				if(file_exists(ABSPATH.$name.".class.php"))
					require_once(ABSPATH.$name.".class.php");
			}
			else
			{
				// load framework code
				if(file_exists(FRAMEWORK.$name.".class.php"))
					require_once(FRAMEWORK.$name.".class.php");
			}
		}
	}
};

?>