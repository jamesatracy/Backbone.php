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
 * Class for triggering and delegating events and callbacks.
 *
 * @since 0.1.0
 */
class Events
{
	/** @var array List of registered events. Events are registered automatically by calls to bind() */
	protected static $_events = array();
	
	/**
	 * Bind a callback function to an event
	 *
	 * @since 0.1.0
	 * @param string $event The event name
	 * @param function|string|array $callable A valid php callback
	 */
	public static function bind($event, $callable)
	{
		if(!isset(self::$_events[$event])) {
			self::$_events[$event] = array();
		}
		self::$_events[$event][] = $callable;
	}
	
	/**
	 * Unbind a callback function from an event
	 *
	 * @since 0.1.0
	 * @param string $event The event name
	 * @param function|string|array $callable Optional. A valid php callback
	 */
	public static function unbind($event, $callable = null)
	{
		if(isset(self::$_events[$event])) {
			if($callable == null) {
				// remove all callbacks
				self::$_events[$event] = array();
			} else {
				foreach(self::$_events[$event] as $index => $callback) {
					if($callback == $callable) {
						self::$_events[$event] = array_splice(self::$_events[$event], $index, 1);
						break;
					}
				}
			}
		}
	}
	
	/**
	 * Trigger an event
	 *
	 * @since 0.1.0
	 * @param string $event The event name
	 * @param mixed $params Parameters to pass along with the event
	 */
	public static function trigger($event, $params = null)
	{
		if(isset(self::$_events[$event])) {
			self::dispatch(self::$_events[$event], $params);
		}
	}
	
	/**
	 * Internal function for dispatching events
	 *
	 * @since 0.1.0
	 * @param array $callbacks The array of event callbacks
	 * @param mixed $params Parameters to pass along with the event
	 */
	protected static function dispatch($callbacks, $params)
	{
		foreach($callbacks as $callable) {
			if(call_user_func($callable, $params) === false) {
				// stop further propagation
				break;
			}
		}
	}
	
};

?>