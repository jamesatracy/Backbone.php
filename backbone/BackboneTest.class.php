<?php
/*
BackboneTest.class.php
Copyright (C) 2012 James Tracy

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/

/*
BackboneTest is the unit testing framework for Backbone.php
*/

class BackboneTest
{
	/* Array of test suites */
	public static $suites = array();
	
	/* Current suite */
	protected static $current = null;
	
	/*
	Load a test suite
	
	@param [string] $classname The classname of the test suite to load
	*/
	public static function load($classname)
	{
		Backbone::uses("/".$classname);
	}
	
	/* 
	Enumerate all of the loaded test suites
	
	@return [array] An array of test suites
	*/
	public static function enumerate()
	{
		$enumerate = array();
		$suites = self::$suites;
		
		// loop over suites
		foreach($suites as $object => $list)
		{
			$index = 0;
			foreach($list as $suite)
			{
				$name = $suite['name'];
				//$tests = $suite['tests'];
				$tests = $object::getTests();
				$count = count($tests);
				$enumerate[] = array("id" => $index, "name" => $name, "classname" => $object, "count" => $count);
				$index++;
			}
		}
		
		return $enumerate;
	}
	
	/*
	Describe a test suite
	A new instance of the test suite class is created per describe statement.
	
	@param [string] $name The name or title of the test suite
	@param [string] $classname The classname of the test suite
		Should be a valid argument for Backbone::use()
	*/
	public static function describe($classname, $name)
	{	
		$current = array("name" => $name);
		if(!isset(self::$suites[$classname]))
			self::$suites[$classname] = array();
		self::$suites[$classname][] = &$current;
	}
	
	/*
	Run the series of test suites
	
	@param [string] $classname Optional classname of the test suite to run. Otherwise run all.
	@param [integer] $id Optional id of a test stuite, from enumerate(), to run. Otherwise run all.
	*/
	public static function run($classname = null, $id = null)
	{
		$suites = self::$suites;
		
		if($classname == null)
		{
			// loop over suites
			foreach($suites as $object => $list)
			{
				foreach($list as $suite)
				{
					self::_runSuite($suite, $object);
				}
			}
			echo "<hr/>";
		}
		else
		{
			if(isset($suites[$classname]))
			{
				if($id == null)
				{
					// run all the tests for this suite
					foreach($suites[$classname] as $suite)
					{
						self::_runSuite($suite, $classname);
					}
				}
				else
				{
					// run one of the tests for this suite
					self::_runSuite($suites[$classname][$id], $classname);
				}
				echo "<hr/>";
			}
		}
	}
	
	/*
	Internal function called by run()
	
	@param [array] $suite The test suite
	@param [string] $object The object's class name
	*/
	protected static function _runSuite($suite, $object)
	{
		$instance = new $object;
		$name = $suite['name'];
		//$tests = $suite['tests'];
		$tests = $object::getTests();
		$count = count($tests);
		echo "<hr/><h3>".$name." (".$count.")</h3>";

		// loops over specs
		$index = 1;
		foreach($tests as $method => $title)
		{
			if(method_exists($object, $method))
			{
				$instance->reset();
				$instance->startUp();
				call_user_func(array($instance, $method));
				$instance->tearDown();
				$time = time().$index;
				echo '<div>';
				echo '<strong onclick="document.getElementById('.$time.').style.display = document.getElementById('.$time.').style.display == \'none\' ? \'block\' : \'none\'" style="cursor:pointer">('.$index.') '.$title.'</strong> ['.$instance->count.' tests. <span style="color:green">'.$instance->passed.' passed</span>. <span style="color:red;'.($instance->failed > 0 ? 'font-weight:bold;': '').'">'.$instance->failed.' failed</span>].';
				//echo '&nbsp;<a href="'.Backbone::$request->base().Backbone::$request->path()."?name=".$suite['classname']."&id=".$suite['id']."&test=".urlencode($method).'">Run Again</a>';
				echo '</div>';
				echo '<div id="'.$time.'" class="suite" style="display:'.($instance->failed > 0 ? 'block' : 'none').'">';
				// test output
				echo join("", $instance->output);
				echo '</div>';
			}
			$index++;
			echo "<br/>";
		}
		//echo "<br/>";
	}
};

?>