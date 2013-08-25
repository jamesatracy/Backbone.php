<?php
/** Setup routine for Backbone.php scripts.
 * This should be included at the top of each test runner.
 */
if(!defined('ABSPATH')) 
	define('ABSPATH', dirname(__FILE__).'/../');
if(!defined('FRAMEWORK'))
	define('FRAMEWORK', ABSPATH.'src/backbone/');

require_once(FRAMEWORK.'Backbone.class.php');
Backbone::$config = new DataMap();  
?>