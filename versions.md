Version History
---------------

*0.3.0*
* Backbone classes now use namespace Backbone.
* Consolidated backbone.php into index.php.
* Added pre response sent events to Response ("response.pre.200").
* Response events now pass the response object as the parameter.
* Added Response->isSent() method.

*0.2.4*
* Added Router->stop() method.
* Added Router->routeMatches (protected method).
* Added HTTP Method mapping support in Router for REST implementations.
* Added Response->redirect() method.

*0.2.3*

* Backbone::uses() can accept a comma delimited list of modules.
* Added exists() to View.
* Added url() to View.
* Added createView() to Router so sub classes can override the Router's view object.

*0.2.2*

* Moved all Backbone.php source files to the /src/ directory.
* Fixed issue #13

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
