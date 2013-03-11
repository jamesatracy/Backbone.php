[Backbone.php Homepage](https://github.com/jamesatracy/Backbone.php) | [Table of Contents](toc.md)

## BackboneTest.class

BackboneTest is part of the unit testing framework for Backbone.php. It loads and runs unit test classes derived from `TestSuite` and outputs the results.

### $command_line `BackboneTest::$command_line`

Whether or not to run on the command line. Boolean defaults to false.

### $output_file `BackboneTest::$output_file`

Optional file to output the results to.

### $total_passed `BackboneTest::$total_passed`

The total number of passed tests is stored in this static variable after a test run.

### $total_failed `BackboneTest::$total_failed`

The total number of failed tests is stored in this static variable after a test run.

### base `BackboneTest::load($classname)`

Load a unit testing suite. `$classname` must be a valid module path.

### enumerate `BackboneTest::enumerate()`

Enumerate all of the loaded test suites. Returns and array of test suite data in the form: {"id", "name", "classname", "count"}.

### describe `BackboneTest::describe($classname, $name)`

Describe a test suite. 	A new instance of the test suite class is created per describe statement.

### run `BackboneTest::run($classname = null, $id = null)`

Run the series of test suites, or a single test suite specified by its classname or ID (the ID is available through the `enumerate` method).