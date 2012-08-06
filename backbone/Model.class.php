<?php
/*
Model.class.php
Copyright (C) 2012 James Tracy

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/

/*
@fileoverview
Base Model class for database objects. 
Provides basic methods for creating, updating, fetching, and validating model data.
This class can be used on its own or extended to implement more specialized model functionality.
*/

Backbone::uses("Schema");

class Model extends Schema
{
	/* The table associated with this model */
	protected $_table = "";
	
	/* Hash map of model attributes */
	protected $_attributes = array();
	
	/* Hash map of changed model attributes */
	protected $_changed = array();
	
	/* Hash map of model associations */
	protected $_models = array();
	
	/* Array of has one model association definitions */
	public $hasModels = array();
	
	/*
	Contructor
	
	@param [string] $table The name of the table corresponding to this model
	@param [string] $connection The name of the mysql database connection
	*/
	public function __construct($table, $connection = "default")
	{
		if(!$table || empty($table))
			return; // bad
		parent::__construct($connection);
		$this->_table = $table;
		$this->initialize($table);
		$this->set($this->defaultValues());
	}
	
	/* 
	Fetch a model from the server based on the primary key
	
	@param [integer] $id The primary key
	@param [array] $options Hash map of options.
		"with" => array of model/collection associations to pull in with
			this fetch request.
	*/
	public function fetch($id, $options = array())
	{
		$attributes = array();
		if(!$this->_db || !$this->_db->isConnected())
			return false;
		$result = $this->_db->selectAll(
			$this->_table, 
			array(
				"where" => array($this->getID() => $id)
			)
		);
		if($result->isValid())
		{
			if($result->numRows() > 0)
			{
				$row = $result->fetch();
				$this->set($row);
				$this->_changed = array();
				
				if(isset($options) && isset($options['with']))
				{
					$this->_with($options['with']);
				}
				return true;
			}
		}
		return false;
	}
	
	/*
	Get the value of an attribute
	
	@param [string] $attr The name of the attribute
	@return [mixed] The value of the attribute, or null
	*/
	public function get($attr)
	{
		if($this->has($attr))
			return $this->_attributes[$attr];
		return null;
	}
	
	/*
	Set the value of an attribute or an array of attributes
	
	@param [string,array] $attr The attribute name or an array of $attr => $value pairs
	@param [mixed] $value The attribute value or null if using a hash map
	*/
	public function set($attr, $value = null)
	{
		$attributes = array();
		if(!$attr || empty($attr))
			return;
		if(is_array($attr))
		{
			// hash map
			$attributes = $attr;
		}
		else
		{
			// single attribute, value
			$attributes[$attr] = $value;
		}
		
		foreach($attributes as $key => $val)
		{
			if($this->hasField($key))
			{
				// only set this attribute if it exists in the schema
				if(!isset($this->_attributes[$key]))
				{
					$this->_attributes[$key] = $val;
					$this->_changed[$key] = true;
				}
				else
				{
					// only mark as changed if it actually changed
					if($this->_attributes[$key] !== $val)
					{
						$this->_attributes[$key] = $val;
						$this->_changed[$key] = true;
					}
				}
			}
		}
	}
	
	/*
	Save a model back to the server.
	Only changed fields are actually updated.
	
	@return [boolean] True on success
	*/
	public function save()
	{
		if(!$this->_db || !$this->_db->isConnected())
			return false;
		$attributes = array();
		foreach($this->_changed as $key => $value)
		{
			if($value && $this->has($key) && $key != $this->getID())
			{
				// update changed values if not primary key
				$attributes[$key] = $this->_attributes[$key];
			}
		}

		if(empty($attributes))
			return true;
		if($this->validate())
		{
			$result = null;
			if($this->isNew())
			{
				// insert
				$result = $this->_db->insert(
					$this->_table,
					$attributes
				);
			}
			else
			{
				// update
				$result = $this->_db->update(
					$this->_table, 
					$attributes,
					array(
						"where" => array($this->getID() => $this->get($this->getID()))
					)
				);
			}
			if(!$result)
				return false;
			if(!$result->isValid() || $this->_db->getError())
			{
				$this->_errors[] = $this->_db->getError();
				return false;
			}
			if($this->isNew())
			{
				$this->_attributes[$this->getID()] = $this->_db->lastInsertID();
			}
			$this->_changed = array();
			// Trigger a changed event on this model. 
			// The ID of the model is available as the hash of the name of the ID, and the changed
			// attributes as "data".
			//
			// Ex: array("car_id => "12", "data" => array("make" => "honda", "model" => "accord"))
			Events::trigger("model.changed.".$this->_table, array($this->getID() => $this->get($this->getID()), "data" => $attributes));
			return true;
		}
		return false;
	}
	
	/* 
	Removes the model from the database. 
	Triggers a deleted event on this model.
	*/
	public function delete()
	{
		if(!$this->_db || !$this->_db->isConnected())
			return false;
		$result = $this->_db->delete(
			$this->_table, 
			array(
				"where" => array($this->getID() => $this->get($this->getID()))
			)
		);
		if(!$result)
			return false;
		if(!$result->isValid() || $this->_db->getError())
		{
			$this->_errors[] = $this->_db->getError();
			return false;
		}
		Events.trigger("model.deleted.".$this->_table, array($this->getID() => $this->get($this->getID())));
		$this->clear();
		return true;
	}
	
	
	/*
	Validates the current set of attributes
	
	Use Model->getErrors() to get the error messages.
	
	@return [boolean] True if the attributes validate, false otherwise
	*/
	public function validate()
	{
		return parent::validate($this->_attributes);
	}
	
	/* 
	Get a reference to an associated model, if it exists.
	The model must have been fetched via the "with" option or $fetch
		must be set to true.
	
	@param [string] $key This model's foreign key to the other model.
	@param [boolean] $fetch Whether or not to force a fetch of the model
	@return [object,null] Returns the model object, or null
	*/
	public function model($key, $fetch = false)
	{
		if($fetch)
		{
			if(isset($this->hasModels[$key]))
			{
				$this->_models[$key] = $this->_fetchModel($key);
				return $this->_models[$key];
			}
			return null;
		}
		if(isset($this->_models[$key]))
			return $this->_models[$key];
		return null;
	}
	
	/*
	Return a JSON representation of the model.
	This is not a string, but an associative array that you can pass to JSON::stringify().
	
	The JSON is compacted by leaving out the field names and returning the values
	as a plain array.
	
	@param [boolean] $compact Whether or not to use a compact representation
	@return [array] A JSON representation of the model.
	*/
	public function toJSON($compact = false)
	{
		$models = array();
		foreach($this->_models as $key => $model)
		{
			if($model)
				$models[$key] = $model->toJSON($compact);
		}
		return (array("attributes" => ($compact ? array_values($this->_attributes) : $this->_attributes), "models" => $models));
	}
	
	/*
	Returns true if the attribute contains a value that is not null or undefined.
	
	@param [string] $attr The name of the attribute
	@return [string] True if the attribute is not null or undefined
	*/
	public function has($attr)
	{
		return (isset($this->_attributes[$attr]));// && $this->_attributes[$attr] != null);
	}
	
	/*
	Checks if an attribute has changed since the last time the model was saved
	
	@param [string] $attr The name of the attribute
	@return [boolean] True if the attribute has changed.
	*/
	public function changed($attr)
	{
		return (isset($this->_changed[$attr]) && $this->_changed[$attr]);
	}
	
	/*
	Checks if the model is new (has not been created on the server).
	It does this by checking the value of the primary key. If it is not > 0, then the model is new.
	
	@returns [boolean] True if the model is new
	*/
	public function isNew()
	{
		$id = $this->getID();
		if($this->has($id) && $this->_attributes[$id] > 0)
			return false;
		return true;
	}
	
	/* Remove all of the model's attributes. */
	public function clear()
	{
		$this->_attributes = array();
		$this->_changed = array();
	}
	
	/*
	Has the model changed since the last update?
	In other words, are there any unsaved changes?
		
	@return [string] True if the model has changed.
	*/
	public function hasChanged()
	{
		$keys = array_keys($this->_changed);
		return (!empty($keys));
	}
	
	/*
	Retrieve a hash of only the model's attributes that have changed.
	
	@return [array] An array of changed attributes
	*/
	public function changedAttributes()
	{
		$attributes = array();
		foreach($this->_changed as $key => $value)
			$attributes[$key] = $this->get($key);
		return $attributes;
	}
	
	/* Magic __get method that internally calls get() */
	public function __get($attr)
	{
		return $this->get($attr);
	}
	
	/* Magic __set method that internally calls set() */
	public function __set($attr, $value)
	{
		return $this->set($attr, $value);
	}
	
	/* Magic __isset method that internally calls has() */
	public function __isset($attr)
	{
		return $this->has($attr);
	}
	
	/*
	Internal function that loops over with options and pulls
	in associated models/collections.
	*/
	protected function _with($with)
	{
		$this->_models = array();
		if(!is_array($with))
			$with = array($with);
		foreach($with as $foreign_key)
		{
			// get the model
			$this->_models[$foreign_key] = $this->_fetchModel($foreign_key);
		}
	}
	
	/*
	Internal function to fetch an associated model
	*/
	protected function _fetchModel($key)
	{
		if(isset($this->hasModels[$key]))
		{
			$options = $this->hasModels[$key];
			if(!isset($options['model']))
				continue;
			$classname = $options['model'];
			// model association exists
			if($this->get($key) > 0)
			{
				$module = $classname;
				if(substr($module, 0, 1) != "/")
					$module = "/models/".$module;
				Backbone::uses($module);
				if(class_exists($classname))
				{
					$instance = new $classname;
					$instance->fetch($this->get($key));
					return $instance;
				}
			}
		}
		return null;
	}
}
?>