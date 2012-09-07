[Backbone.php Homepage](https://github.com/jamesatracy/Backbone.php) * [Table of Contents](toc.md)

## Router.class

Routers map url fragments (such as "/about/") to views and/or callback methods. 

You can explicitly map url fragments (always relative to your web root) to methods in your Router child class to perform application specific logic, or you can have Backbone.php route your url fragments to view files automatically. 

Backbone.php will try and do the latter only if no callback mappings are defined by looking for a corresponding view file in your `./views/` directory. These views must end in `-page`. So, for example, if you have a view file named `about-page.php` then requests for `/about/` will map directly to that view file. Requests with sub-folders will only map to views in corresponding sub-folders in your views directory. For example, `/about/contact/` will map to `/about/contact-page.php` if it is defined.

Argument can be passed through the url using the special strings `:alpha`, `alphanum`, and `:number`, depending on the type of argument to match (alphabetical, alpha-numeric, and numeric). These arguments will be passed in as arguments to your router's callback method. For example, if you have the url fragment `/user/account/1234/` then you can define a route `/user/account/:number/` to capture the user ID.

Example Router:

	class AppRouter extends Router
	{
		public function __construct()
		{
			parent::__construct();
			
			$this->add(array(
				"/" => "index",
				"/login/" => "login"
				"/user/account/:number/" => "user"
			));

		}
		
		public function index()
		{
			// load homepage view
			$this->view->load("homepage");
		}
		
		public function login()
		{
			// app specific logic...
		}
		
		public function user($id)
		{
			// load user with ID $id...
		}
	};
	
	Backbone::addRouter(new AppRouter());