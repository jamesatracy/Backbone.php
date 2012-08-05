<?php
/*
View.class.php
Copyright (C) 2012 James Tracy

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/

/*
Helper class for formattign html tags
*/
class Html
{
	/*
	Constructs an html link, or anchor tag
	
	@param [string] $url The url. If it begins with a '/' then it is considered relative to the root, else an absolute path.
	@param [string] $text The inner text of the link
	@param [array] $attributes A list of key => value pairs
	@return [string] The html
	*/
	public function link($url, $text, $attributes = array())
	{
		$request = Backbone::$request;

		if(substr($url, 0, 1) == "/")
			// relative path
			$url = $request->link($url);
		
		$attributes["href"] = $url;
		return $this->tag("a", $text, $attributes);
	}
	
	/*
	Constructs an image tag
	
	@param [string] $url The source url. If it begins with a '/' then it is considered relative to the root, else an absolute path.
	@param [array] $attributes A list of key => value pairs
	@return [string] The html
	*/
	public function image($url, $attributes = array())
	{
		$request = Backbone::$request;

		if(substr($url, 0, 1) == "/")
			// relative path
			$url = $request->link($url);
		
		$attributes["src"] = $url;
		return $this->tag("img", null, $attributes);
	}
	
	/*
	Constructs a script tag
	
	@param [string] $url The external source of the script. 
		If it begins with a '/' then it is considered relative to the root, else an absolute path.
	@param [array] $attributes A list of key => value pairs
	@param [boolean] $inline If true, the script is inserted inline. $url must be relative to the root.
	@return [string] The html
	*/
	public function script($url, $attributes = array(), $inline = false)
	{
		$request = Backbone::$request;
		
		$attributes["type"] = "text/javascript";
		
		if($inline)
		{
			if(substr($url, 0, 1) == "/")
			{
				$url = substr($url, 1);
				if(file_exists(ABSPATH.$url))
				{
					return $this->tag("script", "\n".file_get_contents(ABSPATH.$url)."\n", $attributes)."\n";
				}
			}
			return "";
		}
		else
		{
			if(substr($url, 0, 1) == "/")
				// relative path
				$url = $request->link($url);
			
			$attributes["src"] = $url;
			return $this->tag("script", "", $attributes)."\n";
		}
	}
	
	/*
	Constructs a link tag to a stylesheet
	
	@param [string] $url The external source of the stylesheet. 
		If it begins with a '/' then it is considered relative to the root, else an absolute path.
	@param [array] $attributes A list of key => value pairs
	@return [string] The html
	*/
	public function stylesheet($url, $attributes = array())
	{
		$request = Backbone::$request;

		if(substr($url, 0, 1) == "/")
			// relative path
			$url = $request->link($url);
		
		$attributes["href"] = $url;
		$attributes["type"] = "text/css";
		$attributes["rel"] = "stylesheet";
		return $this->tag("link", null, $attributes)."\n";
	}
	/*
	Generic function used for constructing any tag open/close combination
	
	@param [string] $name The name of the tag (ex: "img")
	@param [string] $inner Optional. The inner contents of the tag. If this is null, then the tag self closes (ex: <br/>)
	@param [array] $attributes Optional. A list of attributes defined as key => value pairs.
	@return [string] The tag's html
	*/
	public function tag($name, $inner = null, $attributes = array())
	{
		$ob = '<'.$name.$this->_buildAttributes($attributes);
		if($inner === null)
			$ob .= ' />';
		else
			$ob .= '>'.$inner.'</'.$name.'>';
		return $ob;
	}
	
	/*
	Internal functinon for building a string of attributes from an associated array
	
	@param [array] $attributes Associated array of key => value pairs
	@return [string] The string of attributes
	*/
	protected function _buildAttributes($attributes)
	{
		$ob = '';
		$attrs = array();
		foreach($attributes as $attr => $val)
			$attrs[] = $attr.'="'.$val.'"';
		if(count($attrs) > 0)
			$ob .= ' '.join($attrs, " ");
		return $ob;
	}
};

?>