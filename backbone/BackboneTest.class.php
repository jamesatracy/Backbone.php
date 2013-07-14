<?php
/**
 * Backbone.php
 * 
 * @author	James Tracy <james.a.tracy@gmail.com>
 * @copyright	2012-2013
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link https://github.com/jamesatracy/Backbone.php GitHub Page
 */


/**
 * BackboneTest is the unit testing framework for Backbone.php
 * 
 * @since 0.1.0
 */
class BackboneTest
{
	/** @var array Array of test suites */
	public static $suites = array();
	
	/** @var mixed Current suite */
	protected static $current = null;
	
	/** @var bool Whether or not to run on the command line */
	public static $command_line = false;
	
	/** @var string Optional file to output the results to */
	public static $output_file = false;
	
	/** @var int Total tests passed */
	public static $total_passed = 0;
	
	/** @var int Total tests failed */
	public static $total_failed = 0;
	
	/**
	 * Load a test suite
	 * 
	 * @since 0.1.0
	 * @param string $classname The classname of the test suite to load
	 */
	public static function load($classname)
	{
		Backbone::uses("/".$classname);
	}
	
	/**
	 * Enumerate all of the loaded test suites
	 * 
	 * @since 0.1.0
	 * @return array An array of test suites
	 */
	public static function enumerate()
	{
		$enumerate = array();
		$suites = self::$suites;
		
		// loop over suites
		foreach($suites as $object => $list) {
			$index = 0;
			foreach($list as $suite) {
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
	
	/**
	 * Describe a test suite.
	 * 
	 * A new instance of the test suite class is created per describe statement.
	 * 
	 * @since 0.1.0
	 * @param string $name The name or title of the test suite
	 * @param string $classname The classname of the test suite
	 * 	Should be a valid argument for Backbone::use()
	 */
	public static function describe($classname, $name)
	{	
		$current = array("name" => $name);
		if(!isset(self::$suites[$classname])) {
			self::$suites[$classname] = array();
		}
		self::$suites[$classname][] = &$current;
	}
	
	/**
	 * Run the series of test suites
	 * 
	 * @since 0.1.0
	 * @param [string] $classname Optional classname of the test suite to run. Otherwise run all.
	 * @param [integer] $id Optional id of a test stuite, from enumerate(), to run. Otherwise run all.
	 */
	public static function run($classname = null, $id = null)
	{
		self::$total_passed = 0;
		self::$total_failed = 0;
		
		$suites = self::$suites;
		
		if(self::$output_file) {
			file_put_contents(self::$output_file, "");
		}
		
		if($classname == null) {
			// loop over suites
			foreach($suites as $object => $list) {
				foreach($list as $suite) {
					self::_runSuite($suite, $object);
				}
			}
			
			if(self::$command_line) {
				self::startOutput();
				echo "-----------------------------------------------------".PHP_EOL;
				self::endOutput();
			} else {
				echo "<hr/>";
			}
		} else {
			if(isset($suites[$classname])) {
				if($id == null) {
					// run all the tests for this suite
					foreach($suites[$classname] as $suite) {
						self::_runSuite($suite, $classname);
					}
				} else {
					// run one of the tests for this suite
					self::_runSuite($suites[$classname][$id], $classname);
				}
				if(self::$command_line) {
					self::startOutput();
					echo "-----------------------------------------------------".PHP_EOL;
					self::endOutput();
				} else {
					echo "<hr/>";
				}
			}
		}
		if(self::$command_line) {
			self::startOutput();
			echo "TOTAL PASSED: ".number_format(self::$total_passed).", TOTAL FAILED: ".number_format(self::$total_failed).PHP_EOL;
			self::endOutput();
		}
	}
	
	/**
	 * Internal function called by run()
	 * 
	 * @internal
	 * @since 0.1.0
	 * @param array $suite The test suite
	 * @param string $object The object's class name
	 */
	protected static function _runSuite($suite, $object)
	{
		$instance = new $object;
		$name = $suite['name'];
		//$tests = $suite['tests'];
		$tests = $object::getTests();
		$count = count($tests);
		
		if(self::$command_line) {
			self::startOutput();
			echo "-----------------------------------------------------".PHP_EOL.$name." (".$count.")\n";
			self::endOutput();
		} else {
			echo "<hr/><h3>".$name." (".$count.")</h3>";
		}

		// loops over specs
		$index = 1;
		foreach($tests as $method => $title) {
			if(method_exists($object, $method)) {
				$instance->command_line = self::$command_line;
				$instance->reset();
				$instance->startUp();
				call_user_func(array($instance, $method));
				$instance->tearDown();
				$time = time().$index;
				if(self::$command_line) {
					self::startOutput();
					echo '('.$index.') '.$title.' ['.$instance->count.' tests. '.$instance->passed.' passed. '.$instance->failed.' failed].'.PHP_EOL;
					if($instance->failed > 0)
						echo join("", $instance->error_output);
					self::endOutput();
				} else 	{
					echo '<div>';
					echo '<strong onclick="document.getElementById('.$time.').style.display = document.getElementById('.$time.').style.display == \'none\' ? \'block\' : \'none\'" style="cursor:pointer">('.$index.') '.$title.'</strong> ['.$instance->count.' tests. <span style="color:green">'.$instance->passed.' passed</span>. <span style="color:red;'.($instance->failed > 0 ? 'font-weight:bold;': '').'">'.$instance->failed.' failed</span>].';
					//echo '&nbsp;<a href="'.Backbone::$request->base().Backbone::$request->path()."?name=".$suite['classname']."&id=".$suite['id']."&test=".urlencode($method).'">Run Again</a>';
					echo '</div>';
					echo '<div id="'.$time.'" class="suite" style="display:'.($instance->failed > 0 ? 'block' : 'none').'">';
					// test output
					echo join("", $instance->output);
					echo '</div>';
				}
			}
			$index++;
			self::$total_passed += $instance->passed;
			self::$total_failed += $instance->failed;
			
			if($instance->aborted) {
				if(self::$command_line) {
					self::startOutput();
					echo 'TEST SUITE ABORTED!'.PHP_EOL;
					self::endOutput();
				} else {
					echo '<div><span style="color:red">TEST SUITE ABORTED!</span></div><br/>';
				}
				return; // abort
			}
			
			if(self::$command_line) {
				self::startOutput();
				echo PHP_EOL;
				self::endOutput();
			} else {
				echo "<br/>";
			}
		}
		//echo "<br/>";
	}
	
	/**
	 * Start output buffering
	 * 
	 * @internal
	 * @since 0.1.0
	 */ 
	protected static function startOutput()
	{
		ob_start();
	}
	
	/**
	 * End output buffering
	 * 
	 * @internal
	 * @since 0.1.0
	 */
	protected static function endOutput()
	{
		$output = ob_get_clean();
		if(self::$output_file) {
			file_put_contents(self::$output_file, $output, FILE_APPEND);
		}
		echo $output;
	}
};

?>
