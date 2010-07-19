<?php

/**
 * Amazon S3 API Wrapper for PHP 5.3+ 
 *
 * LICENSE
 *
 * This source file is subjekt to the MIT license that is bundled
 * with this package in the file LICENSE.txt
 *
 * @category   Sirprize
 * @package    Amazon\S3
 * @copyright  Copyright (c) 2010, Christian Hoegl, Switzerland (http://sirprize.me)
 * @license    MIT License
 */


namespace Sirprize\Amazon\S3\Core;


use \Sirprize\Amazon\S3;


class EventArgs extends \Doctrine\Common\EventArgs
{

	const EMERG   = 0;  // Emergency: system is unusable
    const ALERT   = 1;  // Alert: action must be taken immediately
    const CRIT    = 2;  // Critical: critical conditions
    const ERR     = 3;  // Error: error conditions
    const WARN    = 4;  // Warning: warning conditions
    const NOTICE  = 5;  // Notice: normal but significant condition
    const INFO    = 6;  // Informational: informational messages
    const DEBUG   = 7;  // Debug: debug messages


	protected $_type = null;
	protected $_sourceObject = null;
	protected $_message = null;
	protected $_args = null;
	protected $_code = null;
	
	
	public function setType($type)
	{
		$this->_type = $type;
		return $this;
	}
	
	
	public function getType()
	{
		if($this->_type === null)
		{
			throw new S3\Exception('call setType() before '.__METHOD__);
		}
		
		return $this->_type;
	}
	
	
	public function setSourceObject($sourceObject)
	{
		if(!is_object($sourceObject))
		{
			throw new S3\Exception('$sourceObject must must be an object');
		}
		
		$this->_sourceObject = $sourceObject;
		return $this;
	}
	
	
	public function getSourceObject()
	{
		if($this->_sourceObject === null)
		{
			throw new S3\Exception('call setSourceObject() before '.__METHOD__);
		}
		
		return $this->_sourceObject;
	}
	
	
	public function setMessage($message, array $args = array())
	{
		$this->_message = $message;
		$this->_args = $args;
		return $this;
	}
	
	
	public function getMessage()
	{
		if($this->_message === null)
		{
			throw new S3\Exception('call setMessage() before '.__METHOD__);
		}
		
		return $this->_message;
	}
	
	
	public function setCode($code)
	{
		$this->_code = $code;
		return $this;
	}
	
	
	public function getCode()
	{
		if($this->_message === null)
		{
			throw new S3\Exception('call setCode() before '.__METHOD__);
		}
		
		return $this->_code;
	}
	
	
	public function getInfo()
	{
		if($this->_message === null)
		{
			return get_class($this->_sourceObject);
		}
		
		$code = ($this->_code === null) ? '' : ' ('.$this->_code.')';
		return vsprintf($this->_message.$code, $this->_args);
	}
	
}