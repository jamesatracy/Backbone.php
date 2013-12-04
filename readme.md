Backbone.php
============

[![Build Status](https://travis-ci.org/jamesatracy/Backbone.php.png?branch=master)](https://travis-ci.org/jamesatracy/Backbone.php?branch=master)

* [Download Latest](https://github.com/jamesatracy/Backbone.php/archive/master.zip)
* [Download 0.3.0](https://github.com/jamesatracy/Backbone.php/releases/tag/0.3.0)

Backbone.php, much like its javascript namesake, is a small collection of PHP classes that provide structure or scaffolding for a PHP application or PHP powered website. It follows the Model-View-Controller (MVC) convention and includes classes for handling routes, database backed models, and HTML views. Applications or websites built using Backbone.php can be up and running very quickly because it removes the necessity of writing much boilerplate code.

**NOTE: This project is pre-1.0 and the code may change in significant and backward incompatible ways between releases.**

The framework is built with the following goals in mind:

* *Lightweight*: The core of Backbone.php provides just the right amount of structure and flexibility without being overly complicated.

* *Modular*: Backbone.php is highly modular and provides a simple mechanism for including other framework and application specific modules.

* *Best Practices*: Backbone.php is designed with software engineering best practices in mind, including MVC, Object-Oriented programming, and unit testing.

At its most simplest form, a Backbone.php application is nothing more than a series of url routes (such as "/about/") that are mapped to either views ("views/about-page.php") or callback methods ("public function about($request)") or combinations of both. 

	// get route mapped to a controller method with a url alias
	Router::get("/", "/controllers/MyController@index")->alias("home");
	// get route mapped to a view
	Router::get("/about/", "View@about");
	// get route with argument parameters, mapped to a controller method
	Router::get("/jobs/:department/:id/", "/controllers/MyController@jobDesc");
	// post route mapped to a controller method
	Router::post("/contact/", "/controllers/MyController@processContactForm");
	
	class MyController
	{
		public function index($request)
		{
		}
		
		public function jobDesc($request, $dept, $id)
		{
		}
		
		...
	}
	

That is essentially all that you need to get a Backbone.php application up and running. However, the framework also provides a number of classes for working specifically with data backed by a SQL database in the form of Models and Collections.

	$dog = Dog::create({
		"name" => "Spot",
		"breed" => "Collie"
	});
	$id = $dog->id;
	
	$dog = Dog::fetch(1); // with id = 1
	$dog->name = "Spot";
	$dog->save();
	
	$dogs = Dog::fetch()->where("breed", "Collie")->limit(10)->exec();
	while($dog->valid()) {
		$dog = $dog->next();
		...
	}

Classes
-------

The Backbone.php framework consists of the following 14 modules: 

`Backbone`, `Collection`, `DB`, `Events`, `Html`, `Model`, `Query`, `Response`, `Request`, `Router`, `Sanitize`, `Schema`, `Validate`, and `View`

Dependencies
------------

Requires PHP version 5.3+. 
		
Documentation
-------------

For all of the documentation, please go to the [documentation table of contents](https://github.com/jamesatracy/Backbone.php/blob/master/docs/toc.md)

See api documentation in /docs/api/index.html

Version History
---------------

Please see [version history](https://github.com/jamesatracy/Backbone.php/blob/master/versions.md).
