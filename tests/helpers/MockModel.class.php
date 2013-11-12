<?php
/**
 * A mock model class for testing purposes.
 */
class MockModel extends Backbone\Model
{
    public static $table = "mock";
    public static $created = "created";
    public static $schemaFile = "tests/fixtures/model_test_fixture.json";
}
?>