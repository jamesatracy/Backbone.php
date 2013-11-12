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
 * Abstract base class for data sources, like a database.
 *
 * A particular implementation of a data source, like a MySQL database, should extend this class.
 *
 * @since 0.1.0
 */
abstract class DataSource 
{	
	/** @var array The options used to connect to this data source. */
	protected $_options = array();
	
	/** @var bool Is the data source connected */
	protected $_is_connected = false;
	
	/**
	 * Connect to a data source
	 *
	 * @since 0.1.0
	 * @param array $options An array of data source specific options.
	 */
	public function connect($options)
	{
		$this->_options = $options;
		$this->_is_connected = true;
	}
	
	/**
	 * Disconnect from a data source
	 *
	 * @since 0.1.0
	 */
	public function disconnect()
	{
		$this->_is_connected = false;
	}
	
	/**
	 * Is the data source connected?
	 *
	 * @since 0.1.0
	 * @return bool True if connected, false otherwise.
	 */
	public function isConnected()
	{
		return $this->_is_connected;
	}
	
	/**
	 * Return a data source's schema. 
	 *
	 * For the data structure used for the schema, see the documentation
	 * for the Schema class.
	 *
	 * @since 0.2.0
	 * @param string $source Usually the table name
	 * @return array The schema structure
	 * @throws RuntimeException
	 */
	public function schema($source)
	{
		// stub function to be implemented
		return false;
	}
	
	/**
	 * CRUD: Create a new record in the data source.
	 *
	 * @since 0.2.0
	 * @param string $source Usually the table name
	 * @param array $data The fields and values to create
	 * @return bool True if the creation succeeded.
	 */
	public function create($source, $data)
	{
		// stub function to be implemented
		return false;
	}
	
	/**
	 * CRUD: Read data from the data source.
	 *
	 * @since 0.2.0
	 * @param string $source Usually the table name
	 * @param array $options Query options.
	 * @return array The data that was read.
	 */
	public function read($source, $options)
	{
		// stub function to be implemented
		return false;
	}
	
	/**
	 * CRUD: Update a record in the data source.
	 *
	 * @since 0.2.0
	 * @param string $source Usually the table name
	 * @param array $data The fields and values to create
	 * @param array $options Query options.
	 * @return bool True if the update succeeded.
	 */
	public function update($source, $data, $options)
	{
		// stub function to be implemented
		return false;
	}
	
	/**
	 * CRUD: Delete data from the data source.
	 *
	 * @since 0.2.0
	 * @param string $source Usually the table name
	 * @param array $options Query options.
	 * @return bool True if the delete succeeded.
	 */
	public function delete($source, $options)
	{
		// stub function to be implemented
		return false;
	}
	
	/**
	 * Get the primary key of the last record inserted
	 *
	 * @since 0.2.0
	 * @return int The primary key ID
	 */
	public function lastInsertID()
	{
		// stub function to be implemented
		return false;
	}
	
	/**
	 * Escapes a string by adding slashes to special chars
	 *
	 * @since 0.1.0
	 * @param string $string The string to escape
	 */
	public function escape($string)
	{
		if($string == null || empty($string) || !is_string($string)) {
			return $string;
		}
		return addslashes($string);
	}
};
?>