[Backbone.php Homepage](https://github.com/jamesatracy/Backbone.php) | [Table of Contents](toc.md)

## Connections.class

Class for managing connections to data sources, like MySQL databases.

### create `Connections::create($name, $type, $config)`

Open a new connection. The name is a unique identifier for the connection while type is a DataSource derived class.

### get `Connections::get($name)`

Get a connection by name.

### remove `Connections::remove($name)`

Remove a connection by name. 