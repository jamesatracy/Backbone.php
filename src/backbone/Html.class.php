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
 * Helper class for formattign html tags.
 *
 * @since 0.1.0
*/
class Html
{
	/**
	 * Constructs an html link, or anchor tag
	 * 
	 * @since 0.1.0
	 * @param string $url The url. If it begins with a '/' then it is considered relative to the root, else an absolute path.
	 * @param string $text The inner text of the link
	 * @param array $attributes A list of key => value pairs
	 * @return string The html
	 */
	public function link($url, $text, $attributes = array())
	{
		if(substr($url, 0, 1) == "/") {
			// relative path
			$url = Request::link($url);
		}
		
		$attributes["href"] = $url;
		return $this->tag("a", $text, $attributes);
	}
	
	/**
	 * Constructs an image tag
	 * 
	 * @since 0.1.0
	 * @param string $url The source url. If it begins with a '/' then it is considered relative to the root, else an absolute path.
	 * @param array $attributes A list of key => value pairs
	 * @return string The html
	 */
	public function image($url, $attributes = array())
	{
		if(substr($url, 0, 1) == "/") {
			// relative path
			$url = Request::link($url);
		}
		
		$attributes["src"] = $url;
		return $this->tag("img", null, $attributes);
	}
	
	/**
	 * Constructs a script tag
	 * 
	 * @since 0.1.0
	 * @param string $url The external source of the script. 
	 * 	If it begins with a '/' then it is considered relative to the root, else an absolute path.
	 * @param array $attributes A list of key => value pairs
	 * @param bool $inline If true, the script is inserted inline. $url must be relative to the root.
	 * @return string The html
	 */
	public function script($url, $attributes = array(), $inline = false)
	{
		$attributes["type"] = "text/javascript";
		
		if($inline) {
			if(substr($url, 0, 1) == "/") {
				$url = substr($url, 1);
				if(file_exists(ABSPATH.$url)) {
					return $this->tag("script", "\n".file_get_contents(ABSPATH.$url)."\n", $attributes)."\n";
				}
			}
			return "";
		} else {
			if(substr($url, 0, 1) == "/") {
				// relative path
				$url = Request::link($url);
			}
			
			$attributes["src"] = $url;
			return $this->tag("script", "", $attributes)."\n";
		}
	}
	
	/**
	 * Constructs a link tag to a stylesheet
	 * 
	 * @since 0.1.0
	 * @param string $url The external source of the stylesheet. 
	 * 	If it begins with a '/' then it is considered relative to the root, else an absolute path.
	 * @param array $attributes A list of key => value pairs
	 * @return string The html
	 */
	public function stylesheet($url, $attributes = array())
	{
		if(substr($url, 0, 1) == "/") {
			// relative path
			$url = Request::link($url);
		}
		
		$attributes["href"] = $url;
		$attributes["type"] = "text/css";
		$attributes["rel"] = "stylesheet";
		return $this->tag("link", null, $attributes)."\n";
	}
	
	/**
	 * Generic function used for constructing any tag open/close combination
	 * 
	 * @since 0.1.0
	 * @param string $name The name of the tag (ex: "img")
	 * @param string $inner Optional. 
	 * 	The inner contents of the tag. If this is null, then the tag self closes (ex: <br/>)
	 * @param array $attributes Optional. A list of attributes defined as key => value pairs.
	 * @return string The tag's html
	 */
	public function tag($name, $inner = null, $attributes = array())
	{
		$ob = '<'.$name.$this->_buildAttributes($attributes);
		if($inner === null) {
			$ob .= ' />';
		} else {
			$ob .= '>'.$inner.'</'.$name.'>';
		}
		return $ob;
	}
	
	/**
	 * Generic function used for constructing an open tag.
	 * 
	 * @since 0.3.0
	 * @param string $name The name of the tag (ex: "img")
	 * @param array $attributes Optional. A list of attributes defined as key => value pairs.
	 * @return string The open tag
	 */
	public function tagOpen($name, $attributes)
	{
		return '<'.$name.$this->_buildAttributes($attributes).'>';
	}
	
	/**
	 * Generic function used for constructing a close tag.
	 * 
	 * @since 0.3.0
	 * @param string $name The name of the tag (ex: "img")
	 * @return string The open tag
	 */
	public function tagClose($name)
	{
		return '</'.$name.'>';
	}
	
	/**
	 * Internal functinon for building a string of attributes from an associated array
	 *
	 * @since 0.1.0
	 * @internal
	 * @param array $attributes Associated array of key => value pairs
	 * @return string The string of attributes
	 */
	protected function _buildAttributes($attributes)
	{
		$ob = '';
		$attrs = array();
		foreach($attributes as $attr => $val) {
			$attrs[] = $attr.'="'.$val.'"';
		}
		if(count($attrs) > 0) {
			$ob .= ' '.join($attrs, " ");
		}
		return $ob;
	}
};
?>