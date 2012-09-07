Backbone.php
============

Backbone.php, much like its javascript namesake, is a small collection of php classes that provide structure or scaffolding for a php application or php powered website. It follows the Model-View-Controller (MVC) convention and includes classes for handling routes, database backed models, and HTML views. Applications or websites built using Backbone.php can be up and running very quickly because it removes the necessity of writing much of the boilerplate code.

The framework is built with the following goals in mind:

* *Lightweight*: The core of Backbone.php provides just the right amount of structure and flexibility without being overly complicated.

* *Modular*: Backbone.php is highly modular and provides a simple mechanism for including other framework and application specific modules.

* *Best Practices*: Backbone.php is designed with software engineering best practices in mind, including MVC, Object-Oriented programming, and unit testing.

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
	
The Backbone.php framework consists of the following 21 modules: `Backbone`, `BackboneTest`, `Collection`, `Connections`, `DataSet`, `DataSource`, `DataType`, `Events`, `Html`, `JSON`, `Model`, `MySQL`, `MySQLResult`, `Request`, `Router`, `Sanitize`, `Schema`, `SchemaRules`, `Session`, `TestSuite`, and `View`
		
Documentation
-------------

For all of the documentation, please go to the [documentation table of contents](https://github.com/jamesatracy/Backbone.php/blob/master/docs/toc.md)
