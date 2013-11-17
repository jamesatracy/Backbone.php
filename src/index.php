<?php
/**
 * Backbone.php
 * 
 * @author	James Tracy <james.a.tracy@gmail.com>
 * @copyright	2012-2013
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link https://github.com/jamesatracy/Backbone.php GitHub Page
 */

// root directory path constant
define('ABSPATH', dirname(__FILE__).'/');

// load backbone.php configurations
require_once(ABSPATH."config.php");

// sanity checks
if(!defined('FRAMEWORK')) {
	echo "Backbone.php configuration error: FRAMEWORK not defined.";
	exit(1);
}
if(!defined('APPPATH')) {
	echo "Backbone.php configuration error: APPPATH not defined.";
	exit(1);
}
if(!defined('VIEWPATH')) {
	echo "Backbone.php configuration error: VIEWPATH not defined.";
	exit(1);
}
if(!defined('ROUTERPATH')) {
	echo "Backbone.php configuration error: ROUTERPATH not defined.";
	exit(1);
}

// load the framework
require_once(FRAMEWORK."Backbone.class.php");
Backbone::initialize();

//  load boot file
if(!file_exists("boot.php")) {
	// The boot file defines Routers and other necessary start up includes.
	$resp = new Response();
	$resp->status(500);
	$resp->body("Missing boot.php!");
	$resp->send();
	exit(1);
}

require("boot.php");

// dispatch request
if(!Backbone::dispatch()) {
	// send 404 response
	$resp = new Response();
	$resp->status(404);
	$resp->send();
}
?>