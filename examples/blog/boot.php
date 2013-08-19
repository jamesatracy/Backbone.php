<?php
/**
 * Perform all of your bootstrap operations here in this file.
 * That includes loading Routers and other resources (database connections, etc.).
 */

// Set the web root path
Backbone::$root = "/Backbone.php/examples/blog/";

// Load routers
Backbone::loadRouter("BlogRouter");

// Include modules
Backbone::uses(array("Connections", "MySQL", "MySQLLogger"));

// Database
Connections::create("default", "MySQL", array("server" => DB_SERVER, "user" => DB_USER, "pass" => DB_PASS));

// Mysql logging
Backbone::$config->set("mysql.log", false);
Backbone::$config->set("mysql.logfile", "mysql.log");

//MySQLLogger::clearQueryLog();
?>
