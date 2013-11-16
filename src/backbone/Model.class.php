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

Backbone::uses("Query","Schema");

/**
 * Base Model class for database objects. 
 *
 * Provides basic methods for creating, updating, fetching, and 
 * validating model data.
 *
 * @since 0.1.0
*/
class Model extends Schema
{
	/** @var string The database table name associated with this model */
	public static $table = "";
	
	/** @var string The field name of the created timestamp, if any */
	public static $created = "created";
	
	/** @var string The query class name for this model */
	public static $queryClass = "\Backbone\ModelQuery";
	
	/** @var array Hash map of field sanitations */
	public static $sanitations = array();
	
	/** @var array Hash map of model attributes */
	protected $_attributes = array();
	
	/** @var array Hash map of changed model attributes */
	protected $_changed = array();
	
	/**
	 * Constructs a new model with an optional set of initial values.
	 *
	 * @since 0.3.0
	 * @param array $fields Optional initial values as key - value pairs.
	 */
	public function __construct($fields = null)
	{
	    $this->initialize(static::$table);
	    
		if($fields !== null) {
			$this->set($fields);
		} else {
			$this->set($this->defaultValues());
		}
		if($this->isNew()) {
			if(static::$created && $this->hasField(static::$created)) {
				$this->set(static::$created, date('Y-m-d H:i:s', time()));
			}
		}
	}
	
	/**
	 * Fetch one or more models from the database.
	 *
	 * When a primary key value is given, the model with that key is
	 * returned or false if it does not exist.
	 *
	 * When no primary key is given, the method returns a query builder
	 * object for constructing more complex select queries. The query builder
	 * always returns the results as a collection.
	 * 
	 * @since 0.3.0
	 * @param int $id The primary key (optional)
	 * @return Model|ModelQuery Returns the Model if a key is given.
	 * @throws \InvalidArgumentException|\RuntimeException
	 */
	public static function fetch($id = null)
	{
		if(!DB::isConnected()) {
			throw new \RuntimeException("Model: No valid DB connection");
		}
		if($id !== null) {
			if(!is_numeric($id) && $id < 1) {
				throw new \InvalidArgumentException("Model: Invalid argument for fetch");
			}
			$schema = self::loadSchema(static::$table);
			$result = DB::table(static::$table)
				->select()
				->where($schema['id'], $id)
				->first();
			if(!$result || empty($result)) {
				return null;
			}
			$classname = get_called_class();
			return new $classname($result);
		} else {
			$query = new static::$queryClass(get_called_class(), static::$table);
			return $query->select();
		}
	}
	
	/**
	 * Creates a new model and saves it to the database.
	 *
	 * This is functionally equivalent to performing:
	 *	$model = new Model($data);
	 *	$model->save();
	 *
	 * @since 0.3.0
	 * @param array $data The attributes to set on the new model.
	 * @return Model|bool The new model object or false if it failed.
	 * @throws \RuntimeException
	 */
	public static function create($data)
	{
		if(!DB::isConnected()) {
			throw new \RuntimeException("Model: No valid DB connection");
		}
		
		$classname = get_called_class();
		$model = new $classname($data);
		if(!$model->save()) {
			return false;
		}
		return $model;
	}
	
	/**
	 * Get the value of an attribute
	 *
	 * @since 0.1.0
	 * @param string $attr The name of the attribute
	 * @return mixed The value of the attribute, or null
	 */
	public function get($attr)
	{
		if($this->has($attr)) {
			return $this->_attributes[$attr];
		}
		return null;
	}
	
	/**
	 * Set the value of an attribute or an array of attributes
	 *
	 * @since 0.1.0
	 * @param string|array $attr The attribute name or an array of $attr => $value pairs
	 * @param [mixed] $value The attribute value or null if using a hash map
	 */
	public function set($attr, $value = null)
	{
		$attributes = array();
		if(!$attr || empty($attr)) {
			return;
		}
		if(is_null($value)) {
			$value = "NULL";
		}
		if(is_array($attr)) {
			// hash map
			$attributes = $attr;
		} else {
			// single attribute, value
			$attributes[$attr] = $value;
		}

		foreach($attributes as $key => $val) {
			if($this->hasField($key)) {
				// check sanitation rules 
				$val = $this->_applySanitation($key, $val);
				
				// only set this attribute if it exists in the schema
				if(!isset($this->_attributes[$key])) {
					$this->_attributes[$key] = $val;
					$this->_changed[$key] = true;
				} else {
					// only mark as changed if it actually changed
					if($this->_attributes[$key] !== $val) {
						$this->_attributes[$key] = $val;
						$this->_changed[$key] = true;
					}
				}
			}
		}
	}
	
	/**
	 * Save a model back to the server. 
	 *
	 * Only valid changed fields are actually sent to the database to be
	 * updated. All else is ignored. For new models, all values are sent
	 * to the server to be inserted.
	 *
	 * @since 0.1.0
	 * @return bool True on success
	 * @throws \RuntimeException
	 */
	public function save()
	{
		if(!DB::isConnected()) {
			throw new \RuntimeException("Model: Invalid connection");
		}
		$attributes = array();
		foreach($this->_changed as $key => $value) {
			if($value && $this->has($key) && $key != $this->getID()) {
				// update changed values if not primary key
				$attributes[$key] = $this->_attributes[$key];
			}
		}

		if(empty($attributes)) {
			return true;
		}
		if($this->validateModel()) {
			$result = null;
			if($this->isNew()) {
				// insert
				$result = DB::table(static::$table)
				->insert($attributes)
				->exec();
			} else {
				// update
				$result = DB::table(static::$table)
				->update($attributes)
				->where($this->getID(), $this->get($this->getID()))
				->exec();
			}
			if(!$result) {
				$this->_errors[] = DB::errorMessage();
				return false;
			}
			if($this->isNew()) {
				$this->_attributes[$this->getID()] = DB::lastInsertID();
			}
			$this->_changed = array();
			$this->_errors = array();
			// Trigger a changed event on this model. 
			// The ID of the model is available as the hash of the name of the ID, and the changed
			// attributes as "data".
			//
			// Ex: array("car_id => "12", "data" => array("make" => "honda", "model" => "accord"))
			Events::trigger(get_class().":changed", array($this->getID() => $this->get($this->getID()), "data" => $attributes));
			return true;
		}
		return false;
	}
	
	/**
	 * Removes the model from the database. Triggers a deleted event on this model.
	 *
	 * @since 0.1.0
	 * @return bool True if the operation succeeded, false otherwise
	 * @throws \RuntimeException
	 */
	public function delete()
	{
		if(!DB::isConnected()) {
			throw new \RuntimeException("Model: Invalid connection");
		}
		
		// cannot delete if the model is new
		if($this->isNew()) {
			$this->_errors[] = get_class($this).": Cannot delete new model";
			return false;
		}
		
		$result = DB::table(static::$table)
		->delete()
		->where($this->getID(), $this->get($this->getID()))
		->exec();

		if(!$result) {
			$this->_errors[] = DB::errorMessage();
			return false;
		}
		Events::trigger(get_class().":deleted", array($this->getID() => $this->get($this->getID())));
		$this->clear();
		return true;
	}
	
	/**
	 * Validates the current set of attributes
	 *
	 * Use Model->getErrors() to get the error messages.
	 *
	 * @since 0.1.0
	 * @return bool True if the attributes validate, false otherwise
	 */
	public function validateModel()
	{
		return parent::validate($this->_attributes);
	}
	
	/**
	 * Return a JSON representation of the model.
	 *
	 * This is not a string, but an associative array that you can pass to json_encode().
	 *
	 * @since 0.1.0
	 * @return array A JSON representation of the model.
	 */
	public function toJSON()
	{
		$models = array();
// 		foreach($this->_models as $key => $model) {
// 			if($model) {
// 				$models[$key] = $model->toJSON($compact);
// 			}
// 		}
		$collections = array();
// 		foreach($this->_collections as $key => $collection) {
// 			if($collection) {
// 				$collections[$key] = $collection->toJSON($compact);
// 			}
// 		}
		return (array("attributes" => $this->_attributes, "models" => $models, "collections" => $collections));
	}
	
	/**
	 * Returns the raw attributes array.
	 *
	 * @param array $include. Include only the attributes in this list. Optional.
	 * @return array The raw attributes array.
	 */
	public function getAttributes($include = null)
	{
		if(!$include) {
			return $this->_attributes;
		}	
		$attributes = array();
		foreach($include as $i => $key) {
			$attributes[$key] = $this->_attributes[$key];
		}
		return $attributes;
	}
	
	/**
	 * Returns true if the attribute contains a value that is not null or undefined.
	 *
	 * @since 0.1.0
	 * @param string $attr The name of the attribute
	 * @return bool True if the attribute is not null or undefined
	 */
	public function has($attr)
	{
		return (isset($this->_attributes[$attr]));// && $this->_attributes[$attr] != null);
	}
	
	/**
	 * Checks if an attribute has changed since the last time the model was saved
	 *
	 * @since 0.1.0
	 * @param string $attr The name of the attribute
	 * @return bool True if the attribute has changed.
	 */
	public function changed($attr)
	{
		return (isset($this->_changed[$attr]) && $this->_changed[$attr]);
	}
	
	/**
	 * Checks if the model is new (has not been created on the server).
	 *
	 * It does this by checking the value of the primary key. If it is not > 0, then the model is new.
	 *
	 * @since 0.1.0
	 * @return bool True if the model is new
	 */
	public function isNew()
	{
		$id = $this->getID();
		if($this->has($id) && $this->_attributes[$id] > 0) {
			return false;
		}
		return true;
	}
	
	/**
	 * Remove all of the model's attributes. 
	 *
	 * @since 0.1.0
	 */
	public function clear()
	{
		$this->_attributes = array();
		$this->_changed = array();
	}
	
	/**
	 * Clear the changed array 
	 *
	 * @since 0.1.0
	 */
	public function clearChanged()
	{
		$this->_changed = array();
	}
	
	/**
	 * Has the model changed since the last update?
	 *
	 * In other words, are there any unsaved changes?
	 *
	 * @since 0.1.0
	 * @return bool True if the model has changed.
	 */
	public function hasChanged()
	{
		$keys = array_keys($this->_changed);
		return (!empty($keys));
	}
	
	/**
	 * Retrieve a hash of only the model's attributes that have changed.
	 *
	 * @since 0.1.0
	 * @return array An array of changed attributes
	 */
	public function changedAttributes()
	{
		$attributes = array();
		foreach($this->_changed as $key => $value) {
			$attributes[$key] = $this->get($key);
		}
		return $attributes;
	}
	
	/**
	 * Magic __get method that internally calls get() 
	 *
	 * @since 0.1.0
	 */
	public function __get($attr)
	{
		return $this->get($attr);
	}
	
	/**
	 * Magic __set method that internally calls set() 
	 *
	 * @since 0.1.0
	 */
	public function __set($attr, $value)
	{
		return $this->set($attr, $value);
	}
	
	/**
	 * Magic __isset method that internally calls has() 
	 *
	 * @since 0.1.0
	 * @return bool
	 */
	public function __isset($attr)
	{
		return $this->has($attr);
	}
	
	/**
	 * Internal function for applying sanitation rules 
	 *
	 * @since 0.1.0
	 * @return mixed The sanitized value
	 */
	protected function _applySanitation($key, $val)
	{
		if(isset(static::$sanitations[$key])) {
			foreach(static::$sanitations[$key] as $index => $callable) {
				$val = call_user_func_array($callable, array($val));
			}
		}
		return $val;
	}
}

/**
 * Extends the Query class so executing select statements on a Model can
 * return a Collection object.
 *
 * @since 0.3.0
 */
class ModelQuery extends Query
{
	protected $_model = "Model";
	
	/**
	 * Constructor
	 *
	 * @param string $model The associated Model name
	 * @param string $table The associated table name
	 */
	public function __construct($model, $table)
	{
		$this->_model = $model;
		parent::__construct($table);
	}
	
	/**
	 * Allows for magic filter methods based on the Model's fields.
	 * 
	 * Example:
	 *      Say that User has a field "name"
	 * 
	 *      I can filter by name:
	 *      User::fetch()->name("John")->exec();
	 *      
	 *      This is equivalent to:
	 *      User->fetch()->where("name", "John")->exec();
	 * 
	 *      You can also pass in operators:
	 *      User::fetch()->name("LIKE", "Jon%")->exec();
	 *      User->fetch()->where("name", "LIKE", "Jon%")->exec();
	 * 
	 *      You can also have the clause conjoined with an OR:
	 *      User::fetch()->name("John")->or_name("Mark")->exec();
	 *      User::fetch()->where("name", "John")->orWhere("name", "Mark")->exec();
	 * 
	 * @param string $name The name of the method called
	 * @param array $arguments The args passed to the method call
	 * @return ModelQuery The query object
	 */
	public function __call($name, $arguments)
	{
	    $numArgs = count($arguments);
	    
	    // is name a model field?
	    $schema = call_user_func_array(array($this->_model, "loadSchema"), array($this->_table));
	    if($schema && $numArgs > 0) {
	        $fields = $schema['schema'];
	        if(isset($fields[$name])) {
	            if($numArgs == 1) {
	                return $this->where($name, $arguments[0]);
	            } else {
	                return $this->where($name, $arguments[0], $arguments[1]);
	            }
	        } else {
	            if(substr($name, 0, 3) === "or_") {
	                // this might be an or condition
	                $realName = substr($name, 3);
	                if(isset($fields[$realName])) {
        	            if($numArgs == 1) {
        	                return $this->orWhere($realName, $arguments[0]);
        	            } else {
        	                return $this->orWhere($realName, $arguments[0], $arguments[1]);
        	            }
        	        }
	            }
	        }
	    }
	    return $this;
	}
	
	/**
	 * Executes the query and returns the first row in the result set
	 * as a new Model object.
	 *
	 * Note that this only works on SELECT queries and it will 
	 * automatically set the limit for the query to 1.
	 *
	 * @since 0.3.0
	 * @return array The first row of the result set.
	 * @throws RuntimeException
	 */
	public function first()
	{
		if($this->_command !== "select") {
			return array();
		}
		$this->_limit = 1;
		$results = $this->exec();
		return $results->getAt(0);
	}
	
	/**
	 * Executes the current query based on data passed to any
	 * of the various query builder methods.
	 *
	 * A command must be specified first.
	 *
	 * Select commands return a new Collection object.
	 * Count commands return the count;
	 * All other commands return the number of rows affected.
	 *
	 * @since 0.3.0
	 * @return array|int The result set for select statements, 
	 * 	otherwise the number of rows affected.
	 * @throws RuntimeException
	 */
	public function exec()
	{
		if(!DB::isConnected()) {
			throw new \RuntimeException("Query: No valid DB connection");
		}
		if($this->_command !== "select") {
			// use the default exec method
			return parent::exec();
		}
		
		$query = $this->getQuery();
		if(empty($query)) {
			return array();
		}
		$pdo = DB::getPDO();
		
		// for selects, wrap the results in a collection object
		$smt = $pdo->query($query, \PDO::FETCH_ASSOC);
		$results = $smt->fetchAll();
		Backbone::uses("Collection");
		return new Collection($this->_model, $results);
	}
}
?>