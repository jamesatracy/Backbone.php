<?php
// Post.class.php
Backbone::uses("Model");

class Post extends Model
{
	public static $table = "posts";
	public static $created = "post_created";
}
?>