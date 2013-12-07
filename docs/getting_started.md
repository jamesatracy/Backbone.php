[Backbone.php Homepage](https://github.com/jamesatracy/Backbone.php) | [Table of Contents](toc.md)

To get a Backbone.php website up and running place a copy of the `/backbone/` directory as well as the files 'htacces', 'config.php', 'boot.php', and 'index.php' in your web root. 

Modify the `congif.php` file to set the path to the backbone core directory, application files, and view files, all relative to your web root. Note that your copy of the backbone core directory need not be located under your site's web root if you wish to share it among several websites. 

The view path can take a semi-colon delimited list of relative paths in the event that you need to split up your views among multiple directories. Backbone will search for the file in each directory, in the order listed, until it is found.

	<?php
	/* Backbone.php configuration file. */

	// Framework path constant. Points to the backbone core.
	define('FRAMEWORK', ABSPATH."backbone/");
	// Application path constant. Points to the application's root directory
	define('APPPATH', ABSPATH."");
	// View path constant. Semicolon delimited list of paths to search for views.
	// Must be relative to the abspath.
	define('VIEWPATH', "/views/");
	?>
	
Modify the `boot.php` file to perform all other bootstrap operations, like defining routes, setting configurations, and initiating database connections. Note that defining at least one route is the bare minimum requirements for getting a Backbone.php website up and running.

	<?php
	/*
	Perform all of your bootstrap operations here in this file.
	That includes loading Routers and other resources (database connections, etc.).
	*/

	// Include modules
    Backbone::uses("DB");
    
    // Routes
    Router::get("/", "/controllers/BlogController@index");
    Router::get("/create/", "/controllers/BlogController@create");
    Router::post("/create/", "/controllers/BlogController@createSubmit");
    
    // Database
    DB::connect("mysql:dbname=".DATABASE_NAME.";host=".DB_SERVER, DB_USER, DB_PASS);
	?>