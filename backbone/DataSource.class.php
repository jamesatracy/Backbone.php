<?php
/*
DataSource.class.php
Copyright (C) 2012 James Tracy

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/

/*
@fileoverview
Abstract base class for data sources, like a database.
A particular implementation of a data source, like a MySQL database, should extend this class.
*/
abstract class DataSource 
{	
	/* Data source options used to connect */
	protected $_options = null;
	
	/* Is the data source connected */
	protected $_is_connected = false;
	
	/*
	Connect to a data source
	
	@param [array] $options An array of data source specific options.
	*/
	public function connect($options)
	{
		$this->_options = $options;
		$this->_is_connected = true;
	}
	
	/*
	Disconnect from a data source
	*/
	public function disconnect()
	{
		$this->_is_connected = false;
	}
	
	/*
	Is the data source connected?
	
	@return [boolean] True if connected, false otherwise.
	*/
	public function isConnected()
	{
		return $this->_is_connected;
	}
	
	/*
	Escapes a string by adding slashes to special chars
	
	@param [string] $string The string to escape
	*/
	public function escape($string)
	{
		if($string == null || emptry($string) || !is_string($string))
			return $string;
		return addslashes($string);
	}
};
?>