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
 * The Request object for the current request is accessible through the global
 * static Request object and its methods.
 * 
 * @since 0.1.0
 */
 
// Load required framework classes
require_once(FRAMEWORK."Request.class.php");
require_once(FRAMEWORK."Response.class.php");
require_once(FRAMEWORK."Router.class.php");
require_once(FRAMEWORK."Events.class.php");

class Backbone
{		
	/** @var array List of loaded modules */
	public static $modules = array();
	
	/** @var Request The active Backbone Request object */
	public static $request = null;
	
	/** @var string Version info */
	protected static $version = "0.3.0";
	
	/**
	 * Start backbone. Creates a request and attempts to dispatch the route.
	 * @since 0.3.0
	 */
	public static function start()
	{
		$request = Request::create();
		Backbone::$request = $request;
		try {
			// (1) request
			$response = Events::trigger("backbone.request", $request);
			if($response && get_class($response) === "Response") {
				return self::sendResponse($response);
			}
			// (2) dispatch
			$response = Router::dispatch($request);
			if($response && get_class($response) === "Response") {
				return self::sendResponse($response);
			}
			// (3) 404
			$response = Response::create(404, "");
			return self::sendResponse($response);
		} catch(Exception $e) {
			// uncaught exception
			$response = Events::trigger("backbone.exception", $request, $e);
			if($response && get_class($response) === "Response") {
				return self::sendResponse($response);
			}
			// 500
			$response = Response::create(500, "")
			->header("X-Backbone-Exception", get_class($e).": ".$e->getMessage());
			return self::sendResponse($response);
		}
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
	public static function uses()
	{
		$argsv = func_get_args();
		$argsc = count($argsv);
		if($argsc === 1 && is_string($argsv[0])) {
			$names = explode(",", $argsv[0]);
		} else if($argsc === 1 && is_array($argsv[0])) {
		    $names = $argsv[0];
		} else {
			$names = $argsv;
		}

		foreach($names as $classname) {
			self::loadModule($classname);
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
	public static function resolvePath($path, $filename)
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
	 * Sends the given response back to the server.
	 *
	 * Adds a X-Backbone-Version response header before sending.
	 * Triggers a backbone.response event before sending so that the
	 * application can intercept all responses and perform any
	 * processing or additional augmentation.
	 *
	 * @since 0.3.0
	 * @param Response $response The response object.
	 */
	protected static function sendResponse($response)
	{
		Events::trigger("backbone.response", $response);
		$response->header("X-Backbone-Version", Backbone::version());
		$response->send();
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
		if(!isset(self::$modules[$name])) {
			self::$modules[$name] = true;
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
