[Backbone.php Homepage](https://github.com/jamesatracy/Backbone.php) | [Table of Contents](toc.md)

## Session.class

Generic class for Session management. Wraps the $_SESSION super global.

### start `Session::start()`

Starts a session if it has not already been started.

### started `Session::started()`

Check if the session has been started.

### load `Session::load($name)`

Loads a session by the session name.

### clear `Session::clear()`

Clears the $_SESSION data.

### destroy `Session::destroy()`

Destroys the session.

### set `Session::set($name, $value)`

Set a value for the given key. Name can be array namespaced using the dot operator. If the namespace does not exist, it will be initialized as an empty array. For example, `Session::set("user.name", "John")` is equivalent to `$_SESSION['user']['name'] = "John"`.

### get `Session::get($name)`

Get a value for a given variable name. Name can be array namespaced using the dot operator. If the namespace does not exist then `get()` will return `null`.