<?php
/**
 * Backbone.php
 * 
 * @author	James Tracy <james.a.tracy@gmail.com>
 * @copyright	2012-2013
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 */

/**
 * Class for managing connections to data sources.
 * 
 * @since 0.1.0
 */
class Connections
{
	/* List of open connections */
	protected static $_connections = array();
	
	/*
	Open a new connection.
	
	@param [string] $name The name of the connection
	@param [string] $type The class name of the DataSource
	@param [array] $config The DataSource configuration
	@return [boolean] True if successful
	*/
	public static function create($name, $type, $config)
	{
		if(!$name || empty($name))
			$name = "default";
		if(isset(self::$_connections[$name]))
			self::$_connections[$name]->disconnect();
		$source = new $type;
		$result = $source->connect($config, $name);
		self::$_connections[$name] = $source;
		return $source;
	}
	
	/*
	Get a connection.
	
	@param [string] $name The name of the connection
	@return [DataSource,null] The DataSource object or null
	*/
	public static function get($name)
	{
		if(isset(self::$_connections[$name]))
			return self::$_connections[$name];
		return null;
	}
	
	/*
	Remove a connection.
	
	@param [string] $name The name of the connection
	*/
	public static function remove($name)
	{
		if(isset(self::$_connections[$name]))
		{
			self::$_connections[$name]->disconnect();
			unset(self::$_connections[$name]);
		}
	}
	
	/* 
	Close all active connections
	*/
	public static function closeAll()
	{
		foreach(self::$_connections as $name => $db)
		{
			$db->disconnect();
			unset(self::$_connections[$name]);
		}
	}
	
	/*
	Get a list of all active connections
	*/
	public static function enumerateConnections()
	{
		return array_keys(self::$_connections);
	}
};

?>