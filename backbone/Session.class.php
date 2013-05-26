<?php
/**
 * Backbone.php
 * 
 * @author	James Tracy <james.a.tracy@gmail.com>
 * @copyright	2012-2013
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 */

Backbone::uses("DataSet");

/**
 * Generic class for Session management.
 *
 * Wraps the $_SESSION super global.
 *
 * @since 0.1.0
 */
class Session
{
	/** @var int Time the session was started */
	public static $time = 0;

	/** @var string The name of the current session */
	public static $name = "";

	/**
	 * Starts a session if it has not already been started 
	 *
	 * @since 0.1.0
	 */
	public static function start()
	{
		if(self::started()) {
			return;
		}
		session_start();
		self::$name = session_name();
		self::$time = time();
	}

	/**
	 * Check if the session has been started
	 * 
	 * @since 0.1.0
	 * @return bool True if the session is started, otherwise false.
	 */
	public static function started()
	{
		return isset($_SESSION) && session_id();
    }

	/**
	 * Loads a session by the session name
	 *
	 * @since 0.1.0
	 * @param string $name The session name
	*/
	public static function load($name)
	{
		unset($_SESSION);
		session_name($name);
		self::start();
	}

	/**
	 * Clear the session.
	 *
	 * @since 0.1.0
	 */
	public static function clear()
	{
		$_SESSION = array();
	}

	/**
	 *Destroy a session 
	 *
	 * @since 0.1.0
	 */
	public static function destroy()
	{
		if(self::started()) {
			session_destroy();
		}
		self::$name = "";
		self::$time = 0;
	}

	/**
	 * Set a value for the given key.
	 *
	 * Name can be array namespaced using the dot operator.
	 * If the namespace does not exist, it will be initialized as an empty array.
	 *
	 * Ex: "user.name" is equivalent to
	 * array("user" => array("name" => "John"))
	 *
	 * @since 0.1.0
	 * @param string $name The name of the variable.
	 * @param string $value The value for the given key.
	 */
	public static function set($name, $value)
	{
		if(!self::started()) {
			return;
		}

		$data = new DataSet($_SESSION);
		$data->set($name, $value);
		$_SESSION = $data->get();
	}

	/**
	 * Get a value for a given variable name
	 *
	 * @since 0.1.0
	 * @param string $name The name of the variable.
	 */
	public static function get($name)
	{
		if(!self::started()) {
			return;
		}

		$data = new DataSet($_SESSION);
		return $data->get($name);
	}
};
?>
