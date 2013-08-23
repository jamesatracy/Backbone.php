Backbone.php
============

[![Build Status](https://travis-ci.org/jamesatracy/Backbone.php.png?branch=master)](https://travis-ci.org/jamesatracy/Backbone.php?branch=master)

* [Download Latest](https://github.com/jamesatracy/Backbone.php/archive/master.zip)
* [Download 0.2.1](https://github.com/jamesatracy/Backbone.php/releases/tag/0.2.1)

Backbone.php, much like its javascript namesake, is a small collection of PHP classes that provide structure or scaffolding for a PHP application or PHP powered website. It follows the Model-View-Controller (MVC) convention and includes classes for handling routes, database backed models, and HTML views. Applications or websites built using Backbone.php can be up and running very quickly because it removes the necessity of writing much boilerplate code.

**NOTE: This project is pre-1.0 and the code may change in significant and backward incompatible ways between releases.**

The framework is built with the following goals in mind:

* *Lightweight*: The core of Backbone.php provides just the right amount of structure and flexibility without being overly complicated.

* *Modular*: Backbone.php is highly modular and provides a simple mechanism for including other framework and application specific modules.

* *Best Practices*: Backbone.php is designed with software engineering best practices in mind, including MVC, Object-Oriented programming, and unit testing.

At its most simplest form, a Backbone.php application is nothing more than a series of url routes (such as "/about/") that are mapped to either views ("views/about-page.php") or callback methods ("public function about($args)") or both. That is essentially all that you need to get a Backbone.php application up and running. However, the framework also provides a number of classes for working specifically with data backed by a MySQL database in the form of Models and Collections.

Classes
-------

The Backbone.php framework consists of the following 16 modules: 

`Backbone`, `Collection`, `Connections`, `DataMap`, `DataSource`, `Events`, `Html`, `Model`, `MySQL`, `Response`, `Request`, `Router`, `Sanitize`, `Schema`, `Validate`, and `View`

Dependencies
------------

Requires PHP version 5.3+. 
		
Documentation
-------------

For all of the documentation, please go to the [documentation table of contents](https://github.com/jamesatracy/Backbone.php/blob/master/docs/toc.md)

Version History
---------------

*0.2.1*

* Fixed issue #11
* Fixed issue #12

*0.2.0*

This release contains significant and fundamental changes across the board:

* Added module: Response.
* Renamed module DataSet to DataMap.
* Renamed module SchemaRules to Validate.
* Removed modules: BackboneTest, DataType, MySQLResult, TestSuite, Session, and JSON.
* Fleshed out abstract DataSource class, the glue between models and data sources.
* Updated MySQL to follow new DataSource interfaces. CRUD methods return actual data. MySQLResult is gone.
* Backbone now triggers a "response.404" event instead of "request.invalid-url"
* Backbone sends a HTTP 500 error if there is an uncaught exception in the route.
* Updated and improved the blog example application.
* Converted unit tests to phpunit.

*0.1.1*
* Added methods method() and getData() to Request.
* Bug fixes.
