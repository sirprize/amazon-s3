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


class Headers
{
	
	const CANNED_ACL_NAME = 'x-amz-acl';
	const ACL_PRIVATE = 'private';
	const ACL_PUBLIC_READ = 'public-read';
	const ACL_PUBLIC_READ_WRITE = 'public-read-write';
	const ACL_AUTHENTICATED_READ = 'authenticated-read';
	const COPY_SOURCE = 'x-amz-copy-source';
	const METADATA_DIRECTIVE = 'x-amz-metadata-directive';
	const METADATA_DIRECTIVE_COPY = 'COPY';
	const METADATA_DIRECTIVE_REPLACE = 'REPLACE';
	
	
	protected $_headers = array();
    
    
    public function getAmzs()
    {
    	$amzs = array();
    	$final = array();
    	
    	foreach($this->_headers as $h)
    	{
    		if(preg_match('/^x-amz/i', $h))
    		{
				$n = strtolower(trim(preg_replace('/^(x-amz-[a-zA-Z0-9-]*):.*/i', "$1", $h)));
				$v = trim(preg_replace('/^x-amz-[a-zA-Z0-9-]*:(.*)/i', "$1", $h));
				$v = preg_replace('/[\t\n\r\s]+/', ' ', $v);
				
				if(isset($amzs[$n]))
				{
					$amzs[$n] .= ','.$v;
				}
				else {
					$amzs[$n] = $v;
				}
			}
    	}
		
    	ksort($amzs);
		
    	foreach($amzs as $n => $v)
		{
			$final[] = $n.':'.$v;
		}
		
    	return $final;
    }
	
	
	
	public function getStandard()
    {
    	$final = array();
    	
    	foreach($this->_headers as $h)
    	{
    		if(!preg_match('/^x-amz/i', $h))
    		{
				$final[] = $h;
			}
    	}
		
    	return $final;
    }
	
    
    
    public function getCanonicalizedAmzs()
    {
    	$canonicalized = '';
		
    	foreach($this->getAmzs() as $v)
    	{
    		$canonicalized .= $v."\n";
    	}
		
    	return $canonicalized;
    }
    
	
    
    public function toArray($amzOnly = false)
    {
        return
			($amzOnly)
			? $this->getAmzs()
			: array_merge($this->getAmzs(), $this->getStandard())
		;
    }
	
	
	
    public function add($s)
    {
    	$this->_headers[] = $s;
    	return $this;
    }
    
}