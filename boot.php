<?php
/*
Perform all of your bootstrap operations here in this file.
That includes loading Routers and other resources (database connections, etc.).
*/

// Set the web root path
Backbone::$root = "/Backbone.php/";

// Load routers
Backbone::loadRouter("TestRouter");

// Database
Backbone::uses(array("Connections", "MySQL"));

Connections::create("db", "MySQL", array("server" => "localhost", "user" => "jtracy", "pass" => "Roxie910"));

?>