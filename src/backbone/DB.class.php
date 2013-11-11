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
 * DB module
 * 
 * Provides an abstract interface to a SQL database connection.
 * DB static methods configure the globally active connection. 
 *
 * The DB class wraps a PDO object.
 * 
 * @since 0.3.0
 */
class DB
{
	/** @var PDO The PDO object for the active connection or null */
	protected static $_pdo = null;
	
	/**
	 * Connect to a database.
	 *
	 * There arguments here are identical to the arguments taken by
	 * the PDO class constructor.
	 *
	 * @since 0.3.0
	 * @param string $dsn The Data Source Name.
	 * @param string $user The user name to connect with.
	 * @param string $password The password to connect with.
	 * @param array $config Optional driver configurations.
	 * @throws RuntimeException
	 */
	public static function connect($dsn, $user, $password, $config = array())
	{
		DB::disconnect();
		
		try {
			self::$_pdo = new \PDO($dsn, $user, $password, $config);
		} catch (\PDOException $e) {
			throw new \RuntimeException($e->getMessage(), (int)$e->getCode());
		}
	}
	
	/**
	 * Disconnect from the active database connection.
	 *
	 * @since 0.3.0
	 */
	public static function disconnect()
	{
		self::$_pdo = null;
	}
	
	/**
	 * Returns true if there is an active database connection.
	 *
	 * @since 0.3.0
	 * @return bool True if the database is connected.
	 */
	public static function isConnected()
	{
		return (self::$_pdo !== null);
	}
	
	/**
	 * Get a reference to the internal PDO object.
	 *
	 * @since 0.3.0
	 * @return PDO The PDO object reference.
	 */
	public static function getPDO()
	{
		return self::$_pdo;
	}
	
	/**
	 * Sets the active PDO object.
	 *
	 * @since 0.3.0
	 * @param PDO $pdo The PDO object reference.
	 */
	public static function setPDO($pdo)
	{
		self::$_pdo = $pdo;
	}
	
	/**
	 * Create a new query builder object with the given table name.
	 *
	 * @since 0.3.0
	 * @param string $name The name of the table.
	 * @return Query A new query builder object.
	 */
	public static function table($name)
	{
		return new Query($name);
	}
	
	/**
	 * Set up a raw query string to execute.
	 *
	 * Example:
	 * 	DB::raw("SELECT * FROM users)->exec();
	 *
	 * @since 0.3.0
	 * @param string $query The raw query string.
	 * @return array The result set.
	 * @throws RuntimeException
	 */
	public static function raw($query)
	{
		if(!self::isConnected()) {
			throw new \RuntimeException("DB: No valid DB connection");
		}
		$smt = self::$_pdo->query($query, \PDO::FETCH_ASSOC);
		$rows = $smt->fetchAll();
		return $rows;
	}
}
?>