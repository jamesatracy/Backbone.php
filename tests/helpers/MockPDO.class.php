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
 * Mock PDO
 */
class MockPDO
{
    /** @var array Holds the mock data */
	protected $data = array();
	
	/** @var string Holds the last error message */
    protected $last_error = "";
    
    /**
	 * Set the mock results data. 
	 *
	 * @param array $data 
	 */
	public function setResultsData($data)
	{
		$this->data = $data;
	}
	
	/**
	 * Get the mock results data.
	 *
	 * @return array The mock results data.
	 */
	public function getResultsData()
	{
		return $this->data;
	}
	
    public function query($query, $mode)
    {
        return new MockPDOStatement($this->data);
    }
    
    public function exec($query)
    {
        return count($this->data);
    }
    
	public function quote($string)
	{
		return "'".$this->escape($string)."'";
	}
	
	public function escape($string)
	{
		return addslashes($string);
	}
	
	public function lastInsertId()
	{
        return 1;	    
	}
	
	public function setLastError($msg)
	{
	    $this->last_error = $msg;
	}
	
	public function errorInfo()
	{
	    return array("MOCKERR", 1, $this->last_error);
	}
}

/**
 * Mock PDOStatement class.
 */
class MockPDOStatement
{
    protected $data = array();
    protected $pos = 0;
    
    public function __construct($data)
    {
        $this->data = $data;
    }
    
    public function fetch()
    {
        if(isset($this->data[$this->pos])) {
            return $this->data[$this->pos];
        }
        return array();
    }
    
    public function fetchAll()
    {
        return $this->data;
    }
}
?>