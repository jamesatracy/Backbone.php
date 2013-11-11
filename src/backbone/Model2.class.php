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

Backbone::uses("Query");

/**
 * Base Model class for database objects. 
 *
 * Provides basic methods for creating, updating, fetching, and 
 * validating model data.
 *
 * @since 0.1.0
*/
class Model2
{
	/** @var string The database table name associated with this model */
	public $table = "";
	
	/** @var array Hash map of model attributes */
	protected $_attributes = array();
	
	/**
	 * Constructs a new model with an optional set of initial values.
	 *
	 * @since 0.3.0
	 * @param array $fields Optional initial values as key - value pairs.
	 */
	public function __construct($fields = null)
	{
		if($fields !== null) {
			$this->set($fields);
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
	 * @throws InvalidArgumentException|RuntimeException
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
			$result = DB::table($this->table)
			->select()
			->where($this->getID(), $id)
			->exec();
			if(empty($result)) {
				return false;
			}
			$classname = get_class($this);
			return new $classname($result);
		} else {
			return new ModelQuery($this->table);
		}
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
	
}
?>