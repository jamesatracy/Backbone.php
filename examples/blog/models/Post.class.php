<?php
// Post.class.php

Backbone::uses("Model");

class Post extends Model
{
	public $created = "post_created";
	
	public function __construct()
	{
		parent::__construct("blog.posts");
	}
}
?>