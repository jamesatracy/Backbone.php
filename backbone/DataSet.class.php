<?php
/*
DataSet.class.php
Copyright (C) 2012 James Tracy

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/

/*
@fileoverview
Utility class for working with sets of nested key, values.
For example, it allows you to get and set values that can be expressed as:

	"products.shirts.polos" which translates to:
	
	array("products" => array("shirts" => array("polos" => $value)))
	
@since 0.1.0
*/

class DataSet 
{
	/* The internal set */
	protected $set;
	
	/*
	Constructor
	
	@param [array] $set The initial set. Defaults to an empty array.
	*/
	public function __construct($set = array())
	{
		$this->_set = &$set;
	}
	
	/* 
	Clear the set.
	*/
	public function clear()
	{
		$this->_set = array();
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
	public function set($name, $value)
	{
		if(empty($name))
			return;
			
		if(strpos($name, ".") === FALSE)
		{
			// single
			$write = array($name);
		}
		else
		{
			// namespaced
			$write = explode(".", $name);
		}

		$current = &$this->_set;
		foreach($write as $val)
		{
			if(!isset($current[$val]))
				$current[$val] = array();
			$current = &$current[$val];
		}
		$current = $value;
	}
	
	/*
	Get a value for a given variable name
	
	@param [string] $name The name of the variable.
	*/
	public function get($name = null)
	{
		if(empty($name))
			return $this->_set;
			
		if(strpos($name, ".") === FALSE)
		{
			// single
			$write = array($name);
		}
		else
		{
			// namespaced
			$write = explode(".", $name);
		}

		$current = &$this->_set;
		foreach($write as $val)
		{
			if(!isset($current[$val]))
				// value does not exist
				return null;
			$current = &$current[$val];
		}
		return $current;
	}
	
	/*
	Whether a value for a given variable name exists
	
	@param [string] $name The name of the variable.
	*/
	public function has($name = null)
	{
		if(empty($name))
			return false;
			
		if(strpos($name, ".") === FALSE)
		{
			// single
			$write = array($name);
		}
		else
		{
			// namespaced
			$write = explode(".", $name);
		}

		$current = &$this->_set;
		foreach($write as $val)
		{
			if(!isset($current[$val]))
				// value does not exist
				return false;
			$current = &$current[$val];
		}
		return true;
	}
};
?>