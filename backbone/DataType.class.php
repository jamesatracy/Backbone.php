<?php
/*
DataType.class.php
Copyright (C) 2012 James Tracy

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/

/*
@fileoverview
Helper class for working with data types.

@since 0.1.0
*/

class DataType
{
	/*
	Print the data and its type
	
	@param [mixed] $var The variable
	@return [string] The printed variable
	*/
	public static function export($var)
	{
		$type = self::type($var);
		
		if($type === "boolean")
			return ($var) ? "true" : "false";
		if($type === "integer")
			return "(integer) ".$var;
		if($type === "float")
			return "(float) ".$var;
		if($type === "string")
			return "(string) '".trim($var)."'";
		if($type === "null")
			return "(null)";
		if($type === "array")
			return "(array)";
		if($type === "resource")
			return "(resource)";
		return "(object) ".$type;
	}
	
	/*
	Get the data type of the variable
	
	@param [mixed] $var The variable
	@return [string] The data type.
	*/
	public static function type($var) 
	{
        if (is_object($var)) 
		{
            return get_class($var);
        }
        if (is_null($var))
		{
            return 'null';
        }
        if (is_string($var)) 
		{
            return 'string';
        }
        if (is_array($var)) 
		{
            return 'array';
        }
        if (is_int($var)) 
		{
            return 'integer';
        }
        if (is_bool($var)) 
		{
            return 'boolean';
        }
        if (is_float($var)) 
		{
            return 'float';
        }
        if (is_resource($var)) 
		{
            return 'resource';
        }
        return 'unknown';
    }
	
	/*
	Check the data type of a variable.
	Types are null, string, array, integer, boolean, float, resource, or the classname for objects.
	
	@param [mixed] $var The variable
	@param [string] $type The data type to compare against
	@return [boolean] True if the $var is a $type
	*/
	public static function is($var, $type)
	{
		return (self::type($var) === $type);
	}
};

?>