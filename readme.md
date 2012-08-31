Backbone.php
============

Backbone.php, much like its javascript namesake, is a small collection of php classes that provide structure or scaffolding for a php application or php powered website. It follows the Model-View-Controller (MVC) convention and includes classes for handling routes, database backed models, and HTML views. Applications or websites built using Backbone.php can be up and running very quickly because it removes the necessity of writing much of the boilerplate code.

The framework is built with the following goals in mind:

* *Lightweight*: The core of Backbone.php provides just the right amount of structure and flexibility without being overly complicated.

* *Modular*: Backbone.php is highly modular and provides a simple mechanism for including other framework and application specific modules.

* *Best Practices*: Backbone.php is designed with software engineering best practices in mind, including MVC and Object-Oriented programming.

At its most simplest form, a Backbone.php application is nothing more than a series of url routes (such as "/about/") that are mapped to either views ("views/about-page.php") or callback methods ("public function about($args)") or both. That is essentially all that you need to get a Backbone.php application up and running. However, the framework also provides a number of classes for working specifically with data backed by a MySQL database in the form of Models and Collections.

For example, you can acheive simple url routing in the following manner:
	
	class AppRouter extends Router
	{
		public function __construct()
		{
			parent::__construct();
			
			$this->add(array(
				"/" => "index",
				"/login/" => "login"
				"/user/account/:number/" => "user"
			));

		}
		
		public function index()
		{
			// load homepage view
			$this->view->load("homepage");
		}
		
		public function login()
		{
			// app specific logic...
		}
		
		public function user($id)
		{
			// load user with ID $id...
		}
	};
	
	Backbone::addRouter(new AppRouter());
		

File Structure
--------------

Backbone.php consists of the core framework code in a `/backbone/` directory and five files that must be placed in your application's web root: 'htacces', 'backbone.php', 'config.php', 'boot.php', and 'index.php'

* `htaccess` Uses Apache's mod_rewrite module to funnel all page requests to the index.php script, which allows for url fragments to be routed to mapped view code.

* `backbone.php` Initializes the Backbone.php framework and sets up the request object. This file should not be modified.

* `config.php` Sets up several directory constants required by Backbone.php to find the core framework, application, and view directories. By modifiying the FRAMEWORK constant you can place the core /backbone/ directory elsewhere than your web root.

* `boot.php` Sets the web root relative path, loads routers, and performs other application specific bootstraping.

* `index.php` Dispatches requests through routers registered by the application. Should not be modified.

Modules
-------

Backbone.php modules are classes. A module is referenced by its classname with a filename formatted as ClassName.class.php. Modules are loaded using the `Backbone::uses()` method. To load a Backbone .phpmodule, just pass in the module name. To load a module within your web root directory, pass in the relative path beginning with a forward slash '/'. If the name does not begin with a forward slash, then Backbone.php will assume that the path is relative to the framework's directory.

	// load a backbone module
	Backbone::uses("Model");
	
	// load multiple backbone modules
	Backbone::uses(array("Model", "View", "Collection"));
	
	// load a backbone plugin
	Backbone::uses("plugins/PluginModule");
	
	// load an application specific model located at ./models/Person.class.php
	Backbone::uses("/models/Person");

Modules are only loaded once and subsequent calls to `Backbone::uses()` will be ingored if the module was previously loaded.

Backbone.php Framework
----------------------

The Backbone.php framework consists of the following 21 modules:

* Backbone

* BackboneTest

* Collection

* Connections

* DataSet

* DataSource

* DataType

* Events

* Html

* JSON

* Model

* MySQL

* MySQLResult

* Request

* Router

* Sanitize

* Schema

* SchemaRules

* Session

* TestSuite

* View