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
use \Backbone;

Backbone::uses("Model");

/**
 * Base Collection class for representing an ordered list of model objects.
 *
 * @since 0.1.0
 */
class Collection implements \Iterator
{
	/** @var MySQL The database connection */
	protected $_db = null;
	
	/** @var string The table associated with this collection */
	protected $_table = "";
	
	/** @var string The model contained by the collection */
	protected $_model = "Model";
	
	/** @var string The class name of the model class contained by the collection */
	protected $_classname = null;
	
	/** @var array The array of raw model data */
	protected $_models = array();
	
	/** @var int The length of the data set returned from a call to fetch() */
	public $length = 0;
	
	/** @var int For iterator implementation */
	protected $_position = 0;
	
	/** @var array An array of error messages */
	protected $_errors = array();
	
	/**
	 * Constructor 
	 */
	public function __construct($table, $options = array())
	{
		if(!$table || empty($table)) {
			return; // bad
		}
		$options = array_merge(array(
			"model" => "Model",
			"classname" => null,
			"db" => "default"
		), $options);
		$this->_table = $table;
		$this->_model = $options['model'];
		$this->_classname = $options['classname'];
		$this->_db = (is_string($options['db']) ? Connections::get($options['db']) : $options['db']);
	}
	
	/**
	 * Get the number of items in a collection without performing a fetch().
	 * 
	 * @since 0.1.0
	 * @param array $options Fetch options.
	 * 	"where" => array()
	 * 	"order_by" => array("field", ["ASC"/"DESC"])
	 * 	"limit" => integer
	 * @return int The number of items.
 	 */
	public function count($options = array())
	{
		if(!$this->_db) {
			throw new RuntimeException("Collection: Invalid Database Connection");
		}
		return $this->_db->count($this->_table, $options);
	}
	
	/**
	 * Fetch a collection from the database
	 * 
	 * @since 0.1.0
	 * @param array $options Fetch options.
	 * 	"where" => array()
	 * 	"order_by" => array("field", ["ASC"/"DESC"])
	 * 	"limit" => integer
	 * 	"offset" => integer
	 * @return bool True if successful.	
	 * @throws RuntimeException
	 */
	public function fetch($options = array())
	{
		$this->reset();
		if(!$this->_db) {
			throw new RuntimeException("Collection: Invalid Database Connection");
		}
		
		$this->_models = $this->_db->read(
			$this->_table,
			$options
		);
		if($this->_db->hasError()) {
			$this->errors[] = $this->_db->getError();
			return false;
		}
		
		$this->length = count($this->_models);
		$this->rewind();
		return true;
	}
	
	/**
	 * Get a model by ID (primary key).
	 * 
	 * Uses the results returned by a call to fetch().
	 * 
	 * @since 0.1.0
	 * @param string $id The model's ID
	 * @return Model The model, or null if not found
	 * @throws RuntimeException
	 */
	public function get($id)
	{
		if(!$this->_db) {
			throw new RuntimeException("Collection: Invalid Database Connection");
		}

		$model = $this->createModel();
		$key = $model->getID();
		
		// loop over models
		foreach($this->_models as $data) {
			if(isset($data[$key]) && $data[$key] == $id) {
				$model->set($data);
				return $model;
			}
		}
		return null;
	}

	/**
	 * Return the current model element. 
	 * Iterator method.
	 * 
	 * @since 0.1.0
	 * @return Model The current model
	 * @throws RuntimeException
	 */
   	public function current() 
	{
		if(isset($this->_models[$this->_position])) {
			$model = $this->createModel();
			$model->set($this->_models[$this->_position]);
			$model->clearChanged();
			return $model;
		}
		return null;
	}
    
	/**
	 * Return the key of the current element. 
	 * Iterator method.
	 * 
	 * This will be the index position into the array of models.
	 * @since 0.1.0
	 */
	public function key()
	{
		return $this->_position;
	}
	
	/**
	 * Return the current element and move forward to next element.
	 * Iterator method.
	 * 
	 * @since 0.1.0
	 * @return Model The current element before the position is incremented
	 */
	public function next() 
	{
		$cur = $this->current();
		++$this->_position;
		return $cur;
	}
	
	/**
	 * Rewind the Iterator to the first element.
	 * Iterator method.
	 * 
	 * @since 0.1.0
	 */
	public function rewind() 
	{
		$this->_position = 0;
	}
	
	/**
	 * Checks if current position is valid.
	 * Iterator method.
	 * 
	 * @since 0.1.0
	 * @return bool True if the positino is valid, false otherwise
	 */
	public function valid()
	{
	    return isset($this->_models[$this->_position]);
	}
	
	/**
	 * Returns the current element's raw attributes 
	 * 
	 * @since 0.1.0
	 * @return array The current model's attributes
	 */
	public function currentAttributes()
	{
		if(isset($this->_models[$this->_position])) {
			return $this->_models[$this->_position];
		}
		return null;
	}
	
	/**
	 * Pluck an attribute from each model in the collection.
	 * 
	 * @since 0.1.0
	 * @param string $name The attribute name
	 * @return array An array of attributes
	 */
	public function pluck($name)
	{
		$attrs = array();
		foreach($this->_models as $model) {
			if(isset($model[$name])) {
				$attrs[] = $model[$name];
			}
		}
		return $attrs;
	}
			
	/**
	 * Return the raw model data.
	 * 
	 * @since 0.1.0
	 * @param bool $compact Whether or not to include the model keys in the representation.
	 * @return array Returns an array of raw model data
	 */
	public function toJSON($compact = false)
	{
		if($compact) {
			$collection = array();
			foreach($this->_models as $attributes) {
				$collection[] = array_values($attributes);
			}
			return $collection;
		}
		return $this->_models;
	}
	
	/**
	 * Get the associated model name
	 * 
	 * @since 0.1.0
	 * @return string The associated model's name.
	 */
	public function getModelName()
	{
		return $this->_model;
	}
	
	/**
	 * Get the associated model table
	 * 
	 * @since 0.1.0
	 * @return string The associated model's table name.
	 */
	public function getTableName()
	{
		return $this->_table;
	}
			
	/**
	 * Reset the collection to an empty state
	 * 
	 * @since 0.1.0
	 */
	public function reset()
	{
		$this->_errors = array();
		$this->_models = array();
		$this->length = 0;
	}
	
	/**
	 * Create a new instance of the Collection's model class
	 *
	 * @since 0.2.1
	 * @return mixed The model object
	 * @throws RuntimeException
	 */
	public function createModel()
	{
		Backbone::uses($this->_model);
		if($this->_classname) {
		    $classname = $this->_classname;
		} else {
		    $classname = Backbone::getClassName($this->_model);
		}
		if(!class_exists($classname)) {
			throw new RuntimeException("Collection: Could not find model of type ".$this->_model);
		}
		$model = new $classname($this->_db);
		return $model;
	}
	
	/**
	 * Retrieve the last errors
	 * 
	 * @since 0.1.0
	 * @return array An array of error strings
	 */
	public function getErrors()
	{
		return $this->_errors;
	}
};

?>
