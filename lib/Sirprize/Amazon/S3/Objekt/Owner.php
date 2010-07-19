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


namespace Sirprize\Amazon\S3\Objekt;


use \Sirprize\Amazon\S3;


/**
 * Represent an objekt owner
 */
class Owner extends S3\Core\Entity
{
	
	protected $_id = null;
	protected $_displayName = null;
	
	
	public function setId($id)
	{
		$this->_id = $id;
		return $this;
	}
	
	
	public function getId()
	{
		return $this->_id;
	}
	
	
	public function setDisplayName($displayName)
	{
		$this->_displayName = $displayName;
		return $this;
	}
	
	
	public function getDisplayName()
	{
		return $this->_displayName;
	}
	
	
	/**
	 * Load data returned from an api request
	 *
	 * @throws \Sirprize\Amazon\S3\Exception
	 * @return \Sirprize\Amazon\S3\Objekt\Owner
	 */
	public function load(\DOMElement $contents, $force = false)
	{
		if($this->_loaded && !$force)
		{
			throw new S3\Exception('entity has already been loaded');
		}
		
		$this->_loaded = true;
		
		$this->_id = $contents->getElementsByTagName('ID')->item(0)->nodeValue;
		$this->_displayName = $contents->getElementsByTagName('DisplayName')->item(0)->nodeValue;
		
		return $this;
	}
	
}