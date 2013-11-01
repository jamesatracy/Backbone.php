<?php
// BlogRouter.class.php

use Backbone\Router as Router;
use Backbone\Request as Request;
use Backbone\Events as Events;
use Backbone\Collection as Collection;
use Backbone\Validate as Validate;

class BlogRouter extends Router
{
	public function __construct()
	{
		parent::__construct();
		
		$this->get("/", "index");
		$this->get("/create/", "create");
		$this->post("/create/", "createSubmit");
		
		// handle invalid urls (404 errors)
		Events::bind("Response:404:before", array($this, "error404"));
	}
	
	/**
	 * Home page implementation.
	 * Maps to: /
	 */
	public function index()
	{
		Backbone::uses("Collection");
		$posts = new Collection(DATABASE_NAME.".posts", array("model" => "/models/Post"));
		$posts->fetch(array("order_by" => array("post_created", "DESC"), "limit" => "10"));
		$this->view->set("title", "Blog Example");
		$this->view->set("posts", $posts);
		$this->view->load("home");
	}
	
	/*
	 * Create page.
	 * Maps to: GET /create/
	 */
	public function create()
	{
		$this->view->set("title", "Create Blog Post");
		$this->view->load("create-post");
	}
	
	/*
	 * Create post page impementation.
	 * Maps to: POST /create/
	 */
	public function createSubmit()
	{
		if(Request::post()) {
			if(Request::post("cancel")) {
				// cancelled, redirect back to home page
				$this->response->header("Location", Request::link("/"));
			} else {
				// do the submit
				Backbone::uses("Validate");
				$errors = array();
				if(!Validate::required("post_title", Request::post("post_title")))
					$errors[] = "*** Post Title is a Required Field.";
				if(!Validate::required("post_author", Request::post("post_author")))
					$errors[] = "*** Post Author is a Required Field.";
				if(!Validate::required("post_body", Request::post("post_body")))
					$errors[] = "*** Post Body is a Required Field.";
				if(!empty($errors)) {
					$this->view->set("errors", join("<br/>", $errors));
				} else {
					// save the post
					Backbone::uses("/models/Post");
					$post = new Post();
					$post->set(Request::post());
					$post->save();
					// after successful submit, redirect to home page
					$this->response->redirect(Request::link("/"));
					return;
				}
			}
		}
		
		$this->create();
	}
	
	public function error404($response)
	{
		$response->body("Invalid URL (HTTP 404): ".Request::here());
	}
}