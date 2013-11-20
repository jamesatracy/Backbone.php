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
 * View class implementation.
 *
 * Example:
 *		$response = View::create("home");
 *
 * @since 0.1.0
 */
class View
{	
	/** @var Request The active request object */
	public $request = null;
	
	/** @var string The name of this view */
	public $name = "";
	
	/** @var array Array of view properties */
	protected $_properties = array();
	
	/** @var array Array of HTML blocks defined for the view */
	protected $_blocks = array();
	
	/** @var string The name of the active block */
	protected $_active_block = null;
	
	/** @var string The mode of the active block, either 'overwrite', 'append', or 'prepend' */
	protected $_active_mode = "overwrite";
	
	/** @var array Array of view extensions, or parents */
	protected $_extensions = array();
	
	/**
	 * @constructor
	 * @since 0.3.0
	 * @param Request $request The request object.
	 * @param string $name The name of the view file to load.
	 */
	public function __construct($request, $name)
	{
		$this->request = $request;
		$this->name = $name;
	}
	
	/**
	 * Create a new view with the given view file and load it.
	 * @since 0.3.0
	 * @param Request $request The request object.
	 * @param string $name The name of the view file to load.
	 * @return Response The response object.
	 */
	public static function create($request, $name)
	{
		$view = new View($request, $name);
		Events::trigger("view.create", $name, $view);
		return $view->load($name);
	}
	
	/**
	 * Shortcut for view code to construct an internal URL. This method
	 * will echo the fully resolved URL string.
	 *
	 * @since 0.2.3
	 * @param string $path The relative url path.
	 * @param bool $ssl Force the url to be secure.
	 */
	public function url($path, $ssl = false)
	{
		$request = $this->request;
		$link = $request->getScheme()."://".$request->getHost();
		if($path) {
			if(substr($path, 0, 1) == "/") {
				$path = substr($path, 1);
			}
			$link .= $request->getBasePath().$path;
		} else {
			$link .= $request->getBasePath();
		}
		
		if($ssl) {
			if(stripos($link, "http://") !== false) {
				$link = str_replace("http://", "https://", $link);
			}
		}
		
		return $link;
	}
	
	/**
	 * Load a view to execute from the application's /views/ directory
	 *
	 * Examples: 
	 *
	 *	To load /views/about.php:
	 *  $view = new View($request, "about");
	 *  $view->load();
	 *	
	 *	To load /views/products/hammer.php
	 *  $view = new View($request, "products/hammer");
	 * 	$view->load();
	 *
	 * @since 0.1.0
	 * @param string name The name of the view.
	 * @return Response The response object.
	 */
	public function load()
	{
		$name = $this->name;
		
		ob_start();
		if(substr($name, 0, 1) == "/") {
			$name = substr($name, 1);
		}
		$fullpath = Backbone::resolvePath(VIEWPATH, $name.".php");
		if(!$fullpath) {
		    throw new InvalidArgumentException("View: Not found: ".$name);
		}
		
		require($fullpath);
		
		// load extensions
		$count = count($this->_extensions);
		for($i = $count - 1; $i >= 0; $i--) {
			$name = $this->_extensions[$i];
			$fullpath = Backbone::resolvePath(VIEWPATH, $name.".php");
			if($fullpath) {
				require($fullpath);
			}
		}
		$ob = ob_get_clean();
		return Response::create(200, $ob);
	}
	
	/**
	 * Display a view file directly from the application's /views/ directory
	 *
	 * Exs: 
	 *
	 *	To include /views/about.php:	
	 *  $view->display("about");
	 *	
	 * To include /views/products/hammer.php
	 * $view->display("products/hammer");
	 *
	 * @since 0.1.0
	 * @param string name The name of the view
	 */
	public function display($name)
	{
		if(substr($name, 0, 1) == "/") {
			$name = substr($name, 1);
		}
		$fullpath = Backbone::resolvePath(VIEWPATH, $name.".php");
		if($fullpath) {
			require($fullpath);
		}
	}
	
	/**
	 * Extend another view.
	 *
	 * This defines the current view as a child of another view.
	 * The parent view will be executed after the child, allowing the child to define
	 * blocks for the parent to render.
	 *
	 * NOTE: Child views are executed in the reverse order in which they are extended.
	 *
	 * @since 0.1.0
	 * @param string $name The name of the view
	 */
	public function extend($name)
	{
		if(substr($name, 0, 1) == "/") {
			$name = substr($name, 1);
		}
		$fullpath = Backbone::resolvePath(VIEWPATH, $name.".php");
		if($fullpath) {
			$this->_extensions[] = $name;
		}
	}
	
	/**
	 * Determine whether or not a given view exists.
	 * 
	 * @since 0.2.3
	 * @param string $name The name of the view
	 * @return bool True if the view exists, false otherwise.
	 */
	public function exists($name)
	{
		$fullpath = Backbone::resolvePath(VIEWPATH, $name.".php");
		return (!empty($fullpath));
	}
	
	/**
	 * Set a property's value
	 *
	 * Ex:
	 *	$view->set("title", "The Title");
	 *	$view->set(array("title" => "The Title", "posts" => $posts);
	 *
	 * @param string|array $key The name of the property or an array of key value pairs
	 * @param string|null $value Optional. The value of the property, or null if $key is an array
	 */
	public function set($key, $value = null)
	{
		if($value) {
			$this->_properties[$key] = $value;
		} else {
			if(is_array($key)) {
				foreach($key as $k => $v) {
					$this->_properties[$k] = $v;
				}
			}
		}
	}
	
	/**
	 * Get a property's value
	 *
	 * @since 0.1.0
	 * @param string $key The name of the property
	 */
	public function get($key)
	{
		if(isset($this->_properties[$key])) {
			return $this->_properties[$key];
		}
		return null;
	}
	
	/**
	 * Push a value onto a user defined stack property.
     *
	 * If it doesn't exist, then it will be created.
	 *
	 * @since 0.1.0
	 * @param string $key The name of the property
	 * @param mixed $value The value to push onto the stack
	 */
	public function push($key, $value)
	{
		if(!isset($this->_properties[$key]) || $this->_properties[$key] == null) {
			$this->_properties[$key] = array();
		}
		array_push($this->_properties[$key], $value);
	}
	
	/**
	 * Pop a value off of a user defined stack property.
	 *
	 * @since 0.1.0
	 * @param string $key The name of the property
	 */
	public function pop($key)
	{
		if(isset($this->_properties[$key]) && is_array($this->_properties[$key]))
			return array_pop($this->_properties[$key]);
		return null;
	}
	
	/**
	 * Define a HTML block. 
	 *
	 * This will overwrite any existing block with the same name.
	 *
	 * @since 0.1.0
	 * @param string $key The name of the block
	 */
	public function define($key)
	{
		if(!isset($this->_blocks[$key])) {
			$this->_blocks[$key] = "";
		}
		$this->_active_block = $key;
		$this->_active_mode = "overwrite";
		ob_start();
	}
	
	/**
	 * Check whether or not an HTML block is defined.
	 *
	 * @since 0.1.0
	 * @param string $key The name of the block
	 * @return bool True if the block exists.
	 */
	public function isDefined($key)
	{
		return (isset($this->_blocks[$key]));
	}
	
	/**
	 * Append to an existing HTML block.
	 *
	 * This will create a new block if it was not defiend by define()
	 *
	 * @param string $key The name of the block
	 * @param string|null $html Optional. An HTML snippet to append to the block.
	 *		If this is not defined, then it includes everything on the page until the next $view->end();
	 */
	public function append($key, $html = null)
	{
		if(!isset($this->_blocks[$key])) {
			$this->_blocks[$key] = "";
		}
		if($html) {
			$this->_blocks[$key] .= $html;
		} else {
			$this->_active_block = $key;
			$this->_active_mode = "append";
			ob_start();
		}
	}
	
	/**
	 * Prepend to an existing HTML block. 
	 *
	 * This will create a new block if it was not defiend by define()
	 *
	 * @param string $key The name of the block
	 * @param string|null $html Optional. An HTML snippet to prepend to the block. 
	 *	If this is not defined, then it includes everything on the page until the next $view->end();
	 */
	public function prepend($key, $html = null)
	{
		if(!isset($this->_blocks[$key])) {
			$this->_blocks[$key] = "";
		}
		if($html) {
			$this->_blocks[$key] = $html . $this->_blocks[$key];
		} else {
			$this->_active_block = $key;
			$this->_active_mode = "prepend";
			ob_start();
		}
	}
	
	/**
	 * End a HTML block.
	 *
	 * Must be preceded by a define() or append() call.
	 *
	 * @since 0.1.0
	 * @param string $key The name of the block
	 */
	public function end()
	{
		if(isset($this->_blocks[$this->_active_block])) {
			$ob = ob_get_clean();
			if($this->_active_mode == "append") {
				// append
				$this->_blocks[$this->_active_block] .= $ob;
			} else if($this->_active_mode == "prepend") {
				// append
				$this->_blocks[$this->_active_block] = $ob . $this->_blocks[$this->_active_block];
			} else {
				// overwrite
				$this->_blocks[$this->_active_block] = $ob;
			}
		}
	}
	
	/**
	 * Clear a block's value
	 *
	 * @since 0.1.0
	 * @param string $key The name of the block
	 */
	public function clear($key)
	{
		if(isset($this->_blocks[$key])) {
			$this->_blocks[$key] = "";
		}
	}
	
	/**
	 * Render a block of HTML on the page.
	 *
	 * Does not render anything if the block was not previously defined.
	 *
	 * @since 0.1.0
	 * @param string $key The name of the block
	 */
	public function render($key)
	{
		if(isset($this->_blocks[$key])) {
			echo $this->_blocks[$key];
		}
	}
};
?>