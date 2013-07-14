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
 * Utility class for working with sets of nested key, values.
 *
 * For example, it allows you to get and set values that can be expressed as:
 *
 * "products.shirts.polos" which translates to:
 * array("products" => array("shirts" => array("polos" => $value)))
 *
 * @since 0.1.0
 */
class DataSet 
{
	/** @var array The internal set */
	protected $set;
	
	/**
	 * Constructor
	 *
	 * @since 0.1.0
	 * @param array $set The initial set. Defaults to an empty array.
	 */
	public function __construct($set = array())
	{
		$this->_set = &$set;
	}
	
	/**
	 * Clear the set.
	 *
	 * @since 0.1.0
	 */
	public function clear()
	{
		$this->_set = array();
	}
	
	/**
	 * Set a value for the given key.
	 *
	 * Name can be array namespaced using the dot operator. 
	 * If the namespace does not exist, it will be initialized as an empty array.
	 *
	 * Ex: "user.name" is equivalent to
	 *	array("user" => array("name" => "John"))
	 *
	 * @since 0.1.0
	 * @param string $name The name of the variable.
	 * @param string $value The value for the given key.
	 */
	public function set($name, $value)
	{
		if(empty($name)) {
			return;
		}
			
		if(strpos($name, ".") === FALSE) {
			// single
			$write = array($name);
		} else {
			// namespaced
			$write = explode(".", $name);
		}

		$current = &$this->_set;
		foreach($write as $val) {
			if(!isset($current[$val])) {
				$current[$val] = array();
			}
			$current = &$current[$val];
		}
		$current = $value;
	}
	
	/**
	 * Get a value for a given variable name
	 *
	 * @since 0.1.0
	 * @param string $name The name of the variable.
	 */
	public function get($name = null)
	{
		if(empty($name)) {
			return $this->_set;
		}
			
		if(strpos($name, ".") === FALSE) {
			// single
			$write = array($name);
		} else {
			// namespaced
			$write = explode(".", $name);
		}

		$current = &$this->_set;
		foreach($write as $val) {
			if(!isset($current[$val])) {
				// value does not exist
				return null;
			}
			$current = &$current[$val];
		}
		return $current;
	}
	
	/**
	 * Whether a value for a given variable name exists
	 *
	 * @since 0.1.0
	 * @param string $name The name of the variable.
	 */
	public function has($name = null)
	{
		if(empty($name)) {
			return false;
		}
			
		if(strpos($name, ".") === FALSE) {
			// single
			$write = array($name);
		} else {
			// namespaced
			$write = explode(".", $name);
		}

		$current = &$this->_set;
		foreach($write as $val) {
			if(!isset($current[$val])) {
				// value does not exist
				return false;
			}
			$current = &$current[$val];
		}
		return true;
	}
};
?>