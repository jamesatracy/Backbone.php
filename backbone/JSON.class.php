<?php
/*
JSON.class.php
Copyright (C) 2012 James Tracy

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/

/*
@fileoverview
Class for working with JSON data types.

@since 0.1.0
*/

class JSON
{
	/*
	Encode an array as a JSON formatted string.
	Internally calls json_encode
	
	@param [mixed] $obj The php object/array to encode
	@return [string] A JSON formatted string
	*/
	public static function encode($obj)
	{
		return json_encode($obj);
	}
	
	/* Wrapper for JSON::encode() */
	public static function stringify($obj)
	{
		return JSON::encode($obj);
	}
	
	/*
	Decode a JSON formatted string into a php array/object
	Internally calls json_decode
	
	@param [string] $json A JSON formatted string
	@param [boolean] $assoc Convert objects into associated arrays. Defaults to true.
	@return [mixed] The php object/array
	*/
	public static function decode($json, $assoc = true)
	{
		return json_decode($json, $assoc);
	}
	
	/* Wrapper for JSON::decode() */
	public static function parse($obj)
	{
		return JSON::decode($obj);
	}
	
	/*
	Indents a flat JSON string to make it more human readable.
	
	@param [string] $json The original JSON string to process.
	@return [string] Indented version of the original JSON string.
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

		for($i = 0; $i <= $strLen; $i++) 
		{

			// Grab the next character in the string.
			$char = substr($json, $i, 1);

			// Are we inside a quoted string?
			if($char == '"' && $prevChar != '\\') 
			{
				$outOfQuotes = !$outOfQuotes;
			
			// If this character is the end of an element, 
			// output a new line and indent the next line.
			} 
			else if(($char == '}' || $char == ']') && $outOfQuotes) 
			{
				$result .= $newLine;
				$pos --;
				for ($j=0; $j<$pos; $j++) 
				{
					$result .= $indentStr;
				}
			}
			
			// Add the character to the result string.
			$result .= $char;

			// If the last character was the beginning of an element, 
			// output a new line and indent the next line.
			if(($char == ',' || $char == '{' || $char == '[') && $outOfQuotes) 
			{
				$result .= $newLine;
				if($char == '{' || $char == '[') 
				{
					$pos ++;
				}
				
				for($j = 0; $j < $pos; $j++) 
				{
					$result .= $indentStr;
				}
			}
			
			$prevChar = $char;
		}

		return $result;
	}
};

?>