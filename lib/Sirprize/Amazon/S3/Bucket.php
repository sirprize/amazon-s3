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
 * Represent a bucket
 */
class Bucket extends S3\Core\Entity
{
	
	protected $_name = null;
	
	
	public function setName($name)
	{
		$this->_name = $name;
		return $this;
	}
	
	
	public function getName()
	{
		return $this->_name;
	}

}