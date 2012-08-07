<?php

Backbone::uses(array("TestSuite", "DataType"));

BackboneTest::describe(
	// class name
	"DataTypeTest", 
	// title
	"DataType class test suite"
);

class DataTypeTest extends TestSuite
{
	public static function getTests()
	{
		return array(
			"testExport" => "Test export()",
			"testType" => "Test type()"
		);
	}
	
	public function testExport()
	{
		// booleans
		$this->is(
			$this->isEqual(DataType::export(true), "true")
		);
		$this->is(
			$this->isEqual(DataType::export(false), "false")
		);
		// integer
		$this->is(
			$this->isEqual(DataType::export(1), "(integer) 1")
		);
		// float
		$this->is(
			$this->isEqual(DataType::export(1.5), "(float) 1.5")
		);
		// string
		$this->is(
			$this->isEqual(DataType::export("this is a string"), "(string) 'this is a string'")
		);
		// null
		$this->is(
			$this->isEqual(DataType::export(null), "(null)")
		);
		// array
		$this->is(
			$this->isEqual(DataType::export(array()), "(array)")
		);
		// object
		$this->is(
			$this->isEqual(DataType::export(new stdclass()), "(object) stdClass")
		);
	}
	
	public function testType()
	{
		// booleans
		$this->is(
			$this->isEqual(DataType::type(true), "boolean")
		);
		$this->is(
			$this->isEqual(DataType::type(false), "boolean")
		);
		// integer
		$this->is(
			$this->isEqual(DataType::type(1), "integer")
		);
		// float
		$this->is(
			$this->isEqual(DataType::type(1.5), "float")
		);
		// string
		$this->is(
			$this->isEqual(DataType::type("this is a string"), "string")
		);
		// null
		$this->is(
			$this->isEqual(DataType::type(null), "null")
		);
		// array
		$this->is(
			$this->isEqual(DataType::type(array()), "array")
		);
		// object
		$this->is(
			$this->isEqual(DataType::type(new stdclass()), "stdClass")
		);
	}
};

?>