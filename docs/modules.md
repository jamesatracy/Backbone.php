[Backbone.php Homepage](https://github.com/jamesatracy/Backbone.php) * Table of Contents](toc.md)

## Modules

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
