<?php
/*
Collection.class.php
Copyright (C) 2012 James Tracy

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/

/*
@fileoverview
Base Collection class for representing an ordered list of model objects.

@since 0.1.0
*/

Backbone::uses("Model");

class Collection
{
	/* The database connection */
	protected $_db = null;
	
	/* The table associated with this collection */
	protected $_table = "";
	
	/* The class name of the model class contained by the collection */
	protected $_model = "Model";
	
	/* The array of raw model data */
	protected $_models = array();
	
	/* The length of the data set returned from a call to fetch() */
	public $length = 0;
	
	/* For iterator implementation */
	protected $_position = 0;
	
	/* An array of error messages */
	protected $_errors = array();
	
	/* Constructor */
	public function __construct($table, $options = array())
	{
		if(!$table || empty($table))
			return; // bad
		$options = array_merge(array(
			"model" => "Model",
			"db" => "default"
		), $options);
		$this->_table = $table;
		$this->_model = $options['model'];
		$this->_db = Connections::get($options['db']);
	}
	
	/*
	Get the number of items in a collection without performing a fetch().
	
	@param [array] $options Fetch options.
		"where" => array()
		"order_by" => array("field", ["ASC"/"DESC"])
		"limit" => integer
	@return [integer] The number of items.
	*/
	public function count($options = array())
	{
		if(!$this->_db)
		{
			$this->_errors[] = "Invalid Database Connection";
			return false;
		}
		return $this->_db->count($this->_table, $options);
	}
	
	/*
	Fetch a collection from the database
	
	@param [array] $options Fetch options.
		"where" => array()
		"order_by" => array("field", ["ASC"/"DESC"])
		"limit" => integer
		"offset" => integer
	@return [boolean] True if successful.	
	*/
	public function fetch($options = array())
	{
		$this->reset();
		if(!$this->_db)
		{
			$this->_errors[] = "Invalid Database Connection";
			return false;
		}
		
		$result = $this->_db->selectAll(
			$this->_table,
			$options
		);
		if(!$result->isValid() || $this->_db->getError())
		{
			$this->errors[] = $this->_db->getError();
		}
		$this->_models = $result->fetchAll();
		$this->length = $result->numRows();
		$this->rewind();
		return true;
	}
	
	/*
	Get a model by ID (primary key).
	Uses the results returned by a call to fetch().
	
	@param [string] $id The model's ID
	@return [Model] The model, or null if not found
	*/
	public function get($id)
	{
		if(!$this->_db)
			return null;
		$model = null;
		$schema = new Schema($this->_db);
		$schema->initialize($this->_table);
		$key = $schema->getID();
		// loop over models
		foreach($this->_models as $data)
		{
			if(isset($data[$key]) && $data[$key] == $id)
			{
				$model = new $this->_model($this->_table, $this->_db);
				$model->set($data);
				break;
			}
		}
		return $model;
	}
	
	/* 
	Create a new instance of a model in a collection.
	This is equivalent of creating a new model instance, settign its attributes,
	and saving it.
	Fires a "collection.[table name].add" event with the new model.
	
	@param [array] $attributes An array of attributes.
	@return [Model,boolean] Returns the model if successful, false otherwise.
	*/
	public function create($attributes)
	{
		$model = new $this->_model($this->_table, $this->_db);
		$model->set($attributes);
		if($model->save())
		{
			Events::trigger("collection.".$this->_table.".add", $model);
			return $model;
		}
		return false;
	}
	
	/*
	Remove a model from a collection.
	This is equivalent of loading the model and then deleting it.
	Fires a "collection.[table name].remove event with the model ID.
	
	@param [integer] $id The ID of the model to remove
	@return [boolean] True if success
	*/
	public function remove($id)
	{
		if($this->_model != "Model")
		{
			Backbone::uses("/models/".$this->_model);
			$model = new $this->_model($this->_db);
		}
		else
		{
			$model = new $this->_model($this->_table, $this->_db);
		}
		if($model->fetch($id))
		{
			if($model->delete())
			{
				Events::trigger("collection.".$this->_table.".remove", $id);
				return true;
			}			
		}
		return false;
	}
	
	/* Rewind the Iterator to the first element */
	public function rewind() 
	{
        $this->_position = 0;
    }

	/* Return the current model element */
    public function current() 
	{
		if(isset($this->_models[$this->_position]))
		{
			if($this->_model != "Model")
			{
				Backbone::uses("/models/".$this->_model);
				$model = new $this->_model($this->_db);
			}
			else
			{
				$model = new $this->_model($this->_table, $this->_db);
			}
			$model->set($this->_models[$this->_position]);
			$model->clearChanged();
			return $model;
		}
		return null;
    }
	
	/* Returns the current element's raw attributes */
	public function currentAttributes()
	{
		if(isset($this->_models[$this->_position]))
			return $this->_models[$this->_position];
		return null;
	}

	/* Return the position of the current element */
    public function position() 
	{
        return $this->_position;
    }

	/* Move forward to next element */
    public function next() 
	{
        ++$this->_position;
    }
	
	/* Checks if current position is valid */
    public function items()
	{
        return isset($this->_models[$this->_position]);
    }
	
	/*
	Pluck an attribute from each model in the collection.
	
	@param [string] $name The attribute name
	@return [array] An array of attributes
	*/
	public function pluck($name)
	{
		$attrs = array();
		foreach($this->_models as $model)
		{
			if(isset($model[$name]))
				$attrs[] = $model[$name];
		}
		return $attrs;
	}
			
	/*
	Return the raw model data.
	
	@param [boolean] $compact Whether or not to include the model keys in the representation.
	@return [string] A JSON formatted string representation of the collection.
	*/
	public function toJSON($compact = false)
	{
		if($compact)
		{
			$collection = array();
			foreach($this->_models as $attributes)
				$collection[] = array_values($attributes);
			return $collection;
		}
		return $this->_models;
	}
	
	/*
	Get the associated model classname
	
	@return [string] The associated model's classname.
	*/
	public function getModelName()
	{
		return $this->_model;
	}
	
	/*
	Get the associated model table
	
	@return [string] The associated model's table name.
	*/
	public function getTableName()
	{
		return $this->_table;
	}
			
	/*
	Reset the collection to an empty state
	*/
	public function reset()
	{
		$this->_errors = array();
		$this->_models = array();
		$this->length = 0;
	}
	
	/*
	Retrieve the last errors
	*/
	public function getErrors()
	{
		return $this->_errors;
	}
};

?>