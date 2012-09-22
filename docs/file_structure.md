[Backbone.php Homepage](https://github.com/jamesatracy/Backbone.php) | [Table of Contents](toc.md)

## File Structure

Backbone.php consists of the core framework code in a `/backbone/` directory and five files that must be placed in your application's web root: 'htacces', 'backbone.php', 'config.php', 'boot.php', and 'index.php'

* `htaccess` Uses Apache's mod_rewrite module to funnel all page requests to the index.php script, which allows for url fragments to be routed to mapped view code.

* `backbone.php` Initializes the Backbone.php framework and sets up the request object. This file should not be modified.

* `config.php` Sets up several directory constants required by Backbone.php to find the core framework, application, and view directories. By modifiying the FRAMEWORK constant you can place the core /backbone/ directory elsewhere than your web root.

* `boot.php` Sets the web root relative path, loads routers, and performs other application specific bootstraping.

* `index.php` Dispatches requests through routers registered by the application. Should not be modified.
