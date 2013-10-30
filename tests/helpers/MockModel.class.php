<?php
/**
 * A mock model class for testing purposes.
 */
class MockModel extends Backbone\Model
{
	public function __construct()
	{
		// automatically stamp created field
		$this->created = "created";
		// load a mock schema for testing
		$this->schemaFile = "tests/fixtures/model_test_fixture.json";
		parent::__construct("table");
	}
}
?>