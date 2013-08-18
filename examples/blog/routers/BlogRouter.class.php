<?php
// BlogRouter.class.php

class BlogRouter extends Router
{
	public function __construct()
	{
		parent::__construct();
		
		$this->add(array(
			"/" => "index",
			"/create/" => "create"
		));
		
		// handle invalid urls (404 errors)
		Events::bind("response.404", array($this, "error404"));
	}
	
	/**
	 * Home page implementation.
	 * Maps to: /
	 */
	public function index()
	{
		Backbone::uses("Collection");
		$posts = new Collection(DATABASE_NAME.".posts", array("model" => "Post"));
		$posts->fetch(array("order_by" => array("post_created", "DESC"), "limit" => "10"));
		$this->view->set("title", "Blog Example");
		$this->view->set("posts", $posts);
		$this->view->load("home");
	}
	
	/*
	 * Create post page impementation.
	 * Maps to: /create/
	 */
	public function create()
	{
		if(Backbone::$request->post()) {
			if(Backbone::$request->post("cancel")) {
				// cancelled, redirect back to home page
				$this->response->header("Location", Backbone::$request->link("/"));
			} else {
				// do the submit
				$errors = array();
				if(!Backbone::$request->post("post_title"))
					$errors[] = "*** Post Title is a Required Field.";
				if(!Backbone::$request->post("post_author"))
					$errors[] = "*** Post Author is a Required Field.";
				if(!Backbone::$request->post("post_body"))
					$errors[] = "*** Post Body is a Required Field.";
				if(!empty($errors)) {
					$this->view->set("errors", join("<br/>", $errors));
				} else {
					// save the post
					Backbone::uses("/models/Post");
					$post = new Post();
					$post->set(Backbone::$request->post());
					$post->save();
					// after successful submit, redirect to home page
					$this->response->header("Location", Backbone::$request->link("/"));
					return;
				}
			}
		}
		
		$this->view->set("title", "Create Blog Post");
		$this->view->load("create-post");
	}
	
	public function error404()
	{
		echo "Invalid URL (HTTP 404): ".Backbone::$request->here();
	}
}

Backbone::addRouter(new BlogRouter());