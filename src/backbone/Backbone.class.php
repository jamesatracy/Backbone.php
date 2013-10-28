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
 * Core Backbone module.
 * 
 * Provides core functionality such as loading modules, loading routers, and
 * dispatching routes.
 * 
 * The Request object for the current request is also stored here and 
 * accessible through Backbone::$request.
 * 
 * @since 0.1.0
 */
 
// Load required framework classes
require_once(FRAMEWORK."Response.class.php");
require_once(FRAMEWORK."Request.class.php");
require_once(FRAMEWORK."Router.class.php");
require_once(FRAMEWORK."Events.class.php");
require_once(FRAMEWORK."DataMap.class.php");
require_once(FRAMEWORK."View.class.php");
require_once(FRAMEWORK."Html.class.php");

class Backbone
{	
	/** @var string Defines the root URI directory */
	public static $root = "/";
	
	/** @var DataMap Global configuration object for application specific configurations */
	public static $config = null;
	
	/** @var array List of registered routers */
	public static $routers = array();
	
	/** @var array List of loaded modules */
	public static $modules = array();
	
	/** @var Request Global request object */
	public static $request = null;
	
	/** @var string Version info */
	protected static $version = "0.2.5";
	
	/**
	 * Initialize Backbone.php 
	 * 
	 * @since 0.1.0
	 */
	public static function initialize()
	{
		self::$config = new DataMap();
		return;
	}
	
	/**
	 * Get the version info 
	 * 
	 * @since 0.1.0
	 * @return string The version number
	 */
	public static function version()
	{
		return self::$version;
	}
	
	/**
	 * Wrapper for native var_dump() function that buffers the output
	 * and returns it back to you.
	 *
	 * By default, this method handles arrays and objects differently than var_dump.
	 * For arrays, it prints out "(array)" plus the length of the array.
	 * For objects, it prints out "(object)" plust the class name.
	 *
	 * @since 0.2.0
	 * @param mixed $var The variable to dump
	 * @return string A string representation of the variable
	 */
	public static function dump($var)
	{
		if(is_array($var)) {
			return "(array) length:".count($var);
		}
		if(is_object($var)) {
			return "(object) ".get_class($var);
		}
		ob_start();
		var_dump($var);
		return ob_get_clean();
	}
	
	/**
	 * Include a class module.
	 * 
	 * For framework code, the $name parameter should be the classname.
	 * For non-framework code, the $name parameter should begin with "/"
	 * and be relative to the root directory.
	 * 
	 * @since 0.1.0
	 * @param string|array $name The class name or an array of class names
	 */
	public static function uses($name)
	{
		if(is_string($name)) {
			$name = explode(",", $name);
		}

		foreach($name as $classname) {
			self::loadModule($classname);
		}
	}
	
	/**
	 * Registers a router with Backbone.php 
	 * 
	 * @since 0.1.0
	 * @param Router $router An instance of the Router derived class
	 */
	public static function addRouter($router)
	{
		array_push(self::$routers, $router);
	}
	
	/**
	 * Loads a Router class.
	 * 
	 * Router must be located in one of the paths defined by the 
	 * ROUTERPATH global system constant.
	 * 
	 * @since 0.1.0
	 * @param string $name The name of the router
	 */
	public static function loadRouter($name)
	{
		$fullpath = self::resolvePath(ROUTERPATH, $name.".class.php");
		if($fullpath) {
			require_once($fullpath);
			if(class_exists($name)) {
				self::addRouter(new $name());
			}
		}
	}
	
	/**
	 * Resolves a path variable and a file name into a full path, if the file exists.
	 * 
	 * The path string may contain multiple paths relative to the application's root 
	 * directory, separated by a semicolon (;)
	 * 
	 * Example:
	 * 
	 * Backbone::resolvePath("/abc/views/;/def/views/", "home.php");
	 * 
	 * Might resolve to one of the following:
	 * 
	 * /abc/views/home.php
	 * /def/views/home.php
	 * 
	 * resolvePath() returns the first file that it finds, determined by the
	 * ordering in the path variable.
	 * 
	 * @since 0.1.0
	 * @param string $path One or more paths to search relative to the application root
	 * 	and separated by a semicolon
	 * @param string $filename The name of the file to search for.
	 * @return string|null Returns the full absolute path of the file, or null
	 */
	public function resolvePath($path, $filename)
	{
		if(empty($path)) {
			return null;
		}
		$path = str_replace(ABSPATH, "", $path); // backward combat with old path constants
		$paths = explode(";", $path);
		$fullpath = null;
		foreach($paths as $current) {
			if(substr($current, 0, 1) == "/") {
				$current = substr($current, 1);
			}
			if(file_exists(ABSPATH.$current.$filename)) {
				$fullpath = ABSPATH.$current.$filename;
				break;
			}
		}
		
		return $fullpath;
	}
	
	/**
	 * Get a class name from a relative path.
	 *
	 * Ex:
	 *	Backbone::className("/models/employees/Developer");
	 *	// returns "Developer"
	 *
	 * @since 0.2.1
	 * @returns string The classname
	 */
	public static function getClassName($path)
	{
		$tmparr = explode("/", $path);	// pass strict mode
		return end($tmparr);
	}
	
	/**
	 * Dispatches the request by trying to find a matching route among the registered routers.
	 * 
	 * @since 0.1.0
	 * @param Request $request The active request object
	 * @return bool Returns true if the request was successfully routed, false otherwise
	*/
	public static function dispatch($request)
	{
		$success = false;
		foreach(self::$routers as $router) {
			if($router->route()) {
				$success = true;
				break;
			}
		}
		if(!$success) {
			$here = trim($request->here(), "/");
			// No routes are defined.
			// Auto route this page if we find a corresponding view that ends in '-page'
			if(Backbone::resolvePath(VIEWPATH, $here."-page.php")) {
				$router = new Router();
				$router->invokeRouteCallback(array($router, "loadView"), array($here."-page"));
				$success = true;
			}
		}
		return $success;
	}
	
	/**
	 * Include a class module. Used internally by uses().
	 * 
	 * For framework code, the $name parameter should be the classname.
	 * 
	 * For plugin code, the $name parameter should begin with "plugins/" 
	 * followed by the classname.
	 * 
	 * For non-framework code, the $name parameter should begin with "/" and be
	 * relative to the application's root directory.
	 * 
	 * @since 0.1.0
	 * @param string|array $name The class name or an array of class names
	 */
	protected static function loadModule($name)
	{
		$classname = self::getClassName($name);
		//if(!isset(self::$modules[$name]))
		if(!class_exists($classname)) {
			//self::$modules[$name] = true;
			if(substr($name, 0, 1) == "/") {
				// load app specific code
				$name = substr($name, 1);
				if(file_exists(ABSPATH.$name.".class.php")) {
					require_once(ABSPATH.$name.".class.php");
				}
			} else {
				// load framework code
				if(file_exists(FRAMEWORK.$name.".class.php")) {
					require_once(FRAMEWORK.$name.".class.php");
				}
			}
		}
	}
};
?>
