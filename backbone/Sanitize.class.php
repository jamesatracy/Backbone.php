<?php
/*
Sanitize.class.php
Copyright (C) 2012 James Tracy

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/

/*
Class for sanitizing strings, including striping html and escpaing
for database input.
*/

Backbone::uses("ConnectionManager");

class Sanitize
{
	/*
	General purpose string sanitation function.
	
	@param [string] $string The string to sanitize
	@param [string] $options Options array.
		array(
			"html" => true // strip html
			"upper" => true // strig to upper case
		)
	@return [string] The sanitized string
	*/
	public static function clean($string, $options = array())
	{
		$options = array_merge(array(
			"html" => false,
			"upper" => false
		), $options);
		
		// First, replace UTF-8 characters.
		$string = str_replace(
			array("\xe2\x80\x98", "\xe2\x80\x99", "\xe2\x80\x9c", "\xe2\x80\x9d", "\xe2\x80\x93", "\xe2\x80\x94", "\xe2\x80\xa6"),
			array("'", "'", '"', '"', '-', '--', '...'),
			$string
		);
		// Next, replace their Windows-1252 equivalents.
		$string = str_replace(
			array(chr(145), chr(146), chr(147), chr(148), chr(150), chr(151), chr(133)),
			array("'", "'", '"', '"', '-', '--', '...'),
			$string
		);
		// Remove carriage returns
		$string = str_replace("\r", "", $string);
		
		if($options['upper'])
			$string = strtoupper($string);
		if($options['html'])
			return trim(Sanitize::html($string));
		return trim($string);
	}
	
	/*
	Removes html or replaces it with html entities.
	
	@param [string] $string The string to sanitize
	@param [string] $options Options array.
		array(
			"remove" => true By defaut it removes html tags rather than replacing with htmlentities
			"allowed" => string String of allowed tags, ex: "<p><a>"
		)
	@return [string] The sanitized string
	*/
	public static function html($string, $options = array())
	{
		$options = array_merge(array(
			"remove" => true,
			"allowed" => ""
		), $options);
		
		if($options['remove'])
			return strip_tags($string, $options['allowed']);
		return htmlentities($string);
	}
		
	/*
	Removes html and html attributes. Useful for stripping javascript handlers like onclick
	as well as <a href="javascript:func()"> references. More paranoid than Sanitize::html().
	
	@param [string] $string The string to sanitize
	@param [array] $allowed_tags array of tags to allow
	@param [array] $disabled_attributes Array of attributes to remove
	@return [string] The sanitized string
	*/
	public static function htmlAttrs($string, $allowed_tags = array(), $disabled_attributes = array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavaible', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragdrop', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterupdate', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmoveout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload'))
	{
		if (empty($disabled_attributes)) return strip_tags($string, implode('', $allowed_tags));

		return preg_replace('/<(.*?)>/ie', "'<' . preg_replace(array('/javascript:[^\"\']*/i', '/(" . implode('|', $disabled_attributes) . ")[ \\t\\n]*=[ \\t\\n]*[\"\'][^\"\']*[\"\']/i', '/\s+/'), array('', '', ' '), stripslashes('\\1')) . '>'", strip_tags($string, implode('', $allowed_tags)));
	}
	
	/*
	Removes all non-alphanumeric characters (except whitespace)
	
	@param [string] $string The string to sanitize
	@return [string] The sanitized string
	*/
	public static function alphanumeric($string)
	{
		return preg_replace("/[^a-zA-Z0-9\s]/", "", $string);
	}
	
	/*
	Espcape a string for database insertion.
	
	@param [string] $string The string to escape
	@param [string] $connection The name of the connection.
	@return [string] The sanitized string
	*/
	public static function escape($string, $connection = "default")
	{
		if(!$connection || empty($connection))
			$connection = "default";
		$dbo = ConnectionManager::get($connection);
		if($dbo)
			return $dbo->escape($string);
		return $string; // not a valid connection
	}
};

?>