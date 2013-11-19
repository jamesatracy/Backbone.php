<?php
/**
 * Perform all of your bootstrap operations here in this file.
 * That includes setting up routes and other resources (database connections, etc.).
 */

 // Include modules
Backbone::uses("DB");

// Routes
Router::get("/", "/controllers/BlogController@index");
Router::get("/create/", "/controllers/BlogController@create");
Router::post("/create/", "/controllers/BlogController@createSubmit");

// Database
DB::connect("mysql:dbname=".DATABASE_NAME.";host=".DB_SERVER, DB_USER, DB_PASS);

// handle invalid urls (404 errors)
function error404($response)
{
	$response->body("Invalid URL (HTTP 404): ".Backbone::$request->getPath());
}
Events::bind("response.404", "error404");

// handle application errors (500 errors)
function error500($response)
{
	$response->body("Internal Server Error (HTTP 500): ".Backbone::$request->getPath());
}
Events::bind("response.500", "error500");
?>
