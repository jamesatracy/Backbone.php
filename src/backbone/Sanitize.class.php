<?php
/**
 * Backbone.php
 * 
 * @author	James Tracy <james.a.tracy@gmail.com>
 * @copyright	2012-2013
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link https://github.com/jamesatracy/Backbone.php GitHub Page
 */

namespace Backbone;
use \Backbone as Backbone;

/**
 * Class for sanitizing strings, including striping html and escpaing for database input.
 *
 * @since 0.1.0
*/
class Sanitize
{
	/**
	 * General purpose string sanitation function.
	 *
	 * @since 0.1.0
	 * @param string $string The string to sanitize
	 * @param string $options Options array.
	 *	array(
	 * 		"html" => true // strip html
	 * 		"upper" => true // string to upper case
	 * 		"lower" => true // string to lower case
	 * 	)
	 * @return string The sanitized string
	 */
	public static function clean($string, $options = array())
	{
		$options = array_merge(array(
			"html" => false,
			"upper" => false,
			"lower" => false
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
		if($options['lower'])
			$string = strtolower($string);
		if($options['html'])
			return trim(Sanitize::html($string));
		return trim($string);
	}
	
	/** 
	 * Removes html or replaces it with html entities.
	 *
	 * @since 0.1.0
	 * @param string $string The string to sanitize
	 * @param string $options Options array.
	 * 	array(
	 *  	"remove" => true By defaut it removes html tags rather than replacing with htmlentities
	 * 		"allowed" => string String of allowed tags, ex: "<p><a>"
	 * 	)
	 * @return string The sanitized string
	*/
	public static function html($string, $options = array())
	{
		$options = array_merge(array(
			"remove" => true,
			"allowed" => ""
		), $options);
		
		if($options['remove']) {
			return strip_tags($string, $options['allowed']);
		}
		return htmlentities($string);
	}
		
	/**
	 * Removes html and html attributes. 
	 *
	 * Useful for stripping javascript handlers like onclick
	 * as well as <a href="javascript:func()"> references. More paranoid than Sanitize::html().
	 *
	 * @since 0.1.0
	 * @param string $string The string to sanitize
	 * @param array $allowed_tags array of tags to allow
	 * @param array $disabled_attributes Array of attributes to remove
	 * @return string The sanitized string
	*/
	public static function htmlAttrs($string, $allowed_tags = array(), $disabled_attributes = array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavaible', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragdrop', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterupdate', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmoveout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload'))
	{
		if (empty($disabled_attributes)) {
			return strip_tags($string, implode('', $allowed_tags));
		}

		return preg_replace('/<(.*?)>/ie', "'<' . preg_replace(array('/javascript:[^\"\']*/i', '/(" . implode('|', $disabled_attributes) . ")[ \\t\\n]*=[ \\t\\n]*[\"\'][^\"\']*[\"\']/i', '/\s+/'), array('', '', ' '), stripslashes('\\1')) . '>'", strip_tags($string, implode('', $allowed_tags)));
	}
	
	/**
	 * Removes all non-alphanumeric characters (except whitespace)
	 *
	 * @since 0.1.0
	 * @param string $string The string to sanitize
	 * @return string The sanitized string
	 */
	public static function alphanumeric($string)
	{
		return preg_replace("/[^a-zA-Z0-9\s]/", "", $string);
	}
	
	/**
	 * Espcape a string for database insertion.
	 *
	 * @since 0.1.0
	 * @param string $string The string to escape
	 * @param string $connection The optional name of the connection.
	 * @return string The sanitized string
	 */
	public static function escape($string, $connection = null)
	{
		if($connection) {
			Backbone::uses("Connections");
			$db = Connections::get($connection);
			if($db) {
				return $db->escape($string);
			}
		}
		return addslashes($string);
	}
};

?>