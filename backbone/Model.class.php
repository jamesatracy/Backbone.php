<?php
/**
 * Backbone.php
 * 
 * @author	James Tracy <james.a.tracy@gmail.com>
 * @copyright	2012-2013
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link https://github.com/jamesatracy/Backbone.php GitHub Page
 */

Backbone::uses("Schema");

/**
 * Base Model class for database objects. 
 *
 * Provides basic methods for creating, updating, fetching, and validating model data.
 * This class can be used on its own or extended to implement more specialized model functionality.
 *
 * @since 0.1.0
*/
class Model extends Schema
{
	/** @var string The database table name associated with this model */
	protected $_table = "";
	
	/** @var array Hash map of model attributes */
	protected $_attributes = array();
	
	/** @var array Hash map of changed model attributes */
	protected $_changed = array();
	
	/** @var array Hash map of field sanitations */
	protected $_sanitations = array();
	
	/** @var array Hash map of model associations */
	protected $_models = array();
	
	/** @var array Hash map of collection associations */
	protected $_collections = array();
	
	/** @var array Array of has one model association definitions */
	public $hasModels = array();
		
	/** @var array Array of has one collection association definitions */
	public $hasCollections = array();
	
	/** @var array Array of additional where conditions to be applied to every model db action */
	public $where = array();
	
	/** @var string Field to update with current timestamp when model is created */
	public $created = null;
	
	/**
	 * Contructor
	 * 
	 * @since 0.1.0
	 * @constructor
	 * @param string $table The name of the table corresponding to this model
	 * @param string $connection The name of the mysql database connection
	 */
	public function __construct($table, $connection = "default")
	{
		if(!$table || empty($table)) {
			return; // bad
		}
		parent::__construct($connection);
		$this->_table = $table;
		$this->initialize($table);
		$this->set($this->defaultValues());
		if($this->created) {
			$this->set($this->created, date('Y-m-d H:i:s', time()));
		}
	}
	
	/**
	 * Fetch a model from the server based on the primary key
	 *
	 * @since 0.1.0
	 * @param int $id The primary key
	 * @param array $options Hash map of options.
	 *	"with" => array of model/collection associations to pull in with
	 *		this fetch request.
	 * @return bool True if the operation succeeded, false otherwise
	 */
	public function fetch($id, $options = array())
	{
		$this->_errors = array();
		$attributes = array();
		if(!$this->_db || !$this->_db->isConnected()) {
			$this->_errors[] = get_class($this).": No database connection.";
			return false;
		}
		
		// select
		$where = $this->where;
		$where[$this->getID()] = $id;
		if(isset($options['where'])) {
			$where = array_merge($where, $options['where']);
		}
		
		$result = $this->_db->read(
			$this->_table, 
			array("where" => $where)
		);

		if(count($result) > 0) {
			$this->set($result[0]);
			$this->_changed = array();
			
			if(isset($options) && isset($options['with'])) {
				$this->_with($options['with']);
			}
			return true;
		} else {
			if($this->_db->hasError()) {
				$this->_errors[] = $this->_db->getError();
			} else {
				$this->_errors[] = get_class($this).": No results returned";
			}
		}
		return false;
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
	 */
	public function save()
	{
		if(!$this->_db || !$this->_db->isConnected()) {
			$this->_errors[] = get_class($this).": No database connection.";
			return false;
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
				$result = $this->_db->create(
					$this->_table,
					$attributes
				);
			} else {
				// update
				$where = $this->where;
				$where[$this->getID()] = $this->get($this->getID());
		
				$result = $this->_db->update(
					$this->_table, 
					$attributes,
					array("where" => $where)
				);
			}
			if(!$result) {
				$this->_errors = array($this->_db->getError());
				return false;
			}
			if($this->isNew()) {
				$this->_attributes[$this->getID()] = $this->_db->lastInsertID();
			}
			$this->_changed = array();
			$this->_errors = array();
			// Trigger a changed event on this model. 
			// The ID of the model is available as the hash of the name of the ID, and the changed
			// attributes as "data".
			//
			// Ex: array("car_id => "12", "data" => array("make" => "honda", "model" => "accord"))
			Events::trigger($this->_table.".model.changed", array($this->getID() => $this->get($this->getID()), "data" => $attributes));
			return true;
		}
		return false;
	}
	
	/**
	 * Removes the model from the database. Triggers a deleted event on this model.
	 *
	 * @since 0.1.0
	 * @return bool True if the operation succeeded, false otherwise
	 */
	public function delete()
	{
		if(!$this->_db || !$this->_db->isConnected()) {
			$this->_errors[] = get_class($this).": No database connection.";
			return false;
		}
		
		// cannot delete if the model is new
		if($this->isNew()) {
			$this->_errors[] = get_class($this).": Cannot delete new model";
			return false;
		}
			
		$where = $this->where;
		$where[$this->getID()] = $this->get($this->getID());
				
		$result = $this->_db->delete(
			$this->_table, 
			array("where" => $where)
		);

		if(!$result) {
			$this->_errors[] = $this->_db->getError();
			return false;
		}
		Events::trigger($this->_table.".model.deleted", array($this->getID() => $this->get($this->getID())));
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
	 * Get a reference to an associated model, if it exists.
	 *
	 * The model must have been fetched via the "with" option or $fetch
	 * must be set to true.
	 *
	 * @since 0.1.0
	 * @param string $key This model's foreign key to the other model.
	 * @param bool $fetch Whether or not to force a fetch of the model
	 * @return array|null Returns the model object, or null
	 */
	public function model($key, $fetch = false)
	{
		if($fetch) {
			if(isset($this->hasModels[$key])) {
				$this->_models[$key] = $this->_fetchModel($key);
				return $this->_models[$key];
			}
			return null;
		}
		if(isset($this->_models[$key])) {
			return $this->_models[$key];
		}
		return null;
	}
	
	/**
	 * Get a reference to an associated collection, if it exists.
	 *
	 * The collection must have been fetched via the "with" option or $fetch
	 * must be set to true.
	 *
	 * @since 0.1.0
	 * @param string $key This collection's table name.
	 * @param bool $fetch Whether or not to force a fetch of the collection
	 * @return array|null Returns the collection object, or null
	 */
	public function collection($key, $fetch = false)
	{
		if($fetch) {
			if(isset($this->hasCollections[$key])) {
				$this->_collections[$key] = $this->_fetchCollection($key);
				return $this->_collections[$key];
			}
			return null;
		}
		if(isset($this->_collections[$key])) {
			return $this->_collections[$key];
		}
		return null;
	}
	
	/**
	 * Return a JSON representation of the model.
	 *
	 * This is not a string, but an associative array that you can pass to json_encode().
	 *
	 * The JSON is compacted by leaving out the field names and returning the values
	 * as a plain array.
	 *
	 * @since 0.1.0
	 * @param bool $compact Whether or not to use a compact representation
	 * @return array A JSON representation of the model.
	 */
	public function toJSON($compact = false)
	{
		$models = array();
		foreach($this->_models as $key => $model) {
			if($model) {
				$models[$key] = $model->toJSON($compact);
			}
		}
		$collections = array();
		foreach($this->_collections as $key => $collection) {
			if($collection) {
				$collections[$key] = $collection->toJSON($compact);
			}
		}
		return (array("attributes" => ($compact ? array_values($this->_attributes) : $this->_attributes), "models" => $models, "collections" => $collections));
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
	 * Set the model's field sanitation rules
	 *
	 * array(
	 *	"field1" => array(callable, ...),
	 *	"field2" => array(callable, ...)
	 * )
	 *
	 * @since 0.1.0
	 * @param array $sanitations Hash map of sanitation rules
	 */
	public function sanitations($sanitations)
	{
		$this->_sanitations = $sanitations;
	}
	
	/**
	 * Get the table name used for this model
	 *
	 * @since 0.1.0
	 * @return string The table name
	 */
	public function getTable()
	{
		return $this->_table;
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
	 * Internal function that loops over with options and pulls in associated models/collections.
	 *
	 * @since 0.1.0
	 */
	protected function _with($with)
	{
		$this->_models = array();
		$this->_collections = array();
		if(is_string($with)) {
			$with = array($with);
		}

		foreach($with as $index => $foreign_key) {
			if(isset($this->hasModels[$foreign_key])) {
				// get the model
				$this->_models[$foreign_key] = $this->_fetchModel($foreign_key);
			} else if (isset($this->hasCollections[$foreign_key])) {
				// get the collection
				$this->_collections[$foreign_key] = $this->_fetchCollection($foreign_key);
			}
		}
	}
	
	/**
	 * Internal function to fetch an associated model
	 *
	 * @since 0.1.0
	 * @return mixed
	 */
	protected function _fetchModel($key)
	{
		if(isset($this->hasModels[$key])) {
			$options = $this->hasModels[$key];
			if(!isset($options['model'])) {
				continue;
			}
			$classname = $options['model'];
			// model association exists
			if($this->get($key) > 0) {
				$module = $classname;
				if(substr($module, 0, 1) != "/") {
					$module = "/models/".$module;
				}
				Backbone::uses($module);
				if(class_exists($classname)) {
					$instance = new $classname($this->_db);
					if($instance->fetch($this->get($key)))
						return $instance;
				}
			}
		}
		return null;
	}
	
	/**
	 * Internal function to fetch an associated collection
	 *
	 * @since 0.1.0
	 * @return mixed
	 */
	protected function _fetchCollection($key)
	{
		if(isset($this->hasCollections[$key])) {
			$options = $this->hasCollections[$key];
			if(!isset($options['collection'])) {
				continue;
			}
			$classname = $options['collection'];
			// model association exists
			$module = $classname;
			if(substr($module, 0, 1) != "/") {
				$module = "/models/".$module;
			}
			Backbone::uses($module);
			if(class_exists($classname)) {
				$instance = new $classname($this->_db);
				if($instance->fetch(array("where" => array($options['key'] => $this->get($this->_id))))) {
					return $instance;
				}
			}
		}
		return null;
	}
	
	/**
	 * Internal function for applying sanitation rules 
	 *
	 * @since 0.1.0
	 * @return mixed The sanitized value
	 */
	protected function _applySanitation($key, $val)
	{
		if(isset($this->_sanitations[$key])) {
			foreach($this->_sanitations[$key] as $index => $callable) {
				$val = call_user_func_array($callable, array($val));
			}
		}
		return $val;
	}
}
?>
