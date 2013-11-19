<?php
/* Backbone.php configuration file. */

// Framework path constant. Points to the backbone core.
define('FRAMEWORK', ABSPATH."../../src/backbone/");
// Application path constant. Points to the application's root directory
define('APPPATH', ABSPATH."/");
// View path constant. Semicolon delimited list of paths to search for views.
// Must be relative to the abspath.
define('VIEWPATH', "/views/");

/* Application specific defines */

// Database configurations
if(!defined('DATABASE_NAME')) {
	define('DATABASE_NAME', 'blog');
}
if(!defined('DB_SERVER')) {
	define('DB_SERVER', 'localhost');
}
if(!defined('DB_USER')) {
	define('DB_USER', 'root');
}
if(!defined('DB_PASS')) {
	define('DB_PASS', '');
}
?>