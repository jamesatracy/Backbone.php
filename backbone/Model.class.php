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
	*/
	public function fetch($id)
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
			$row = $result->fetch();
			$this->set($row);
			$this->_changed = array();
		}
		return true;
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
	Return a JSON formatted string representation of the model's attributes
	
	@return [string] A JSON formatted string representation of the model's attributes
	*/
	public function toJSON()
	{
		return JSON::encode($this->_attributes);
	}
	
	/*
	Return a compact JSON formatted string representation of the model's attributes.
	The JSON string is compacted by leaving out the field names and returning the values
	as a plain array.
	
	@return [string] A JSON formatted string representation of the model's attributes
	*/
	public function toCompactJSON()
	{
		return JSON::encode(array_values($this->_attributes));
	}
	
	/*
	Returns true if the attribute contains a value that is not null or undefined.
	
	@param [string] $attr The name of the attribute
	@return [string] True if the attribute is not null or undefined
	*/
	public function has($attr)
	{
		return (isset($this->_attributes[$attr]) && $this->_attributes[$attr] != null);
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
}
?>