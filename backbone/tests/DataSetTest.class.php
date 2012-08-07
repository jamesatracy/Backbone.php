<?php

Backbone::uses(array("TestSuite", "DataSet"));

BackboneTest::describe(
	// class name
	"DatatSetTest", 
	// title
	"DataSet class test suite"
);

class DatatSetTest extends TestSuite
{
	public static function getTests()
	{
		return array(
			"testSimple" => "Test simple set() and get() functions",
			"testNested" => "Test nested set() and get() functions"
		);
	}
	
	public function testSimple()
	{
		$set = new DataSet();
		
		// set a simple value
		$set->set("foo", "one");
		$this->is(
			$this->isEqual($set->get("foo"), "one")
		);
		// change a simple value
		$set->set("foo", "two");
		$this->is(
			$this->isEqual($set->get("foo"), "two")
		);
		// unset the same value
		$set->set("foo", null);
		$this->is(
			$this->isNull($set->get("foo"))
		);
	}
	
	public function testNested()
	{
		$set = new DataSet();
		
		// set a nested value
		$set->set("foo.homer", "simpson");
		$this->is(
			$this->isEqual($set->get("foo.homer"), "simpson")
		);
		$this->is(
			$this->isArray($set->get("foo"))
		);
		$this->is(
			$this->isEqual($set->get("foo"), array("homer" => "simpson"))
		);
		// change the nested value
		$set->set("foo.homer", "beer");
		$this->is(
			$this->isEqual($set->get("foo.homer"), "beer")
		);
		$this->is(
			$this->isArray($set->get("foo"))
		);
		$this->is(
			$this->isEqual($set->get("foo"), array("homer" => "beer"))
		);
		// unset the nested value
		$set->set("foo.homer", null);
		$this->is(
			$this->isNull($set->get("foo.homer"))
		);
		// test three levels
		$set->set("one.two.three", "four");
		$this->is(
			$this->isEqual($set->get("one.two.three"), "four")
		);
	}
};

?>