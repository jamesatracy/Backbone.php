<?php
// BlogController.class.php

Backbone::uses("View");

class BlogController
{	
	/**
	 * Home page implementation.
	 * Maps to: /
	 */
	public function index($request)
	{
		Backbone::uses("/models/Post");
		$posts = Post::fetch()->limit(10)->orderBy("post_created DESC")->exec();
		return View::create($request, 'home', array(
			'title' => 'Blog Example',
			'posts' => $posts
		));
	}
	
	/*
	 * Create page.
	 * Maps to: GET /create/
	 */
	public function create($request)
	{
		return View::create($request, "create-post");
	}
	
	/*
	 * Create post page impementation.
	 * Maps to: POST /create/
	 */
	public function createSubmit($request)
	{
		$view = new View($request, "create-post");
		
		if($request->request) {
			if($request->getRequest("cancel")) {
				// cancelled, redirect back to home page
				return Response::create()->redirect($request->getBaseURL());
			} else {
				// do the submit
				Backbone::uses("Validate");
				$errors = array();
				if(!Validate::required("post_title", $request->getRequest("post_title")))
					$errors[] = "*** Post Title is a Required Field.";
				if(!Validate::required("post_author", $request->getRequest("post_author")))
					$errors[] = "*** Post Author is a Required Field.";
				if(!Validate::required("post_body", $request->getRequest("post_body")))
					$errors[] = "*** Post Body is a Required Field.";
				if(!empty($errors)) {
					$view->set("errors", join("<br/>", $errors));
				} else {
					// save the post
					Backbone::uses("/models/Post");
					Post::create($request->request);
					// after successful submit, redirect to home page
					return Response::create()->redirect($request->getBaseURL());
				}
			}
		}
		
		return $view->load();
	}
}