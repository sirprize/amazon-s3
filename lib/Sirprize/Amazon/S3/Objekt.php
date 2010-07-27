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


namespace Sirprize\Amazon\S3;


use \Sirprize\Amazon\S3;


/**
 * Represent a objekt
 */
class Objekt extends S3\Core\Entity
{
	
	
	const EVENT_HEAD = 'head';
	const EVENT_COPY = 'copy';
	const EVENT_DELETE = 'delete';
	
	
	protected $_bucket = null;
	protected $_key = null;
	protected $_lastModified = null;
	protected $_eTag = null;
	protected $_size = null;
	protected $_storageClass = null;
	protected $_owner = null;
	protected $_events = array(self::EVENT_HEAD, self::EVENT_COPY, self::EVENT_DELETE);
	
	
	public function setBucket(S3\Bucket $bucket)
	{
		$this->_bucket = $bucket;
		return $this;
	}
	
	
	public function getBucket()
	{
		return $this->_bucket;
	}
	
	
	public function setKey($key)
	{
		$this->_key = $key;
		return $this;
	}
	
	
	public function getKey()
	{
		return $this->_key;
	}
	
	
	public function setLastModified($lastModified)
	{
		$this->_lastModified = $lastModified;
		return $this;
	}
	
	
	public function getLastModified()
	{
		return $this->_lastModified;
	}
	
	
	public function setETag($eTag)
	{
		$this->_eTag = $eTag;
		return $this;
	}
	
	
	public function getETag()
	{
		return $this->_eTag;
	}
	
	
	public function setSize($size)
	{
		$this->_size = $size;
		return $this;
	}
	
	
	public function getSize()
	{
		return $this->_size;
	}
	
	
	public function setStorageClass($storageClass)
	{
		$this->_storageClass = $storageClass;
		return $this;
	}
	
	
	public function getStorageClass()
	{
		return $this->_storageClass;
	}
	
	/*
	public function setOwner($owner)
	{
		$this->_owner = $owner;
		return $this;
	}
	*/
	
	/**
	 * @return \Sirprize\Amazon\S3\Objekt\Owner|null
	 */
	public function getOwner()
	{
		return $this->_owner;
	}
	
	
	/**
	 * Instantiate a new objekt-owner entity
	 *
	 * @return \Sirprize\Amazon\S3\Objekt\Owner
	 */
	public function getOwnerInstance()
	{
		$owner = new S3\Objekt\Owner();
		return $owner;
	}
	
	
	/**
	 * Load data returned from an api request
	 *
	 * @throws \Sirprize\Amazon\S3\Exception
	 * @return \Sirprize\Amazon\S3\Objekt
	 */
	public function load(\DOMElement $contents, $force = false)
	{
		if($this->_loaded && !$force)
		{
			throw new S3\Exception('entity has already been loaded');
		}
		
		$this->_loaded = true;
		
		$this->_key = $contents->getElementsByTagName('Key')->item(0)->nodeValue;
		$this->_lastModified = $contents->getElementsByTagName('LastModified')->item(0)->nodeValue;
		$this->_eTag = $contents->getElementsByTagName('ETag')->item(0)->nodeValue;
		$this->_size = $contents->getElementsByTagName('Size')->item(0)->nodeValue;
		$this->_storageClass = $contents->getElementsByTagName('StorageClass')->item(0)->nodeValue;
		
		$this->_owner = $this->getOwnerInstance();
		$this->_owner->load($contents->getElementsByTagName('Owner')->item(0));
		
		return $this;
	}
	
	
	
	public function head($numRetries = 0)
	{
		if(!$this->getBucket() instanceof S3\Bucket)
		{
			throw new S3\Exception('call setBucket() before '.__METHOD__);
		}
		
		if($this->getKey() === null)
		{
			throw new S3\Exception('call setKey() before '.__METHOD__);
		}
		
		$date = gmdate(S3::DATE_FORMAT);
		$md5 = '';
		$mime = '';
		
		$authSignature = $this->getS3()->makeAuthSignature(
			'HEAD',
			$md5,
			$mime,
			$date,
			'',
			'/'.$this->getBucket()->getName().'/'.$this->getKey()
		);
		
		$headers = $this->getS3()->getHeadersInstance();
		
		$headers
			->add('Authorization: '.$authSignature)
			->add('Date: '.$date)
		;
		
		$uri = S3::makeUri($this->getBucket()->getName(), $this->getKey());
		$uri = \Zend_Uri::factory($uri);
		
		$this->getRestClient()
			->getHttpClient()
			->resetParameters(true)
			->setUri($uri)
			->setHeaders($headers->toArray())
		;
		
		$this->_responseHandler = $this->getS3()->getResponseHandlerInstance();
		$this->getRestClient()->head($this->_responseHandler, $numRetries);
		
		if($this->_responseHandler->isError())
		{
			// service error
			$eventArgs =
				$this->getS3()->getEventArgsInstance()
				->setType(S3\Core\EventArgs::ERR)
				->setCode($this->_responseHandler->getCode())
				->setMessage($this->_responseHandler->getMessage())
				->setSourceObject($this)
			;
			
			$this->getEventManager()->dispatchEvent(self::EVENT_HEAD, $eventArgs);
			return $this;
		}
		
		$this->_size = $this->_responseHandler->getHttpResponse()->getHeader('Content-Length');
		$this->_eTag = $this->_responseHandler->getHttpResponse()->getHeader('ETag');
		$this->_lastModified = $this->_responseHandler->getHttpResponse()->getHeader('Last-Modified');
		/*
		#print get_class($this->_responseHandler->getHttpResponse()); exit;
		#print $this->_responseHandler->getHttpResponse()->getMessage(); exit;
		#print $this->_responseHandler->getHttpResponse()->getBody(); exit;
		
		$this->_date = $this->_responseHandler->getHttpResponse()->getHeader('Date');
		$this->_requestId = $this->_responseHandler->getHttpResponse()->getHeader('x-amz-request-id');
		$this->_versionId = $this->_responseHandler->getHttpResponse()->getHeader('x-amz-version-id');
		*/
		
		$eventArgs =
			$this->getS3()->getEventArgsInstance()
			->setType(S3\Core\EventArgs::INFO)
			->setMessage('OBJECT::HEAD '.$this->getBucket()->getName().'/'.$this->getKey())
			->setSourceObject($this)
		;
		
		$this->getEventManager()->dispatchEvent(self::EVENT_HEAD, $eventArgs);
		return $this;
	}
	
	
	
	
	public function copy(S3\Objekt $newObjekt, S3\Headers $headers = null, $numRetries = 0)
    {
		if(!$this->getBucket() instanceof S3\Bucket)
		{
			throw new S3\Exception('call setBucket() before '.__METHOD__);
		}
		
		if($this->getKey() === null)
		{
			throw new S3\Exception('call setKey() before '.__METHOD__);
		}
		
		if(!$newObjekt->getBucket() instanceof S3\Bucket)
		{
			throw new S3\Exception('newObject - call setBucket() before '.__METHOD__);
		}
		
		if($newObjekt->getKey() === null)
		{
			throw new S3\Exception('newObject - call setKey() before '.__METHOD__);
		}
		
		if($headers === null)
		{
			$headers = $this->getS3()->getHeadersInstance();
		}
		
		$mime = S3::getMimeFromSuffix($this->getKey());
		$date = gmdate(S3::DATE_FORMAT);
		
		$directive
			= ($mime || sizeof($headers->toArray()))
			? S3\Headers::METADATA_DIRECTIVE_REPLACE
			: S3\Headers::METADATA_DIRECTIVE_COPY
		;
		
		$headers->add(S3\Headers::METADATA_DIRECTIVE.':'.$directive);
		$headers->add(S3\Headers::COPY_SOURCE.':/'.$this->getBucket()->getName().'/'.$this->getKey());
		
		$authSignature = $this->getS3()->makeAuthSignature(
			'PUT',
			'',
			$mime,
			$date,
			$headers->getCanonicalizedAmzs(),
			'/'.$newObjekt->getBucket()->getName().'/'.$newObjekt->getKey()
		);
		
		$headers
			->add('Authorization: '.$authSignature)
			->add('Content-Type: '.$mime)
			->add('Date: '.$date)
		;
		
		$uri = S3::makeUri($newObjekt->getBucket()->getName(), $newObjekt->getKey());
		$uri = \Zend_Uri::factory($uri);
		
		$this->getRestClient()
			->getHttpClient()
			->resetParameters(true)
			->setUri($uri)
			->setHeaders($headers->toArray())
		;
		
		$this->_responseHandler = $this->getS3()->getResponseHandlerInstance();
		$this->getRestClient()->put($this->_responseHandler, $numRetries);
		
		$eventMsg = 'OBJECT::COPY '.$this->getBucket()->getName().'/'.$this->getKey().' > '.$newObjekt->getBucket()->getName().'/'.$newObjekt->getKey();
		
		if($this->_responseHandler->isError())
		{
			// service error
			$eventArgs =
				$this->getS3()->getEventArgsInstance()
				->setType(S3\Core\EventArgs::ERR)
				->setCode($this->_responseHandler->getCode())
				->setMessage($eventMsg.' // '.$this->_responseHandler->getMessage())
				->setSourceObject($this)
			;
			
			$this->getEventManager()->dispatchEvent(self::EVENT_COPY, $eventArgs);
			return $this;
		}
		
		$eventArgs =
			$this->getS3()->getEventArgsInstance()
			->setType(S3\Core\EventArgs::INFO)
			->setMessage($eventMsg)
			->setSourceObject($this)
		;
		
		$this->getEventManager()->dispatchEvent(self::EVENT_COPY, $eventArgs);
		return $this;
    }
	
	
	
	public function delete($numRetries = 0)
	{
		if(!$this->getBucket() instanceof S3\Bucket)
		{
			throw new S3\Exception('call setBucket() before '.__METHOD__);
		}
		
		if($this->getKey() === null)
		{
			throw new S3\Exception('call setKey() before '.__METHOD__);
		}
		
		$date = gmdate(S3::DATE_FORMAT);
		
		$authSignature = $this->getS3()->makeAuthSignature(
			'DELETE',
			'',
			'',
			$date,
			'',
			'/'.$this->getBucket()->getName().'/'.$this->getKey()
		);
		
		$headers = $this->getS3()->getHeadersInstance();
		
		$headers
			->add('Authorization: '.$authSignature)
			->add('Date: '.$date)
		;
		
		$uri = S3::makeUri($this->getBucket()->getName(), $this->getKey());
		$uri = \Zend_Uri::factory($uri);
		
		$this->getRestClient()
			->getHttpClient()
			->resetParameters(true)
			->setUri($uri)
			->setHeaders($headers->toArray())
		;
		
		$this->_responseHandler = $this->getS3()->getResponseHandlerInstance();
		$this->getRestClient()->delete($this->_responseHandler, $numRetries);
		
		if($this->_responseHandler->isError())
		{
			// service error
			$eventArgs =
				$this->getS3()->getEventArgsInstance()
				->setType(S3\Core\EventArgs::ERR)
				->setCode($this->_responseHandler->getCode())
				->setMessage($this->_responseHandler->getMessage())
				->setSourceObject($this)
			;
			
			$this->getEventManager()->dispatchEvent(self::EVENT_DELETE, $eventArgs);
			return $this;
		}
		
		$eventArgs =
			$this->getS3()->getEventArgsInstance()
			->setType(S3\Core\EventArgs::INFO)
			->setMessage('OBJECT::DELETE '.$this->getBucket()->getName().'/'.$this->getKey())
			->setSourceObject($this)
		;
		
		$this->getEventManager()->dispatchEvent(self::EVENT_DELETE, $eventArgs);
		return $this;
	}
	
}