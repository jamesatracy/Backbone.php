<?php
/*
Session.class.php
Copyright (C) 2012 James Tracy

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/

Backbone::uses("DataSet");

/*
@fileoverview
Generic class for Session management. 
Wraps the $_SESSION super global.
*/
class Session
{
	/* Time the session was started */
	public static $time = 0;
	
	/* The name of the current session */
	public static $name = "";
	
	/* The DataSet object used for getting and setting session variables */
	protected static $_data;
	
	/* Starts a session if it has not already been started */
	public static function start()
	{
		if(self::started())
			return;
		session_start();
		self::$name = session_name();
		self::$time = time();
		self::$_data = new DataSet($_SESSION);
	}
	
	/* 
	Check if the session has been started
	
	@return [boolean] True if the session is started, otherwise false.
	*/
	public static function started() 
	{
		return isset($_SESSION) && session_id();
    }
	
	/*
	Loads a session by the session name
	
	@param [string] $name The session name
	*/
	public static function load($name)
	{
		unset($_SESSION);
		session_name($name);
		self::start();
	}
	
	/* 
	Clear the session.
	*/
	public static function clear()
	{
		if(!self::$_data)
			return;
		self::$_data->clear();
	}
	
	/* Destroy a session */
	public static function destroy()
	{
		if(self::started())
			session_destroy();
		self::$name = "";
		self::$time = 0;
		self::$_data = null;
	}
	
	/*
	Set a value for the given key.
	Name can be array namespaced using the dot operator. 
	If the namespace does not exist, it will be initialized as an empty array.
	
	Ex: "user.name" is equivalent to
		array("user" => array("name" => "John"))
	
	@param [string] $name The name of the variable.
	@param [string] $value The value for the given key.
	*/
	public static function set($name, $value)
	{
		if(!self::started())
			return;
			
		self::$_data->set($name, $value);
	}
	
	/*
	Get a value for a given variable name
	
	@param [string] $name The name of the variable.
	*/
	public static function get($name)
	{
		if(!self::started())
			return;
			
		return self::$_data->get($name);
	}
};
?>