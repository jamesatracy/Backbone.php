<?php
/**
 * Backbone.php
 * 
 * @author	James Tracy <james.a.tracy@gmail.com>
 * @copyright	2012-2013
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link https://github.com/jamesatracy/Backbone.php GitHub Page
 */

// load the framework
require("backbone.php");

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
if(!Backbone::dispatch(Backbone::$request)) {
	// send 404 response
	$resp = new Response();
	$resp->status(404);
	$resp->send();
}
?>
