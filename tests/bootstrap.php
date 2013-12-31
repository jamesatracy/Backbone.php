<?php
/** PHPUnit bootstrap file */

// PHP and Web server settings
error_reporting(E_ALL | E_STRICT);
// Set the default timezone
date_default_timezone_set("America/New_York");

/** Setup routine for Backbone.php scripts. */
if(!defined('ABSPATH')) 
	define('ABSPATH', dirname(__FILE__).'/../');
if(!defined('FRAMEWORK'))
	define('FRAMEWORK', ABSPATH.'src/backbone/');
if(!defined('APPPATH'))
	define('APPPATH', ABSPATH);
$VIEWPATH = ABSPATH.'tests/views/';

require_once(FRAMEWORK.'Backbone.class.php');
?>