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


class Entity
{
	
	
	protected $_s3 = null;
	protected $_restClient = null;
	protected $_started = false;
	protected $_loaded = false;
	protected $_responseHandler = null;
	protected $_eventManager = null;
	protected $_events = array();
	
	
	public function setS3(S3 $s3)
	{
		$this->_s3 = $s3;
		return $this;
	}
	
	
	public function setRestClient(\Sirprize\Rest\Client $restClient)
	{
		$this->_restClient = $restClient;
		return $this;
	}
	
	
	/**
	 * Get response objekt
	 *
	 * @return \Sirprize\Amazon\S3\ResponseHandler\Dom|null
	 */
	public function getResponseHandler()
	{
		return $this->_responseHandler;
	}
	
	
	
	public function setEventManager(\Doctrine\Common\EventManager $eventManager)
	{
		$this->_eventManager = $eventManager;
		return $this;
	}
	
	
	public function getEventManager()
	{
		if($this->_eventManager === null)
		{
			throw new S3\Exception('call setEventManager() before '.__METHOD__);
		}
		
		return $this->_eventManager;
	}
	
	
	public function getEvents()
	{
		return $this->_events;
	}
	
	
	public function addEventsToListener(S3\Core\EventListener $listener)
	{
		foreach($this->_events as $event)
		{
			$this->getEventManager()->addEventListener($event, $listener);
		}
		
		return $this;
	}
	
	
	public function getS3()
	{
		if($this->_s3 === null)
		{
			throw new S3\Exception('call setS3() before '.__METHOD__);
		}
		
		return $this->_s3;
	}
	
	
	
	public function getRestClient()
	{
		if($this->_restClient === null)
		{
			throw new S3\Exception('call setRestClient() before '.__METHOD__);
		}
		
		return $this->_restClient;
	}
	
}