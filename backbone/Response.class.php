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
 * Handles sending HTTP response headers and content.
 *
 * @since 0.2.0
 */
class Response
{
	/** @var int The status code for the response */
	protected $_status = 200;
	
	/** @var string The response HTTP prototcol */
	protected $_protocol = 'HTTP/1.1';
	
	/** @var string The response content mime type */
	protected $_content_type = "text/html";
	
	/** @var array The response headers */
	protected $_headers = array();
	
	/** @var string The response body */
	protected $_body = null;
	
	/** @var array Holds HTTP status codes */
	protected $_status_codes = array(
		100 => 'Continue',
		101 => 'Switching Protocols',
		200 => 'OK',
		201 => 'Created',
		202 => 'Accepted',
		203 => 'Non-Authoritative Information',
		204 => 'No Content',
		205 => 'Reset Content',
		206 => 'Partial Content',
		300 => 'Multiple Choices',
		301 => 'Moved Permanently',
		302 => 'Found',
		303 => 'See Other',
		304 => 'Not Modified',
		305 => 'Use Proxy',
		307 => 'Temporary Redirect',
		400 => 'Bad Request',
		401 => 'Unauthorized',
		402 => 'Payment Required',
		403 => 'Forbidden',
		404 => 'Not Found',
		405 => 'Method Not Allowed',
		406 => 'Not Acceptable',
		407 => 'Proxy Authentication Required',
		408 => 'Request Time-out',
		409 => 'Conflict',
		410 => 'Gone',
		411 => 'Length Required',
		412 => 'Precondition Failed',
		413 => 'Request Entity Too Large',
		414 => 'Request-URI Too Large',
		415 => 'Unsupported Media Type',
		416 => 'Requested range not satisfiable',
		417 => 'Expectation Failed',
		500 => 'Internal Server Error',
		501 => 'Not Implemented',
		502 => 'Bad Gateway',
		503 => 'Service Unavailable',
		504 => 'Gateway Time-out',
		505 => 'Unsupported Version'
	);
	
	/** 
	 * Sends the complete response, including headers and content.
	 *
	 * @since 0.2.0
	 * @param int $status Optional status as a shortcut to calling status()
	 */
	public function send($status = null)
	{
		if($status !== null) {
			$this->status($status);
		}
		// send protocol and status
		$code_message = $this->_status_codes[$this->_status];
		$this->sendHeader($this->_protocol." ".$this->_status." ".$code_message);
		// set content type header
		$this->header("Content-Type", $this->_content_type);
		// send all headers
		foreach($this->_headers as $key => $value) {
			$this->sendHeader($key, $value);
		}
		// send body
		if($this->_body !== null) {
			echo $this->_body;
		}
		// trigger status code event
		Events::trigger("response.".$this->_status);
	}
	
	/**
	 * Gets or sets the status code. Must be a valid code.
	 *
	 * @since 0.2.0
	 * @param int The status code number
	 * @return int Returns the status if $code is set to null
	 * @throws InvalidArgumentException
	 */
	public function status($code = null)
	{
		if($code === null) {
			return $this->_status;
		}
		if(!isset($this->_status_codes[$code])) {
			throw new InvalidArgumentException("Response: Invalid status code ".$code);
		}
		$this->_status = $code;
		return $this->_status;
	}

	/**
	 * Gets or sets the content mime type
	 *
	 * @since 0.2.0
	 * @param string The content mime type
	 * @return string Returns the content mime type if $type is set to null
	 */
	public function contentType($type = null)
	{
		if($type === null) {
			return $this->_content_type;
		}
		$this->_content_type = $type;
		return $this->_content_type;
	}
	
	/** Gets or sets header(s)
	 * 
	 * @since 0.2.0
	 * @param string|array $header The header name or an array of headers
	 * @param string $value The header value, if $header is a string
	 * @return array Returns all headers if $header is null
	 */
	public function header($header = null, $value = null)
	{
		if($header === null) {
			return $this->_headers;
		}
		if(is_array($header)) {
			foreach($header as $key => $value) {
				$this->header($key, $value);
			}
			return $this->_headers;
		}
		if($value !== null) {
			$this->_headers[$header] = $value;
		}
		return $this->_headers;
	}
	
	/**
	 * Gets or sets the body content.
	 *
	 * @since 0.2.0
	 * @param string The body content
	 * @return int Returns the body if $body si set to null
	 */
	public function body($body = null)
	{
		if($body === null) {
			return $this->_body;
		}
		$this->_body = $body;
		return $this->_body;
	}
	
	/**
	 * Sends a header
	 *
	 * @since 0.2.0
	 * @protected
	 * @param string $header The header
	 * @param string $value Optional value, if not included in $header
	 */
	protected function sendHeader($header, $value = null)
	{
		if(!headers_sent()) {
			if($value === null) {
				header($header);
			} else {
				header($header.": ".$value);
			}
		}
	}
}
?> 