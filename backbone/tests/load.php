<?php
// root directory path constant
define('ABSPATH', dirname(__FILE__).'/');

// requires
require_once(ABSPATH."config.php");
require_once(FRAMEWORK."Backbone.class.php");
Backbone::initialize();

// Set the web root path
Backbone::$root = "/Backbone.php/backbone/tests/";

// create request object
Backbone::$request = new Request();
?>