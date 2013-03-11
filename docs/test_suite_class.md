[Backbone.php Homepage](https://github.com/jamesatracy/Backbone.php) | [Table of Contents](toc.md)

Provides a base class for all unit test suites.

Override the `getTests` method to define your unit tests:

	public static function getTests()
	{
		return array(
			"testOne" => "Test number one",
			"testTwo" => "Test number two"
		);
	}
	
The keys correspond to the (non-static) class methods that implement the actual tests and the values correspond to the labels or descriptions that will be printed before each test is run.

`TestSuite` provides `startUp` and `tearDown` stubs that can be optionally overriden in your base classes. `startUp` is called before each test (as defined by `getTests`) and `tearDown` is called after each test completes.