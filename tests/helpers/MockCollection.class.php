<?php
/**
 * A mock collection class for testing purposes.
 */
class MockCollection extends Backbone\Collection
{
	public function __construct()
	{
		parent::__construct("MockModel",
		    json_decode(file_get_contents(ABSPATH."tests/fixtures/collection_test_fixture.json"), TRUE)
		);
	}
}
?>