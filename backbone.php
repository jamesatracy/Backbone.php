<?php
/*
backbone.php
Copyright (C) 2012 James Tracy

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/

// root directory path constant
define('ABSPATH', dirname(__FILE__).'/');

// requires
require_once(ABSPATH."config.php");

// sanity checks
if(!defined('FRAMEWORK'))
{
	echo "Backbone.php configuration error: FRAMEWORK not defined.";
	exit(1);
}
if(!defined('APPPATH'))
{
	echo "Backbone.php configuration error: APPPATH not defined.";
	exit(1);
}
if(!defined('VIEWPATH'))
{
	echo "Backbone.php configuration error: VIEWPATH not defined.";
	exit(1);
}

require_once(FRAMEWORK."Backbone.class.php");
Backbone::initialize();

// create request object
Backbone::$request = new Request();
?>