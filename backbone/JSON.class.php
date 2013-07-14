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
 * Class for working with JSON data types.
 * 
 * @since 0.1.0
 */ 
class JSON
{
	/**
	 * Encode an array as a JSON formatted string.
	 * 
	 * Internally calls json_encode
	 * 
	 * @since 0.1.0
	 * @param array $obj The php array to encode
	 * @return string A JSON formatted string
	 */
	public static function encode($obj)
	{
		return json_encode($obj);
	}
	
	/**
	 * Wrapper for JSON::encode() 
	 * 
	 * @since 0.1.0
	 * @param array $obj The php array to encode
	 * @return string A JSON formatted string
	 */
	public static function stringify($obj)
	{
		return JSON::encode($obj);
	}
	
	/**
	 * Decode a JSON formatted string into a php array/object
	 * 
	 * Internally calls json_decode
	 * 
	 * @since 0.1.0
	 * @param string $json A JSON formatted string
	 * @param bool $assoc Convert objects into associated arrays. Defaults to true.
	 * @return array The php equivalent array
	 */
	public static function decode($json, $assoc = true)
	{
		return json_decode($json, $assoc);
	}
	
	/**
	 * Wrapper for JSON::decode() 
	 * @since 0.1.0
	 * @param string $json A JSON formatted string
	 * @param bool $assoc Convert objects into associated arrays. Defaults to true.
	 * @return array The php equivalent array
	 */
	public static function parse($obj)
	{
		return JSON::decode($obj);
	}
	
	/**
	 * Indents a flat JSON string to make it more human readable.
	 * 
	 * @since 0.1.0
	 * @param [string] $json The original JSON string to process.
	 * @return [string] Indented version of the original JSON string.
	 */
	public static function indent($json) 
	{
		$result      = '';
		$pos         = 0;
		$strLen      = strlen($json);
		$indentStr   = '  ';
		$newLine     = "\n";
		$prevChar    = '';
		$outOfQuotes = true;

		for($i = 0; $i <= $strLen; $i++) {

			// Grab the next character in the string.
			$char = substr($json, $i, 1);

			// Are we inside a quoted string?
			if($char == '"' && $prevChar != '\\') {
				$outOfQuotes = !$outOfQuotes;
			
			// If this character is the end of an element, 
			// output a new line and indent the next line.
			} else if(($char == '}' || $char == ']') && $outOfQuotes) {
				$result .= $newLine;
				$pos --;
				for ($j=0; $j<$pos; $j++) {
					$result .= $indentStr;
				}
			}
			
			// Add the character to the result string.
			$result .= $char;

			// If the last character was the beginning of an element, 
			// output a new line and indent the next line.
			if(($char == ',' || $char == '{' || $char == '[') && $outOfQuotes) {
				$result .= $newLine;
				if($char == '{' || $char == '[') {
					$pos ++;
				}
				
				for($j = 0; $j < $pos; $j++) {
					$result .= $indentStr;
				}
			}
			
			$prevChar = $char;
		}

		return $result;
	}
};

?>
