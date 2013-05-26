<?php
/**
 * Backbone.php
 * 
 * @author	James Tracy <james.a.tracy@gmail.com>
 * @copyright	2012-2013
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 */

// Default configurations
Backbone::$config->set("mysql.log", false);
Backbone::$config->set("mysql.logfile", "mysql.log");

/**
 * Class for logging MySQL queries.
 * 
 * To set mysql logging to true, Backbone::$config->set("mysql.log", true)
 * To change the log file, Backbone::$config->set("mysql.logfile", "mysql.log")
 * File name is relative to the application's web root and must be writable.
 */
class MySQLLogger
{
	/* Query logging */
	protected static $_queries_count = 0;
	protected static $_queries_time = 0;
	protected static $_paused = false;
	
	/*
	Log a mysql query
	To set mysql logging to true, Backbone::$config->set("mysql.log", true)
	To change the log file, Backbone::$config->set("mysql.logfile", "mysql.log")
		File name is relative to the application's web root.
		
	@param [string] $server The mysql connection server name
	@param [string] $sql The query string
	@param [integer] $duration The time, in seconds, that the query took to execute
	@param [integer] $num_rows The number of rows returned by the query
	@param [string] $name Optional name for this db connection
	*/
	public static function logQuery($server, $sql, $duration, $num_rows, $name = "")
	{
		if(self::$_paused)
			return;
			
		self::$_queries_count++;
		self::$_queries_time += $duration;

		$ob = "[".$server.($name ? " / ".$name : "")."]\r\n".$sql."\r\nNum Rows: ".$num_rows."\r\nDuration: ".$duration." seconds\r\n";
		if(mysql_error())
			$ob .= "Error: ".mysql_error()."\r\n";
		$ob .= "\r\n";
		file_put_contents(APPPATH.Backbone::$config->get("mysql.logfile"), $ob, FILE_APPEND);
	}
	
	/*
	Writes the current running totals to the end of the query log.
	*/
	public static function tallyQueryLog()
	{
		if(Backbone::$config->get("mysql.log") == true)
		{
			if(file_exists(APPPATH.Backbone::$config->get("mysql.logfile")))
			{
				$ob = "----------------------------------------------------------------\r\n"; 
				$ob .= "Total Queries: ".self::$_queries_count."\r\n";
				$ob .= "Total Duration: ".self::$_queries_time." seconds\r\n";
				$ob .= "----------------------------------------------------------------\r\n"; 
				$ob .= "Active DB Connections: ".join(", ", Connections::enumerateConnections())."\r\n";
				$ob .= "----------------------------------------------------------------\r\n\r\n"; 
				file_put_contents(APPPATH.Backbone::$config->get("mysql.logfile"), $ob, FILE_APPEND);
			}
		}
	}
	
	/*
	Clear the mysql log file if mysql logging is set to true.
	To set mysql logging to true, Backbone::$config->set("mysql.log", true)
	To change the log file, Backbone::$config->set("mysql.logfile", "mysql.log")
		File name is relative to the application's web root.
	*/
	public static function clearQueryLog()
	{
		self::$_queries_count = 0;
		self::$_queries_time = 0;
		
		if(Backbone::$config->get("mysql.log") == true)
		{
			if(file_exists(APPPATH.Backbone::$config->get("mysql.logfile")))
			{
				file_put_contents(APPPATH.Backbone::$config->get("mysql.logfile"), "");
			}
		}
	}
	
	/* Get the current running total query count */
	public static function getQueryCount()
	{
		return self::$_queries_count;
	}
	
	/* Get the current running total query time in milliseconds */
	public static function getQueryTime()
	{
		return self::$_queries_time;
	}
	
	/* Pause the query logging. Call resume() to recommence */
	public static function pause()
	{
		self::$_paused = true;
	}
	
	/* Resume query logging */
	public static function resume()
	{
		self::$_paused = false;
	}
}
?>