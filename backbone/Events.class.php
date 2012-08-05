<?php
/*
EventManager.class.php
Copyright (C) 2012 James Tracy

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/

/*
Class for triggering and delegating events and callbacks.
*/
class Events
{
	/* List of registered events. Events are registered automatically by calls to bind() */
	protected static $_events = array();
	
	/*
	Bind a callback function to an event
	
	@param [string] $event The event name
	@param [mixed] $callable A valid php callback
	*/
	public static function bind($event, $callable)
	{
		if(!isset(self::$_events[$event]))
			self::$_events[$event] = array();
		self::$_events[$event][] = $callable;
	}
	
	/*
	Unbind a callback function from an event
	
	@param [string] $event The event name
	@param [mixed] $callable Optional. A valid php callback
	*/
	public static function unbind($event, $callable = null)
	{
		if(isset(self::$_events[$event]))
		{
			if($callable == null)
			{
				// remove all callbacks
				self::$_events[$event] = array();
			}
			else
			{
				foreach(self::$_events[$event] as $index => $callback)
				{
					if($callback == $callable)
					{
						self::$_events[$event] = array_splice(self::$_events[$event], $index, 1);
						break;
					}
				}
			}
		}
	}
	
	/*
	Trigger an event
	
	@param [string] $event The event name
	@param [mixed] $params Parameters to pass along with the event
	*/
	public function trigger($event, $params = null)
	{
		if(isset(self::$_events[$event]))
			self::dispatch(self::$_events[$event], $params);
	}
	
	/*
	Internal function for dispatching events
	
	@param [array] $callbacks The array of event callbacks
	@param [mixed] $params Parameters to pass along with the event
	*/
	protected function dispatch($callbacks, $params)
	{
		foreach($callbacks as $callable)
		{
			if(call_user_func($callable, $params) === false)
			{
				// stop further propagation
				break;
			}
		}
	}
	
};

?>