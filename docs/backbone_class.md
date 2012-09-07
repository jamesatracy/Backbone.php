[Backbone.php Homepage](https://github.com/jamesatracy/Backbone.php) * [Table of Contents](toc.md)

## Backbone.class

The Backbone class is the heart of a Backbone.php application. It is a static class that handles routes and module loading as well as servers as a namespace for global objects and variables.

`$rootBackbone::$root`

A public static string representing the web root relative to the domain name. If your web root is in the root of your domain name, then $root should be set to "/". If, say, your web root is in "www.example.com/app/" then $root should be set to "/app/". This is required for the Html class to properly format links.

*$config* `Backbone::$config`

A public static configuration object to hold all of your application's configuration settings. The $config variable is a DataSet object that exposes get() and set() methods that maniuplate key => value pairs. Keys can be namespaced using the "." operator. Values can be of any type. Some core Backbone.php classes (such as MySQL) may make use of the $config object (these will always be prefixed by the name of the class) so be sure that your key names are unique.

	Backbone::$config.set("app.debugging", false);
	Backbone::$config.set("mysql.log", true);
	Backbone::$config.set("mysql.logfile", "/tmp/");

	// returns false
	Backbone::$config.get("app.debugging");
	// returns true
	Backbone::$config.get("mysql.log");
	// returns "/tmp/"
	Backbone::$config.get("mysql.logfile");
	
*initialize* `Backbone::initialize()`

Initializes the framework. This method is called by backbone.php and should not be called directly by the application.

*version* `Backbone::version()`

Returns the version number of the Backbone.php installation.

*uses* `Backbone::uses($modules)`

Includes a class module. [Modules](modules.md) are referenced by their corresponding class name. For example, if you have a module named "Employee" then you must define a class named "Employee" and name the file "Employee.class.php" for backbone to find it. The parameter to uses() can be either a string or an arrray of strings to load multiple modules. For loading Backbone.php modules you simply pass in the class name - for application defined modules you prepend the classname with the path relative to the web root (or "/" if in the web root).

	// Backbone.php module
	Backbone::uses("Model");
	// Load multiple Backbone.php modules
	Backbone::uses(array("MySQL", "Model"));
	// Load an application defined module 
	Backbone::uses("/models/Employee"); 

Modules that have already been loaded are ignored by uses(). Backbone.class keeps a list of loaded modules that can be examined through:

	Backbone::$modules
	
*loadRouter* `Backbone::loadRouter($name)`

Includes a router. This is equivalent of calling Backbone::uses("/router/[Router]").

*addRouter* `Backbone::addRouter($router)`

Registers a router instance with the framework. Routes are checked for matches in the order in which they are added.

	Backbone::addRouter(new MyAppRouter());
	
*dispatch* `Backbone::dispatch($request);`

Dispatches the request by trying to find a matching route among the registered routers. This method is called by the framework in index.php and does not need to be called by the application.
