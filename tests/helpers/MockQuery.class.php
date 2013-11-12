<?php
/**
 * Backbone.php
 * 
 * @author	James Tracy <james.a.tracy@gmail.com>
 * @copyright	2012-2013
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link https://github.com/jamesatracy/Backbone.php GitHub Page
 */

Backbone::uses("Query");
use Backbone\Query;
use Backbone\DB;

/**
 * Mock Query
 */
class MockQuery extends Query
{
    /**
	 * Build an insert query.
	 *
	 * Sets the current command to 'insert'. Requires at least
	 * one set of key - value pairs to insert.
	 *
	 * @since 0.3.0
	 * @param array $fields An array of key value pairs to insert.
	 * @return Query The active query object.
	 * @throws InvalidArgumentException
	 */
	public function insert($fields)
	{
	    $pdo = DB::getPDO();
	    $pdo->setResultsData(array($fields));
		return parent::insert($fields);
	}
	
	/**
	 * Build an update query.
	 *
	 * Sets the current command to 'udpate'. Requires at least
	 * one set of key - value pairs to update.
	 *
	 * @since 0.3.0
	 * @param array $fields An array of key value pairs to update.
	 * @return Query The active query object.
	 * @throws InvalidArgumentException
	 */
	public function update($fields)
	{
		$pdo = DB::getPDO();
	    $pdo->setResultsData(array($fields));
		return parent::update($fields);
	}
}
?>