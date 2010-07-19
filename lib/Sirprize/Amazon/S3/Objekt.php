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
	
	protected $_key = null;
	protected $_lastModified = null;
	protected $_eTag = null;
	protected $_size = null;
	protected $_storageClass = null;
	protected $_owner = null;
	
	
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
	
	
	public function setOwner($owner)
	{
		$this->_owner = $owner;
		return $this;
	}
	
	
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
	
}