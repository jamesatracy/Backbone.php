<?php
/**
 * Perform all of your bootstrap operations here in this file.
 * That includes loading Routers and other resources (database connections, etc.).
 */

// Set the web root path
Backbone::$root = "/Backbone.php/examples/blog/";

// Include modules
Backbone::uses("DB");

// Load routers
Backbone::loadRouter("/routers/BlogRouter");

// Database
Backbone\DB::connect("mysql:dbname=".DATABASE_NAME.";host=".DB_SERVER, DB_USER, DB_PASS);
?>
