<?php
/**
 * A mock collection class for testing purposes.
 */
class MockCollection extends Backbone\Collection
{
	public function __construct()
	{
		parent::__construct("table", array(
		    "model" => "/tests/helpers/MockModel",
		    "classname" => "MockModel"
		));
	}
}
?>