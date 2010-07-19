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
 * Encapsulate a set of bucket objekts
 */
class Buckets extends S3\Core\Collection
{
	
	
	/**
	 * Instantiate a new bucket entity
	 *
	 * @return \Sirprize\Amazon\S3\Bucket
	 */
	public function getBucketInstance()
	{
		$bucket = new S3\Bucket();
		$bucket
			->setRestClient($this->_getRestClient())
			->setS3($this->_getS3())
		;
		
		return $bucket;
	}
	
}