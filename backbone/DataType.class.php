<?php
/**
 * Backbone.php
 * 
 * @author	James Tracy <james.a.tracy@gmail.com>
 * @copyright	2012-2013
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 */

/**
 * Helper class for working with data types.
 *
 * @since 0.1.0
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