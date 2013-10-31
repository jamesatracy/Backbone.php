<?php
// Post.class.php

Backbone::uses("Model");

use Backbone\Model as Model;

class Post extends Model
{
	public $created = "post_created";
	
	public function __construct()
	{
		parent::__construct(DATABASE_NAME.".posts");
	}
}
?>