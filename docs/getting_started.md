[Backbone.php Homepage](https://github.com/jamesatracy/Backbone.php) | [Table of Contents](toc.md)

To get a Backbone.php website up and running place a copy of the `/backbone/` directory as well as the files 'htacces', 'backbone.php', 'config.php', 'boot.php', and 'index.php' in your web root. 

Modify the `congif.php` file to set the path to the backbone core directory, application files, and view files, all relative to your web root. Note that your copy of the backbone core directory need not be located under your site's web root if you wish to share it among several websites.

	<?php
	/* Backbone.php configuration file. */

	// Framework path constant. Points to the backbone core.
	define('FRAMEWORK', ABSPATH."../../backbone/");
	// Application path constant. Points to the application's root directory
	define('APPPATH', ABSPATH."/");
	// View path constant. Points to the application's view path.
	define('VIEWPATH', APPPATH."views/");
	?>
	
Modify the `boot.php` file to set your web root's path (if not the top level directory of your domain) and perform all other bootstrap operations, like loading Routers, setting configurations, and initiating database connections. Note that setting the web root path and loading the main Router are the bare minimum requirements for getting a Backbone.php website up and running.

	<?php
	/*
	Perform all of your bootstrap operations here in this file.
	That includes loading Routers and other resources (database connections, etc.).
	*/

	// Set the web root path
	Backbone::$root = "/Backbone.php/examples/blog/";

	// Load routers
	Backbone::loadRouter("BlogRouter");

	// Include modules
	Backbone::uses(array("Connections", "MySQL"));

	// Database
	Connections::create("default", "MySQL", array("server" => "localhost", "user" => "root", "pass" => ""));
	?>