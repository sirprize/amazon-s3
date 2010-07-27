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


namespace Sirprize\Amazon;


use \Sirprize\Amazon\S3;


class S3
{
	
	const DATE_FORMAT = 'D, d M Y G:i:s T';
	
	
	protected $_restClient = null;
	protected $_accessKey = null;
	protected $_secretKey = null;
	
	
	public function __construct(array $config = array())
	{
		if(isset($config['accessKey']))
		{
			$this->_accessKey = $config['accessKey'];
		}
		
		if(isset($config['secretKey']))
		{
			$this->_secretKey = $config['secretKey'];
		}
	}
	
	
	public function setRestClient(\Sirprize\Rest\Client $restClient)
	{
		$this->_restClient = $restClient;
		return $this;
	}
	
	
	public function getRestClient()
    {
        if($this->_restClient === null)
		{
            $this->_restClient = new \Sirprize\Rest\Client();
        }
		
        return $this->_restClient;
    }
	
	
	public function setAccessKey($accessKey)
	{
		$this->_accessKey = $accessKey;
		return $this;
	}
	
	
	public function getAccessKey()
	{
		return $this->_accessKey;
	}
	
	
	public function setSecretKey($secretKey)
	{
		$this->_secretKey = $secretKey;
		return $this;
	}
	
	
	public function getSecretKey()
	{
		return $this->_secretKey;
	}
	
	
	public static function makeUri($bucket, $key = '', $query = '', $cname = false, $ssl = false)
    {
    	$key = ($key) ? '/'.$key : '';
    	$query = ($query) ? '?'.$query : '';
    	$url = ($cname) ? "http://$bucket$key$query" : "http://$bucket.s3.amazonaws.com$key$query";
		return ($ssl) ? "https://$bucket.s3.amazonaws.com$key$query" : $url;
    }
	
	
	public function makeAuthSignature($verb, $md5, $mime, $date, $canonicalizedAmzHeaders, $canonicalizedResource)
    {
    	#Huevo_Debug::print_r(explode("\n", $canonicalizedAmzHeaders));
    	$s = $verb."\n".$md5."\n".$mime."\n".$date."\n".$canonicalizedAmzHeaders.$canonicalizedResource;
    	$s = mb_convert_encoding($s, 'UTF-8');
    	$s = hash_hmac('sha1', $s, $this->getSecretKey(), true);
		$signature = base64_encode($s);
		return 'AWS '.$this->getAccessKey().':'.$signature;
    }
    
    
    public function makeQuerySignature($bucket, $key, $expires, $argSep = '&')
    {
    	$expires = time() + $expires;
		$s = "GET\n\n\n$expires\n/$bucket/$key";
		
		$signature = urlencode(
			base64_encode(
				hash_hmac('sha1', $s, $this->getSecretKey(), true)
			)
		);
		
    	$args = array(
			'AWSAccessKeyId' => $this->getAccessKey(),
			'Expires' => $expires,
			'Signature' => $signature
		);
		
		$query = '';
		
		foreach($args as $name => $val)
		{
			$query .= ($val != '') ? (($query) ? $argSep : '').$name.'='.$val : '';
		}
		
		return '?'.$query;
    }
	
	
	public static function getMimeFromSuffix($suffix)
    {
        $patterns = array(
        	'/\.?swf$/i' => 'application/x-shockwave-flash',
            '/\.?psd$/i' => 'image/vnd.adobe.photoshop',
            '/\.?doc$/i' => 'application/msword',
            '/\.?xls$/i' => 'application/vnd.ms-excel',
            '/\.?ppt$/i' => 'application/vnd.ms-powerpoint',
            '/\.?zip$/i' => 'application/zip',
            '/\.?pdf$/i' => 'application/pdf',
            '/\.?mp3$/i' => 'audio/mpeg',
            '/\.?aif$/i' => 'audio/x-aiff',
            '/\.?wav$/i' => 'audio/wav',
            '/\.?bmp$/i' => 'image/bmp',
            '/\.?gif$/i' => 'image/gif',
            '/\.?(jpg|jpeg|pjpeg)$/i' => 'image/jpeg',
            '/\.?png$/i' => 'image/png',
            '/\.?tif$/i' => 'image/tiff',
            '/\.?css$/i' => 'text/css',
            '/\.?csv$/i' => 'text/csv',
            '/\.?html?$/i' => 'text/html',
            '/\.?txt$/i' => 'text/plain',
            '/\.?rtf$/i' => 'text/rtf',
            '/\.?mpg$/i' => 'video/mp4',
            '/\.?qt$/i' => 'video/quicktime',
            '/\.?avi$/i' => 'video/x-msvideo',
            '/\.?sit$/i' => 'application/x-stuffit',
            '/\.?tar$/i' => 'application/x-tar'
        );
        
        foreach($patterns as $pattern => $mime)
		{
            if(preg_match($pattern, $suffix))
			{
				return $mime;
			}
        }

        return '';
    }
	
	
	public function getEventManagerInstance()
	{
		return new \Doctrine\Common\EventManager();
	}
	
	
	public function getEventArgsInstance()
	{
		return new S3\Core\EventArgs();
	}
	
	
	public function getEventPrinterInstance()
	{
		return new S3\Core\EventPrinter();
	}
	
	
	public function getEventLoggerInstance()
	{
		return new S3\Core\EventLogger();
	}
	
	
	public function getResponseHandlerInstance()
	{
		return new S3\Rest\ResponseHandler\Dom();
	}
	
	
	public function getHeadersInstance()
	{
		$header = new S3\Headers();
		return $header;
	}
	
	
	public function getBucketsInstance()
	{
		$buckets = new S3\Buckets();
		$buckets
			->setS3($this)
			->setRestClient($this->getRestClient())
		;
		return $buckets;
	}
	
	
	public function getObjektsInstance()
	{
		$buckets = new S3\Objekts();
		$buckets
			->setS3($this)
			->setRestClient($this->getRestClient())
			->setEventManager($this->getEventManagerInstance())
		;
		return $buckets;
	}
	
}