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
 * Encapsulate a set of persisted objekt objekts and the operations performed over them
 */
class Objekts extends S3\Core\Collection
{
	
	const EVENT_START_ALL_IN_BUCKET = 'startAllInBucket';
	
	protected $_prefix = null;
	protected $_marker = null;
	protected $_maxKeys = null;
	protected $_delimiter = null;
	protected $_isTruncated = false;
	protected $_events = array(self::EVENT_START_ALL_IN_BUCKET);
	
	
	public function setPrefix($prefix)
	{
		if($this->_started)
		{
			throw new S3\Exception('call before starting: '.__METHOD__);
		}
		
		$this->_prefix = $prefix;
		return $this;
	}
	
	
	public function getPrefix()
	{
		return $this->_prefix;
	}
	
	
	public function setMarker($marker)
	{
		if($this->_started)
		{
			throw new S3\Exception('call before starting: '.__METHOD__);
		}
		
		$this->_marker = $marker;
		return $this;
	}
	
	
	public function getMarker()
	{
		return $this->_marker;
	}
	
	
	public function setMaxKeys($maxKeys)
	{
		if($this->_started)
		{
			return $this;
		}
		
		if(!is_int($maxKeys) || $maxKeys < 1)
		{
			throw new S3\Exception('maxKey must be an int > 0');
		}
		
		$this->_maxKeys = $maxKeys;
		return $this;
	}
	
	
	public function getMaxKeys()
	{
		return $this->_maxKeys;
	}
	
	
	public function setDelimiter($delimiter)
	{
		if($this->_started)
		{
			throw new S3\Exception('call before starting: '.__METHOD__);
		}
		
		$this->_delimiter = $delimiter;
		return $this;
	}
	
	
	public function getDelimiter()
	{
		return $this->_delimiter;
	}
	
	
	public function getIsTruncated()
	{
		return $this->_isTruncated;
	}
	
	
	/**
	 * Instantiate a new objekt entity
	 *
	 * @return \Sirprize\Amazon\S3\Objekt
	 */
	public function getObjektInstance()
	{
		$objekt = new S3\Objekt();
		$objekt
			->setRestClient($this->getRestClient())
			->setService($this->getService())
			->setEventManager($this->getEventManager())
		;
		
		return $objekt;
	}
	
	
	
	/*
	
	<?xml version="1.0" encoding="UTF-8"?>
	<ListBucketResult xmlns="http://s3.amazonaws.com/doc/2006-03-01/">
		<Name>media.dev.kompakt.fm</Name>
		<Prefix/>
		<Marker/>
		<MaxKeys>1000</MaxKeys>
		<IsTruncated>true</IsTruncated>
		<Contents>
			<Key>4O88nAT5eaRP.gif</Key>
			<LastModified>2010-07-20T21:41:43.000Z</LastModified>
			<ETag>"5a5e8fc9b5d17a865cabd25ae5aecc62"</ETag>
			<Size>6910</Size>
			<Owner>
				<ID>ce6b45d48e07bf20362a76aa4d367445e8d7cfabd529027eecf4e5d300d0b780</ID>
				<DisplayName>choegl</DisplayName>
			</Owner>
			<StorageClass>STANDARD</StorageClass>
		</Contents>
	</ListBucketResult>
	
	*/
	
	
	
	public function startAllInBucket(S3\Bucket $bucket, $numRetries = 0)
	{
		if($this->_started)
		{
			return $this;
		}
		
		$this->_started = true;
		
		$args = array();
		
		if($this->_prefix !== null)
		{
			$args['prefix'] = $this->_prefix;
		}
		
		if($this->_marker !== null)
		{
			$args['marker'] = $this->_marker;
		}
		
		if($this->_maxKeys !== null)
		{
			$args['max-keys'] = $this->_maxKeys;
		}
		
		if($this->_delimiter !== null)
		{
			$args['delimiter'] = $this->_delimiter;
		}
		
		$date = gmdate(S3\Service::DATE_FORMAT);
		$md5 = '';
		$mime = '';
		$key = '';
		
		$authSignature = $this->getService()->makeAuthSignature(
			'GET',
			$md5,
			$mime,
			$date,
			'',
			'/'.$bucket->getName().'/'
		);
		
		$headers = $this->getService()->getHeadersInstance();
		
		$headers
			->add('Authorization: '.$authSignature)
			->add('Date: '.$date)
		;
		
		$uri = S3\Service::makeUri($bucket->getName(), $key);
		$uri = \Zend_Uri::factory($uri);
		
		$this->getRestClient()
			->getHttpClient()
			->resetParameters(true)
			->setUri($uri)
			->setParameterGet($args)
			->setHeaders($headers->toArray())
		;
		
		$this->_responseHandler = $this->getService()->getResponseHandlerInstance();
		$this->getRestClient()->get($this->_responseHandler, $numRetries);
		
		if($this->_responseHandler->isError())
		{
			// service error
			$eventArgs =
				$this->getService()->getEventArgsInstance()
				->setType(S3\Core\EventArgs::ERR)
				->setCode($this->_responseHandler->getCode())
				->setMessage($this->_responseHandler->getMessage())
				->setSourceObject($this)
			;

			$this->getEventManager()->dispatchEvent(self::EVENT_START_ALL_IN_BUCKET, $eventArgs);
			return $this;
		}
		
		$this->load($this->_responseHandler->getDom(), $bucket);
		
		$eventArgs =
			$this->getService()->getEventArgsInstance()
			->setType(S3\Core\EventArgs::INFO)
			->setMessage('result returned %1$s objects', array($this->count()))
			->setSourceObject($this)
		;
		
		$this->getEventManager()->dispatchEvent(self::EVENT_START_ALL_IN_BUCKET, $eventArgs);
		return $this;
	}
	
	
	
	/**
	 * Instantiate objekt entities with api response data
	 *
	 * @return \Sirprize\Amazon\S3\Objekts
	 */
	public function load(\DOMDocument $dom, S3\Bucket $bucket)
	{
		if($this->_loaded)
		{
			throw new S3\Exception('collection has already been loaded');
		}
		
		$this->_prefix = $dom->getElementsByTagName('Prefix')->item(0)->nodeValue;
		$this->_marker = $dom->getElementsByTagName('Marker')->item(0)->nodeValue;
		$this->_maxKeys = $dom->getElementsByTagName('MaxKeys')->item(0)->nodeValue;
		
		if($dom->getElementsByTagName('Delimiter')->item(0) !== null)
		{
			$this->_delimiter = $dom->getElementsByTagName('Delimiter')->item(0)->nodeValue;
		}
		
		$this->_isTruncated = ($dom->getElementsByTagName('IsTruncated')->item(0)->nodeValue == 'true');
		
		foreach($dom->getElementsByTagName('ListBucketResult')->item(0)->getElementsByTagName('Contents') as $contents)
		{
			$objekt = $this->getObjektInstance();
			$objekt->setBucket($bucket);
			$objekt->load($contents);
			$this->attach($objekt);
		}
		
		$this->_loaded = true;
		return $this;
	}
	
	
	
	/**
	 * Defined by \SplObjectStorage
	 *
	 * Add objekt entity
	 *
	 * @param \Sirprize\Amazon\S3\Objekt $objekt
	 * @throws \Sirprize\Amazon\S3\Exception
	 * @return \Sirprize\Amazon\S3\Objekts
	 */
	public function attach($objekt, $data = null)
	{
		if(!$objekt instanceof S3\Objekt)
		{
			throw new S3\Exception('expecting an instance of \Sirprize\Amazon\S3\Objekt');
		}
		
		parent::attach($objekt);
		return $this;
	}
	
}