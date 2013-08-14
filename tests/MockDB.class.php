<?php

Backbone::uses("DataSource");

class MockDB extends DataSource
{
	public function isConnected()
	{
		return true;
	}
}
?>