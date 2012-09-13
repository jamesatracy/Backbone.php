[Backbone.php Homepage](https://github.com/jamesatracy/Backbone.php) | [Table of Contents](toc.md)

## Request.class

RequestPHP provides a convenience object for working with HTTP requests in PHP. It wraps the $_SERVER, $_GET, $_POST, and $_FILES super globals. It is accessed through Backbone::$request and is created automatically when Backbone.php is loaded.

	Backbone::$request;

### Setting the web root

If the web root is not the document root but a subfolder in the document root then you can set this via the static `Backbone::$root` variable, which defaults to "/". For example, if your site's web root exists in `www.example.com/path/to/here/` then you will want to set the `Backbone::$root` to "/path/to/here/". If you do not do that, then the link() and a few other methods will not work properly. 

### base `Backbone::$request->base()`

Get the base url

	// Ex: http://www.example.com/path/to/here/?p=1
	$base = Backbone::$request->base();
	echo $base; // "http://www.example.com"

### url `Backbone::$request->url()`

Get the full url of the request

	// Ex: http://www.example.com/path/to/here/?p=1 
	$url = Backbone::$request->url();
	echo $url; // "http://www.example.com/path/to/here/?p=1"

### path `Backbone::$request->path()`

Get the url path (portion that follows the base, minus query strings)

	// Ex: http://www.example.com/path/to/here/?p=1
	$path = Backbone::$request->path();
	echo $path; // "/path/to/here/"

### here `Backbone::$request->here()`

Get the current path of the request

	// Ex: http://www.example.com/path/to/here/?p=1
	Backbone::$root = "/";
	$here = Backbone::$request->here();
	echo $here; // "/path/to/here/" 
	
	Backbone::$root = "/path/";
	$here = Backbone::$request->here();
	echo $here; // "/to/here/" 

### queryString `Backbone::$request->queryString()`

Get the query string

	// Ex: http://www.example.com/path/to/here/?p=1
	$qs = Backbone::$request->queryString();
	echo $qs; // ?p=1

### query `Backbone::$request->query($arg)`

Get a query string argument, if it exists, from $_SERVER['QUERY_STRING']

	// Ex: http://www.example.com/path/to/here/?p=1&q=2

	echo Backbone::$request->query("p"); // 1

	echo Backbone::$request->query("q"); // 2

	echo Backbone::$request->query("r"); // null

### ipaddress `Backbone::$request->ipaddress()`

Get the IP address of the request

	echo Backbone::$request->ipaddress();

### link `Backbone::$request->link($subpath, $print = false, $ssl = false)`

Builds a link from the base and the given subpath (or relative path)

	// Ex: http://www.example.com/
	echo Backbone::$request->link("/about/team/"); // http://www.example.com/about/team/

	echo Backbone::$request->link("/about/team/", false, true); // https://www.example.com/about/team/

### is `Backbone::$request->is($prop)`

Check for a particular property of the request.
	
These include:

	Backbone::$request->is("get"); // Is a HTTP GET request?

	Backbone::$request->is("post"); // Is a HTTP POST request?

	Backbone::$request->is("put"); // Is a HTTP PUT request?

	Backbone::$request->is("ajax"); // Was the request done through XMLHttpRequest?

	Backbone::$request->is("ssl"); // Is the request over SSL?

### get `Backbone::$request->get($key = null)`

Get a specific GET parameter, or get the entire array of GET parameters. Checks if the key is set via isset().

	Backbone::$request->get();
	Backbone::$request->get('param');

### post `Backbone::$request->post($key = null)`

Get a specific POST parameter, or get the entire array of POST parameters. Checks if the key is set via isset().

	Backbone::$request->post();
	Backbone::$request->post('param');

### files `Backbone::$request->files($key = null)`

Get a specific FILES parameter, or get the entire array of FILES parameters. Checks if the key is set via isset().

	Backbone::$request->files();
	Backbone::$request->files('filename');