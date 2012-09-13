<?php
/*
ConnectionManager.class.php
Copyright (C) 2012 James Tracy

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/

/*
@fileoverview
Class for managing connections to data sources.

@since 0.1.0
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
		$result = $source->connect($config);
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
			self::$_connections[$name]->disconnect();		
	}
};

?>