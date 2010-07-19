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
	
	
	protected function _getS3()
	{
		if($this->_s3 === null)
		{
			throw new S3\Exception('call setS3() before '.__METHOD__);
		}
		
		return $this->_s3;
	}
	
	
	
	protected function _getRestClient()
	{
		if($this->_restClient === null)
		{
			throw new S3\Exception('call setRestClient() before '.__METHOD__);
		}
		
		return $this->_restClient;
	}
	
}