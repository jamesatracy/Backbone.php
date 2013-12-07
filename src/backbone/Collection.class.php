<?php
/**
 * Backbone.php
 * 
 * @author	James Tracy <james.a.tracy@gmail.com>
 * @copyright	2012-2013
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link https://github.com/jamesatracy/Backbone.php GitHub Page
 */

Backbone::uses("Model");

/**
 * Collection class for representing an ordered list of model objects.
 *
 * @since 0.1.0
 */
class Collection implements Iterator
{	
	/** @var string The model contained by the collection */
	protected $_model = "Model";
	
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
	 * 
	 * @param string $model The name of the model class
	 * @param array $data The raw attribute data
	 */
	public function __construct($model, $data)
	{
		$this->_model = $model;
		$this->_models = $data;
		$this->length = count($data);
	}
	
	/**
	 * Get a model by index.
	 * 
	 * @since 0.3.0
	 * @param string $id The model's ID
	 * @return Model The model, or null if not found
	 * @throws RuntimeException
	 */
	public function getAt($index)
	{
		if(isset($this->_models[$index])) {
			$model = new $this->_model($this->_models[$index]);
			$model->clearChanged();
			return $model;
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
			$model = new $this->_model($this->_models[$this->_position]);
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
	 * @return array Returns an array of raw model data
	 */
	public function toJSON()
	{
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