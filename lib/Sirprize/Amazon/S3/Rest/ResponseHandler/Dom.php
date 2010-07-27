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


namespace Sirprize\Amazon\S3\Rest\ResponseHandler;


class Dom extends \Sirprize\Rest\ResponseHandler\Dom
{
	
	
	public function load(\Zend_Http_Response $httpResponse)
    {
		parent::load($httpResponse);
		
		if($this->_dom !== null && $this->_dom->getElementsByTagName('Error')->item(0) !== null)
		{
			$error = $this->_dom->getElementsByTagName('Error')->item(0);
			$this->_code = $error->getElementsByTagName('Code')->item(0)->nodeValue;
			$this->_message = $error->getElementsByTagName('Message')->item(0)->nodeValue;
			#$this->_requestId = $error->getElementsByTagName('RequestId')->item(0)->nodeValue;
		}
		
		return $this;
    }
	
}