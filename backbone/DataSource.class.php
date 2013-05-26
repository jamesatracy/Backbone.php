<?php
/**
 * Backbone.php
 * 
 * @author	James Tracy <james.a.tracy@gmail.com>
 * @copyright	2012-2013
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 */
 
/**
 * Abstract base class for data sources, like a database.
 *
 * A particular implementation of a data source, like a MySQL database, should extend this class.
 *
 * @since 0.1.0
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