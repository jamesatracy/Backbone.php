[Backbone.php Homepage](https://github.com/jamesatracy/Backbone.php) | [Table of Contents](toc.md)

## View.class

The base class for all Backbone.php views. An instance of View is automatically created when a url fragment is routed to a [Router](router_class.md) or to a view PHP script. From within the Router, it is accessible via `$this->view` and from within the view script via `$this`.

### load `$view->load($name)`

Load a view to execute from the application's `/views/` directory. Examples:
	
	// To load /views/about.php:	
	$view->load("about");
	
	// To load /views/products/hammer.php
	$view->load("products/hammer");
	
### display `$view->display($name)`

Display a view file directly from the application's `/views/` directory. This is equivalent to inline requiring the script. Examples:

	// To include /views/about.php:	
	$view->display("about");
	
	// To include /views/products/hammer.php
	$view->display("products/hammer");
	
### extend `$view->extend($name)`

Extend the current view from another view. This defines the current view as a child of another view. The parent view will be executed after the child, allowing the child to define (using the `define` method) blocks for the parent to render. NOTE: Child views are executed in the reverse order in which they are extended.

Example:

	View #1
	<?php
	// view1.php
	?>
	<html>
	<body>
	<?php $this-render("content"); ?>
	</body>
	</html>
	
	View #2
	<?php
	// view2.php
	$this->extend("view1");
	$this->define("content");
	?>
	<h1>Hello World</h2>
	<?php
	$this->end();
	?>
	
In the above example, `<h1>Hello World</h1>` is rendered in View #1's body where the call to `$this-render("content")` is placed.

### set `$view->set($key, $value = null)`

Set a property's value. Examples:

	$view->set("title", "The Title");
	$view->set(array("title" => "The Title", "posts" => $posts);
	
### get `$view->get($key)`

Get a property's value.

### push `$view->push($key, $value)`

Push a value onto a user defined stack property. If it doesn't exist, then it will be created.

### pop `$view->pop($key)`

Pop a value off of a user defined stack property.

### define `$view->define($key)`

Define a HTML block. This will overwrite any existing block with the same name.

### isDefine `$view->isDefined($key)`

Check whether or not an HTML block is defined.

### append `$view->append($key, $html = null)`

Append to an existing HTML block. This will create a new block if it was not defiend by define().

### prepend `$view->prepend($key, $html = null)`

Prepend to an existing HTML block. This will create a new block if it was not defiend by define().

### end `$view->end()`

End a HTML block. Must be preceded by a define() or append() call.

### clear `$view->clear($key)`

Clear a block's (defined with `define`, `append`, or `prepend`) value.

### render `$view->render($key)`

Render a block of HTML on the page. Does not render anything if the block was not previously defined.