<?php
/*
TestSuite.class.php
Copyright (C) 2012 James Tracy

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/

Backbone::uses(array("DataType", "DataSet"));

/*
@fileoverview
Base class for Test Suites.

@since 0.1.0
*/

class TestSuite
{	
	protected $messages;
	protected $variables;
	
	public $count;
	public $passed;
	public $failed;
	public $aborted;
	public $output = array(); // for buffering output
	public $error_output = array(); // for buffering erros output
	
	public $command_line = false;
	
	public function __construct()
	{
		$this->variables = new DataSet(array());
	}
	
	public function reset()
	{
		$this->count = 0;
		$this->passed = 0;
		$this->failed = 0;
		$this->aborted = false;
		$this->output = array();
		$this->error_output = array();
	}
	
	public function startUp()
	{
		return; // stub function
	}
	
	public function tearDown()
	{
		return; // stub function
	}
	
	/* Abort this suite */
	public function abort()
	{
		$this->aborted = true;
	}
	
	/* Output a caption */
	public function caption($text)
	{
		if($this->command_line)
			$this->output[] = $text.':\n';
		else
			$this->output[] = '<div style="font-style: italic; margin-top:5px;">'.$text.':</div>';
	}
	
	/* Output error messages */
	public function errors($text)
	{
		if($this->command_line)
		{
			if(is_array($text))
				$text = join("\n====> ", $text);
			$this->output[] = '====> '.$text.PHP_EOL;
			$this->error_output[] = '====> '.$text.PHP_EOL;
		}
		else
		{
			if(is_array($text))
				$text = join("<br/>====> ", $text);
			$this->output[] = '<div>====> '.$text.'</div>';
			$this->error_output[] = '<div>====> '.$text.'</div>';
		}
	}
	
	/* Trigger other error (not from a test case) */
	public function triggerError($message)
	{
		$this->count++;
		$this->failed++;
		if($this->command_line)
		{
			$this->output[] = 'Error Triggered: '.$message.PHP_EOL;
			$this->error_output[] = 'Error Triggered: '.$message.PHP_EOL;
		}
		else
		{
			$this->output[] = '<div style="color: red;">Error Triggered: '.$message.'</div>';
			$this->error_output[] = '<div style="color: red;">Error Triggered: '.$message.'</div>';
		}
		$backtrace = debug_backtrace();
		if($this->command_line)
		{
			$this->output[] = '====> '.$backtrace[1]['file'].' : '.$backtrace[1]['line'].PHP_EOL;
			$this->error_output[] = '====> '.$backtrace[1]['file'].' : '.$backtrace[1]['line'].PHP_EOL;
		}
		else
		{
			$this->output[] = '<div>====> '.$backtrace[1]['file'].' : '.$backtrace[1]['line'].'</div>';
			$this->error_output[] = '<div>====> '.$backtrace[1]['file'].' : '.$backtrace[1]['line'].'</div>';
		}
	}
	
	/* Check for a true condition */
	public function is($conditional)
	{
		if($conditional)
		{
			$this->passed($this->message);
			return true;
		}
		$this->failed($this->message);
		return false;
	}
	
	/* Check for a logical not condition */
	public function not($conditional)
	{
		if($conditional)
		{
			$this->failed($this->message);
			return false;
		}
		$this->passed($this->message);
		return true;
	}
	
	/* Strict type comparison checking */
	public function isType($condition, $result)
	{
		if($condition !== $result)
		{
			$this->message = DataType::export($condition)." is not ".DataType::export($result);
			return false;
		}
		else
		{
			$this->message = DataType::export($condition)." is ".DataType::export($result);
			return true;
		}
	}
	
	/* Negate the passed in condition */
	
	/* For boolean false comparison */
	public function isFalse($condition)
	{
		if($condition != false)
		{
			$this->message = DataType::export($condition)." is not false";
			return false;
		}
		else
		{
			$this->message = DataType::export($condition)." is false";
			return true;
		}
	}
	
	/* For boolean true comparison */
	public function isTrue($condition)
	{
		if($condition != true)
		{
			$this->message = DataType::export($condition)." is not true";
			return false;
		}
		else
		{
			$this->message = DataType::export($condition)." is true";
			return true;
		}
	}
	
	/* Null comparison */
	public function isNull($condition)
	{
		if($condition !== null)
		{
			$this->message = DataType::export($condition)." is not null";
			return false;
		}
		else
		{
			$this->message = DataType::export($condition)." is null";
			return true;
		}
	}
	
	/* Integer type comparison */
	public function isInteger($condition)
	{
		if(!is_int($condition))
		{
			$this->message = DataType::export($condition)." is not an integer";
			return false;
		}
		else
		{
			$this->message = DataType::export($condition)." is an integer";
			return true;
		}
	}
	
	/* Boolean type comparison */
	public function isBoolean($condition)
	{
		if(!is_bool($condition))
		{
			$this->message = DataType::export($condition)." is not a boolean";
			return false;
		}
		else
		{
			$this->message = DataType::export($condition)." is a boolean";
			return true;
		}
	}
	
	/* String type comparison */
	public function isString($condition)
	{
		if(!is_string($condition))
		{
			$this->message = DataType::export($condition)." is not a string";
			return false;
		}
		else
		{
			$this->message = DataType::export($condition)." is a string";
			return true;
		}
	}
	
	/* Array type comparison */
	public function isArray($condition)
	{
		if(!is_array($condition))
		{
			$this->message = DataType::export($condition)." is not an array";
			return false;
		}
		else
		{
			$this->message = DataType::export($condition)." is an array";
			return true;
		}
	}
	
	/* Float type comparison */
	public function isFloat($condition)
	{
		if(!is_float($condition))
		{
			$this->message = DataType::export($condition)." is not a float";
			return false;
		}
		else
		{
			$this->message = DataType::export($condition)." is a float";
			return true;
		}
	}
	
	/* Object type comparison */
	public function isObject($condition)
	{
		if(!is_object($condition))
		{
			$this->message = DataType::export($condition)." is not an object";
			return false;
		}
		else
		{
			$this->message = DataType::export($condition)." is an object";
			return false;
		}
	}
	
	/* Empty check */
	public function isEmpty($condition)
	{
		if(!empty($condition))
		{
			$this->message = DataType::export($condition)." is not empty";
			return false;
		}
		else
		{
			$this->message = DataType::export($condition)." is empty";
			return true;
		}
	}
	
	/* For string or number comparison */
	public function isEqual($condition, $result)
	{
		$bool = ($condition == $result);
		if(!$bool)
		{
			$this->message = DataType::export($condition)." != ".DataType::export($result);
			return false;
		}
		else
		{
			$this->message = DataType::export($condition)." == ".DataType::export($result);
			return true;
		}
	}
	
	/* For string or number comparison, uses the === operator insted of == for type comparison */
	public function isReallyEqual($condition, $result)
	{
		$bool = ($condition === $result);
		if(!$bool)
		{
			$this->message = DataType::export($condition)." != ".DataType::export($result);
			return false;
		}
		else
		{
			$this->message = DataType::export($condition)." == ".DataType::export($result);
			return true;
		}
	}
	
	/* For comparing a value with an array of accepted values */
	public function isIn($condition, $array)
	{
		if(!in_array($condition, $array))
		{
			$this->message = DataType::export($condition)." is not in [";
			$tmp = array();
			foreach($array as $value)
				$tmp[] = DataType::export($value);
			$this->message .= join(", ", $tmp)."]";
			return false;
		}
		else
		{
			$this->message = DataType::export($condition)." is in [";
			$tmp = array();
			foreach($array as $value)
				$tmp[] = DataType::export($value);
			$this->message .= join(", ", $tmp)."]";
			return true;			
		}
	}
	
	/* For comparing numbers */
	public function isGreaterThan($condition, $result)
	{
		if($condition <= $result)
		{
			$this->message = DataType::export($condition)." is not > ".$result;
			return false;
		}
		else
		{
			$this->message = DataType::export($condition)." > ".$result;
			return true;
		}
	}
	
	/* For comparing numbers */
	public function isGreaterThanOrEqual($condition, $result)
	{
		if($condition < $result)
		{
			$this->message = DataType::export($condition)." is not >= ".$result;
			return false;
		}
		else
		{
			$this->message = DataType::export($condition)." >= ".$result;
			return true;
		}
	}
	
	/* For comparing numbers */
	public function isLessThan($condition, $result)
	{
		if($condition >= $result)
		{
			$this->message = DataType::export($condition)." is not < ".$result;
			return false;
		}
		else
		{
			$this->message = DataType::export($condition)." < ".$result;
			return true;
		}
	}
	
	/* For comparing numbers */
	public function isLessThanOrEqual($condition, $result)
	{
		if($condition > $result)
		{
			$this->message = DataType::export($condition)." is not <= ".$result;
			return false;
		}
		else
		{
			$this->message = DataType::export($condition)." <= ".$result;
			return true;
		}
	}
	
	protected function pushMessage($message)
	{
		array_push($this->messages, $message);
	}
	
	/* Internal function for formatting passed assertions */
	protected function passed($message)
	{
		$this->count++;
		$this->passed++;
		if($this->command_line)
			$this->output[] = 'Test Passed: '.$message.PHP_EOL;
		else
			$this->output[] = '<div style="color: green;">Test Passed: '.$message.'</div>';
	}
	
	/* Internal function for formattign failed assertions */
	protected function failed($message)
	{
		$this->count++;
		$this->failed++;
		if($this->command_line)
		{
			$this->output[] = 'Assertion Failed: '.$message.PHP_EOL;
			$this->error_output[] = 'Assertion Failed: '.$message.PHP_EOL;
		}
		else
		{
			$this->output[] = '<div style="color: red;">Assertion Failed: '.$message.'</div>';
			$this->error_output[] = '<div style="color: red;">Assertion Failed: '.$message.'</div>';
		}
		$backtrace = debug_backtrace();
		if($this->command_line)
		{
			$this->output[] = '====> '.$backtrace[1]['file'].' : '.$backtrace[1]['line'].PHP_EOL;
			$this->error_output[] = '====> '.$backtrace[1]['file'].' : '.$backtrace[1]['line'].PHP_EOL;
		}
		else
		{
			$this->output[] = '<div>====> '.$backtrace[1]['file'].' : '.$backtrace[1]['line'].'</div>';
			$this->error_output[] = '<div>====> '.$backtrace[1]['file'].' : '.$backtrace[1]['line'].'</div>';
		}
	}
	
	public function set($key, $value)
	{
		$this->variables->set($key, $value);
	}
	
	public function get($key)
	{
		return $this->variables->get($key);
	}
};

?>